<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
public function show($orderNumber)
{
    $order = Order::where('order_number', $orderNumber)
        ->with(['items.product', 'shippingAddress', 'billingAddress', 'coupon'])
        ->firstOrFail();

    if (Auth::check()) {
        // Logged-in user trying to see a guest order → deny
        if (is_null($order->user_id)) {
            abort(403);
        }
        // Logged-in user trying to see another user's order → deny
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }
        return view('orders.show', compact('order'));
    }

    // Guest — only their own just-placed order via session
    if (session('last_order_number') === $order->order_number) {
        return view('orders.show', compact('order'));
    }

    abort(403);
}
}