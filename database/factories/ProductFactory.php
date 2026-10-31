<?php

namespace Database\Factories;

use App\Models\Category;
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
            'category_id' => Category::factory(),
            'name' => fake()->unique()->words(3, true),
            'price' => 100000,
            'stock' => 10,
            'badge' => 'Pilihan',
            'image_path' => 'products/sample.jpg',
            'image_alt' => fake()->sentence(3),
            'is_active' => true,
        ];
    }
}
