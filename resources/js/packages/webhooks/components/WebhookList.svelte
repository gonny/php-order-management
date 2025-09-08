<script lang="ts">
    import { useWebhooks, useReprocessWebhook } from '../hooks/use-webhooks';
    import type { WebhookFilters, Webhook } from '@/types';
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

    interface Props {
        filters: WebhookFilters;
        onFiltersChange: (filters: WebhookFilters) => void;
    }

    let { filters, onFiltersChange }: Props = $props();

    // Get webhooks using TanStack Query
    let webhooksQuery = $derived(useWebhooks(filters));
    
    // Mutations
    const reprocessMutation = useReprocessWebhook();

    // Filter options
    const sourceOptions = [
        { value: '', label: 'All Sources' },
        { value: 'dpd', label: 'DPD' },
        { value: 'shoptet', label: 'Shoptet' },
        { value: 'custom', label: 'Custom' },
    ];

    const statusOptions = [
        { value: '', label: 'All Statuses' },
        { value: 'pending', label: 'Pending' },
        { value: 'processing', label: 'Processing' },
        { value: 'completed', label: 'Completed' },
        { value: 'failed', label: 'Failed' },
        { value: 'cancelled', label: 'Cancelled' },
    ];

    // Dialog state
    let selectedWebhook: Webhook | null = $state(null);
    let dialogOpen = $state(false);
    let copiedPayload = $state(false);

    function updateFilters(updates: Partial<WebhookFilters>) {
        onFiltersChange({ ...filters, ...updates });
    }

    function changePage(newPage: number) {
        updateFilters({ page: newPage });
    }

    function handleSort(column: string) {
        if (filters.sort === column) {
            updateFilters({ direction: filters.direction === 'asc' ? 'desc' : 'asc' });
        } else {
            updateFilters({ sort: column, direction: 'asc' });
        }
    }

    function handleFilterChange() {
        updateFilters({ page: 1 }); // Reset to first page
    }

    function clearFilters() {
        onFiltersChange({
            page: 1,
            per_page: 20,
            sort: 'created_at',
            direction: 'desc',
        });
    }

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

    function getStatusIcon(status: string) {
        switch (status) {
            case 'completed':
                return CheckCircle;
            case 'failed':
            case 'cancelled':
                return XCircle;
            case 'processing':
                return Clock;
            case 'pending':
            default:
                return AlertTriangle;
        }
    }

    function getStatusVariant(status: string) {
        switch (status) {
            case 'completed':
                return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
            case 'failed':
            case 'cancelled':
                return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
            case 'processing':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
            case 'pending':
            default:
                return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        }
    }

    function showWebhookDetails(webhook: any) {
        selectedWebhook = webhook;
        dialogOpen = true;
    }

    function reprocessWebhook(webhookId: string) {
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

<div class="space-y-4">
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
                    <Select.Root type="single" bind:value={filters.source}>
                        <Select.Trigger>
                            <span>Select source...</span>
                        </Select.Trigger>
                        <Select.Content>
                            {#each sourceOptions as option (option.value)}
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
                    <Select.Root type="single" bind:value={filters.status}>
                        <Select.Trigger>
                            <span>Select status...</span>
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

    <!-- Results -->
    <Card.Root>
        <Card.Header>
            <Card.Title class="text-lg">
                {#if $webhooksQuery.isLoading}
                    Loading webhooks...
                {:else if $webhooksQuery.data}
                    {@const webhooksData = $webhooksQuery.data}
                    Webhooks ({webhooksData.total})
                {:else}
                    Webhooks
                {/if}
            </Card.Title>
        </Card.Header>
        <Card.Content class="p-0">
            {#if $webhooksQuery.isLoading}
                <!-- Loading skeleton -->
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
                            <Table.Head>Status</Table.Head>
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
                                onclick={() => handleSort('source')}
                            >
                                Source
                                {#if filters.sort === 'source'}
                                    {filters.direction === 'asc' ? '↑' : '↓'}
                                {/if}
                            </Table.Head>
                            <Table.Head>Order</Table.Head>
                            <Table.Head 
                                class="cursor-pointer"
                                onclick={() => handleSort('created_at')}
                            >
                                Received
                                {#if filters.sort === 'created_at'}
                                    {filters.direction === 'asc' ? '↑' : '↓'}
                                {/if}
                            </Table.Head>
                            <Table.Head>Attempts</Table.Head>
                            <Table.Head class="text-right">Actions</Table.Head>
                        </Table.Row>
                    </Table.Header>
                    <Table.Body>
                        {#each webhooksData.data as webhook (webhook.id)}
                            <Table.Row>
                                <Table.Cell>
                                    {@const StatusIcon = getStatusIcon(webhook.status)}
                                    <div class="flex items-center space-x-2">
                                        <StatusIcon class="h-4 w-4" />
                                        <Badge class={getStatusVariant(webhook.status)}>
                                            {webhook.status}
                                        </Badge>
                                    </div>
                                </Table.Cell>
                                <Table.Cell>
                                    <code class="text-sm bg-muted px-1 py-0.5 rounded">
                                        {webhook.event_type}
                                    </code>
                                </Table.Cell>
                                <Table.Cell>
                                    <Badge variant="outline">{webhook.source}</Badge>
                                </Table.Cell>
                                <Table.Cell>
                                    {#if webhook.related_order_id}
                                        <Button
                                            variant="link"
                                            onclick={() => router.visit(`/orders/${webhook.related_order_id}`)}
                                            class="p-0 h-auto"
                                        >
                                            #{webhook.related_order_id}
                                        </Button>
                                    {:else}
                                        <span class="text-muted-foreground">-</span>
                                    {/if}
                                </Table.Cell>
                                <Table.Cell>
                                    <span class="text-sm text-muted-foreground">
                                        {formatDateTime(webhook.created_at)}
                                    </span>
                                </Table.Cell>
                                <Table.Cell>
                                    <span class="text-sm">
                                        {webhook.attempts || 1}
                                        {#if webhook.max_attempts}
                                            / {webhook.max_attempts}
                                        {/if}
                                    </span>
                                </Table.Cell>
                                <Table.Cell class="text-right">
                                    <div class="flex justify-end space-x-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onclick={() => showWebhookDetails(webhook)}
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                        {#if webhook.status === 'failed'}
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onclick={() => reprocessWebhook(webhook.id)}
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
                {#if webhooksData.last_page > 1}
                    <div class="flex items-center justify-between px-6 py-4 border-t">
                        <div class="text-sm text-muted-foreground">
                            Showing {((webhooksData.current_page - 1) * webhooksData.per_page) + 1} to {Math.min(webhooksData.current_page * webhooksData.per_page, webhooksData.total)} of {webhooksData.total} entries
                        </div>
                        <div class="flex items-center space-x-2">
                            <Button
                                variant="outline"
                                size="sm"
                                onclick={() => changePage(webhooksData.current_page - 1)}
                                disabled={webhooksData.current_page <= 1}
                            >
                                <ChevronLeft class="h-4 w-4" />
                                Previous
                            </Button>
                            <div class="flex items-center space-x-1">
                                {#each Array.from({ length: Math.min(5, webhooksData.last_page) }, (_, i) => {
                                    const start = Math.max(1, webhooksData.current_page - 2);
                                    return start + i;
                                }).filter(page => page <= webhooksData.last_page) as page (page)}
                                    <Button
                                        variant={page === webhooksData.current_page ? "default" : "outline"}
                                        size="sm"
                                        onclick={() => changePage(page)}
                                    >
                                        {page}
                                    </Button>
                                {/each}
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                onclick={() => changePage(webhooksData.current_page + 1)}
                                disabled={webhooksData.current_page >= webhooksData.last_page}
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

<!-- Webhook Details Dialog -->
<Dialog.Root bind:open={dialogOpen}>
    <Dialog.Content class="max-w-4xl max-h-[80vh] overflow-hidden flex flex-col">
        <Dialog.Header>
            <Dialog.Title>Webhook Details</Dialog.Title>
            <Dialog.Description>
                {#if selectedWebhook}
                    Event: {selectedWebhook.event_type} from {selectedWebhook.source}
                {/if}
            </Dialog.Description>
        </Dialog.Header>
        
        {#if selectedWebhook}
            <div class="flex-1 overflow-auto space-y-4">
                <!-- Basic Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label class="text-sm font-medium">Status</Label>
                        {#if selectedWebhook}
                            {@const StatusIcon = getStatusIcon(selectedWebhook.status)}
                            <p class="text-sm text-muted-foreground mt-1">
                                <Badge class={`${getStatusVariant(selectedWebhook.status)} inline-flex items-center gap-1`}>
                                    <StatusIcon class="h-3 w-3" />
                                    {selectedWebhook.status}
                                </Badge>
                            </p>
                        {/if}
                    </div>
                    <div>
                        <Label class="text-sm font-medium">Received At</Label>
                        <p class="text-sm text-muted-foreground mt-1">
                            {formatDateTime(selectedWebhook.created_at)}
                        </p>
                    </div>
                    <div>
                        <Label class="text-sm font-medium">Attempts</Label>
                        <p class="text-sm text-muted-foreground mt-1">
                            {selectedWebhook.attempts || 1}
                            {#if selectedWebhook.max_attempts}
                                / {selectedWebhook.max_attempts}
                            {/if}
                        </p>
                    </div>
                    {#if selectedWebhook.related_order_id}
                        <div>
                            <Label class="text-sm font-medium">Related Order</Label>
                            <p class="text-sm text-muted-foreground mt-1">
                                <Button
                                    variant="link"
                                    onclick={() => router.visit(`/orders/${selectedWebhook.related_order_id}`)}
                                    class="p-0 h-auto"
                                >
                                    #{selectedWebhook.related_order_id}
                                </Button>
                            </p>
                        </div>
                    {/if}
                </div>

                <!-- Error Message -->
                {#if selectedWebhook.error_message}
                    <div>
                        <Label class="text-sm font-medium">Error Message</Label>
                        <div class="mt-1 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                            <p class="text-sm text-red-800 dark:text-red-200">
                                {selectedWebhook.error_message}
                            </p>
                        </div>
                    </div>
                {/if}

                <!-- Payload -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <Label class="text-sm font-medium">Payload</Label>
                        <Button
                            variant="outline"
                            size="sm"
                            onclick={copyPayload}
                            disabled={!selectedWebhook.payload}
                        >
                            <Copy class="h-4 w-4 mr-2" />
                            {copiedPayload ? 'Copied!' : 'Copy'}
                        </Button>
                    </div>
                    <Textarea
                        value={selectedWebhook.payload ? JSON.stringify(selectedWebhook.payload, null, 2) : 'No payload'}
                        readonly
                        class="font-mono text-xs h-64 resize-none"
                    />
                </div>
            </div>
        {/if}
    </Dialog.Content>
</Dialog.Root>