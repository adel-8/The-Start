<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display the specified product.
     */
    public function show($slug)
    {
        // Find active product with reviews and variations (colors)
        $product = Product::where('slug', $slug)
                          ->where('status', 'active')
                          ->with(['reviews', 'variations'])
                          ->firstOrFail();

        // Get related products (also eager load variations for product cards)
        $relatedProducts = Product::where('status', 'active')
                                  ->where('id', '!=', $product->id)
                                  ->when($product->category_id, function ($query) use ($product) {
                                      $query->where('category_id', $product->category_id);
                                  })
                                  ->with('variations')
                                  ->inRandomOrder()
                                  ->limit(4)
                                  ->get();

        return view('product', compact('product', 'relatedProducts'));
    }
}