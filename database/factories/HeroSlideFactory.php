<?php

namespace Database\Factories;

use App\Models\HeroSlide;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HeroSlide>
 */
class HeroSlideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'badge' => fake()->words(3, true),
            'title' => fake()->sentence(3),
            'highlight' => fake()->sentence(2),
            'description' => fake()->sentence(12),
            'button_text' => 'Belanja sekarang',
            'button_url' => '/produk',
            'image_path' => 'hero/sample.jpg',
            'sort_order' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
