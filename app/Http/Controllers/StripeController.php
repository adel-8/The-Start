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
        // The actual order creation is handled asynchronously via Stripe webhooks.
        // This endpoint now only shows a confirmation to the user.
        return view('stripe.success', [
            'message' => 'Payment received. Your order is being processed and you will receive a confirmation email shortly.'
        ]);
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