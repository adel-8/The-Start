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
     * Main admin dashboard
     */
    public function index()
    {
        // ----- Basic Stats (exclude canceled orders from revenue) -----
        $totalRevenue = Order::where('status', '!=', 'canceled')->sum('total_price');
        $totalOrders = Order::count();
        $totalCustomers = User::where('role_id', 3)->count(); // adjust role_id if needed
        $totalProducts = Product::count();

        // ----- Today & This Week -----
        $todayRevenue = Order::whereDate('created_at', Carbon::today())->sum('total_price');
        $todayOrders = Order::whereDate('created_at', Carbon::today())->count();

        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $weekRevenue = Order::whereBetween('created_at', [$weekStart, $weekEnd])->sum('total_price');
        $weekOrders = Order::whereBetween('created_at', [$weekStart, $weekEnd])->count();

        // ----- Trend: revenue vs previous week (7–14 days ago) -----
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();
        $lastWeekRevenue = Order::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->sum('total_price');
        $revenueTrend = $lastWeekRevenue ? (($weekRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100 : 0;

        // ----- 30‑day chart data (separate revenue & orders) -----
        $revenueLast30Days = [];
        $ordersLast30Days = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $revenue = Order::whereDate('created_at', $date)->sum('total_price');
            $ordersCount = Order::whereDate('created_at', $date)->count();
            $revenueLast30Days[] = [
                'date'  => $date->format('Y-m-d'),
                'total' => $revenue
            ];
            $ordersLast30Days[] = [
                'date'  => $date->format('Y-m-d'),
                'count' => $ordersCount
            ];
        }

        // ----- Top Selling Products (with actual revenue) -----
        $topProducts = OrderItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_sold'),
                DB::raw('SUM(price_at_purchase * quantity) as total_revenue')
            )
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // ----- Recent Orders (last 5) -----
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

    /**
     * AJAX endpoint for dynamic chart data (revenue / orders / customers)
     * Used by the date range filter in dashboard.
     */
    public function getAnalyticsData(Request $request)
    {
        $type = $request->get('type', 'revenue');   // revenue, orders, customers
        $range = $request->get('range', '30d');     // today, 7d, 30d, 12m

        $labels = [];
        $values = [];

        switch ($range) {
            case 'today':
                $labels = ['Today'];
                if ($type === 'revenue') {
                    $values = [Order::whereDate('created_at', Carbon::today())->sum('total_price')];
                } elseif ($type === 'orders') {
                    $values = [Order::whereDate('created_at', Carbon::today())->count()];
                } elseif ($type === 'customers') {
                    $values = [User::whereDate('created_at', Carbon::today())->count()];
                }
                break;

            case '7d':
                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::today()->subDays($i);
                    $labels[] = $date->format('Y-m-d');
                    if ($type === 'revenue') {
                        $values[] = Order::whereDate('created_at', $date)->sum('total_price');
                    } elseif ($type === 'orders') {
                        $values[] = Order::whereDate('created_at', $date)->count();
                    } elseif ($type === 'customers') {
                        $values[] = User::whereDate('created_at', $date)->count();
                    }
                }
                break;

            case '30d':
                for ($i = 29; $i >= 0; $i--) {
                    $date = Carbon::today()->subDays($i);
                    $labels[] = $date->format('Y-m-d');
                    if ($type === 'revenue') {
                        $values[] = Order::whereDate('created_at', $date)->sum('total_price');
                    } elseif ($type === 'orders') {
                        $values[] = Order::whereDate('created_at', $date)->count();
                    } elseif ($type === 'customers') {
                        $values[] = User::whereDate('created_at', $date)->count();
                    }
                }
                break;

            case '12m':
                for ($i = 11; $i >= 0; $i--) {
                    $date = Carbon::now()->subMonths($i);
                    $labels[] = $date->format('M Y');
                    $year = $date->year;
                    $month = $date->month;
                    if ($type === 'revenue') {
                        $values[] = Order::whereYear('created_at', $year)
                            ->whereMonth('created_at', $month)
                            ->sum('total_price');
                    } elseif ($type === 'orders') {
                        $values[] = Order::whereYear('created_at', $year)
                            ->whereMonth('created_at', $month)
                            ->count();
                    } elseif ($type === 'customers') {
                        $values[] = User::whereYear('created_at', $year)
                            ->whereMonth('created_at', $month)
                            ->count();
                    }
                }
                break;

            default:
                // fallback to 30d
                for ($i = 29; $i >= 0; $i--) {
                    $date = Carbon::today()->subDays($i);
                    $labels[] = $date->format('Y-m-d');
                    if ($type === 'revenue') {
                        $values[] = Order::whereDate('created_at', $date)->sum('total_price');
                    } elseif ($type === 'orders') {
                        $values[] = Order::whereDate('created_at', $date)->count();
                    } elseif ($type === 'customers') {
                        $values[] = User::whereDate('created_at', $date)->count();
                    }
                }
        }

        return response()->json(['labels' => $labels, 'values' => $values]);
    }

    /**
     * Owner-only analytics page – full business insights
     * (Optional – keep if you use it, otherwise remove)
     */
    /**
 * Owner-only analytics page – full business insights
 */
/**
 * Owner-only analytics page – full business insights
 */
/**
 * Owner-only analytics page – full business insights
 */
public function analytics(Request $request)
{
    // ----- KPIs -----
    $totalRevenue = Order::where('status', '!=', 'canceled')->sum('total_price');
    $totalOrders = Order::count();
    $totalCustomers = User::where('role_id', 3)->count();
    $totalProducts = Product::count();

    // Net profit (example: 30% margin – replace with actual logic)
    $netProfit = $totalRevenue * 0.3;

    // Average order value
    $averageOrderValue = $totalOrders ? $totalRevenue / $totalOrders : 0;

    // New customers in last 30 days
    $newCustomers = User::where('created_at', '>=', Carbon::now()->subDays(30))->count();

    // Conversion rate – placeholder (replace with actual data from analytics/visitors)
    $conversionRate = 2.5; // percentage

    // KPI array
    $kpis = [
        'total_revenue'       => $totalRevenue,
        'net_profit'          => $netProfit,
        'total_orders'        => $totalOrders,
        'average_order_value' => $averageOrderValue,
        'new_customers'       => $newCustomers,
        'conversion_rate'     => $conversionRate,
    ];

    // ----- Chart data (30 days) -----
    $dates = [];
    $revenueData = [];
    $ordersData = [];
    for ($i = 29; $i >= 0; $i--) {
        $date = Carbon::today()->subDays($i);
        $dates[] = $date->format('Y-m-d');
        $revenueData[] = Order::whereDate('created_at', $date)->sum('total_price');
        $ordersData[] = Order::whereDate('created_at', $date)->count();
    }

    // ----- Revenue by Category -----
    $categories = Category::all();
    $categoryLabels = [];
    $categoryData = [];
    foreach ($categories as $category) {
        $revenue = OrderItem::whereHas('product', function ($q) use ($category) {
                $q->where('category_id', $category->id);
            })
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select(DB::raw('SUM(order_items.price_at_purchase * order_items.quantity) as total'))
            ->value('total') ?? 0;
        if ($revenue > 0) {
            $categoryLabels[] = $category->name;
            $categoryData[] = $revenue;
        }
    }

    // ----- Payment Methods Distribution -----
    $paymentMethods = Order::select('payment_method', DB::raw('count(*) as count'))
        ->groupBy('payment_method')
        ->pluck('count', 'payment_method');

    // ----- Top Customers -----
    $topCustomers = User::withCount('orders')
        ->withSum('orders', 'total_price')
        ->orderByDesc('orders_count')
        ->limit(10)
        ->get();

    // ----- Top Selling Products -----
    $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
        ->with('product')
        ->groupBy('product_id')
        ->orderByDesc('total_sold')
        ->limit(5)
        ->get();

    // ----- Low Stock Products -----
    $lowStock = Product::where('stock', '<=', 10)->orderBy('stock')->limit(5)->get();

    // ----- Worst Performing Products -----
    $worstProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
        ->with('product')
        ->groupBy('product_id')
        ->orderBy('total_sold')
        ->limit(5)
        ->get();

    // ----- Recent Orders -----
    $recentOrders = Order::with('user')->latest()->take(10)->get();

    // ----- Customer Type Data -----
    $returningCustomers = User::has('orders', '>', 1)->count();

    // ----- Customer Growth (last 30 days) -----
    $customersData = [];
    for ($i = 29; $i >= 0; $i--) {
        $date = Carbon::today()->subDays($i);
        $customersData[] = User::whereDate('created_at', $date)->count();
    }

    // Return the view with all variables
    return view('admin.analytics', compact(
        'kpis',
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