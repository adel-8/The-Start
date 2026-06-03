<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'user_id' => null,
            'guest_name' => $this->faker->name,
            'guest_email' => $this->faker->email,
            'guest_phone' => $this->faker->phoneNumber,
            'total_price' => $this->faker->randomFloat(2, 50, 500),
            'shipping_cost' => 0,
            'status' => 'pending',
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
        ];
    }
}