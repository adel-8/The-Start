<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use App\Services\CouponService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Traits\CartHelper;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Models\Setting;

class CheckoutController extends Controller
{
    use CartHelper;

    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function index()
    {
        $cart = $this->getCart();
        if (empty($cart)) {
            return redirect()->route('cart')->with('error', __('messages.cart_empty'));
        }

        // ── Pre-checkout stock validation ─────────────────
        // Check every cart item against live stock before the
        // user even sees the checkout form.
        $productIds    = array_column($cart, 'product_id');
        $liveProducts  = Product::whereIn('id', $productIds)
            ->where('status', 'active')
            ->get()
            ->keyBy('id');

        $stockProblems = [];
        foreach ($cart as $key => $item) {
            $product = $liveProducts[$item['product_id']] ?? null;
            if (!$product) {
                $stockProblems[] = __('messages.product_unavailable_in_cart', ['name' => $item['name']]);
            } elseif ($product->stock <= 0) {
                $stockProblems[] = __('messages.product_out_of_stock_in_cart', ['name' => $product->name]);
            } elseif ($product->stock < $item['quantity']) {
                $stockProblems[] = __('messages.product_low_stock_in_cart', [
                    'name'  => $product->name,
                    'stock' => $product->stock,
                ]);
            }
        }

        if (!empty($stockProblems)) {
            return redirect()->route('cart')
                ->with('stock_errors', $stockProblems)
                ->with('error', __('messages.cart_has_stock_issues'));
        }
        // ─────────────────────────────────────────────────

        $user           = Auth::user();
        $addresses      = $user ? $user->addresses : collect();
        $defaultAddress = $addresses->where('is_default', true)->first();

        $settings   = Setting::pluck('setting_value', 'setting_key');
        $regionCosts = [];
        if (isset($settings['shipping_region_costs'])) {
            $regionCosts = json_decode($settings['shipping_region_costs'], true);
        }

        $regions = [
            'Adrar', 'Chlef', 'Laghouat', 'Oum El Bouaghi', 'Batna', 'Béjaïa', 'Biskra', 'Béchar', 'Blida', 'Bouira',
            'Tamanrasset', 'Tébessa', 'Tlemcen', 'Tiaret', 'Tizi Ouzou', 'Algiers', 'Djelfa', 'Jijel', 'Sétif', 'Saïda',
            'Skikda', 'Sidi Bel Abbès', 'Annaba', 'Guelma', 'Constantine', 'Médéa', 'Mostaganem', "M'Sila", 'Mascara',
            'Ouargla', 'Oran', 'El Bayadh', 'Illizi', 'Bordj Bou Arréridj', 'Boumerdès', 'El Tarf', 'Tindouf', 'Tissemsilt',
            'El Oued', 'Khenchela', 'Souk Ahras', 'Tipaza', 'Mila', 'Aïn Defla', 'Naâma', 'Aïn Témouchent', 'Ghardaïa',
            'Relizane', 'Timimoun', 'Bordj Badji Mokhtar', 'Ouled Djellal', 'Béni Abbès', 'In Salah', 'In Guezzam', 'Touggourt',
            "Djanet", "El M'ghair", 'El Menia',
        ];

        $enabledPayments = [];
        if (($settings['payment_cod_enabled'] ?? '1') == '1')      $enabledPayments[] = 'cash_on_delivery';
        if (($settings['payment_baridimob_enabled'] ?? '1') == '1') $enabledPayments[] = 'baridimob';
        if (($settings['payment_stripe_enabled'] ?? '0') == '1')    $enabledPayments[] = 'stripe';

        return view('checkout', compact(
            'cart', 'addresses', 'defaultAddress',
            'regions', 'regionCosts', 'enabledPayments'
        ));
    }

    public function store(Request $request)
    {
        $settings        = Setting::pluck('setting_value', 'setting_key')->toArray();
        $enabledPayments = [];
        if (($settings['payment_cod_enabled'] ?? '1') == '1')      $enabledPayments[] = 'cash_on_delivery';
        if (($settings['payment_baridimob_enabled'] ?? '1') == '1') $enabledPayments[] = 'baridimob';
        if (($settings['payment_stripe_enabled'] ?? '0') == '1')    $enabledPayments[] = 'stripe';
        if (empty($enabledPayments))                                 $enabledPayments   = ['cash_on_delivery'];

        $request->validate([
            'address_id'     => 'nullable|exists:addresses,id',
            'full_name'      => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'required|string|max:20',
            'address'        => 'required_without:address_id|nullable|string|max:255',
            'city'           => 'required_without:address_id|nullable|string|max:100',
            'region'         => 'nullable|string|max:100',
            'postal_code'    => 'nullable|string|max:20',
            'delivery_type'  => 'nullable|in:home,bureau',
            'payment_method' => 'required|in:' . implode(',', $enabledPayments),
            'coupon_code'    => 'nullable|string|max:50',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $cart = $this->getCart();
        if (empty($cart)) {
            return $this->jsonResponse(false, __('messages.cart_empty'), 400, $request);
        }

        // Shipping cost
        $shippingCost = 0;
        if ($request->filled('region') && isset($settings['shipping_region_costs'])) {
            $regionCosts  = json_decode($settings['shipping_region_costs'], true);
            $shippingCost = (float) ($regionCosts[$request->region] ?? 0);
        }

        // ── BaridiMob ─────────────────────────────────────
        if ($request->payment_method === 'baridimob') {
            Session::put('baridimob_checkout_data', $request->except('_token'));
            Session::put('baridimob_cart', $cart);
            if ($request->filled('coupon_code')) {
                Session::put('baridimob_coupon_code', $request->coupon_code);
            }
            return redirect()->route('payment.baridimob');
        }

        // ── Stripe ────────────────────────────────────────
        if ($request->payment_method === 'stripe') {
            Session::put('stripe_checkout_data', $request->except('_token'));
            Session::put('stripe_cart', $cart);
            if ($request->filled('coupon_code')) {
                Session::put('stripe_coupon_code', $request->coupon_code);
            }
            return redirect()->route('stripe.checkout');
        }

        // ── COD ───────────────────────────────────────────
        DB::beginTransaction();

        try {
            // Lock products and check stock atomically
            $productIds = array_map(fn($item) => $item['product_id'], $cart);
            $products   = Product::whereIn('id', $productIds)
                ->where('status', 'active')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $items    = [];

            foreach ($cart as $cartKey => $item) {
                $product = $products[$item['product_id']] ?? null;

                // ── Clear error messages per product ──────
                if (!$product) {
                    throw new \Exception(
                        __('messages.product_no_longer_available', ['name' => $item['name']])
                    );
                }
                if ($product->stock <= 0) {
                    throw new \Exception(
                        __('messages.product_out_of_stock_checkout', ['name' => $product->name])
                    );
                }
                if ($product->stock < $item['quantity']) {
                    throw new \Exception(
                        __('messages.product_insufficient_stock_checkout', [
                            'name'      => $product->name,
                            'available' => $product->stock,
                            'requested' => $item['quantity'],
                        ])
                    );
                }

                $price     = $product->price;
                $subtotal += $price * $item['quantity'];
                $items[]   = [
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $price,
                    'color_id'   => $item['color_id'] ?? null,
                ];
            }

            // Coupon
            $couponData = $this->couponService->validateCoupon(
                $request->coupon_code,
                $subtotal,
                Auth::id(),
                $request->email
            );
            $discount = 0;
            $couponId = null;
            if ($couponData && $couponData['valid']) {
                $discount = $couponData['discount'];
                $couponId = $couponData['coupon']->id;
            }

            $total  = max(0, $subtotal - $discount + $shippingCost);
            $userId = Auth::id();

            // Address
            if ($request->filled('address_id')) {
                $address = Address::where('id', $request->address_id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();
            } else {
                $address = null;
                if ($userId) {
                    $address = Address::where('user_id', $userId)
                        ->where('address_line1', $request->address)
                        ->where('city', $request->city)
                        ->where('country', 'Algeria')
                        ->first();
                }
                if (!$address) {
                    $address = Address::create([
                        'user_id'       => $userId,
                        'address_line1' => $request->address,
                        'city'          => $request->city,
                        'state'         => $request->region,
                        'postal_code'   => $request->postal_code,
                        'country'       => 'Algeria',
                        'is_default'    => false,
                    ]);
                }
            }

            $orderNumber = 'ORD-' . strtoupper(Str::random(10));

            $order = Order::create([
                'order_number'        => $orderNumber,
                'user_id'             => $userId,
                'guest_name'          => $request->full_name,
                'guest_email'         => $request->email,
                'guest_phone'         => $request->phone,
                'shipping_address_id' => $address->id,
                'billing_address_id'  => $address->id,
                'coupon_id'           => $couponId,
                'total_price'         => $total,
                'shipping_cost'       => $shippingCost,
                'status'              => 'pending',
                'payment_method'      => $request->payment_method,
                'payment_status'      => 'pending',
                'delivery_type'       => $request->delivery_type ?? 'home',
                'notes'               => $request->notes,
            ]);

            if ($couponId) {
                $this->couponService->recordUsage(
                    $couponId, $order->id, Auth::id(), $request->email
                );
            }

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id'          => $order->id,
                    'product_id'        => $item['product_id'],
                    'variation_id'      => null,
                    'quantity'          => $item['quantity'],
                    'price_at_purchase' => $item['price'],
                    'color_id'          => $item['color_id'],
                ]);
                $products[$item['product_id']]->decrement('stock', $item['quantity']);
            }

            $this->saveCart([]);
            DB::commit();

            // Send confirmation email
            try {
                $email = $order->user?->email ?? $order->guest_email;
                if ($email) {
                    Mail::to($email)->queue(new OrderConfirmation($order));
                }
            } catch (\Exception $mailEx) {
                Log::error('Order confirmation email queue failed: ' . $mailEx->getMessage());
            }

            session(['last_order_number' => $order->order_number]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'message'      => __('messages.order_placed_successfully'),
                    'redirect'     => route('orders.show', $order->order_number),
                    'order_number' => $order->order_number,
                ]);
            }

            return redirect()->route('orders.show', $order->order_number)
                ->with('success', __('messages.order_placed_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout error: ' . $e->getMessage(), [
                'cart'        => $cart,
                'user_id'     => Auth::id(),
                'coupon_code' => $request->coupon_code,
            ]);

            // Generic user‑friendly message – never expose the real exception
            $userMessage = __('messages.checkout_error_generic');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error'   => $userMessage,
                ], 422);
            }

            return back()->with('error', $userMessage)->withInput();
        }
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string|max:50']);

        $cart = $this->getCart();
        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty'], 400);
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $result = $this->couponService->validateCoupon($request->code, $subtotal, Auth::id());

        if (!$result['valid']) {
            return response()->json(['success' => false, 'message' => $result['message']], 422);
        }

        session(['coupon' => $result['coupon']]);

        return response()->json([
            'success'  => true,
            'discount' => number_format($result['discount'], 2),
            'total'    => number_format($result['total'], 2),
        ]);
    }

    protected function jsonResponse($success, $message, $status, $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => $success, 'error' => $message], $status);
        }
        return back()->with('error', $message);
    }
}