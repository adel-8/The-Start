@extends('admin.layouts.app')

@section('title', __('admin.order') . ' #' . $order->order_number)

@section('content')
<div class="admin-header">
    <h1>{{ __('admin.order') }} #{{ $order->order_number }}</h1>
    <a href="{{ route('admin.orders.index') }}" class="btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> {{ __('admin.back_to_orders') }}
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="order-details">

    {{-- Customer Information --}}
    <div class="order-info-card">
        <h3>{{ __('admin.customer_information') }}</h3>
        <p><strong>{{ __('admin.name') }}:</strong> {{ $order->user ? $order->user->name : $order->guest_name }}</p>
        <p><strong>{{ __('admin.email') }}:</strong> {{ $order->user ? $order->user->email : ($order->guest_email ?? __('admin.not_provided')) }}</p>
        <p><strong>{{ __('admin.phone') }}:</strong>
            @if($order->guest_phone)
                {{ $order->guest_phone }}
            @elseif($order->user && $order->user->phone)
                {{ $order->user->phone }}
            @else
                {{ __('admin.not_provided') }}
            @endif
        </p>
    </div>

    {{-- Shipping Address --}}
    <div class="order-info-card">
        <h3>{{ __('admin.shipping_address') }}</h3>
        @if($order->shippingAddress)
            <p>
                {{ $order->shippingAddress->address_line1 }}<br>
                @if($order->shippingAddress->address_line2)
                    {{ $order->shippingAddress->address_line2 }}<br>
                @endif
                {{ $order->shippingAddress->city }}
                @if($order->shippingAddress->state), {{ $order->shippingAddress->state }}@endif
                {{ $order->shippingAddress->postal_code ?? '' }}<br>
                {{ $order->shippingAddress->country }}
            </p>
        @else
            <p>{{ __('admin.no_address_provided') }}</p>
        @endif
    </div>

    {{-- Delivery Type --}}
    <div class="order-info-card">
        <h3>{{ __('admin.delivery_type') ?? 'نوع التوصيل' }}</h3>
        @if($order->delivery_type === 'bureau')
            <p class="delivery-type-display delivery-bureau">
                <i class="fas fa-building"></i>
                {{ __('messages.delivery_bureau') ?? 'استلام من المكتب' }}
            </p>
        @else
            <p class="delivery-type-display delivery-home">
                <i class="fas fa-home"></i>
                {{ __('messages.delivery_home') ?? 'توصيل للمنزل' }}
            </p>
        @endif
    </div>

    {{-- Order Notes --}}
    @if($order->notes)
        <div class="order-info-card">
            <h3>{{ __('messages.order_notes') }}</h3>
            <p>{{ $order->notes }}</p>
        </div>
    @endif

    {{-- Order Status Update Form --}}
    <div class="order-info-card">
        <h3>{{ __('admin.order_status') }}</h3>
        <form action="{{ route('admin.orders.update', $order) }}" method="POST" id="orderStatusForm">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="status">{{ __('admin.order_status') }}</label>
                <select name="status" id="status">
                    <option value="pending"    {{ $order->status == 'pending'    ? 'selected' : '' }}>{{ __('status.pending') }}</option>
                    <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>{{ __('status.processing') }}</option>
                    <option value="shipped"    {{ $order->status == 'shipped'    ? 'selected' : '' }}>{{ __('status.shipped') }}</option>
                    <option value="delivered"  {{ $order->status == 'delivered'  ? 'selected' : '' }}>{{ __('status.delivered') }}</option>
                    <option value="canceled"   {{ $order->status == 'canceled'   ? 'selected' : '' }}>{{ __('status.canceled') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label for="payment_status">{{ __('admin.payment_status') }}</label>
                <select name="payment_status" id="payment_status">
                    <option value="pending"  {{ $order->payment_status == 'pending'  ? 'selected' : '' }}>{{ __('admin.pending') }}</option>
                    <option value="paid"     {{ $order->payment_status == 'paid'     ? 'selected' : '' }}>{{ __('admin.paid') }}</option>
                    <option value="failed"   {{ $order->payment_status == 'failed'   ? 'selected' : '' }}>{{ __('admin.failed') }}</option>
                    <option value="refunded" {{ $order->payment_status == 'refunded' ? 'selected' : '' }}>{{ __('admin.refunded') }}</option>
                </select>
            </div>

            {{-- Payment Proof --}}
            @if($order->payment_proof)
                <div class="form-group">
                    <label>{{ __('admin.payment_proof') }}</label>
                    <div class="payment-proof">
                        @php
                            $proofPath = ltrim($order->payment_proof, '/');
                            $proofRoute = route('admin.orders.proof', $order);
                            $ext       = pathinfo($proofPath, PATHINFO_EXTENSION);
                            $isImage   = in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp','webp']);
                        @endphp
                        @if($isImage)
                            <a href="{{ $proofRoute }}" target="_blank">
                                <img src="{{ $proofRoute }}" class="proof-image" alt="{{ __('admin.payment_proof') }}">
                            </a>
                        @else
                            <a href="{{ $proofRoute }}" target="_blank" class="btn-sm btn-primary">
                                <i class="fas fa-file-pdf"></i> {{ __('admin.view_proof') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Accept/Reject for BaridiMob --}}
            @if($order->payment_method == 'baridimob' && $order->payment_proof && $order->payment_status == 'pending')
                <div class="form-group payment-actions">
                    <label>{{ __('admin.verify_payment') }}</label>
                    <div class="action-buttons">
                        <form action="{{ route('admin.orders.payment.update', $order) }}" method="POST" style="display:inline-block;">
                            @csrf
                            <input type="hidden" name="payment_status" value="paid">
                            <button type="submit" class="btn-sm btn-success"
                                    onclick="return confirm('{{ __('admin.confirm_accept_payment') }}')">
                                <i class="fas fa-check"></i> {{ __('admin.accept_payment') }}
                            </button>
                        </form>
                        <form action="{{ route('admin.orders.payment.update', $order) }}" method="POST" style="display:inline-block;">
                            @csrf
                            <input type="hidden" name="payment_status" value="failed">
                            <button type="submit" class="btn-sm btn-danger"
                                    onclick="return confirm('{{ __('admin.confirm_reject_payment') }}')">
                                <i class="fas fa-times"></i> {{ __('admin.reject_payment') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="form-group">
                <label for="tracking_number">{{ __('admin.tracking_number') }}</label>
                <input type="text" name="tracking_number" id="tracking_number"
                       value="{{ $order->tracking_number }}"
                       placeholder="{{ __('admin.enter_tracking_number') }}">
            </div>
            <button type="submit" class="btn-primary"
                    onclick="return confirm('{{ __('admin.confirm_status_update') }}')">
                {{ __('admin.update_order') }}
            </button>
        </form>
    </div>

</div>

{{-- Order Items Table --}}
<h3 style="margin-bottom:1rem;">{{ __('admin.order_items') }}</h3>
<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>{{ __('admin.product') }}</th>
                <th>{{ __('admin.quantity') }}</th>
                <th>{{ __('admin.unit_price') }}</th>
                <th>{{ __('admin.total') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($order->items as $item)
                <tr>
                    <td>{{ $item->product?->name ?? __('admin.product_unavailable') }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ format_currency($item->price_at_purchase) }}</td>
                    <td>{{ format_currency($item->price_at_purchase * $item->quantity) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">{{ __('admin.no_items_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Order Totals --}}
<div class="order-totals">
    @if($order->coupon)
        <div class="total-line">
            <span>{{ __('admin.coupon') }} ({{ $order->coupon->code }}):</span>
            <span>-{{ format_currency($order->coupon->discount_value) }}</span>
        </div>
    @endif
    @if($order->shipping_cost > 0)
        <div class="total-line">
            <span>{{ __('admin.shipping_cost') }}:</span>
            <span>{{ format_currency($order->shipping_cost) }}</span>
        </div>
    @endif
    <div class="total-line grand-total">
        <strong>{{ __('admin.total') }}:</strong>
        <strong>{{ format_currency($order->total_price) }}</strong>
    </div>
</div>

@endsection

@push('styles')
<style>
    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .order-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .order-info-card {
        background: var(--color-surface);
        border-radius: 1rem;
        padding: 1.25rem;
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-sm);
    }
    .order-info-card h3 {
        margin-top: 0;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--color-border);
        font-size: 1rem;
    }
    .order-info-card p {
        margin: 0.5rem 0;
        font-size: 0.9rem;
    }

    /* Delivery type display */
    .delivery-type-display {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-weight: 600;
        font-size: 0.9rem;
        margin-top: 0.25rem;
    }
    .delivery-home {
        background: rgba(100,95,125,0.08);
        color: var(--color-primary, #645F7D);
        border: 1px solid rgba(100,95,125,0.2);
    }
    .delivery-bureau {
        background: rgba(59,130,246,0.08);
        color: #3B82F6;
        border: 1px solid rgba(59,130,246,0.2);
    }

    .table-responsive {
        overflow-x: auto;
        margin-bottom: 1.5rem;
        border-radius: 1rem;
        border: 1px solid var(--color-border);
    }
    .admin-table {
        min-width: 500px;
        width: 100%;
        border: none;
    }
    .order-totals {
        background: var(--color-surface);
        border-radius: 1rem;
        padding: 1.25rem;
        border: 1px solid var(--color-border);
        max-width: 400px;
        margin-left: auto;
    }
    .total-line {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    .grand-total {
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 2px solid var(--color-border);
        font-size: 1.1rem;
    }
    .text-center { text-align: center; }
    .proof-image {
        max-width: 200px;
        max-height: 200px;
        border: 1px solid var(--color-border);
        border-radius: 0.5rem;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .proof-image:hover { transform: scale(1.05); }
    .payment-actions { margin-top: 1rem; }
    .action-buttons { display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.5rem; }
    .btn-success { background: #10B981; color: white; border: none; }
    .btn-success:hover { background: #059669; }
    .btn-danger { background: #EF4444; color: white; border: none; }
    .btn-danger:hover { background: #DC2626; }
</style>
@endpush