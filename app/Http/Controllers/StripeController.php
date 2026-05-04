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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;
use App\Mail\OrderConfirmation;
use App\Traits\CartHelper;

class StripeController extends Controller
{
    use CartHelper;

    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function checkout(Request $request)
    {
        // The data was already validated and stored in session by CheckoutController
        $checkoutData = Session::get('stripe_checkout_data');
        $cart = Session::get('stripe_cart');

        if (!$checkoutData || !$cart) {
            return redirect()->route('cart')->with('error', 'Missing checkout data. Please try again.');
        }

        $userId = Auth::id();

        // Handle address – if address_id was selected, use that, otherwise the address fields
        $address = null;
        if (!empty($checkoutData['address_id'])) {
            $address = Address::where('id', $checkoutData['address_id'])
                ->where('user_id', $userId)
                ->first();
            if (!$address) {
                return redirect()->route('cart')->with('error', 'Invalid address.');
            }
        }

        // Calculate subtotal
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        // Apply coupon if any
        $couponCode = $checkoutData['coupon_code'] ?? null;
        $discount = 0;
        $coupon = null;
        if ($couponCode) {
            $couponResult = $this->couponService->validateCoupon($couponCode, $subtotal);
            if ($couponResult['valid']) {
                $discount = $couponResult['discount'];
                $coupon = $couponResult['coupon'];
            }
        }

        // Determine shipping cost from settings / region data
        $shippingCost = 0;
        $settings = Setting::pluck('setting_value', 'setting_key')->toArray();
        if (!empty($checkoutData['region']) && isset($settings['shipping_region_costs'])) {
            $regionCosts = json_decode($settings['shipping_region_costs'], true);
            if (isset($regionCosts[$checkoutData['region']])) {
                $shippingCost = (float) $regionCosts[$checkoutData['region']];
            }
        }
        if ($shippingCost === 0 && isset($settings['shipping_cost'])) {
            $shippingCost = (float) $settings['shipping_cost'];
        }
        if (isset($settings['free_shipping_threshold']) && $settings['free_shipping_threshold'] !== '' && is_numeric($settings['free_shipping_threshold']) && $subtotal >= (float) $settings['free_shipping_threshold']) {
            $shippingCost = 0;
        }

        $total = max(0, $subtotal - $discount + $shippingCost);

        // Prepare Stripe line items
        $lineItems = [];
        foreach ($cart as $id => $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'dzd',
                    'product_data' => ['name' => $item['name']],
                    'unit_amount'  => $item['price'] * 100,
                ],
                'quantity' => $item['quantity'],
            ];
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        if ($shippingCost > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'dzd',
                    'product_data' => ['name' => 'Shipping'],
                    'unit_amount'  => $shippingCost * 100,
                ],
                'quantity' => 1,
            ];
        }

        $stripeSession = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => route('stripe.success'),
            'cancel_url'           => route('stripe.cancel'),
            'metadata' => [
                'cart'           => json_encode($cart),
                'checkout_data'  => json_encode($checkoutData),
                'coupon_code'    => $couponCode ?? '',
                'address_id'     => $address ? $address->id : '',
                'coupon_discount'=> $discount,
            ],
        ]);

        // Store the Stripe session ID for later retrieval
        Session::put('stripe_session_id', $stripeSession->id);

        return redirect($stripeSession->url);
    }

    public function success(Request $request)
    {
        // Retrieve data from local session
        $cart = Session::get('stripe_cart');
        $checkoutData = Session::get('stripe_checkout_data');
        $couponCode = Session::get('stripe_coupon_code');
        $addressId = Session::get('stripe_address_id');

        // Fallback to Stripe metadata if local session missing
        if (!$cart || !$checkoutData) {
            $sessionId = $request->query('session_id');
            if (!$sessionId) {
                return redirect()->route('cart')->with('error', 'Unable to verify payment. Please contact support.');
            }

            Stripe::setApiKey(config('services.stripe.secret'));
            try {
                $stripeSession = StripeSession::retrieve($sessionId);
                $cart = json_decode($stripeSession->metadata->cart, true);
                $checkoutData = json_decode($stripeSession->metadata->checkout_data, true);
                $couponCode = $stripeSession->metadata->coupon_code ?? null;
                $addressId = $stripeSession->metadata->address_id ?? null;
            } catch (\Exception $e) {
                Log::error('Stripe fallback failed: ' . $e->getMessage());
                return redirect()->route('cart')->with('error', 'Payment verification failed. Please contact support.');
            }
        }

        if (!$cart || !$checkoutData) {
            return redirect()->route('cart')->with('error', 'Unable to retrieve order details.');
        }

        DB::beginTransaction();

        try {
            $productIds = array_keys($cart);
            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $items = [];

            foreach ($cart as $id => $item) {
                $product = $products[$id] ?? null;
                if (!$product) {
                    throw new \Exception("Product not found.");
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
            $discount = 0;
            $couponId = null;
            if ($couponCode) {
                $couponResult = $this->couponService->validateCoupon($couponCode, $subtotal);
                if ($couponResult['valid']) {
                    $discount = $couponResult['discount'];
                    $couponId = $couponResult['coupon']->id;
                }
            }

            $shippingCost = 0;
            $settings = Setting::pluck('setting_value', 'setting_key')->toArray();
            if (!empty($checkoutData['region']) && isset($settings['shipping_region_costs'])) {
                $regionCosts = json_decode($settings['shipping_region_costs'], true);
                if (isset($regionCosts[$checkoutData['region']])) {
                    $shippingCost = (float) $regionCosts[$checkoutData['region']];
                }
            }
            if ($shippingCost === 0 && isset($settings['shipping_cost'])) {
                $shippingCost = (float) $settings['shipping_cost'];
            }
            if (isset($settings['free_shipping_threshold']) && $settings['free_shipping_threshold'] !== '' && is_numeric($settings['free_shipping_threshold']) && $subtotal >= (float) $settings['free_shipping_threshold']) {
                $shippingCost = 0;
            }

            $total = max(0, $subtotal - $discount + $shippingCost);

            // Create or reuse address
            $userId = Auth::id();
            $address = Address::where('user_id', $userId)
                ->where('address_line1', $checkoutData['address'])
                ->where('city', $checkoutData['city'])
                ->where('country', 'Algeria')
                ->first();

            if (!$address) {
                $address = Address::create([
                    'user_id'        => $userId,
                    'address_line1'  => $checkoutData['address'],
                    'city'           => $checkoutData['city'],
                    'state'          => $checkoutData['region'],
                    'postal_code'    => $checkoutData['postal_code'],
                    'country'        => 'Algeria',
                    'is_default'     => false,
                ]);
            }

            $orderNumber = 'ORD-' . strtoupper(Str::random(10));

            $order = Order::create([
                'order_number'        => $orderNumber,
                'user_id'             => Auth::id(),
                'guest_name'          => $checkoutData['full_name'],
                'guest_email'         => $checkoutData['email'],
                'guest_phone'         => $checkoutData['phone'],
                'shipping_address_id' => $address->id,
                'billing_address_id'  => $address->id,
                'coupon_id'           => $couponId,
                'total_price'         => $total,
                'shipping_cost'       => $shippingCost,
                'status'              => 'pending',
                'payment_method'      => 'stripe',
                'payment_status'      => 'paid',
                'notes'               => $checkoutData['notes'] ?? null,
            ]);

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

            // Clear all Stripe session data
            Session::forget([
                'stripe_cart', 'stripe_checkout_data', 'stripe_coupon_code',
                'stripe_address_id', 'stripe_session_id'
            ]);

            // Clear the cart using the helper
            $this->saveCart([]);

            DB::commit();

            // Send order confirmation email
            $email = $order->user ? $order->user->email : $order->guest_email;
            Mail::to($email)->send(new OrderConfirmation($order));

            return redirect()->route('orders.show', $order->order_number)
                ->with('success', 'Payment successful! Your order has been placed.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stripe order creation failed: ' . $e->getMessage());
            return redirect()->route('cart')->with('error', 'Payment was successful but we encountered an issue creating your order. Please contact support.');
        }
    }

    public function cancel()
    {
        Session::forget([
            'stripe_cart', 'stripe_checkout_data', 'stripe_coupon_code',
            'stripe_address_id', 'stripe_session_id'
        ]);
        return redirect()->route('cart')->with('error', 'Payment was cancelled.');
    }
}