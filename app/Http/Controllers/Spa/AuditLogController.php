<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Get audit logs with filtering and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::query()->orderBy('created_at', 'desc');

        // Filter by entity type and ID
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->get('entity_type'));
        }

        if ($request->filled('entity_id')) {
            $query->where('entity_id', $request->get('entity_id'));
        }

        // Filter by actor
        if ($request->filled('actor_type')) {
            $query->where('actor_type', $request->get('actor_type'));
        }

        if ($request->filled('actor_id')) {
            $query->where('actor_id', $request->get('actor_id'));
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->get('action'));
        }

        // Date range filtering
        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->get('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->get('to_date'));
        }

        $perPage = min((int) $request->get('per_page', 20), 100);
        $auditLogs = $query->paginate($perPage);

        return response()->json([
            'data' => $auditLogs->items(),
            'meta' => [
                'current_page' => $auditLogs->currentPage(),
                'per_page' => $auditLogs->perPage(),
                'total' => $auditLogs->total(),
                'last_page' => $auditLogs->lastPage(),
                'from' => $auditLogs->firstItem(),
                'to' => $auditLogs->lastItem(),
            ],
            'message' => 'Audit logs retrieved successfully',
        ]);
    }

    /**
     * Get audit logs for a specific order
     */
    public function orderAuditLogs(Request $request, string $orderId): JsonResponse
    {
        $query = AuditLog::byEntity('order', $orderId)
            ->orderBy('created_at', 'desc');

        $perPage = min((int) $request->get('per_page', 20), 100);
        $auditLogs = $query->paginate($perPage);

        return response()->json([
            'data' => $auditLogs->items(),
            'meta' => [
                'current_page' => $auditLogs->currentPage(),
                'per_page' => $auditLogs->perPage(),
                'total' => $auditLogs->total(),
                'last_page' => $auditLogs->lastPage(),
                'from' => $auditLogs->firstItem(),
                'to' => $auditLogs->lastItem(),
            ],
            'message' => 'Order audit logs retrieved successfully',
        ]);
    }

    /**
     * Get summary statistics for audit logs
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total_entries' => AuditLog::count(),
            'entries_today' => AuditLog::whereDate('created_at', today())->count(),
            'entries_this_week' => AuditLog::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])->count(),
            'by_entity_type' => AuditLog::selectRaw('entity_type, COUNT(*) as count')
                ->groupBy('entity_type')
                ->pluck('count', 'entity_type'),
            'by_action' => AuditLog::selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action'),
            'by_actor_type' => AuditLog::selectRaw('actor_type, COUNT(*) as count')
                ->groupBy('actor_type')
                ->pluck('count', 'actor_type'),
        ];

        return response()->json([
            'data' => $stats,
            'message' => 'Audit log statistics retrieved successfully',
        ]);
    }
}
