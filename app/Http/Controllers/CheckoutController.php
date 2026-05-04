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
use App\Models\Setting; // <-- added to fetch settings

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

        // ---------- NEW: fetch regions and shipping costs ----------
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
        // ---------------------------------------------------------

        return view('checkout', compact(
            'cart', 'addresses', 'defaultAddress',
            'regions', 'regionCosts'           // <-- pass to view
        ));
    }

    public function store(Request $request)
    {
        // Remove address fields if address_id is provided
        if ($request->filled('address_id')) {
            $request->request->remove('address');
            $request->request->remove('city');
            $request->request->remove('region');
            $request->request->remove('postal_code');
        }

        $request->validate([
            'address_id'     => 'nullable|exists:addresses,id',
            'full_name'      => 'required|string|max:255',
            'email'          => 'required|email|max:255',
            'phone'          => 'required|string|max:20',
            'address'        => 'required_if:address_id,null|string|max:255',
            'city'           => 'required_if:address_id,null|string|max:100',
            'region'         => 'nullable|string|max:100',
            'postal_code'    => 'nullable|string|max:20',
            'payment_method' => 'required|in:cash_on_delivery,baridimob,stripe',
            'coupon_code'    => 'nullable|string|max:50',
            'notes'          => 'nullable|string|max:500',
        ]);

        $cart = $this->getCart();
        if (empty($cart)) {
            return $this->jsonResponse(false, 'Your cart is empty.', 400, $request);
        }

        // Calculate shipping cost for the selected region
        $settings = Setting::pluck('setting_value', 'setting_key')->toArray();
        $shippingCost = 0;
        if ($request->filled('region') && isset($settings['shipping_region_costs'])) {
            $regionCosts = json_decode($settings['shipping_region_costs'], true);
            if (isset($regionCosts[$request->region])) {
                $shippingCost = (float) $regionCosts[$request->region];
            }
        }
        if ($shippingCost === 0 && isset($settings['shipping_cost'])) {
            $shippingCost = (float) $settings['shipping_cost'];
        }

        // Apply free shipping threshold if configured
        if (isset($settings['free_shipping_threshold']) && $settings['free_shipping_threshold'] !== '' && is_numeric($settings['free_shipping_threshold'])) {
            $freeThreshold = (float) $settings['free_shipping_threshold'];
            $subtotal = 0;
            foreach ($cart as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            if ($subtotal >= $freeThreshold) {
                $shippingCost = 0;
            }
        }

        // ---------- BARIDIMOB: store data and redirect to instructions (no order yet) ----------
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

        // ---------- STRIPE: redirect to Stripe checkout (order created after payment) ----------
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

        // ---------- COD: create order immediately ----------
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

            // Use saved address if provided, otherwise check for existing or create new
            if ($request->address_id) {
                $address = Address::where('id', $request->address_id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();
            } else {
                // Look for an existing address with the exact same unique fields
                $address = Address::where('user_id', $userId)
                    ->where('address_line1', $request->address)
                    ->where('city', $request->city)
                    ->where('country', 'Algeria')
                    ->first();

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
                'notes'               => $request->notes,
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

            $this->saveCart([]); // clear cart

            DB::commit();

            // Send order confirmation email
            $email = $order->user ? $order->user->email : $order->guest_email;
            Mail::to($email)->send(new OrderConfirmation($order));

            if ($request->expectsJson()) {
                return response()->json([
                    'success'      => true,
                    'message'      => 'Order placed successfully!',
                    'order_number' => $order->order_number,
                ]);
            }

            return redirect()->route('orders.show', $order->order_number)
                ->with('success', 'Order placed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

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