<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard metrics data.
     */
    public function metrics(): JsonResponse
    {
        $metrics = [
            'orders' => [
                'total' => Order::count(),
                'pending' => Order::where('status', 'pending')->count(),
                'processing' => Order::where('status', 'processing')->count(),
                'shipped' => Order::where('status', 'shipped')->count(),
                'delivered' => Order::where('status', 'delivered')->count(),
                'cancelled' => Order::where('status', 'cancelled')->count(),
                'today' => Order::whereDate('created_at', today())->count(),
                'this_week' => Order::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
                'this_month' => Order::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ],
            'clients' => [
                'total' => Client::count(),
                'active' => Client::where('is_active', true)->count(),
                'new_this_month' => Client::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ],
            'revenue' => [
                'total' => Order::sum('total_amount') ?: 0,
                'this_month' => Order::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('total_amount') ?: 0,
                'this_week' => Order::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->sum('total_amount') ?: 0,
            ],
            'recent_orders' => Order::with('client')
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'client_name' => $order->client?->name ?? 'Unknown',
                        'status' => $order->status,
                        'total_amount' => $order->total_amount,
                        'created_at' => $order->created_at->toISOString(),
                    ];
                }),
        ];

        return response()->json([
            'data' => $metrics,
            'message' => 'Dashboard metrics retrieved successfully'
        ]);
    }
}