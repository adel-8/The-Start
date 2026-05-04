<?php

// app/Http/Controllers/Admin/BannerController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $query = Banner::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('device_type')) {
            $query->where('device_type', $request->device_type);
        }

        $banners = $query->orderBy('position')->orderBy('id')->paginate(20);
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'nullable|string|max:255',
            'image'       => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'link'        => 'nullable|url|max:255',
            'position'    => 'nullable|integer|min:0',
            'status'      => 'nullable|boolean',
            'starts_at'   => 'nullable|date',
            'ends_at'     => 'nullable|date|after_or_equal:starts_at',
            'device_type' => 'nullable|in:all,mobile,desktop',
        ]);

        $path = $request->file('image')->store('banners', 'public');
        $data = $request->only(['title', 'link', 'position', 'status', 'starts_at', 'ends_at', 'device_type']);
        $data['image_url'] = '/storage/' . $path;

        Banner::create($data);

        return redirect()->route('admin.banners.index')->with('success', __('admin.banner_created'));
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'title'       => 'nullable|string|max:255',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'link'        => 'nullable|url|max:255',
            'position'    => 'nullable|integer|min:0',
            'status'      => 'nullable|boolean',
            'starts_at'   => 'nullable|date',
            'ends_at'     => 'nullable|date|after_or_equal:starts_at',
            'device_type' => 'nullable|in:all,mobile,desktop',
        ]);

        $data = $request->only(['title', 'link', 'position', 'status', 'starts_at', 'ends_at', 'device_type']);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($banner->image_url) {
                $oldPath = str_replace('/storage/', '', $banner->image_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('banners', 'public');
            $data['image_url'] = '/storage/' . $path;
        }

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('success', __('admin.banner_updated'));
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image_url) {
            $oldPath = str_replace('/storage/', '', $banner->image_url);
            Storage::disk('public')->delete($oldPath);
        }
        $banner->delete();
        return redirect()->route('admin.banners.index')->with('success', __('admin.banner_deleted'));
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        if ($ids) {
            Banner::whereIn('id', $ids)->delete();
            return redirect()->route('admin.banners.index')->with('success', __('admin.banners_deleted'));
        }
        return redirect()->route('admin.banners.index')->with('error', __('admin.no_banners_selected'));
    }

    public function move(Banner $banner, $direction)
    {
        if ($direction === 'up') {
            $banner->decrement('position');
        } elseif ($direction === 'down') {
            $banner->increment('position');
        }
        return redirect()->route('admin.banners.index')->with('success', __('admin.position_updated'));
    }

    // Optional: track clicks from frontend
    public function trackClick($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->increment('clicks');
        if ($banner->link) {
            return redirect()->away($banner->link);
        }
        return redirect()->back();
    }
}