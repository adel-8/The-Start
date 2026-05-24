<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Statuses that represent real, countable business activity.
     * Canceled and pending orders are excluded from all revenue/count metrics.
     */
    private const ACTIVE_STATUSES = ['processing', 'confirmed', 'shipped', 'delivered', 'completed'];

    // -------------------------------------------------------------------------
    // Main admin dashboard
    // -------------------------------------------------------------------------
    public function index()
    {
        // ----- Basic Stats -----
        $totalRevenue = Order::whereIn('status', self::ACTIVE_STATUSES)->sum('total_price');
        $totalOrders  = Order::whereIn('status', self::ACTIVE_STATUSES)->count();
        $totalCustomers = User::where('role_id', 3)->count();
        $totalProducts  = Product::count();

        // ----- Today -----
        $todayRevenue = Order::whereIn('status', self::ACTIVE_STATUSES)
            ->whereDate('created_at', Carbon::today())
            ->sum('total_price');

        $todayOrders = Order::whereIn('status', self::ACTIVE_STATUSES)
            ->whereDate('created_at', Carbon::today())
            ->count();

        // ----- This week -----
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd   = Carbon::now()->endOfWeek();

        $weekRevenue = Order::whereIn('status', self::ACTIVE_STATUSES)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->sum('total_price');

        $weekOrders = Order::whereIn('status', self::ACTIVE_STATUSES)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();

        // ----- Revenue trend vs previous week -----
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd   = Carbon::now()->subWeek()->endOfWeek();

        $lastWeekRevenue = Order::whereIn('status', self::ACTIVE_STATUSES)
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->sum('total_price');

        $revenueTrend = $lastWeekRevenue
            ? round((($weekRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100, 1)
            : 0;

        // ----- 30-day chart data -----
        $revenueLast30Days = [];
        $ordersLast30Days  = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            $revenueLast30Days[] = [
                'date'  => $date->format('Y-m-d'),
                'total' => Order::whereIn('status', self::ACTIVE_STATUSES)
                    ->whereDate('created_at', $date)
                    ->sum('total_price'),
            ];

            $ordersLast30Days[] = [
                'date'  => $date->format('Y-m-d'),
                'count' => Order::whereIn('status', self::ACTIVE_STATUSES)
                    ->whereDate('created_at', $date)
                    ->count(),
            ];
        }

        // ----- Top Selling Products (from active orders only) -----
        $topProducts = OrderItem::select(
                'product_id',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.price_at_purchase * order_items.quantity) as total_revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', self::ACTIVE_STATUSES)
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // ----- Recent Orders (last 5, all statuses for visibility) -----
        $recentOrders = Order::with('user')->latest()->take(5)->get();

        // ----- Low Stock Products (stock ≤ 10) -----
        $lowStock = Product::where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalRevenue', 'totalOrders', 'totalCustomers', 'totalProducts',
            'todayRevenue', 'todayOrders', 'weekRevenue', 'weekOrders',
            'revenueTrend',
            'revenueLast30Days', 'ordersLast30Days',
            'topProducts', 'recentOrders', 'lowStock'
        ));
    }

    // -------------------------------------------------------------------------
    // AJAX endpoint for dynamic chart data
    // -------------------------------------------------------------------------
    public function getAnalyticsData(Request $request)
    {
        $type  = $request->get('type', 'revenue'); // revenue | orders | customers
        $range = $request->get('range', '30d');    // today | 7d | 30d | 12m

        $labels = [];
        $values = [];

        switch ($range) {
            case 'today':
                $labels = [Carbon::today()->format('Y-m-d')];
                $values = [$this->resolveChartValue($type, 'date', Carbon::today())];
                break;

            case '7d':
                for ($i = 6; $i >= 0; $i--) {
                    $date      = Carbon::today()->subDays($i);
                    $labels[]  = $date->format('Y-m-d');
                    $values[]  = $this->resolveChartValue($type, 'date', $date);
                }
                break;

            case '12m':
                for ($i = 11; $i >= 0; $i--) {
                    $date      = Carbon::now()->subMonths($i);
                    $labels[]  = $date->format('M Y');
                    $values[]  = $this->resolveChartValue($type, 'month', $date);
                }
                break;

            default: // 30d
                for ($i = 29; $i >= 0; $i--) {
                    $date      = Carbon::today()->subDays($i);
                    $labels[]  = $date->format('Y-m-d');
                    $values[]  = $this->resolveChartValue($type, 'date', $date);
                }
        }

        return response()->json(['labels' => $labels, 'values' => $values]);
    }

    /**
     * Helper: resolve a single chart data point with proper status filtering.
     */
    private function resolveChartValue(string $type, string $period, Carbon $date): float|int
    {
        if ($type === 'customers') {
            // Customers are not filtered by order status
            $query = User::query();
            return $period === 'month'
                ? $query->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count()
                : $query->whereDate('created_at', $date)->count();
        }

        $query = Order::whereIn('status', self::ACTIVE_STATUSES);

        if ($period === 'month') {
            $query->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month);
        } else {
            $query->whereDate('created_at', $date);
        }

        return $type === 'revenue' ? (float) $query->sum('total_price') : $query->count();
    }

    // -------------------------------------------------------------------------
    // Owner analytics page – full business insights
    // -------------------------------------------------------------------------
    public function analytics(Request $request)
    {
        $currentRange = $request->get('range', '30d');

        // ----- KPIs -----
        $totalRevenue = Order::whereIn('status', self::ACTIVE_STATUSES)->sum('total_price');
        $totalOrders  = Order::whereIn('status', self::ACTIVE_STATUSES)->count();
        $totalCustomers = User::where('role_id', 3)->count();
        $totalProducts  = Product::count();

        // Net profit using actual buy_for / sell_for columns from products
        $netProfit = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereIn('orders.status', self::ACTIVE_STATUSES)
            ->sum(DB::raw('(order_items.price_at_purchase - products.buy_for) * order_items.quantity'));

        // Average order value (based on active orders only)
        $averageOrderValue = $totalOrders ? $totalRevenue / $totalOrders : 0;

        // New customers in last 30 days
        $newCustomers = User::where('created_at', '>=', Carbon::now()->subDays(30))->count();

        // Conversion rate – placeholder (replace with real visitor data if available)
        $conversionRate = 2.5;

        $kpis = [
            'total_revenue'       => $totalRevenue,
            'net_profit'          => $netProfit,
            'total_orders'        => $totalOrders,
            'average_order_value' => $averageOrderValue,
            'new_customers'       => $newCustomers,
            'conversion_rate'     => $conversionRate,
        ];

        // ----- Week-over-week trends for KPI badges -----
        $thisWeekStart = Carbon::now()->startOfWeek();
        $thisWeekEnd   = Carbon::now()->endOfWeek();
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd   = Carbon::now()->subWeek()->endOfWeek();

        $thisWeekRevenue = Order::whereIn('status', self::ACTIVE_STATUSES)
            ->whereBetween('created_at', [$thisWeekStart, $thisWeekEnd])->sum('total_price');
        $lastWeekRevenue = Order::whereIn('status', self::ACTIVE_STATUSES)
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->sum('total_price');

        $thisWeekOrders = Order::whereIn('status', self::ACTIVE_STATUSES)
            ->whereBetween('created_at', [$thisWeekStart, $thisWeekEnd])->count();
        $lastWeekOrders = Order::whereIn('status', self::ACTIVE_STATUSES)
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();

        $thisWeekProfit = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereIn('orders.status', self::ACTIVE_STATUSES)
            ->whereBetween('orders.created_at', [$thisWeekStart, $thisWeekEnd])
            ->sum(DB::raw('(order_items.price_at_purchase - products.buy_for) * order_items.quantity'));
        $lastWeekProfit = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereIn('orders.status', self::ACTIVE_STATUSES)
            ->whereBetween('orders.created_at', [$lastWeekStart, $lastWeekEnd])
            ->sum(DB::raw('(order_items.price_at_purchase - products.buy_for) * order_items.quantity'));

        $thisWeekCustomers = User::whereBetween('created_at', [$thisWeekStart, $thisWeekEnd])->count();
        $lastWeekCustomers = User::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();

        $trends = [
            'revenue'    => $lastWeekRevenue  ? round((($thisWeekRevenue  - $lastWeekRevenue)  / $lastWeekRevenue)  * 100, 1) : 0,
            'profit'     => $lastWeekProfit   ? round((($thisWeekProfit   - $lastWeekProfit)   / $lastWeekProfit)   * 100, 1) : 0,
            'orders'     => $lastWeekOrders   ? round((($thisWeekOrders   - $lastWeekOrders)   / $lastWeekOrders)   * 100, 1) : 0,
            'customers'  => $lastWeekCustomers? round((($thisWeekCustomers- $lastWeekCustomers)/ $lastWeekCustomers)* 100, 1) : 0,
            'aov'        => 0,
            'conversion' => 0,
        ];

        // ----- Chart data (30 days, active orders only) -----
        $dates       = [];
        $revenueData = [];
        $ordersData  = [];

        for ($i = 29; $i >= 0; $i--) {
            $date          = Carbon::today()->subDays($i);
            $dates[]       = $date->format('Y-m-d');
            $revenueData[] = Order::whereIn('status', self::ACTIVE_STATUSES)
                ->whereDate('created_at', $date)->sum('total_price');
            $ordersData[]  = Order::whereIn('status', self::ACTIVE_STATUSES)
                ->whereDate('created_at', $date)->count();
        }

        // ----- Revenue by Category (active orders only) -----
        $categories    = Category::all();
        $categoryLabels = [];
        $categoryData   = [];

        foreach ($categories as $category) {
            $revenue = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereIn('orders.status', self::ACTIVE_STATUSES)
                ->whereHas('product', fn($q) => $q->where('category_id', $category->id))
                ->sum(DB::raw('order_items.price_at_purchase * order_items.quantity'));

            if ($revenue > 0) {
                $categoryLabels[] = $category->name;
                $categoryData[]   = $revenue;
            }
        }

        // ----- Payment Methods Distribution (active orders only) -----
        $paymentMethods = Order::whereIn('status', self::ACTIVE_STATUSES)
            ->select('payment_method', DB::raw('count(*) as count'))
            ->groupBy('payment_method')
            ->pluck('count', 'payment_method');

        // ----- Top Customers (sum from active orders only) -----
        $topCustomers = User::withCount(['orders' => fn($q) => $q->whereIn('status', self::ACTIVE_STATUSES)])
            ->withSum(['orders' => fn($q) => $q->whereIn('status', self::ACTIVE_STATUSES)], 'total_price')
            ->orderByDesc('orders_count')
            ->limit(10)
            ->get();

        // ----- Top Selling Products (active orders only) -----
        $topProducts = OrderItem::select(
                'product_id',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.price_at_purchase * order_items.quantity) as total_revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', self::ACTIVE_STATUSES)
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // ----- Worst Performing Products (active orders only) -----
        $worstProducts = OrderItem::select(
                'product_id',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.price_at_purchase * order_items.quantity) as total_revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', self::ACTIVE_STATUSES)
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('total_sold')
            ->limit(5)
            ->get();

        // ----- Low Stock Products -----
        $lowStock = Product::where('stock', '<=', 10)->orderBy('stock')->limit(5)->get();

        // ----- Recent Orders (all statuses – for visibility) -----
        $recentOrders = Order::with('user')->latest()->take(10)->get();

        // ----- Customer Segments -----
        $returningCustomers = User::has('orders', '>', 1)->count();

        // ----- Customer Growth (last 30 days) -----
        $customersData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date            = Carbon::today()->subDays($i);
            $customersData[] = User::whereDate('created_at', $date)->count();
        }

        return view('admin.analytics', compact(
            'kpis',
            'trends',
            'currentRange',
            'dates',
            'revenueData',
            'ordersData',
            'categoryLabels',
            'categoryData',
            'paymentMethods',
            'topCustomers',
            'topProducts',
            'lowStock',
            'worstProducts',
            'recentOrders',
            'newCustomers',
            'returningCustomers',
            'customersData'
        ));
    }
}