@extends('layouts.app')

@section('title', $settings['home_page_title'] ?? __('messages.home_page_title'))

@push('styles')
    @vite('resources/css/home.css')
@endpush

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

                <!-- Slider navigation dots -->
                <div class="slider-dots">
                    @foreach($banners as $index => $banner)
                        <span class="dot" data-slide="{{ $index }}"></span>
                    @endforeach
                </div>
            </div>
        @else
            <!-- Fallback static hero (all texts can be overridden via settings) -->
            <div class="hero-static">
                <h1>{{ $settings['home_hero_fallback_title'] ?? __('messages.effortless_style') }}<br>{{ $settings['home_hero_fallback_subtitle'] ?? __('messages.uncompromised_comfort') }}</h1>
                <p>{{ $settings['home_hero_fallback_description'] ?? __('messages.discover_collection') }}</p>
                <a href="{{ route('Shop') }}" class="btn-primary">
                    {{ $settings['home_hero_fallback_button_text'] ?? __('messages.shop_now') }} →
                </a>
            </div>
        @endif

        <!-- Features (already using settings) -->
        <div class="features">
            <div class="feature">
                <i class="fa-solid fa-truck-fast"></i>
                <h3>{{ $settings['feature_1_title'] ?? __('messages.free_shipping') }}</h3>
                <p>{{ $settings['feature_1_desc'] ?? __('messages.free_shipping_desc') }}</p>
            </div>
            <div class="feature">
                <i class="fa-solid fa-rotate-left"></i>
                <h3>{{ $settings['feature_2_title'] ?? __('messages.secure_payment') }}</h3>
                <p>{{ $settings['feature_2_desc'] ?? __('messages.secure_payment_desc') }}</p>
            </div>
            <div class="feature">
                <i class="fa-solid fa-gem"></i>
                <h3>{{ $settings['feature_3_title'] ?? __('messages.fast_delivery') }}</h3>
                <p>{{ $settings['feature_3_desc'] ?? __('messages.fast_delivery_desc') }}</p>
            </div>
        </div>

        <!-- Section 1: Best Sellers -->
        <h2 class="section-title">{{ $settings['home_best_sellers_heading'] ?? __('messages.best_sellers') }}</h2>
        <div class="product-grid">
            @forelse($bestsellers->take(3) as $product)
                @include('partials.product-card', ['product' => $product])
            @empty
                <p>{{ __('messages.no_bestsellers') }}</p>
            @endforelse
            <a href="{{ route('Shop') }}?filter=bestseller" class="view-more-card">
                <div class="view-more-icon"><i class="fas fa-arrow-right"></i></div>
                <div class="view-more-text">{{ $settings['home_view_more_text'] ?? __('messages.view_more') }}</div>
            </a>
        </div>

        <!-- Section 2: New Arrivals -->
        <h2 class="section-title">{{ $settings['home_new_arrivals_heading'] ?? __('messages.new_arrivals') }}</h2>
        <div class="product-grid">
            @forelse($newArrivals->take(3) as $product)
                @include('partials.product-card', ['product' => $product])
            @empty
                <p>{{ __('messages.no_new_products') }}</p>
            @endforelse
            <a href="{{ route('Shop') }}?filter=new" class="view-more-card">
                <div class="view-more-icon"><i class="fas fa-arrow-right"></i></div>
                <div class="view-more-text">{{ $settings['home_view_more_text'] ?? __('messages.view_more') }}</div>
            </a>
        </div>

        <!-- Section 3: Category Products (category name already dynamic) -->
        <h2 class="section-title">{{ $featuredCategoryName }}</h2>
        <div class="product-grid">
            @forelse($categoryProducts->take(3) as $product)
                @include('partials.product-card', ['product' => $product])
            @empty
                <p>{{ __('messages.no_category_products') }}</p>
            @endforelse
            <a href="{{ route('Shop') }}?category={{ urlencode($featuredCategoryName) }}" class="view-more-card">
                <div class="view-more-icon"><i class="fas fa-arrow-right"></i></div>
                <div class="view-more-text">{{ $settings['home_view_more_text'] ?? __('messages.view_more') }}</div>
            </a>
        </div>

        <!-- Call to Action (already using settings) -->
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