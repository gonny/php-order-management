<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\ShippingLabel;
use App\Services\Shipping\DpdApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateDpdLabelJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 300; // 5 minutes for DPD API calls

    public $backoff = [30, 60, 120]; // Exponential backoff

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order,
        public array $options = []
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(DpdApiService $dpdService): void
    {
        Log::info('GenerateDpdLabelJob: Starting DPD label generation', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->number,
            'shipping_method' => $this->order->shipping_method,
        ]);

        try {
            // Validate order requirements
            $this->validateOrder();

            // Check for order consolidation
            $ordersToProcess = $this->getOrdersForConsolidation();

            // Prepare shipment data
            $shipmentData = $this->prepareShipmentData($ordersToProcess);

            // Create DPD shipment
            $response = $dpdService->createShipment($shipmentData);

            // Download and save PDF label
            $labelPath = $this->downloadAndSaveLabel($dpdService, $response['shipment_id']);

            // Create shipping label records and update orders
            $this->updateOrdersWithShipmentInfo($ordersToProcess, $response, $labelPath);

            Log::info('GenerateDpdLabelJob: DPD label generated successfully', [
                'order_id' => $this->order->id,
                'shipment_id' => $response['shipment_id'],
                'tracking_number' => $response['tracking_number'] ?? null,
                'consolidated_orders' => count($ordersToProcess),
            ]);

        } catch (\Exception $e) {
            Log::error('GenerateDpdLabelJob: Failed to generate DPD label', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Create failed shipping label record
            $this->createFailedShippingLabel($e->getMessage());

            throw $e;
        }
    }

    /**
     * Validate order requirements for DPD label generation
     */
    private function validateOrder(): void
    {
        if (!$this->order->shippingAddress) {
            throw new \InvalidArgumentException('Order missing shipping address');
        }

        if (!in_array($this->order->shipping_method, ['DPD_Home', 'DPD_PickupPoint'])) {
            throw new \InvalidArgumentException('Invalid shipping method for DPD: ' . $this->order->shipping_method);
        }

        if ($this->order->shipping_method === 'DPD_PickupPoint' && !$this->order->pickup_point_id) {
            throw new \InvalidArgumentException('Pickup point ID required for DPD pickup point delivery');
        }

        if (!in_array($this->order->shippingAddress->country_code, ['CZ', 'SK'])) {
            throw new \InvalidArgumentException('DPD shipping only available for CZ and SK');
        }
    }

    /**
     * Get orders for consolidation based on business rules
     */
    private function getOrdersForConsolidation(): array
    {
        $ordersToProcess = [$this->order];

        // Check if we should consolidate orders
        if ($this->order->status === 'paid') {
            // Find other paid orders for the same customer with "rozpracovaná" status
            $consolidatableOrders = Order::where('client_id', $this->order->client_id)
                ->where('status', 'paid')
                ->where('id', '!=', $this->order->id)
                ->whereNull('dpd_shipment_id') // Not already shipped
                ->where('shipping_method', $this->order->shipping_method)
                ->where('pickup_point_id', $this->order->pickup_point_id) // Same pickup point if applicable
                ->get();

            if ($consolidatableOrders->isNotEmpty()) {
                $ordersToProcess = array_merge($ordersToProcess, $consolidatableOrders->toArray());

                Log::info('GenerateDpdLabelJob: Consolidating orders', [
                    'primary_order_id' => $this->order->id,
                    'consolidated_order_ids' => $consolidatableOrders->pluck('id')->toArray(),
                    'total_orders' => count($ordersToProcess),
                ]);
            }
        }

        return $ordersToProcess;
    }

    /**
     * Prepare shipment data for DPD API
     */
    private function prepareShipmentData(array $orders): array
    {
        $totalItems = 0;
        $orderNumbers = [];

        foreach ($orders as $order) {
            $orderObj = $order instanceof Order ? $order : Order::find($order['id']);
            $totalItems += $orderObj->items()->sum('quantity');
            $orderNumbers[] = $orderObj->number;
        }

        $packages = DpdApiService::calculatePackageDimensions($totalItems);

        $parcelGroupId = count($orders) > 1 ? 'GRP_' . $this->order->number . '_' . now()->format('YmdHis') : null;

        return [
            'order_number' => implode(',', $orderNumbers),
            'shipping_method' => $this->order->shipping_method,
            'pickup_point_id' => $this->order->pickup_point_id,
            'parcel_group_id' => $parcelGroupId,
            'recipient' => [
                'name' => $this->order->shippingAddress->name,
                'phone' => $this->order->shippingAddress->phone,
                'email' => $this->order->client->email,
            ],
            'address' => [
                'street1' => $this->order->shippingAddress->street1,
                'street2' => $this->order->shippingAddress->street2,
                'city' => $this->order->shippingAddress->city,
                'postal_code' => $this->order->shippingAddress->postal_code,
                'country_code' => $this->order->shippingAddress->country_code,
            ],
            'packages' => $packages,
        ];
    }

    /**
     * Download and save the PDF label
     */
    private function downloadAndSaveLabel(DpdApiService $dpdService, string $shipmentId): string
    {
        $labelContent = $dpdService->downloadLabel($shipmentId);

        $fileName = "dpd_label_{$this->order->number}_{$shipmentId}.pdf";
        $labelPath = "labels/{$fileName}";

        Storage::disk('local')->put($labelPath, $labelContent);

        return $labelPath;
    }

    /**
     * Update orders with shipment information
     */
    private function updateOrdersWithShipmentInfo(array $orders, array $response, string $labelPath): void
    {
        $shipmentId = $response['shipment_id'];
        $trackingNumber = $response['tracking_number'] ?? null;
        $parcelGroupId = $response['parcel_group_id'] ?? null;

        foreach ($orders as $order) {
            $orderObj = $order instanceof Order ? $order : Order::find($order['id']);

            // Update order with DPD information
            $orderObj->update([
                'dpd_shipment_id' => $shipmentId,
                'pdf_label_path' => $labelPath,
                'parcel_group_id' => $parcelGroupId,
            ]);

            // Create shipping label record
            $orderObj->shippingLabels()->create([
                'carrier' => 'dpd',
                'carrier_shipment_id' => $shipmentId,
                'tracking_number' => $trackingNumber,
                'file_path' => $labelPath,
                'format' => 'pdf',
                'status' => ShippingLabel::STATUS_GENERATED,
                'raw_response' => $response,
                'meta' => [
                    'shipping_method' => $orderObj->shipping_method,
                    'pickup_point_id' => $orderObj->pickup_point_id,
                    'parcel_group_id' => $parcelGroupId,
                    'generated_at' => now()->toISOString(),
                ],
            ]);
        }
    }

    /**
     * Create failed shipping label record
     */
    private function createFailedShippingLabel(string $errorMessage): void
    {
        $this->order->shippingLabels()->create([
            'carrier' => 'dpd',
            'status' => ShippingLabel::STATUS_FAILED,
            'raw_response' => [
                'error' => $errorMessage,
                'failed_at' => now()->toISOString(),
                'job_id' => $this->job?->getJobId(),
            ],
            'meta' => [
                'shipping_method' => $this->order->shipping_method,
                'pickup_point_id' => $this->order->pickup_point_id,
                'error_message' => $errorMessage,
            ],
        ]);
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateDpdLabelJob: Job failed permanently', [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}
