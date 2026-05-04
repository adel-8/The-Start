@extends('admin.layouts.app')

@section('title', __('admin.dashboard'))

@section('content')
<!-- Stats Row 1 -->
<div class="dashboard-stats">
    <div class="stat-card">
        <i class="fas fa-money-bill-wave stat-icon"></i>
        <h3>{{ __('admin.total_revenue') }}</h3>
        <div class="stat-value">{{ format_currency($totalRevenue) }}</div>
        @if(isset($revenueTrend))
            <div class="stat-trend {{ $revenueTrend >= 0 ? 'trend-up' : 'trend-down' }}">
                {!! $revenueTrend >= 0 ? '↑' : '↓' !!} {{ abs($revenueTrend) }}% vs last week
            </div>
        @endif
    </div>
    <div class="stat-card">
        <i class="fas fa-shopping-cart stat-icon"></i>
        <h3>{{ __('admin.total_orders') }}</h3>
        <div class="stat-value">{{ $totalOrders }}</div>
    </div>
    <div class="stat-card">
        <i class="fas fa-users stat-icon"></i>
        <h3>{{ __('admin.customers') }}</h3>
        <div class="stat-value">{{ $totalCustomers }}</div>
    </div>
    <div class="stat-card">
        <i class="fas fa-boxes stat-icon"></i>
        <h3>{{ __('admin.products') }}</h3>
        <div class="stat-value">{{ $totalProducts }}</div>
    </div>
</div>

<!-- Stats Row 2 (Today + Week) -->
<div class="dashboard-stats">
    <div class="stat-card">
        <i class="fas fa-calendar-day stat-icon"></i>
        <h3>{{ __('admin.today_sales') }}</h3>
        <div class="stat-value">{{ format_currency($todayRevenue) }}</div>
        <div class="stat-today">{{ $todayOrders }} {{ __('admin.orders') }}</div>
    </div>
    <div class="stat-card">
        <i class="fas fa-chart-line stat-icon"></i>
        <h3>{{ __('admin.this_week') }}</h3>
        <div class="stat-value">{{ format_currency($weekRevenue) }}</div>
        <div class="stat-today">{{ $weekOrders }} {{ __('admin.orders') }}</div>
    </div>
</div>

<!-- Date Range Filter (optional, requires JS) -->
<div class="dashboard-filters">
    <div class="filter-group">
        <label for="dateRange">{{ __('admin.date_range') }}</label>
        <select id="dateRange" class="filter-select">
            <option value="7d">{{ __('admin.last_7_days') }}</option>
            <option value="30d" selected>{{ __('admin.last_30_days') }}</option>
            <option value="12m">{{ __('admin.last_12_months') }}</option>
        </select>
    </div>
    <button id="exportChartBtn" class="btn-secondary btn-sm">
        <i class="fas fa-download"></i> {{ __('admin.export_chart') }}
    </button>
</div>

<!-- Charts Row -->
<div class="charts-row">
    <div class="chart-card">
        <h3>{{ __('admin.revenue_last_30_days') }}</h3>
        <canvas id="revenueChart" data-revenue='@json($revenueLast30Days)'></canvas>
    </div>
    <div class="chart-card">
        <h3>{{ __('admin.orders_last_30_days') }}</h3>
        <canvas id="ordersChart" data-orders='@json($ordersLast30Days)'></canvas>
    </div>
</div>

<!-- Tables Row 1: Top Products + Recent Orders -->
<div class="dashboard-tables">
    <div class="table-card">
        <h3>{{ __('admin.top_selling_products') }}</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.product') }}</th>
                    <th scope="col">{{ __('admin.sold') }}</th>
                    <th scope="col">{{ __('admin.revenue') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topProducts as $item)
                <tr>
                    <td>{{ $item->product->name ?? __('admin.deleted_product') }}</td>
                    <td>{{ $item->total_sold }}</td>
                    <td>{{ format_currency($item->total_revenue ?? ($item->total_sold * ($item->product->price ?? 0))) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3">{{ __('admin.no_top_products') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-card">
        <h3>{{ __('admin.recent_orders') }}</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.order_number') }}</th>
                    <th scope="col">{{ __('admin.customer') }}</th>
                    <th scope="col">{{ __('admin.total') }}</th>
                    <th scope="col">{{ __('admin.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                <tr>
                    <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                    <td>{{ $order->user?->name ?? $order->guest_name }}</td>
                    <td>{{ format_currency($order->total_price) }}</td>
                    <td><span class="status-badge status-{{ $order->status }}">{{ __('status.' . $order->status) }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">{{ __('admin.no_orders') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Tables Row 2: Low Stock + Quick Actions -->
<div class="dashboard-tables dashboard-tables-spacing">
    <div class="table-card">
        <h3>{{ __('admin.low_stock_products') }}</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.product') }}</th>
                    <th scope="col">{{ __('admin.stock') }}</th>
                    <th scope="col">{{ __('admin.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lowStock as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td class="{{ $product->stock == 0 ? 'out-of-stock' : 'low-stock-item' }}">{{ $product->stock }}</td>
                    <td>{{ $product->stock == 0 ? __('admin.out_of_stock') : __('admin.low_stock') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3">{{ __('admin.all_products_sufficient') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-card">
        <h3>{{ __('admin.quick_actions') }}</h3>
        <div class="quick-actions">
            <a href="{{ route('admin.products.create') }}" class="btn-primary"><i class="fas fa-plus"></i> {{ __('admin.add_product') }}</a>
            <a href="{{ route('admin.categories.index') }}" class="btn-secondary"><i class="fas fa-tags"></i> {{ __('admin.manage_categories') }}</a>
            <a href="{{ route('admin.users.index') }}" class="btn-secondary"><i class="fas fa-users"></i> {{ __('admin.view_customers') }}</a>
            <a href="{{ route('admin.coupons.index') }}" class="btn-secondary"><i class="fas fa-percent"></i> {{ __('admin.manage_coupons') }}</a>
            <a href="{{ route('admin.orders.index') }}" class="btn-secondary"><i class="fas fa-eye"></i> {{ __('admin.view_all_orders') }}</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    const revenueCanvas = document.getElementById('revenueChart');
    let revenueChart = null;
    let ordersChart = null;

    function initCharts() {
        if (revenueCanvas && revenueCanvas.dataset.revenue) {
            const revenueData = JSON.parse(revenueCanvas.dataset.revenue);
            if (revenueChart) revenueChart.destroy();
            revenueChart = new Chart(revenueCanvas, {
                type: 'line',
                data: {
                    labels: revenueData.map(item => item.date),
                    datasets: [{
                        label: '{{ __("admin.revenue") }} ({{ __('admin.currency_symbol') }})',
                        data: revenueData.map(item => item.total),
                        borderColor: '#645F7D',
                        backgroundColor: 'rgba(100,95,125,0.1)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let value = context.raw;
                                    return '$' + value.toFixed(2);
                                }
                            }
                        }
                    }
                }
            });
        }

        const ordersCanvas = document.getElementById('ordersChart');
        if (ordersCanvas && ordersCanvas.dataset.orders) {
            const ordersData = JSON.parse(ordersCanvas.dataset.orders);
            if (ordersChart) ordersChart.destroy();
            ordersChart = new Chart(ordersCanvas, {
                type: 'line',
                data: {
                    labels: ordersData.map(item => item.date),
                    datasets: [{
                        label: '{{ __("admin.orders") }}',
                        data: ordersData.map(item => item.count),
                        borderColor: '#E0B854',
                        backgroundColor: 'rgba(224,184,84,0.1)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.raw + ' {{ __("admin.orders") }}';
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    initCharts();

    // Date range filter (AJAX example)
    const rangeSelect = document.getElementById('dateRange');
    if (rangeSelect) {
        rangeSelect.addEventListener('change', function() {
            const range = this.value;
            fetch(`{{ route('admin.analytics.data') }}?type=revenue&range=${range}`)
                .then(response => response.json())
                .then(data => {
                    if (revenueChart) {
                        revenueChart.data.labels = data.labels;
                        revenueChart.data.datasets[0].data = data.values;
                        revenueChart.update();
                    }
                })
                .catch(err => console.error('Failed to load chart data', err));
        });
    }

    // Export chart as image
    const exportBtn = document.getElementById('exportChartBtn');
    if (exportBtn && revenueChart) {
        exportBtn.addEventListener('click', function() {
            const link = document.createElement('a');
            link.download = 'revenue-chart.png';
            link.href = revenueChart.toBase64Image();
            link.click();
        });
    }
});
</script>
@endpush