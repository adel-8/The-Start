<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status == 'low') {
                $query->where('stock', '>', 0)->where('stock', '<=', 5);
            } elseif ($request->stock_status == 'out') {
                $query->where('stock', 0);
            }
        }

        $products = $query->orderBy('id', 'desc')->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'buy_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'is_new' => 'nullable|boolean',
            'bestseller' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240'
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->slug);
        $data['is_new'] = $request->has('is_new');
        $data['bestseller'] = $request->has('bestseller');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image_url'] = '/storage/' . $path;
        }

        // Create product first
        $product = Product::create($data);

        // Handle variations (colors)
        if ($request->has('variations')) {
            foreach ($request->variations as $key => $varData) {
                $variation = $product->variations()->create([
                    'sku'             => $varData['sku'] ?? null,
                    'attribute_name'  => 'color',
                    'attribute_value' => $varData['attribute_value'],
                    'price'           => $varData['price'] ?? null,
                    'stock'           => $varData['stock'] ?? 0,
                ]);

                if ($request->hasFile("variation_images.{$key}")) {
                    $path = $request->file("variation_images.{$key}")->store('product_variations', 'public');
                    $variation->update(['image_url' => '/storage/' . $path]);
                }
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        // Load variations for the edit form
        $product->load('variations');
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,' . $product->id,
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'buy_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'is_new' => 'nullable|boolean',
            'bestseller' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->slug);
        $data['is_new'] = $request->has('is_new');
        $data['bestseller'] = $request->has('bestseller');

        if ($request->hasFile('image')) {
            if ($product->image_url) {
                $oldPath = str_replace('/storage/', '', $product->image_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('products', 'public');
            $data['image_url'] = '/storage/' . $path;
        }

        // Update product basic info
        $product->update($data);

        // Handle variations (colors)
        if ($request->has('variation_ids')) {
            $keepIds = $request->variation_ids;
            $product->variations()->whereNotIn('id', $keepIds)->delete();
        } else {
            $product->variations()->delete();
        }

        if ($request->has('variations')) {
            foreach ($request->variations as $key => $varData) {
                $variation = $product->variations()->updateOrCreate(
                    ['id' => $request->variation_ids[$key] ?? null],
                    [
                        'sku'             => $varData['sku'] ?? null,
                        'attribute_name'  => 'color',
                        'attribute_value' => $varData['attribute_value'],
                        'price'           => $varData['price'] ?? null,
                        'stock'           => $varData['stock'] ?? 0,
                    ]
                );

                if ($request->hasFile("variation_images.{$key}")) {
                    if ($variation->image_url) {
                        $oldPath = str_replace('/storage/', '', $variation->image_url);
                        Storage::disk('public')->delete($oldPath);
                    }
                    $path = $request->file("variation_images.{$key}")->store('product_variations', 'public');
                    $variation->update(['image_url' => '/storage/' . $path]);
                }
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->image_url) {
            $oldPath = str_replace('/storage/', '', $product->image_url);
            Storage::disk('public')->delete($oldPath);
        }
        // Delete variation images as well
        foreach ($product->variations as $variation) {
            if ($variation->image_url) {
                $oldPath = str_replace('/storage/', '', $variation->image_url);
                Storage::disk('public')->delete($oldPath);
            }
        }
        $product->delete();
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        if ($ids && count($ids) > 0) {
            Product::whereIn('id', $ids)->delete();
            return redirect()->route('admin.products.index')->with('success', __('admin.products_deleted'));
        }
        return redirect()->route('admin.products.index')->with('error', __('admin.no_products_selected'));
    }
}