<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'path' => 'attachments/'.fake()->numberBetween(1, 1000).'/'.fake()->uuid().'.jpg',
            'mime' => 'image/jpeg',
            'size' => fake()->numberBetween(10_000, 2_000_000),
        ];
    }
}
