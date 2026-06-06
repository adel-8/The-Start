<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'active')
            ->with([
                'reviews',
                'images'  => fn($q) => $q->orderBy('sort_order'),
                'colors'  => fn($q) => $q->orderBy('sort_order'),
            ])
            ->firstOrFail();

        // Related products: same category, also load their images for product-card
        $relatedProducts = Product::where('status', 'active')
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn($q) => $q->where('category_id', $product->category_id))
            ->with(['images' => fn($q) => $q->where('is_primary', true)->limit(1)])
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('product', compact('product', 'relatedProducts'));
    }
}