<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::query()->withCount('items');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('public_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'data' => $query->latest()->paginate($request->integer('per_page', 25)),
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json([
            'data' => $order->load(['items', 'history.admin:id,name']),
        ]);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:'.implode(',', Order::STATUSES),
            'note' => 'nullable|string|max:1024',
        ]);

        $from = $order->status;
        $order->update(['status' => $data['status']]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $from,
            'to_status' => $data['status'],
            'note' => $data['note'] ?? null,
            'admin_user_id' => $request->user()->id,
        ]);

        return response()->json(['data' => $order->fresh(['items', 'history'])]);
    }
}
