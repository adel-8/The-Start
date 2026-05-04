@extends('layouts.app')

@section('title', __('messages.my_orders'))

@push('styles')
<style>
    /* ----- Orders Page Styles ----- */
    .orders-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1.5rem;
    }

    .page-header {
        margin-bottom: 2rem;
    }
    .page-header h1 {
        font-size: 2rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--color-primary), #4e3b64);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
    }
    .page-header p {
        color: var(--color-muted);
        margin-top: 0.25rem;
    }

    /* Filter bar */
    .filter-bar {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 2rem;
    }
    .filter-select {
        padding: 0.5rem 1rem;
        border: 1px solid var(--color-border);
        border-radius: 2rem;
        background: var(--color-surface);
        font-size: 0.9rem;
        cursor: pointer;
    }

    /* Orders grid */
    .orders-grid {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* Order card */
    .order-card {
        background: var(--color-surface);
        border-radius: 1.5rem;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--color-border);
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .order-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        padding: 1rem 1.5rem;
        background: rgba(0,0,0,0.02);
        border-bottom: 1px solid var(--color-border);
    }
    .order-number {
        font-weight: 700;
        font-size: 1rem;
        color: var(--color-primary);
    }
    .order-date {
        font-size: 0.85rem;
        color: var(--color-muted);
    }

    .order-body {
        padding: 1.5rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        justify-content: space-between;
    }
    .order-info {
        flex: 2;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem;
    }
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .info-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        font-weight: 600;
        color: var(--color-muted);
        letter-spacing: 0.5px;
    }
    .info-value {
        font-weight: 600;
        font-size: 1rem;
    }
    .order-items-preview {
        flex: 1;
        min-width: 180px;
        text-align: right;
        font-size: 0.85rem;
        color: var(--color-muted);
    }

    .order-actions {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--color-border);
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
    }
    .btn-outline {
        background: transparent;
        border: 1px solid var(--color-primary);
        color: var(--color-primary);
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-weight: 500;
        font-size: 0.85rem;
        cursor: pointer;
        transition: 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    .btn-outline:hover {
        background: var(--color-primary);
        color: white;
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: 0.2rem 0.8rem;
        border-radius: 40px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-pending { background: #f0ad4e; color: white; }
    .badge-processing { background: #5bc0de; color: white; }
    .badge-shipped { background: #5cb85c; color: white; }
    .badge-delivered { background: #2c7a4b; color: white; }
    .badge-canceled { background: #d9534f; color: white; }
    .badge-paid { background: #2c7a4b; color: white; }
    .badge-failed { background: #d9534f; color: white; }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 3rem;
        background: var(--color-surface);
        border-radius: 1.5rem;
        border: 1px solid var(--color-border);
    }
    .empty-state i {
        font-size: 3rem;
        color: var(--color-muted);
        margin-bottom: 1rem;
    }
    .empty-state p {
        margin-bottom: 1rem;
        color: var(--color-muted);
    }

    /* Pagination */
    .pagination {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
        gap: 0.5rem;
    }
    .pagination a, .pagination span {
        padding: 0.5rem 1rem;
        border: 1px solid var(--color-border);
        border-radius: 0.5rem;
        color: var(--color-text);
        text-decoration: none;
        transition: 0.2s;
    }
    .pagination a:hover {
        background: var(--color-primary);
        color: white;
        border-color: var(--color-primary);
    }
    .pagination .active span {
        background: var(--color-primary);
        color: white;
        border-color: var(--color-primary);
    }

    @media (max-width: 768px) {
        .order-body {
            flex-direction: column;
        }
        .order-info {
            grid-template-columns: 1fr 1fr;
        }
        .order-items-preview {
            text-align: left;
        }
    }
</style>
@endpush

@section('content')
<div class="orders-container">
    <div class="page-header">
        <h1><i class="fas fa-box"></i> {{ __('messages.my_orders') }}</h1>
        <p>{{ __('messages.track_orders') }}</p>
    </div>

    <div class="filter-bar">
        <form method="GET" action="{{ route('orders.index') }}" id="filterForm">
            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">{{ __('messages.all_orders') }}</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('messages.status_pending') }}</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>{{ __('messages.status_processing') }}</option>
                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>{{ __('messages.status_shipped') }}</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>{{ __('messages.status_delivered') }}</option>
                <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>{{ __('messages.status_canceled') }}</option>
            </select>
        </form>
    </div>

    @if($orders->count())
        <div class="orders-grid">
            @foreach($orders as $order)
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-number">#{{ $order->order_number }}</div>
                        <div class="order-date">{{ $order->created_at->format('M d, Y') }}</div>
                    </div>
                    <div class="order-body">
                        <div class="order-info">
                            <div class="info-item">
                                <span class="info-label">{{ __('messages.total') }}</span>
                                <span class="info-value">${{ number_format($order->total_price, 2) }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">{{ __('messages.order_status') }}</span>
                                <span class="info-value">
                                    <span class="badge badge-{{ $order->status }}">{{ __('messages.status_' . $order->status) }}</span>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">{{ __('messages.payment_status') }}</span>
                                <span class="info-value">
                                    <span class="badge badge-{{ $order->payment_status }}">{{ __('messages.payment_status_' . $order->payment_status) }}</span>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">{{ __('messages.items') }}</span>
                                <span class="info-value">{{ $order->items->sum('quantity') }}</span>
                            </div>
                        </div>
                        <div class="order-items-preview">
                            {{ $order->items->take(2)->pluck('product.name')->implode(', ') }}
                            @if($order->items->count() > 2)
                                +{{ $order->items->count() - 2 }} {{ __('messages.more') }}
                            @endif
                        </div>
                    </div>
                    <div class="order-actions">
                        <a href="{{ route('orders.show', $order->order_number) }}"  class="btn-outline">
                            <i class="fas fa-eye"></i> {{ __('messages.view_details') }}
                        </a>
                        @if($order->status == 'shipped' && $order->tracking_number)
                            <a href="#" class="btn-outline track-order-btn" data-tracking="{{ $order->tracking_number }}">
                                <i class="fas fa-truck"></i> {{ __('messages.track_order') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pagination">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-shopping-bag"></i>
            <p>{{ __('messages.no_orders') }}</p>
            <a href="{{ route('Shop') }}" class="btn-primary">{{ __('messages.start_shopping') }}</a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.track-order-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const trackingNumber = btn.getAttribute('data-tracking');
                alert(`{{ __('messages.track_order') }}: ${trackingNumber}`);
            });
        });
    });
</script>
@endpush