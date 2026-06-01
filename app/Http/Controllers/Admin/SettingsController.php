<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('setting_value', 'setting_key');
        $categories = Category::orderBy('name')->get();
        return view('admin.settings.index', compact('settings', 'categories'));
    }

    public function update(Request $request)
    {
        // Log all incoming request data for debugging
        Log::info('Settings update request received', [
            'all_data' => $request->all(),
            'files' => $request->files->keys(),
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type')
        ]);

        // Validation rules (removed shipping_cost, free_shipping_threshold, enable_github_login)
        $rules = [
            // General
            'site_name' => 'nullable|string|max:255',
            'site_email' => 'nullable|email|max:255',
            'site_phone' => 'nullable|string|max:50',
            'site_address' => 'nullable|string|max:1000',
            // Appearance
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_logo' => 'nullable|boolean',
            'favicon' => 'nullable|image|mimes:ico,png|max:512',
            'remove_favicon' => 'nullable|boolean',
            'primary_color' => 'nullable|string|max:20',
            'accent_color' => 'nullable|string|max:20',
            // Home
            'home_page_title' => 'nullable|string|max:255',
            'home_page_title_en' => 'nullable|string|max:255',
            'home_page_title_ar' => 'nullable|string|max:255',
            'home_hero_fallback_title' => 'nullable|string|max:255',
            'home_hero_fallback_title_en' => 'nullable|string|max:255',
            'home_hero_fallback_title_ar' => 'nullable|string|max:255',
            'home_hero_fallback_subtitle' => 'nullable|string|max:255',
            'home_hero_fallback_subtitle_en' => 'nullable|string|max:255',
            'home_hero_fallback_subtitle_ar' => 'nullable|string|max:255',
            'home_hero_fallback_description' => 'nullable|string|max:1000',
            'home_hero_fallback_description_en' => 'nullable|string|max:1000',
            'home_hero_fallback_description_ar' => 'nullable|string|max:1000',
            'home_hero_fallback_button_text' => 'nullable|string|max:100',
            'home_hero_fallback_button_text_en' => 'nullable|string|max:100',
            'home_hero_fallback_button_text_ar' => 'nullable|string|max:100',
            'home_hero_background' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_home_hero_background' => 'nullable|boolean',
            'feature_1_title' => 'nullable|string|max:100',
            'feature_1_title_en' => 'nullable|string|max:100',
            'feature_1_title_ar' => 'nullable|string|max:100',
            'feature_1_desc' => 'nullable|string|max:255',
            'feature_1_desc_en' => 'nullable|string|max:255',
            'feature_1_desc_ar' => 'nullable|string|max:255',
            'feature_2_title' => 'nullable|string|max:100',
            'feature_2_title_en' => 'nullable|string|max:100',
            'feature_2_title_ar' => 'nullable|string|max:100',
            'feature_2_desc' => 'nullable|string|max:255',
            'feature_2_desc_en' => 'nullable|string|max:255',
            'feature_2_desc_ar' => 'nullable|string|max:255',
            'feature_3_title' => 'nullable|string|max:100',
            'feature_3_title_en' => 'nullable|string|max:100',
            'feature_3_title_ar' => 'nullable|string|max:100',
            'feature_3_desc' => 'nullable|string|max:255',
            'feature_3_desc_en' => 'nullable|string|max:255',
            'feature_3_desc_ar' => 'nullable|string|max:255',
            'home_best_sellers_heading' => 'nullable|string|max:255',
            'home_best_sellers_heading_en' => 'nullable|string|max:255',
            'home_best_sellers_heading_ar' => 'nullable|string|max:255',
            'home_new_arrivals_heading' => 'nullable|string|max:255',
            'home_new_arrivals_heading_en' => 'nullable|string|max:255',
            'home_new_arrivals_heading_ar' => 'nullable|string|max:255',
            'home_view_more_text' => 'nullable|string|max:100',
            'home_view_more_text_en' => 'nullable|string|max:100',
            'home_view_more_text_ar' => 'nullable|string|max:100',
            'home_featured_category_id' => 'nullable|integer|min:1',
            'home_cta_title' => 'nullable|string|max:255',
            'home_cta_title_en' => 'nullable|string|max:255',
            'home_cta_title_ar' => 'nullable|string|max:255',
            'home_cta_text' => 'nullable|string|max:1000',
            'home_cta_text_en' => 'nullable|string|max:1000',
            'home_cta_text_ar' => 'nullable|string|max:1000',
            'home_cta_button_text' => 'nullable|string|max:50',
            'home_cta_button_text_en' => 'nullable|string|max:50',
            'home_cta_button_text_ar' => 'nullable|string|max:50',
            'home_cta_button_link' => 'nullable|string|max:255',
            // Shop
            'shop_page_title' => 'nullable|string|max:255',
            'shop_page_title_en' => 'nullable|string|max:255',
            'shop_page_title_ar' => 'nullable|string|max:255',
            'shop_search_placeholder' => 'nullable|string|max:100',
            'shop_search_placeholder_en' => 'nullable|string|max:100',
            'shop_search_placeholder_ar' => 'nullable|string|max:100',
            'shop_per_page' => 'nullable|integer|min:4|max:48',
            'shop_default_sort' => 'nullable|in:newest,bestseller,price_asc,price_desc',
            // Product
            'product_add_to_cart_button_text' => 'nullable|string|max:100',
            'product_add_to_cart_button_text_en' => 'nullable|string|max:100',
            'product_add_to_cart_button_text_ar' => 'nullable|string|max:100',
            'product_buy_now_button_text' => 'nullable|string|max:100',
            'product_buy_now_button_text_en' => 'nullable|string|max:100',
            'product_buy_now_button_text_ar' => 'nullable|string|max:100',
            'product_reviews_heading' => 'nullable|string|max:255',
            'product_reviews_heading_en' => 'nullable|string|max:255',
            'product_reviews_heading_ar' => 'nullable|string|max:255',
            'product_related_heading' => 'nullable|string|max:255',
            'product_related_heading_en' => 'nullable|string|max:255',
            'product_related_heading_ar' => 'nullable|string|max:255',
            // Contact
            'contact_page_title' => 'nullable|string|max:255',
            'contact_page_title_en' => 'nullable|string|max:255',
            'contact_page_title_ar' => 'nullable|string|max:255',
            'contact_heading' => 'nullable|string|max:255',
            'contact_heading_en' => 'nullable|string|max:255',
            'contact_heading_ar' => 'nullable|string|max:255',
            'contact_description' => 'nullable|string|max:1000',
            'contact_description_en' => 'nullable|string|max:1000',
            'contact_description_ar' => 'nullable|string|max:1000',
            'contact_submit_button_text' => 'nullable|string|max:100',
            'contact_submit_button_text_en' => 'nullable|string|max:100',
            'contact_submit_button_text_ar' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'contact_email2' => 'nullable|email|max:255',
            'contact_address' => 'nullable|string|max:1000',
            'contact_map_url' => 'nullable|string|max:500',
            'contact_faq' => 'nullable|string',
            // About
            'about_page_title' => 'nullable|string|max:255',
            'about_page_title_en' => 'nullable|string|max:255',
            'about_page_title_ar' => 'nullable|string|max:255',
            'about_hero_title' => 'nullable|string|max:255',
            'about_hero_title_en' => 'nullable|string|max:255',
            'about_hero_title_ar' => 'nullable|string|max:255',
            'about_hero_tagline' => 'nullable|string|max:255',
            'about_hero_tagline_en' => 'nullable|string|max:255',
            'about_hero_tagline_ar' => 'nullable|string|max:255',
            'about_hero_description' => 'nullable|string|max:1000',
            'about_hero_description_en' => 'nullable|string|max:1000',
            'about_hero_description_ar' => 'nullable|string|max:1000',
            'about_mission_title' => 'nullable|string|max:255',
            'about_mission_title_en' => 'nullable|string|max:255',
            'about_mission_title_ar' => 'nullable|string|max:255',
            'about_mission_text' => 'nullable|string|max:2000',
            'about_mission_text_en' => 'nullable|string|max:2000',
            'about_mission_text_ar' => 'nullable|string|max:2000',
            'about_vision_title' => 'nullable|string|max:255',
            'about_vision_title_en' => 'nullable|string|max:255',
            'about_vision_title_ar' => 'nullable|string|max:255',
            'about_vision_text' => 'nullable|string|max:2000',
            'about_vision_text_en' => 'nullable|string|max:2000',
            'about_vision_text_ar' => 'nullable|string|max:2000',
            'about_story_title' => 'nullable|string|max:255',
            'about_story_title_en' => 'nullable|string|max:255',
            'about_story_title_ar' => 'nullable|string|max:255',
            'about_story_subtitle' => 'nullable|string|max:255',
            'about_story_subtitle_en' => 'nullable|string|max:255',
            'about_story_subtitle_ar' => 'nullable|string|max:255',
            'about_story_text' => 'nullable|string|max:5000',
            'about_story_text_en' => 'nullable|string|max:5000',
            'about_story_text_ar' => 'nullable|string|max:5000',
            'about_values_heading' => 'nullable|string|max:255',
            'about_values_heading_en' => 'nullable|string|max:255',
            'about_values_heading_ar' => 'nullable|string|max:255',
            'about_values' => 'nullable|string',
            'about_features_heading' => 'nullable|string|max:255',
            'about_features_heading_en' => 'nullable|string|max:255',
            'about_features_heading_ar' => 'nullable|string|max:255',
            'about_features_subtitle' => 'nullable|string|max:255',
            'about_features_subtitle_en' => 'nullable|string|max:255',
            'about_features_subtitle_ar' => 'nullable|string|max:255',
            'about_features' => 'nullable|string',
            'about_team_heading' => 'nullable|string|max:255',
            'about_team_heading_en' => 'nullable|string|max:255',
            'about_team_heading_ar' => 'nullable|string|max:255',
            'about_team_subtitle' => 'nullable|string|max:255',
            'about_team_subtitle_en' => 'nullable|string|max:255',
            'about_team_subtitle_ar' => 'nullable|string|max:255',
            'about_cta_title' => 'nullable|string|max:255',
            'about_cta_title_en' => 'nullable|string|max:255',
            'about_cta_title_ar' => 'nullable|string|max:255',
            'about_cta_text' => 'nullable|string|max:1000',
            'about_cta_text_en' => 'nullable|string|max:1000',
            'about_cta_text_ar' => 'nullable|string|max:1000',
            'about_cta_button_text' => 'nullable|string|max:50',
            'about_cta_button_text_en' => 'nullable|string|max:50',
            'about_cta_button_text_ar' => 'nullable|string|max:50',
            'about_cta_button_link' => 'nullable|string|max:255',
            // Footer
            'footer_about_text' => 'nullable|string|max:1000',
            'footer_about_text_en' => 'nullable|string|max:1000',
            'footer_about_text_ar' => 'nullable|string|max:1000',
            'footer_copyright' => 'nullable|string|max:255',
            'footer_quick_links' => 'nullable|json',
            'footer_customer_service' => 'nullable|json',
            // Social
            'facebook_url' => 'nullable|string|max:255',
            'instagram_url' => 'nullable|string|max:255',
            'twitter_url' => 'nullable|string|max:255',
            'youtube_url' => 'nullable|string|max:255',
            // Cart
            'cart_page_title' => 'nullable|string|max:255',
            'cart_empty_message' => 'nullable|string|max:1000',
            // Checkout & Shipping (shipping_cost and free_shipping_threshold removed)
            'shipping_region_costs' => 'nullable|array',
            // Payment
            'payment_cod_enabled' => 'nullable|boolean',
            'payment_baridimob_enabled' => 'nullable|boolean',
            'payment_stripe_enabled' => 'nullable|boolean',
            'stripe_public_key' => 'nullable|string|max:255',
            'stripe_secret_key' => 'nullable|string|max:255',
            'baridimob_account' => 'nullable|string|max:100',
            'baridimob_account_name' => 'nullable|string|max:255',
            'baridimob_bank' => 'nullable|string|max:255',
            // Auth (enable_github_login removed)
            'enable_google_login' => 'nullable|boolean',
            'signup_brand_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_signup_brand_logo' => 'nullable|boolean',
            'signin_brand_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_signin_brand_logo' => 'nullable|boolean',
            // Legal
            'privacy_policy' => 'nullable|string|max:10000',
            'terms_of_service' => 'nullable|string|max:10000',
            'shipping_policy' => 'nullable|string|max:5000',
            'return_policy' => 'nullable|string|max:5000',

            // Add inside the $rules array (around line 200-250, where legal pages are)
            'terms_of_service_en' => 'nullable|string',
            'terms_of_service_ar' => 'nullable|string',
            'privacy_policy_en' => 'nullable|string',
            'privacy_policy_ar' => 'nullable|string',
            'shipping_policy_en' => 'nullable|string',
            'shipping_policy_ar' => 'nullable|string',
            'return_policy_en' => 'nullable|string',
            'return_policy_ar' => 'nullable|string',
        ];

        $validated = $request->validate($rules);

        // Handle file uploads (store on the `public` disk -> storage/app/public)
        $this->handleFileUpload($request, 'logo', 'settings');
        $this->handleFileUpload($request, 'favicon', 'settings');
        $this->handleFileUpload($request, 'home_hero_background', 'settings');
        $this->handleFileUpload($request, 'signup_brand_logo', 'settings');
        $this->handleFileUpload($request, 'signin_brand_logo', 'settings');

        // Remove files if requested
        if ($request->has('remove_logo')) {
            $this->removeSettingFile('logo');
        }
        if ($request->has('remove_favicon')) {
            $this->removeSettingFile('favicon');
        }
        if ($request->has('remove_home_hero_background')) {
            $this->removeSettingFile('home_hero_background');
        }
        if ($request->has('remove_signup_brand_logo')) {
            $this->removeSettingFile('signup_brand_logo');
        }
        if ($request->has('remove_signin_brand_logo')) {
            $this->removeSettingFile('signin_brand_logo');
        }

        // File keys to skip in the main loop
        $fileKeys = ['logo', 'favicon', 'home_hero_background', 'signup_brand_logo', 'signin_brand_logo'];

        // Save each setting from validated data (skip file fields and removal flags)
        foreach ($validated as $key => $value) {
            if (in_array($key, $fileKeys) || strpos($key, 'remove_') === 0 || $key === 'shipping_region_costs') {
                continue;
            }
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value]
            );
        }

        // Handle shipping_region_costs separately (save as JSON)
        if ($request->has('shipping_region_costs')) {
            $regionCosts = $request->input('shipping_region_costs', []);
            Setting::updateOrCreate(
                ['setting_key' => 'shipping_region_costs'],
                ['setting_value' => json_encode($regionCosts)]
            );
        }

        // Handle payment method toggles (after validation, using updateOrCreate)
        $paymentMethods = ['payment_cod_enabled', 'payment_baridimob_enabled', 'payment_stripe_enabled'];
        foreach ($paymentMethods as $method) {
            if ($request->has($method)) {
                Setting::updateOrCreate(
                    ['setting_key' => $method],
                    ['setting_value' => $request->input($method)]
                );
            }
        }

        // Clear settings cache
        cache()->forget('site_settings');

        return redirect()->route('admin.settings.index')->with('success', __('admin.settings_updated'));
    }

    private function handleFileUpload($request, $field, $path)
    {
        if ($request->hasFile($field)) {
            $file = $request->file($field);
            $filename = time() . '_' . $field . '.' . $file->getClientOriginalExtension();
            // Store in storage/app/public/{path}/filename
            $stored = $file->storeAs($path, $filename, 'public');
            $value = $stored; // e.g., settings/filename.jpg
            Setting::updateOrCreate(
                ['setting_key' => $field],
                ['setting_value' => $value]
            );
        }
    }

    private function removeSettingFile($key)
    {
        $old = Setting::where('setting_key', $key)->value('setting_value');
        if ($old && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }
        Setting::where('setting_key', $key)->delete();
    }
}