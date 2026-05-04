@extends('layouts.app')

@section('title', __('messages.order_confirmation_title') . ' #' . $order->order_number)

@section('content')
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
                    {{ __('messages.status_'.$order->status) }}
                </span>
                <span class="payment-method">
                    <i class="fas fa-credit-card"></i> {{ __('messages.payment_method_'.$order->payment_method) }}
                </span>
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
                            <div class="item-price">${{ number_format($item->price_at_purchase, 2) }}</div>
                            <div class="item-total">${{ number_format($item->price_at_purchase * $item->quantity, 2) }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Totals --}}
                @php
                    $subtotal = $order->items->sum(fn($item) => $item->price_at_purchase * $item->quantity);
                    $discountAmount = $subtotal - $order->total_price;
                @endphp
                <div class="totals">
                    <div class="totals-row">
                        <span>{{ __('messages.subtotal') }}</span>
                        <span>${{ number_format($subtotal, 2) }}</span>
                    </div>
                    @if($discountAmount > 0)
                        <div class="totals-row discount">
                            <span>{{ __('messages.coupon_label') }} @if($order->coupon)({{ $order->coupon->code }})@endif</span>
                            <span>-${{ number_format($discountAmount, 2) }}</span>
                        </div>
                    @endif
                    <div class="totals-row total">
                        <span>{{ __('messages.total') }}</span>
                        <span>${{ number_format($order->total_price, 2) }}</span>
                    </div>
                </div>
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
                            {{ $order->shippingAddress->city }},
                            {{ $order->shippingAddress->state ?? '' }}
                            {{ $order->shippingAddress->postal_code ?? '' }}<br>
                            {{ $order->shippingAddress->country }}
                        </p>
                    @else
                        <p class="text-muted">{{ __('messages.no_shipping_address') }}</p>
                    @endif
                </div>

                <div class="address-card">
                    <h3>{{ __('messages.billing_address') }}</h3>
                    @if($order->billingAddress)
                        <p>
                            {{ $order->billingAddress->address_line1 }}<br>
                            @if($order->billingAddress->address_line2)
                                {{ $order->billingAddress->address_line2 }}<br>
                            @endif
                            {{ $order->billingAddress->city }},
                            {{ $order->billingAddress->state ?? '' }}
                            {{ $order->billingAddress->postal_code ?? '' }}<br>
                            {{ $order->billingAddress->country }}
                        </p>
                    @else
                        <p class="text-muted">{{ __('messages.billing_address_same_as_shipping') }}</p>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="actions">
                <a href="{{ route('home') }}" class="btn-primary">{{ __('messages.continue_shopping') }}</a>
            </div>
        </div>

    </div>
</div>

<style>
    /* (keep the existing CSS as is – unchanged) */
    .order-confirmation { margin: 2rem 0; }
    .container { max-width: 1200px; margin: 0 auto; padding: 0 1rem; }
    .success-header { text-align: center; margin-bottom: 2rem; }
    .success-header i { font-size: 3rem; color: #2c7a4b; margin-bottom: 0.5rem; }
    .success-header h1 { font-size: 1.8rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--color-text, #2D2A35); }
    .success-header p { color: var(--color-muted, #6F6A7A); }
    .order-card { background: var(--color-surface, #FFFDF8); border-radius: 1.5rem; box-shadow: var(--shadow-md, 0 10px 25px -5px rgba(0,0,0,0.08)); overflow: hidden; border: 1px solid var(--color-border, #E3DFD4); }
    .order-header { display: flex; justify-content: space-between; align-items: baseline; background: var(--color-primary, #645F7D); color: white; padding: 1rem 1.5rem; }
    .order-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.8; margin-right: 0.5rem; }
    .order-number { font-weight: 700; font-size: 1rem; }
    .order-date { font-weight: 500; }
    .order-status { padding: 1rem 1.5rem; background: rgba(0,0,0,0.02); border-bottom: 1px solid var(--color-border, #E3DFD4); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; }
    .status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 2rem; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
    .status-pending { background: #f0ad4e; color: #fff; }
    .status-paid, .status-processing { background: #5bc0de; color: #fff; }
    .status-shipped { background: #5cb85c; color: #fff; }
    .status-delivered { background: #2c7a4b; color: #fff; }
    .status-canceled { background: #d9534f; color: #fff; }
    .payment-method { font-size: 0.85rem; color: var(--color-muted, #6F6A7A); }
    .payment-method i { margin-right: 0.3rem; }
    .order-items { padding: 1.5rem; }
    .order-items h2 { font-size: 1.2rem; font-weight: 600; margin-bottom: 1rem; color: var(--color-text, #2D2A35); }
    .items-table { border-bottom: 1px solid var(--color-border, #E3DFD4); margin-bottom: 1rem; }
    .item-row { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-top: 1px solid var(--color-border, #E3DFD4); }
    .item-row:first-child { border-top: none; }
    .item-name { flex: 3; font-weight: 500; }
    .item-qty { flex: 1; text-align: center; color: var(--color-muted, #6F6A7A); }
    .item-price { flex: 1; text-align: right; }
    .item-total { flex: 1; text-align: right; font-weight: 600; }
    .totals { max-width: 300px; margin-left: auto; text-align: right; }
    .totals-row { display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 0.9rem; }
    .totals-row.total { font-weight: 700; font-size: 1rem; border-top: 1px solid var(--color-border, #E3DFD4); margin-top: 0.5rem; padding-top: 0.5rem; }
    .totals-row.discount { color: #2c7a4b; }
    .address-section { display: flex; flex-wrap: wrap; gap: 1.5rem; padding: 1.5rem; background: #faf9f7; border-top: 1px solid var(--color-border, #E3DFD4); border-bottom: 1px solid var(--color-border, #E3DFD4); }
    .address-card { flex: 1; min-width: 200px; }
    .address-card h3 { font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--color-primary, #645F7D); }
    .address-card p { font-size: 0.85rem; line-height: 1.5; color: var(--color-text, #2D2A35); }
    .text-muted { color: var(--color-muted, #6F6A7A); }
    .actions { padding: 1.5rem; text-align: center; }
    .btn-primary { display: inline-block; background: var(--color-primary, #645F7D); color: white; border: none; padding: 0.8rem 2rem; border-radius: 60px; font-weight: 600; font-size: 1rem; text-decoration: none; transition: all 0.2s; }
    .btn-primary:hover { background: var(--color-primary-hover, #4E3B64); transform: translateY(-2px); }
    @media (max-width: 768px) { .item-row { flex-wrap: wrap; } .item-name { flex: 1 0 100%; margin-bottom: 0.5rem; } .item-qty, .item-price, .item-total { flex: 1; text-align: left; } .address-section { flex-direction: column; } .order-header { flex-direction: column; gap: 0.25rem; } }

    /* ===== Order Confirmation Styles ===== */
    .order-confirmation {
        margin: 2rem 0;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
    }

    .success-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    .success-header i {
        font-size: 3rem;
        color: #2c7a4b;
        margin-bottom: 0.5rem;
    }
    .success-header h1 {
        font-size: 1.8rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--color-text, #2D2A35);
    }
    .success-header p {
        color: var(--color-muted, #6F6A7A);
    }

    .order-card {
        background: var(--color-surface, #FFFDF8);
        border-radius: 1.5rem;
        box-shadow: var(--shadow-md, 0 10px 25px -5px rgba(0,0,0,0.08));
        overflow: hidden;
        border: 1px solid var(--color-border, #E3DFD4);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        background: var(--color-primary, #645F7D);
        color: white;
        padding: 1rem 1.5rem;
    }
    .order-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.8;
        margin-right: 0.5rem;
    }
    .order-number {
        font-weight: 700;
        font-size: 1rem;
    }
    .order-date {
        font-weight: 500;
    }

    .order-status {
        padding: 1rem 1.5rem;
        background: rgba(0,0,0,0.02);
        border-bottom: 1px solid var(--color-border, #E3DFD4);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .status-pending {
        background: #f0ad4e;
        color: #fff;
    }
    .status-paid,
    .status-processing {
        background: #5bc0de;
        color: #fff;
    }
    .status-shipped {
        background: #5cb85c;
        color: #fff;
    }
    .status-delivered {
        background: #2c7a4b;
        color: #fff;
    }
    .status-canceled {
        background: #d9534f;
        color: #fff;
    }
    .payment-method {
        font-size: 0.85rem;
        color: var(--color-muted, #6F6A7A);
    }
    .payment-method i {
        margin-right: 0.3rem;
    }

    .order-items {
        padding: 1.5rem;
    }
    .order-items h2 {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--color-text, #2D2A35);
    }
    .items-table {
        border-bottom: 1px solid var(--color-border, #E3DFD4);
        margin-bottom: 1rem;
    }
    .item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-top: 1px solid var(--color-border, #E3DFD4);
    }
    .item-row:first-child {
        border-top: none;
    }
    .item-name {
        flex: 3;
        font-weight: 500;
    }
    .item-qty {
        flex: 1;
        text-align: center;
        color: var(--color-muted, #6F6A7A);
    }
    .item-price {
        flex: 1;
        text-align: right;
    }
    .item-total {
        flex: 1;
        text-align: right;
        font-weight: 600;
    }
    .totals {
        max-width: 300px;
        margin-left: auto;
        text-align: right;
    }
    .totals-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        font-size: 0.9rem;
    }
    .totals-row.total {
        font-weight: 700;
        font-size: 1rem;
        border-top: 1px solid var(--color-border, #E3DFD4);
        margin-top: 0.5rem;
        padding-top: 0.5rem;
    }
    .totals-row.discount {
        color: #2c7a4b;
    }

    .address-section {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        padding: 1.5rem;
        background: #faf9f7;
        border-top: 1px solid var(--color-border, #E3DFD4);
        border-bottom: 1px solid var(--color-border, #E3DFD4);
    }
    .address-card {
        flex: 1;
        min-width: 200px;
    }
    .address-card h3 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--color-primary, #645F7D);
    }
    .address-card p {
        font-size: 0.85rem;
        line-height: 1.5;
        color: var(--color-text, #2D2A35);
    }
    .text-muted {
        color: var(--color-muted, #6F6A7A);
    }

    .actions {
        padding: 1.5rem;
        text-align: center;
    }
    .btn-primary {
        display: inline-block;
        background: var(--color-primary, #645F7D);
        color: white;
        border: none;
        padding: 0.8rem 2rem;
        border-radius: 60px;
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-primary:hover {
        background: var(--color-primary-hover, #4E3B64);
        transform: translateY(-2px);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .item-row {
            flex-wrap: wrap;
        }
        .item-name {
            flex: 1 0 100%;
            margin-bottom: 0.5rem;
        }
        .item-qty, .item-price, .item-total {
            flex: 1;
            text-align: left;
        }
        .address-section {
            flex-direction: column;
        }
        .order-header {
            flex-direction: column;
            gap: 0.25rem;
        }
    }
</style>
@endsection