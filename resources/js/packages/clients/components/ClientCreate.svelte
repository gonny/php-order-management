<script lang="ts">
    import ClientForm from '@/packages/clients/components/ClientForm.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import type { BreadcrumbItem } from '@/types';

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Clients', href: '/clients' },
        { title: 'Create Client', href: '/clients/create' },
    ];
</script>

<svelte:head>
    <title>Create Client - Order Management</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <div class="space-y-6 p-4 md:p-8 pt-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold">Create New Client</h1>
            <p class="text-muted-foreground">Add a new client to the system</p>
        </div>

        <ClientForm mode="create" />
    </div>
</AppLayout>