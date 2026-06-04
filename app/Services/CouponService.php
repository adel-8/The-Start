<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\CouponUsage;
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
            return ['valid' => false, 'message' => "Minimum order amount is $" . number_format($coupon->min_order_amount, 2)];
        }

        // Global usage limit (using coupon_usages table)
        if ($coupon->total_usage_limit !== null) {
            $totalUses = CouponUsage::where('coupon_id', $coupon->id)->count();
            if ($totalUses >= $coupon->total_usage_limit) {
                return ['valid' => false, 'message' => 'This coupon has reached its total usage limit.'];
            }
        }

        // Per‑user / per‑guest limit
        if ($coupon->usage_limit_per_user !== null && ($userId !== null || $guestEmail !== null)) {
            $userUses = CouponUsage::where('coupon_id', $coupon->id)
                ->when($userId !== null, fn($q) => $q->where('user_id', $userId))
                ->when($guestEmail !== null, fn($q) => $q->where('guest_email', $guestEmail))
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
            'valid'    => true,
            'coupon'   => $coupon,
            'discount' => $discount,
            'total'    => max(0, $subtotal - $discount),
        ];
    }

    /**
     * Record usage of a coupon after an order is successfully created.
     */
    public function recordUsage($couponId, $orderId, $userId = null, $guestEmail = null)
    {
        CouponUsage::create([
            'coupon_id'   => $couponId,
            'order_id'    => $orderId,
            'user_id'     => $userId,
            'guest_email' => $guestEmail,
        ]);
    }
}