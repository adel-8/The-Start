<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Support\Facades\Log;
class CouponService
{
    public function validateCoupon($code, $subtotal)
{
 
    if (!$code) return ['valid' => false, 'message' => 'No coupon provided'];

    $coupon = Coupon::where('code', $code)
        ->where('active', 1)
        ->where('valid_from', '<=', now())
        ->where('valid_to', '>=', now())
        ->first();

    if (!$coupon) {
        return ['valid' => false, 'message' => 'Invalid or expired coupon'];
    }

    if ($coupon->min_order_amount > 0 && $subtotal < $coupon->min_order_amount) {
        return ['valid' => false, 'message' => "Minimum order amount is $".number_format($coupon->min_order_amount,2)];
    }

    $discount = 0;
    if ($coupon->discount_type === 'percentage') {
        $discount = $subtotal * ($coupon->discount_value / 100);
    } else {
        $discount = $coupon->discount_value;
    }

    return [
        'valid' => true,
        'coupon' => $coupon,
        'discount' => $discount,
        'total' => $subtotal - $discount,
    ];
}
}