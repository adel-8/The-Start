@extends('layouts.app')

@section('title', $settings['cart_page_title'] ?? __('messages.cart_page_title'))

@push('styles')
    @vite('resources/css/cart.css')
    <style>
        /* ── Out-of-stock cart item ── */
        .cart-item.oos {
            opacity: 0.75;
            border-left: 4px solid #dc3545;
            background: #fff5f5;
        }
        .oos-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #dc3545;
            color: white;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 20px;
            margin-top: 4px;
        }
        .low-stock-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #fd7e14;
            color: white;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 20px;
            margin-top: 4px;
        }
        .stock-error-box {
            background: #fff3f3;
            border: 1px solid #f5c6cb;
            border-radius: 10px;
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
        }
        .stock-error-box p { margin: 0 0 0.5rem; font-weight: 600; color: #842029; }
        .stock-error-box ul { margin: 0; padding-left: 1.2rem; color: #842029; font-size: 0.9rem; }
        .btn-checkout.disabled-checkout,
        .btn-checkout[disabled] {
            opacity: 0.45;
            cursor: not-allowed;
            pointer-events: none;
        }
        .oos-notice {
            font-size: 0.82rem;
            color: #dc3545;
            font-weight: 500;
            margin-top: 8px;
        }
    </style>
@endpush

@section('content')
<div class="cart-page">
    <div class="container">
        <h1 class="cart-title">{{ $settings['cart_page_title'] ?? __('messages.shopping_cart') }}</h1>

        {{-- ── Stock issue banner (set by CheckoutController redirect) ── --}}
        @if(session('stock_errors'))
            <div class="stock-error-box">
                <p><i class="fas fa-exclamation-triangle"></i> {{ __('messages.cart_has_stock_issues') }}</p>
                <ul>
                    @foreach(session('stock_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if(count($cart) > 0)
            @php
                // Determine if ANY item is out of stock → block checkout
                $hasOosItem = collect($cart)->contains(fn($item) => $item['out_of_stock'] ?? false);
            @endphp

            <div class="cart-layout">
                <!-- Cart Items -->
                <div class="cart-items">
                    @foreach($cart as $id => $item)
                        @php
                            $isOos      = $item['out_of_stock'] ?? false;
                            $isLowStock = $item['low_stock']    ?? false;
                        @endphp
                        <div class="cart-item {{ $isOos ? 'oos' : '' }}" data-id="{{ $id }}">
                            <div class="item-image">
                                @if($item['image'])
                                    <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}">
                                @else
                                    <i class="fas fa-image"></i>
                                @endif
                            </div>
                            <div class="item-details">
                                <h3 class="item-name">{{ $item['name'] }}</h3>
                                @if(!empty($item['color_name']))
                                    <small class="item-color">
                                        <i class="fas fa-circle" style="font-size:9px"></i>
                                        {{ $item['color_name'] }}
                                    </small>
                                @endif
                                <div class="item-price">{{ format_currency($item['price']) }}</div>

                                {{-- Stock status badges --}}
                                @if($isOos)
                                    <span class="oos-badge">
                                        <i class="fas fa-times-circle"></i>
                                        {{ __('messages.out_of_stock') }}
                                    </span>
                                    <p class="oos-notice">{{ __('messages.remove_oos_to_checkout') }}</p>
                                @elseif($isLowStock)
                                    <span class="low-stock-badge">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ __('messages.only_x_left', ['count' => $item['live_stock']]) }}
                                    </span>
                                @endif
                            </div>

                            <div class="item-quantity">
                                @if(!$isOos)
                                    <div class="quantity-control">
                                        <button class="qty-btn decrease" data-id="{{ $id }}">-</button>
                                        <input type="number" class="qty-input"
                                               value="{{ $item['quantity'] }}"
                                               min="1"
                                               max="{{ $item['live_stock'] }}"
                                               data-id="{{ $id }}">
                                        <button class="qty-btn increase"
                                                data-id="{{ $id }}"
                                                data-max="{{ $item['live_stock'] }}">+</button>
                                    </div>
                                @endif
                            </div>

                            <div class="item-subtotal">
                                @if(!$isOos)
                                    <span class="subtotal-value"
                                          data-price="{{ $item['price'] }}"
                                          data-qty="{{ $item['quantity'] }}">
                                        {{ format_currency($item['price'] * $item['quantity']) }}
                                    </span>
                                @else
                                    <span style="color:#dc3545">—</span>
                                @endif
                            </div>

                            <div class="item-actions">
                                <button class="remove-item" data-id="{{ $id }}"
                                        title="{{ __('messages.remove') }}">
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

                    @if($hasOosItem)
                        <div class="stock-error-box" style="margin-bottom: 1rem;">
                            <p style="font-size:0.85rem; margin:0;">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ __('messages.remove_oos_to_checkout') }}
                            </p>
                        </div>
                    @endif

                    <button class="btn-clear-cart" id="clearCartBtn">
                        {{ __('messages.clear_cart') }}
                    </button>

                    <a href="{{ route('checkout') }}"
                       class="btn-checkout {{ $hasOosItem ? 'disabled-checkout' : '' }}"
                       {{ $hasOosItem ? 'aria-disabled=true' : '' }}>
                        {{ __('messages.proceed_to_checkout') }}
                    </a>
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