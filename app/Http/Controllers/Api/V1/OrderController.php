<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingLabel;
use App\Services\OrderManagement\AuditLogger;
use App\Services\OrderManagement\Exceptions\InvalidOrderTransitionException;
use App\Services\OrderManagement\OrderStateMachine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct(
        private OrderStateMachine $stateMachine,
        private AuditLogger $auditLogger
    ) {}

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => ['sometimes', Rule::in([
                Order::STATUS_NEW,
                Order::STATUS_CONFIRMED,
                Order::STATUS_PAID,
                Order::STATUS_FULFILLED,
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED,
                Order::STATUS_ON_HOLD,
                Order::STATUS_FAILED,
            ])],
            'client_id' => ['sometimes', 'string', 'exists:clients,id'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = Order::with(['client', 'items', 'shippingAddress', 'billingAddress']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $orders = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'client' => ['required', 'array'],
            'client.external_id' => ['sometimes', 'string'],
            'client.email' => ['required', 'email'],
            'client.first_name' => ['required', 'string', 'max:255'],
            'client.last_name' => ['required', 'string', 'max:255'],
            'client.phone' => ['sometimes', 'string'],
            'client.company' => ['sometimes', 'string'],
            
            'shipping_address' => ['required', 'array'],
            'shipping_address.name' => ['required', 'string'],
            'shipping_address.street1' => ['required', 'string'],
            'shipping_address.street2' => ['sometimes', 'string'],
            'shipping_address.city' => ['required', 'string'],
            'shipping_address.postal_code' => ['required', 'string'],
            'shipping_address.country_code' => ['required', 'string', 'size:2'],
            'shipping_address.state' => ['sometimes', 'string'],
            
            'billing_address' => ['sometimes', 'array'],
            'billing_address.name' => ['required_with:billing_address', 'string'],
            'billing_address.street1' => ['required_with:billing_address', 'string'],
            'billing_address.city' => ['required_with:billing_address', 'string'],
            'billing_address.postal_code' => ['required_with:billing_address', 'string'],
            'billing_address.country_code' => ['required_with:billing_address', 'string', 'size:2'],
            
            'items' => ['required', 'array', 'min:1'],
            'items.*.sku' => ['required', 'string'],
            'items.*.name' => ['required', 'string'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['sometimes', 'numeric', 'min:0', 'max:1'],
            
            'carrier' => ['sometimes', Rule::in([Order::CARRIER_BALIKOVNA, Order::CARRIER_DPD])],
            'currency' => ['sometimes', 'string', 'size:3'],
            'meta' => ['sometimes', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $order = DB::transaction(function () use ($request) {
                // Create or find client
                $clientData = $request->client;
                $client = Client::where('email', $clientData['email'])->first();
                
                if (!$client) {
                    $client = Client::create($clientData);
                }

                // Create addresses
                $shippingAddress = Address::create(array_merge(
                    $request->shipping_address,
                    ['type' => 'shipping', 'client_id' => $client->id]
                ));

                $billingAddress = null;
                if ($request->has('billing_address')) {
                    $billingAddress = Address::create(array_merge(
                        $request->billing_address,
                        ['type' => 'billing', 'client_id' => $client->id]
                    ));
                }

                // Calculate total
                $totalAmount = collect($request->items)->sum(function ($item) {
                    return $item['qty'] * $item['price'] * (1 + ($item['tax_rate'] ?? 0));
                });

                // Create order
                $order = Order::create([
                    'number' => 'ORD-' . strtoupper(uniqid()),
                    'client_id' => $client->id,
                    'status' => Order::STATUS_NEW,
                    'total_amount' => $totalAmount,
                    'currency' => $request->get('currency', 'USD'),
                    'shipping_address_id' => $shippingAddress->id,
                    'billing_address_id' => $billingAddress?->id,
                    'carrier' => $request->get('carrier'),
                    'meta' => $request->get('meta'),
                ]);

                // Create order items
                foreach ($request->items as $itemData) {
                    OrderItem::create(array_merge($itemData, ['order_id' => $order->id]));
                }

                // Log creation
                $this->auditLogger->logOrderCreation($order, 'api', $this->getApiClientId($request));

                return $order->load(['client', 'items', 'shippingAddress', 'billingAddress']);
            });

            return response()->json([
                'data' => $order,
                'message' => 'Order created successfully',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Order creation failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Request $request, string $identifier): JsonResponse
    {
        // Support lookup by ID, number, or pmi_id
        $order = Order::with(['client', 'items', 'shippingAddress', 'billingAddress', 'shippingLabels'])
            ->where(function ($query) use ($identifier) {
                $query->where('id', $identifier)
                    ->orWhere('number', $identifier)
                    ->orWhere('pmi_id', $identifier);
            })
            ->first();

        if (!$order) {
            return response()->json([
                'error' => 'Order not found',
            ], 404);
        }

        $data = $order->toArray();
        $data['available_transitions'] = $this->stateMachine->getAvailableTransitions($order);
        $data['status_display'] = $this->stateMachine->getStatusDisplayName($order->status);

        return response()->json(['data' => $data]);
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'carrier' => ['sometimes', Rule::in([Order::CARRIER_BALIKOVNA, Order::CARRIER_DPD])],
            'meta' => ['sometimes', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $originalData = $order->toArray();
        
        $order->update($request->only(['carrier', 'meta']));

        $this->auditLogger->logOrderUpdate($order, $originalData, 'api', $this->getApiClientId($request));

        return response()->json([
            'data' => $order->fresh(['client', 'items', 'shippingAddress', 'billingAddress']),
            'message' => 'Order updated successfully',
        ]);
    }

    /**
     * Remove the specified order.
     */
    public function destroy(Request $request, Order $order): JsonResponse
    {
        // Only allow deletion of new orders
        if ($order->status !== Order::STATUS_NEW) {
            return response()->json([
                'error' => 'Cannot delete order',
                'message' => 'Only orders with status "new" can be deleted',
            ], 422);
        }

        $this->auditLogger->logOrderDeletion($order, 'api', $this->getApiClientId($request));
        
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully',
        ]);
    }

    /**
     * Transition order status.
     */
    public function transition(Request $request, Order $order, string $transition): JsonResponse
    {
        $validator = Validator::make(['transition' => $transition] + $request->all(), [
            'transition' => ['required', Rule::in([
                Order::STATUS_CONFIRMED,
                Order::STATUS_PAID,
                Order::STATUS_FULFILLED,
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED,
                Order::STATUS_ON_HOLD,
                Order::STATUS_FAILED,
            ])],
            'reason' => ['sometimes', 'string'],
            'metadata' => ['sometimes', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $transitionedOrder = $this->stateMachine->transition(
                $order,
                $transition,
                $request->get('reason'),
                $request->get('metadata'),
                'api',
                $this->getApiClientId($request)
            );

            return response()->json([
                'data' => $transitionedOrder->load(['client', 'items', 'shippingAddress', 'billingAddress']),
                'message' => "Order status changed to {$transition}",
            ]);

        } catch (InvalidOrderTransitionException $e) {
            return response()->json([
                'error' => 'Invalid transition',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Generate shipping label.
     */
    public function generateLabel(Request $request, Order $order): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'carrier' => ['sometimes', Rule::in([Order::CARRIER_BALIKOVNA, Order::CARRIER_DPD])],
            'options' => ['sometimes', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!$order->isPaid()) {
            return response()->json([
                'error' => 'Cannot generate label',
                'message' => 'Order must be paid before generating shipping label',
            ], 422);
        }

        // For now, create a mock label (will implement actual carrier integration later)
        $label = ShippingLabel::create([
            'order_id' => $order->id,
            'carrier' => $request->get('carrier', $order->carrier),
            'carrier_shipment_id' => 'MOCK_' . uniqid(),
            'tracking_number' => 'TRK_' . strtoupper(uniqid()),
            'file_path' => 'labels/' . uniqid() . '.pdf',
            'format' => 'pdf',
            'status' => 'generated',
            'raw_response' => ['mock' => 'response'],
        ]);

        return response()->json([
            'data' => $label,
            'message' => 'Shipping label generated successfully',
        ], 201);
    }

    /**
     * Void shipping label.
     */
    public function voidLabel(Request $request, ShippingLabel $label): JsonResponse
    {
        if ($label->status !== 'generated') {
            return response()->json([
                'error' => 'Cannot void label',
                'message' => 'Only generated labels can be voided',
            ], 422);
        }

        $label->update(['status' => 'voided']);

        return response()->json([
            'data' => $label,
            'message' => 'Shipping label voided successfully',
        ]);
    }

    /**
     * Generate DPD shipping label.
     */
    public function generateDpdLabel(Request $request, Order $order): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'shipping_method' => ['required', Rule::in(['DPD_Home', 'DPD_PickupPoint'])],
            'pickup_point_id' => ['required_if:shipping_method,DPD_PickupPoint', 'string', 'max:50'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!$order->isPaid()) {
            return response()->json([
                'error' => 'Cannot generate DPD label',
                'message' => 'Order must be paid before generating shipping label',
            ], 422);
        }

        if (!$order->shippingAddress) {
            return response()->json([
                'error' => 'Cannot generate DPD label',
                'message' => 'Order must have a shipping address',
            ], 422);
        }

        if (!in_array($order->shippingAddress->country_code, ['CZ', 'SK'])) {
            return response()->json([
                'error' => 'Cannot generate DPD label',
                'message' => 'DPD shipping only available for CZ and SK',
            ], 422);
        }

        if ($order->dpd_shipment_id) {
            return response()->json([
                'error' => 'DPD label already exists',
                'message' => 'Order already has a DPD shipment',
            ], 422);
        }

        try {
            // Update order with DPD shipping details
            $order->update([
                'carrier' => Order::CARRIER_DPD,
                'shipping_method' => $request->shipping_method,
                'pickup_point_id' => $request->get('pickup_point_id'),
            ]);

            // Dispatch DPD label generation job
            \App\Jobs\GenerateDpdLabelJob::dispatch($order);

            // Log the label generation request
            $this->auditLogger->logAuditEvent(
                'order',
                $order->id,
                'dpd_label_generation_requested',
                'api',
                $this->getApiClientId($request),
                [
                    'shipping_method' => $request->shipping_method,
                    'pickup_point_id' => $request->get('pickup_point_id'),
                ]
            );

            return response()->json([
                'message' => 'DPD label generation started. Check back later for completion.',
                'order_id' => $order->id,
                'status' => 'queued',
                'shipping_method' => $request->shipping_method,
            ], 202);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'DPD label generation failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete DPD shipment.
     */
    public function deleteDpdShipment(Request $request, Order $order): JsonResponse
    {
        if (!$order->dpd_shipment_id) {
            return response()->json([
                'error' => 'No DPD shipment to delete',
                'message' => 'Order does not have a DPD shipment',
            ], 422);
        }

        try {
            // Dispatch DPD shipment deletion job
            \App\Jobs\DeleteDpdShipmentJob::dispatch($order);

            // Log the deletion request
            $this->auditLogger->logAuditEvent(
                'order',
                $order->id,
                'dpd_shipment_deletion_requested',
                'api',
                $this->getApiClientId($request),
                [
                    'shipment_id' => $order->dpd_shipment_id,
                    'parcel_group_id' => $order->parcel_group_id,
                ]
            );

            return response()->json([
                'message' => 'DPD shipment deletion started.',
                'order_id' => $order->id,
                'shipment_id' => $order->dpd_shipment_id,
                'status' => 'queued',
            ], 202);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'DPD shipment deletion failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh tracking information for DPD shipment.
     */
    public function refreshDpdTracking(Request $request, Order $order): JsonResponse
    {
        if (!$order->dpd_shipment_id) {
            return response()->json([
                'error' => 'No DPD shipment to track',
                'message' => 'Order does not have a DPD shipment',
            ], 422);
        }

        // Find the shipping label associated with this DPD shipment
        $shippingLabel = $order->shippingLabels()
            ->where('carrier', 'dpd')
            ->where('carrier_shipment_id', $order->dpd_shipment_id)
            ->first();

        if (!$shippingLabel || !$shippingLabel->tracking_number) {
            return response()->json([
                'error' => 'No tracking number available',
                'message' => 'DPD shipment does not have a tracking number',
            ], 422);
        }

        try {
            $dpdService = app(\App\Services\Shipping\DpdApiService::class);
            $trackingData = $dpdService->getTrackingInfo($shippingLabel->tracking_number);

            // Update the shipping label with the latest tracking information
            $currentMeta = $shippingLabel->meta ?? [];
            $currentMeta['tracking_data'] = $trackingData;
            $currentMeta['last_tracking_update'] = now()->toISOString();

            $shippingLabel->update([
                'meta' => $currentMeta,
                'raw_response' => array_merge($shippingLabel->raw_response ?? [], [
                    'latest_tracking' => $trackingData,
                    'tracking_updated_at' => now()->toISOString(),
                ])
            ]);

            // Log the tracking refresh
            $this->auditLogger->logAuditEvent(
                'order',
                $order->id,
                'dpd_tracking_refreshed',
                'api',
                $this->getApiClientId($request),
                [
                    'tracking_number' => $shippingLabel->tracking_number,
                    'tracking_status' => $trackingData['status'] ?? 'unknown',
                ]
            );

            return response()->json([
                'message' => 'Tracking information refreshed successfully',
                'tracking_data' => $trackingData,
                'updated_at' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to refresh tracking information',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate PDF for order.
     */
    public function generatePdf(Request $request, Order $order): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'images' => ['required', 'array', 'min:1', 'max:9'],
            'images.*' => ['required', 'url'],
            'cell_size' => ['required', 'integer', 'min:100', 'max:600'],
            'overlay_url' => ['required', 'url'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Additional validation for image URLs (MIME type check would require actual requests)
        foreach ($request->images as $imageUrl) {
            if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                return response()->json([
                    'error' => 'Invalid image URL',
                    'message' => "Invalid URL: {$imageUrl}",
                ], 422);
            }
        }

        try {
            // Dispatch PDF generation job
            \App\Jobs\GenerateOrderPdfJob::dispatch(
                $order,
                $request->images,
                $request->cell_size,
                $request->overlay_url
            );

            // Log the PDF generation request
            $this->auditLogger->logAuditEvent(
                'order',
                $order->id,
                'pdf_generation_requested',
                'api',
                $this->getApiClientId($request),
                [
                    'images_count' => count($request->images),
                    'cell_size' => $request->cell_size,
                    'overlay_url' => $request->overlay_url,
                ]
            );

            return response()->json([
                'message' => 'PDF generation started. Check back later for completion.',
                'order_id' => $order->id,
                'status' => 'processing',
            ], 202);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'PDF generation failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get API client ID from request.
     */
    private function getApiClientId(Request $request): string
    {
        $apiClient = $request->attributes->get('api_client');
        return $apiClient ? $apiClient->key_id : 'unknown';
    }
}
