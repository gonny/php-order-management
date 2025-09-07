<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import type { BreadcrumbItem } from '@/types';
    import * as Card from '@/components/ui/card';
    import { AlertTriangle } from 'lucide-svelte';

    let { failed_jobs = [] } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: route('dashboard') },
        { title: 'Queues', href: route('queues.index') },
        { title: 'Failed Jobs', href: '' }
    ];
</script>

<AppLayout {breadcrumbs}>
    <div class="container mx-auto space-y-6">
        <div class="flex items-center gap-2">
            <AlertTriangle class="h-6 w-6 text-red-500" />
            <h1 class="text-2xl font-bold">Failed Jobs</h1>
        </div>
        
        {#if failed_jobs.length === 0}
            <Card.Root>
                <Card.Content class="p-6">
                    <p class="text-muted-foreground text-center">No failed jobs found.</p>
                </Card.Content>
            </Card.Root>
        {:else}
            <div class="grid gap-4">
                {#each failed_jobs as job (job.id)}
                    <Card.Root>
                        <Card.Header>
                            <Card.Title class="text-lg">{job.name || 'Unknown Job'}</Card.Title>
                            <Card.Description>Failed at: {job.failed_at}</Card.Description>
                        </Card.Header>
                        {#if job.exception}
                            <Card.Content>
                                <pre class="text-xs bg-destructive/10 p-3 rounded-md overflow-x-auto">{job.exception}</pre>
                            </Card.Content>
                        {/if}
                    </Card.Root>
                {/each}
            </div>
        {/if}
    </div>
</AppLayout>