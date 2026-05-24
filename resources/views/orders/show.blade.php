@extends('layouts.app')

@section('title', __('messages.order_confirmation_title') . ' #' . $order->order_number)

@section('content')

@if(session('success'))
    <div class="alert alert-success" style="max-width:1200px; margin:1rem auto; padding:0.75rem 1rem; background:rgba(16,185,129,0.1); border:1px solid #10B981; border-radius:0.5rem; color:#0B5E42;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div class="order-confirmation">
    <div class="container">

        {{-- Success header --}}
        <div class="success-header">
            <i class="fas fa-check-circle"></i>
            <h1>{{ __('messages.thank_you_for_order') }}</h1>
            <p>{{ __('messages.order_placed_successfully') }}</p>
        </div>

        {{-- Order summary card --}}
        <div class="order-card">
            <div class="order-header">
                <div>
                    <span class="order-label">{{ __('messages.order_number_label') }}</span>
                    <span class="order-number">{{ $order->order_number }}</span>
                </div>
                <div>
                    <span class="order-label">{{ __('messages.placed_on_label') }}</span>
                    <span class="order-date">{{ $order->created_at->format('F j, Y') }}</span>
                </div>
            </div>

            <div class="order-status">
                <span class="status-badge status-{{ strtolower($order->status) }}">
                    {{ __('messages.status_' . $order->status) }}
                </span>
                <span class="payment-method">
                    <i class="fas fa-credit-card"></i>
                    {{ __('messages.payment_method_' . $order->payment_method) }}
                </span>
                {{-- Delivery Type --}}
                @if($order->delivery_type)
                    <span class="delivery-type-badge">
                        @if($order->delivery_type === 'home')
                            <i class="fas fa-home"></i>
                            {{ __('messages.delivery_home') ?? 'توصيل للمنزل' }}
                        @else
                            <i class="fas fa-building"></i>
                            {{ __('messages.delivery_bureau') ?? 'استلام من المكتب' }}
                        @endif
                    </span>
                @endif
            </div>

            {{-- Order items --}}
            <div class="order-items">
                <h2>{{ __('messages.order_items') }}</h2>
                <div class="items-table">
                    @foreach($order->items as $item)
                        <div class="item-row">
                            <div class="item-name">
                                {{ $item->product?->name ?? __('messages.product_unavailable') }}
                            </div>
                            <div class="item-qty">× {{ $item->quantity }}</div>
                            <div class="item-price">{{ format_currency($item->price_at_purchase) }}</div>
                            <div class="item-total">{{ format_currency($item->price_at_purchase * $item->quantity) }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Totals --}}
                @php
                    $subtotal       = $order->items->sum(fn($item) => $item->price_at_purchase * $item->quantity);
                    $discountAmount = $subtotal + ($order->shipping_cost ?? 0) - $order->total_price;
                @endphp

                <div class="totals">
                    <div class="totals-row">
                        <span>{{ __('messages.subtotal') }}</span>
                        <span>{{ format_currency($subtotal) }}</span>
                    </div>
                    @if($discountAmount > 0)
                        <div class="totals-row discount">
                            <span>
                                {{ __('messages.coupon_label') }}
                                @if($order->coupon)({{ $order->coupon->code }})@endif
                            </span>
                            <span>-{{ format_currency($discountAmount) }}</span>
                        </div>
                    @endif
                    @if(($order->shipping_cost ?? 0) > 0)
                        <div class="totals-row">
                            <span>{{ __('messages.shipping') }}</span>
                            <span>{{ format_currency($order->shipping_cost) }}</span>
                        </div>
                    @endif
                    <div class="totals-row total">
                        <span><strong>{{ __('messages.total') }}</strong></span>
                        <span><strong>{{ format_currency($order->total_price) }}</strong></span>
                    </div>
                </div>

                {{-- Order notes --}}
                @if($order->notes)
                    <div class="order-notes">
                        <strong>{{ __('messages.order_notes') }}:</strong>
                        <p>{{ $order->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Addresses --}}
            <div class="address-section">
                <div class="address-card">
                    <h3>{{ __('messages.shipping_address') }}</h3>
                    @if($order->shippingAddress)
                        <p>
                            {{ $order->shippingAddress->address_line1 }}<br>
                            @if($order->shippingAddress->address_line2)
                                {{ $order->shippingAddress->address_line2 }}<br>
                            @endif
                            {{ $order->shippingAddress->city }}
                            @if($order->shippingAddress->state), {{ $order->shippingAddress->state }}@endif
                            @if($order->shippingAddress->postal_code) {{ $order->shippingAddress->postal_code }}@endif
                            <br>{{ $order->shippingAddress->country }}
                        </p>
                    @else
                        <p class="text-muted">{{ __('messages.no_shipping_address') }}</p>
                    @endif
                </div>

                <div class="address-card">
                    <h3>{{ __('messages.billing_address') }}</h3>
                    @if($order->billingAddress && $order->billing_address_id !== $order->shipping_address_id)
                        <p>
                            {{ $order->billingAddress->address_line1 }}<br>
                            @if($order->billingAddress->address_line2)
                                {{ $order->billingAddress->address_line2 }}<br>
                            @endif
                            {{ $order->billingAddress->city }}
                            @if($order->billingAddress->state), {{ $order->billingAddress->state }}@endif
                            @if($order->billingAddress->postal_code) {{ $order->billingAddress->postal_code }}@endif
                            <br>{{ $order->billingAddress->country }}
                        </p>
                    @else
                        <p class="text-muted">{{ __('messages.billing_address_same_as_shipping') }}</p>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="actions">
                <a href="{{ route('home') }}" class="btn-primary">
                    {{ __('messages.continue_shopping') }}
                </a>
                @auth
                    <a href="{{ route('orders.index') }}" class="btn-secondary" style="margin-left:0.5rem;">
                        {{ __('messages.view_all_orders') }}
                    </a>
                @endauth
            </div>
        </div>

    </div>
</div>

<style>
    .order-confirmation { margin: 2rem 0; }
    .container { max-width: 1200px; margin: 0 auto; padding: 0 1rem; }
    .success-header { text-align: center; margin-bottom: 2rem; }
    .success-header i { font-size: 3rem; color: #2c7a4b; display: block; margin-bottom: 0.5rem; }
    .success-header h1 { font-size: 1.8rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--color-text, #2D2A35); }
    .success-header p { color: var(--color-muted, #6F6A7A); }
    .order-card { background: var(--color-surface, #FFFDF8); border-radius: 1.5rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08); overflow: hidden; border: 1px solid var(--color-border, #E3DFD4); }
    .order-header { display: flex; justify-content: space-between; align-items: baseline; background: var(--color-primary, #645F7D); color: white; padding: 1rem 1.5rem; flex-wrap: wrap; gap: 0.5rem; }
    .order-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.8; margin-right: 0.5rem; }
    .order-number { font-weight: 700; font-size: 1rem; }
    .order-date { font-weight: 500; }
    .order-status { padding: 1rem 1.5rem; background: rgba(0,0,0,0.02); border-bottom: 1px solid var(--color-border, #E3DFD4); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; }
    .status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 2rem; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
    .status-pending { background: #f0ad4e; color: #fff; }
    .status-processing { background: #5bc0de; color: #fff; }
    .status-shipped { background: #5cb85c; color: #fff; }
    .status-delivered { background: #2c7a4b; color: #fff; }
    .status-canceled { background: #d9534f; color: #fff; }
    .payment-method { font-size: 0.85rem; color: var(--color-muted, #6F6A7A); }
    .payment-method i { margin-right: 0.3rem; }

    /* Delivery type badge */
    .delivery-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--color-primary, #645F7D);
        background: rgba(100,95,125,0.08);
        padding: 0.25rem 0.75rem;
        border-radius: 2rem;
        border: 1px solid rgba(100,95,125,0.2);
    }
    .delivery-type-badge i { font-size: 0.8rem; }

    .order-items { padding: 1.5rem; }
    .order-items h2 { font-size: 1.2rem; font-weight: 600; margin-bottom: 1rem; color: var(--color-text, #2D2A35); }
    .items-table { border-bottom: 1px solid var(--color-border, #E3DFD4); margin-bottom: 1rem; }
    .item-row { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-top: 1px solid var(--color-border, #E3DFD4); }
    .item-row:first-child { border-top: none; }
    .item-name { flex: 3; font-weight: 500; }
    .item-qty { flex: 1; text-align: center; color: var(--color-muted, #6F6A7A); }
    .item-price { flex: 1; text-align: right; }
    .item-total { flex: 1; text-align: right; font-weight: 600; }
    .totals { max-width: 300px; margin-left: auto; }
    .totals-row { display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 0.9rem; }
    .totals-row.total { font-weight: 700; font-size: 1rem; border-top: 1px solid var(--color-border, #E3DFD4); margin-top: 0.5rem; padding-top: 0.5rem; }
    .totals-row.discount { color: #2c7a4b; }
    .order-notes { margin-top: 1rem; padding: 1rem; background: #f8f9fb; border-radius: 0.5rem; border: 1px solid #e9ecf0; }
    .order-notes p { margin-top: 0.25rem; }
    .address-section { display: flex; flex-wrap: wrap; gap: 1.5rem; padding: 1.5rem; background: #faf9f7; border-top: 1px solid var(--color-border, #E3DFD4); border-bottom: 1px solid var(--color-border, #E3DFD4); }
    .address-card { flex: 1; min-width: 200px; }
    .address-card h3 { font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--color-primary, #645F7D); }
    .address-card p { font-size: 0.85rem; line-height: 1.5; color: var(--color-text, #2D2A35); }
    .text-muted { color: var(--color-muted, #6F6A7A); }
    .actions { padding: 1.5rem; text-align: center; display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap; }
    .btn-primary { display: inline-block; background: var(--color-primary, #645F7D); color: white; border: none; padding: 0.8rem 2rem; border-radius: 60px; font-weight: 600; font-size: 1rem; text-decoration: none; transition: all 0.2s; }
    .btn-primary:hover { background: var(--color-primary-hover, #4E3B64); transform: translateY(-2px); }
    .btn-secondary { display: inline-block; background: transparent; color: var(--color-primary, #645F7D); border: 2px solid var(--color-primary, #645F7D); padding: 0.8rem 2rem; border-radius: 60px; font-weight: 600; font-size: 1rem; text-decoration: none; transition: all 0.2s; }
    .btn-secondary:hover { background: var(--color-primary, #645F7D); color: white; }
    @media (max-width: 768px) {
        .item-row { flex-wrap: wrap; }
        .item-name { flex: 1 0 100%; margin-bottom: 0.5rem; }
        .item-qty, .item-price, .item-total { flex: 1; text-align: left; }
        .address-section { flex-direction: column; }
        .order-header { flex-direction: column; gap: 0.25rem; }
        .totals { max-width: 100%; }
        .order-status { flex-direction: column; align-items: flex-start; }
    }
</style>
@endsection