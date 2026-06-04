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
use Stripe\Webhook;
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

        // Load live product data to enforce active products and correct pricing.
        $productIds = array_keys($cart);
        $products = Product::whereIn('id', $productIds)
            ->where('status', 'active')
            ->get()
            ->keyBy('id');

        $subtotal = 0;
        $lineItems = [];
        foreach ($cart as $id => $item) {
            $product = $products[$id] ?? null;
            if (!$product) {
                return redirect()->route('cart')->with('error', 'One or more products in your cart are no longer available.');
            }
            if ($product->stock < $item['quantity']) {
                return redirect()->route('cart')->with('error', 'Not enough stock for ' . $product->name . '.');
            }

            $price = $product->price;
            $subtotal += $price * $item['quantity'];

            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'dzd',
                    'product_data' => ['name' => $product->name],
                    'unit_amount'  => $price * 100,
                ],
                'quantity' => $item['quantity'],
            ];
        }

        // Apply coupon if any
        $couponCode = $checkoutData['coupon_code'] ?? null;
        $discount = 0;
        $coupon = null;
        if ($couponCode) {
            $couponResult = $this->couponService->validateCoupon(
                $couponCode,
                $subtotal,
                Auth::id(),
                $checkoutData['email'] ?? null
            );
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
            'success_url'          => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => route('stripe.cancel'),
            'metadata' => [
                'cart'           => json_encode($cart),
                'checkout_data'  => json_encode($checkoutData),
                'coupon_code'    => $couponCode ?? '',
                'address_id'     => $address ? $address->id : '',
                'coupon_discount'=> $discount,
                'user_id'        => $userId ?? '',
            ],
        ]);

        // Store the Stripe session ID for later retrieval
        Session::put('stripe_session_id', $stripeSession->id);

        return redirect($stripeSession->url);
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return redirect()->route('cart')->with('error', 'Unable to verify payment. Please contact support.');
        }

        $order = Order::where('stripe_session_id', $sessionId)->first();
        if ($order) {
            return redirect()->route('orders.show', $order->order_number)
                ->with('success', 'Payment successful! Your order has been placed.');
        }

        return view('stripe.success', [
            'sessionId' => $sessionId,
        ]);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret') ?? env('STRIPE_WEBHOOK_SECRET');

        if (!$webhookSecret) {
            Log::error('Stripe webhook secret is not configured.');
            return response('Webhook secret is not configured.', 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            return response('Invalid payload.', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response('Invalid signature.', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $metadata = $session->metadata ?? null;
            if ($metadata && !empty($metadata->cart) && !empty($metadata->checkout_data)) {
                $sessionId = $session->id;
                if (!Order::where('stripe_session_id', $sessionId)->exists()) {
                    try {
                        $this->createStripeOrderFromMetadata(
                            json_decode($metadata->cart, true),
                            json_decode($metadata->checkout_data, true),
                            $metadata->coupon_code ?? null,
                            $metadata->address_id ?? null,
                            !empty($metadata->user_id) ? (int) $metadata->user_id : null,
                            $sessionId
                        );
                    } catch (\Exception $e) {
                        Log::error('Stripe webhook order creation failed: ' . $e->getMessage());
                        return response('Webhook processing failed.', 500);
                    }
                }
            }
        }

        return response('Webhook received.', 200);
    }

    protected function createStripeOrderFromMetadata(array $cart, array $checkoutData, ?string $couponCode, ?string $addressId, ?int $userId, string $stripeSessionId)
    {
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

            $discount = 0;
            $couponId = null;
            if ($couponCode) {
                $couponResult = $this->couponService->validateCoupon(
                    $couponCode,
                    $subtotal,
                    $userId,
                    $checkoutData['email'] ?? null
                );
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

            $userId = Auth::id();
            $address = null;
            if (!empty($addressId)) {
                $address = Address::find($addressId);
            }

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
                'payment_method'      => 'stripe',
                'payment_status'      => 'paid',
                'stripe_session_id'   => $stripeSessionId,
                'notes'               => $checkoutData['notes'] ?? null,
            ]);
            if ($couponId) {
                $this->couponService->recordUsage(
                    $couponId,
                    $order->id,
                    $userId,          // use the passed $userId, not Auth::id()
                    $checkoutData['email'] ?? null
                );
            }

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

            DB::commit();

            try {
                $email = $order->user ? $order->user->email : $order->guest_email;
                Mail::to($email)->send(new OrderConfirmation($order));
            } catch (\Exception $mailEx) {
                Log::error('Stripe order confirmation email failed: ' . $mailEx->getMessage());
            }

            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
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