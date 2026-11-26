<?php

namespace Database\Factories;

use App\Models\EventType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventType>
 */
class EventTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'has_duration' => false,
        ];
    }

    public function walk(): static
    {
        return $this->state(fn (): array => [
            'slug' => 'walk',
            'name' => 'Walk',
            'has_duration' => true,
        ]);
    }

    public function meal(): static
    {
        return $this->state(fn (): array => [
            'slug' => 'meal',
            'name' => 'Meal',
            'has_duration' => false,
        ]);
    }

    public function exercise(): static
    {
        return $this->state(fn (): array => [
            'slug' => 'exercise',
            'name' => 'Exercise',
            'has_duration' => true,
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (): array => [
            'slug' => 'expense',
            'name' => 'Expense',
            'has_duration' => false,
        ]);
    }
}
