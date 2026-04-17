<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();

        $stats = [
            'orders_today'       => Order::today()->count(),
            'revenue_today'      => Order::today()->where('status', 'delivered')->sum('total_sale_price'),
            'profit_today'       => Order::today()->where('status', 'delivered')->sum('platform_profit'),
            'active_resellers'   => User::where('role', 'reseller')->where('status', 'active')->count(),
            'pending_orders'     => Order::whereIn('status', ['new', 'confirmed', 'preparing'])->count(),
            'out_for_delivery'   => Order::where('status', 'out_for_delivery')->count(),
            'total_orders'       => Order::count(),
            'pending_withdrawals'=> WithdrawalRequest::where('status', 'pending')->count(),
        ];

        // Last 7 days chart data
        $chartData = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('SUM(CASE WHEN status = "delivered" THEN platform_profit ELSE 0 END) as profit')
        )
        ->where('created_at', '>=', now()->subDays(6)->startOfDay())
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // Recent orders
        $recentOrders = Order::with('reseller')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard.index', compact('stats', 'chartData', 'recentOrders'));
    }
}
