<?php

namespace Database\Seeders;

use App\Models\EventType;
use Illuminate\Database\Seeder;

class EventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['slug' => 'walk', 'name' => 'Walk', 'has_duration' => true],
            ['slug' => 'meal', 'name' => 'Meal', 'has_duration' => false],
            ['slug' => 'exercise', 'name' => 'Exercise', 'has_duration' => true],
            ['slug' => 'expense', 'name' => 'Expense', 'has_duration' => false],
        ];

        foreach ($types as $type) {
            EventType::updateOrCreate(
                ['slug' => $type['slug']],
                ['name' => $type['name'], 'has_duration' => $type['has_duration']],
            );
        }
    }
}
