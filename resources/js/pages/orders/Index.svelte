<script lang="ts">
    import { useOrders } from '@/hooks/use-orders';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { type BreadcrumbItem, type OrderFilters, type OrderStatus, type Carrier } from '@/types';
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
        MoreHorizontal, 
        Eye, 
        Edit, 
        Trash2,
        RefreshCw,
        Download,
        ChevronLeft,
        ChevronRight
    } from 'lucide-svelte';
    import { router } from '@inertiajs/svelte';

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Orders', href: '/orders' },
    ];

    // Initialize filters from URL params - in Inertia we'll get these from props or use simple state
    let filters: OrderFilters = $state({
        page: 1,
        per_page: 20,
        search: '',
        status: [],
        carrier: [],
        sort: 'created_at',
        direction: 'desc',
    });

    // Get orders using TanStack Query - make it reactive
    let ordersQuery = $derived(useOrders(filters));

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

    function navigateToOrders() {
        const params = new URLSearchParams();
        
        Object.entries(filters).forEach(([key, value]) => {
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
        filters.page = 1; // Reset to first page
        navigateToOrders();
    }

    function handleFilterChange() {
        filters.page = 1; // Reset to first page
        navigateToOrders();
    }

    function changePage(newPage: number) {
        filters.page = newPage;
        navigateToOrders();
    }

    function handleSort(column: string) {
        if (filters.sort === column) {
            filters.direction = filters.direction === 'asc' ? 'desc' : 'asc';
        } else {
            filters.sort = column;
            filters.direction = 'asc';
        }
        navigateToOrders();
    }

    function clearFilters() {
        filters = {
            page: 1,
            per_page: 20,
            search: '',
            status: [],
            carrier: [],
            sort: 'created_at',
            direction: 'desc',
        };
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
                                bind:value={filters.search}
                                class="pl-8"
                                onkeydown={(e) => e.key === 'Enter' && handleSearch()}
                            />
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="space-y-2">
                        <Label for="status">Status</Label>
                        <Select.Root type="single">
                            <Select.Trigger>
                                <span>Select status...</span>
                            </Select.Trigger>
                            <Select.Content>
                                {#each statusOptions as option}
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
                        <Select.Root type="single">
                            <Select.Trigger>
                                <span>Select carrier...</span>
                            </Select.Trigger>
                            <Select.Content>
                                {#each carrierOptions as option}
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
                            {#each Array(5) as _}
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
                                    {#if filters.sort === 'number'}
                                        {filters.direction === 'asc' ? '↑' : '↓'}
                                    {/if}
                                </Table.Head>
                                <Table.Head>PMI ID</Table.Head>
                                <Table.Head 
                                    class="cursor-pointer"
                                    onclick={() => handleSort('status')}
                                >
                                    Status
                                    {#if filters.sort === 'status'}
                                        {filters.direction === 'asc' ? '↑' : '↓'}
                                    {/if}
                                </Table.Head>
                                <Table.Head>Client</Table.Head>
                                <Table.Head>Carrier</Table.Head>
                                <Table.Head 
                                    class="cursor-pointer text-right"
                                    onclick={() => handleSort('total')}
                                >
                                    Total
                                    {#if filters.sort === 'total'}
                                        {filters.direction === 'asc' ? '↑' : '↓'}
                                    {/if}
                                </Table.Head>
                                <Table.Head 
                                    class="cursor-pointer"
                                    onclick={() => handleSort('created_at')}
                                >
                                    Created
                                    {#if filters.sort === 'created_at'}
                                        {filters.direction === 'asc' ? '↑' : '↓'}
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
                                        <Badge class={statusColors[order.status]}>
                                            {order.status}
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
                                        {formatCurrency(order.total)}
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
                    {#if ordersData.meta.last_page > 1}
                        <div class="flex items-center justify-between px-6 py-4 border-t">
                            <div class="text-sm text-muted-foreground">
                                Showing {ordersData.meta.from} to {ordersData.meta.to} of {ordersData.meta.total} orders
                            </div>
                            <div class="flex items-center space-x-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onclick={() => changePage(ordersData.meta.current_page - 1)}
                                    disabled={ordersData.meta.current_page === 1}
                                >
                                    <ChevronLeft class="h-4 w-4" />
                                    Previous
                                </Button>
                                
                                <div class="flex items-center space-x-1">
                                    {#each Array.from({length: Math.min(5, ordersData.meta.last_page)}, (_, i) => {
                                        const start = Math.max(1, ordersData.meta.current_page - 2);
                                        return start + i;
                                    }).filter(page => page <= ordersData.meta.last_page) as pageNum}
                                        <Button
                                            variant={pageNum === ordersData.meta.current_page ? "default" : "outline"}
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
                                    onclick={() => changePage(ordersData.meta.current_page + 1)}
                                    disabled={ordersData.meta.current_page === ordersData.meta.last_page}
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