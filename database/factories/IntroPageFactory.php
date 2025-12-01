<?php

namespace Database\Factories;

use App\Models\IntroPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IntroPage>
 */
class IntroPageFactory extends Factory
{
    protected $model = IntroPage::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->paragraphs(3, true),
        ];
    }
}
