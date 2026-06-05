@extends('layouts.app')

@section('title', $product->name . ' - ' . ($settings['site_name'] ?? config('app.name', 'The Start')))

@push('styles')
    @vite('resources/css/product.css')
@endpush

@section('content')
<div class="product-page">
    <div class="container">

        {{-- Breadcrumb --}}
        <nav class="breadcrumb" aria-label="breadcrumb">
            <a href="{{ route('home') }}">{{ __('messages.home') }}</a>
            <span class="separator">/</span>
            <a href="{{ route('Shop') }}">{{ __('messages.shop') }}</a>
            <span class="separator">/</span>
            <span class="current">{{ $product->name }}</span>
        </nav>

        <div class="product-layout">

            {{-- ── Left: Gallery with color variations ── --}}
            <div class="product-gallery" data-reveal>
                @php
                    $colorVariations = $product->variations->where('attribute_name', 'color');
                    $mainImage = $product->image_url;
                    if ($colorVariations->isNotEmpty() && $colorVariations->first()->image_url) {
                        $mainImage = $colorVariations->first()->image_url;
                    }
                @endphp
                <div class="main-image">
                    <img id="product-main-image" src="{{ asset($mainImage) }}" alt="{{ $product->name }}">
                </div>

                @if($colorVariations->count() > 1)
                    <div class="color-swatches">
                        <h4>{{ __('messages.colors') }}:</h4>
                        <div class="swatch-list">
                            @foreach($colorVariations as $var)
                                @php $img = $var->image_url ?: $product->image_url; @endphp
                                <button type="button"
                                        class="color-swatch @if($loop->first) active @endif"
                                        data-image="{{ asset($img) }}"
                                        data-name="{{ $var->attribute_value }}">
                                    {{ $var->attribute_value }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- ── Right: Details ── --}}
            <div class="product-details" data-reveal>

                {{-- Badges --}}
                <div class="product-badges">
                    @if($product->is_new)
                        <span class="badge badge-new">{{ __('messages.new') }}</span>
                    @endif
                    @if($product->bestseller)
                        <span class="badge badge-bestseller">{{ __('messages.best_seller') }}</span>
                    @endif
                    @if($product->stock <= 0)
                        <span class="badge badge-danger">{{ __('messages.out_of_stock') }}</span>
                    @endif
                </div>

                <h1 class="product-title">{{ $product->name }}</h1>

                {{-- Animated star rating --}}
                @php $avgRating = $product->averageRating(); @endphp
                @if($avgRating)
                    <div class="product-stars" aria-label="{{ __('messages.average_rating') }}: {{ number_format($avgRating, 1) }}">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="star {{ $i <= round($avgRating) ? 'star-filled' : 'star-empty' }}">
                                {{ $i <= round($avgRating) ? '★' : '☆' }}
                            </span>
                        @endfor
                        <span class="rating-value">({{ number_format($avgRating, 1) }})</span>
                    </div>
                @endif

                <div class="product-price">{{ format_currency($product->price) }}</div>

                {{-- Stock status --}}
                @php $inStock = ($product->stock ?? 0) > 0 && $product->status === 'active'; @endphp
                <div class="stock-status">
                    @if($inStock)
                        <span class="in-stock">
                            <i class="fas fa-check-circle"></i> {{ __('messages.in_stock') }}
                        </span>
                    @else
                        <span class="out-of-stock">
                            <i class="fas fa-times-circle"></i> {{ __('messages.out_of_stock') }}
                        </span>
                    @endif
                </div>

                @if($inStock)
                    {{-- Quantity selector --}}
                    <div class="quantity-selector">
                        <label for="quantity">{{ __('messages.quantity') }}:</label>
                        <div class="quantity-controls">
                            <button class="qty-btn" id="decreaseQty" type="button" aria-label="Decrease">−</button>
                            <input type="number" id="quantity" name="quantity"
                                   value="1" min="1" max="{{ $product->stock }}">
                            <button class="qty-btn" id="increaseQty" type="button" aria-label="Increase">+</button>
                        </div>
                    </div>

                    {{-- Action buttons --}}
                    <div class="action-buttons">
                        <button class="add-cart-btn"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-price="{{ $product->price }}">
                            <i class="fas fa-shopping-bag"></i>
                            {{ $settings['product_add_to_cart_button_text'] ?? __('messages.add_to_cart') }}
                        </button>
                        <button class="buy-now-btn" id="buyNowBtn">
                            {{ $settings['product_buy_now_button_text'] ?? __('messages.buy_now') }}
                        </button>
                    </div>
                @else
                    <div class="action-buttons">
                        <button class="add-cart-btn disabled" disabled>{{ __('messages.out_of_stock') }}</button>
                    </div>
                @endif
            </div>

            {{-- TABS: Description | Reviews --}}
            <div class="product-tabs" data-reveal>
                <div class="tabs-nav" role="tablist">
                    <button class="tab-btn active" role="tab" aria-selected="true" data-tab="description">
                        {{ __('messages.description') }}
                    </button>
                    <button class="tab-btn" role="tab" aria-selected="false" data-tab="reviews">
                        {{ __('messages.customer_reviews') }}
                        @php $reviewCount = $product->approvedReviews()->count(); @endphp
                        @if($reviewCount)
                            <span class="tab-count">{{ $reviewCount }}</span>
                        @endif
                    </button>
                </div>

                {{-- Description tab --}}
                <div class="tab-panel active" id="tab-description" role="tabpanel">
                    @if($product->description)
                        <div class="product-description-body">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                    @else
                        <p class="tab-empty">{{ __('messages.no_description') }}</p>
                    @endif
                </div>

                {{-- Reviews tab --}}
                <div class="tab-panel" id="tab-reviews" role="tabpanel">
                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        </div>
                    @endif

                    @if($avgRating)
                        <div class="average-rating">
                            <span class="avg-number">{{ number_format($avgRating, 1) }}</span>
                            <div class="avg-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="star {{ $i <= round($avgRating) ? 'star-filled' : 'star-empty' }}">
                                        {{ $i <= round($avgRating) ? '★' : '☆' }}
                                    </span>
                                @endfor
                            </div>
                            <span class="avg-label">{{ __('messages.average_rating') }}</span>
                        </div>
                    @endif

                    <div class="reviews-list">
                        @forelse($product->approvedReviews()->latest()->get() as $review)
                            <div class="review-item">
                                <div class="review-header">
                                    <strong>{{ $review->user->name ?? $review->user->username }}</strong>
                                    <div class="review-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="{{ $i <= $review->rating ? 'star-filled' : 'star-empty' }}">
                                                {{ $i <= $review->rating ? '★' : '☆' }}
                                            </span>
                                        @endfor
                                    </div>
                                    <small>{{ $review->created_at->format('M d, Y') }}</small>
                                </div>
                                @if($review->comment)
                                    <p>{{ $review->comment }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="tab-empty">{{ __('messages.no_reviews_yet') }}</p>
                        @endforelse
                    </div>

                    @auth
                        @php $userReview = $product->reviews()->where('user_id', auth()->id())->first(); @endphp
                        @if(!$userReview)
                            <div class="review-form">
                                <h4>{{ __('messages.write_a_review') }}</h4>
                                <form action="{{ route('product.review.store', $product) }}" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label>{{ __('messages.rating') }}</label>
                                        <select name="rating" required>
                                            <option value="5">5 – {{ __('messages.excellent') }}</option>
                                            <option value="4">4 – {{ __('messages.very_good') }}</option>
                                            <option value="3">3 – {{ __('messages.average') }}</option>
                                            <option value="2">2 – {{ __('messages.poor') }}</option>
                                            <option value="1">1 – {{ __('messages.very_poor') }}</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('messages.your_review') }}</label>
                                        <textarea name="comment" rows="4"
                                                  placeholder="{{ __('messages.share_your_experience') }}"
                                                  required minlength="10"></textarea>
                                    </div>
                                    <button type="submit" class="btn-primary">{{ __('messages.submit_review') }}</button>
                                </form>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> {{ __('messages.already_reviewed') }}
                            </div>
                        @endif
                    @else
                        <p class="login-to-review-message">
                            {!! __('messages.login_to_review', ['link' => '<a href="'.route('signin').'">'.__('messages.login').'</a>']) !!}
                        </p>
                    @endauth
                </div>
            </div>
        </div>

        {{-- Related Products --}}
        @if($relatedProducts && $relatedProducts->count())
            <div class="related-products">
                <h2 class="section-title" data-reveal>
                    {{ $settings['product_related_heading'] ?? __('messages.you_might_also_like') }}
                </h2>
                <div class="product-grid">
                    @foreach($relatedProducts as $related)
                        @include('partials.product-card', ['product' => $related])
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>
@endsection

@push('styles')
<style>
/* ── Animated star rating on product page ── */
.product-stars {
    display: flex;
    align-items: center;
    gap: 3px;
    margin: 10px 0 14px;
    font-size: 20px;
}
.product-stars .star-filled { color: var(--gold, #C9A96E); }
.product-stars .star-empty  { color: #d1d5db; }
.rating-value { font-size: 13px; color: #6b7280; margin-left: 4px; }

/* ── Qty controls ── */
.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0;
    border: 1px solid var(--color-border, #e5e7eb);
    border-radius: 8px;
    overflow: hidden;
    width: fit-content;
}
.qty-btn {
    width: 38px; height: 38px;
    border: none;
    background: var(--color-surface, #f9fafb);
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
    transition: background .15s;
    display: flex; align-items: center; justify-content: center;
}
.qty-btn:hover { background: var(--gold-light, #E8D5A3); }
#quantity {
    width: 52px; height: 38px;
    border: none; border-left: 1px solid var(--color-border, #e5e7eb);
    border-right: 1px solid var(--color-border, #e5e7eb);
    text-align: center; font-size: 15px;
    -moz-appearance: textfield;
}
#quantity::-webkit-inner-spin-button,
#quantity::-webkit-outer-spin-button { -webkit-appearance: none; }

/* ── Tabs ── */
.product-tabs {
    grid-column: 1 / -1;
    margin-top: 2.5rem;
    border: 1px solid var(--color-border, #e5e7eb);
    border-radius: 12px;
    overflow: hidden;
}
.tabs-nav {
    display: flex;
    border-bottom: 1px solid var(--color-border, #e5e7eb);
    background: var(--color-surface, #f9fafb);
}
.tab-btn {
    padding: 13px 22px;
    border: none; background: none;
    font-size: 14px; font-weight: 500;
    cursor: pointer;
    color: var(--color-text-secondary, #6b7280);
    border-bottom: 2px solid transparent;
    transition: color .2s, border-color .2s;
    display: flex; align-items: center; gap: 6px;
}
.tab-btn.active {
    color: var(--color-text-primary, #111);
    border-bottom-color: var(--gold, #C9A96E);
}
.tab-count {
    background: var(--gold-light, #E8D5A3);
    color: var(--gold-dark, #8B6914);
    font-size: 11px; font-weight: 600;
    padding: 1px 6px; border-radius: 10px;
}
.tab-panel { display: none; padding: 24px; }
.tab-panel.active { display: block; }
.tab-empty { color: var(--color-text-secondary, #6b7280); font-style: italic; }

/* ── Average rating block ── */
.average-rating {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    padding: 14px 16px;
    background: var(--color-surface, #f9fafb);
    border-radius: 8px;
    border: 1px solid var(--color-border, #e5e7eb);
}
.avg-number { font-size: 28px; font-weight: 700; color: var(--gold-dark, #8B6914); }
.avg-stars .star-filled { color: var(--gold, #C9A96E); font-size: 18px; }
.avg-stars .star-empty  { color: #d1d5db; font-size: 18px; }

/* ── Review items ── */
.review-item {
    padding: 14px 0;
    border-bottom: 1px solid var(--color-border, #e5e7eb);
}
.review-item:last-child { border-bottom: none; }
.review-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 6px;
    flex-wrap: wrap;
}
.review-stars .star-filled { color: var(--gold, #C9A96E); }
.review-stars .star-empty  { color: #d1d5db; }
.review-header small { color: #9ca3af; font-size: 12px; margin-left: auto; }

/* ── Review form ── */
.review-form {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid var(--color-border, #e5e7eb);
}
.review-form textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--color-border, #e5e7eb);
    border-radius: 8px;
    font-family: inherit;
    font-size: 14px;
    resize: vertical;
    transition: border-color .2s;
}
.review-form textarea:focus {
    outline: none;
    border-color: var(--gold, #C9A96E);
    box-shadow: 0 0 0 3px var(--gold-glow, rgba(201,169,110,.2));
}

/* ── Color swatches ── */
.color-swatches {
    margin-top: 20px;
}
.swatch-list {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 8px;
}
.color-swatch {
    background: var(--gold-light, #E8D5A3);
    border: 1px solid var(--gold-dark, #8B6914);
    border-radius: 30px;
    padding: 8px 20px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 14px;
    font-weight: 500;
}
.color-swatch.active {
    background: var(--gold-dark, #8B6914);
    color: white;
    border-color: var(--gold-dark);
}
</style>
@endpush

@push('scripts')
    @vite('resources/js/product.js')
    <script>
    (function () {
        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.dataset.tab;
                document.querySelectorAll('.tab-btn').forEach(b => {
                    b.classList.remove('active');
                    b.setAttribute('aria-selected', 'false');
                });
                document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                btn.setAttribute('aria-selected', 'true');
                document.getElementById('tab-' + target)?.classList.add('active');
            });
        });

        // Buy now
        const buyNow = document.getElementById('buyNowBtn');
        const qtyInput = document.getElementById('quantity');
        if (buyNow) {
            buyNow.addEventListener('click', () => {
                const addBtn = document.querySelector('.add-cart-btn:not(.disabled)');
                if (!addBtn) return;
                addBtn.dataset.quantity = qtyInput?.value || 1;
                addBtn.click();
            });
        }

        // Color swatch image switcher
        const swatches = document.querySelectorAll('.color-swatch');
        const mainImage = document.getElementById('product-main-image');
        if (swatches.length && mainImage) {
            swatches.forEach(swatch => {
                swatch.addEventListener('click', () => {
                    const newImage = swatch.dataset.image;
                    if (newImage) {
                        mainImage.src = newImage;
                        swatches.forEach(s => s.classList.remove('active'));
                        swatch.classList.add('active');
                    }
                });
            });
        }
    })();
    </script>
@endpush