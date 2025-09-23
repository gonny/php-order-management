<script lang="ts">
    import { useOrders } from '@/packages/orders';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import type { BreadcrumbItem } from '@/types';
    import * as Card from '@/components/ui/card';
    import { Badge } from '@/components/ui/badge';
    import * as Table from '@/components/ui/table';
    import * as Select from '@/components/ui/select';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Skeleton } from '@/components/ui/skeleton';
    import { Label } from '@/components/ui/label';
    import { 
        Search, 
        Filter, 
        Plus, 
        Eye, 
        Edit, 
        RefreshCw,
        ChevronLeft,
        ChevronRight
    } from 'lucide-svelte';
    import { router } from '@inertiajs/svelte';
    import {
        getStatusColor,
        getStatusLabel,
        formatCurrency,
        createOrderFiltersStore,
    } from '@/packages/orders';

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Orders', href: '/orders' },
    ];

    // Initialize filters using Svelte 5 runes
    const filtersStore = createOrderFiltersStore({
        page: 1,
        per_page: 20,
        sort: 'created_at',
        direction: 'desc',
    });

    // Get orders using TanStack Query with reactive filters (Svelte 5 runes syntax)
    const ordersQuery = $derived(useOrders(filtersStore.filters));

    // Status options
    const statusOptions = [
        { value: 'new', label: 'New' },
        { value: 'confirmed', label: 'Confirmed' },
        { value: 'paid', label: 'Paid' },
        { value: 'fulfilled', label: 'Fulfilled' },
        { value: 'completed', label: 'Completed' },
        { value: 'cancelled', label: 'Cancelled' },
        { value: 'on_hold', label: 'On Hold' },
        { value: 'failed', label: 'Failed' },
    ];

    const carrierOptions = [
        { value: 'balikovna', label: 'Balíkovna' },
        { value: 'dpd', label: 'DPD' },
    ];

    // Selected values for filters
    let selectedStatus = $state<string>('');
    let selectedCarrier = $state<string>('');

    // FIXED: Use direct form inputs that update filters immediately
    // Remove reactive loops by not using $effect blocks

    function formatDateTime(dateString: string): string {
        return new Intl.DateTimeFormat('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(new Date(dateString));
    }

    function navigateToOrders() {
        const params = new URLSearchParams();
        
        Object.entries(filtersStore.filters).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                if (Array.isArray(value)) {
                    value.forEach(v => params.append(key, v.toString()));
                } else {
                    params.append(key, value.toString());
                }
            }
        });

        const queryString = params.toString();
        router.visit(`/orders${queryString ? `?${queryString}` : ''}`, {
            preserveState: true,
            preserveScroll: true,
        });
    }

    function handleSearch() {
        navigateToOrders();
    }

    function handleFilterChange() {
        navigateToOrders();
    }

    function changePage(newPage: number) {
        filtersStore.setPagination(newPage);
        navigateToOrders();
    }

    function handleSort(column: string) {
        const currentDirection = filtersStore.filters.sort === column 
            ? (filtersStore.filters.direction === 'asc' ? 'desc' : 'asc')
            : 'asc';
        filtersStore.setSort(column, currentDirection);
        navigateToOrders();
    }

    function clearFilters() {
        filtersStore.clearFilters();
        navigateToOrders();
    }

    function navigateToOrder(orderId: string) {
        router.visit(`/orders/${orderId}`);
    }

    function navigateToCreateOrder() {
        router.visit('/orders/create');
    }
</script>

<svelte:head>
    <title>Orders - Order Management</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <div class="flex-1 space-y-4 p-4 md:p-8 pt-6">
        <!-- Header -->
        <div class="flex items-center justify-between space-y-2">
            <h2 class="text-3xl font-bold tracking-tight">Orders</h2>
            <div class="flex items-center space-x-2">
                <Button
                    variant="outline"
                    size="sm"
                    onclick={() => $ordersQuery.refetch()}
                    disabled={$ordersQuery.isFetching}
                >
                    <RefreshCw class="mr-2 h-4 w-4 {$ordersQuery.isFetching ? 'animate-spin' : ''}" />
                    Refresh
                </Button>
                <Button onclick={navigateToCreateOrder}>
                    <Plus class="mr-2 h-4 w-4" />
                    New Order
                </Button>
            </div>
        </div>

        <!-- Filters -->
        <Card.Root>
            <Card.Header>
                <Card.Title class="text-lg">Filters</Card.Title>
            </Card.Header>
            <Card.Content>
                <div class="grid gap-4 md:grid-cols-4">
                    <!-- Search -->
                    <div class="space-y-2">
                        <Label for="search">Search</Label>
                        <div class="relative">
                            <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                id="search"
                                placeholder="Order number, PMI ID..."
                                bind:value={filtersStore.filters.search}
                                class="pl-8"
                                onkeydown={(e) => e.key === 'Enter' && handleSearch()}
                                oninput={(e) => filtersStore.setSearch((e.target as HTMLInputElement).value)}
                            />
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="space-y-2">
                        <Label for="status">Status</Label>
                        <Select.Root 
                            type="single"
                            bind:value={selectedStatus}
                            onValueChange={(value: string | undefined) => {
                                if (value) {
                                    filtersStore.setStatus([value]);
                                } else {
                                    filtersStore.setStatus([]);
                                }
                            }}
                        >
                            <Select.Trigger>
                                <Select.Value placeholder="Select status..." />
                            </Select.Trigger>
                            <Select.Content>
                                {#each statusOptions as option (option.value)}
                                    <Select.Item value={option.value}>
                                        {option.label}
                                    </Select.Item>
                                {/each}
                            </Select.Content>
                        </Select.Root>
                    </div>

                    <!-- Carrier Filter -->
                    <div class="space-y-2">
                        <Label for="carrier">Carrier</Label>
                        <Select.Root 
                            type="single"
                            bind:value={selectedCarrier}
                            onValueChange={(value: string | undefined) => {
                                if (value) {
                                    filtersStore.setCarrier([value]);
                                } else {
                                    filtersStore.setCarrier([]);
                                }
                            }}
                        >
                            <Select.Trigger>
                                <Select.Value placeholder="Select carrier..." />
                            </Select.Trigger>
                            <Select.Content>
                                {#each carrierOptions as option (option.value)}
                                    <Select.Item value={option.value}>
                                        {option.label}
                                    </Select.Item>
                                {/each}
                            </Select.Content>
                        </Select.Root>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-2">
                        <Label>&nbsp;</Label>
                        <div class="flex space-x-2">
                            <Button onclick={handleFilterChange} class="flex-1">
                                <Filter class="mr-2 h-4 w-4" />
                                Apply
                            </Button>
                            <Button variant="outline" onclick={clearFilters}>
                                Clear
                            </Button>
                        </div>
                    </div>
                </div>
            </Card.Content>
        </Card.Root>

        <!-- Orders Table -->
        <Card.Root>
            <Card.Content class="p-0">
                {#if $ordersQuery.isLoading}
                    <!-- Loading state -->
                    <div class="p-6">
                        <div class="space-y-4">
                            <!-- eslint-disable-next-line @typescript-eslint/no-unused-vars -->
                            {#each Array(5) as _, index (index)}
                                <div class="flex items-center space-x-4">
                                    <Skeleton class="h-12 w-12 rounded" />
                                    <div class="space-y-2 flex-1">
                                        <Skeleton class="h-4 w-[200px]" />
                                        <Skeleton class="h-4 w-[150px]" />
                                    </div>
                                    <Skeleton class="h-8 w-[80px]" />
                                    <Skeleton class="h-8 w-[100px]" />
                                </div>
                            {/each}
                        </div>
                    </div>
                {:else if $ordersQuery.error}
                    <!-- Error state -->
                    <div class="p-6 text-center">
                        <p class="text-red-600 dark:text-red-400">
                            {$ordersQuery.error?.message || 'Failed to load orders'}
                        </p>
                        <Button 
                            class="mt-4" 
                            variant="outline" 
                            onclick={() => $ordersQuery.refetch()}
                        >
                            Try Again
                        </Button>
                    </div>
                {:else if $ordersQuery.data}
                    {@const ordersData = $ordersQuery.data}
                    
                    <Table.Root>
                        <Table.Header>
                            <Table.Row>
                                <Table.Head 
                                    class="cursor-pointer"
                                    onclick={() => handleSort('number')}
                                >
                                    Order Number
                                    {#if filtersStore.filters.sort === 'number'}
                                        {filtersStore.filters.direction === 'asc' ? '↑' : '↓'}
                                    {/if}
                                </Table.Head>
                                <Table.Head>PMI ID</Table.Head>
                                <Table.Head 
                                    class="cursor-pointer"
                                    onclick={() => handleSort('status')}
                                >
                                    Status
                                    {#if filtersStore.filters.sort === 'status'}
                                        {filtersStore.filters.direction === 'asc' ? '↑' : '↓'}
                                    {/if}
                                </Table.Head>
                                <Table.Head>Client</Table.Head>
                                <Table.Head>Carrier</Table.Head>
                                <Table.Head 
                                    class="cursor-pointer text-right"
                                    onclick={() => handleSort('total')}
                                >
                                    Total
                                    {#if filtersStore.filters.sort === 'total'}
                                        {filtersStore.filters.direction === 'asc' ? '↑' : '↓'}
                                    {/if}
                                </Table.Head>
                                <Table.Head 
                                    class="cursor-pointer"
                                    onclick={() => handleSort('created_at')}
                                >
                                    Created
                                    {#if filtersStore.filters.sort === 'created_at'}
                                        {filtersStore.filters.direction === 'asc' ? '↑' : '↓'}
                                    {/if}
                                </Table.Head>
                                <Table.Head class="text-right">Actions</Table.Head>
                            </Table.Row>
                        </Table.Header>
                        <Table.Body>
                            {#each ordersData.data as order (order.id)}
                                <Table.Row>
                                    <Table.Cell class="font-medium">#{order.number}</Table.Cell>
                                    <Table.Cell>
                                        {order.pmi_id || '-'}
                                    </Table.Cell>
                                    <Table.Cell>
                                        <Badge class={getStatusColor(order.status)}>
                                            {getStatusLabel(order.status)}
                                        </Badge>
                                    </Table.Cell>
                                    <Table.Cell>
                                        {#if order.client}
                                            <div>
                                                <p class="font-medium">
                                                    {order.client.first_name} {order.client.last_name}
                                                </p>
                                                <p class="text-sm text-muted-foreground">
                                                    {order.client.email}
                                                </p>
                                            </div>
                                        {:else}
                                            -
                                        {/if}
                                    </Table.Cell>
                                    <Table.Cell class="capitalize">{order.carrier}</Table.Cell>
                                    <Table.Cell class="text-right">
                                        {formatCurrency(order.total, order.currency)}
                                    </Table.Cell>
                                    <Table.Cell>
                                        {formatDateTime(order.created_at)}
                                    </Table.Cell>
                                    <Table.Cell class="text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onclick={() => navigateToOrder(order.id)}
                                            >
                                                <Eye class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onclick={() => router.visit(`/orders/${order.id}/edit`)}
                                            >
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </Table.Cell>
                                </Table.Row>
                            {/each}
                        </Table.Body>
                    </Table.Root>

                    <!-- Pagination -->
                    {#if ordersData.last_page > 1}
                        <div class="flex items-center justify-between px-6 py-4 border-t">
                            <div class="text-sm text-muted-foreground">
                                Showing {ordersData.from} to {ordersData.to} of {ordersData.total} orders
                            </div>
                            <div class="flex items-center space-x-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onclick={() => changePage(ordersData.current_page - 1)}
                                    disabled={ordersData.current_page === 1}
                                >
                                    <ChevronLeft class="h-4 w-4" />
                                    Previous
                                </Button>

                                <div class="flex items-center space-x-1">
                                    {#each Array.from({length: Math.min(5, ordersData.last_page)}, (_, i) => {
                                        const start = Math.max(1, ordersData.current_page - 2);
                                        return start + i;
                                    }).filter(page => page <= ordersData.last_page) as pageNum (pageNum)}
                                        <Button
                                            variant={pageNum === ordersData.current_page ? "default" : "outline"}
                                            size="sm"
                                            onclick={() => changePage(pageNum)}
                                        >
                                            {pageNum}
                                        </Button>
                                    {/each}
                                </div>

                                <Button
                                    variant="outline"
                                    size="sm"
                                    onclick={() => changePage(ordersData.current_page + 1)}
                                    disabled={ordersData.current_page === ordersData.last_page}
                                >
                                    Next
                                    <ChevronRight class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    {/if}
                {/if}
            </Card.Content>
        </Card.Root>
    </div>
</AppLayout>