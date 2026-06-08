@extends('layouts.app')

@section('title', $settings['home_page_title_' . app()->getLocale()] ?? $settings['home_page_title_en'] ?? __('messages.home_page_title'))

@push('styles')
    @vite('resources/css/home.css')
@endpush

@section('content')
<div class="home-main">
    <div class="container">

        {{-- ══════════════════════════════════════════
             HERO SLIDER — Ken Burns + crossfade + dots
        ══════════════════════════════════════════ --}}
        @if($banners->isNotEmpty())
            <div class="hero-slider" id="heroSlider">
                @foreach($banners as $index => $banner)
                    <div class="hero-slide {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}">
                        <img src="{{ asset($banner->image_url) }}"
                             alt="{{ $banner->title }}"
                             class="hero-slide-img">
                        <div class="hero-slide-overlay"></div>
                        <div class="hero-slide-gradient"></div>
                        <div class="hero-content">
                            <div class="hero-content-backdrop">
                                <h1 class="hero-title">{{ $banner->title }}</h1>
                                @if($banner->link)
                                    <a href="{{ route('banner.click', $banner->id) }}"
                                       class="btn-hero btn-premium btn-shimmer btn-glow"
                                       target="_blank">
                                        <span class="btn-text">{{ __('messages.shop_now') }}</span>
                                        <span class="btn-icon">→</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="slider-dots">
                    @foreach($banners as $index => $banner)
                        <button class="dot {{ $index === 0 ? 'active' : '' }}"
                                data-slide="{{ $index }}"
                                aria-label="Go to slide {{ $index + 1 }}"></button>
                    @endforeach
                </div>

                {{-- Arrow controls --}}
                @if($banners->count() > 1)
                <button class="slider-arrow slider-prev" id="sliderPrev" aria-label="Previous">
                    <i class="fas fa-chevron-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}"></i>
                </button>
                <button class="slider-arrow slider-next" id="sliderNext" aria-label="Next">
                    <i class="fas fa-chevron-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}"></i>
                </button>
                @endif
            </div>

        @else
            {{-- Fallback static hero --}}
            @php
                $locale = app()->getLocale();
                $heroTitle    = $settings['home_hero_fallback_title_'.$locale]    ?? $settings['home_hero_fallback_title_en']    ?? __('messages.effortless_style');
                $heroSubtitle = $settings['home_hero_fallback_subtitle_'.$locale] ?? $settings['home_hero_fallback_subtitle_en'] ?? __('messages.uncompromised_comfort');
                $heroDesc     = $settings['home_hero_fallback_description_'.$locale] ?? $settings['home_hero_fallback_description_en'] ?? __('messages.discover_collection');
                $heroButton   = $settings['home_hero_fallback_button_text_'.$locale] ?? $settings['home_hero_fallback_button_text_en'] ?? __('messages.shop_now');
            @endphp
            <div class="hero-static">
                <div class="hero-static-bg"></div>
                <div class="hero-static-content">
                    <h1 class="hero-static-title">
                        {{ $heroTitle }}<span class="title-accent">{{ $heroSubtitle }}</span>
                    </h1>
                    <p class="hero-static-desc">{{ $heroDesc }}</p>
                    <a href="{{ route('Shop') }}" class="btn-primary btn-premium btn-shimmer btn-glow">
                        <span class="btn-text">{{ $heroButton }}</span>
                        <span class="btn-icon">→</span>
                    </a>
                </div>
                <div class="hero-decoration"></div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════
             FEATURE STRIP — SVG icons + scroll-reveal stagger
        ══════════════════════════════════════════ --}}
        @php
            $locale = app()->getLocale();
            $feature1Title = $settings['feature_1_title_'.$locale] ?? $settings['feature_1_title_en'] ?? __('messages.free_shipping');
            $feature1Desc  = $settings['feature_1_desc_'.$locale]  ?? $settings['feature_1_desc_en']  ?? __('messages.free_shipping_desc');
            $feature2Title = $settings['feature_2_title_'.$locale] ?? $settings['feature_2_title_en'] ?? __('messages.secure_payment');
            $feature2Desc  = $settings['feature_2_desc_'.$locale]  ?? $settings['feature_2_desc_en']  ?? __('messages.secure_payment_desc');
            $feature3Title = $settings['feature_3_title_'.$locale] ?? $settings['feature_3_title_en'] ?? __('messages.fast_delivery');
            $feature3Desc  = $settings['feature_3_desc_'.$locale]  ?? $settings['feature_3_desc_en']  ?? __('messages.fast_delivery_desc');
        @endphp

        <div class="features">
            {{-- Free shipping --}}
            <div class="feature" data-reveal style="transition-delay:0ms">
                <div class="feature-icon">
                    <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="1" y="3" width="15" height="13" rx="1"/>
                        <path d="M16 8h4l3 3v5h-7V8z"/>
                        <circle cx="5.5" cy="18.5" r="2.5"/>
                        <circle cx="18.5" cy="18.5" r="2.5"/>
                    </svg>
                </div>
                <h3>{{ $feature1Title }}</h3>
                <p>{{ $feature1Desc }}</p>
            </div>

            {{-- Secure payment --}}
            <div class="feature" data-reveal style="transition-delay:120ms">
                <div class="feature-icon">
                    <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 1L3 5v6c0 5.25 3.75 10.15 9 11.35C17.25 21.15 21 16.25 21 11V5l-9-4z"/>
                        <path d="M9 12l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3>{{ $feature2Title }}</h3>
                <p>{{ $feature2Desc }}</p>
            </div>

            {{-- Fast delivery --}}
            <div class="feature" data-reveal style="transition-delay:240ms">
                <div class="feature-icon">
                    <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3>{{ $feature3Title }}</h3>
                <p>{{ $feature3Desc }}</p>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             BEST SELLERS
        ══════════════════════════════════════════ --}}
        @php
            $bestsellersHeading = $settings['home_best_sellers_heading_'.$locale] ?? $settings['home_best_sellers_heading_en'] ?? __('messages.best_sellers');
            $viewMore = $settings['home_view_more_text_'.$locale] ?? $settings['home_view_more_text_en'] ?? __('messages.view_more');
        @endphp

        <h2 class="section-title" data-reveal>{{ $bestsellersHeading }}</h2>
        <div class="product-grid">
            @forelse($bestsellers->take(3) as $product)
                @include('partials.product-card', ['product' => $product])
            @empty
                <p>{{ __('messages.no_bestsellers') }}</p>
            @endforelse
            <a href="{{ route('Shop') }}?filter=bestseller" class="view-more-card" data-reveal>
                <div class="view-more-icon"><i class="fas fa-arrow-right"></i></div>
                <div class="view-more-text">{{ $viewMore }}</div>
            </a>
        </div>

        {{-- ══════════════════════════════════════════
             NEW ARRIVALS
        ══════════════════════════════════════════ --}}
        @php
            $newArrivalsHeading = $settings['home_new_arrivals_heading_'.$locale] ?? $settings['home_new_arrivals_heading_en'] ?? __('messages.new_arrivals');
        @endphp

        <h2 class="section-title" data-reveal>{{ $newArrivalsHeading }}</h2>
        <div class="product-grid">
            @forelse($newArrivals->take(3) as $product)
                @include('partials.product-card', ['product' => $product])
            @empty
                <p>{{ __('messages.no_new_products') }}</p>
            @endforelse
            <a href="{{ route('Shop') }}?filter=new" class="view-more-card" data-reveal>
                <div class="view-more-icon"><i class="fas fa-arrow-right"></i></div>
                <div class="view-more-text">{{ $viewMore }}</div>
            </a>
        </div>

        {{-- ══════════════════════════════════════════
             CATEGORY PRODUCTS
        ══════════════════════════════════════════ --}}
        <h2 class="section-title" data-reveal>{{ $featuredCategoryName }}</h2>
        <div class="product-grid">
            @forelse($categoryProducts->take(3) as $product)
                @include('partials.product-card', ['product' => $product])
            @empty
                <p>{{ __('messages.no_category_products') }}</p>
            @endforelse
            <a href="{{ route('Shop') }}?category={{ urlencode($featuredCategoryName) }}" class="view-more-card" data-reveal>
                <div class="view-more-icon"><i class="fas fa-arrow-right"></i></div>
                <div class="view-more-text">{{ $viewMore }}</div>
            </a>
        </div>

        {{-- ══════════════════════════════════════════
             CTA — animated gradient + glow button
        ══════════════════════════════════════════ --}}
        @php
            $ctaTitle      = $settings['home_cta_title_'.$locale]       ?? $settings['home_cta_title_en']       ?? __('messages.discover_collection');
            $ctaText       = $settings['home_cta_text_'.$locale]        ?? $settings['home_cta_text_en']        ?? __('messages.discover_collection_desc');
            $ctaButtonText = $settings['home_cta_button_text_'.$locale] ?? $settings['home_cta_button_text_en'] ?? __('messages.shop_now');
            $ctaButtonLink = $settings['home_cta_button_link'] ?? '/shop';
        @endphp
        <div class="cta-section" data-reveal>
            <h2>{{ $ctaTitle }}</h2>
            <p>{{ $ctaText }}</p>
            <a href="{{ $ctaButtonLink }}" class="btn-primary btn-shimmer btn-glow">
                {{ $ctaButtonText }} →
            </a>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
/* ── Feature icon gold accent ── */
.feature-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 64px; height: 64px;
    border-radius: 50%;
    background: var(--gold-light, #E8D5A3);
    color: var(--gold-dark, #8B6914);
    margin: 0 auto 14px;
    transition: transform .3s ease, box-shadow .3s ease;
}
.feature:hover .feature-icon {
    transform: scale(1.1) rotate(-4deg);
    box-shadow: 0 6px 20px var(--gold-glow, rgba(201,169,110,.35));
}

/* ── Slider arrows ── */
.slider-arrow {
    position: absolute;
    top: 50%; transform: translateY(-50%);
    z-index: 10;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.3);
    color: #fff;
    width: 42px; height: 42px;
    border-radius: 50%;
    cursor: pointer;
    backdrop-filter: blur(4px);
    transition: background .2s ease;
    display: flex; align-items: center; justify-content: center;
}
.slider-arrow:hover { background: rgba(201,169,110,0.6); }
.slider-prev { left: 16px; }
.slider-next { right: 16px; }
[dir="rtl"] .slider-prev { left: auto; right: 16px; }
[dir="rtl"] .slider-next { right: auto; left: 16px; }
/* ========== VIEW MORE CARD – PREMIUM EFFECT ========== */
.view-more-card {
    transition: transform 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1),
                box-shadow 0.4s ease,
                border-color 0.3s ease;
    border: 1px solid var(--color-border);
}
.view-more-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12), 0 0 18px rgba(201, 169, 110, 0.4);
    border-color: var(--gold, #C9A96E);
}
.view-more-icon i {
    transition: transform 0.3s cubic-bezier(0.34, 1.2, 0.64, 1);
    display: inline-block;
}
.view-more-card:hover .view-more-icon i {
    transform: translateX(6px);
}
[dir="rtl"] .view-more-card:hover .view-more-icon i {
    transform: translateX(-6px);
}
</style>
@endpush

@push('scripts')
    @vite('resources/js/home.js')
    @vite('resources/js/cards.js')
    {{-- Hero slider inline script (only slideshow, no add-to-cart) --}}
    <script>
    (function () {
        const slider = document.getElementById('heroSlider');
        if (!slider) return;

        const slides = slider.querySelectorAll('.hero-slide');
        const dots = slider.querySelectorAll('.dot');
        const prevBtn = document.getElementById('sliderPrev');
        const nextBtn = document.getElementById('sliderNext');
        let current = 0;
        let timer = null;

        function goTo(n) {
            slides[current].classList.remove('active');
            dots[current]?.classList.remove('active');
            current = (n + slides.length) % slides.length;
            slides[current].classList.add('active');
            dots[current]?.classList.add('active');
            const img = slides[current].querySelector('.hero-slide-img');
            if (img) {
                const clone = img.cloneNode(true);
                img.replaceWith(clone);
            }
        }

        function startAuto() {
            clearInterval(timer);
            timer = setInterval(() => goTo(current + 1), 4500);
        }

        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                goTo(parseInt(dot.dataset.slide));
                startAuto();
            });
        });
        if (prevBtn) prevBtn.addEventListener('click', () => { goTo(current - 1); startAuto(); });
        if (nextBtn) nextBtn.addEventListener('click', () => { goTo(current + 1); startAuto(); });
        slider.addEventListener('mouseenter', () => clearInterval(timer));
        slider.addEventListener('mouseleave', startAuto);
        if (slides.length > 1) startAuto();
    })();
    </script>
@endpush