<div class="product-card" data-reveal>
    @php $stock = $product->stock ?? 0; @endphp

    <a href="{{ route('product.show', $product->slug) }}" class="product-card__image-link">
        <div class="product-card__image-wrapper">
            <div class="product-img">
                @if($product->image_url)
                    <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}" loading="lazy">
                @else
                    <i class="fa-solid fa-clock"></i>
                @endif
            </div>

            {{-- Hover overlay — slides up from bottom --}}
            <div class="product-card__hover-overlay">
                @if($stock > 0)
                    <button
                        class="add-cart-btn overlay-cart-btn"
                        data-id="{{ $product->id }}"
                        data-name="{{ $product->name }}"
                        data-price="{{ $product->price }}"
                        aria-label="{{ __('messages.add_to_cart') }}: {{ $product->name }}">
                        <i class="fas fa-shopping-bag"></i>
                        {{ __('messages.add_to_cart') }}
                    </button>
                @else
                    <span class="overlay-out-of-stock">{{ __('messages.out_of_stock') }}</span>
                @endif
            </div>

            <div class="product-card__badges">
                @if($product->is_new)
                    <span class="badge badge-new">{{ __('messages.new') }}</span>
                @endif
                @if($product->bestseller)
                    <span class="badge badge-bestseller">{{ __('messages.best_seller') }}</span>
                @endif
                @if($stock <= 0)
                    <span class="badge badge-danger">{{ __('messages.out_of_stock') }}</span>
                @endif
            </div>
        </div>
    </a>

    <div class="product-info">
        @php $avgRating = $product->averageRating(); @endphp
        @if($avgRating)
            <div class="product-card__rating">
                @for($i = 1; $i <= 5; $i++)
                    <span class="star {{ $i <= round($avgRating) ? 'star-filled' : 'star-empty' }}">
                        {{ $i <= round($avgRating) ? '★' : '☆' }}
                    </span>
                @endfor
                <span class="rating-count">({{ number_format($avgRating, 1) }})</span>
            </div>
        @endif

        <a href="{{ route('product.show', $product->slug) }}" class="product-name-link">
            <h3 class="product-name">{{ $product->name }}</h3>
        </a>

        <div class="product-price">{{ format_currency($product->price) }}</div>

        {{-- Desktop fallback actions (visible when not hovering on touch devices) --}}
        <div class="product-actions product-actions--fallback">
            @if($stock > 0)
                <button class="add-cart-btn"
                        data-id="{{ $product->id }}"
                        data-name="{{ $product->name }}"
                        data-price="{{ $product->price }}">
                    {{ __('messages.add_to_cart') }}
                </button>
            @else
                <button class="add-cart-btn disabled" disabled>{{ __('messages.out_of_stock') }}</button>
            @endif
            <button type="button" class="details-btn" data-slug="{{ $product->slug }}" data-name="{{ $product->name }}">
                {{ __('messages.more_details') }}
            </button>
        </div>
    </div>
</div>

@once
@push('styles')
<style>
/* ── Product card: hover lift + image zoom ── */
.product-card {
    position: relative;
    overflow: hidden;
    border-radius: 12px;
    transition: box-shadow .3s ease, transform .3s ease;
    background: var(--color-surface, #fff);
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 18px 44px rgba(0,0,0,0.13);
}
.product-card__image-wrapper {
    position: relative;
    overflow: hidden;
}
.product-card__image-wrapper .product-img img {
    transition: transform .55s ease;
    width: 100%;
    display: block;
}
.product-card:hover .product-card__image-wrapper .product-img img {
    transform: scale(1.07);
}

/* ── Hover overlay — slides up ── */
.product-card__hover-overlay {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    background: rgba(10,10,10,0.86);
    padding: 14px 16px;
    transform: translateY(100%);
    transition: transform .32s cubic-bezier(.22,.68,0,1.2);
    display: flex;
    align-items: center;
    justify-content: center;
}
.product-card:hover .product-card__hover-overlay {
    transform: translateY(0);
}

.overlay-cart-btn {
    background: transparent;
    border: 1.5px solid var(--gold, #C9A96E);
    color: var(--gold, #C9A96E);
    padding: 9px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background .2s, color .2s;
}
.overlay-cart-btn:hover {
    background: var(--gold, #C9A96E);
    color: #fff;
}
.overlay-out-of-stock {
    color: #ef4444;
    font-size: 13px;
    font-weight: 500;
}

/* ── Rating stars gold ── */
.product-card__rating {
    display: flex;
    align-items: center;
    gap: 2px;
    margin-bottom: 4px;
    font-size: 14px;
}
.product-card__rating .star-filled { color: var(--gold, #C9A96E); }
.product-card__rating .star-empty  { color: #d1d5db; }
.rating-count { font-size: 11px; color: #6b7280; margin-left: 3px; }

/* ── Hide fallback actions on true hover devices ── */
@media (hover: hover) {
    .product-actions--fallback { display: none; }
}
/* Touch devices: always show fallback */
@media (hover: none) {
    .product-card__hover-overlay { transform: translateY(0); }
}

/* ── Badge gold for "new" ── */
.badge-new {
    background: var(--gold, #C9A96E);
    color: #fff;
}
</style>
@endpush
@endonce