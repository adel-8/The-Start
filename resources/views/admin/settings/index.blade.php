@extends('admin.layouts.app')

@section('title', __('admin.site_settings'))

@push('styles')
<style>
    /* Additional styles for the settings page – will blend with your admin CSS */
    .settings-section {
        margin-bottom: 2rem;
    }

    .settings-card {
        background: var(--color-surface);
        border-radius: 1rem;
        border: 1px solid var(--color-border);
        overflow: hidden;
        transition: box-shadow 0.2s;
    }

    .settings-card:hover {
        box-shadow: var(--shadow-md);
    }

    .settings-card-header {
        padding: 1.25rem 1.5rem;
        background: var(--color-surface);
        border-bottom: 1px solid var(--color-border);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        user-select: none;
    }

    .settings-card-header h2 {
        font-size: 1.2rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: var(--color-text);
    }

    .settings-card-header i {
        color: var(--color-primary);
        font-size: 1.2rem;
    }

    .settings-card-header .toggle-icon {
        transition: transform 0.2s;
        color: var(--color-muted);
    }

    .settings-card[open] .settings-card-header .toggle-icon {
        transform: rotate(180deg);
    }

    .settings-card-body {
        padding: 1.5rem;
    }

    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin-bottom: 1rem;
    }

    .form-group {
        flex: 1;
        min-width: 200px;
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--color-text);
        font-size: 0.85rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.6rem 0.8rem;
        border: 1px solid var(--color-border);
        border-radius: 0.5rem;
        background: var(--color-surface);
        font-family: inherit;
        font-size: 0.9rem;
        transition: 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 2px rgba(100, 95, 125, 0.1);
    }

    .form-group input[type="color"] {
        height: 40px;
        padding: 0.2rem;
    }

    .form-group input[type="file"] {
        padding: 0.4rem 0.6rem;
    }

    .form-group small {
        display: block;
        font-size: 0.7rem;
        color: var(--color-muted);
        margin-top: 0.25rem;
    }

    .current-image {
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .current-image img {
        max-width: 100px;
        max-height: 60px;
        border-radius: 0.5rem;
        border: 1px solid var(--color-border);
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: normal;
        font-size: 0.85rem;
    }

    .toggle-group {
        display: flex;
        gap: 1rem;
        margin-top: 0.5rem;
    }

    .toggle-group label {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        font-weight: normal;
        cursor: pointer;
    }

    .toggle-group input[type="radio"],
    .toggle-group input[type="checkbox"] {
        width: auto;
        margin-right: 0.3rem;
    }

    .btn-validate-json {
        margin-top: 0.5rem;
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        padding: 0.25rem 0.6rem;
        font-size: 0.7rem;
        cursor: pointer;
        border-radius: 0.5rem;
        transition: 0.2s;
    }

    .btn-validate-json:hover {
        background: var(--color-primary);
        color: white;
        border-color: var(--color-primary);
    }

    .shipping-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .shipping-table th,
    .shipping-table td {
        padding: 0.5rem;
        border-bottom: 1px solid var(--color-border);
        text-align: left;
    }

    .shipping-table th {
        font-weight: 600;
        background: rgba(0,0,0,0.02);
    }

    .shipping-table input {
        width: 100px;
    }

    .form-actions {
        margin-top: 2rem;
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
    }

    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 0;
        }
    }
</style>
@endpush

@section('content')
<div class="settings-container">
    <h1>{{ __('admin.site_settings') }}</h1>
    <p class="text-muted">{{ __('admin.settings_help') }}</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
        @csrf
        @method('PUT')

        <!-- ========== GENERAL ========== -->
        <details class="settings-section settings-card" open>
            <summary class="settings-card-header">
                <h2><i class="fas fa-globe"></i> {{ __('admin.general_settings') }}</h2>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </summary>
            <div class="settings-card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="site_name">{{ __('admin.site_name') }}</label>
                        <input type="text" name="site_name" id="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="site_email">{{ __('admin.site_email') }}</label>
                        <input type="email" name="site_email" id="site_email" value="{{ old('site_email', $settings['site_email'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="site_phone">{{ __('admin.site_phone') }}</label>
                        <input type="text" name="site_phone" id="site_phone" value="{{ old('site_phone', $settings['site_phone'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="site_address">{{ __('admin.site_address') }}</label>
                        <textarea name="site_address" id="site_address" rows="2">{{ old('site_address', $settings['site_address'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </details>

        <!-- ========== APPEARANCE ========== -->
        <details class="settings-section settings-card" open>
            <summary class="settings-card-header">
                <h2><i class="fas fa-palette"></i> {{ __('admin.appearance') }}</h2>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </summary>
            <div class="settings-card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="logo">{{ __('admin.logo') }}</label>
                        <input type="file" name="logo" id="logo" accept="image/*">
                        @if(isset($settings['logo']) && $settings['logo'])
                            <div class="current-image" data-image-type="logo">
                                <img src="{{ asset($settings['logo']) }}" class="image-preview-thumb" alt="Logo">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="remove_logo" value="1"> {{ __('admin.remove_logo') }}
                                </label>
                            </div>
                        @endif
                        <small>{{ __('admin.logo_help') }}</small>
                    </div>
                    <div class="form-group">
                        <label for="favicon">{{ __('admin.favicon') }}</label>
                        <input type="file" name="favicon" id="favicon" accept="image/x-icon,image/png">
                        @if(isset($settings['favicon']) && $settings['favicon'])
                            <div class="current-image" data-image-type="favicon">
                                <img src="{{ asset($settings['favicon']) }}" class="image-preview-thumb" alt="Favicon">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="remove_favicon" value="1"> {{ __('admin.remove_favicon') }}
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="primary_color">{{ __('admin.primary_color') }}</label>
                        <input type="color" name="primary_color" id="primary_color" value="{{ old('primary_color', $settings['primary_color'] ?? '#645F7D') }}">
                    </div>
                    <div class="form-group">
                        <label for="accent_color">{{ __('admin.accent_color') }}</label>
                        <input type="color" name="accent_color" id="accent_color" value="{{ old('accent_color', $settings['accent_color'] ?? '#E0B854') }}">
                    </div>
                </div>
            </div>
        </details>

        <!-- ========== HOME PAGE ========== -->
        <details class="settings-section settings-card" open>
            <summary class="settings-card-header">
                <h2><i class="fas fa-home"></i> {{ __('admin.home_page') }}</h2>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </summary>
            <div class="settings-card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="home_page_title_en">{{ __('admin.home_page_title') }} (English)</label>
                        <input type="text" name="home_page_title_en" id="home_page_title_en" value="{{ old('home_page_title_en', $settings['home_page_title_en'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="home_page_title_ar">{{ __('admin.home_page_title') }} (العربية)</label>
                        <input type="text" name="home_page_title_ar" id="home_page_title_ar" value="{{ old('home_page_title_ar', $settings['home_page_title_ar'] ?? '') }}">
                    </div>
                </div>

                <h4>{{ __('admin.hero_section') }}</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="home_hero_fallback_title_en">{{ __('admin.hero_fallback_title') }} (English)</label>
                        <input type="text" name="home_hero_fallback_title_en" id="home_hero_fallback_title_en" value="{{ old('home_hero_fallback_title_en', $settings['home_hero_fallback_title_en'] ?? 'Effortless style') }}">
                    </div>
                    <div class="form-group">
                        <label for="home_hero_fallback_title_ar">{{ __('admin.hero_fallback_title') }} (العربية)</label>
                        <input type="text" name="home_hero_fallback_title_ar" id="home_hero_fallback_title_ar" value="{{ old('home_hero_fallback_title_ar', $settings['home_hero_fallback_title_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="home_hero_fallback_subtitle_en">{{ __('admin.hero_fallback_subtitle') }} (English)</label>
                        <input type="text" name="home_hero_fallback_subtitle_en" id="home_hero_fallback_subtitle_en" value="{{ old('home_hero_fallback_subtitle_en', $settings['home_hero_fallback_subtitle_en'] ?? 'uncompromised comfort') }}">
                    </div>
                    <div class="form-group">
                        <label for="home_hero_fallback_subtitle_ar">{{ __('admin.hero_fallback_subtitle') }} (العربية)</label>
                        <input type="text" name="home_hero_fallback_subtitle_ar" id="home_hero_fallback_subtitle_ar" value="{{ old('home_hero_fallback_subtitle_ar', $settings['home_hero_fallback_subtitle_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="home_hero_fallback_description_en">{{ __('admin.hero_fallback_description') }} (English)</label>
                        <textarea name="home_hero_fallback_description_en" id="home_hero_fallback_description_en" rows="2">{{ old('home_hero_fallback_description_en', $settings['home_hero_fallback_description_en'] ?? 'Discover our new collection — crafted with natural fibers and a modern edge.') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="home_hero_fallback_description_ar">{{ __('admin.hero_fallback_description') }} (العربية)</label>
                        <textarea name="home_hero_fallback_description_ar" id="home_hero_fallback_description_ar" rows="2">{{ old('home_hero_fallback_description_ar', $settings['home_hero_fallback_description_ar'] ?? '') }}</textarea>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="home_hero_fallback_button_text_en">{{ __('admin.hero_fallback_button_text') }} (English)</label>
                        <input type="text" name="home_hero_fallback_button_text_en" id="home_hero_fallback_button_text_en" value="{{ old('home_hero_fallback_button_text_en', $settings['home_hero_fallback_button_text_en'] ?? 'Shop Now') }}">
                    </div>
                    <div class="form-group">
                        <label for="home_hero_fallback_button_text_ar">{{ __('admin.hero_fallback_button_text') }} (العربية)</label>
                        <input type="text" name="home_hero_fallback_button_text_ar" id="home_hero_fallback_button_text_ar" value="{{ old('home_hero_fallback_button_text_ar', $settings['home_hero_fallback_button_text_ar'] ?? '') }}">
                    </div>
                </div>

                <h4>{{ __('admin.features_section') }}</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>{{ __('admin.feature_1_title') }} (English)</label>
                        <input type="text" name="feature_1_title_en" value="{{ old('feature_1_title_en', $settings['feature_1_title_en'] ?? 'Free Shipping') }}">
                    </div>
                    <div class="form-group">
                        <label>{{ __('admin.feature_1_title') }} (العربية)</label>
                        <input type="text" name="feature_1_title_ar" value="{{ old('feature_1_title_ar', $settings['feature_1_title_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>{{ __('admin.feature_1_desc') }} (English)</label>
                        <input type="text" name="feature_1_desc_en" value="{{ old('feature_1_desc_en', $settings['feature_1_desc_en'] ?? __('messages.free_shipping_desc')) }}">
                    </div>
                    <div class="form-group">
                        <label>{{ __('admin.feature_1_desc') }} (العربية)</label>
                        <input type="text" name="feature_1_desc_ar" value="{{ old('feature_1_desc_ar', $settings['feature_1_desc_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>{{ __('admin.feature_2_title') }} (English)</label>
                        <input type="text" name="feature_2_title_en" value="{{ old('feature_2_title_en', $settings['feature_2_title_en'] ?? 'Secure Payment') }}">
                    </div>
                    <div class="form-group">
                        <label>{{ __('admin.feature_2_title') }} (العربية)</label>
                        <input type="text" name="feature_2_title_ar" value="{{ old('feature_2_title_ar', $settings['feature_2_title_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>{{ __('admin.feature_2_desc') }} (English)</label>
                        <input type="text" name="feature_2_desc_en" value="{{ old('feature_2_desc_en', $settings['feature_2_desc_en'] ?? '100% safe transactions') }}">
                    </div>
                    <div class="form-group">
                        <label>{{ __('admin.feature_2_desc') }} (العربية)</label>
                        <input type="text" name="feature_2_desc_ar" value="{{ old('feature_2_desc_ar', $settings['feature_2_desc_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>{{ __('admin.feature_3_title') }} (English)</label>
                        <input type="text" name="feature_3_title_en" value="{{ old('feature_3_title_en', $settings['feature_3_title_en'] ?? 'Fast Delivery') }}">
                    </div>
                    <div class="form-group">
                        <label>{{ __('admin.feature_3_title') }} (العربية)</label>
                        <input type="text" name="feature_3_title_ar" value="{{ old('feature_3_title_ar', $settings['feature_3_title_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>{{ __('admin.feature_3_desc') }} (English)</label>
                        <input type="text" name="feature_3_desc_en" value="{{ old('feature_3_desc_en', $settings['feature_3_desc_en'] ?? '2-3 days') }}">
                    </div>
                    <div class="form-group">
                        <label>{{ __('admin.feature_3_desc') }} (العربية)</label>
                        <input type="text" name="feature_3_desc_ar" value="{{ old('feature_3_desc_ar', $settings['feature_3_desc_ar'] ?? '') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="home_best_sellers_heading_en">{{ __('admin.best_sellers_heading') }} (English)</label>
                        <input type="text" name="home_best_sellers_heading_en" id="home_best_sellers_heading_en" value="{{ old('home_best_sellers_heading_en', $settings['home_best_sellers_heading_en'] ?? 'Best Sellers') }}">
                    </div>
                    <div class="form-group">
                        <label for="home_best_sellers_heading_ar">{{ __('admin.best_sellers_heading') }} (العربية)</label>
                        <input type="text" name="home_best_sellers_heading_ar" id="home_best_sellers_heading_ar" value="{{ old('home_best_sellers_heading_ar', $settings['home_best_sellers_heading_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="home_new_arrivals_heading_en">{{ __('admin.new_arrivals_heading') }} (English)</label>
                        <input type="text" name="home_new_arrivals_heading_en" id="home_new_arrivals_heading_en" value="{{ old('home_new_arrivals_heading_en', $settings['home_new_arrivals_heading_en'] ?? 'New Arrivals') }}">
                    </div>
                    <div class="form-group">
                        <label for="home_new_arrivals_heading_ar">{{ __('admin.new_arrivals_heading') }} (العربية)</label>
                        <input type="text" name="home_new_arrivals_heading_ar" id="home_new_arrivals_heading_ar" value="{{ old('home_new_arrivals_heading_ar', $settings['home_new_arrivals_heading_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="home_view_more_text_en">{{ __('admin.view_more_text') }} (English)</label>
                        <input type="text" name="home_view_more_text_en" id="home_view_more_text_en" value="{{ old('home_view_more_text_en', $settings['home_view_more_text_en'] ?? 'View more') }}">
                    </div>
                    <div class="form-group">
                        <label for="home_view_more_text_ar">{{ __('admin.view_more_text') }} (العربية)</label>
                        <input type="text" name="home_view_more_text_ar" id="home_view_more_text_ar" value="{{ old('home_view_more_text_ar', $settings['home_view_more_text_ar'] ?? '') }}">
                    </div>
                </div>

                <h4>{{ __('admin.call_to_action') }}</h4>
                
                <!-- CTA Title - Bilingual -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="home_cta_title_en">{{ __('admin.cta_title') }} (English)</label>
                        <input type="text" name="home_cta_title_en" id="home_cta_title_en" value="{{ old('home_cta_title_en', $settings['home_cta_title_en'] ?? 'Discover our full collection') }}">
                    </div>
                    <div class="form-group">
                        <label for="home_cta_title_ar">{{ __('admin.cta_title') }} (العربية)</label>
                        <input type="text" name="home_cta_title_ar" id="home_cta_title_ar" value="{{ old('home_cta_title_ar', $settings['home_cta_title_ar'] ?? '') }}">
                    </div>
                </div>
                
                <!-- CTA Text - Bilingual -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="home_cta_text_en">{{ __('admin.cta_text') }} (English)</label>
                        <textarea name="home_cta_text_en" id="home_cta_text_en" rows="2">{{ old('home_cta_text_en', $settings['home_cta_text_en'] ?? 'Explore thousands of products curated just for you.') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="home_cta_text_ar">{{ __('admin.cta_text') }} (العربية)</label>
                        <textarea name="home_cta_text_ar" id="home_cta_text_ar" rows="2">{{ old('home_cta_text_ar', $settings['home_cta_text_ar'] ?? '') }}</textarea>
                    </div>
                </div>
                
                <!-- CTA Button Text - Bilingual -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="home_cta_button_text_en">{{ __('admin.cta_button_text') }} (English)</label>
                        <input type="text" name="home_cta_button_text_en" id="home_cta_button_text_en" value="{{ old('home_cta_button_text_en', $settings['home_cta_button_text_en'] ?? 'Shop Now') }}">
                    </div>
                    <div class="form-group">
                        <label for="home_cta_button_text_ar">{{ __('admin.cta_button_text') }} (العربية)</label>
                        <input type="text" name="home_cta_button_text_ar" id="home_cta_button_text_ar" value="{{ old('home_cta_button_text_ar', $settings['home_cta_button_text_ar'] ?? '') }}">
                    </div>
                </div>
                
                <!-- CTA Button Link - Shared -->
                <div class="form-group">
                    <label for="home_cta_button_link">{{ __('admin.cta_button_link') }}</label>
                    <input type="text" name="home_cta_button_link" id="home_cta_button_link" value="{{ old('home_cta_button_link', $settings['home_cta_button_link'] ?? '/shop') }}">
                </div>

                <div class="form-group">
                    <label for="home_featured_category_id">{{ __('admin.featured_category') }}</label>
                    <select name="home_featured_category_id" id="home_featured_category_id">
                        <option value="">{{ __('admin.select_category') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ ($settings['home_featured_category_id'] ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="home_hero_background">{{ __('admin.hero_background') }}</label>
                    <input type="file" name="home_hero_background" id="home_hero_background" accept="image/*">
                    @if(isset($settings['home_hero_background']) && $settings['home_hero_background'])
                        <div class="current-image" data-image-type="hero_bg">
                            <img src="{{ asset($settings['home_hero_background']) }}" class="image-preview-thumb" alt="Hero Background">
                            <label class="checkbox-label">
                                <input type="checkbox" name="remove_home_hero_background" value="1"> {{ __('admin.remove') }}
                            </label>
                        </div>
                    @endif
                </div>
            </div>
        </details>

        <!-- ========== SHOP PAGE ========== -->
        <details class="settings-section settings-card" open>
            <summary class="settings-card-header">
                <h2><i class="fas fa-store"></i> {{ __('admin.shop_page') }}</h2>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </summary>
            <div class="settings-card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="shop_page_title_en">{{ __('admin.shop_page_title') }} (English)</label>
                        <input type="text" name="shop_page_title_en" id="shop_page_title_en" value="{{ old('shop_page_title_en', $settings['shop_page_title_en'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="shop_page_title_ar">{{ __('admin.shop_page_title') }} (العربية)</label>
                        <input type="text" name="shop_page_title_ar" id="shop_page_title_ar" value="{{ old('shop_page_title_ar', $settings['shop_page_title_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="shop_search_placeholder_en">{{ __('admin.search_placeholder') }} (English)</label>
                        <input type="text" name="shop_search_placeholder_en" id="shop_search_placeholder_en" value="{{ old('shop_search_placeholder_en', $settings['shop_search_placeholder_en'] ?? 'Search products...') }}">
                    </div>
                    <div class="form-group">
                        <label for="shop_search_placeholder_ar">{{ __('admin.search_placeholder') }} (العربية)</label>
                        <input type="text" name="shop_search_placeholder_ar" id="shop_search_placeholder_ar" value="{{ old('shop_search_placeholder_ar', $settings['shop_search_placeholder_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="shop_per_page">{{ __('admin.products_per_page') }}</label>
                        <input type="number" name="shop_per_page" id="shop_per_page" value="{{ old('shop_per_page', $settings['shop_per_page'] ?? 12) }}" min="4" max="48" step="4">
                    </div>
                    <div class="form-group">
                        <label for="shop_default_sort">{{ __('admin.default_sort') }}</label>
                        <select name="shop_default_sort" id="shop_default_sort">
                            <option value="newest" {{ ($settings['shop_default_sort'] ?? 'newest') == 'newest' ? 'selected' : '' }}>{{ __('admin.newest') }}</option>
                            <option value="bestseller" {{ ($settings['shop_default_sort'] ?? 'newest') == 'bestseller' ? 'selected' : '' }}>{{ __('admin.best_sellers') }}</option>
                            <option value="price_asc" {{ ($settings['shop_default_sort'] ?? 'newest') == 'price_asc' ? 'selected' : '' }}>{{ __('admin.price_low_to_high') }}</option>
                            <option value="price_desc" {{ ($settings['shop_default_sort'] ?? 'newest') == 'price_desc' ? 'selected' : '' }}>{{ __('admin.price_high_to_low') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </details>

        <!-- ========== PRODUCT PAGE ========== -->
        <details class="settings-section settings-card" open>
            <summary class="settings-card-header">
                <h2><i class="fas fa-box"></i> {{ __('admin.product_page') }}</h2>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </summary>
            <div class="settings-card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="product_add_to_cart_button_text_en">{{ __('admin.add_to_cart_button_text') }} (English)</label>
                        <input type="text" name="product_add_to_cart_button_text_en" id="product_add_to_cart_button_text_en" value="{{ old('product_add_to_cart_button_text_en', $settings['product_add_to_cart_button_text_en'] ?? 'Add to cart') }}">
                    </div>
                    <div class="form-group">
                        <label for="product_add_to_cart_button_text_ar">{{ __('admin.add_to_cart_button_text') }} (العربية)</label>
                        <input type="text" name="product_add_to_cart_button_text_ar" id="product_add_to_cart_button_text_ar" value="{{ old('product_add_to_cart_button_text_ar', $settings['product_add_to_cart_button_text_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="product_buy_now_button_text_en">{{ __('admin.buy_now_button_text') }} (English)</label>
                        <input type="text" name="product_buy_now_button_text_en" id="product_buy_now_button_text_en" value="{{ old('product_buy_now_button_text_en', $settings['product_buy_now_button_text_en'] ?? 'Buy now') }}">
                    </div>
                    <div class="form-group">
                        <label for="product_buy_now_button_text_ar">{{ __('admin.buy_now_button_text') }} (العربية)</label>
                        <input type="text" name="product_buy_now_button_text_ar" id="product_buy_now_button_text_ar" value="{{ old('product_buy_now_button_text_ar', $settings['product_buy_now_button_text_ar'] ?? '') }}">
                    </div>
                </div>
                
                <!-- Reviews Heading - Bilingual -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="product_reviews_heading_en">{{ __('admin.reviews_heading') }} (English)</label>
                        <input type="text" name="product_reviews_heading_en" id="product_reviews_heading_en" value="{{ old('product_reviews_heading_en', $settings['product_reviews_heading_en'] ?? 'Customer Reviews') }}">
                    </div>
                    <div class="form-group">
                        <label for="product_reviews_heading_ar">{{ __('admin.reviews_heading') }} (العربية)</label>
                        <input type="text" name="product_reviews_heading_ar" id="product_reviews_heading_ar" value="{{ old('product_reviews_heading_ar', $settings['product_reviews_heading_ar'] ?? '') }}">
                    </div>
                </div>
                
                <!-- Related Heading - Bilingual -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="product_related_heading_en">{{ __('admin.related_heading') }} (English)</label>
                        <input type="text" name="product_related_heading_en" id="product_related_heading_en" value="{{ old('product_related_heading_en', $settings['product_related_heading_en'] ?? 'You might also like') }}">
                    </div>
                    <div class="form-group">
                        <label for="product_related_heading_ar">{{ __('admin.related_heading') }} (العربية)</label>
                        <input type="text" name="product_related_heading_ar" id="product_related_heading_ar" value="{{ old('product_related_heading_ar', $settings['product_related_heading_ar'] ?? '') }}">
                    </div>
                </div>
            </div>
        </details>

        <!-- ========== CONTACT PAGE ========== -->
        <details class="settings-section settings-card" open>
            <summary class="settings-card-header">
                <h2><i class="fas fa-envelope"></i> {{ __('admin.contact_page') }}</h2>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </summary>
            <div class="settings-card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_page_title_en">{{ __('admin.page_title') }} (English)</label>
                        <input type="text" name="contact_page_title_en" id="contact_page_title_en" value="{{ old('contact_page_title_en', $settings['contact_page_title_en'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="contact_page_title_ar">{{ __('admin.page_title') }} (العربية)</label>
                        <input type="text" name="contact_page_title_ar" id="contact_page_title_ar" value="{{ old('contact_page_title_ar', $settings['contact_page_title_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="contact_heading_en">{{ __('admin.heading') }} (English)</label>
                    <input type="text" name="contact_heading_en" id="contact_heading_en" value="{{ old('contact_heading_en', $settings['contact_heading_en'] ?? 'Contact Us') }}">
                </div>
                <div class="form-group">
                    <label for="contact_heading_ar">{{ __('admin.heading') }} (العربية)</label>
                    <input type="text" name="contact_heading_ar" id="contact_heading_ar" value="{{ old('contact_heading_ar', $settings['contact_heading_ar'] ?? '') }}">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_description_en">{{ __('admin.description') }} (English)</label>
                        <textarea name="contact_description_en" id="contact_description_en" rows="2">{{ old('contact_description_en', $settings['contact_description_en'] ?? 'We’d love to hear from you.') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="contact_description_ar">{{ __('admin.description') }} (العربية)</label>
                        <textarea name="contact_description_ar" id="contact_description_ar" rows="2">{{ old('contact_description_ar', $settings['contact_description_ar'] ?? '') }}</textarea>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_submit_button_text_en">{{ __('admin.submit_button_text') }} (English)</label>
                        <input type="text" name="contact_submit_button_text_en" id="contact_submit_button_text_en" value="{{ old('contact_submit_button_text_en', $settings['contact_submit_button_text_en'] ?? 'Send Message') }}">
                    </div>
                    <div class="form-group">
                        <label for="contact_submit_button_text_ar">{{ __('admin.submit_button_text') }} (العربية)</label>
                        <input type="text" name="contact_submit_button_text_ar" id="contact_submit_button_text_ar" value="{{ old('contact_submit_button_text_ar', $settings['contact_submit_button_text_ar'] ?? '') }}">
                    </div>
                </div>

                <h4>{{ __('admin.contact_details') }}</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_phone">{{ __('admin.phone') }}</label>
                        <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="contact_email">{{ __('admin.email') }}</label>
                        <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $settings['contact_email'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="contact_email2">{{ __('admin.email2') }}</label>
                        <input type="email" name="contact_email2" id="contact_email2" value="{{ old('contact_email2', $settings['contact_email2'] ?? '') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="contact_address">{{ __('admin.address') }}</label>
                    <textarea name="contact_address" id="contact_address" rows="2">{{ old('contact_address', $settings['contact_address'] ?? '') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="contact_map_url">{{ __('admin.map_url') }}</label>
                    <input type="url" name="contact_map_url" id="contact_map_url" value="{{ old('contact_map_url', $settings['contact_map_url'] ?? '') }}">
                </div>

                <div class="form-group">
                    <label for="contact_faq">{{ __('admin.faq') }} </label>
                    <textarea name="contact_faq" id="contact_faq" rows="6" class="json-textarea">{{ old('contact_faq', $settings['contact_faq'] ?? '') }}</textarea>
                    <small>{{ __('admin.contact_faq_help') }}</small>
                    <button type="button" class="btn-validate-json" data-target="contact_faq">{{ __('admin.validate_json') }}</button>
                </div>
            </div>
        </details>

        <!-- ========== ABOUT PAGE ========== -->
        <details class="settings-section settings-card" open>
            <summary class="settings-card-header">
                <h2><i class="fas fa-info-circle"></i> {{ __('admin.about_page') }}</h2>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </summary>
            <div class="settings-card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="about_page_title_en">{{ __('admin.page_title') }} (English)</label>
                        <input type="text" name="about_page_title_en" id="about_page_title_en" value="{{ old('about_page_title_en', $settings['about_page_title_en'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="about_page_title_ar">{{ __('admin.page_title') }} (العربية)</label>
                        <input type="text" name="about_page_title_ar" id="about_page_title_ar" value="{{ old('about_page_title_ar', $settings['about_page_title_ar'] ?? '') }}">
                    </div>
                </div>
                
                <!-- Hero Title - Bilingual -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="about_hero_title_en">{{ __('admin.hero_title') }} (English)</label>
                        <input type="text" name="about_hero_title_en" id="about_hero_title_en" value="{{ old('about_hero_title_en', $settings['about_hero_title_en'] ?? 'The Start') }}">
                    </div>
                    <div class="form-group">
                        <label for="about_hero_title_ar">{{ __('admin.hero_title') }} (العربية)</label>
                        <input type="text" name="about_hero_title_ar" id="about_hero_title_ar" value="{{ old('about_hero_title_ar', $settings['about_hero_title_ar'] ?? '') }}">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="about_hero_tagline_en">{{ __('admin.hero_tagline') }} (English)</label>
                        <input type="text" name="about_hero_tagline_en" id="about_hero_tagline_en" value="{{ old('about_hero_tagline_en', $settings['about_hero_tagline_en'] ?? 'Your Trusted E-Commerce Partner in Algeria') }}">
                    </div>
                    <div class="form-group">
                        <label for="about_hero_tagline_ar">{{ __('admin.hero_tagline') }} (العربية)</label>
                        <input type="text" name="about_hero_tagline_ar" id="about_hero_tagline_ar" value="{{ old('about_hero_tagline_ar', $settings['about_hero_tagline_ar'] ?? '') }}">
                    </div>
                </div>
                
                <!-- Hero Description - Bilingual -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="about_hero_description_en">{{ __('admin.hero_description') }} (English)</label>
                        <textarea name="about_hero_description_en" id="about_hero_description_en" rows="2">{{ old('about_hero_description_en', $settings['about_hero_description_en'] ?? 'We\'re building a simpler, more trustworthy way to shop online.') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="about_hero_description_ar">{{ __('admin.hero_description') }} (العربية)</label>
                        <textarea name="about_hero_description_ar" id="about_hero_description_ar" rows="2">{{ old('about_hero_description_ar', $settings['about_hero_description_ar'] ?? '') }}</textarea>
                    </div>
                </div>

                <!-- Mission - Bilingual -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="about_mission_title_en">{{ __('admin.mission_title') }} (English)</label>
                        <input type="text" name="about_mission_title_en" id="about_mission_title_en" value="{{ old('about_mission_title_en', $settings['about_mission_title_en'] ?? 'Our Mission') }}">
                    </div>
                    <div class="form-group">
                        <label for="about_mission_title_ar">{{ __('admin.mission_title') }} (العربية)</label>
                        <input type="text" name="about_mission_title_ar" id="about_mission_title_ar" value="{{ old('about_mission_title_ar', $settings['about_mission_title_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="about_mission_text_en">{{ __('admin.mission_text') }} (English)</label>
                        <textarea name="about_mission_text_en" id="about_mission_text_en" rows="3">{{ old('about_mission_text_en', $settings['about_mission_text_en'] ?? 'To provide a seamless, secure, and trustworthy e-commerce experience...') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="about_mission_text_ar">{{ __('admin.mission_text') }} (العربية)</label>
                        <textarea name="about_mission_text_ar" id="about_mission_text_ar" rows="3">{{ old('about_mission_text_ar', $settings['about_mission_text_ar'] ?? '') }}</textarea>
                    </div>
                </div>
                
                <!-- Vision - Bilingual -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="about_vision_title_en">{{ __('admin.vision_title') }} (English)</label>
                        <input type="text" name="about_vision_title_en" id="about_vision_title_en" value="{{ old('about_vision_title_en', $settings['about_vision_title_en'] ?? 'Our Vision') }}">
                    </div>
                    <div class="form-group">
                        <label for="about_vision_title_ar">{{ __('admin.vision_title') }} (العربية)</label>
                        <input type="text" name="about_vision_title_ar" id="about_vision_title_ar" value="{{ old('about_vision_title_ar', $settings['about_vision_title_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="about_vision_text_en">{{ __('admin.vision_text') }} (English)</label>
                        <textarea name="about_vision_text_en" id="about_vision_text_en" rows="3">{{ old('about_vision_text_en', $settings['about_vision_text_en'] ?? 'To become Algeria\'s most trusted e-commerce platform...') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="about_vision_text_ar">{{ __('admin.vision_text') }} (العربية)</label>
                        <textarea name="about_vision_text_ar" id="about_vision_text_ar" rows="3">{{ old('about_vision_text_ar', $settings['about_vision_text_ar'] ?? '') }}</textarea>
                    </div>
                </div>

                <!-- Story - Bilingual -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="about_story_title_en">{{ __('admin.story_title') }} (English)</label>
                        <input type="text" name="about_story_title_en" id="about_story_title_en" value="{{ old('about_story_title_en', $settings['about_story_title_en'] ?? 'Our Story') }}">
                    </div>
                    <div class="form-group">
                        <label for="about_story_title_ar">{{ __('admin.story_title') }} (العربية)</label>
                        <input type="text" name="about_story_title_ar" id="about_story_title_ar" value="{{ old('about_story_title_ar', $settings['about_story_title_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="about_story_subtitle_en">{{ __('admin.story_subtitle') }} (English)</label>
                        <input type="text" name="about_story_subtitle_en" id="about_story_subtitle_en" value="{{ old('about_story_subtitle_en', $settings['about_story_subtitle_en'] ?? 'Born from a simple idea...') }}">
                    </div>
                    <div class="form-group">
                        <label for="about_story_subtitle_ar">{{ __('admin.story_subtitle') }} (العربية)</label>
                        <input type="text" name="about_story_subtitle_ar" id="about_story_subtitle_ar" value="{{ old('about_story_subtitle_ar', $settings['about_story_subtitle_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="about_story_text_en">{{ __('admin.story_text') }} (English)</label>
                        <textarea name="about_story_text_en" id="about_story_text_en" rows="6">{{ old('about_story_text_en', $settings['about_story_text_en'] ?? 'The Start was founded with a clear vision...') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="about_story_text_ar">{{ __('admin.story_text') }} (العربية)</label>
                        <textarea name="about_story_text_ar" id="about_story_text_ar" rows="6">{{ old('about_story_text_ar', $settings['about_story_text_ar'] ?? '') }}</textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="about_values_heading_en">{{ __('admin.values_heading') }} (English)</label>
                        <input type="text" name="about_values_heading_en" id="about_values_heading_en" value="{{ old('about_values_heading_en', $settings['about_values_heading_en'] ?? 'Our Core Values') }}">
                    </div>
                    <div class="form-group">
                        <label for="about_values_heading_ar">{{ __('admin.values_heading') }} (العربية)</label>
                        <input type="text" name="about_values_heading_ar" id="about_values_heading_ar" value="{{ old('about_values_heading_ar', $settings['about_values_heading_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="about_values">{{ __('admin.values') }} (JSON)</label>
                    <textarea name="about_values" id="about_values" rows="6" class="json-textarea">{{ old('about_values', $settings['about_values'] ?? '') }}</textarea>
                    <small>{{ __('admin.values_help') }}</small>
                    <button type="button" class="btn-validate-json" data-target="about_values">{{ __('admin.validate_json') }}</button>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="about_features_heading_en">{{ __('admin.features_heading') }} (English)</label>
                        <input type="text" name="about_features_heading_en" id="about_features_heading_en" value="{{ old('about_features_heading_en', $settings['about_features_heading_en'] ?? 'Why Choose Us') }}">
                    </div>
                    <div class="form-group">
                        <label for="about_features_heading_ar">{{ __('admin.features_heading') }} (العربية)</label>
                        <input type="text" name="about_features_heading_ar" id="about_features_heading_ar" value="{{ old('about_features_heading_ar', $settings['about_features_heading_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="about_features_subtitle_en">{{ __('admin.features_subtitle') }} (English)</label>
                        <input type="text" name="about_features_subtitle_en" id="about_features_subtitle_en" value="{{ old('about_features_subtitle_en', $settings['about_features_subtitle_en'] ?? 'Experience e-commerce differently') }}">
                    </div>
                    <div class="form-group">
                        <label for="about_features_subtitle_ar">{{ __('admin.features_subtitle') }} (العربية)</label>
                        <input type="text" name="about_features_subtitle_ar" id="about_features_subtitle_ar" value="{{ old('about_features_subtitle_ar', $settings['about_features_subtitle_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="about_features">{{ __('admin.features') }} (JSON)</label>
                    <textarea name="about_features" id="about_features" rows="8" class="json-textarea">{{ old('about_features', $settings['about_features'] ?? '') }}</textarea>
                    <small>{{ __('admin.features_help') }}</small>
                    <button type="button" class="btn-validate-json" data-target="about_features">{{ __('admin.validate_json') }}</button>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="about_team_heading_en">{{ __('admin.team_heading') }} (English)</label>
                        <input type="text" name="about_team_heading_en" id="about_team_heading_en" value="{{ old('about_team_heading_en', $settings['about_team_heading_en'] ?? 'Meet Our Team') }}">
                    </div>
                    <div class="form-group">
                        <label for="about_team_heading_ar">{{ __('admin.team_heading') }} (العربية)</label>
                        <input type="text" name="about_team_heading_ar" id="about_team_heading_ar" value="{{ old('about_team_heading_ar', $settings['about_team_heading_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="about_team_subtitle_en">{{ __('admin.team_subtitle') }} (English)</label>
                        <input type="text" name="about_team_subtitle_en" id="about_team_subtitle_en" value="{{ old('about_team_subtitle_en', $settings['about_team_subtitle_en'] ?? 'Passionate people behind the scenes') }}">
                    </div>
                    <div class="form-group">
                        <label for="about_team_subtitle_ar">{{ __('admin.team_subtitle') }} (العربية)</label>
                        <input type="text" name="about_team_subtitle_ar" id="about_team_subtitle_ar" value="{{ old('about_team_subtitle_ar', $settings['about_team_subtitle_ar'] ?? '') }}">
                    </div>
                </div>

                <h4>{{ __('admin.cta') }}</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="about_cta_title_en">{{ __('admin.cta_title') }} (English)</label>
                        <input type="text" name="about_cta_title_en" id="about_cta_title_en" value="{{ old('about_cta_title_en', $settings['about_cta_title_en'] ?? 'Ready to Start Shopping?') }}">
                    </div>
                    <div class="form-group">
                        <label for="about_cta_title_ar">{{ __('admin.cta_title') }} (العربية)</label>
                        <input type="text" name="about_cta_title_ar" id="about_cta_title_ar" value="{{ old('about_cta_title_ar', $settings['about_cta_title_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="about_cta_text_en">{{ __('admin.cta_text') }} (English)</label>
                        <textarea name="about_cta_text_en" id="about_cta_text_en" rows="2">{{ old('about_cta_text_en', $settings['about_cta_text_en'] ?? 'Join thousands of satisfied customers.') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="about_cta_text_ar">{{ __('admin.cta_text') }} (العربية)</label>
                        <textarea name="about_cta_text_ar" id="about_cta_text_ar" rows="2">{{ old('about_cta_text_ar', $settings['about_cta_text_ar'] ?? '') }}</textarea>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="about_cta_button_text_en">{{ __('admin.cta_button_text') }} (English)</label>
                        <input type="text" name="about_cta_button_text_en" id="about_cta_button_text_en" value="{{ old('about_cta_button_text_en', $settings['about_cta_button_text_en'] ?? 'Shop Now') }}">
                    </div>
                    <div class="form-group">
                        <label for="about_cta_button_text_ar">{{ __('admin.cta_button_text') }} (العربية)</label>
                        <input type="text" name="about_cta_button_text_ar" id="about_cta_button_text_ar" value="{{ old('about_cta_button_text_ar', $settings['about_cta_button_text_ar'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="about_cta_button_link">{{ __('admin.cta_button_link') }}</label>
                        <input type="text" name="about_cta_button_link" id="about_cta_button_link" value="{{ old('about_cta_button_link', $settings['about_cta_button_link'] ?? '/shop') }}">
                    </div>
                </div>
            </div>
        </details>

        <!-- ========== FOOTER ========== -->
        <details class="settings-section settings-card" open>
            <summary class="settings-card-header">
                <h2><i class="fas fa-football-ball"></i> {{ __('admin.footer') }}</h2>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </summary>
            <div class="settings-card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="footer_about_text_en">{{ __('admin.about_text') }} (English)</label>
                        <textarea name="footer_about_text_en" id="footer_about_text_en" rows="3">{{ old('footer_about_text_en', $settings['footer_about_text_en'] ?? 'Your trusted e-commerce platform in Algeria.') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="footer_about_text_ar">{{ __('admin.about_text') }} (العربية)</label>
                        <textarea name="footer_about_text_ar" id="footer_about_text_ar" rows="3">{{ old('footer_about_text_ar', $settings['footer_about_text_ar'] ?? '') }}</textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label for="footer_quick_links">{{ __('admin.quick_links') }} (JSON)</label>
                    <textarea name="footer_quick_links" id="footer_quick_links" rows="5" class="json-textarea">{{ old('footer_quick_links', $settings['footer_quick_links'] ?? '') }}</textarea>
                    <small>{{ __('admin.footer_links_help') }}</small>
                    <button type="button" class="btn-validate-json" data-target="footer_quick_links">{{ __('admin.validate_json') }}</button>
                </div>
                <div class="form-group">
                    <label for="footer_customer_service">{{ __('admin.customer_service_links') }} (JSON)</label>
                    <textarea name="footer_customer_service" id="footer_customer_service" rows="5" class="json-textarea">{{ old('footer_customer_service', $settings['footer_customer_service'] ?? '') }}</textarea>
                    <button type="button" class="btn-validate-json" data-target="footer_customer_service">{{ __('admin.validate_json') }}</button>
                </div>
                <div class="form-group">
                    <label for="footer_copyright">{{ __('admin.copyright_text') }}</label>
                    <input type="text" name="footer_copyright" id="footer_copyright" value="{{ old('footer_copyright', $settings['footer_copyright'] ?? '© 2024 The Start. All rights reserved.') }}">
                </div>

                <h4>{{ __('admin.social_media') }}</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="facebook_url">{{ __('admin.facebook') }}</label>
                        <input type="url" name="facebook_url" id="facebook_url" value="{{ old('facebook_url', $settings['facebook_url'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="instagram_url">{{ __('admin.instagram') }}</label>
                        <input type="url" name="instagram_url" id="instagram_url" value="{{ old('instagram_url', $settings['instagram_url'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="twitter_url">{{ __('admin.twitter') }}</label>
                        <input type="url" name="twitter_url" id="twitter_url" value="{{ old('twitter_url', $settings['twitter_url'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="youtube_url">{{ __('admin.youtube') }}</label>
                        <input type="url" name="youtube_url" id="youtube_url" value="{{ old('youtube_url', $settings['youtube_url'] ?? '') }}">
                    </div>
                </div>
            </div>
        </details>

        <!-- ========== CHECKOUT & SHIPPING ========== -->
        <details class="settings-section settings-card" open>
            <summary class="settings-card-header">
                <h2><i class="fas fa-truck"></i> {{ __('admin.checkout_shipping') }}</h2>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </summary>
            <div class="settings-card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="shipping_cost">{{ __('admin.default_shipping_cost') }}</label>
                        <input type="number" step="0.01" name="shipping_cost" id="shipping_cost" value="{{ old('shipping_cost', $settings['shipping_cost'] ?? 0) }}" min="0">
                    </div>
                    <div class="form-group">
                        <label for="free_shipping_threshold">{{ __('admin.free_shipping_threshold') }}</label>
                        <input type="number" step="0.01" name="free_shipping_threshold" id="free_shipping_threshold" value="{{ old('free_shipping_threshold', $settings['free_shipping_threshold'] ?? 0) }}" min="0">
                        <small>{{ __('admin.free_shipping_threshold_help') }}</small>
                    </div>
                </div>

                <h4>{{ __('admin.shipping_regions') }}</h4>
                <p class="text-muted">{{ __('admin.shipping_regions_help') }}</p>
                <div class="table-responsive">
                    <table class="shipping-table">
                        <thead>
                            <tr><th>{{ __('admin.region') }}</th><th>{{ __('admin.shipping_cost') }} ({{ __('admin.currency_symbol') }})</th></tr>
                        </thead>
                        <tbody>
                            @php
                                $wilayas = [
                                    'Adrar', 'Chlef', 'Laghouat', 'Oum El Bouaghi', 'Batna', 'Béjaïa', 'Biskra', 'Béchar', 'Blida', 'Bouira',
                                    'Tamanrasset', 'Tébessa', 'Tlemcen', 'Tiaret', 'Tizi Ouzou', 'Algiers', 'Djelfa', 'Jijel', 'Sétif', 'Saïda',
                                    'Skikda', 'Sidi Bel Abbès', 'Annaba', 'Guelma', 'Constantine', 'Médéa', 'Mostaganem', 'M\'Sila', 'Mascara',
                                    'Ouargla', 'Oran', 'El Bayadh', 'Illizi', 'Bordj Bou Arréridj', 'Boumerdès', 'El Tarf', 'Tindouf', 'Tissemsilt',
                                    'El Oued', 'Khenchela', 'Souk Ahras', 'Tipaza', 'Mila', 'Aïn Defla', 'Naâma', 'Aïn Témouchent', 'Ghardaïa',
                                    'Relizane', 'Timimoun', 'Bordj Badji Mokhtar', 'Ouled Djellal', 'Béni Abbès', 'In Salah', 'In Guezzam', 'Touggourt',
                                    'Djanet', 'El M\'ghair', 'El Menia'
                                ];
                                $regionCosts = isset($settings['shipping_region_costs']) ? json_decode($settings['shipping_region_costs'], true) : [];
                            @endphp
                            @foreach($wilayas as $wilaya)
                            <tr>
                                <td>{{ $wilaya }}</td>
                                <td><input type="number" step="0.01" name="shipping_region_costs[{{ $wilaya }}]" value="{{ $regionCosts[$wilaya] ?? 0 }}" class="form-control" style="width: 100px;"></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <h4>{{ __('admin.payment_methods') }}</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>{{ __('admin.cash_on_delivery') }}</label>
                        <select name="payment_cod_enabled">
                            <option value="1" {{ ($settings['payment_cod_enabled'] ?? '1') == '1' ? 'selected' : '' }}>{{ __('admin.enabled') }}</option>
                            <option value="0" {{ ($settings['payment_cod_enabled'] ?? '1') == '0' ? 'selected' : '' }}>{{ __('admin.disabled') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('admin.baridimob') }}</label>
                        <select name="payment_baridimob_enabled">
                            <option value="1" {{ ($settings['payment_baridimob_enabled'] ?? '1') == '1' ? 'selected' : '' }}>{{ __('admin.enabled') }}</option>
                            <option value="0" {{ ($settings['payment_baridimob_enabled'] ?? '1') == '0' ? 'selected' : '' }}>{{ __('admin.disabled') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('admin.stripe') }}</label>
                        <select name="payment_stripe_enabled">
                            <option value="1" {{ ($settings['payment_stripe_enabled'] ?? '0') == '1' ? 'selected' : '' }}>{{ __('admin.enabled') }}</option>
                            <option value="0" {{ ($settings['payment_stripe_enabled'] ?? '0') == '0' ? 'selected' : '' }}>{{ __('admin.disabled') }}</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="stripe_public_key">{{ __('admin.stripe_public_key') }}</label>
                        <input type="text" name="stripe_public_key" id="stripe_public_key" value="{{ old('stripe_public_key', $settings['stripe_public_key'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="stripe_secret_key">{{ __('admin.stripe_secret_key') }}</label>
                        <input type="password" name="stripe_secret_key" id="stripe_secret_key" value="{{ old('stripe_secret_key', $settings['stripe_secret_key'] ?? '') }}" autocomplete="off">
                        <small>{{ __('admin.stripe_key_help') }}</small>
                    </div>
                    <div class="form-group">
                        <label for="baridimob_account_name">{{ __('admin.baridimob_account_name') }}</label>
                        <input type="text" name="baridimob_account_name" id="baridimob_account_name" value="{{ old('baridimob_account_name', $settings['baridimob_account_name'] ?? 'The Start E-commerce') }}">
                    </div>
                    <div class="form-group">
                        <label for="baridimob_account">{{ __('admin.baridimob_account') }}</label>
                        <input type="text" name="baridimob_account" id="baridimob_account" value="{{ old('baridimob_account', $settings['baridimob_account'] ?? '123 456 789 01') }}">
                    </div>
                    <div class="form-group">
                        <label for="baridimob_bank">{{ __('admin.baridimob_bank') }}</label>
                        <input type="text" name="baridimob_bank" id="baridimob_bank" value="{{ old('baridimob_bank', $settings['baridimob_bank'] ?? 'Algerian Post (BaridiMob)') }}">
                    </div>
                </div>
            </div>
        </details>

        <!-- ========== AUTHENTICATION ========== -->
        <details class="settings-section settings-card" open>
            <summary class="settings-card-header">
                <h2><i class="fas fa-user-lock"></i> {{ __('admin.authentication') }}</h2>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </summary>
            <div class="settings-card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="enable_google_login">{{ __('admin.enable_google_login') }}</label>
                        <select name="enable_google_login" id="enable_google_login">
                            <option value="1" {{ ($settings['enable_google_login'] ?? '1') == '1' ? 'selected' : '' }}>{{ __('admin.yes') }}</option>
                            <option value="0" {{ ($settings['enable_google_login'] ?? '1') == '0' ? 'selected' : '' }}>{{ __('admin.no') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="enable_github_login">{{ __('admin.enable_github_login') }}</label>
                        <select name="enable_github_login" id="enable_github_login">
                            <option value="1" {{ ($settings['enable_github_login'] ?? '0') == '1' ? 'selected' : '' }}>{{ __('admin.yes') }}</option>
                            <option value="0" {{ ($settings['enable_github_login'] ?? '0') == '0' ? 'selected' : '' }}>{{ __('admin.no') }}</option>
                        </select>
                        <small>{{ __('admin.github_login_note') }}</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="signup_brand_logo">{{ __('admin.signup_brand_logo') }}</label>
                        <input type="file" name="signup_brand_logo" id="signup_brand_logo" accept="image/*">
                        @if(isset($settings['signup_brand_logo']) && $settings['signup_brand_logo'])
                            <div class="current-image">
                                <img src="{{ asset('storage/' . $settings['signup_brand_logo']) }}" class="image-preview-thumb">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="remove_signup_brand_logo" value="1"> {{ __('admin.remove') }}
                                </label>
                            </div>
                        @endif
                        <small>{{ __('admin.logo_help') }}</small>
                    </div>
                    <div class="form-group">
                        <label for="signin_brand_logo">{{ __('admin.signin_brand_logo') }}</label>
                        <input type="file" name="signin_brand_logo" id="signin_brand_logo" accept="image/*">
                        @if(isset($settings['signin_brand_logo']) && $settings['signin_brand_logo'])
                            <div class="current-image">
                                <img src="{{ asset('storage/' . $settings['signin_brand_logo']) }}" class="image-preview-thumb">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="remove_signin_brand_logo" value="1"> {{ __('admin.remove') }}
                                </label>
                            </div>
                        @endif
                        <small>{{ __('admin.logo_help') }}</small>
                    </div>
                </div>
            </div>
        </details>

        <!-- ========== LEGAL PAGES ========== -->
        <details class="settings-section settings-card" open>
            <summary class="settings-card-header">
                <h2><i class="fas fa-gavel"></i> {{ __('admin.legal_pages') }}</h2>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </summary>
            <div class="settings-card-body">
                <div class="form-group">
                    <label for="privacy_policy">{{ __('admin.privacy_policy') }}</label>
                    <textarea name="privacy_policy" id="privacy_policy" rows="8">{{ old('privacy_policy', $settings['privacy_policy'] ?? '') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="terms_of_service">{{ __('admin.terms_of_service') }}</label>
                    <textarea name="terms_of_service" id="terms_of_service" rows="8">{{ old('terms_of_service', $settings['terms_of_service'] ?? '') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="shipping_policy">{{ __('admin.shipping_policy') }}</label>
                    <textarea name="shipping_policy" id="shipping_policy" rows="6">{{ old('shipping_policy', $settings['shipping_policy'] ?? '') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="return_policy">{{ __('admin.return_policy') }}</label>
                    <textarea name="return_policy" id="return_policy" rows="6">{{ old('return_policy', $settings['return_policy'] ?? '') }}</textarea>
                </div>
            </div>
        </details>

        <div class="form-actions">
            <button type="submit" class="btn-primary">{{ __('admin.save_all_settings') }}</button>
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">{{ __('admin.cancel') }}</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // File image preview
    function setupImagePreview(fileInput, containerSelector) {
        fileInput.addEventListener('change', function(e) {
            const container = document.querySelector(containerSelector);
            if (!container) return;
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    let img = container.querySelector('img');
                    if (!img) {
                        img = document.createElement('img');
                        img.classList.add('image-preview-thumb');
                        container.prepend(img);
                    }
                    img.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    const logoInput = document.getElementById('logo');
    if (logoInput) setupImagePreview(logoInput, '.current-image[data-image-type="logo"]');
    const faviconInput = document.getElementById('favicon');
    if (faviconInput) setupImagePreview(faviconInput, '.current-image[data-image-type="favicon"]');
    const heroBgInput = document.getElementById('home_hero_background');
    if (heroBgInput) setupImagePreview(heroBgInput, '.current-image[data-image-type="hero_bg"]');

    // JSON validation for textareas
    const validateButtons = document.querySelectorAll('.btn-validate-json');
    validateButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const textarea = document.getElementById(targetId);
            if (!textarea) return;
            const value = textarea.value.trim();
            if (!value) {
                alert('{{ __("admin.json_empty") }}');
                return;
            }
            try {
                JSON.parse(value);
                alert('{{ __("admin.json_valid") }}');
                textarea.classList.remove('invalid-json');
                textarea.classList.add('valid-json');
            } catch (e) {
                alert('{{ __("admin.json_invalid") }}\n' + e.message);
                textarea.classList.add('invalid-json');
                textarea.classList.remove('valid-json');
            }
        });
    });
</script>
@endpush