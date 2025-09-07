<script lang="ts">
    import { useClient } from '../hooks/use-clients';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import type { BreadcrumbItem } from '@/types';
    import * as Card from '@/components/ui/card';
    import * as Table from '@/components/ui/table';
    import * as Tabs from '@/components/ui/tabs';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Skeleton } from '@/components/ui/skeleton';
    import { 
        ArrowLeft, 
        Edit, 
        Mail,
        Phone,
        Building,
        FileText,
        Package,
        Calendar,
        User
    } from 'lucide-svelte';
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
    ] as BreadcrumbItem[] : [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Clients', href: '/clients' },
        { title: 'Client Details', href: `/clients/${clientId}` },
    ] as BreadcrumbItem[]);

    function formatDate(dateString: string) {
        return new Date(dateString).toLocaleDateString();
    }

    function formatCurrency(amount: number, currency = 'CZK') {
        return new Intl.NumberFormat('cs-CZ', {
            style: 'currency',
            currency: currency
        }).format(amount);
    }

    function getOrderStatusColor(status: string) {
        const colors = {
            new: 'bg-blue-100 text-blue-800',
            confirmed: 'bg-yellow-100 text-yellow-800',
            paid: 'bg-green-100 text-green-800',
            fulfilled: 'bg-purple-100 text-purple-800',
            completed: 'bg-green-100 text-green-800',
            cancelled: 'bg-red-100 text-red-800',
            on_hold: 'bg-orange-100 text-orange-800',
            failed: 'bg-red-100 text-red-800',
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    }
</script>

<svelte:head>
    <title>Client Details - Order Management</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <div class="space-y-6 p-4 md:p-8 pt-6">
        {#if $clientQuery.isLoading}
            <!-- Loading state -->
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Skeleton class="h-10 w-32" />
                        <div>
                            <Skeleton class="h-8 w-[200px] mb-2" />
                            <Skeleton class="h-4 w-[300px]" />
                        </div>
                    </div>
                    <Skeleton class="h-10 w-32" />
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-1">
                        <Skeleton class="h-[400px] w-full" />
                    </div>
                    <div class="lg:col-span-2">
                        <Skeleton class="h-[400px] w-full" />
                    </div>
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
                <div class="flex items-center gap-4">
                    <Button 
                        variant="ghost" 
                        size="sm" 
                        onclick={() => router.visit('/clients')}
                    >
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Back to Clients
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold">
                            {client.first_name} {client.last_name}
                        </h1>
                        <p class="text-muted-foreground">Client details and order history</p>
                    </div>
                </div>
                <Button onclick={() => router.visit(`/clients/${client.id}/edit`)}>
                    <Edit class="h-4 w-4 mr-2" />
                    Edit Client
                </Button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Client Information -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Basic Info -->
                    <Card.Root>
                        <Card.Header>
                            <Card.Title>Client Information</Card.Title>
                        </Card.Header>
                        <Card.Content class="space-y-4">
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <Mail class="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <p class="text-sm font-medium">Email</p>
                                        <p class="text-sm text-muted-foreground">{client.email}</p>
                                    </div>
                                </div>

                                {#if client.phone}
                                    <div class="flex items-center gap-3">
                                        <Phone class="h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <p class="text-sm font-medium">Phone</p>
                                            <p class="text-sm text-muted-foreground">{client.phone}</p>
                                        </div>
                                    </div>
                                {/if}

                                {#if client.company}
                                    <div class="flex items-center gap-3">
                                        <Building class="h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <p class="text-sm font-medium">Company</p>
                                            <p class="text-sm text-muted-foreground">{client.company}</p>
                                        </div>
                                    </div>
                                {/if}

                                {#if client.vat_id}
                                    <div class="flex items-center gap-3">
                                        <FileText class="h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <p class="text-sm font-medium">VAT ID</p>
                                            <p class="text-sm text-muted-foreground">{client.vat_id}</p>
                                        </div>
                                    </div>
                                {/if}

                                {#if client.external_id}
                                    <div class="flex items-center gap-3">
                                        <User class="h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <p class="text-sm font-medium">External ID</p>
                                            <p class="text-sm text-muted-foreground font-mono">{client.external_id}</p>
                                        </div>
                                    </div>
                                {/if}

                                <div class="flex items-center gap-3">
                                    <Calendar class="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <p class="text-sm font-medium">Member Since</p>
                                        <p class="text-sm text-muted-foreground">{formatDate(client.created_at)}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="w-4 h-4 flex items-center justify-center">
                                        <div class="w-2 h-2 rounded-full {client.is_active ? 'bg-green-500' : 'bg-red-500'}"></div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium">Status</p>
                                        <p class="text-sm text-muted-foreground">
                                            {client.is_active ? 'Active' : 'Inactive'}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </Card.Content>
                    </Card.Root>

                    <!-- Order Statistics -->
                    {#if client.orders_count !== undefined}
                        <Card.Root>
                            <Card.Header>
                                <Card.Title>Order Statistics</Card.Title>
                            </Card.Header>
                            <Card.Content>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Total Orders</span>
                                        <span class="font-medium">{client.orders_count || 0}</span>
                                    </div>
                                    {#if client.total_spent !== undefined}
                                        <div class="flex justify-between">
                                            <span class="text-sm text-muted-foreground">Total Spent</span>
                                            <span class="font-medium">{formatCurrency(client.total_spent)}</span>
                                        </div>
                                    {/if}
                                    {#if client.last_order_date}
                                        <div class="flex justify-between">
                                            <span class="text-sm text-muted-foreground">Last Order</span>
                                            <span class="font-medium">{formatDate(client.last_order_date)}</span>
                                        </div>
                                    {/if}
                                </div>
                            </Card.Content>
                        </Card.Root>
                    {/if}
                </div>

                <!-- Orders and Activity -->
                <div class="lg:col-span-2">
                    <Tabs.Root value="orders" class="w-full">
                        <Tabs.List class="grid w-full grid-cols-2">
                            <Tabs.Trigger value="orders">Orders</Tabs.Trigger>
                            <Tabs.Trigger value="activity">Activity</Tabs.Trigger>
                        </Tabs.List>
                        
                        <Tabs.Content value="orders" class="space-y-4">
                            <Card.Root>
                                <Card.Header>
                                    <Card.Title>Recent Orders</Card.Title>
                                    <Card.Description>Client's order history</Card.Description>
                                </Card.Header>
                                <Card.Content>
                                    {#if client.orders && client.orders.length > 0}
                                        <Table.Root>
                                            <Table.Header>
                                                <Table.Row>
                                                    <Table.Head>Order #</Table.Head>
                                                    <Table.Head>Date</Table.Head>
                                                    <Table.Head>Status</Table.Head>
                                                    <Table.Head>Total</Table.Head>
                                                </Table.Row>
                                            </Table.Header>
                                            <Table.Body>
                                                {#each client.orders as order (order.id)}
                                                    <Table.Row>
                                                        <Table.Cell>
                                                            <Button
                                                                variant="link"
                                                                onclick={() => router.visit(`/orders/${order.id}`)}
                                                                class="p-0 h-auto font-mono"
                                                            >
                                                                #{order.order_number}
                                                            </Button>
                                                        </Table.Cell>
                                                        <Table.Cell>{formatDate(order.created_at)}</Table.Cell>
                                                        <Table.Cell>
                                                            <Badge class={getOrderStatusColor(order.status)}>
                                                                {order.status}
                                                            </Badge>
                                                        </Table.Cell>
                                                        <Table.Cell>{formatCurrency(order.total_amount)}</Table.Cell>
                                                    </Table.Row>
                                                {/each}
                                            </Table.Body>
                                        </Table.Root>
                                    {:else}
                                        <div class="text-center py-8">
                                            <Package class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                            <p class="text-muted-foreground">No orders found</p>
                                        </div>
                                    {/if}
                                </Card.Content>
                            </Card.Root>
                        </Tabs.Content>

                        <Tabs.Content value="activity" class="space-y-4">
                            <Card.Root>
                                <Card.Header>
                                    <Card.Title>Activity Log</Card.Title>
                                    <Card.Description>Recent client activity</Card.Description>
                                </Card.Header>
                                <Card.Content>
                                    <div class="text-center py-8">
                                        <Calendar class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <p class="text-muted-foreground">Activity tracking coming soon</p>
                                    </div>
                                </Card.Content>
                            </Card.Root>
                        </Tabs.Content>
                    </Tabs.Root>
                </div>
            </div>
        {/if}
    </div>
</AppLayout>