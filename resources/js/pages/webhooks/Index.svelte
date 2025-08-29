<script lang="ts">
    import { useWebhooks, useReprocessWebhook } from '@/hooks/use-webhooks';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { type BreadcrumbItem, type WebhookFilters, type WebhookSource, type WebhookStatus } from '@/types';
    import * as Card from '@/components/ui/card';
    import * as Dialog from '@/components/ui/dialog';
    import * as Table from '@/components/ui/table';
    import * as Select from '@/components/ui/select';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Skeleton } from '@/components/ui/skeleton';
    import { Label } from '@/components/ui/label';
    import { Textarea } from '@/components/ui/textarea';
    import { 
        Search, 
        Filter, 
        RefreshCw,
        ChevronLeft,
        ChevronRight,
        Eye,
        RotateCcw,
        Copy,
        CheckCircle,
        XCircle,
        Clock,
        AlertTriangle
    } from 'lucide-svelte';
    import { router } from '@inertiajs/svelte';

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Webhooks', href: '/webhooks' },
    ];

    // Initialize filters
    let filters: WebhookFilters = $state({
        page: 1,
        per_page: 20,
        sort: 'created_at',
        direction: 'desc',
    });

    // Get webhooks using TanStack Query
    let webhooksQuery = $derived(useWebhooks(filters));
    
    // Mutations
    const reprocessMutation = useReprocessWebhook();

    // Filter options
    const sourceOptions = [
        { value: 'balikovna', label: 'Balíkovna' },
        { value: 'dpd', label: 'DPD' },
        { value: 'payments', label: 'Payments' },
        { value: 'custom', label: 'Custom' },
    ];

    const statusOptions = [
        { value: 'pending', label: 'Pending' },
        { value: 'processing', label: 'Processing' },
        { value: 'completed', label: 'Completed' },
        { value: 'failed', label: 'Failed' },
    ];

    // Status badge color mapping
    const statusColors = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        failed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };

    // Dialog state
    let selectedWebhook = $state(null);
    let showDetailDialog = $state(false);
    let copiedPayload = $state(false);

    function formatDateTime(dateString: string): string {
        return new Intl.DateTimeFormat('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        }).format(new Date(dateString));
    }

    function navigateToWebhooks() {
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
        router.visit(`/webhooks${queryString ? `?${queryString}` : ''}`, {
            preserveState: true,
            preserveScroll: true,
        });
    }

    function handleFilterChange() {
        filters.page = 1; // Reset to first page
        navigateToWebhooks();
    }

    function changePage(newPage: number) {
        filters.page = newPage;
        navigateToWebhooks();
    }

    function handleSort(column: string) {
        if (filters.sort === column) {
            filters.direction = filters.direction === 'asc' ? 'desc' : 'asc';
        } else {
            filters.sort = column;
            filters.direction = 'asc';
        }
        navigateToWebhooks();
    }

    function clearFilters() {
        filters = {
            page: 1,
            per_page: 20,
            sort: 'created_at',
            direction: 'desc',
        };
        navigateToWebhooks();
    }

    function viewWebhookDetails(webhook: any) {
        selectedWebhook = webhook;
        showDetailDialog = true;
    }

    function handleReprocess(webhookId: string) {
        $reprocessMutation.mutate(webhookId);
    }

    async function copyPayload() {
        if (selectedWebhook?.payload) {
            try {
                await navigator.clipboard.writeText(JSON.stringify(selectedWebhook.payload, null, 2));
                copiedPayload = true;
                setTimeout(() => copiedPayload = false, 2000);
            } catch (err) {
                console.error('Failed to copy payload:', err);
            }
        }
    }
</script>

<svelte:head>
    <title>Webhooks - Order Management</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <div class="flex-1 space-y-4 p-4 md:p-8 pt-6">
        <!-- Header -->
        <div class="flex items-center justify-between space-y-2">
            <h2 class="text-3xl font-bold tracking-tight">Webhooks</h2>
            <Button
                variant="outline"
                size="sm"
                onclick={() => $webhooksQuery.refetch()}
                disabled={$webhooksQuery.isFetching}
            >
                <RefreshCw class="mr-2 h-4 w-4 {$webhooksQuery.isFetching ? 'animate-spin' : ''}" />
                Refresh
            </Button>
        </div>

        <!-- Filters -->
        <Card.Root>
            <Card.Header>
                <Card.Title class="text-lg">Filters</Card.Title>
            </Card.Header>
            <Card.Content>
                <div class="grid gap-4 md:grid-cols-5">
                    <!-- Source Filter -->
                    <div class="space-y-2">
                        <Label for="source">Source</Label>
                        <Select.Root multiple>
                            <Select.Trigger>
                                <span>Select source...</span>
                            </Select.Trigger>
                            <Select.Content>
                                {#each sourceOptions as option}
                                    <Select.Item value={option.value}>
                                        {option.label}
                                    </Select.Item>
                                {/each}
                            </Select.Content>
                        </Select.Root>
                    </div>

                    <!-- Status Filter -->
                    <div class="space-y-2">
                        <Label for="status">Status</Label>
                        <Select.Root multiple>
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

                    <!-- Event Type Filter -->
                    <div class="space-y-2">
                        <Label for="event_type">Event Type</Label>
                        <Input
                            id="event_type"
                            placeholder="e.g., order.paid"
                            bind:value={filters.event_type}
                        />
                    </div>

                    <!-- Order ID Filter -->
                    <div class="space-y-2">
                        <Label for="related_order_id">Order ID</Label>
                        <Input
                            id="related_order_id"
                            placeholder="Order ID"
                            bind:value={filters.related_order_id}
                        />
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

        <!-- Webhooks Table -->
        <Card.Root>
            <Card.Content class="p-0">
                {#if $webhooksQuery.isLoading}
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
                {:else if $webhooksQuery.error}
                    <!-- Error state -->
                    <div class="p-6 text-center">
                        <p class="text-red-600 dark:text-red-400">
                            {$webhooksQuery.error?.message || 'Failed to load webhooks'}
                        </p>
                        <Button 
                            class="mt-4" 
                            variant="outline" 
                            onclick={() => $webhooksQuery.refetch()}
                        >
                            Try Again
                        </Button>
                    </div>
                {:else if $webhooksQuery.data}
                    {@const webhooksData = $webhooksQuery.data}
                    
                    <Table.Root>
                        <Table.Header>
                            <Table.Row>
                                <Table.Head 
                                    class="cursor-pointer"
                                    onclick={() => handleSort('source')}
                                >
                                    Source
                                    {#if filters.sort === 'source'}
                                        {filters.direction === 'asc' ? '↑' : '↓'}
                                    {/if}
                                </Table.Head>
                                <Table.Head 
                                    class="cursor-pointer"
                                    onclick={() => handleSort('event_type')}
                                >
                                    Event Type
                                    {#if filters.sort === 'event_type'}
                                        {filters.direction === 'asc' ? '↑' : '↓'}
                                    {/if}
                                </Table.Head>
                                <Table.Head 
                                    class="cursor-pointer"
                                    onclick={() => handleSort('status')}
                                >
                                    Status
                                    {#if filters.sort === 'status'}
                                        {filters.direction === 'asc' ? '↑' : '↓'}
                                    {/if}
                                </Table.Head>
                                <Table.Head>Related Order</Table.Head>
                                <Table.Head>Retry Count</Table.Head>
                                <Table.Head>Processed At</Table.Head>
                                <Table.Head 
                                    class="cursor-pointer"
                                    onclick={() => handleSort('created_at')}
                                >
                                    Received
                                    {#if filters.sort === 'created_at'}
                                        {filters.direction === 'asc' ? '↑' : '↓'}
                                    {/if}
                                </Table.Head>
                                <Table.Head class="text-right">Actions</Table.Head>
                            </Table.Row>
                        </Table.Header>
                        <Table.Body>
                            {#each webhooksData.data as webhook}
                                <Table.Row>
                                    <Table.Cell>
                                        <Badge variant="secondary" class="capitalize">
                                            {webhook.source}
                                        </Badge>
                                    </Table.Cell>
                                    <Table.Cell class="font-medium">
                                        {webhook.event_type}
                                    </Table.Cell>
                                    <Table.Cell>
                                        <div class="flex items-center space-x-2">
                                            {#if webhook.status === 'completed'}
                                                <CheckCircle class="h-4 w-4 text-green-600" />
                                            {:else if webhook.status === 'failed'}
                                                <XCircle class="h-4 w-4 text-red-600" />
                                            {:else if webhook.status === 'processing'}
                                                <Clock class="h-4 w-4 text-blue-600" />
                                            {:else}
                                                <AlertTriangle class="h-4 w-4 text-yellow-600" />
                                            {/if}
                                            <Badge class={statusColors[webhook.status]}>
                                                {webhook.status}
                                            </Badge>
                                        </div>
                                    </Table.Cell>
                                    <Table.Cell>
                                        {#if webhook.related_order_id}
                                            <Button
                                                variant="link"
                                                size="sm"
                                                class="p-0 h-auto"
                                                onclick={() => router.visit(`/orders/${webhook.related_order_id}`)}
                                            >
                                                #{webhook.related_order_id}
                                            </Button>
                                        {:else}
                                            <span class="text-muted-foreground">-</span>
                                        {/if}
                                    </Table.Cell>
                                    <Table.Cell>
                                        {#if webhook.retry_count > 0}
                                            <Badge variant="secondary">{webhook.retry_count}</Badge>
                                        {:else}
                                            <span class="text-muted-foreground">0</span>
                                        {/if}
                                    </Table.Cell>
                                    <Table.Cell>
                                        {webhook.processed_at ? formatDateTime(webhook.processed_at) : '-'}
                                    </Table.Cell>
                                    <Table.Cell>
                                        {formatDateTime(webhook.created_at)}
                                    </Table.Cell>
                                    <Table.Cell class="text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onclick={() => viewWebhookDetails(webhook)}
                                            >
                                                <Eye class="h-4 w-4" />
                                            </Button>
                                            {#if webhook.status === 'failed'}
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    onclick={() => handleReprocess(webhook.id)}
                                                    disabled={$reprocessMutation.isPending}
                                                >
                                                    <RotateCcw class="h-4 w-4" />
                                                </Button>
                                            {/if}
                                        </div>
                                    </Table.Cell>
                                </Table.Row>
                            {/each}
                        </Table.Body>
                    </Table.Root>

                    <!-- Pagination -->
                    {#if webhooksData.meta.last_page > 1}
                        <div class="flex items-center justify-between px-6 py-4 border-t">
                            <div class="text-sm text-muted-foreground">
                                Showing {webhooksData.meta.from} to {webhooksData.meta.to} of {webhooksData.meta.total} webhooks
                            </div>
                            <div class="flex items-center space-x-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onclick={() => changePage(webhooksData.meta.current_page - 1)}
                                    disabled={webhooksData.meta.current_page === 1}
                                >
                                    <ChevronLeft class="h-4 w-4" />
                                    Previous
                                </Button>
                                
                                <div class="flex items-center space-x-1">
                                    {#each Array.from({length: Math.min(5, webhooksData.meta.last_page)}, (_, i) => {
                                        const start = Math.max(1, webhooksData.meta.current_page - 2);
                                        return start + i;
                                    }).filter(page => page <= webhooksData.meta.last_page) as pageNum}
                                        <Button
                                            variant={pageNum === webhooksData.meta.current_page ? "default" : "outline"}
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
                                    onclick={() => changePage(webhooksData.meta.current_page + 1)}
                                    disabled={webhooksData.meta.current_page === webhooksData.meta.last_page}
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

    <!-- Webhook Detail Dialog -->
    <Dialog.Root bind:open={showDetailDialog}>
        <Dialog.Content class="max-w-4xl max-h-[80vh] overflow-y-auto">
            <Dialog.Header>
                <Dialog.Title>Webhook Details</Dialog.Title>
                <Dialog.Description>
                    Detailed information about the webhook event
                </Dialog.Description>
            </Dialog.Header>
            
            {#if selectedWebhook}
                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Source</Label>
                                <p class="text-sm font-medium capitalize">{selectedWebhook.source}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Event Type</Label>
                                <p class="text-sm font-medium">{selectedWebhook.event_type}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Status</Label>
                                <Badge class={statusColors[selectedWebhook.status]}>
                                    {selectedWebhook.status}
                                </Badge>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Retry Count</Label>
                                <p class="text-sm font-medium">{selectedWebhook.retry_count}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Received At</Label>
                                <p class="text-sm font-medium">{formatDateTime(selectedWebhook.created_at)}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Processed At</Label>
                                <p class="text-sm font-medium">
                                    {selectedWebhook.processed_at ? formatDateTime(selectedWebhook.processed_at) : 'Not processed'}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Error Message (if any) -->
                    {#if selectedWebhook.error_message}
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Error Message</Label>
                            <div class="mt-2 p-3 bg-red-50 border border-red-200 rounded-md">
                                <p class="text-sm text-red-800">{selectedWebhook.error_message}</p>
                            </div>
                        </div>
                    {/if}

                    <!-- Payload -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label class="text-sm font-medium text-muted-foreground">Payload</Label>
                            <Button
                                variant="outline"
                                size="sm"
                                onclick={copyPayload}
                                disabled={copiedPayload}
                            >
                                {#if copiedPayload}
                                    <CheckCircle class="mr-2 h-4 w-4" />
                                    Copied
                                {:else}
                                    <Copy class="mr-2 h-4 w-4" />
                                    Copy
                                {/if}
                            </Button>
                        </div>
                        <Textarea
                            readonly
                            value={JSON.stringify(selectedWebhook.payload, null, 2)}
                            rows="15"
                            class="font-mono text-sm"
                        />
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-2">
                        {#if selectedWebhook.status === 'failed'}
                            <Button
                                onclick={() => {
                                    handleReprocess(selectedWebhook.id);
                                    showDetailDialog = false;
                                }}
                                disabled={$reprocessMutation.isPending}
                            >
                                <RotateCcw class="mr-2 h-4 w-4" />
                                Reprocess
                            </Button>
                        {/if}
                        <Button variant="outline" onclick={() => showDetailDialog = false}>
                            Close
                        </Button>
                    </div>
                </div>
            {/if}
        </Dialog.Content>
    </Dialog.Root>
</AppLayout>