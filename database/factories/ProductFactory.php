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
            'slug' => $this->faker->unique()->slug,
            'description' => $this->faker->paragraph,
            'category_id' => \App\Models\Category::factory(), // add this
            'buy_price' => $this->faker->numberBetween(20, 80),
            'price' => $this->faker->numberBetween(50, 200),
            'stock' => $this->faker->numberBetween(0, 50),
            'is_new' => false,
            'bestseller' => false,
            'status' => 'active',
            'image_url' => '/storage/products/dummy.jpg',
        ];
    }
}