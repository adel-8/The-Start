<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition()
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('???###')),
            'discount_type' => $this->faker->randomElement(['percentage', 'fixed']),
            'discount_value' => $this->faker->randomFloat(2, 5, 50),
            'min_order_amount' => 0,
            'valid_from' => Carbon::yesterday(),
            'valid_to' => Carbon::tomorrow(),
            'usage_limit_per_user' => null,
            'total_usage_limit' => null,
            'active' => true,
        ];
    }
}