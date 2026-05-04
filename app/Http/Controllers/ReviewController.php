<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Check if user already reviewed this product
        $existing = Review::where('product_id', $product->id)
            ->where('user_id', Auth::id())
            ->first();
        if ($existing) {
            return back()->with('error', 'You have already reviewed this product.');
        }

        // Optional: require purchase (uncomment if needed)
        // $hasPurchased = $product->orders()->where('user_id', Auth::id())->exists();
        // if (!$hasPurchased) {
        //     return back()->with('error', 'You can only review products you have purchased.');
        // }

        Review::create([
            'product_id' => $product->id,
            'user_id'    => Auth::id(),
            'rating'     => $request->rating,
            'comment'    => $request->comment,
            'approved'   => false, // require admin approval
        ]);

        return back()->with('success', 'Thank you for your review. It will appear after approval.');
    }
}