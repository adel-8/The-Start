<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    protected $cartService;
    protected $couponService;

    public function __construct(CartService $cartService, CouponService $couponService)
    {
        $this->cartService = $cartService;
        $this->couponService = $couponService;
    }

    public function createOrder(array $validatedData, $userId, $couponCode = null)
    {
        $cart = $this->cartService->getCart();
        if (empty($cart)) {
            throw new \Exception('Cart is empty');
        }

        // Fetch all product IDs from cart
        $productIds = array_keys($cart);
        $products = Product::whereIn('id', $productIds)
            ->where('status', 'active')
            ->lockForUpdate() // prevent stock race condition
            ->get()
            ->keyBy('id');

        // Validate stock and calculate totals
        $orderItems = [];
        $subtotal = 0;
        foreach ($cart as $id => $item) {
            $product = $products[$id] ?? null;
            if (!$product) {
                throw new \Exception("Product '{$item['name']}' is no longer available.");
            }
            if ($product->stock < $item['quantity']) {
                throw new \Exception("Insufficient stock for {$product->name}. Only {$product->stock} left.");
            }
            // Use price from database, not from session
            $price = $product->price;
            $quantity = $item['quantity'];
            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price_at_purchase' => $price,
            ];
            $subtotal += $price * $quantity;
        }

        // Apply coupon if provided
        $discount = 0;
        $couponId = null;
        if ($couponCode) {
            $couponResult = $this->couponService->validateCoupon($couponCode, $subtotal);
            if ($couponResult['valid']) {
                $discount = $couponResult['discount'];
                $couponId = $couponResult['coupon']->id;
            } else {
                throw new \Exception($couponResult['message']);
            }
        }

        $total = $subtotal - $discount;
        $shippingCost = 0; // can be dynamic later

        // Create order
        $order = Order::create([
            'order_number' => $this->generateOrderNumber(),
            'user_id' => $userId,
            'shipping_address_id' => $validatedData['address_id'], // we'll create address before calling this
            'billing_address_id' => $validatedData['address_id'],
            'coupon_id' => $couponId,
            'total_price' => $total,
            'shipping_cost' => $shippingCost,
            'status' => 'pending',
            'payment_method' => $validatedData['payment_method'],
            'payment_status' => 'pending',
            'notes' => $validatedData['notes'] ?? null,
        ]);

        // Create order items and update stock
        foreach ($orderItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'variation_id' => null,
                'quantity' => $item['quantity'],
                'price_at_purchase' => $item['price_at_purchase'],
            ]);

            // Deduct stock
            Product::where('id', $item['product_id'])->decrement('stock', $item['quantity']);
        }

        // Clear cart
        $this->cartService->clearCart();

        return $order;
    }

    protected function generateOrderNumber()
    {
        return 'ORD-' . strtoupper(Str::random(8));
    }
}