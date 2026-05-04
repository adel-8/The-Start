<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function show($orderNumber)
{
    $order = Order::where('order_number', $orderNumber)->firstOrFail();
        // Guest orders: require the session last_order_number to match
        if (is_null($order->user_id)) {
            if (session('last_order_number') !== $order->order_number) {
                abort(403, 'Unauthorized');
            }
        } else {
            // Logged-in user: must be the owner
            if (!Auth::check() || Auth::id() !== $order->user_id) {
                abort(403, 'Unauthorized');
            }
        }

        return view('orders.show', compact('order'));
    }
}