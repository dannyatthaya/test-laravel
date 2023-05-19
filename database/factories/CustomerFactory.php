<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
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
            'display_name' => fake()->name(),
            'location' => fake()->city(),
            'gender' => fake()->randomElement(['M', 'F']),
        ];
    }
}
