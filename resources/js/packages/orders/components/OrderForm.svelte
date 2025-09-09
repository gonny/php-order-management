<script lang="ts">
    import type { Order, OrderItemForm } from '../types/order';
    import * as Card from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Button } from '@/components/ui/button';
    import { Textarea } from '@/components/ui/textarea';
    import * as Select from '@/components/ui/select';
    import { Save, ArrowLeft, Plus, X } from 'lucide-svelte';
    import { router } from '@inertiajs/svelte';
    import { useForm } from '@inertiajs/svelte';

    interface Props {
        order?: Order;
        mode: 'create' | 'edit';
        onCancel?: () => void;
        errors?: Record<string, string>;
    }

    let { order, mode, onCancel, errors }: Props = $props();

    // Create Inertia form with proper initial data - FIXED: Use only Inertia form
    const form = useForm({
        client_id: order?.client_id || '',
        status: order?.status || 'new',
        total_amount: order?.total_amount || 0,
        currency: order?.currency || 'CZK',
        notes: order?.notes || '',
        items: (order?.items || [{ 
            name: '', 
            description: '', 
            quantity: 1, 
            unit_price: 0, 
            total_price: 0 
        }]) as OrderItemForm[],
        shipping_address: order?.shipping_address || {
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
        billing_address: order?.billing_address || {
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
        $form.items = [...$form.items, { 
            name: '', 
            description: '', 
            quantity: 1, 
            unit_price: 0, 
            total_price: 0 
        }];
    }

    function removeItem(index: number) {
        $form.items = $form.items.filter((_, i) => i !== index);
        calculateTotal();
    }

    function calculateItemTotal(index: number) {
        const item = $form.items[index];
        if (item) {
            item.total_price = item.quantity * item.unit_price;
            $form.items = [...$form.items];
            calculateTotal();
        }
    }

    function calculateTotal() {
        $form.total_amount = $form.items.reduce((sum, item) => sum + item.total_price, 0);
    }

    function copyBillingToShipping() {
        $form.shipping_address = { ...$form.billing_address };
    }

    function handleSubmit(event: Event) {
        event.preventDefault();

        if (mode === 'create') {
            $form.post('/spa/v1/orders', {
                onSuccess: () => {
                    router.visit('/orders');
                },
            });
        } else if (order?.id) {
            $form.put(`/spa/v1/orders/${order.id}`, {
                onSuccess: () => {
                    router.visit('/orders');
                },
            });
        }
    }

    function handleCancel() {
        if (onCancel) {
            onCancel();
        } else {
            router.visit('/orders');
        }
    }

    let isSubmitting = $derived($form.processing);
    console.log(errors ?? "no errors");
</script>

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
                    <Label for="client_id">Client ID *</Label>
                    <Input 
                        id="client_id" 
                        bind:value={$form.client_id}
                        placeholder="Enter client ID"
                        required
                        disabled={isSubmitting}
                    />
                    {#if $form.errors?.client_id}
                        <p class="text-sm text-red-600">{$form.errors.client_id}</p>
                    {/if}
                </div>
                <div class="space-y-2">
                    <Label for="status">Status</Label>
                    <Select.Root bind:value={$form.status} disabled={isSubmitting}>
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
                        </Select.Content>
                    </Select.Root>
                    {#if $form.errors?.status}
                        <p class="text-sm text-red-600">{$form.errors.status}</p>
                    {/if}
                </div>
            </div>
            <div class="space-y-2">
                <Label for="notes">Notes</Label>
                <Textarea 
                    id="notes" 
                    bind:value={$form.notes}
                    placeholder="Order notes..."
                    rows={3}
                    disabled={isSubmitting}
                />
                {#if $form.errors?.notes}
                    <p class="text-sm text-red-600">{$form.errors.notes}</p>
                {/if}
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
                <Button type="button" variant="outline" onclick={addItem} disabled={isSubmitting}>
                    <Plus class="h-4 w-4 mr-2" />
                    Add Item
                </Button>
            </div>
        </Card.Header>
        <Card.Content class="space-y-4">
            {#each $form.items as item, index (index)}
                <div class="grid grid-cols-12 gap-4 items-end">
                    <div class="col-span-3">
                        <Label>Item Name *</Label>
                        <Input 
                            bind:value={item.name}
                            placeholder="Product name"
                            required
                            disabled={isSubmitting}
                        />
                    </div>
                    <div class="col-span-3">
                        <Label>Description</Label>
                        <Input 
                            bind:value={item.description}
                            placeholder="Description"
                            disabled={isSubmitting}
                        />
                    </div>
                    <div class="col-span-2">
                        <Label>Quantity *</Label>
                        <Input 
                            type="number" 
                            bind:value={item.quantity}
                            min="1"
                            disabled={isSubmitting}
                            onchange={() => calculateItemTotal(index)}
                        />
                    </div>
                    <div class="col-span-2">
                        <Label>Unit Price *</Label>
                        <Input 
                            type="number" 
                            bind:value={item.unit_price}
                            step="0.01"
                            min="0"
                            disabled={isSubmitting}
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
                        {#if $form.items.length > 1}
                            <Button 
                                type="button" 
                                variant="ghost" 
                                size="sm"
                                onclick={() => removeItem(index)}
                                disabled={isSubmitting}
                            >
                                <X class="h-4 w-4" />
                            </Button>
                        {/if}
                    </div>
                </div>
            {/each}
            <div class="flex justify-end">
                <div class="text-lg font-semibold">
                    Total: {$form.total_amount.toFixed(2)} {$form.currency}
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
                    <Label>First Name *</Label>
                    <Input bind:value={$form.billing_address.first_name} required disabled={isSubmitting} />
                    {#if $form.errors?.['billing_address.first_name']}
                        <p class="text-sm text-red-600">{$form.errors['billing_address.first_name']}</p>
                    {/if}
                </div>
                <div class="space-y-2">
                    <Label>Last Name *</Label>
                    <Input bind:value={$form.billing_address.last_name} required disabled={isSubmitting} />
                    {#if $form.errors?.['billing_address.last_name']}
                        <p class="text-sm text-red-600">{$form.errors['billing_address.last_name']}</p>
                    {/if}
                </div>
            </div>
            <div class="space-y-2">
                <Label>Company</Label>
                <Input bind:value={$form.billing_address.company} disabled={isSubmitting} />
            </div>
            <div class="space-y-2">
                <Label>Street Address *</Label>
                <Input bind:value={$form.billing_address.street} required disabled={isSubmitting} />
                {#if $form.errors?.['billing_address.street']}
                    <p class="text-sm text-red-600">{$form.errors['billing_address.street']}</p>
                {/if}
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div class="space-y-2">
                    <Label>City *</Label>
                    <Input bind:value={$form.billing_address.city} required disabled={isSubmitting} />
                    {#if $form.errors?.['billing_address.city']}
                        <p class="text-sm text-red-600">{$form.errors['billing_address.city']}</p>
                    {/if}
                </div>
                <div class="space-y-2">
                    <Label>Postal Code *</Label>
                    <Input bind:value={$form.billing_address.postal_code} required disabled={isSubmitting} />
                    {#if $form.errors?.['billing_address.postal_code']}
                        <p class="text-sm text-red-600">{$form.errors['billing_address.postal_code']}</p>
                    {/if}
                </div>
                <div class="space-y-2">
                    <Label>Country *</Label>
                    <Input bind:value={$form.billing_address.country} required disabled={isSubmitting} />
                    {#if $form.errors?.['billing_address.country']}
                        <p class="text-sm text-red-600">{$form.errors['billing_address.country']}</p>
                    {/if}
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <Label>Phone</Label>
                    <Input bind:value={$form.billing_address.phone} disabled={isSubmitting} />
                </div>
                <div class="space-y-2">
                    <Label>Email</Label>
                    <Input type="email" bind:value={$form.billing_address.email} disabled={isSubmitting} />
                    {#if $form.errors?.['billing_address.email']}
                        <p class="text-sm text-red-600">{$form.errors['billing_address.email']}</p>
                    {/if}
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
                <Button type="button" variant="outline" onclick={copyBillingToShipping} disabled={isSubmitting}>
                    Copy from billing
                </Button>
            </div>
        </Card.Header>
        <Card.Content class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <Label>First Name *</Label>
                    <Input bind:value={$form.shipping_address.first_name} required disabled={isSubmitting} />
                    {#if $form.errors?.['shipping_address.first_name']}
                        <p class="text-sm text-red-600">{$form.errors['shipping_address.first_name']}</p>
                    {/if}
                </div>
                <div class="space-y-2">
                    <Label>Last Name *</Label>
                    <Input bind:value={$form.shipping_address.last_name} required disabled={isSubmitting} />
                    {#if $form.errors?.['shipping_address.last_name']}
                        <p class="text-sm text-red-600">{$form.errors['shipping_address.last_name']}</p>
                    {/if}
                </div>
            </div>
            <div class="space-y-2">
                <Label>Company</Label>
                <Input bind:value={$form.shipping_address.company} disabled={isSubmitting} />
            </div>
            <div class="space-y-2">
                <Label>Street Address *</Label>
                <Input bind:value={$form.shipping_address.street} required disabled={isSubmitting} />
                {#if $form.errors?.['shipping_address.street']}
                    <p class="text-sm text-red-600">{$form.errors['shipping_address.street']}</p>
                {/if}
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div class="space-y-2">
                    <Label>City *</Label>
                    <Input bind:value={$form.shipping_address.city} required disabled={isSubmitting} />
                    {#if $form.errors?.['shipping_address.city']}
                        <p class="text-sm text-red-600">{$form.errors['shipping_address.city']}</p>
                    {/if}
                </div>
                <div class="space-y-2">
                    <Label>Postal Code *</Label>
                    <Input bind:value={$form.shipping_address.postal_code} required disabled={isSubmitting} />
                    {#if $form.errors?.['shipping_address.postal_code']}
                        <p class="text-sm text-red-600">{$form.errors['shipping_address.postal_code']}</p>
                    {/if}
                </div>
                <div class="space-y-2">
                    <Label>Country *</Label>
                    <Input bind:value={$form.shipping_address.country} required disabled={isSubmitting} />
                    {#if $form.errors?.['shipping_address.country']}
                        <p class="text-sm text-red-600">{$form.errors['shipping_address.country']}</p>
                    {/if}
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <Label>Phone</Label>
                    <Input bind:value={$form.shipping_address.phone} disabled={isSubmitting} />
                </div>
                <div class="space-y-2">
                    <Label>Email</Label>
                    <Input type="email" bind:value={$form.shipping_address.email} disabled={isSubmitting} />
                    {#if $form.errors?.['shipping_address.email']}
                        <p class="text-sm text-red-600">{$form.errors['shipping_address.email']}</p>
                    {/if}
                </div>
            </div>
        </Card.Content>
    </Card.Root>

    <!-- Form Actions -->
    <div class="flex justify-end space-x-4">
        <Button type="button" variant="outline" onclick={handleCancel} disabled={isSubmitting}>
            <ArrowLeft class="h-4 w-4 mr-2" />
            Cancel
        </Button>
        <Button type="submit" disabled={isSubmitting}>
            <Save class="h-4 w-4 mr-2" />
            {isSubmitting ? 'Saving...' : (mode === 'create' ? 'Create Order' : 'Save Changes')}
        </Button>
    </div>
</form>