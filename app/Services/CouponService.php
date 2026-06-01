<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class CouponService
{
    public function validateCoupon($code, $subtotal, $userId = null, $guestEmail = null)
    {
        if (!$code) {
            return ['valid' => false, 'message' => 'No coupon provided'];
        }

        $coupon = Coupon::where('code', $code)
            ->where('active', 1)
            ->where('valid_from', '<=', now())
            ->where('valid_to', '>=', now())
            ->first();

        if (!$coupon) {
            return ['valid' => false, 'message' => 'Invalid or expired coupon'];
        }

        if ($coupon->min_order_amount > 0 && $subtotal < $coupon->min_order_amount) {
            return ['valid' => false, 'message' => "Minimum order amount is $".number_format($coupon->min_order_amount, 2)];
        }

        if ($coupon->total_usage_limit !== null) {
            $totalUses = Order::where('coupon_id', $coupon->id)->count();
            if ($totalUses >= $coupon->total_usage_limit) {
                return ['valid' => false, 'message' => 'This coupon has reached its total usage limit.'];
            }
        }

        if ($coupon->usage_limit_per_user !== null && ($userId !== null || $guestEmail !== null)) {
            $userUses = Order::where('coupon_id', $coupon->id)
                ->when($userId !== null, fn($query) => $query->orWhere('user_id', $userId))
                ->when($guestEmail !== null, fn($query) => $query->orWhere('guest_email', $guestEmail))
                ->count();

            if ($userUses >= $coupon->usage_limit_per_user) {
                return ['valid' => false, 'message' => 'You have already reached the usage limit for this coupon.'];
            }
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
            'total' => max(0, $subtotal - $discount),
        ];
    }
}