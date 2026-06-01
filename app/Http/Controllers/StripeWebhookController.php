<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmation;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Setting;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class StripeWebhookController extends Controller
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook: invalid payload');
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook: invalid signature');
            return response('Invalid signature', 400);
        } catch (\Exception $e) {
            Log::error('Stripe webhook unexpected error: '.$e->getMessage());
            return response('Webhook error', 500);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $cart = [];
            $checkoutData = [];
            $couponCode = null;

            if (!empty($session->metadata->cart)) {
                $cart = json_decode($session->metadata->cart, true) ?? [];
            }
            if (!empty($session->metadata->checkout_data)) {
                $checkoutData = json_decode($session->metadata->checkout_data, true) ?? [];
            }
            $couponCode = $session->metadata->coupon_code ?? null;

            if (empty($cart) || empty($checkoutData)) {
                Log::warning('Stripe webhook: missing cart or checkout_data in metadata');
                return response('Missing metadata', 400);
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
                        throw new \Exception("Product not found: {$id}");
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

                // Shipping costs
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

                // Determine user if exists by email
                $user = null;
                if (!empty($checkoutData['email'])) {
                    $user = User::where('email', $checkoutData['email'])->first();
                }
                $userId = $user ? $user->id : null;

                // Create or reuse address
                $address = null;
                if (!empty($checkoutData['address'])) {
                    $address = Address::where('user_id', $userId)
                        ->where('address_line1', $checkoutData['address'])
                        ->where('city', $checkoutData['city'] ?? null)
                        ->where('country', 'Algeria')
                        ->first();
                }

                if (!$address) {
                    $address = Address::create([
                        'user_id'        => $userId,
                        'address_line1'  => $checkoutData['address'] ?? null,
                        'city'           => $checkoutData['city'] ?? null,
                        'state'          => $checkoutData['region'] ?? null,
                        'postal_code'    => $checkoutData['postal_code'] ?? null,
                        'country'        => 'Algeria',
                        'is_default'     => false,
                    ]);
                }

                $orderNumber = 'ORD-' . strtoupper(Str::random(10));

                $order = Order::create([
                    'order_number'        => $orderNumber,
                    'user_id'             => $userId,
                    'guest_name'          => $checkoutData['full_name'] ?? ($user ? $user->name : null),
                    'guest_email'         => $checkoutData['email'] ?? ($user ? $user->email : null),
                    'guest_phone'         => $checkoutData['phone'] ?? null,
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

                // Queue confirmation email
                $email = $order->user ? $order->user->email : $order->guest_email;
                if ($email) {
                    Mail::to($email)->queue(new OrderConfirmation($order));
                }

                return response('Received', 200);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Stripe webhook order creation failed: ' . $e->getMessage());
                return response('Webhook handler error', 500);
            }
        }

        return response('Event ignored', 200);
    }
}
