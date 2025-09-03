<script lang="ts">
    import { useDashboardMetrics } from '@/hooks/use-dashboard';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { type BreadcrumbItem } from '@/types';
    import * as Card from '@/components/ui/card';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Skeleton } from '@/components/ui/skeleton';
    import { RefreshCw, TrendingUp, TrendingDown, AlertTriangle, Clock } from 'lucide-svelte';

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
        },
    ];

    // Get dashboard metrics using TanStack Query
    const dashboardQuery = useDashboardMetrics();

    // Status badge color mapping
    const statusColors: Record<string, string> = {
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
</script>

<svelte:head>
    <title>Dashboard - Order Management</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <div class="flex-1 space-y-4 p-4 md:p-8 pt-6">
        <div class="flex items-center justify-between space-y-2">
            <h2 class="text-3xl font-bold tracking-tight">Dashboard</h2>
            <div class="flex items-center space-x-2">
                <Button
                    variant="outline"
                    size="sm"
                    onclick={() => $dashboardQuery.refetch()}
                    disabled={$dashboardQuery.isFetching}
                >
                    <RefreshCw class="mr-2 h-4 w-4 {$dashboardQuery.isFetching ? 'animate-spin' : ''}" />
                    Refresh
                </Button>
            </div>
        </div>

        {#if $dashboardQuery.isLoading}
            <!-- Loading state -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                {#each Array(8) as _}
                    <Card.Root>
                        <Card.Header class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <Skeleton class="h-4 w-[100px]" />
                            <Skeleton class="h-4 w-4" />
                        </Card.Header>
                        <Card.Content>
                            <Skeleton class="h-8 w-[60px] mb-2" />
                            <Skeleton class="h-3 w-[120px]" />
                        </Card.Content>
                    </Card.Root>
                {/each}
            </div>
        {:else if $dashboardQuery.error}
            <!-- Error state -->
            <Card.Root class="border-red-200 dark:border-red-800">
                <Card.Header>
                    <Card.Title class="text-red-800 dark:text-red-200 flex items-center">
                        <AlertTriangle class="mr-2 h-5 w-5" />
                        Failed to load dashboard
                    </Card.Title>
                </Card.Header>
                <Card.Content>
                    <p class="text-red-600 dark:text-red-400">
                        {$dashboardQuery.error?.message || 'An error occurred while loading the dashboard'}
                    </p>
                    <Button 
                        class="mt-4" 
                        variant="outline" 
                        onclick={() => $dashboardQuery.refetch()}
                    >
                        Try Again
                    </Button>
                </Card.Content>
            </Card.Root>
        {:else if $dashboardQuery.data}
            {@const metrics = $dashboardQuery.data}
            
            <!-- Order Status Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                {#each Object.entries(metrics.order_counts) as [status, count]}
                    <Card.Root>
                        <Card.Header class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <Card.Title class="text-sm font-medium capitalize">
                                {status.replace('_', ' ')}
                            </Card.Title>
                            <Badge class={statusColors[status] || 'bg-gray-100 text-gray-800'}>
                                {count}
                            </Badge>
                        </Card.Header>
                        <Card.Content>
                            <div class="text-2xl font-bold">{count}</div>
                            <p class="text-xs text-muted-foreground">
                                {count === 1 ? 'order' : 'orders'}
                            </p>
                        </Card.Content>
                    </Card.Root>
                {/each}
            </div>

            <!-- Summary Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card.Root>
                    <Card.Header class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <Card.Title class="text-sm font-medium">Total Revenue</Card.Title>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </Card.Header>
                    <Card.Content>
                        <div class="text-2xl font-bold">{formatCurrency(metrics.total_revenue)}</div>
                        <p class="text-xs text-muted-foreground">All time</p>
                    </Card.Content>
                </Card.Root>

                <Card.Root>
                    <Card.Header class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <Card.Title class="text-sm font-medium">Failed Jobs</Card.Title>
                        <AlertTriangle class="h-4 w-4 text-muted-foreground" />
                    </Card.Header>
                    <Card.Content>
                        <div class="text-2xl font-bold">{metrics.failed_jobs_count}</div>
                        <p class="text-xs text-muted-foreground">Require attention</p>
                    </Card.Content>
                </Card.Root>

                <Card.Root>
                    <Card.Header class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <Card.Title class="text-sm font-medium">API Response Time</Card.Title>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </Card.Header>
                    <Card.Content>
                        <div class="text-2xl font-bold">{metrics.api_response_time_p95}ms</div>
                        <p class="text-xs text-muted-foreground">95th percentile</p>
                    </Card.Content>
                </Card.Root>

                <Card.Root>
                    <Card.Header class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <Card.Title class="text-sm font-medium">Queue Size</Card.Title>
                        <TrendingDown class="h-4 w-4 text-muted-foreground" />
                    </Card.Header>
                    <Card.Content>
                        <div class="text-2xl font-bold">
                            {Object.values(metrics.queue_sizes).reduce((a, b) => a + b, 0)}
                        </div>
                        <p class="text-xs text-muted-foreground">Pending jobs</p>
                    </Card.Content>
                </Card.Root>
            </div>

            <!-- Recent Orders and Activities -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Recent Orders -->
                <Card.Root>
                    <Card.Header>
                        <Card.Title>Recent Orders</Card.Title>
                        <Card.Description>Latest order activity</Card.Description>
                    </Card.Header>
                    <Card.Content>
                        <div class="space-y-4">
                            {#each metrics.recent_orders.slice(0, 5) as order}
                                <div class="flex items-center justify-between">
                                    <div class="flex flex-col">
                                        <p class="text-sm font-medium">#{order.number}</p>
                                        <p class="text-xs text-muted-foreground">
                                            {formatDateTime(order.created_at)}
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Badge class={statusColors[order.status]}>
                                            {order.status}
                                        </Badge>
                                        <span class="text-sm font-medium">
                                            {formatCurrency(order.total)}
                                        </span>
                                    </div>
                                </div>
                            {/each}
                            {#if metrics.recent_orders.length === 0}
                                <p class="text-sm text-muted-foreground text-center py-4">
                                    No recent orders
                                </p>
                            {/if}
                        </div>
                    </Card.Content>
                </Card.Root>

                <!-- Recent Activities -->
                <Card.Root>
                    <Card.Header>
                        <Card.Title>Recent Activities</Card.Title>
                        <Card.Description>System activity log</Card.Description>
                    </Card.Header>
                    <Card.Content>
                        <div class="space-y-4">
                            {#each metrics.recent_activities.slice(0, 5) as activity}
                                <div class="flex items-center justify-between">
                                    <div class="flex flex-col">
                                        <p class="text-sm font-medium">{activity.action}</p>
                                        <p class="text-xs text-muted-foreground">
                                            {formatDateTime(activity.created_at)}
                                        </p>
                                    </div>
                                </div>
                            {/each}
                            {#if metrics.recent_activities.length === 0}
                                <p class="text-sm text-muted-foreground text-center py-4">
                                    No recent activities
                                </p>
                            {/if}
                        </div>
                    </Card.Content>
                </Card.Root>
            </div>

            <!-- Queue Status -->
            {#if Object.keys(metrics.queue_sizes).length > 0}
                <Card.Root>
                    <Card.Header>
                        <Card.Title>Queue Status</Card.Title>
                        <Card.Description>Background job queue sizes</Card.Description>
                    </Card.Header>
                    <Card.Content>
                        <div class="grid gap-4 md:grid-cols-3">
                            {#each Object.entries(metrics.queue_sizes) as [queueName, size]}
                                <div class="flex items-center justify-between p-3 border rounded-lg">
                                    <div class="flex flex-col">
                                        <p class="text-sm font-medium capitalize">{queueName}</p>
                                        <p class="text-xs text-muted-foreground">jobs pending</p>
                                    </div>
                                    <Badge variant={size > 10 ? 'destructive' : size > 0 ? 'secondary' : 'outline'}>
                                        {size}
                                    </Badge>
                                </div>
                            {/each}
                        </div>
                    </Card.Content>
                </Card.Root>
            {/if}
        {/if}
    </div>
</AppLayout>
