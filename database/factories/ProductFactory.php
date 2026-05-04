<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->slug,
            'description' => $this->faker->paragraph,
            'category_id' => Category::factory(),
            'buy_price' => $this->faker->randomFloat(2, 10, 50),
            'price' => $this->faker->randomFloat(2, 20, 200),
            'stock' => $this->faker->numberBetween(0, 100),
            'is_new' => $this->faker->boolean(20),
            'bestseller' => $this->faker->boolean(20),
            'status' => 'active',
            'image_url' => null,
        ];
    }
}