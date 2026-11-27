<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Event;
use App\Models\EventType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_photo_upload_stores_private_file_and_owner_can_download_it(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $mealType = EventType::factory()->meal()->create();
        $photo = UploadedFile::fake()->image('lunch.jpg', 800, 600)->size(500);

        $this->actingAs($user)->post(route('events.store'), [
            'event_type_id' => $mealType->id,
            'started_at' => '2026-11-24T12:30',
            'data' => ['meal' => 'lunch'],
            'photos' => [$photo],
        ])->assertRedirect(route('home'));

        $attachment = Attachment::sole();
        $this->assertStringStartsWith("attachments/{$user->id}/", $attachment->path);
        Storage::disk('local')->assertExists($attachment->path);

        $response = $this->actingAs($user)->get(route('attachments.show', $attachment));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_other_user_cannot_download_an_attachment(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $event = Event::factory()->expense()->for($owner)->create();
        $attachment = Attachment::factory()->for($event)->create();
        Storage::disk('local')->put($attachment->path, 'private photo');

        $response = $this->actingAs($otherUser)->get(route('attachments.show', $attachment));

        $response->assertForbidden();
    }

    public function test_deleting_an_event_removes_its_attachment_files(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $event = Event::factory()->expense()->for($user)->create();
        $attachment = Attachment::factory()->for($event)->create();
        Storage::disk('local')->put($attachment->path, 'private photo');

        $response = $this->actingAs($user)->delete(route('events.destroy', $event));

        $response->assertRedirect(route('home'));
        Storage::disk('local')->assertMissing($attachment->path);
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_guest_is_redirected_before_downloading_an_attachment(): void
    {
        $attachment = Attachment::factory()->create();

        $response = $this->get(route('attachments.show', $attachment));

        $response->assertRedirect(route('login'));
    }
}
