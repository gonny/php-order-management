<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import type { BreadcrumbItem } from '@/types';
    import * as Card from '@/components/ui/card';
    import * as Table from '@/components/ui/table';
    import * as Tabs from '@/components/ui/tabs';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { RefreshCw, Activity, AlertTriangle, CheckCircle, Clock, XCircle } from 'lucide-svelte';
    import { onMount } from 'svelte';
    import { spaApiClient } from '@/lib/spa-api';

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Queue Management', href: '/queues' },
    ];

    let stats = {
        pending_jobs: 0,
        failed_jobs: 0,
        processed_jobs_today: 0,
        queue_names: [],
        workers_status: { active_workers: 0, last_heartbeat: '' }
    };

    let recentJobs = [];
    let failedJobs = [];
    let isLoading = true;
    let activeTab = 'overview';

    async function loadData() {
        isLoading = true;
        // Update frontend components use spaApiClient for authenticated API calls
        try {
            // Load queue statistics
            const statsResponse = await spaApiClient.get('/api/v1/queues/stats');
            stats = statsResponse.data;

            // Load recent jobs
            const recentResponse = await spaApiClient.get('/api/v1/queues/recent');
            recentJobs = recentResponse.data;

            // Load failed jobs
            const failedResponse = await spaApiClient.get('/api/v1/queues/failed');
            failedJobs = failedResponse.data;
        } catch (error) {
            console.error('Failed to load queue data:', error);
        } finally {
            isLoading = false;
        }
    }

    async function retryJob(jobId) {
        try {
            await spaApiClient.post(`/api/v1/queues/failed/${jobId}/retry`);
            loadData(); // Refresh data
        } catch (error) {
            console.error('Failed to retry job:', error);
        }
    }

    async function deleteJob(jobId) {
        if (confirm('Are you sure you want to delete this failed job?')) {
            try {
                await spaApiClient.delete(`/api/v1/queues/failed/${jobId}`);
                loadData(); // Refresh data
            } catch (error) {
                console.error('Failed to delete job:', error);
            }
        }
    }

    async function clearAllFailedJobs() {
        if (confirm('Are you sure you want to clear all failed jobs? This action cannot be undone.')) {
            try {
                await spaApiClient.delete('/api/v1/queues/failed');
                loadData(); // Refresh data
            } catch (error) {
                console.error('Failed to clear failed jobs:', error);
            }
        }
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleString();
    }

    function getStatusColor(status) {
        const colors = {
            pending: 'bg-yellow-100 text-yellow-800',
            processing: 'bg-blue-100 text-blue-800',
            completed: 'bg-green-100 text-green-800',
            failed: 'bg-red-100 text-red-800',
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    }

    function getStatusIcon(status) {
        switch (status) {
            case 'pending': return Clock;
            case 'processing': return Activity;
            case 'completed': return CheckCircle;
            case 'failed': return XCircle;
            default: return Clock;
        }
    }

    onMount(() => {
        loadData();
        // Auto-refresh every 30 seconds
        const interval = setInterval(loadData, 30000);
        return () => clearInterval(interval);
    });
</script>

<AppLayout {breadcrumbs}>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Queue Management</h1>
                <p class="text-muted-foreground">Monitor and manage Laravel queue jobs</p>
            </div>
            <Button onclick={loadData} disabled={isLoading}>
                <RefreshCw class="h-4 w-4 mr-2 {isLoading ? 'animate-spin' : ''}" />
                Refresh
            </Button>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <Card.Root>
                <Card.Content class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Pending Jobs</p>
                            <p class="text-2xl font-bold">{stats.pending_jobs}</p>
                        </div>
                        <Clock class="h-8 w-8 text-yellow-600" />
                    </div>
                </Card.Content>
            </Card.Root>

            <Card.Root>
                <Card.Content class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Failed Jobs</p>
                            <p class="text-2xl font-bold">{stats.failed_jobs}</p>
                        </div>
                        <AlertTriangle class="h-8 w-8 text-red-600" />
                    </div>
                </Card.Content>
            </Card.Root>

            <Card.Root>
                <Card.Content class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Processed Today</p>
                            <p class="text-2xl font-bold">{stats.processed_jobs_today}</p>
                        </div>
                        <CheckCircle class="h-8 w-8 text-green-600" />
                    </div>
                </Card.Content>
            </Card.Root>

            <Card.Root>
                <Card.Content class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Active Workers</p>
                            <p class="text-2xl font-bold">{stats.workers_status.active_workers}</p>
                        </div>
                        <Activity class="h-8 w-8 text-blue-600" />
                    </div>
                </Card.Content>
            </Card.Root>
        </div>

        <!-- Tabs -->
        <Tabs.Root bind:value={activeTab} class="w-full">
            <Tabs.List class="grid w-full grid-cols-2">
                <Tabs.Trigger value="overview">Recent Jobs</Tabs.Trigger>
                <Tabs.Trigger value="failed">
                    Failed Jobs 
                    {#if stats.failed_jobs > 0}
                        <Badge variant="destructive" class="ml-2">{stats.failed_jobs}</Badge>
                    {/if}
                </Tabs.Trigger>
            </Tabs.List>

            <!-- Recent Jobs Tab -->
            <Tabs.Content value="overview" class="space-y-4">
                <Card.Root>
                    <Card.Header>
                        <Card.Title>Recent Jobs</Card.Title>
                        <Card.Description>Latest jobs in the queue system</Card.Description>
                    </Card.Header>
                    <Card.Content>
                        {#if isLoading}
                            <div class="flex justify-center py-8">
                                <RefreshCw class="h-6 w-6 animate-spin" />
                            </div>
                        {:else if recentJobs.length > 0}
                            <Table.Root>
                                <Table.Header>
                                    <Table.Row>
                                        <Table.Head>Job</Table.Head>
                                        <Table.Head>Queue</Table.Head>
                                        <Table.Head>Status</Table.Head>
                                        <Table.Head>Attempts</Table.Head>
                                        <Table.Head>Created</Table.Head>
                                    </Table.Row>
                                </Table.Header>
                                <Table.Body>
                                    {#each recentJobs as job}
                                        <Table.Row>
                                            <Table.Cell class="font-medium">{job.job_class}</Table.Cell>
                                            <Table.Cell>{job.queue}</Table.Cell>
                                            <Table.Cell>
                                                <div class="flex items-center gap-2">
                                                    <svelte:component this={getStatusIcon(job.status)} class="h-4 w-4" />
                                                    <Badge class={getStatusColor(job.status)}>
                                                        {job.status}
                                                    </Badge>
                                                </div>
                                            </Table.Cell>
                                            <Table.Cell>{job.attempts}</Table.Cell>
                                            <Table.Cell>{formatDate(job.created_at)}</Table.Cell>
                                        </Table.Row>
                                    {/each}
                                </Table.Body>
                            </Table.Root>
                        {:else}
                            <div class="text-center py-8">
                                <Activity class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                                <h3 class="text-lg font-medium">No recent jobs</h3>
                                <p class="text-muted-foreground">Jobs will appear here as they are processed</p>
                            </div>
                        {/if}
                    </Card.Content>
                </Card.Root>
            </Tabs.Content>

            <!-- Failed Jobs Tab -->
            <Tabs.Content value="failed" class="space-y-4">
                <Card.Root>
                    <Card.Header>
                        <div class="flex items-center justify-between">
                            <div>
                                <Card.Title>Failed Jobs</Card.Title>
                                <Card.Description>Jobs that failed to process successfully</Card.Description>
                            </div>
                            {#if failedJobs.length > 0}
                                <Button variant="destructive" onclick={clearAllFailedJobs}>
                                    Clear All Failed
                                </Button>
                            {/if}
                        </div>
                    </Card.Header>
                    <Card.Content>
                        {#if isLoading}
                            <div class="flex justify-center py-8">
                                <RefreshCw class="h-6 w-6 animate-spin" />
                            </div>
                        {:else if failedJobs.length > 0}
                            <Table.Root>
                                <Table.Header>
                                    <Table.Row>
                                        <Table.Head>Job</Table.Head>
                                        <Table.Head>Queue</Table.Head>
                                        <Table.Head>Failed At</Table.Head>
                                        <Table.Head>Exception</Table.Head>
                                        <Table.Head>Actions</Table.Head>
                                    </Table.Row>
                                </Table.Header>
                                <Table.Body>
                                    {#each failedJobs as job}
                                        <Table.Row>
                                            <Table.Cell class="font-medium">
                                                {job.payload?.displayName || 'Unknown Job'}
                                            </Table.Cell>
                                            <Table.Cell>{job.queue}</Table.Cell>
                                            <Table.Cell>{formatDate(job.failed_at)}</Table.Cell>
                                            <Table.Cell class="max-w-xs truncate" title={job.exception}>
                                                {job.exception?.split('\n')[0] || 'No exception details'}
                                            </Table.Cell>
                                            <Table.Cell>
                                                <div class="flex gap-2">
                                                    <Button 
                                                        size="sm" 
                                                        variant="outline"
                                                        onclick={() => retryJob(job.id)}
                                                    >
                                                        Retry
                                                    </Button>
                                                    <Button 
                                                        size="sm" 
                                                        variant="destructive"
                                                        onclick={() => deleteJob(job.id)}
                                                    >
                                                        Delete
                                                    </Button>
                                                </div>
                                            </Table.Cell>
                                        </Table.Row>
                                    {/each}
                                </Table.Body>
                            </Table.Root>
                        {:else}
                            <div class="text-center py-8">
                                <CheckCircle class="h-12 w-12 mx-auto text-green-600 mb-4" />
                                <h3 class="text-lg font-medium">No failed jobs</h3>
                                <p class="text-muted-foreground">All jobs are processing successfully</p>
                            </div>
                        {/if}
                    </Card.Content>
                </Card.Root>
            </Tabs.Content>
        </Tabs.Root>
    </div>
</AppLayout>