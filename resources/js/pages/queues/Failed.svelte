<script>
import { Head } from "@inertiajs/svelte";

let { failed_jobs = [] } = $props();
</script>

<Head>
    <title>Failed Jobs</title>
</Head>

<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Failed Jobs</h1>
    
    {#if failed_jobs.length === 0}
        <p class="text-gray-500">No failed jobs found.</p>
    {:else}
        <div class="grid gap-4">
            {#each failed_jobs as job}
                <div class="border rounded-lg p-4">
                    <h3 class="font-semibold">{job.name || 'Unknown Job'}</h3>
                    <p class="text-sm text-gray-600">Failed at: {job.failed_at}</p>
                    {#if job.exception}
                        <pre class="mt-2 text-xs bg-red-50 p-2 rounded overflow-x-auto">{job.exception}</pre>
                    {/if}
                </div>
            {/each}
        </div>
    {/if}
</div>