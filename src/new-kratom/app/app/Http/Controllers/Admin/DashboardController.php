<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        return response()->json([
            'counters' => [
                'products' => Product::count(),
                'orders_today' => Order::where('created_at', '>=', $today)->count(),
                'orders_month' => Order::where('created_at', '>=', $thisMonth)->count(),
                'revenue_month' => (int) Order::where('created_at', '>=', $thisMonth)
                    ->whereNotIn('status', ['cancelled', 'refunded'])
                    ->sum('total'),
                'pending_orders' => Order::whereIn('status', ['pending', 'received', 'paid'])->count(),
            ],
            'recent_orders' => Order::with('items:order_id,product_name,qty')
                ->latest()
                ->limit(10)
                ->get(['public_id', 'first_name', 'last_name', 'total', 'status', 'created_at']),
        ]);
    }
}
