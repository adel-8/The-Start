@if($products->count())

    <div class="product-grid">
        @foreach($products as $product)
            <div class="product-card">

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

                {{-- Image --}}
                <div class="product-img">
                    @if($product->image_url)
                        <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}">
                    @else
                        <i class="fa-solid fa-shirt"></i>
                    @endif
                </div>

                {{-- Info --}}
                <div class="product-info">
                    <h3 class="product-name">{{ $product->name }}</h3>

                    <div class="product-price">
                        {{ format_currency($product->price) }}
                    </div>

                    <div class="product-actions">
                        @if($product->stock > 0)
                            <button class="add-cart-btn"
                                    data-id="{{ $product->id }}"
                                    data-name="{{ $product->name }}"
                                    data-price="{{ $product->price }}">
                                {{ __('messages.add_to_cart') }}
                            </button>
                        @else
                            <button class="add-cart-btn disabled" disabled>{{ __('messages.out_of_stock') }}</button>
                        @endif

                        <button type="button"
                                class="details-btn"
                                data-slug="{{ $product->slug }}"
                                data-name="{{ $product->name }}">
                            {{ __('messages.more_details') }}
                        </button>
                    </div>
                </div>

            </div>
        @endforeach
    </div>

@else

    <div class="no-results">
        <i class="fas fa-search"></i>
        <p>{{ __('messages.no_products_found') }}</p>
        <a href="{{ route('Shop') }}" class="btn-primary">{{ __('messages.clear_filters') }}</a>
    </div>

@endif