<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Mail\OrderConfirmation;
use App\Traits\CartHelper;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    use CartHelper;

    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function baridimobInstructions()
    {
        $checkoutData = Session::get('baridimob_checkout_data');
        $cart = Session::get('baridimob_cart');

        if (!$checkoutData || !$cart) {
            return redirect()->route('cart')->with('error', 'No pending BaridiMob order.');
        }

        // Calculate totals (re‑use coupon if any)
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $discount = 0;
        $couponCode = Session::get('baridimob_coupon_code');
        if ($couponCode) {
            $couponResult = $this->couponService->validateCoupon($couponCode, $subtotal);
            if ($couponResult['valid']) {
                $discount = $couponResult['discount'];
            }
        }

        // Calculate shipping cost (region‑based only)
        $shippingCost = 0;
        $region = $checkoutData['region'] ?? null;
        $settings = Setting::pluck('setting_value', 'setting_key')->toArray();
        if ($region && isset($settings['shipping_region_costs'])) {
            $regionCosts = json_decode($settings['shipping_region_costs'], true);
            if (isset($regionCosts[$region])) {
                $shippingCost = (float) $regionCosts[$region];
            }
        }

        $total = max(0, $subtotal - $discount + $shippingCost);

        return view('payment.baridimob', compact('checkoutData', 'cart', 'subtotal', 'total', 'shippingCost', 'discount'));
    }

    public function uploadProof(Request $request)
    {
        $checkoutData = Session::get('baridimob_checkout_data');
        $cart = Session::get('baridimob_cart');

        if (!$checkoutData || !$cart) {
            return redirect()->route('cart')->with('error', 'No pending BaridiMob order.');
        }

        $request->validate([
            'proof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240',
        ]);

        // Calculate shipping cost (region‑based only)
        $shippingCost = 0;
        $region = $checkoutData['region'] ?? null;
        $settings = Setting::pluck('setting_value', 'setting_key')->toArray();
        if ($region && isset($settings['shipping_region_costs'])) {
            $regionCosts = json_decode($settings['shipping_region_costs'], true);
            if (isset($regionCosts[$region])) {
                $shippingCost = (float) $regionCosts[$region];
            }
        }

        DB::beginTransaction();

        try {
            $productIds = array_keys($cart);
            $products = Product::whereIn('id', $productIds)
                ->where('status', 'active')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $items = [];

            foreach ($cart as $id => $item) {
                $product = $products[$id] ?? null;
                if (!$product) {
                    throw new \Exception('One or more products in your cart are no longer available.');
                }
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Not enough stock for {$product->name}.");
                }

                $price = $product->price;
                $subtotal += $price * $item['quantity'];

                $items[] = [
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $price,
                ];
            }

            // Apply coupon if present
            $couponCode = Session::get('baridimob_coupon_code');
            $discount = 0;
            $couponId = null;
            if ($couponCode) {
                $couponResult = $this->couponService->validateCoupon(
                    $couponCode,
                    $subtotal,
                    Auth::id(),
                    $checkoutData['email'] ?? null
                );
                if ($couponResult['valid']) {
                    $discount = $couponResult['discount'];
                    $couponId = $couponResult['coupon']->id;
                }
            }
            $total = max(0, $subtotal - $discount + $shippingCost);

            // Handle address (deduplication) – no postal_code
            $userId = Auth::id();
            $address = null;

            if ($userId) {
                $address = Address::where('user_id', $userId)
                    ->where('address_line1', $checkoutData['address'])
                    ->where('city', $checkoutData['city'])
                    ->where('country', 'Algeria')
                    ->first();
            }

            if (!$address) {
                $address = Address::create([
                    'user_id'        => $userId,
                    'address_line1'  => $checkoutData['address'],
                    'city'           => $checkoutData['city'],
                    'state'          => $checkoutData['region'],
                    'postal_code'    => null, // explicitly set null (or remove from fillable)
                    'country'        => 'Algeria',
                    'is_default'     => false,
                ]);
            }

            $orderNumber = 'ORD-' . strtoupper(Str::random(10));

            $order = Order::create([
                'order_number'        => $orderNumber,
                'user_id'             => $userId,
                'guest_name'          => $checkoutData['full_name'],
                'guest_email'         => $checkoutData['email'],
                'guest_phone'         => $checkoutData['phone'],
                'shipping_address_id' => $address->id,
                'billing_address_id'  => $address->id,
                'coupon_id'           => $couponId,
                'total_price'         => $total,
                'shipping_cost'       => $shippingCost,
                'status'              => 'pending',
                'payment_method'      => 'baridimob',
                'payment_status'      => 'pending',
                'delivery_type'       => $checkoutData['delivery_type'] ?? 'home',
                'notes'               => $checkoutData['notes'] ?? null,
            ]);
            if ($couponId) {
                $this->couponService->recordUsage(
                    $couponId,
                    $order->id,
                    Auth::id(),
                    $checkoutData['email'] ?? null
                );
            }

            // Store in session for guest access (critical!)
            session(['last_order_number' => $order->order_number]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id'          => $order->id,
                    'product_id'        => $item['product_id'],
                    'variation_id'      => null,
                    'quantity'          => $item['quantity'],
                    'price_at_purchase' => $item['price'],
                ]);

                $products[$item['product_id']]->decrement('stock', $item['quantity']);
            }

            // Save the proof on the non-public local disk so proofs are not directly web-accessible.
            $path = $request->file('proof')->store('proofs', 'local');
            $order->update(['payment_proof' => $path]);

            // Clear all BaridiMob session data
            Session::forget(['baridimob_checkout_data', 'baridimob_cart', 'baridimob_coupon_code']);

            // Clear the actual cart using the trait
            $this->saveCart([]);

            DB::commit();

            Log::info('BaridiMob order created', [
                'order_id' => $order->id,
                'proof_path' => $path,
            ]);

            // Send confirmation email (if email exists) (queued)
            if ($order->guest_email || ($order->user && $order->user->email)) {
                try {
                    $email = $order->user ? $order->user->email : $order->guest_email;
                    Mail::to($email)->queue(new OrderConfirmation($order));
                } catch (\Exception $mailEx) {
                    Log::error('BaridiMob order email queue failed: ' . $mailEx->getMessage());
                }
            }

            return redirect()->route('orders.show', $order->order_number)
                ->with('success', 'Payment proof uploaded! Your order has been placed.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BaridiMob order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to create order. Please contact support.');
        }
    }
}