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
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        $user = Auth::user();
        $addresses = $user ? $user->addresses : collect();
        $defaultAddress = $addresses->where('is_default', true)->first();

        // Fetch shipping region costs
        $settings = Setting::pluck('setting_value', 'setting_key');
        $regionCosts = [];
        if (isset($settings['shipping_region_costs'])) {
            $regionCosts = json_decode($settings['shipping_region_costs'], true);
        }

        // List of Algerian wilayas (58)
        $regions = [
            'Adrar', 'Chlef', 'Laghouat', 'Oum El Bouaghi', 'Batna', 'Béjaïa', 'Biskra', 'Béchar', 'Blida', 'Bouira',
            'Tamanrasset', 'Tébessa', 'Tlemcen', 'Tiaret', 'Tizi Ouzou', 'Algiers', 'Djelfa', 'Jijel', 'Sétif', 'Saïda',
            'Skikda', 'Sidi Bel Abbès', 'Annaba', 'Guelma', 'Constantine', 'Médéa', 'Mostaganem', 'M\'Sila', 'Mascara',
            'Ouargla', 'Oran', 'El Bayadh', 'Illizi', 'Bordj Bou Arréridj', 'Boumerdès', 'El Tarf', 'Tindouf', 'Tissemsilt',
            'El Oued', 'Khenchela', 'Souk Ahras', 'Tipaza', 'Mila', 'Aïn Defla', 'Naâma', 'Aïn Témouchent', 'Ghardaïa',
            'Relizane', 'Timimoun', 'Bordj Badji Mokhtar', 'Ouled Djellal', 'Béni Abbès', 'In Salah', 'In Guezzam', 'Touggourt',
            'Djanet', 'El M\'ghair', 'El Menia'
        ];

        // Get enabled payment methods from settings
        $enabledPayments = [];
        if (($settings['payment_cod_enabled'] ?? '1') == '1') {
            $enabledPayments[] = 'cash_on_delivery';
        }
        if (($settings['payment_baridimob_enabled'] ?? '1') == '1') {
            $enabledPayments[] = 'baridimob';
        }
        if (($settings['payment_stripe_enabled'] ?? '0') == '1') {
            $enabledPayments[] = 'stripe';
        }

        return view('checkout', compact(
            'cart', 'addresses', 'defaultAddress',
            'regions', 'regionCosts', 'enabledPayments'
        ));
    }

    public function store(Request $request)
    {
        // Get enabled payment methods from settings for validation
        $settings = Setting::pluck('setting_value', 'setting_key')->toArray();
        $enabledPayments = [];
        if (($settings['payment_cod_enabled'] ?? '1') == '1') {
            $enabledPayments[] = 'cash_on_delivery';
        }
        if (($settings['payment_baridimob_enabled'] ?? '1') == '1') {
            $enabledPayments[] = 'baridimob';
        }
        if (($settings['payment_stripe_enabled'] ?? '0') == '1') {
            $enabledPayments[] = 'stripe';
        }
        if (empty($enabledPayments)) {
            $enabledPayments = ['cash_on_delivery'];
        }

        // FIX: use required_without instead of required_if for saved address logic
        // FIX: email and postal_code are now optional (nullable)
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
            return $this->jsonResponse(false, 'Your cart is empty.', 400, $request);
        }

        // Calculate shipping cost for the selected region
        $shippingCost = 0;
        if ($request->filled('region') && isset($settings['shipping_region_costs'])) {
            $regionCosts = json_decode($settings['shipping_region_costs'], true);
            if (isset($regionCosts[$request->region])) {
                $shippingCost = (float) $regionCosts[$request->region];
            }
        }

        // BaridiMob: store data and redirect to instructions
        if ($request->payment_method === 'baridimob') {
            Log::info('BaridiMob checkout initiated', [
                'user_id' => Auth::id(),
                'cart_count' => count($cart),
                'ip' => $request->ip(),
            ]);
            Session::put('baridimob_checkout_data', $request->except('_token'));
            Session::put('baridimob_cart', $cart);
            if ($request->filled('coupon_code')) {
                Session::put('baridimob_coupon_code', $request->coupon_code);
            }
            return redirect()->route('payment.baridimob');
        }

        // Stripe: redirect to Stripe checkout
        if ($request->payment_method === 'stripe') {
            Log::info('Stripe checkout initiated', [
                'user_id' => Auth::id(),
                'cart_count' => count($cart),
                'ip' => $request->ip(),
            ]);
            Session::put('stripe_checkout_data', $request->except('_token'));
            Session::put('stripe_cart', $cart);
            if ($request->filled('coupon_code')) {
                Session::put('stripe_coupon_code', $request->coupon_code);
            }
            return redirect()->route('stripe.checkout');
        }

        // COD: create order immediately
        DB::beginTransaction();

        try {
            $products = Product::whereIn('id', array_keys($cart))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $items = [];

            foreach ($cart as $id => $item) {
                $product = $products[$id] ?? null;
                if (!$product) throw new \Exception("Product not found.");
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

            $couponData = $this->couponService->validateCoupon($request->coupon_code, $subtotal);
            $discount = 0;
            $couponId = null;
            if ($couponData && $couponData['valid']) {
                $discount = $couponData['discount'];
                $couponId = $couponData['coupon']->id;
            }

            $total = max(0, $subtotal - $discount + $shippingCost);

            $userId = Auth::id();

            // FIX: handle saved address correctly
            if ($request->filled('address_id')) {
                // Use saved address — verify it belongs to the current user
                $address = Address::where('id', $request->address_id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();
            } else {
                // New address — look for existing or create
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
                        'user_id'        => $userId,
                        'address_line1'  => $request->address,
                        'city'           => $request->city,
                        'state'          => $request->region,
                        'postal_code'    => $request->postal_code,
                        'country'        => 'Algeria',
                        'is_default'     => false,
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

            $this->saveCart([]); // clear cart

            DB::commit();

            // Send order confirmation email (queued)
            if ($order->guest_email || ($order->user && $order->user->email)) {
                try {
                    $email = $order->user ? $order->user->email : $order->guest_email;
                    Mail::to($email)->queue(new OrderConfirmation($order));
                } catch (\Exception $mailEx) {
                    Log::error('Order confirmation queue failed: ' . $mailEx->getMessage());
                }
            }

            // Store in session for guest access
            session(['last_order_number' => $order->order_number]);

            // Always return JSON — JS handles the redirect
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'message'      => 'Order placed successfully!',
                    'redirect'     => route('orders.show', $order->order_number),
                    'order_number' => $order->order_number,
                ]);
            }

            return redirect()->route('orders.show', $order->order_number)
                ->with('success', __('messages.order_placed_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout error: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error'   => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    

    public function applyCoupon(Request $request)
    {
        Log::info('Coupon request', ['code' => $request->code]);

        $request->validate([
            'code' => 'required|string|max:50'
        ]);

        $cart = $this->getCart();

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $result = $this->couponService->validateCoupon($request->code, $subtotal);

        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 422);
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