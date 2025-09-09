<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import type { BreadcrumbItem } from '@/types';
    import { OrderForm } from '@/packages/orders';

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Orders', href: '/orders' },
        { title: 'Create Order', href: '/orders/create' },
    ];
</script>

<AppLayout {breadcrumbs}>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Create New Order</h1>
                <p class="text-muted-foreground">Add a new order to the system</p>
            </div>
        </div>

        <!-- Order Form -->
        <OrderForm mode="create" />
    </div>
</AppLayout>