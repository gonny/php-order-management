<script lang="ts">
    import { useClient } from '../hooks/use-clients';
    import ClientForm from '@/packages/clients/components/ClientForm.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import type { BreadcrumbItem } from '@/types';
    import { Button } from '@/components/ui/button';
    import { Skeleton } from '@/components/ui/skeleton';
    import { Trash2 } from 'lucide-svelte';
    import { router } from '@inertiajs/svelte';

    interface Props {
        clientId: string;
    }

    let { clientId }: Props = $props();

    // Get client data
    let clientQuery = $derived(useClient(clientId));

    let breadcrumbs = $derived($clientQuery.data ? [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Clients', href: '/clients' },
        { title: $clientQuery.data.first_name + ' ' + $clientQuery.data.last_name, href: `/clients/${clientId}` },
        { title: 'Edit', href: `/clients/${clientId}/edit` },
    ] as BreadcrumbItem[] : [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Clients', href: '/clients' },
        { title: 'Edit Client', href: `/clients/${clientId}/edit` },
    ] as BreadcrumbItem[]);

    function handleDelete() {
        if (!$clientQuery.data) return;
        
        if (confirm('Are you sure you want to delete this client? This action cannot be undone.')) {
            router.delete(`/spa/v1/clients/${clientId}`, {
                onSuccess: () => {
                    router.visit('/clients');
                }
            });
        }
    }
</script>

<svelte:head>
    <title>Edit Client - Order Management</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <div class="space-y-6 p-4 md:p-8 pt-6">
        {#if $clientQuery.isLoading}
            <!-- Loading state -->
            <div class="space-y-6">
                <div>
                    <Skeleton class="h-8 w-[200px] mb-2" />
                    <Skeleton class="h-4 w-[300px]" />
                </div>
                <div class="space-y-4">
                    <Skeleton class="h-[400px] w-full" />
                </div>
            </div>
        {:else if $clientQuery.error}
            <!-- Error state -->
            <div class="text-center py-12">
                <p class="text-red-600 dark:text-red-400 mb-4">
                    {$clientQuery.error?.message || 'Failed to load client'}
                </p>
                <Button 
                    variant="outline" 
                    onclick={() => $clientQuery.refetch()}
                >
                    Try Again
                </Button>
            </div>
        {:else if $clientQuery.data}
            {@const client = $clientQuery.data}
            
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Edit Client: {client.first_name} {client.last_name}</h1>
                    <p class="text-muted-foreground">Update client information</p>
                </div>
                <Button 
                    variant="destructive"
                    onclick={handleDelete}
                >
                    <Trash2 class="h-4 w-4 mr-2" />
                    Delete Client
                </Button>
            </div>

            <ClientForm mode="edit" {client} />
        {/if}
    </div>
</AppLayout>