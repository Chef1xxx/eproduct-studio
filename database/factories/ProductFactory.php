<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
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
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'price' => fake()->randomFloat(2, 100, 50000),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraphs(2, true),
            'advantages' => [
                fake()->sentence(4),
                fake()->sentence(4),
                fake()->sentence(4),
            ],
            'image_path' => null,
        ];
    }
}
