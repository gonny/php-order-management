<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import type { BreadcrumbItem } from '@/types';
    import * as Card from '@/components/ui/card';
    import * as Table from '@/components/ui/table';
    import * as Tabs from '@/components/ui/tabs';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Separator } from '@/components/ui/separator';
    import { 
        ArrowLeft, 
        Edit, 
        Mail,
        Phone,
        MapPin,
        Building,
        FileText,
        Package,
        Calendar
    } from 'lucide-svelte';
    import { router } from '@inertiajs/svelte';

    // Props from Inertia
    let { client } = $props<{ client: any }>();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Clients', href: '/clients' },
        { title: client.first_name + ' ' + client.last_name, href: `/clients/${client.id}` },
    ];

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

<AppLayout {breadcrumbs}>
    <div class="space-y-6">
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

                            <div class="flex items-center gap-3">
                                <Calendar class="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-medium">Client Since</p>
                                    <p class="text-sm text-muted-foreground">{formatDate(client.created_at)}</p>
                                </div>
                            </div>
                        </div>
                    </Card.Content>
                </Card.Root>

                <!-- Status & Statistics -->
                <Card.Root>
                    <Card.Header>
                        <Card.Title>Statistics</Card.Title>
                    </Card.Header>
                    <Card.Content class="space-y-4">
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm font-medium">Status</span>
                                <Badge variant={client.is_active ? "default" : "secondary"}>
                                    {client.is_active ? 'Active' : 'Inactive'}
                                </Badge>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium">Total Orders</span>
                                <span class="text-sm font-semibold">{client.orders?.length || 0}</span>
                            </div>
                            {#if client.orders && client.orders.length > 0}
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium">Total Value</span>
                                    <span class="text-sm font-semibold">
                                        {formatCurrency(client.orders.reduce((sum, order) => sum + (order.total_amount || 0), 0))}
                                    </span>
                                </div>
                            {/if}
                        </div>
                    </Card.Content>
                </Card.Root>

                <!-- Addresses -->
                {#if client.addresses && client.addresses.length > 0}
                    <Card.Root>
                        <Card.Header>
                            <Card.Title>Addresses</Card.Title>
                        </Card.Header>
                        <Card.Content class="space-y-4">
                            {#each client.addresses as address}
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <MapPin class="h-4 w-4 text-muted-foreground" />
                                        <span class="text-sm font-medium">{address.type || 'Address'}</span>
                                    </div>
                                    <div class="pl-6 text-sm text-muted-foreground">
                                        <p>{address.street}</p>
                                        <p>{address.city}, {address.postal_code}</p>
                                        <p>{address.country}</p>
                                    </div>
                                </div>
                                {#if address !== client.addresses[client.addresses.length - 1]}
                                    <Separator />
                                {/if}
                            {/each}
                        </Card.Content>
                    </Card.Root>
                {/if}
            </div>

            <!-- Orders -->
            <div class="lg:col-span-2">
                <Card.Root>
                    <Card.Header>
                        <div class="flex items-center justify-between">
                            <div>
                                <Card.Title>Order History</Card.Title>
                                <Card.Description>Recent orders from this client</Card.Description>
                            </div>
                            {#if client.orders && client.orders.length > 0}
                                <Button 
                                    variant="outline" 
                                    size="sm"
                                    onclick={() => router.visit('/orders?client_id=' + client.id)}
                                >
                                    View All Orders
                                </Button>
                            {/if}
                        </div>
                    </Card.Header>
                    <Card.Content>
                        {#if client.orders && client.orders.length > 0}
                            <Table.Root>
                                <Table.Header>
                                    <Table.Row>
                                        <Table.Head>Order #</Table.Head>
                                        <Table.Head>Date</Table.Head>
                                        <Table.Head>Items</Table.Head>
                                        <Table.Head>Status</Table.Head>
                                        <Table.Head class="text-right">Amount</Table.Head>
                                        <Table.Head></Table.Head>
                                    </Table.Row>
                                </Table.Header>
                                <Table.Body>
                                    {#each client.orders as order}
                                        <Table.Row>
                                            <Table.Cell class="font-medium">
                                                #{order.order_number || order.id}
                                            </Table.Cell>
                                            <Table.Cell>{formatDate(order.created_at)}</Table.Cell>
                                            <Table.Cell>
                                                <div class="flex items-center gap-1">
                                                    <Package class="h-3 w-3" />
                                                    {order.items?.length || 0} items
                                                </div>
                                            </Table.Cell>
                                            <Table.Cell>
                                                <Badge class={getOrderStatusColor(order.status)}>
                                                    {order.status}
                                                </Badge>
                                            </Table.Cell>
                                            <Table.Cell class="text-right">
                                                {formatCurrency(order.total_amount || 0)}
                                            </Table.Cell>
                                            <Table.Cell>
                                                <Button 
                                                    variant="ghost" 
                                                    size="sm"
                                                    onclick={() => router.visit(`/orders/${order.id}`)}
                                                >
                                                    View
                                                </Button>
                                            </Table.Cell>
                                        </Table.Row>
                                    {/each}
                                </Table.Body>
                            </Table.Root>
                        {:else}
                            <div class="text-center py-8">
                                <Package class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                                <h3 class="text-lg font-medium">No orders yet</h3>
                                <p class="text-muted-foreground mb-4">This client hasn't placed any orders.</p>
                                <Button onclick={() => router.visit('/orders/create?client_id=' + client.id)}>
                                    Create First Order
                                </Button>
                            </div>
                        {/if}
                    </Card.Content>
                </Card.Root>
            </div>
        </div>
    </div>
</AppLayout>