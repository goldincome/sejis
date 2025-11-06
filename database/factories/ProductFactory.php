<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $name = $this->faker->unique()->words(3, true);
        
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'category_id' => Category::factory(),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'status' => 'active',
            'is_featured' => $this->faker->boolean(20), // 20% chance of being featured
            'weight' => $this->faker->randomFloat(2, 0.5, 50),
            'dimensions' => json_encode([
                'length' => $this->faker->numberBetween(10, 200),
                'width' => $this->faker->numberBetween(10, 200),
                'height' => $this->faker->numberBetween(5, 100)
            ]),
            'meta_title' => $name,
            'meta_description' => $this->faker->sentence(),
        ];
    }
}
