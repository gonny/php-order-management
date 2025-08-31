<script lang="ts">
    import { useOrder, useTransitionOrder } from '@/hooks/use-orders';
    import { useCreateLabel, useVoidLabel, useDownloadLabel } from '@/hooks/use-labels';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { type BreadcrumbItem, type OrderTransition } from '@/types';
    import * as Card from '@/components/ui/card';
    import * as Tabs from '@/components/ui/tabs';
    import * as Table from '@/components/ui/table';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Skeleton } from '@/components/ui/skeleton';
    import { Separator } from '@/components/ui/separator';
    import { 
        ArrowLeft, 
        Edit, 
        RefreshCw, 
        FileText, 
        Download, 
        Trash2,
        Package,
        User,
        History,
        Webhook,
        AlertTriangle,
        CheckCircle,
        Clock
    } from 'lucide-svelte';
    import { router } from '@inertiajs/svelte';

    // Props from Inertia
    let { orderId } = $props<{ orderId: string }>();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Orders', href: '/orders' },
        { title: `Order #${orderId}`, href: `/orders/${orderId}` },
    ];

    // Get order data
    const orderQuery = useOrder(orderId);
    
    // Mutations
    const transitionMutation = useTransitionOrder();
    const createLabelMutation = useCreateLabel();
    const voidLabelMutation = useVoidLabel();
    const downloadLabelMutation = useDownloadLabel();

    // Status badge color mapping
    const statusColors = {
        new: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        confirmed: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        fulfilled: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        on_hold: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        failed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };

    // Available transitions based on current status
    function getAvailableTransitions(status: string): { transition: OrderTransition; label: string; variant?: string }[] {
        const transitions: Record<string, { transition: OrderTransition; label: string; variant?: string }[]> = {
            new: [
                { transition: 'confirm', label: 'Confirm Order' },
                { transition: 'cancel', label: 'Cancel Order', variant: 'destructive' },
            ],
            confirmed: [
                { transition: 'pay', label: 'Mark as Paid' },
                { transition: 'hold', label: 'Put on Hold', variant: 'secondary' },
                { transition: 'cancel', label: 'Cancel Order', variant: 'destructive' },
            ],
            paid: [
                { transition: 'fulfill', label: 'Mark as Fulfilled' },
                { transition: 'hold', label: 'Put on Hold', variant: 'secondary' },
                { transition: 'fail', label: 'Mark as Failed', variant: 'destructive' },
            ],
            fulfilled: [
                { transition: 'complete', label: 'Complete Order' },
            ],
            on_hold: [
                { transition: 'restart', label: 'Restart Order' },
                { transition: 'cancel', label: 'Cancel Order', variant: 'destructive' },
            ],
            failed: [
                { transition: 'restart', label: 'Restart Order' },
                { transition: 'cancel', label: 'Cancel Order', variant: 'destructive' },
            ],
        };
        
        return transitions[status] || [];
    }

    function formatCurrency(amount: number): string {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'EUR',
        }).format(amount);
    }

    function formatDateTime(dateString: string): string {
        return new Intl.DateTimeFormat('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(new Date(dateString));
    }

    function handleTransition(transition: OrderTransition) {
        $transitionMutation.mutate({ id: orderId, transition });
    }

    function handleCreateLabel() {
        $createLabelMutation.mutate({ orderId });
    }

    function handleVoidLabel(labelId: string) {
        $voidLabelMutation.mutate({ labelId, orderId });
    }

    function handleDownloadLabel(labelId: string) {
        $downloadLabelMutation.mutate(labelId);
    }

    // DPD-specific handlers
    let selectedPickupPointId: string | undefined = undefined; // Should be set via UI
    function handleCreateDpdLabel(shippingMethod: 'DPD_Home' | 'DPD_PickupPoint') {
        let pickupPointId: string | undefined = undefined;
        if (shippingMethod === 'DPD_PickupPoint') {
            if (!selectedPickupPointId) {
                alert('Please select a pickup point before creating the label.');
                return;
            }
            pickupPointId = selectedPickupPointId;
        }
        
        // Call DPD-specific API endpoint
        fetch(`/api/v1/orders/${orderId}/label/dpd`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                shipping_method: shippingMethod,
                pickup_point_id: pickupPointId,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                // Handle error
                console.error('DPD label creation failed:', data.message);
            } else {
                // Success - refresh order data
                $orderQuery.refetch();
            }
        })
        .catch(error => {
            console.error('DPD label creation error:', error);
        });
    }

    function handleDownloadDpdLabel(orderId: string) {
        // Download PDF label
        window.open(`/orders/${orderId}/label/dpd/download`, '_blank');
    }

    function handleDeleteDpdShipment(orderId: string) {
        if (confirm('Are you sure you want to delete this DPD shipment? This action cannot be undone.')) {
            fetch(`/api/v1/orders/${orderId}/shipment/dpd`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('DPD shipment deletion failed:', data.message);
                } else {
                    // Success - refresh order data
                    $orderQuery.refetch();
                }
            })
            .catch(error => {
                console.error('DPD shipment deletion error:', error);
            });
        }
    }

    // Tracking refresh functionality
    let isRefreshingTracking = false;

    function handleRefreshTracking(orderId: string) {
        isRefreshingTracking = true;
        
        fetch(`/api/v1/orders/${orderId}/tracking/refresh`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Tracking refresh failed:', data.message);
                alert('Failed to refresh tracking: ' + data.message);
            } else {
                console.log('Tracking refreshed successfully:', data);
                // Refresh order data to show updated tracking info
                $orderQuery.refetch();
            }
        })
        .catch(error => {
            console.error('Tracking refresh error:', error);
            alert('Failed to refresh tracking information');
        })
        .finally(() => {
            isRefreshingTracking = false;
        });
    }
</script>

<svelte:head>
    <title>Order #{orderId} - Order Management</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <div class="flex-1 space-y-4 p-4 md:p-8 pt-6">
        {#if $orderQuery.isLoading}
            <!-- Loading state -->
            <div class="space-y-4">
                <div class="flex items-center space-x-4">
                    <Skeleton class="h-8 w-[200px]" />
                    <Skeleton class="h-8 w-[100px]" />
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    {#each Array(3) as _}
                        <Card.Root>
                            <Card.Header>
                                <Skeleton class="h-4 w-[100px]" />
                            </Card.Header>
                            <Card.Content>
                                <Skeleton class="h-20 w-full" />
                            </Card.Content>
                        </Card.Root>
                    {/each}
                </div>
            </div>
        {:else if $orderQuery.error}
            <!-- Error state -->
            <Card.Root class="border-red-200 dark:border-red-800">
                <Card.Header>
                    <Card.Title class="text-red-800 dark:text-red-200 flex items-center">
                        <AlertTriangle class="mr-2 h-5 w-5" />
                        Failed to load order
                    </Card.Title>
                </Card.Header>
                <Card.Content>
                    <p class="text-red-600 dark:text-red-400">
                        {$orderQuery.error?.message || 'An error occurred while loading the order'}
                    </p>
                    <div class="flex space-x-2 mt-4">
                        <Button 
                            variant="outline" 
                            onclick={() => $orderQuery.refetch()}
                        >
                            Try Again
                        </Button>
                        <Button 
                            variant="outline" 
                            onclick={() => router.visit('/orders')}
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Orders
                        </Button>
                    </div>
                </Card.Content>
            </Card.Root>
        {:else if $orderQuery.data}
            {@const order = $orderQuery.data}
            
            <!-- Header -->
            <div class="flex items-center justify-between space-y-2">
                <div class="flex items-center space-x-4">
                    <Button
                        variant="outline"
                        size="sm"
                        onclick={() => router.visit('/orders')}
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back
                    </Button>
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight">Order #{order.number}</h2>
                        <p class="text-muted-foreground">
                            Created {formatDateTime(order.created_at)}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <Badge class={statusColors[order.status]}>
                        {order.status}
                    </Badge>
                    <Button
                        variant="outline"
                        size="sm"
                        onclick={() => $orderQuery.refetch()}
                        disabled={$orderQuery.isFetching}
                    >
                        <RefreshCw class="mr-2 h-4 w-4 {$orderQuery.isFetching ? 'animate-spin' : ''}" />
                        Refresh
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        onclick={() => router.visit(`/orders/${order.id}/edit`)}
                    >
                        <Edit class="mr-2 h-4 w-4" />
                        Edit
                    </Button>
                </div>
            </div>

            <!-- Quick Actions -->
            {#if getAvailableTransitions(order.status).length > 0}
                <Card.Root>
                    <Card.Header>
                        <Card.Title>Quick Actions</Card.Title>
                    </Card.Header>
                    <Card.Content>
                        <div class="flex flex-wrap gap-2">
                            {#each getAvailableTransitions(order.status) as { transition, label, variant }}
                                <Button
                                    variant={variant || 'default'}
                                    size="sm"
                                    onclick={() => handleTransition(transition)}
                                    disabled={$transitionMutation.isPending}
                                >
                                    {label}
                                </Button>
                            {/each}
                        </div>
                    </Card.Content>
                </Card.Root>
            {/if}

            <!-- Tabs -->
            <Tabs.Root value="overview" class="w-full">
                <Tabs.List class="grid w-full grid-cols-6">
                    <Tabs.Trigger value="overview">Overview</Tabs.Trigger>
                    <Tabs.Trigger value="items">Items</Tabs.Trigger>
                    <Tabs.Trigger value="client">Client</Tabs.Trigger>
                    <Tabs.Trigger value="labels">Labels</Tabs.Trigger>
                    <Tabs.Trigger value="history">History</Tabs.Trigger>
                    <Tabs.Trigger value="logs">Logs</Tabs.Trigger>
                </Tabs.List>

                <!-- Overview Tab -->
                <Tabs.Content value="overview" class="mt-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <!-- Order Details -->
                        <Card.Root>
                            <Card.Header>
                                <Card.Title class="flex items-center">
                                    <Package class="mr-2 h-5 w-5" />
                                    Order Details
                                </Card.Title>
                            </Card.Header>
                            <Card.Content class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-muted-foreground">Order Number</p>
                                        <p class="text-sm">#{order.number}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-muted-foreground">PMI ID</p>
                                        <p class="text-sm">{order.pmi_id || '-'}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-muted-foreground">Status</p>
                                        <Badge class={statusColors[order.status]}>
                                            {order.status}
                                        </Badge>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-muted-foreground">Carrier</p>
                                        <p class="text-sm capitalize">{order.carrier}</p>
                                    </div>
                                    {#if order.shipping_method}
                                        <div>
                                            <p class="text-sm font-medium text-muted-foreground">Shipping Method</p>
                                            <p class="text-sm">{order.shipping_method.replace('_', ' ')}</p>
                                        </div>
                                    {/if}
                                    {#if order.pickup_point_id}
                                        <div>
                                            <p class="text-sm font-medium text-muted-foreground">Pickup Point</p>
                                            <p class="text-sm">{order.pickup_point_id}</p>
                                        </div>
                                    {/if}
                                    <div>
                                        <p class="text-sm font-medium text-muted-foreground">Currency</p>
                                        <p class="text-sm">{order.currency}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-muted-foreground">Total</p>
                                        <p class="text-sm font-semibold">{formatCurrency(order.total_amount)}</p>
                                    </div>
                                    {#if order.dpd_shipment_id}
                                        <div class="col-span-2">
                                            <p class="text-sm font-medium text-muted-foreground">DPD Shipment ID</p>
                                            <p class="text-sm font-mono">{order.dpd_shipment_id}</p>
                                        </div>
                                    {/if}
                                </div>
                                
                                {#if order.notes}
                                    <Separator />
                                    <div>
                                        <p class="text-sm font-medium text-muted-foreground">Notes</p>
                                        <p class="text-sm">{order.notes}</p>
                                    </div>
                                {/if}
                            </Card.Content>
                        </Card.Root>

                        <!-- Financial Summary -->
                        <Card.Root>
                            <Card.Header>
                                <Card.Title>Financial Summary</Card.Title>
                            </Card.Header>
                            <Card.Content class="space-y-4">
                                <div class="flex justify-between">
                                    <span class="text-sm">Subtotal</span>
                                    <span class="text-sm">{formatCurrency(order.subtotal)}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm">Tax</span>
                                    <span class="text-sm">{formatCurrency(order.tax_total)}</span>
                                </div>
                                <Separator />
                                <div class="flex justify-between font-semibold">
                                    <span>Total</span>
                                    <span>{formatCurrency(order.total)}</span>
                                </div>
                            </Card.Content>
                        </Card.Root>
                    </div>
                </Tabs.Content>

                <!-- Items Tab -->
                <Tabs.Content value="items" class="mt-4">
                    <Card.Root>
                        <Card.Header>
                            <Card.Title>Order Items</Card.Title>
                        </Card.Header>
                        <Card.Content class="p-0">
                            {#if order.items && order.items.length > 0}
                                <Table.Root>
                                    <Table.Header>
                                        <Table.Row>
                                            <Table.Head>SKU</Table.Head>
                                            <Table.Head>Product Name</Table.Head>
                                            <Table.Head class="text-center">Quantity</Table.Head>
                                            <Table.Head class="text-right">Unit Price</Table.Head>
                                            <Table.Head class="text-right">Tax Rate</Table.Head>
                                            <Table.Head class="text-right">Total</Table.Head>
                                        </Table.Row>
                                    </Table.Header>
                                    <Table.Body>
                                        {#each order.items as item}
                                            <Table.Row>
                                                <Table.Cell class="font-medium">{item.sku}</Table.Cell>
                                                <Table.Cell>{item.name}</Table.Cell>
                                                <Table.Cell class="text-center">{item.qty}</Table.Cell>
                                                <Table.Cell class="text-right">{formatCurrency(item.price)}</Table.Cell>
                                                <Table.Cell class="text-right">{(item.tax_rate * 100).toFixed(1)}%</Table.Cell>
                                                <Table.Cell class="text-right">{formatCurrency(item.total)}</Table.Cell>
                                            </Table.Row>
                                        {/each}
                                    </Table.Body>
                                </Table.Root>
                            {:else}
                                <div class="p-6 text-center text-muted-foreground">
                                    No items found
                                </div>
                            {/if}
                        </Card.Content>
                    </Card.Root>
                </Tabs.Content>

                <!-- Client Tab -->
                <Tabs.Content value="client" class="mt-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <!-- Client Information -->
                        <Card.Root>
                            <Card.Header>
                                <Card.Title class="flex items-center">
                                    <User class="mr-2 h-5 w-5" />
                                    Client Information
                                </Card.Title>
                            </Card.Header>
                            <Card.Content>
                                {#if order.client}
                                    <div class="space-y-3">
                                        <div>
                                            <p class="text-sm font-medium text-muted-foreground">Name</p>
                                            <p class="text-sm">{order.client.first_name} {order.client.last_name}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-muted-foreground">Email</p>
                                            <p class="text-sm">{order.client.email}</p>
                                        </div>
                                        {#if order.client.phone}
                                            <div>
                                                <p class="text-sm font-medium text-muted-foreground">Phone</p>
                                                <p class="text-sm">{order.client.phone}</p>
                                            </div>
                                        {/if}
                                        {#if order.client.company}
                                            <div>
                                                <p class="text-sm font-medium text-muted-foreground">Company</p>
                                                <p class="text-sm">{order.client.company}</p>
                                            </div>
                                        {/if}
                                        {#if order.client.external_id}
                                            <div>
                                                <p class="text-sm font-medium text-muted-foreground">External ID</p>
                                                <p class="text-sm">{order.client.external_id}</p>
                                            </div>
                                        {/if}
                                    </div>
                                {:else}
                                    <p class="text-sm text-muted-foreground">No client information available</p>
                                {/if}
                            </Card.Content>
                        </Card.Root>

                        <!-- Shipping Address -->
                        <Card.Root>
                            <Card.Header>
                                <Card.Title>Shipping Address</Card.Title>
                            </Card.Header>
                            <Card.Content>
                                {#if order.shipping_address}
                                    <div class="space-y-1">
                                        <p class="text-sm font-medium">{order.shipping_address.name}</p>
                                        {#if order.shipping_address.company}
                                            <p class="text-sm">{order.shipping_address.company}</p>
                                        {/if}
                                        <p class="text-sm">{order.shipping_address.street1}</p>
                                        {#if order.shipping_address.street2}
                                            <p class="text-sm">{order.shipping_address.street2}</p>
                                        {/if}
                                        <p class="text-sm">
                                            {order.shipping_address.city}, {order.shipping_address.postal_code}
                                        </p>
                                        <p class="text-sm">{order.shipping_address.country_code}</p>
                                    </div>
                                {:else}
                                    <p class="text-sm text-muted-foreground">No shipping address available</p>
                                {/if}
                            </Card.Content>
                        </Card.Root>
                    </div>
                </Tabs.Content>

                <!-- Labels Tab -->
                <Tabs.Content value="labels" class="mt-4">
                    <div class="grid gap-4">
                        <!-- DPD Label Management (if carrier is DPD) -->
                        {#if order.carrier === 'dpd'}
                            <Card.Root>
                                <Card.Header class="flex flex-row items-center justify-between">
                                    <Card.Title>DPD Shipping Labels</Card.Title>
                                    {#if !order.dpd_shipment_id}
                                        <div class="flex space-x-2">
                                            <Button
                                                size="sm"
                                                onclick={() => handleCreateDpdLabel('DPD_Home')}
                                                disabled={$createLabelMutation.isPending}
                                            >
                                                <Package class="mr-2 h-4 w-4" />
                                                Home Delivery
                                            </Button>
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                onclick={() => handleCreateDpdLabel('DPD_PickupPoint')}
                                                disabled={$createLabelMutation.isPending}
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
                                                    <p class="text-sm text-muted-foreground">
                                                        Method: {order.shipping_method || 'Standard'}
                                                    </p>
                                                    {#if order.pickup_point_id}
                                                        <p class="text-sm text-muted-foreground">
                                                            Pickup Point: {order.pickup_point_id}
                                                        </p>
                                                    {/if}
                                                    {#if order.parcel_group_id}
                                                        <p class="text-sm text-muted-foreground">
                                                            Consolidated Group: {order.parcel_group_id}
                                                        </p>
                                                    {/if}
                                                    
                                                    <!-- Display tracking information if available -->
                                                    {#if order.shipping_labels && order.shipping_labels.length > 0}
                                                        {@const dpdLabel = order.shipping_labels.find(label => label.carrier === 'dpd' && label.carrier_shipment_id === order.dpd_shipment_id)}
                                                        {#if dpdLabel?.tracking_number}
                                                            <p class="text-sm text-muted-foreground">
                                                                Tracking: {dpdLabel.tracking_number}
                                                            </p>
                                                            {#if dpdLabel.meta?.tracking_data?.status}
                                                                <p class="text-sm text-muted-foreground">
                                                                    Status: <Badge variant="secondary">{dpdLabel.meta.tracking_data.status}</Badge>
                                                                </p>
                                                            {/if}
                                                            {#if dpdLabel.meta?.last_tracking_update}
                                                                <p class="text-xs text-muted-foreground">
                                                                    Last updated: {formatDateTime(dpdLabel.meta.last_tracking_update)}
                                                                </p>
                                                            {/if}
                                                        {/if}
                                                    {/if}
                                                </div>
                                                <div class="flex space-x-2">
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onclick={() => handleRefreshTracking(order.id)}
                                                        disabled={isRefreshingTracking}
                                                    >
                                                        <RefreshCw class="mr-2 h-4 w-4 {isRefreshingTracking ? 'animate-spin' : ''}" />
                                                        Refresh Tracking
                                                    </Button>
                                                    {#if order.pdf_label_path}
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onclick={() => handleDownloadDpdLabel(order.id)}
                                                            disabled={$downloadLabelMutation.isPending}
                                                        >
                                                            <Download class="mr-2 h-4 w-4" />
                                                            Download PDF
                                                        </Button>
                                                    {/if}
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onclick={() => handleDeleteDpdShipment(order.id)}
                                                        disabled={$voidLabelMutation.isPending}
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
                                </Card.Content>
                            </Card.Root>
                        {/if}

                        <!-- Generic Shipping Labels -->
                        <Card.Root>
                            <Card.Header class="flex flex-row items-center justify-between">
                                <Card.Title>All Shipping Labels</Card.Title>
                                {#if order.carrier !== 'dpd'}
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
                                {#if order.shipping_labels && order.shipping_labels.length > 0}
                                    <div class="space-y-4">
                                        {#each order.shipping_labels as label}
                                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                                <div class="space-y-1">
                                                    <p class="text-sm font-medium">
                                                        {label.carrier.toUpperCase()} Label #{label.id.slice(-8)}
                                                    </p>
                                                    {#if label.tracking_number}
                                                        <p class="text-sm text-muted-foreground">
                                                            Tracking: {label.tracking_number}
                                                        </p>
                                                    {/if}
                                                    <p class="text-sm text-muted-foreground">
                                                        Status: <Badge variant={label.status === 'generated' ? 'default' : 'secondary'}>
                                                            {label.status}
                                                        </Badge>
                                                    </p>
                                                    {#if label.created_at}
                                                        <p class="text-xs text-muted-foreground">
                                                            Created: {formatDateTime(label.created_at)}
                                                        </p>
                                                    {/if}
                                                </div>
                                                <div class="flex space-x-2">
                                                    {#if label.file_path}
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onclick={() => handleDownloadLabel(label.id)}
                                                            disabled={$downloadLabelMutation.isPending}
                                                        >
                                                            <Download class="h-4 w-4" />
                                                        </Button>
                                                    {/if}
                                                    {#if label.status !== 'voided'}
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onclick={() => handleVoidLabel(label.id)}
                                                            disabled={$voidLabelMutation.isPending}
                                                        >
                                                            <Trash2 class="h-4 w-4" />
                                                        </Button>
                                                    {/if}
                                                </div>
                                            </div>
                                        {/each}
                                    </div>
                                {:else}
                                    <div class="text-center py-6">
                                        <Package class="mx-auto h-12 w-12 text-muted-foreground" />
                                        <p class="mt-2 text-sm text-muted-foreground">No shipping labels created yet</p>
                                    </div>
                                {/if}
                            </Card.Content>
                        </Card.Root>
                    </div>
                </Tabs.Content>

                <!-- Placeholder tabs -->
                <Tabs.Content value="history" class="mt-4">
                    <Card.Root>
                        <Card.Header>
                            <Card.Title class="flex items-center">
                                <History class="mr-2 h-5 w-5" />
                                Order History
                            </Card.Title>
                        </Card.Header>
                        <Card.Content>
                            <p class="text-center text-muted-foreground py-6">Order history functionality coming soon</p>
                        </Card.Content>
                    </Card.Root>
                </Tabs.Content>

                <Tabs.Content value="logs" class="mt-4">
                    <Card.Root>
                        <Card.Header>
                            <Card.Title class="flex items-center">
                                <FileText class="mr-2 h-5 w-5" />
                                System Logs
                            </Card.Title>
                        </Card.Header>
                        <Card.Content>
                            <p class="text-center text-muted-foreground py-6">System logs functionality coming soon</p>
                        </Card.Content>
                    </Card.Root>
                </Tabs.Content>
            </Tabs.Root>
        {/if}
    </div>
</AppLayout>