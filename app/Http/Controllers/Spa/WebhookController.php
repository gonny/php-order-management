<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWebhook;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    /**
     * List all webhooks with pagination and filtering
     */
    public function index(Request $request)
    {
        $query = Webhook::query();

        // Apply filters
        if ($request->filled('source')) {
            $query->where('source', $request->get('source'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('event_type')) {
            $query->where('event', $request->get('event_type'));
        }

        if ($request->filled('related_order_id')) {
            $query->whereJsonContains('payload->order_id', $request->get('related_order_id'));
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginate results
        $perPage = min($request->get('per_page', 20), 100);
        $webhooks = $query->paginate($perPage);

        return response()->json([
            'data' => $webhooks->items(),
            'current_page' => $webhooks->currentPage(),
            'last_page' => $webhooks->lastPage(),
            'per_page' => $webhooks->perPage(),
            'total' => $webhooks->total(),
            'from' => $webhooks->firstItem(),
            'to' => $webhooks->lastItem(),
        ]);
    }

    /**
     * Get a specific webhook
     */
    public function show(string $id)
    {
        $webhook = Webhook::findOrFail($id);

        return response()->json([
            'data' => $webhook,
        ]);
    }

    /**
     * Reprocess a webhook
     */
    public function reprocess(string $id)
    {
        $webhook = Webhook::findOrFail($id);

        try {
            // Reset webhook status
            $webhook->update([
                'status' => 'processing',
                'error_message' => null,
                'processed_at' => null,
            ]);

            // Dispatch the webhook processing job
            ProcessWebhook::dispatch($webhook);

            Log::info('Webhook reprocessing initiated', [
                'webhook_id' => $webhook->id,
                'source' => $webhook->source,
            ]);

            return response()->json([
                'data' => $webhook->fresh(),
                'message' => 'Webhook reprocessing initiated successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reprocess webhook', [
                'webhook_id' => $webhook->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to reprocess webhook',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
