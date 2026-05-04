@extends('admin.layouts.app')

@section('title', __('admin.manage_orders'))

@section('content')
<div class="admin-header">
    <h1>{{ __('admin.orders') }}</h1>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<!-- Filter Bar (optional, keep as you had) -->
<div class="orders-filters">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="filter-form">
        <div class="filter-group">
            <input type="text" name="search" placeholder="{{ __('admin.search_orders') }}" value="{{ request('search') }}">
        </div>
        <div class="filter-group">
            <select name="status">
                <option value="">{{ __('admin.all_statuses') }}</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('admin.status_pending') }}</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>{{ __('admin.status_processing') }}</option>
                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>{{ __('admin.status_shipped') }}</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>{{ __('admin.status_delivered') }}</option>
                <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>{{ __('admin.status_canceled') }}</option>
            </select>
        </div>
        <button type="submit" class="btn-primary btn-sm">{{ __('admin.filter') }}</button>
        <a href="{{ route('admin.orders.index') }}" class="btn-secondary btn-sm">{{ __('admin.reset') }}</a>
    </form>
</div>

<!-- Orders Table -->
<div class="table-responsive">
    <table class="admin-table">
        <thead>
             <tr>
                <th>{{ __('admin.order_number') }}</th>
                <th>{{ __('admin.customer') }}</th>
                <th>{{ __('admin.total') }}</th>
                <th>{{ __('admin.order_status') }}</th>
                <th>{{ __('admin.payment_method') }}</th>
                <th>{{ __('admin.payment_status') }}</th>
                <th>{{ __('admin.proof') }}</th>
                <th>{{ __('admin.date') }}</th>
                <th>{{ __('admin.actions') }}</th>
             </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>
                        @if($order->user)
                            {{ $order->user->name }}<br>
                            <small>{{ $order->user->email }}</small>
                        @else
                            {{ $order->guest_name }}<br>
                            <small>{{ $order->guest_email }}</small>
                        @endif
                    </td>
                    <td>{{ format_currency($order->total_price) }}</td>
                    <td><span class="badge status-{{ $order->status }}">{{ __('admin.status_' . $order->status) }}</span></td>
                    <td>{{ __('admin.payment_method_' . $order->payment_method) }}</td>
                    <td><span class="badge payment-{{ $order->payment_status }}">{{ __('admin.payment_status_' . $order->payment_status) }}</span></td>
                    <td>
                        @if($order->payment_method == 'baridimob' && $order->payment_proof)
                            <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" class="btn-sm btn-info">{{ __('admin.view_proof') }}</a>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $order->created_at->format('Y-m-d') }}</td>
                    <td>
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn-sm btn-edit">{{ __('admin.view') }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">{{ __('admin.no_orders_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination-container">
    {{ $orders->appends(request()->query())->links() }}
</div>
@endsection

@push('styles')
<style>
    .orders-filters {
        background: var(--color-surface);
        padding: 1rem;
        border-radius: 0.75rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--color-border);
    }
    .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-end;
    }
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .filter-group input, .filter-group select {
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--color-border);
        border-radius: 0.5rem;
        background: var(--color-surface);
        min-width: 180px;
    }
    .table-responsive {
        overflow-x: auto;
        margin-bottom: 1.5rem;
    }
    .admin-table {
        min-width: 800px;
        width: 100%;
    }
    .btn-sm {
        padding: 0.25rem 0.6rem;
        font-size: 0.75rem;
        border-radius: 0.5rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        text-decoration: none;
        cursor: pointer;
        border: none;
    }
    .btn-edit {
        background: var(--color-info);
        color: white;
    }
    .btn-info {
        background: var(--color-primary);
        color: white;
    }
    .text-center {
        text-align: center;
    }
    @media (max-width: 768px) {
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }
        .filter-group input, .filter-group select {
            width: 100%;
        }
    }
</style>
@endpush