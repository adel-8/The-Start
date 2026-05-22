@extends('layouts.app')

@section('title', $settings['home_page_title_' . app()->getLocale()] ?? $settings['home_page_title_en'] ?? __('messages.home_page_title'))

<link rel="stylesheet" href="{{ asset('css/home.css') }}">
@section('content')
<div class="home-main">
    <div class="container">
        <!-- Hero Slider (only if banners exist) -->
        @if($banners->isNotEmpty())
            <div class="hero-slider">
                @foreach($banners as $index => $banner)
                    <div class="hero-slide {{ $index === 0 ? 'active' : '' }}">
                        <img src="{{ asset($banner->image_url) }}" alt="{{ $banner->title }}">
                        <div class="hero-content">
                            <h1>{{ $banner->title }}</h1>
                            @if($banner->link)
                                <a href="{{ route('banner.click', $banner->id) }}" class="btn-hero" target="_blank">
                                    {{ __('messages.shop_now') }} →
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
                <div class="slider-dots">
                    @foreach($banners as $index => $banner)
                        <span class="dot" data-slide="{{ $index }}"></span>
                    @endforeach
                </div>
            </div>
        @else
            <!-- Fallback static hero (bilingual) -->
            @php
                $locale = app()->getLocale();
                $heroTitle = $settings['home_hero_fallback_title_' . $locale] 
                    ?? $settings['home_hero_fallback_title_en'] 
                    ?? __('messages.effortless_style');
                $heroSubtitle = $settings['home_hero_fallback_subtitle_' . $locale] 
                    ?? $settings['home_hero_fallback_subtitle_en'] 
                    ?? __('messages.uncompromised_comfort');
                $heroDesc = $settings['home_hero_fallback_description_' . $locale] 
                    ?? $settings['home_hero_fallback_description_en'] 
                    ?? __('messages.discover_collection');
                $heroButton = $settings['home_hero_fallback_button_text_' . $locale] 
                    ?? $settings['home_hero_fallback_button_text_en'] 
                    ?? __('messages.shop_now');
            @endphp
            <div class="hero-static">
                <h1>{{ $heroTitle }}<br>{{ $heroSubtitle }}</h1>
                <p>{{ $heroDesc }}</p>
                <a href="{{ route('Shop') }}" class="btn-primary">{{ $heroButton }} →</a>
            </div>
        @endif

        <!-- Features (bilingual) -->
        @php
            $locale = app()->getLocale();
            $feature1Title = $settings['feature_1_title_' . $locale] ?? $settings['feature_1_title_en'] ?? __('messages.free_shipping');
            $feature1Desc  = $settings['feature_1_desc_' . $locale]  ?? $settings['feature_1_desc_en']  ?? __('messages.free_shipping_desc');
            $feature2Title = $settings['feature_2_title_' . $locale] ?? $settings['feature_2_title_en'] ?? __('messages.secure_payment');
            $feature2Desc  = $settings['feature_2_desc_' . $locale]  ?? $settings['feature_2_desc_en']  ?? __('messages.secure_payment_desc');
            $feature3Title = $settings['feature_3_title_' . $locale] ?? $settings['feature_3_title_en'] ?? __('messages.fast_delivery');
            $feature3Desc  = $settings['feature_3_desc_' . $locale]  ?? $settings['feature_3_desc_en']  ?? __('messages.fast_delivery_desc');
        @endphp
        <div class="features">
            <div class="feature">
                <i class="fa-solid fa-truck-fast"></i>
                <h3>{{ $feature1Title }}</h3>
                <p>{{ $feature1Desc }}</p>
            </div>
            <div class="feature">
                <i class="fa-solid fa-rotate-left"></i>
                <h3>{{ $feature2Title }}</h3>
                <p>{{ $feature2Desc }}</p>
            </div>
            <div class="feature">
                <i class="fa-solid fa-gem"></i>
                <h3>{{ $feature3Title }}</h3>
                <p>{{ $feature3Desc }}</p>
            </div>
        </div>

        <!-- Section 1: Best Sellers -->
        @php
            $bestsellersHeading = $settings['home_best_sellers_heading_' . $locale] ?? $settings['home_best_sellers_heading_en'] ?? __('messages.best_sellers');
        @endphp
        <h2 class="section-title">{{ $bestsellersHeading }}</h2>
        <div class="product-grid">
            @forelse($bestsellers->take(3) as $product)
                @include('partials.product-card', ['product' => $product])
            @empty
                <p>{{ __('messages.no_bestsellers') }}</p>
            @endforelse
            @php
                $viewMore = $settings['home_view_more_text_' . $locale] ?? $settings['home_view_more_text_en'] ?? __('messages.view_more');
            @endphp
            <a href="{{ route('Shop') }}?filter=bestseller" class="view-more-card">
                <div class="view-more-icon"><i class="fas fa-arrow-right"></i></div>
                <div class="view-more-text">{{ $viewMore }}</div>
            </a>
        </div>

        <!-- Section 2: New Arrivals -->
        @php
            $newArrivalsHeading = $settings['home_new_arrivals_heading_' . $locale] ?? $settings['home_new_arrivals_heading_en'] ?? __('messages.new_arrivals');
        @endphp
        <h2 class="section-title">{{ $newArrivalsHeading }}</h2>
        <div class="product-grid">
            @forelse($newArrivals->take(3) as $product)
                @include('partials.product-card', ['product' => $product])
            @empty
                <p>{{ __('messages.no_new_products') }}</p>
            @endforelse
            <a href="{{ route('Shop') }}?filter=new" class="view-more-card">
                <div class="view-more-icon"><i class="fas fa-arrow-right"></i></div>
                <div class="view-more-text">{{ $viewMore }}</div>
            </a>
        </div>

        <!-- Section 3: Category Products -->
        <h2 class="section-title">{{ $featuredCategoryName }}</h2>
        <div class="product-grid">
            @forelse($categoryProducts->take(3) as $product)
                @include('partials.product-card', ['product' => $product])
            @empty
                <p>{{ __('messages.no_category_products') }}</p>
            @endforelse
            <a href="{{ route('Shop') }}?category={{ urlencode($featuredCategoryName) }}" class="view-more-card">
                <div class="view-more-icon"><i class="fas fa-arrow-right"></i></div>
                <div class="view-more-text">{{ $viewMore }}</div>
            </a>
        </div>

        <!-- Call to Action (bilingual) -->
        @php
            $ctaTitle       = $settings['home_cta_title_' . $locale]       ?? $settings['home_cta_title_en']       ?? __('messages.discover_collection');
            $ctaText        = $settings['home_cta_text_' . $locale]        ?? $settings['home_cta_text_en']        ?? __('messages.discover_collection_desc');
            $ctaButtonText  = $settings['home_cta_button_text_' . $locale] ?? $settings['home_cta_button_text_en'] ?? __('messages.shop_now');
            $ctaButtonLink  = $settings['home_cta_button_link'] ?? '/shop';
        @endphp
        <div class="cta-section">
            <h2>{{ $ctaTitle }}</h2>
            <p>{{ $ctaText }}</p>
            <a href="{{ $ctaButtonLink }}" class="btn-primary">{{ $ctaButtonText }} →</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/home.js')
@endpush