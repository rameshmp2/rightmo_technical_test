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
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'category' => fake()->randomElement(['Electronics', 'Furniture', 'Appliances', 'Sports', 'Clothing']),
            'price' => fake()->randomFloat(2, 10, 2000),
            'rating' => fake()->randomFloat(2, 0, 5),
            'description' => fake()->sentence(10),
            'image' => null,
        ];
    }
}
