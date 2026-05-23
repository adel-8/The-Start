<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function Shop(Request $request)
    {
        $request->validate([
            'category'   => 'nullable|string|max:100',
            'min_price'  => 'nullable|numeric|min:0',
            'max_price'  => 'nullable|numeric|min:0|gt:min_price',
            'search'     => 'nullable|string|max:100',
            'sort'       => 'nullable|in:price_asc,price_desc,newest,bestseller',
            'filter'     => 'nullable|in:bestseller,new',
            'page'       => 'nullable|integer|min:1',
        ]);

        $query = Product::where('status', 'active');

        // Filter (bestseller / new)
        if ($request->filled('filter')) {
            if ($request->filter === 'bestseller') {
                $query->where('bestseller', 1);
            } elseif ($request->filter === 'new') {
                $query->where('is_new', 1);
            }
        }

        // Category filter
        if ($request->filled('category')) {
            $categoryValue = $request->category;
            if (is_numeric($categoryValue)) {
                $query->where('category_id', (int)$categoryValue);
            } else {
                $category = Category::where('name', $categoryValue)->first();
                if ($category) {
                    $query->where('category_id', $category->id);
                }
            }
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        // Sorting
        switch ($request->get('sort')) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'bestseller':
                $query->orderBy('bestseller', 'desc')->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(8)->withQueryString();

        // Categories for sidebar
        $categories = Category::orderBy('name')->get();

        // AJAX response (partial refresh)
        if ($request->ajax() || $request->expectsJson()) {
            $html = view('partials.product-grid', compact('products'))->render();
            return response()->json([
                'html'       => $html,
                'pagination' => (string) $products->links(),
                'count'      => $products->total()
            ]);
        }

        return view('Shop', compact('products', 'categories'));
    }
}