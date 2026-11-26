<?php

namespace Database\Factories;

use App\Enums\EventSource;
use App\Models\Event;
use App\Models\EventType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-1 month');

        return [
            'user_id' => User::factory(),
            'event_type_id' => EventType::factory()->walk(),
            'started_at' => $startedAt,
            'ended_at' => (clone $startedAt)->modify('+30 minutes'),
            'notes' => fake()->optional()->sentence(),
            'data' => ['distance_km' => fake()->randomFloat(1, 0.5, 10)],
            'source' => EventSource::Web,
            'google_event_id' => null,
        ];
    }

    public function walk(float $distanceKm = 3.2, int $minutes = 45): static
    {
        return $this->state(fn (array $attributes): array => [
            'event_type_id' => $this->canonicalType('walk', 'Walk', true)->id,
            'ended_at' => (clone $attributes['started_at'])->modify("+{$minutes} minutes"),
            'data' => ['distance_km' => $distanceKm],
        ]);
    }

    public function meal(string $meal = 'lunch'): static
    {
        return $this->state(fn (): array => [
            'event_type_id' => $this->canonicalType('meal', 'Meal', false)->id,
            'ended_at' => null,
            'data' => ['meal' => $meal, 'description' => fake()->words(3, true)],
        ]);
    }

    public function exercise(string $kind = 'Strength', string $intensity = 'medium'): static
    {
        return $this->state(fn (): array => [
            'event_type_id' => $this->canonicalType('exercise', 'Exercise', true)->id,
            'data' => ['kind' => $kind, 'intensity' => $intensity],
        ]);
    }

    public function expense(int $amountCents = 1500): static
    {
        return $this->state(fn (): array => [
            'event_type_id' => $this->canonicalType('expense', 'Expense', false)->id,
            'ended_at' => null,
            'data' => [
                'amount_cents' => $amountCents,
                'currency' => 'BOB',
                'category' => 'Food',
            ],
        ]);
    }

    private function canonicalType(string $slug, string $name, bool $hasDuration): EventType
    {
        return EventType::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'has_duration' => $hasDuration],
        );
    }
}
