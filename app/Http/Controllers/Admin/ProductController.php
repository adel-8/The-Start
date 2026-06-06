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
use Illuminate\Support\Facades\Log;

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

        // Create product
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
        $product->load('variations');
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        // Log incoming request for debugging (remove after fixing)
        Log::info('Product update request', $request->all());

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

        // Prepare product data
        $data = $request->only(['name', 'slug', 'description', 'category_id', 'buy_price', 'price', 'stock', 'status']);
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

        $product->update($data);

        // --- Handle variations ---
        $existingIds = $request->input('variation_ids', []);
        $variations = $request->input('variations', []);

        // Delete removed variations
        $product->variations()->whereNotIn('id', $existingIds)->delete();

        // Process variations
        foreach ($variations as $key => $varData) {
            // Skip if color name is missing
            if (empty($varData['attribute_value'])) {
                continue;
            }

            // Determine if this is an existing variation (ID is numeric and appears in existingIds)
            $variationId = null;
            if (is_numeric($key) && in_array($key, $existingIds)) {
                $variationId = $key;
            }

            $variation = $product->variations()->updateOrCreate(
                ['id' => $variationId],
                [
                    'sku'             => $varData['sku'] ?? null,
                    'attribute_name'  => 'color',
                    'attribute_value' => $varData['attribute_value'],
                    'price'           => !empty($varData['price']) ? $varData['price'] : null,
                    'stock'           => $varData['stock'] ?? 0,
                ]
            );

            // Handle image upload for this variation
            if ($request->hasFile("variation_images.{$key}")) {
                if ($variation->image_url) {
                    $oldPath = str_replace('/storage/', '', $variation->image_url);
                    Storage::disk('public')->delete($oldPath);
                }
                $path = $request->file("variation_images.{$key}")->store('product_variations', 'public');
                $variation->update(['image_url' => '/storage/' . $path]);
            }
        }

        Log::info('Product updated', ['product_id' => $product->id, 'variation_count' => $product->variations()->count()]);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
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