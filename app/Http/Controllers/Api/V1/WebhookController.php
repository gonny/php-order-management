<?php

namespace App\Http\Controllers\Api\V1;

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
            'meta' => [
                'current_page' => $webhooks->currentPage(),
                'last_page' => $webhooks->lastPage(),
                'per_page' => $webhooks->perPage(),
                'total' => $webhooks->total(),
                'from' => $webhooks->firstItem(),
                'to' => $webhooks->lastItem(),
            ],
            'message' => 'Webhooks retrieved successfully'
        ]);
    }

    /**
     * Receive generic webhook from various sources
     */
    public function receive(Request $request, string $source): Response
    {
        $payload = $request->all();
        $event = $request->input('event', 'unknown');

        Log::info('Webhook received', [
            'source' => $source,
            'event' => $event,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Create webhook record
        $webhook = Webhook::create([
            'source' => $source,
            'event' => $event,
            'payload' => $payload,
            'headers' => $request->headers->all(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'pending',
        ]);

        // Dispatch processing job
        ProcessWebhook::dispatch($webhook);

        return response()->json([
            'status' => 'received',
            'webhook_id' => $webhook->id,
            'message' => 'Webhook received and queued for processing',
        ], 202);
    }

    /**
     * Handle Balíkovna webhook
     */
    public function balikovna(Request $request): Response
    {
        // Validate Balíkovna signature if needed
        // $this->validateBalikovnaSignature($request);

        $payload = $request->all();
        $event = $this->determineBalikovnaEvent($payload);

        Log::info('Balíkovna webhook received', [
            'event' => $event,
            'order_id' => $payload['order_id'] ?? 'unknown',
        ]);

        $webhook = Webhook::create([
            'source' => 'balikovna',
            'event' => $event,
            'payload' => $payload,
            'headers' => $request->headers->all(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'pending',
        ]);

        ProcessWebhook::dispatch($webhook);

        return response()->json([
            'status' => 'received',
            'webhook_id' => $webhook->id,
        ], 200);
    }

    /**
     * Handle DPD webhook
     */
    public function dpd(Request $request): Response
    {
        // Validate DPD signature if needed
        // $this->validateDpdSignature($request);

        $payload = $request->all();
        $event = $this->determineDpdEvent($payload);

        Log::info('DPD webhook received', [
            'event' => $event,
            'order_id' => $payload['order_id'] ?? 'unknown',
        ]);

        $webhook = Webhook::create([
            'source' => 'dpd',
            'event' => $event,
            'payload' => $payload,
            'headers' => $request->headers->all(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'pending',
        ]);

        ProcessWebhook::dispatch($webhook);

        return response()->json([
            'status' => 'received',
            'webhook_id' => $webhook->id,
        ], 200);
    }

    /**
     * Handle payment webhook
     */
    public function payment(Request $request): Response
    {
        // Validate payment provider signature if needed
        // $this->validatePaymentSignature($request);

        $payload = $request->all();
        $event = $this->determinePaymentEvent($payload);

        Log::info('Payment webhook received', [
            'event' => $event,
            'pmi_id' => $payload['pmi_id'] ?? 'unknown',
        ]);

        $webhook = Webhook::create([
            'source' => 'payment',
            'event' => $event,
            'payload' => $payload,
            'headers' => $request->headers->all(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'pending',
        ]);

        ProcessWebhook::dispatch($webhook);

        return response()->json([
            'status' => 'received',
            'webhook_id' => $webhook->id,
        ], 200);
    }

    private function determineBalikovnaEvent(array $payload): string
    {
        // Map Balíkovna webhook data to our event types
        if (isset($payload['label_created']) || isset($payload['tracking_number'])) {
            return 'label_created';
        }

        if (isset($payload['status'])) {
            return match ($payload['status']) {
                'delivered' => 'package_delivered',
                'returned' => 'package_returned',
                'in_transit' => 'package_in_transit',
                default => 'status_update'
            };
        }

        return 'unknown';
    }

    private function determineDpdEvent(array $payload): string
    {
        // Map DPD webhook data to our event types
        if (isset($payload['label_created']) || isset($payload['tracking_number'])) {
            return 'label_created';
        }

        if (isset($payload['status'])) {
            return match ($payload['status']) {
                'delivered' => 'package_delivered',
                'returned' => 'package_returned',
                'in_transit' => 'package_in_transit',
                default => 'status_update'
            };
        }

        return 'unknown';
    }

    private function determinePaymentEvent(array $payload): string
    {
        // Map payment webhook data to our event types
        if (isset($payload['status'])) {
            return match ($payload['status']) {
                'confirmed', 'completed', 'paid' => 'payment_confirmed',
                'failed', 'declined', 'rejected' => 'payment_failed',
                'pending' => 'payment_pending',
                'refunded' => 'payment_refunded',
                default => 'payment_update'
            };
        }

        if (isset($payload['event'])) {
            return $payload['event'];
        }

        return 'unknown';
    }

    private function validateBalikovnaSignature(Request $request): void
    {
        // Implement Balíkovna signature validation
        // $signature = $request->header('X-Balikovna-Signature');
        // $computedSignature = hash_hmac('sha256', $request->getContent(), config('services.balikovna.webhook_secret'));
        // 
        // if (!hash_equals($signature, $computedSignature)) {
        //     throw new \Exception('Invalid Balíkovna webhook signature');
        // }
    }

    private function validateDpdSignature(Request $request): void
    {
        // Implement DPD signature validation
        // $signature = $request->header('X-DPD-Signature');
        // $computedSignature = hash_hmac('sha256', $request->getContent(), config('services.dpd.webhook_secret'));
        // 
        // if (!hash_equals($signature, $computedSignature)) {
        //     throw new \Exception('Invalid DPD webhook signature');
        // }
    }

    private function validatePaymentSignature(Request $request): void
    {
        // Implement payment provider signature validation
        // $signature = $request->header('X-Payment-Signature');
        // $computedSignature = hash_hmac('sha256', $request->getContent(), config('services.payment.webhook_secret'));
        // 
        // if (!hash_equals($signature, $computedSignature)) {
        //     throw new \Exception('Invalid payment webhook signature');
        // }
    }
}
