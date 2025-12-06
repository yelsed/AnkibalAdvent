<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Calendar>
 */
class CalendarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => \App\Models\User::factory(),
            'title' => fake()->words(3, true) . ' Advent Calendar',
            'year' => now()->year,
            'description' => fake()->sentence(),
            'theme_color' => fake()->randomElement(['#ec4899', '#f472b6', '#fb7185', '#f9a8d4']),
        ];
    }
}
