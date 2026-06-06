<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    // ── Index ─────────────────────────────────────────────

    public function index(Request $request)
    {
        $products = Product::with(['images' => fn($q) => $q->where('is_primary', true)])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status,  fn($q) => $q->where('status', $request->status))
            ->when($request->stock_status === 'low', fn($q) => $q->whereBetween('stock', [1, 5]))
            ->when($request->stock_status === 'out', fn($q) => $q->where('stock', 0))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    // ── Create ────────────────────────────────────────────

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    // ── Store ─────────────────────────────────────────────

    public function store(Request $request)
    {
        $this->validateProduct($request);

        $product = Product::create([
            'name'        => $request->name,
            'slug'        => $request->slug,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'buy_price'   => $request->buy_price,
            'price'       => $request->price,
            'stock'       => $request->stock,
            'is_new'      => $request->boolean('is_new'),
            'bestseller'  => $request->boolean('bestseller'),
            'status'      => $request->status,
        ]);

        // Handle colors → returns [formIndex => colorDbId] map
        $colorIdMap = $this->saveColors($product, $request->input('colors', []));

        // Handle gallery images
        $this->saveNewImages($product, $request, $colorIdMap);

        // Sync products.image_url with primary image for backwards compat
        $this->syncPrimaryImage($product->fresh());

        return redirect()->route('admin.products.index')
                         ->with('success', __('admin.product_created'));
    }

    // ── Edit ──────────────────────────────────────────────

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $product->load([
            'colors' => fn($q) => $q->orderBy('sort_order'),
            'images' => fn($q) => $q->orderBy('sort_order'),
        ]);
        return view('admin.products.edit', compact('product', 'categories'));
    }

    // ── Update ────────────────────────────────────────────

    public function update(Request $request, Product $product)
    {
        $this->validateProduct($request, $product->id);

        $product->update([
            'name'        => $request->name,
            'slug'        => $request->slug,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'buy_price'   => $request->buy_price,
            'price'       => $request->price,
            'stock'       => $request->stock,
            'is_new'      => $request->boolean('is_new'),
            'bestseller'  => $request->boolean('bestseller'),
            'status'      => $request->status,
        ]);

        // ① Delete images marked for removal
        foreach ($request->input('delete_image_ids', []) as $imgId) {
            $img = ProductImage::where('id', $imgId)
                               ->where('product_id', $product->id)
                               ->first();
            if ($img) {
                Storage::disk('public')->delete(str_replace('storage/', '', $img->image_path));
                $img->delete();
            }
        }

        // ② Delete colors marked for removal (nullOnDelete handles their images' color_id)
        if ($request->filled('delete_color_ids')) {
            ProductColor::where('product_id', $product->id)
                        ->whereIn('id', $request->delete_color_ids)
                        ->delete();
        }

        // ③ Update / create colors
        $colorIdMap = $this->saveColors($product, $request->input('colors', []), isUpdate: true);

        // ④ Reassign colors on existing images
        foreach ($request->input('existing_image_color', []) as $imgId => $colorId) {
            ProductImage::where('id', $imgId)
                        ->where('product_id', $product->id)
                        ->update(['color_id' => $colorId ?: null]);
        }

        // ⑤ Set primary image (existing images)
        if ($request->filled('primary_image_id')) {
            $product->images()->update(['is_primary' => false]);
            ProductImage::where('id', $request->primary_image_id)
                        ->where('product_id', $product->id)
                        ->update(['is_primary' => true]);
        }

        // ⑥ Upload new images
        $this->saveNewImages($product, $request, $colorIdMap);

        // ⑦ Sync products.image_url
        $this->syncPrimaryImage($product->fresh());

        return redirect()->route('admin.products.index')
                         ->with('success', __('admin.product_updated'));
    }

    // ── Destroy ───────────────────────────────────────────

    public function destroy(Product $product)
    {
        $product->delete(); // soft delete
        return redirect()->route('admin.products.index')
                         ->with('success', __('admin.product_deleted'));
    }

    public function bulkDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if ($ids) Product::whereIn('id', $ids)->delete();

        return redirect()->route('admin.products.index')
                         ->with('success', __('admin.products_deleted'));
    }

    // ── Private helpers ───────────────────────────────────

    private function validateProduct(Request $request, ?int $ignoreId = null): void
    {
        $slugRule = $ignoreId
            ? "required|string|unique:products,slug,{$ignoreId}|max:255"
            : 'required|string|unique:products,slug|max:255';

        $request->validate([
            'name'                 => 'required|string|max:255',
            'slug'                 => $slugRule,
            'description'          => 'nullable|string',
            'category_id'          => 'nullable|exists:categories,id',
            'buy_price'            => 'required|numeric|min:0',
            'price'                => 'required|numeric|min:0',
            'stock'                => 'nullable|integer|min:0',
            'status'               => 'required|in:active,inactive',
            // Colors
            'colors'               => 'nullable|array|max:20',
            'colors.*.name'        => 'required_with:colors|string|max:100',
            'colors.*.name_ar'     => 'nullable|string|max:100',
            'colors.*.hex_code'    => 'required_with:colors|string|max:20',
            'colors.*.id'          => 'nullable|integer|exists:product_colors,id',
            // Delete lists
            'delete_image_ids'     => 'nullable|array',
            'delete_image_ids.*'   => 'integer|exists:product_images,id',
            'delete_color_ids'     => 'nullable|array',
            'delete_color_ids.*'   => 'integer|exists:product_colors,id',
            // New gallery images
            'new_images'           => 'nullable|array|max:20',
            'new_images.*'         => 'image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);
    }

    /**
     * Create or update color rows.
     * Returns [formIndex => dbColorId] so image uploads can reference the right ID.
     */
    private function saveColors(Product $product, array $colors, bool $isUpdate = false): array
    {
        $map = [];

        foreach ($colors as $idx => $data) {
            $existingId = isset($data['id']) ? (int) $data['id'] : null;

            if ($isUpdate && $existingId) {
                $color = ProductColor::where('id', $existingId)
                                     ->where('product_id', $product->id)
                                     ->first();
                if ($color) {
                    $color->update([
                        'name'       => $data['name'],
                        'name_ar'    => $data['name_ar'] ?? null,
                        'hex_code'   => $data['hex_code'],
                        'sort_order' => $idx,
                    ]);
                    $map[$idx] = $color->id;
                    continue;
                }
            }

            // New color
            $color    = $product->colors()->create([
                'name'       => $data['name'],
                'name_ar'    => $data['name_ar'] ?? null,
                'hex_code'   => $data['hex_code'],
                'sort_order' => $idx,
            ]);
            $map[$idx] = $color->id;
        }

        return $map;
    }

    /**
     * Upload new_images[], assign colors and primary flag.
     */
    private function saveNewImages(Product $product, Request $request, array $colorIdMap): void
{
    if (! $request->hasFile('new_images')) {
        Log::info('No new images uploaded.');
        return;
    }

    // Ensure the storage directory exists
    if (! Storage::disk('public')->exists('products')) {
        Storage::disk('public')->makeDirectory('products');
    }

    $offset = $product->images()->count();
    $primaryFormKey = $request->input('new_primary_idx', null);

    foreach ($request->file('new_images') as $idx => $file) {
        try {
            $path = $file->store('products', 'public');
            $fullPath = 'storage/' . $path;

            $colorIdxRaw = $request->input("new_image_color_idx.{$idx}", '');
            $colorId = ($colorIdxRaw !== '' && isset($colorIdMap[(int)$colorIdxRaw]))
                       ? $colorIdMap[(int)$colorIdxRaw]
                       : null;

            $isPrimary = ((string)$primaryFormKey === (string)$idx)
                      || ($offset === 0 && $idx === 0 && $primaryFormKey === null);

            if ($isPrimary) {
                $product->images()->update(['is_primary' => false]);
            }

            $product->images()->create([
                'image_path' => $fullPath,
                'color_id'   => $colorId,
                'is_primary' => $isPrimary,
                'sort_order' => $offset + $idx,
            ]);

            Log::info("Image saved: {$fullPath} for product {$product->id}");
        } catch (\Exception $e) {
            Log::error("Image upload failed: " . $e->getMessage());
        }
    }
}

    /**
     * Keep products.image_url in sync with the primary gallery image.
     * This ensures product cards still work without any changes.
     */
    private function syncPrimaryImage(Product $product): void
    {
        $primary = $product->images()->where('is_primary', true)->first()
                ?? $product->images()->orderBy('sort_order')->first();

        if ($primary) {
            $product->update(['image_url' => $primary->image_path]);
        }
    }
}