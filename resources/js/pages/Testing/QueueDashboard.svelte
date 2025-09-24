<script lang="ts">
  import { router } from '@inertiajs/svelte';
  import AppLayout from '@/layouts/AppLayout.svelte';
  import { Button } from '@/components/ui/button';
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
  import { Badge } from '@/components/ui/badge';
  import { AlertTriangle, CheckCircle, Clock, Trash2, RefreshCw } from 'lucide-svelte';
  import type { BreadcrumbItem } from '@/types';

  interface QueueStats {
    pending: number;
    failed: number;
    completed_today: number;
    average_processing_time: number;
  }

  interface QueueJob {
    id: string;
    type: 'pending' | 'failed';
    job_class: string;
    queue: string;
    created_at?: string;
    failed_at?: string;
    exception?: string;
  }

  interface RecentJobs {
    failed: QueueJob[];
    pending: QueueJob[];
  }

  interface ApiClient {
    id: number;
    name: string;
    key_id: string;
    is_active: boolean;
  }

  interface Props {
    queueStats: QueueStats;
    recentJobs: RecentJobs;
    apiClients: ApiClient[];
  }

  let { queueStats, recentJobs, apiClients }: Props = $props();

  function clearFailedJobs() {
    if (confirm('Are you sure you want to clear all failed jobs?')) {
      router.delete('/testing/queue/failed/clear', {
        onSuccess: () => {
          router.reload();
        }
      });
    }
  }

  function retryAllFailedJobs() {
    if (confirm('Are you sure you want to retry all failed jobs?')) {
      router.post('/testing/queue/failed/retry-all', {}, {
        onSuccess: () => {
          router.reload();
        }
      });
    }
  }

  function formatDate(dateString: string) {
    return new Date(dateString).toLocaleString();
  }

  function getBadgeVariant(type: string) {
    switch (type) {
      case 'failed':
        return 'destructive';
      case 'pending':
        return 'secondary';
      default:
        return 'default';
    }
  }
        const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Orders', href: '/orders' },
    ];
</script>

  <svelte:head>
    <title>"API Testing Interface</title>
</svelte:head>
<AppLayout {breadcrumbs} >
  <div class="container mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Queue Testing Dashboard</h1>
        <p class="text-muted-foreground">Monitor and test your Laravel queue system</p>
      </div>
      <div class="flex gap-2">
        <Button variant="outline" href="/swagger">
          API Docs
        </Button>
        <Button variant="outline" href="/testing/api-testing">
          API Testing
        </Button>
        <Button onclick={() => router.reload()}>
          <RefreshCw class="mr-2 h-4 w-4" />
          Refresh
        </Button>
      </div>
    </div>

    <!-- Queue Statistics -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Pending Jobs</CardTitle>
          <Clock class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{queueStats.pending}</div>
          <p class="text-xs text-muted-foreground">Jobs waiting to be processed</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Failed Jobs</CardTitle>
          <AlertTriangle class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-destructive">{queueStats.failed}</div>
          <p class="text-xs text-muted-foreground">Jobs that failed to process</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Completed Today</CardTitle>
          <CheckCircle class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{queueStats.completed_today}</div>
          <p class="text-xs text-muted-foreground">Successfully processed jobs</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Avg. Processing Time</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{queueStats.average_processing_time.toFixed(2)}s</div>
          <p class="text-xs text-muted-foreground">Average time per job</p>
        </CardContent>
      </Card>
    </div>

    <!-- Queue Actions -->
    {#if queueStats.failed > 0}
      <Card>
        <CardHeader>
          <CardTitle>Queue Management Actions</CardTitle>
          <CardDescription>Manage failed jobs and queue operations</CardDescription>
        </CardHeader>
        <CardContent class="flex gap-2">
          <Button variant="outline" onclick={retryAllFailedJobs}>
            <RefreshCw class="mr-2 h-4 w-4" />
            Retry All Failed Jobs
          </Button>
          <Button variant="destructive" onclick={clearFailedJobs}>
            <Trash2 class="mr-2 h-4 w-4" />
            Clear All Failed Jobs
          </Button>
        </CardContent>
      </Card>
    {/if}

    <div class="grid gap-6 lg:grid-cols-2">
      <!-- Recent Failed Jobs -->
      <Card>
        <CardHeader>
          <CardTitle>Recent Failed Jobs</CardTitle>
          <CardDescription>Last 10 jobs that failed to process</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          {#if recentJobs.failed.length === 0}
            <p class="text-sm text-muted-foreground">No failed jobs found</p>
          {:else}
            {#each recentJobs.failed as job (job.id)}
              <div class="flex items-start justify-between border-b pb-3 last:border-b-0">
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <Badge variant={getBadgeVariant(job.type)}>{job.type}</Badge>
                    <span class="text-sm font-medium">{job.job_class}</span>
                  </div>
                  <p class="text-xs text-muted-foreground">Queue: {job.queue}</p>
                  {#if job.failed_at}
                    <p class="text-xs text-muted-foreground">Failed: {formatDate(job.failed_at)}</p>
                  {/if}
                  {#if job.exception}
                    <p class="text-xs text-red-600 truncate max-w-xs">{job.exception}</p>
                  {/if}
                </div>
              </div>
            {/each}
          {/if}
        </CardContent>
      </Card>

      <!-- Recent Pending Jobs -->
      <Card>
        <CardHeader>
          <CardTitle>Recent Pending Jobs</CardTitle>
          <CardDescription>Last 10 jobs waiting to be processed</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          {#if recentJobs.pending.length === 0}
            <p class="text-sm text-muted-foreground">No pending jobs found</p>
          {:else}
            {#each recentJobs.pending as job (job.id)}
              <div class="flex items-start justify-between border-b pb-3 last:border-b-0">
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <Badge variant={getBadgeVariant(job.type)}>{job.type}</Badge>
                    <span class="text-sm font-medium">{job.job_class}</span>
                  </div>
                  <p class="text-xs text-muted-foreground">Queue: {job.queue}</p>
                  {#if job.created_at}
                    <p class="text-xs text-muted-foreground">Created: {formatDate(job.created_at)}</p>
                  {/if}
                </div>
              </div>
            {/each}
          {/if}
        </CardContent>
      </Card>
    </div>

    <!-- API Clients Overview -->
    <Card>
      <CardHeader>
        <CardTitle>API Clients for Testing</CardTitle>
        <CardDescription>Available API clients for HMAC authentication testing</CardDescription>
      </CardHeader>
      <CardContent>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          {#each apiClients as client (client.id)}
            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <p class="font-medium">{client.name}</p>
                <p class="text-xs text-muted-foreground">Key ID: {client.key_id}</p>
              </div>
              <Badge variant={client.is_active ? 'default' : 'secondary'}>
                {client.is_active ? 'Active' : 'Inactive'}
              </Badge>
            </div>
          {/each}
        </div>
      </CardContent>
    </Card>
  </div>
</AppLayout>