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

            {{-- ── Left: Gallery with colors ── --}}
            <div class="product-gallery" data-reveal>
                @php
                    $colors = $product->colors;
                    $mainImage = $product->getMainImageUrlAttribute();
                @endphp
                <div class="main-image">
                    <img id="product-main-image" src="{{ $mainImage }}" alt="{{ $product->name }}">
                </div>

                @if($colors->count() > 1)
                    <div class="color-swatches">
                        <h4>{{ __('messages.colors') }}:</h4>
                        <div class="swatch-list">
                            @foreach($colors as $color)
                                @php
                                    $colorImage = $product->images->firstWhere('color_id', $color->id)?->url ?? $mainImage;
                                @endphp
                                <button type="button"
                                        class="color-swatch @if($loop->first) active @endif"
                                        data-image="{{ $colorImage }}"
                                        data-color-id="{{ $color->id }}"
                                        data-color-name="{{ $color->display_name }}">
                                    {{ $color->display_name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- ── Right: Details ── --}}
            <div class="product-details" data-reveal>
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
                    <div class="quantity-selector">
                        <label for="quantity">{{ __('messages.quantity') }}:</label>
                        <div class="quantity-controls">
                            <button class="qty-btn" id="decreaseQty" type="button" aria-label="Decrease">−</button>
                            <input type="number" id="quantity" name="quantity"
                                   value="1" min="1" max="{{ $product->stock }}">
                            <button class="qty-btn" id="increaseQty" type="button" aria-label="Increase">+</button>
                        </div>
                    </div>

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

                <div class="tab-panel active" id="tab-description" role="tabpanel">
                    @if($product->description)
                        <div class="product-description-body">{!! nl2br(e($product->description)) !!}</div>
                    @else
                        <p class="tab-empty">{{ __('messages.no_description') }}</p>
                    @endif
                </div>

                <div class="tab-panel" id="tab-reviews" role="tabpanel">
                    @if(session('success'))
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
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
                                @if($review->comment)<p>{{ $review->comment }}</p>@endif
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
                                        <textarea name="comment" rows="4" placeholder="{{ __('messages.share_your_experience') }}" required minlength="10"></textarea>
                                    </div>
                                    <button type="submit" class="btn-primary">{{ __('messages.submit_review') }}</button>
                                </form>
                            </div>
                        @else
                            <div class="alert alert-info"><i class="fas fa-info-circle"></i> {{ __('messages.already_reviewed') }}</div>
                        @endif
                    @else
                        <p class="login-to-review-message">
                            {!! __('messages.login_to_review', ['link' => '<a href="'.route('signin').'">'.__('messages.login').'</a>']) !!}
                        </p>
                    @endauth
                </div>
            </div>
        </div>

        @if($relatedProducts && $relatedProducts->count())
            <div class="related-products">
                <h2 class="section-title" data-reveal>{{ $settings['product_related_heading'] ?? __('messages.you_might_also_like') }}</h2>
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
/* your existing styles – keep as is */
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

        // Color swatch – update main image and store selected color ID for cart
        const swatches = document.querySelectorAll('.color-swatch');
        const mainImage = document.getElementById('product-main-image');
        let selectedColorId = null;
        if (swatches.length && mainImage) {
            // Initialize with the first active swatch
            const active = document.querySelector('.color-swatch.active');
            if (active) selectedColorId = active.dataset.colorId;

            swatches.forEach(swatch => {
                swatch.addEventListener('click', () => {
                    const newImage = swatch.dataset.image;
                    if (newImage) {
                        mainImage.src = newImage;
                        swatches.forEach(s => s.classList.remove('active'));
                        swatch.classList.add('active');
                        selectedColorId = swatch.dataset.colorId;
                    }
                });
            });
        }

        // Extend the Add to Cart AJAX to include the selected color_id
        const addCartBtn = document.querySelector('.add-cart-btn:not(.disabled)');
        if (addCartBtn) {
            const originalClick = addCartBtn.onclick;
            addCartBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-id');
                const productName = this.getAttribute('data-name');
                const productPrice = this.getAttribute('data-price');
                const quantity = qtyInput ? qtyInput.value : 1;

                // If product has colors but no color selected, show error
                if (swatches.length > 1 && !selectedColorId) {
                    showToast('Please select a color', true);
                    return;
                }

                fetch('/cart/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: parseInt(quantity),
                        color_id: selectedColorId || null
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(`🛍️ ${productName} added to cart`);
                        updateCartCount();
                    } else {
                        showToast(data.message || 'Failed to add item', true);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Network error, please try again', true);
                });
            });
        }

        function showToast(message, isError = false) {
            let toast = document.querySelector('.toast-notify');
            if (toast) toast.remove();
            toast = document.createElement('div');
            toast.className = 'toast-notify';
            toast.innerHTML = `<i class="fas ${isError ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2000);
        }

        function updateCartCount() {
            fetch('/cart/count', {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                const counter = document.querySelector('.cart-count');
                if (counter) counter.textContent = data.count;
            })
            .catch(error => console.error('Error updating cart count:', error));
        }
    })();
    </script>
@endpush