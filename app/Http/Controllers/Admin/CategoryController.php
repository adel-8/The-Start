<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories with search, status filter, and pagination.
     */
    public function index(Request $request)
    {
        $query = Category::withCount('products');

        // Search by name, slug, or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status (1 = active, 0 = inactive)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $categories = $query->orderBy('position')
                           ->orderBy('name')
                           ->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')
                                    ->orderBy('position')
                                    ->orderBy('name')
                                    ->get();

        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:100|unique:categories,name',
            'slug'         => 'nullable|string|max:100|unique:categories,slug',
            'description'  => 'nullable|string|max:500',
            'parent_id'    => 'nullable|exists:categories,id',
            'status'       => 'nullable|boolean',
            'position'     => 'nullable|integer|min:0',
        ]);

        $data = $request->only(['name', 'description', 'parent_id', 'status', 'position']);

        // Generate slug if not provided
        if ($request->filled('slug')) {
            $data['slug'] = Str::slug($request->slug);
        } else {
            $data['slug'] = Str::slug($request->name);
        }

        // Ensure unique slug
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Category::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter++;
        }

        // Set default position to last if not provided
        if (!isset($data['position'])) {
            $maxPosition = Category::max('position');
            $data['position'] = $maxPosition + 1;
        }

        // Default status to active if not provided
        $data['status'] = $request->boolean('status', true);

        Category::create($data);

        return redirect()->route('admin.categories.index')
                         ->with('success', __('admin.category_created'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::where('id', '!=', $category->id)
                                    ->whereNull('parent_id')
                                    ->orderBy('position')
                                    ->orderBy('name')
                                    ->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name'         => 'required|string|max:100|unique:categories,name,' . $category->id,
            'slug'         => 'nullable|string|max:100|unique:categories,slug,' . $category->id,
            'description'  => 'nullable|string|max:500',
            'parent_id'    => 'nullable|exists:categories,id',
            'status'       => 'nullable|boolean',
            'position'     => 'nullable|integer|min:0',
        ]);

        $data = $request->only(['name', 'description', 'parent_id', 'status', 'position']);

        // Slug handling
        if ($request->filled('slug')) {
            $data['slug'] = Str::slug($request->slug);
        } else {
            $data['slug'] = Str::slug($request->name);
        }

        // Ensure unique slug (excluding current category)
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Category::where('slug', $data['slug'])->where('id', '!=', $category->id)->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter++;
        }

        // Default status to current if not provided
        $data['status'] = $request->boolean('status', $category->status);

        // Prevent category from being its own parent
        if ($data['parent_id'] == $category->id) {
            $data['parent_id'] = null;
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
                         ->with('success', __('admin.category_updated'));
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return back()->with('error', __('admin.category_has_products'));
        }

        // Optionally, reorder children to prevent orphans
        $category->children()->update(['parent_id' => null]);

        $category->delete();

        return redirect()->route('admin.categories.index')
                         ->with('success', __('admin.category_deleted'));
    }

    /**
     * Delete multiple categories in bulk.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        if (!$ids || count($ids) == 0) {
            return redirect()->route('admin.categories.index')
                             ->with('error', __('admin.no_categories_selected'));
        }

        // Check if any of the selected categories have products
        $hasProducts = Category::whereIn('id', $ids)
                               ->whereHas('products')
                               ->exists();
        if ($hasProducts) {
            return redirect()->route('admin.categories.index')
                             ->with('error', __('admin.bulk_delete_has_products'));
        }

        Category::whereIn('id', $ids)->delete();

        return redirect()->route('admin.categories.index')
                         ->with('success', __('admin.categories_deleted'));
    }

    /**
     * Move a category up or down to change its position.
     */
    public function move(Category $category, $direction)
    {
        if ($direction === 'up') {
            $category->decrement('position');
        } elseif ($direction === 'down') {
            $category->increment('position');
        }

        return redirect()->route('admin.categories.index')
                         ->with('success', __('admin.position_updated'));
    }
}