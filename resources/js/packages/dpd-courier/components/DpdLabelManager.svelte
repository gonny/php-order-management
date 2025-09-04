<script lang="ts">
    import { useCreateDpdLabel, useDownloadDpdLabel, useDeleteDpdShipment } from '../hooks/use-dpd-labels';
    import { validatePickupPointRequired } from '../utils/dpd-helpers';
    import type { Order } from '@/packages/orders';
    import type { DpdShipmentCreateRequest } from '../types';
    import * as Card from '@/components/ui/card';
    import { Button } from '@/components/ui/button';
    import { Badge } from '@/components/ui/badge';
    import { 
        Package, 
        CheckCircle, 
        Download, 
        Trash2, 
        AlertTriangle 
    } from 'lucide-svelte';

    // Props
    let { order, selectedPickupPointId }: { 
        order: Order; 
        selectedPickupPointId?: string; 
    } = $props();

    // Mutations
    const createDpdLabelMutation = useCreateDpdLabel();
    const downloadDpdLabelMutation = useDownloadDpdLabel();
    const deleteDpdShipmentMutation = useDeleteDpdShipment();

    // Handlers
    function handleCreateDpdLabel(shippingMethod: 'DPD_Home' | 'DPD_PickupPoint') {
        if (!validatePickupPointRequired(shippingMethod, selectedPickupPointId)) {
            alert('Please select a pickup point before creating the label.');
            return;
        }

        const data: DpdShipmentCreateRequest = {
            shipping_method: shippingMethod,
            pickup_point_id: shippingMethod === 'DPD_PickupPoint' ? selectedPickupPointId : undefined,
        };

        $createDpdLabelMutation.mutate({ orderId: order.id, data });
    }

    function handleDownloadDpdLabel() {
        $downloadDpdLabelMutation.mutate(order.id);
    }

    function handleDeleteDpdShipment() {
        if (confirm('Are you sure you want to delete this DPD shipment? This action cannot be undone.')) {
            $deleteDpdShipmentMutation.mutate(order.id);
        }
    }
</script>

<Card.Root>
    <Card.Header class="flex flex-row items-center justify-between">
        <Card.Title>DPD Shipping Labels</Card.Title>
        {#if !order.dpd_shipment_id}
            <div class="flex space-x-2">
                <Button
                    size="sm"
                    onclick={() => handleCreateDpdLabel('DPD_Home')}
                    disabled={$createDpdLabelMutation.isPending}
                >
                    <Package class="mr-2 h-4 w-4" />
                    Home Delivery
                </Button>
                <Button
                    size="sm"
                    variant="outline"
                    onclick={() => handleCreateDpdLabel('DPD_PickupPoint')}
                    disabled={$createDpdLabelMutation.isPending}
                >
                    <Package class="mr-2 h-4 w-4" />
                    Pickup Point
                </Button>
            </div>
        {/if}
    </Card.Header>
    <Card.Content>
        {#if order.dpd_shipment_id}
            <!-- Existing DPD Shipment -->
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 border rounded-lg bg-green-50 dark:bg-green-900/20">
                    <div class="space-y-1">
                        <p class="text-sm font-medium flex items-center">
                            <CheckCircle class="mr-2 h-4 w-4 text-green-600" />
                            DPD Shipment Created
                        </p>
                        <p class="text-sm text-muted-foreground">
                            Shipment ID: {order.dpd_shipment_id}
                        </p>
                        {#if order.tracking_number}
                            <p class="text-sm text-muted-foreground">
                                Tracking: {order.tracking_number}
                            </p>
                        {/if}
                        <div class="flex items-center space-x-2">
                            <Badge variant="default">Active</Badge>
                            {#if order.shipping_method}
                                <Badge variant="outline">{order.shipping_method}</Badge>
                            {/if}
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <Button
                            size="sm"
                            variant="outline"
                            onclick={handleDownloadDpdLabel}
                            disabled={$downloadDpdLabelMutation.isPending}
                        >
                            <Download class="mr-2 h-4 w-4" />
                            Download
                        </Button>
                        <Button
                            size="sm"
                            variant="destructive"
                            onclick={handleDeleteDpdShipment}
                            disabled={$deleteDpdShipmentMutation.isPending}
                        >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete Shipment
                        </Button>
                    </div>
                </div>
            </div>
        {:else}
            <!-- No DPD Shipment -->
            <div class="text-center py-6">
                <Package class="mx-auto h-12 w-12 text-muted-foreground" />
                <p class="mt-2 text-sm text-muted-foreground">
                    No DPD shipment created yet
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    Choose between home delivery or pickup point delivery
                </p>
            </div>
        {/if}

        {#if $createDpdLabelMutation.isError}
            <div class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                <div class="flex items-center">
                    <AlertTriangle class="h-4 w-4 text-red-500 mr-2" />
                    <p class="text-sm text-red-700 dark:text-red-300">
                        {$createDpdLabelMutation.error?.message || 'Failed to create DPD label'}
                    </p>
                </div>
            </div>
        {/if}
    </Card.Content>
</Card.Root>