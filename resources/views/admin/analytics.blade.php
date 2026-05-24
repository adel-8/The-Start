@extends('admin.layouts.app')

@section('title', __('admin.analytics'))

@section('content')
@php
    $analyticsPayload = json_encode([
        'dates'           => collect($dates)->toArray(),
        'revenueData'     => collect($revenueData)->toArray(),
        'ordersData'      => collect($ordersData)->toArray(),
        'categoryLabels'  => collect($categoryLabels ?? [])->toArray(),
        'categoryData'    => collect($categoryData ?? [])->toArray(),
        'paymentLabels'   => collect($paymentMethods ?? [])->keys()->toArray(),
        'paymentData'     => collect($paymentMethods ?? [])->values()->toArray(),
        'newCustomers'    => $newCustomers ?? 0,
        'returningCustomers' => $returningCustomers ?? 0,
        'customersData'   => collect($customersData ?? [])->toArray(),
        'currentRange'    => $currentRange ?? '30d',
    ]);
@endphp
<div class="analytics-container" data-analytics="{{ $analyticsPayload }}">

    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ format_currency($kpis['total_revenue']) }}</div>
                <div class="kpi-label">{{ __('admin.total_revenue') }}</div>
                @if(isset($trends['revenue']))
                    <div class="kpi-change {{ $trends['revenue'] >= 0 ? 'up' : 'down' }}">
                        {{ $trends['revenue'] >= 0 ? '↑' : '↓' }} {{ abs($trends['revenue']) }}% {{ __('admin.vs_last_week') }}
                    </div>
                @endif
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-chart-line"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ format_currency($kpis['net_profit']) }}</div>
                <div class="kpi-label">{{ __('admin.net_profit') }}</div>
                @if(isset($trends['profit']))
                    <div class="kpi-change {{ $trends['profit'] >= 0 ? 'up' : 'down' }}">
                        {{ $trends['profit'] >= 0 ? '↑' : '↓' }} {{ abs($trends['profit']) }}% {{ __('admin.vs_last_week') }}
                    </div>
                @endif
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ $kpis['total_orders'] }}</div>
                <div class="kpi-label">{{ __('admin.total_orders') }}</div>
                @if(isset($trends['orders']))
                    <div class="kpi-change {{ $trends['orders'] >= 0 ? 'up' : 'down' }}">
                        {{ $trends['orders'] >= 0 ? '↑' : '↓' }} {{ abs($trends['orders']) }}% {{ __('admin.vs_last_week') }}
                    </div>
                @endif
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-percent"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ number_format($kpis['conversion_rate'], 1) }}%</div>
                <div class="kpi-label">{{ __('admin.conversion_rate') }}</div>
                @if(isset($trends['conversion']))
                    <div class="kpi-change {{ $trends['conversion'] >= 0 ? 'up' : 'down' }}">
                        {{ $trends['conversion'] >= 0 ? '↑' : '↓' }} {{ abs($trends['conversion']) }}% {{ __('admin.vs_last_week') }}
                    </div>
                @endif
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-receipt"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ format_currency($kpis['average_order_value']) }}</div>
                <div class="kpi-label">{{ __('admin.aov') }}</div>
                @if(isset($trends['aov']))
                    <div class="kpi-change {{ $trends['aov'] >= 0 ? 'up' : 'down' }}">
                        {{ $trends['aov'] >= 0 ? '↑' : '↓' }} {{ abs($trends['aov']) }}% {{ __('admin.vs_last_week') }}
                    </div>
                @endif
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-user-plus"></i></div>
            <div class="kpi-info">
                <div class="kpi-value">{{ $kpis['new_customers'] }}</div>
                <div class="kpi-label">{{ __('admin.new_customers') }}</div>
                @if(isset($trends['customers']))
                    <div class="kpi-change {{ $trends['customers'] >= 0 ? 'up' : 'down' }}">
                        {{ $trends['customers'] >= 0 ? '↑' : '↓' }} {{ abs($trends['customers']) }}% {{ __('admin.vs_last_week') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Chart with Toggle and Filters -->
    <div class="chart-section">
        <div class="chart-header">
            <h3>{{ __('admin.performance_overview') }}</h3>
            <div class="chart-controls">
                <div class="chart-type-toggle">
                    <button class="chart-type-btn active" data-type="revenue">{{ __('admin.revenue') }}</button>
                    <button class="chart-type-btn" data-type="orders">{{ __('admin.orders') }}</button>
                    <button class="chart-type-btn" data-type="customers">{{ __('admin.customers') }}</button>
                </div>
                <div class="date-range">
                    <button class="range-btn active" data-range="today">{{ __('admin.today') }}</button>
                    <button class="range-btn" data-range="7d">{{ __('admin.last_7_days') }}</button>
                    <button class="range-btn" data-range="30d">{{ __('admin.last_30_days') }}</button>
                    <button class="range-btn" data-range="12m">{{ __('admin.last_12_months') }}</button>
                </div>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="mainChart" width="800" height="400"></canvas>
        </div>
    </div>

    <!-- Two-column analytics grid -->
    <div class="analytics-grid">
        <div class="grid-left">
            <!-- Revenue by Category -->
            <div class="analytics-card">
                <h3>{{ __('admin.revenue_by_category') }}</h3>
                <canvas id="categoryChart" height="200"></canvas>
            </div>

            <!-- Top Customers -->
            <div class="analytics-card">
                <h3>{{ __('admin.top_customers') }}</h3>
                <div class="table-responsive">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>{{ __('admin.customer') }}</th>
                                <th>{{ __('admin.orders') }}</th>
                                <th>{{ __('admin.spent') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCustomers as $customer)
                                <tr>
                                    <td>{{ $customer->name ?? $customer->email }}</td>
                                    <td>{{ $customer->orders_count }}</td>
                                    <td>{{ format_currency($customer->orders_sum_total_price ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">{{ __('admin.no_customers_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- New vs Returning Customers -->
            <div class="analytics-card">
                <h3>{{ __('admin.customer_type') }}</h3>
                <div class="customer-types">
                    <div class="customer-type">
                        <span class="type-label">{{ __('admin.new') }}</span>
                        <span class="type-value">{{ $newCustomers }}</span>
                    </div>
                    <div class="customer-type">
                        <span class="type-label">{{ __('admin.returning') }}</span>
                        <span class="type-value">{{ $returningCustomers }}</span>
                    </div>
                </div>
                <canvas id="customerTypeChart" height="150"></canvas>
            </div>
        </div>

        <div class="grid-right">
            <!-- Top Selling Products -->
            <div class="analytics-card">
                <h3>{{ __('admin.top_selling_products') }}</h3>
                <div class="table-responsive">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>{{ __('admin.product') }}</th>
                                <th>{{ __('admin.sold') }}</th>
                                <th>{{ __('admin.revenue') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? __('admin.deleted_product') }}</td>
                                    <td>{{ $item->total_sold }}</td>
                                    {{-- Use price_at_purchase aggregated in query, not current product price --}}
                                    <td>{{ format_currency($item->total_revenue ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">{{ __('admin.no_top_products') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div class="analytics-card">
                <h3>{{ __('admin.low_stock_alerts') }}</h3>
                @if($lowStock->count())
                    <ul class="stock-list">
                        @foreach($lowStock as $product)
                            <li><strong>{{ $product->name }}</strong> – {{ __('admin.stock') }}: {{ $product->stock }}</li>
                        @endforeach
                    </ul>
                @else
                    <p>{{ __('admin.all_products_sufficient') }}</p>
                @endif
            </div>

            <!-- Worst Performing Products -->
            <div class="analytics-card">
                <h3>{{ __('admin.worst_performing_products') }}</h3>
                <div class="table-responsive">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>{{ __('admin.product') }}</th>
                                <th>{{ __('admin.sold') }}</th>
                                <th>{{ __('admin.revenue') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($worstProducts as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? __('admin.deleted_product') }}</td>
                                    <td>{{ $item->total_sold }}</td>
                                    <td>{{ format_currency($item->total_revenue ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">{{ __('admin.no_products') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Methods Distribution -->
            <div class="analytics-card">
                <h3>{{ __('admin.payment_methods') }}</h3>
                <canvas id="paymentChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Orders (all statuses shown for full visibility) -->
    <div class="analytics-card full-width">
        <h3>{{ __('admin.recent_orders') }}</h3>
        <div class="table-responsive">
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>{{ __('admin.order_number') }}</th>
                        <th>{{ __('admin.customer') }}</th>
                        <th>{{ __('admin.total') }}</th>
                        <th>{{ __('admin.status') }}</th>
                        <th>{{ __('admin.date') }}</th>
                        <th>{{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                            <td>{{ $order->user?->name ?? $order->guest_name }}</td>
                            <td>{{ format_currency($order->total_price) }}</td>
                            <td><span class="status-badge status-{{ $order->status }}">{{ __('status.' . $order->status) }}</span></td>
                            <td>{{ $order->created_at->format('Y-m-d') }}</td>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="btn-sm btn-edit">{{ __('admin.view') }}</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">{{ __('admin.no_orders_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .analytics-container { padding: 0; }
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .kpi-card {
        background: var(--color-surface);
        border-radius: 1rem;
        padding: 1rem;
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    .kpi-icon {
        width: 48px;
        height: 48px;
        background: rgba(100, 95, 125, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: var(--color-primary);
        flex-shrink: 0;
    }
    .kpi-info { flex: 1; }
    .kpi-value {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--color-text);
        line-height: 1.2;
    }
    .kpi-label {
        font-size: 0.85rem;
        color: var(--color-muted);
        margin-top: 0.2rem;
    }
    .kpi-change { font-size: 0.7rem; margin-top: 0.3rem; }
    .kpi-change.up   { color: #10B981; }
    .kpi-change.down { color: #EF4444; }

    .chart-section {
        background: var(--color-surface);
        border-radius: 1rem;
        padding: 1rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-sm);
    }
    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .chart-header h3 { font-size: 1.1rem; font-weight: 600; margin: 0; }
    .chart-controls { display: flex; gap: 1rem; flex-wrap: wrap; }
    .chart-type-toggle, .date-range {
        display: flex;
        gap: 0.25rem;
        background: #f1f5f9;
        border-radius: 2rem;
        padding: 0.2rem;
    }
    .chart-type-btn, .range-btn {
        background: none;
        border: none;
        padding: 0.3rem 0.8rem;
        border-radius: 2rem;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .chart-type-btn.active, .range-btn.active {
        background: white;
        box-shadow: var(--shadow-sm);
        color: var(--color-primary);
    }
    .chart-container canvas { width: 100%; height: auto; max-height: 400px; }

    .analytics-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .grid-left, .grid-right { display: flex; flex-direction: column; gap: 1.5rem; }
    .analytics-card {
        background: var(--color-surface);
        border-radius: 1rem;
        padding: 1rem;
        box-shadow: var(--shadow-sm);
    }
    .analytics-card h3 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        border-left: 3px solid var(--color-primary);
        padding-left: 0.5rem;
    }
    .analytics-table {
        width: 100%;
        font-size: 0.85rem;
        border-collapse: collapse;
    }
    .analytics-table th, .analytics-table td {
        padding: 0.5rem;
        text-align: left;
        border-bottom: 1px solid var(--color-border);
    }
    .analytics-table th { font-weight: 600; color: var(--color-muted); }
    .stock-list { list-style: none; padding: 0; }
    .stock-list li { padding: 0.5rem 0; border-bottom: 1px solid var(--color-border); }
    .customer-types { display: flex; gap: 2rem; margin-bottom: 1rem; }
    .customer-type {
        flex: 1;
        text-align: center;
        background: #f8fafc;
        padding: 0.5rem;
        border-radius: 0.5rem;
    }
    .type-label { display: block; font-size: 0.7rem; color: var(--color-muted); }
    .type-value { font-size: 1.2rem; font-weight: 700; }
    .full-width { grid-column: span 2; }
    .table-responsive { overflow-x: auto; }

    @media (max-width: 900px) {
        .analytics-grid { grid-template-columns: 1fr; }
        .full-width { grid-column: span 1; }
    }
</style>
@endpush

@push('scripts')
    <script>
        if (typeof Chart === 'undefined') {
            console.error('Chart.js not loaded');
        }
    </script>
    @vite('resources/js/analytics.js')
@endpush