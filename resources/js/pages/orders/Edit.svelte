<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import type { BreadcrumbItem } from '@/types';
    import { OrderForm } from '@/packages/orders';
    import type { Order } from '@/packages/orders/types/order';

    // Props from Inertia
    let { order } = $props<{ order: Order }>();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Orders', href: '/orders' },
        { title: `Order #${order?.number || 'Unknown'}`, href: `/orders/${order?.id}` },
        { title: 'Edit', href: `/orders/${order?.id}/edit` },
    ];
</script>

<AppLayout {breadcrumbs}>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Edit Order #{order?.number || 'Unknown'}</h1>
                <p class="text-muted-foreground">Update order information</p>
            </div>
        </div>

        <!-- Order Form -->
        <OrderForm mode="edit" {order} />
    </div>
</AppLayout>