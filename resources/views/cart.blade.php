@extends('layouts.app')

@section('title', $settings['cart_page_title'] ?? __('messages.cart_page_title'))

@push('styles')
    @vite('resources/css/cart.css')
@endpush

@section('content')
<div class="cart-page">
    <div class="container">
        <h1 class="cart-title">{{ $settings['cart_page_title'] ?? __('messages.shopping_cart') }}</h1>

        @if(count($cart) > 0)
            <div class="cart-layout">
                <!-- Cart Items -->
                <div class="cart-items">
                    @foreach($cart as $id => $item)
                        <div class="cart-item" data-id="{{ $id }}">
                            <div class="item-image">
                                @if($item['image'])
                                    <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}">
                                @else
                                    <i class="fas fa-image"></i>
                                @endif
                            </div>
                            <div class="item-details">
                                <h3 class="item-name">{{ $item['name'] }}</h3>
                                <div class="item-price">{{ format_currency($item['price']) }}</div>
                            </div>
                            <div class="item-quantity">
                                <div class="quantity-control">
                                    <button class="qty-btn decrease" data-id="{{ $id }}">-</button>
                                    <input type="number" class="qty-input" value="{{ $item['quantity'] }}" min="1" data-id="{{ $id }}">
                                    <button class="qty-btn increase" data-id="{{ $id }}">+</button>
                                </div>
                            </div>
                            <div class="item-subtotal">
                                <span class="subtotal-value" data-price="{{ $item['price'] }}" data-qty="{{ $item['quantity'] }}">
                                    {{ format_currency($item['price'] * $item['quantity']) }}
                                </span>
                            </div>
                            <div class="item-actions">
                                <button class="remove-item" data-id="{{ $id }}" title="{{ __('messages.remove') }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h2>{{ __('messages.order_summary') }}</h2>
                    <div class="summary-row">
                        <span>{{ __('messages.subtotal') }}</span>
                        <span id="subtotal">{{ format_currency(0) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>{{ __('messages.shipping') }}</span>
                        <span>{{ __('messages.calculated_at_checkout') }}</span>
                    </div>
                    <div class="summary-row total">
                        <span>{{ __('messages.total') }}</span>
                        <span id="total">{{ format_currency(0) }}</span>
                    </div>
                    <button class="btn-clear-cart" id="clearCartBtn">{{ __('messages.clear_cart') }}</button>
                    <a href="{{ route('checkout') }}" class="btn-checkout">{{ __('messages.proceed_to_checkout') }}</a>
                </div>
            </div>
        @else
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>{{ $settings['cart_empty_message'] ?? __('messages.cart_empty') }}</p>
                <a href="{{ route('Shop') }}" class="btn-primary">{{ __('messages.continue_shopping') }}</a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/cart.js')
@endpush