<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Inertia API Controller
 * 
 * Provides session-authenticated API endpoints for Inertia components.
 * These endpoints use traditional Laravel session authentication with CSRF protection,
 * which is appropriate for same-server frontend communication with Inertia.js.
 */
class InertiaApiController extends Controller
{
    /**
     * Get dashboard metrics data.
     */
    public function dashboardMetrics(): JsonResponse
    {
        // Get order counts by status
        $orderCounts = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses are present
        $allStatuses = ['new', 'confirmed', 'paid', 'fulfilled', 'completed', 'cancelled', 'on_hold', 'failed'];
        foreach ($allStatuses as $status) {
            if (!isset($orderCounts[$status])) {
                $orderCounts[$status] = 0;
            }
        }

        $metrics = [
            'order_counts' => $orderCounts,
            'total_revenue' => Order::sum('total_amount') ?: 0,
            'failed_jobs_count' => \DB::table('failed_jobs')->count(),
            'api_response_time_p95' => rand(45, 120), // Mock for now
            'queue_sizes' => [
                'default' => \DB::table('jobs')->where('queue', 'default')->count(),
                'emails' => \DB::table('jobs')->where('queue', 'emails')->count(),
                'reports' => \DB::table('jobs')->where('queue', 'reports')->count(),
            ],
            'recent_orders' => Order::with('client')
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'number' => $order->number,
                        'client_name' => $order->client?->full_name ?? 'Unknown',
                        'status' => $order->status,
                        'total' => $order->total_amount,
                        'created_at' => $order->created_at->toISOString(),
                    ];
                }),
            'recent_activities' => [], // Mock for now
        ];

        return response()->json($metrics);
    }

    /**
     * Get orders list with filtering.
     */
    public function orders(Request $request): JsonResponse
    {
        $query = Order::with('client');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('full_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $orders = $query->latest()->paginate($perPage);

        return response()->json($orders);
    }

    /**
     * Get single order.
     */
    public function order(Order $order): JsonResponse
    {
        $order->load(['client', 'items', 'shippingLabels']);
        return response()->json(['data' => $order]);
    }

    /**
     * Get clients list with filtering.
     */
    public function clients(Request $request): JsonResponse
    {
        $query = Client::query();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $clients = $query->latest()->paginate($perPage);

        return response()->json($clients);
    }

    /**
     * Get single client.
     */
    public function client(Client $client): JsonResponse
    {
        $client->load(['orders']);
        return response()->json(['data' => $client]);
    }
}