<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CalendarDay>
 */
class CalendarDayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $giftType = fake()->randomElement(['text', 'image_text', 'product']);

        return [
            'calendar_id' => \App\Models\Calendar::factory(),
            'day_number' => fake()->numberBetween(1, 31),
            'gift_type' => $giftType,
            'title' => fake()->words(3, true),
            'content_text' => fake()->paragraph(),
            'content_image_path' => $giftType === 'image_text' ? 'calendar_images/sample.jpg' : null,
            'unlocked_at' => null,
        ];
    }

    public function unlocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'unlocked_at' => now(),
        ]);
    }
}
