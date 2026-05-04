<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Banner;
use App\Models\Setting;
use App\Models\Category;

class HomeController extends Controller
{
    public function home()
    {
        // Fetch all settings once
        $settings = Setting::pluck('setting_value', 'setting_key');

        // Best Sellers
        $bestsellers = Product::where('status', 'active')
            ->where('bestseller', 1)
            ->take(8)
            ->get();

        // New Arrivals
        $newArrivals = Product::where('status', 'active')
            ->where('is_new', 1)
            ->take(8)
            ->get();

        // Featured Category (from settings)
        $featuredCategoryId = $settings['home_featured_category_id'] ?? null;
        $featuredCategoryName = $settings['home_featured_category_name'] ?? 'Accessories';

        $categoryProducts = collect();
        $category = null;

        if ($featuredCategoryId) {
            $category = Category::find($featuredCategoryId);
        } else {
            // Try by name (case‑insensitive)
            $category = Category::whereRaw('LOWER(name) = ?', [strtolower($featuredCategoryName)])->first();
        }

        if ($category) {
            $categoryProducts = $category->products()->where('status', 'active')->take(8)->get();
            $featuredCategoryName = $category->name; // use the actual category name for the title
        }

        // CTA settings with fallbacks
        $ctaTitle = $settings['home_cta_title'] ?? 'Discover our full collection';
        $ctaText = $settings['home_cta_text'] ?? 'Explore thousands of products curated just for you.';
        $ctaButtonText = $settings['home_cta_button_text'] ?? 'Shop Now';
        $ctaButtonLink = $settings['home_cta_button_link'] ?? route('Shop');

        // Hero banners (collection)
        $banners = Banner::latest()->take(3)->get();

        // Pass all variables to view
        return view('home', compact(
            'bestsellers', 'newArrivals', 'categoryProducts', 'banners', 'settings',
            'featuredCategoryName', 'ctaTitle', 'ctaText', 'ctaButtonText', 'ctaButtonLink'
        ));
    }
}