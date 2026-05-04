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
        // Find the active product by slug, or fail with 404
        $product = Product::where('slug', $slug)
                          ->where('status', 'active')
                          ->with('reviews') // eager load reviews (if needed in view)
                          ->firstOrFail();

        // Get related products: same category, exclude current product, limit 4
        $relatedProducts = Product::where('status', 'active')
                                  ->where('id', '!=', $product->id)
                                  ->when($product->category_id, function ($query) use ($product) {
                                      $query->where('category_id', $product->category_id);
                                  })
                                  ->inRandomOrder()
                                  ->limit(4)
                                  ->get();

        return view('product', compact('product', 'relatedProducts'));
    }
}