<?php

namespace Database\Factories;

use App\Models\DailySummary;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailySummary>
 */
class DailySummaryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => fake()->dateTimeBetween('-1 month')->format('Y-m-d'),
            'summary' => fake()->optional()->paragraph(),
            'calories_estimate' => fake()->optional()->numberBetween(1200, 3500),
            'recommendations' => fake()->optional()->sentence(),
            'metrics' => ['events_count' => fake()->numberBetween(0, 10)],
            'is_stale' => true,
            'generated_at' => null,
            'notified_at' => null,
        ];
    }
}
