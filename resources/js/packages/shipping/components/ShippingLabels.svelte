<script lang="ts">
    import { useCreateLabel, useVoidLabel, useDownloadLabel } from '../hooks/use-labels';
    import { 
        getLabelStatusColor, 
        getLabelStatusLabel, 
        canVoidLabel, 
        canDownloadLabel, 
        formatLabelId, 
        formatTrackingNumber 
    } from '../utils/label-formatters';
    import type { Order } from '@/packages/orders';
    import type { ShippingLabel } from '../types';
    import * as Card from '@/components/ui/card';
    import { Button } from '@/components/ui/button';
    import { Badge } from '@/components/ui/badge';
    import { Package, Download, Trash2 } from 'lucide-svelte';

    // Props
    let { order, excludeCarriers = [] }: { 
        order: Order; 
        excludeCarriers?: string[]; 
    } = $props();

    // Mutations
    const createLabelMutation = useCreateLabel();
    const voidLabelMutation = useVoidLabel();
    const downloadLabelMutation = useDownloadLabel();

    // Filter labels based on excluded carriers
    const filteredLabels = $derived(
        order.shipping_labels?.filter(
            label => !excludeCarriers.includes(label.carrier)
        ) || []
    );

    // Handlers
    function handleCreateLabel() {
        $createLabelMutation.mutate({ orderId: order.id });
    }

    function handleVoidLabel(label: ShippingLabel) {
        if (confirm('Are you sure you want to void this label? This action cannot be undone.')) {
            $voidLabelMutation.mutate({ labelId: label.id, orderId: order.id });
        }
    }

    function handleDownloadLabel(label: ShippingLabel) {
        $downloadLabelMutation.mutate(label.id);
    }
</script>

<Card.Root>
    <Card.Header class="flex flex-row items-center justify-between">
        <Card.Title>Shipping Labels</Card.Title>
        {#if !excludeCarriers.includes(order.carrier || '')}
            <Button
                size="sm"
                onclick={handleCreateLabel}
                disabled={$createLabelMutation.isPending}
            >
                <Package class="mr-2 h-4 w-4" />
                Create Label
            </Button>
        {/if}
    </Card.Header>
    <Card.Content>
        {#if filteredLabels.length > 0}
            <div class="space-y-4">
                {#each filteredLabels as label (label.id)}
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div class="space-y-1">
                            <p class="text-sm font-medium">
                                {label.carrier.toUpperCase()} Label {formatLabelId(label.id)}
                            </p>
                            {#if label.tracking_number}
                                <p class="text-sm text-muted-foreground">
                                    Tracking: {formatTrackingNumber(label.tracking_number)}
                                </p>
                            {/if}
                            <div class="flex items-center space-x-2">
                                <Badge variant={getLabelStatusColor(label.status)}>
                                    {getLabelStatusLabel(label.status)}
                                </Badge>
                                {#if label.shipment_type}
                                    <Badge variant="outline">{label.shipment_type}</Badge>
                                {/if}
                            </div>
                            {#if label.created_at}
                                <p class="text-xs text-muted-foreground">
                                    Created: {new Date(label.created_at).toLocaleDateString()}
                                </p>
                            {/if}
                            {#if label.error_message}
                                <p class="text-xs text-red-600">
                                    Error: {label.error_message}
                                </p>
                            {/if}
                        </div>
                        <div class="flex space-x-2">
                            {#if canDownloadLabel(label)}
                                <Button
                                    size="sm"
                                    variant="outline"
                                    onclick={() => handleDownloadLabel(label)}
                                    disabled={$downloadLabelMutation.isPending}
                                >
                                    <Download class="mr-2 h-4 w-4" />
                                    Download
                                </Button>
                            {/if}
                            {#if canVoidLabel(label)}
                                <Button
                                    size="sm"
                                    variant="destructive"
                                    onclick={() => handleVoidLabel(label)}
                                    disabled={$voidLabelMutation.isPending}
                                >
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    Void
                                </Button>
                            {/if}
                        </div>
                    </div>
                {/each}
            </div>
        {:else}
            <div class="text-center py-6">
                <Package class="mx-auto h-12 w-12 text-muted-foreground" />
                <p class="mt-2 text-sm text-muted-foreground">
                    No shipping labels found
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    Create a label to get started
                </p>
            </div>
        {/if}
    </Card.Content>
</Card.Root>