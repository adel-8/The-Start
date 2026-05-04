<div class="product-card">
    @php $stock = $product->stock ?? 0; @endphp

    {{-- Image wrapper with badges overlay (scoped to product-card) --}}
    <div class="product-card__image-wrapper">
        <div class="product-img">
            @if($product->image_url)
                <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}">
            @else
                <i class="fa-solid fa-shirt"></i>
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

    {{-- Product info (rating now inside) --}}
    <div class="product-info">
        @php $avgRating = $product->averageRating(); @endphp
        @if($avgRating)
            <div class="product-card__rating">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= round($avgRating))
                        ★
                    @else
                        ☆
                    @endif
                @endfor
                ({{ number_format($avgRating, 1) }})
            </div>
        @endif

        <h3 class="product-name">{{ $product->name }}</h3>
        <div class="product-price">{{ format_currency($product->price) }}</div>

        <div class="product-actions">
            @if($stock > 0)
                <button class="add-cart-btn" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price }}">
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