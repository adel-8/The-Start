@extends('layouts.app')

@section('title', $product->name . ' - ' . ($settings['site_name'] ?? config('app.name', 'The Start')))

@push('styles')
    @vite('resources/css/product.css')
@endpush

@section('content')
<div class="product-page">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb" aria-label="breadcrumb">
            <a href="{{ route('/') }}">{{ __('messages.home') }}</a>
            <span class="separator">/</span>
            <a href="{{ route('Shop') }}">{{ __('messages.shop') }}</a>
            <span class="separator">/</span>
            <span class="current">{{ $product->name }}</span>
        </nav>

        <div class="product-layout">
            <!-- Left: Image -->
            <div class="product-gallery">
                <div class="main-image">
                    @if($product->image_url)
                        <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}">
                    @else
                        <div class="no-image">
                            <i class="fas fa-image"></i>
                            <span>{{ __('messages.no_image') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right: Details -->
            <div class="product-details">
                <!-- Badges -->
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

                <div class="product-price">
                    {{ format_currency($product->price) }}
                </div>

                <div class="product-description">
                    {{ $product->description ?? __('messages.no_description') }}
                </div>

                <!-- Stock status -->
                @php
                    $inStock = ($product->stock ?? 0) > 0 && $product->status === 'active';
                @endphp
                <div class="stock-status">
                    @if($inStock)
                        <span class="in-stock">{{ __('messages.in_stock') }}</span>
                    @else
                        <span class="out-of-stock">{{ __('messages.out_of_stock') }}</span>
                    @endif
                </div>

                @if($inStock)
                    <!-- Quantity selector -->
                    <div class="quantity-selector">
                        <label for="quantity">{{ __('messages.quantity') }}:</label>
                        <div class="quantity-controls">
                            <button class="qty-btn" id="decreaseQty" type="button">-</button>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ $product->stock }}">
                            <button class="qty-btn" id="increaseQty" type="button">+</button>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="action-buttons">
                        <button class="add-cart-btn" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price }}">
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

            <!-- Reviews Section -->
            <div class="reviews-section">
                <h3>{{ $settings['product_reviews_heading'] ?? __('messages.customer_reviews') }}</h3>

                <!-- Display flash messages -->
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

                @php
                    $avgRating = $product->averageRating(); // must be defined in Product model
                @endphp
                @if($avgRating)
                    <div class="average-rating">
                        <span>{{ __('messages.average_rating') }}: </span>
                        <div class="stars">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= round($avgRating))
                                    ★
                                @else
                                    ☆
                                @endif
                            @endfor
                            ({{ number_format($avgRating, 1) }})
                        </div>
                    </div>
                @endif

                <div class="reviews-list">
                    @forelse($product->approvedReviews()->latest()->get() as $review)
                        <div class="review-item">
                            <div class="review-header">
                                <strong>{{ $review->user->name ?? $review->user->username }}</strong>
                                <div class="stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $review->rating)
                                            ★
                                        @else
                                            ☆
                                        @endif
                                    @endfor
                                </div>
                                <small>{{ $review->created_at->format('M d, Y') }}</small>
                            </div>
                            <p>{{ $review->comment }}</p>
                        </div>
                    @empty
                        <p>{{ __('messages.no_reviews_yet') }}</p>
                    @endforelse
                </div>

                @auth
                    @php
                        $userReview = $product->reviews()->where('user_id', auth()->id())->first();
                    @endphp
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
                                    <label>{{ __('messages.comment_optional') }}</label>
                                    <textarea name="comment" rows="4"></textarea>
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
                    <p>{!! __('messages.login_to_review', ['link' => '<a href="'.route('signin').'">'.__('messages.login').'</a>']) !!}</p>
                @endauth
            </div>
        </div>

        <!-- Related Products -->
        @if($relatedProducts && $relatedProducts->count())
            <div class="related-products">
                <h2 class="section-title">{{ $settings['product_related_heading'] ?? __('messages.you_might_also_like') }}</h2>
                <div class="product-grid">
                    @foreach($relatedProducts as $related)
                        <div class="product-card">
                            <a href="{{ route('product.show', $related->slug) }}" class="product-link">
                                <div class="product-img">
                                    @if($related->image_url)
                                        <img src="{{ asset($related->image_url) }}" alt="{{ $related->name }}">
                                    @else
                                        <i class="fa-solid fa-shirt"></i>
                                    @endif
                                </div>
                                <div class="product-info">
                                    <h3 class="product-name">{{ $related->name }}</h3>
                                    <div class="product-price">{{ format_currency($related->price) }}</div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/product.js')
@endpush