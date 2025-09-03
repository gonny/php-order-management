<script lang="ts">
    import { useOrder, useTransitionOrder } from '@/packages/orders';
    import { ShippingLabels } from '@/packages/shipping';
    import { DpdLabelManager } from '@/packages/dpd-courier';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import type { BreadcrumbItem } from '@/types';
    import type { OrderTransition } from '@/packages/orders';
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
        Package,
        User,
        History,
        AlertTriangle
    } from 'lucide-svelte';
    import { router } from '@inertiajs/svelte';
    import {
        getStatusColor,
        getStatusLabel,
        formatCurrency,
        getValidTransitions,
        getTransitionLabel,
        getTransitionVariant,
        requiresConfirmation,
    } from '@/packages/orders';

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

    // Available transitions based on current status - now using utility functions
    function getAvailableTransitions(status: string) {
        return getValidTransitions(status as any).map(transition => ({
            transition,
            label: getTransitionLabel(transition),
            variant: getTransitionVariant(transition),
            requiresConfirmation: requiresConfirmation(transition),
        }));
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

    // Pickup point selection for DPD (should be set via UI in real implementation)
    let selectedPickupPointId: string | undefined = undefined;


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
                    <!-- eslint-disable-next-line @typescript-eslint/no-unused-vars -->
                    {#each Array(3) as _, i (i)}
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
                    <Badge class={getStatusColor(order.status)}>
                        {getStatusLabel(order.status)}
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
                            {#each getAvailableTransitions(order.status) as { transition, label, variant } (transition)}
                                <Button
                                    variant={(variant as "default" | "link" | "outline" | "destructive" | "secondary" | "ghost" | undefined) || 'default'}
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
                                        <Badge class={getStatusColor(order.status)}>
                                            {getStatusLabel(order.status)}
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
                                        <p class="text-sm font-semibold">{formatCurrency(order.total_amount, order.currency)}</p>
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
                                    <span class="text-sm">{formatCurrency(order.subtotal, order.currency)}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm">Tax</span>
                                    <span class="text-sm">{formatCurrency(order.tax_total, order.currency)}</span>
                                </div>
                                <Separator />
                                <div class="flex justify-between font-semibold">
                                    <span>Total</span>
                                    <span>{formatCurrency(order.total, order.currency)}</span>
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
                                        {#each order.items as item (item.id)}
                                            <Table.Row>
                                                <Table.Cell class="font-medium">{item.sku}</Table.Cell>
                                                <Table.Cell>{item.name}</Table.Cell>
                                                <Table.Cell class="text-center">{item.qty}</Table.Cell>
                                                <Table.Cell class="text-right">{formatCurrency(item.price, order.currency)}</Table.Cell>
                                                <Table.Cell class="text-right">{(item.tax_rate * 100).toFixed(1)}%</Table.Cell>
                                                <Table.Cell class="text-right">{formatCurrency(item.total, order.currency)}</Table.Cell>
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
                            <DpdLabelManager {order} {selectedPickupPointId} />
                        {/if}

                        <!-- Generic Shipping Labels -->
                        <ShippingLabels {order} excludeCarriers={order.carrier === 'dpd' ? ['dpd'] : []} />
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