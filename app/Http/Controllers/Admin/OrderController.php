<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request)
{
    $query = Order::query();
    
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('order_number', 'like', '%'.$request->search.'%')
              ->orWhere('guest_name', 'like', '%'.$request->search.'%')
              ->orWhere('guest_email', 'like', '%'.$request->search.'%')
              ->orWhereHas('user', function($uq) use ($request) {
                  $uq->where('name', 'like', '%'.$request->search.'%')
                     ->orWhere('email', 'like', '%'.$request->search.'%');
              });
        });
    }
    
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    
    $orders = $query->latest()->paginate(20);
    
    return view('admin.orders.index', compact('orders'));
}

    public function show(Order $order)
    {
        $order->load('items.product', 'items.color', 'user', 'shippingAddress', 'billingAddress', 'coupon');
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
{
    $request->validate([
        'status' => 'required|in:pending,processing,shipped,delivered,canceled',
        'payment_status' => 'required|in:pending,paid,failed,refunded',
        'tracking_number' => 'nullable|string|max:255',
    ]);

    $order->update($request->only(['status', 'payment_status', 'tracking_number']));

    return redirect()->route('admin.orders.show', $order)
        ->with('success', __('admin.order_updated'));
}

public function updatePaymentStatus(Request $request, Order $order)
{
    $request->validate([
        'payment_status' => 'required|in:paid,failed'
    ]);

    // Only allow admins/owners to do this (middleware already ensures)
    $order->update(['payment_status' => $request->payment_status]);

    return redirect()->route('admin.orders.show', $order)
        ->with('success', 'Payment status updated.');
}


public function downloadProof(Order $order)
{
    // Ensure the user is admin (already via middleware)
    if (!in_array(auth()->user()->role_id, [1, 2])) {
        abort(403);
    }

    $proofPath = $order->payment_proof;
    if (!$proofPath || !Storage::disk('local')->exists($proofPath)) {
        abort(404, 'Proof not found.');
    }

    return Storage::disk('local')->download($proofPath);
}

}