<?php

namespace Tests\Feature;

use App\Enums\EventSource;
use App\Models\Attachment;
use App\Models\Event;
use App\Models\EventType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, array<string, mixed>, ?string}>
     */
    public static function eventTypePayloads(): array
    {
        return [
            'walk' => ['walk', ['distance_km' => 3.2, 'steps' => 4200], '2026-11-24T22:15'],
            'meal' => ['meal', ['meal' => 'lunch', 'description' => 'Soup'], null],
            'exercise' => ['exercise', ['kind' => 'Strength', 'intensity' => 'high'], '2026-11-24T22:15'],
            'expense' => ['expense', ['amount_cents' => 1500, 'currency' => 'BOB'], null],
        ];
    }

    #[DataProvider('eventTypePayloads')]
    public function test_owner_can_store_each_event_type(
        string $eventTypeSlug,
        array $eventData,
        ?string $endedAt,
    ): void {
        $user = User::factory()->create();
        $eventType = EventType::factory()->{$eventTypeSlug}()->create();

        $response = $this->actingAs($user)->post(route('events.store'), [
            'event_type_id' => $eventType->id,
            'started_at' => '2026-11-24T21:30',
            'ended_at' => $endedAt,
            'data' => $eventData,
        ]);

        $response->assertRedirect(route('home'));
        $event = Event::sole();
        $this->assertTrue($event->user->is($user));
        $this->assertSame('2026-11-25 01:30:00', $event->started_at->format('Y-m-d H:i:s'));
        $this->assertSame($eventData, $event->data);
        $this->assertSame(EventSource::Web, $event->source);
    }

    public function test_expense_requires_an_amount(): void
    {
        $user = User::factory()->create();
        $expenseType = EventType::factory()->expense()->create();

        $response = $this->actingAs($user)->post(route('events.store'), [
            'event_type_id' => $expenseType->id,
            'started_at' => '2026-11-24T21:30',
            'data' => ['currency' => 'BOB'],
        ]);

        $response->assertInvalid(['data.amount_cents']);
        $this->assertDatabaseEmpty('events');
    }

    public function test_meal_rejects_an_end_time(): void
    {
        $user = User::factory()->create();
        $mealType = EventType::factory()->meal()->create();

        $response = $this->actingAs($user)->post(route('events.store'), [
            'event_type_id' => $mealType->id,
            'started_at' => '2026-11-24T21:30',
            'ended_at' => '2026-11-24T22:00',
            'data' => ['meal' => 'dinner'],
        ]);

        $response->assertInvalid(['ended_at']);
        $this->assertDatabaseEmpty('events');
    }

    public function test_event_rejects_an_end_time_before_its_start(): void
    {
        $user = User::factory()->create();
        $walkType = EventType::factory()->walk()->create();

        $response = $this->actingAs($user)->post(route('events.store'), [
            'event_type_id' => $walkType->id,
            'started_at' => '2026-11-24T21:30',
            'ended_at' => '2026-11-24T21:00',
            'data' => ['distance_km' => 3.2],
        ]);

        $response->assertInvalid(['ended_at']);
        $this->assertDatabaseEmpty('events');
    }

    public function test_unknown_type_data_is_dropped_before_storage(): void
    {
        $user = User::factory()->create();
        $expenseType = EventType::factory()->expense()->create();

        $this->actingAs($user)->post(route('events.store'), [
            'event_type_id' => $expenseType->id,
            'started_at' => '2026-11-24T21:30',
            'data' => [
                'amount_cents' => 1500,
                'currency' => 'BOB',
                'unexpected' => 'not stored',
            ],
        ])->assertRedirect(route('home'));

        $this->assertSame(
            ['amount_cents' => 1500, 'currency' => 'BOB'],
            Event::sole()->data,
        );
    }

    public function test_timeline_returns_the_users_events_and_timezone(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-25 03:00:00 UTC'));
        $user = User::factory()->create();
        $event = Event::factory()->expense()->for($user)->create([
            'started_at' => '2026-11-25 01:30:00+00',
        ]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page): Assert => $page
            ->component('Events/Index', false)
            ->where('app.userTimezone', 'America/La_Paz')
            ->has('events.data', 1)
            ->where('events.data.0.id', $event->id)
            ->where('events.data.0.started_at', '2026-11-25T01:30:00.000000Z')
        );
    }

    public function test_owner_can_update_an_event(): void
    {
        $user = User::factory()->create();
        $expenseType = EventType::factory()->expense()->create();
        $event = Event::factory()->expense(1500)->for($user)->for($expenseType)->create();

        $response = $this->actingAs($user)->put(route('events.update', $event), [
            'event_type_id' => $expenseType->id,
            'started_at' => '2026-11-24T19:15',
            'notes' => 'Updated note',
            'data' => ['amount_cents' => 2750, 'currency' => 'BOB'],
        ]);

        $response->assertRedirect(route('home'));
        $this->assertSame('Updated note', $event->refresh()->notes);
        $this->assertSame(2750, $event->data['amount_cents']);
    }

    public function test_update_rejects_an_attachment_from_another_event(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $event = Event::factory()->expense()->for($user)->create(['notes' => 'Original note']);
        $otherEvent = Event::factory()->expense()->for($user)->create();
        $otherAttachment = Attachment::factory()->for($otherEvent)->create();
        Storage::disk('local')->put($otherAttachment->path, 'other event photo');

        $response = $this->actingAs($user)->put(route('events.update', $event), [
            'event_type_id' => $event->event_type_id,
            'started_at' => '2026-11-24T19:15',
            'notes' => 'Changed note',
            'data' => ['amount_cents' => 2750, 'currency' => 'BOB'],
            'removed_attachment_ids' => [$otherAttachment->id],
        ]);

        $response->assertInvalid(['removed_attachment_ids.0']);
        $this->assertSame('Original note', $event->refresh()->notes);
        $this->assertModelExists($otherAttachment);
        Storage::disk('local')->assertExists($otherAttachment->path);
    }

    public function test_update_rejects_more_than_four_photos_counting_existing_attachments(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $event = Event::factory()->expense()->for($user)->create(['notes' => 'Original note']);
        Attachment::factory()->count(4)->for($event)->create();
        $newPhoto = UploadedFile::fake()->image('receipt.jpg', 800, 600)->size(500);

        $response = $this->actingAs($user)->put(route('events.update', $event), [
            'event_type_id' => $event->event_type_id,
            'started_at' => '2026-11-24T19:15',
            'notes' => 'Changed note',
            'data' => ['amount_cents' => 2750, 'currency' => 'BOB'],
            'photos' => [$newPhoto],
        ]);

        $response->assertInvalid(['photos']);
        $this->assertSame('Original note', $event->refresh()->notes);
        $this->assertSame(4, $event->attachments()->count());
    }

    public function test_create_ignores_removed_attachment_ids_and_deletes_nothing(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $existingEvent = Event::factory()->expense()->for($user)->create();
        $existingAttachment = Attachment::factory()->for($existingEvent)->create();
        Storage::disk('local')->put($existingAttachment->path, 'existing photo');

        $response = $this->actingAs($user)->post(route('events.store'), [
            'event_type_id' => $existingEvent->event_type_id,
            'started_at' => '2026-11-24T19:15',
            'data' => ['amount_cents' => 2750, 'currency' => 'BOB'],
            'removed_attachment_ids' => [$existingAttachment->id],
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseCount('events', 2);
        $this->assertModelExists($existingAttachment);
        Storage::disk('local')->assertExists($existingAttachment->path);
    }

    /** @return array<string, array{string}> */
    public static function protectedEventActions(): array
    {
        return [
            'edit' => ['edit'],
            'update' => ['update'],
            'delete' => ['delete'],
        ];
    }

    #[DataProvider('protectedEventActions')]
    public function test_other_user_is_forbidden_from_event_actions(string $protectedAction): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $event = Event::factory()->expense()->for($owner)->create();

        $response = match ($protectedAction) {
            'edit' => $this->actingAs($otherUser)->get(route('events.edit', $event)),
            'update' => $this->actingAs($otherUser)->put(route('events.update', $event), []),
            'delete' => $this->actingAs($otherUser)->delete(route('events.destroy', $event)),
        };

        $response->assertForbidden();
        $this->assertModelExists($event);
    }

    public function test_guest_is_redirected_to_login_from_the_timeline(): void
    {
        $response = $this->get(route('home'));

        $response->assertRedirect(route('login'));
    }
}
