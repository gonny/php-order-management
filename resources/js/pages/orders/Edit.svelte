<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import type { BreadcrumbItem } from '@/types';
    import * as Card from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Button } from '@/components/ui/button';
    import { Textarea } from '@/components/ui/textarea';
    import * as Select from '@/components/ui/select';
    import { ArrowLeft, Save, Plus, X } from 'lucide-svelte';
    import { router } from '@inertiajs/svelte';
    import { useForm } from '@inertiajs/svelte';

    // Props from Inertia
    let { order } = $props<{ order: any }>();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Orders', href: '/orders' },
        { title: `Order #${order.order_number}`, href: `/orders/${order.id}` },
        { title: 'Edit', href: `/orders/${order.id}/edit` },
    ];

    // Initialize form with order data
    let form = useForm({
        client_id: order.client_id || '',
        status: order.status || 'new',
        total_amount: order.total_amount || 0,
        currency: order.currency || 'CZK',
        notes: order.notes || '',
        items: order.items || [{ 
            id: null,
            name: '', 
            description: '', 
            quantity: 1, 
            unit_price: 0, 
            total_price: 0 
        }],
        shipping_address: order.shipping_address || {
            street: '',
            city: '',
            postal_code: '',
            country: 'CZ',
            first_name: '',
            last_name: '',
            company: '',
            phone: '',
            email: ''
        },
        billing_address: order.billing_address || {
            street: '',
            city: '',
            postal_code: '',
            country: 'CZ',
            first_name: '',
            last_name: '',
            company: '',
            phone: '',
            email: ''
        }
    });

    function addItem() {
        form.items = [...form.items, { 
            id: null,
            name: '', 
            description: '', 
            quantity: 1, 
            unit_price: 0, 
            total_price: 0 
        }];
    }

    function removeItem(index: number) {
        form.items = form.items.filter((_: any, i: number) => i !== index);
        calculateTotal();
    }

    function calculateItemTotal(index: number) {
        const item = form.items[index];
        item.total_price = item.quantity * item.unit_price;
        form.items = [...form.items];
        calculateTotal();
    }

    function calculateTotal() {
        form.total_amount = form.items.reduce((sum: number, item: any) => sum + item.total_price, 0);
    }

    function copyBillingToShipping() {
        form.shipping_address = { ...form.billing_address };
    }

    function handleSubmit() {
        form.patch(`/spa/v1/orders/${order.id}`, {
            onSuccess: () => {
                router.visit(`/orders/${order.id}`);
            }
        });
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
                    onclick={() => router.visit(`/orders/${order.id}`)}
                >
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Back to Order
                </Button>
                <div>
                    <h1 class="text-2xl font-bold">Edit Order #{order.order_number}</h1>
                    <p class="text-muted-foreground">Update order information</p>
                </div>
            </div>
        </div>

        <form onsubmit={handleSubmit} class="space-y-6">
            <!-- Basic Information -->
            <Card.Root>
                <Card.Header>
                    <Card.Title>Basic Information</Card.Title>
                    <Card.Description>Order details and status</Card.Description>
                </Card.Header>
                <Card.Content class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="client_id">Client ID</Label>
                            <Input 
                                id="client_id" 
                                bind:value={form.client_id}
                                placeholder="Enter client ID"
                                required
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="status">Status</Label>
                            <Select.Root type="single" bind:value={form.status}>
                                <Select.Trigger>
                                    <Select.Value placeholder="Select status" />
                                </Select.Trigger>
                                <Select.Content>
                                    <Select.Item value="new">New</Select.Item>
                                    <Select.Item value="confirmed">Confirmed</Select.Item>
                                    <Select.Item value="paid">Paid</Select.Item>
                                    <Select.Item value="fulfilled">Fulfilled</Select.Item>
                                    <Select.Item value="completed">Completed</Select.Item>
                                    <Select.Item value="cancelled">Cancelled</Select.Item>
                                    <Select.Item value="on_hold">On Hold</Select.Item>
                                </Select.Content>
                            </Select.Root>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="notes">Notes</Label>
                        <Textarea 
                            id="notes" 
                            bind:value={form.notes}
                            placeholder="Order notes..."
                            rows={3}
                        />
                    </div>
                </Card.Content>
            </Card.Root>

            <!-- Order Items -->
            <Card.Root>
                <Card.Header>
                    <div class="flex items-center justify-between">
                        <div>
                            <Card.Title>Order Items</Card.Title>
                            <Card.Description>Products or services in this order</Card.Description>
                        </div>
                        <Button type="button" variant="outline" onclick={addItem}>
                            <Plus class="h-4 w-4 mr-2" />
                            Add Item
                        </Button>
                    </div>
                </Card.Header>
                <Card.Content class="space-y-4">
                    {#each form.items as item, index (index)}
                        <div class="grid grid-cols-12 gap-4 items-end">
                            <div class="col-span-3">
                                <Label>Item Name</Label>
                                <Input 
                                    bind:value={item.name}
                                    placeholder="Product name"
                                    required
                                />
                            </div>
                            <div class="col-span-3">
                                <Label>Description</Label>
                                <Input 
                                    bind:value={item.description}
                                    placeholder="Description"
                                />
                            </div>
                            <div class="col-span-2">
                                <Label>Quantity</Label>
                                <Input 
                                    type="number" 
                                    bind:value={item.quantity}
                                    min="1"
                                    onchange={() => calculateItemTotal(index)}
                                />
                            </div>
                            <div class="col-span-2">
                                <Label>Unit Price</Label>
                                <Input 
                                    type="number" 
                                    bind:value={item.unit_price}
                                    step="0.01"
                                    min="0"
                                    onchange={() => calculateItemTotal(index)}
                                />
                            </div>
                            <div class="col-span-1">
                                <Label>Total</Label>
                                <Input 
                                    value={item.total_price.toFixed(2)}
                                    readonly
                                />
                            </div>
                            <div class="col-span-1">
                                {#if form.items.length > 1}
                                    <Button 
                                        type="button" 
                                        variant="ghost" 
                                        size="sm"
                                        onclick={() => removeItem(index)}
                                    >
                                        <X class="h-4 w-4" />
                                    </Button>
                                {/if}
                            </div>
                        </div>
                    {/each}
                    <div class="flex justify-end">
                        <div class="text-lg font-semibold">
                            Total: {form.total_amount.toFixed(2)} {form.currency}
                        </div>
                    </div>
                </Card.Content>
            </Card.Root>

            <!-- Billing Address -->
            <Card.Root>
                <Card.Header>
                    <Card.Title>Billing Address</Card.Title>
                    <Card.Description>Customer billing information</Card.Description>
                </Card.Header>
                <Card.Content class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>First Name</Label>
                            <Input bind:value={form.billing_address.first_name} required />
                        </div>
                        <div class="space-y-2">
                            <Label>Last Name</Label>
                            <Input bind:value={form.billing_address.last_name} required />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label>Company</Label>
                        <Input bind:value={form.billing_address.company} />
                    </div>
                    <div class="space-y-2">
                        <Label>Street Address</Label>
                        <Input bind:value={form.billing_address.street} required />
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label>City</Label>
                            <Input bind:value={form.billing_address.city} required />
                        </div>
                        <div class="space-y-2">
                            <Label>Postal Code</Label>
                            <Input bind:value={form.billing_address.postal_code} required />
                        </div>
                        <div class="space-y-2">
                            <Label>Country</Label>
                            <Input bind:value={form.billing_address.country} required />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Phone</Label>
                            <Input bind:value={form.billing_address.phone} />
                        </div>
                        <div class="space-y-2">
                            <Label>Email</Label>
                            <Input type="email" bind:value={form.billing_address.email} />
                        </div>
                    </div>
                </Card.Content>
            </Card.Root>

            <!-- Shipping Address -->
            <Card.Root>
                <Card.Header>
                    <div class="flex items-center justify-between">
                        <div>
                            <Card.Title>Shipping Address</Card.Title>
                            <Card.Description>Delivery address information</Card.Description>
                        </div>
                        <Button type="button" variant="outline" onclick={copyBillingToShipping}>
                            Copy from billing
                        </Button>
                    </div>
                </Card.Header>
                <Card.Content class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>First Name</Label>
                            <Input bind:value={form.shipping_address.first_name} required />
                        </div>
                        <div class="space-y-2">
                            <Label>Last Name</Label>
                            <Input bind:value={form.shipping_address.last_name} required />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label>Company</Label>
                        <Input bind:value={form.shipping_address.company} />
                    </div>
                    <div class="space-y-2">
                        <Label>Street Address</Label>
                        <Input bind:value={form.shipping_address.street} required />
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label>City</Label>
                            <Input bind:value={form.shipping_address.city} required />
                        </div>
                        <div class="space-y-2">
                            <Label>Postal Code</Label>
                            <Input bind:value={form.shipping_address.postal_code} required />
                        </div>
                        <div class="space-y-2">
                            <Label>Country</Label>
                            <Input bind:value={form.shipping_address.country} required />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Phone</Label>
                            <Input bind:value={form.shipping_address.phone} />
                        </div>
                        <div class="space-y-2">
                            <Label>Email</Label>
                            <Input type="email" bind:value={form.shipping_address.email} />
                        </div>
                    </div>
                </Card.Content>
            </Card.Root>

            <!-- Actions -->
            <div class="flex justify-end gap-4">
                <Button type="button" variant="outline" onclick={() => router.visit(`/orders/${order.id}`)}>
                    Cancel
                </Button>
                <Button type="submit" disabled={form.processing}>
                    <Save class="h-4 w-4 mr-2" />
                    {form.processing ? 'Updating...' : 'Update Order'}
                </Button>
            </div>
        </form>
    </div>
</AppLayout>