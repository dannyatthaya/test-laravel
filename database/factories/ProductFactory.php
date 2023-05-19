<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->unique()->randomNumber(9, true),
            'display_name' => fake()->company(),
            'category' => fake()->jobTitle(),
            'price' => fake()->randomNumber(6, true),
        ];
    }
}
