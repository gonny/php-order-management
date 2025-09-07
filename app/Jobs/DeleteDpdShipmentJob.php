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

class DeleteDpdShipmentJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(DpdApiService $dpdService): void
    {
        Log::info('DeleteDpdShipmentJob: Starting DPD shipment deletion', [
            'order_id' => $this->order->id,
            'shipment_id' => $this->order->dpd_shipment_id,
        ]);

        try {
            if (!$this->order->dpd_shipment_id) {
                throw new \InvalidArgumentException('Order has no DPD shipment to delete');
            }

            // Delete shipment via DPD API
            $dpdService->deleteShipment($this->order->dpd_shipment_id);

            // Update order - clear DPD shipment data
            $this->order->update([
                'dpd_shipment_id' => null,
                'pdf_label_path' => null,
                'parcel_group_id' => null,
            ]);

            // Void shipping labels
            $this->order->shippingLabels()
                ->where('carrier', 'dpd')
                ->where('status', ShippingLabel::STATUS_GENERATED)
                ->update(['status' => ShippingLabel::STATUS_VOIDED]);

            // Delete PDF file if it exists
            if ($this->order->pdf_label_path && Storage::disk('local')->exists($this->order->pdf_label_path)) {
                Storage::disk('local')->delete($this->order->pdf_label_path);
            }

            // Handle consolidated shipments
            $this->handleConsolidatedShipmentDeletion();

            Log::info('DeleteDpdShipmentJob: DPD shipment deleted successfully', [
                'order_id' => $this->order->id,
                'shipment_id' => $this->order->dpd_shipment_id,
            ]);

        } catch (\Exception $e) {
            Log::error('DeleteDpdShipmentJob: Failed to delete DPD shipment', [
                'order_id' => $this->order->id,
                'shipment_id' => $this->order->dpd_shipment_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle deletion for consolidated shipments
     */
    private function handleConsolidatedShipmentDeletion(): void
    {
        if (!$this->order->parcel_group_id) {
            return;
        }

        // Find other orders in the same parcel group
        $relatedOrders = Order::where('parcel_group_id', $this->order->parcel_group_id)
            ->where('id', '!=', $this->order->id)
            ->get();

        foreach ($relatedOrders as $relatedOrder) {
            // Clear DPD shipment data for related orders
            $relatedOrder->update([
                'dpd_shipment_id' => null,
                'pdf_label_path' => null,
                'parcel_group_id' => null,
            ]);

            // Void their shipping labels
            $relatedOrder->shippingLabels()
                ->where('carrier', 'dpd')
                ->where('status', ShippingLabel::STATUS_GENERATED)
                ->update(['status' => ShippingLabel::STATUS_VOIDED]);
        }

        Log::info('DeleteDpdShipmentJob: Handled consolidated shipment deletion', [
            'parcel_group_id' => $this->order->parcel_group_id,
            'related_orders_count' => $relatedOrders->count(),
        ]);
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('DeleteDpdShipmentJob: Job failed permanently', [
            'order_id' => $this->order->id,
            'shipment_id' => $this->order->dpd_shipment_id,
            'error' => $exception->getMessage(),
        ]);
    }
}
