<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request)
{
    $query = Coupon::query();

    if ($request->filled('search')) {
        $query->where('code', 'like', "%{$request->search}%");
    }
    if ($request->filled('status')) {
        $query->where('active', $request->status);
    }
    if ($request->filled('discount_type')) {
        $query->where('discount_type', $request->discount_type);
    }

    $coupons = $query->orderBy('id', 'desc')->paginate(20);
    return view('admin.coupons.index', compact('coupons'));
}

public function bulkDelete(Request $request)
{
    $ids = $request->input('ids');
    if ($ids) {
        Coupon::whereIn('id', $ids)->delete();
        return redirect()->route('admin.coupons.index')->with('success', __('admin.coupons_deleted'));
    }
    return redirect()->route('admin.coupons.index')->with('error', __('admin.no_coupons_selected'));
}

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0|max:999999.99',
            'min_order_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after:valid_from',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'total_usage_limit' => 'nullable|integer|min:1',
            'active' => 'sometimes|boolean',
        ]);

        $data = $request->all();
        $data['active'] = $request->has('active') ? 1 : 0;
        $data['min_order_amount'] = $request->filled('min_order_amount') ? $request->min_order_amount : 0;
        $data['usage_limit_per_user'] = $request->filled('usage_limit_per_user') ? $request->usage_limit_per_user : null;
        $data['total_usage_limit'] = $request->filled('total_usage_limit') ? $request->total_usage_limit : null;

        Coupon::create($data);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully.');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0|max:999999.99',
            'min_order_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after:valid_from',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'total_usage_limit' => 'nullable|integer|min:1',
            'active' => 'sometimes|boolean',
        ]);

        $data = $request->all();
        $data['active'] = $request->has('active') ? 1 : 0;
        $data['min_order_amount'] = $request->filled('min_order_amount') ? $request->min_order_amount : 0;
        $data['usage_limit_per_user'] = $request->filled('usage_limit_per_user') ? $request->usage_limit_per_user : null;
        $data['total_usage_limit'] = $request->filled('total_usage_limit') ? $request->total_usage_limit : null;

        $coupon->update($data);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon deleted successfully.');
    }
}