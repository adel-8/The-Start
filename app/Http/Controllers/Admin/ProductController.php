<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
class ProductController extends Controller
{



    public function index(Request $request)
    {
        $query = Product::with('category');

        // Search by name or slug
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by stock status
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

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image_url'] = '/storage/' . $path;
        }

        $data['is_new'] = $request->has('is_new');
        $data['bestseller'] = $request->has('bestseller');

        Product::create($data);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
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

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image_url) {
                $oldPath = str_replace('/storage/', '', $product->image_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('products', 'public');
            $data['image_url'] = '/storage/' . $path;
        }

        $data['is_new'] = $request->has('is_new');
        $data['bestseller'] = $request->has('bestseller');

        $product->update($data);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Delete image if exists
        if ($product->image_url) {
            $oldPath = str_replace('/storage/', '', $product->image_url);
            Storage::disk('public')->delete($oldPath);
        }
        $product->delete();
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted.');
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