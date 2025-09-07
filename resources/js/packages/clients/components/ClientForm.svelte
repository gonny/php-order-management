<script lang="ts">
    import type { Client } from '@/types';
    import * as Card from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Button } from '@/components/ui/button';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Save, ArrowLeft } from 'lucide-svelte';
    import { router } from '@inertiajs/svelte';
    import { useForm } from '@inertiajs/svelte';

    interface Props {
        client?: Client;
        mode: 'create' | 'edit';
        onCancel?: () => void;
    }

    let { client, mode, onCancel }: Props = $props();

    // Form data
    let form = useForm({
        external_id: client?.external_id || '',
        email: client?.email || '',
        phone: client?.phone || '',
        first_name: client?.first_name || '',
        last_name: client?.last_name || '',
        company: client?.company || '',
        vat_id: client?.vat_id || '',
        is_active: client?.is_active ?? true,
        meta: client?.meta || {},
    });

    function handleSubmit() {
        if (mode === 'create') {
            form.post('/api/v1/clients', {
                onSuccess: () => {
                    router.visit('/clients');
                }
            });
        } else {
            form.put(`/api/v1/clients/${client?.id}`, {
                onSuccess: () => {
                    router.visit('/clients');
                }
            });
        }
    }

    function handleCancel() {
        if (onCancel) {
            onCancel();
        } else {
            router.visit('/clients');
        }
    }

    let isSubmitting = $derived(form.processing);
</script>

<form onsubmit={handleSubmit} class="space-y-6">
    <!-- Basic Information -->
    <Card.Root>
        <Card.Header>
            <Card.Title>Basic Information</Card.Title>
            <Card.Description>Client personal details</Card.Description>
        </Card.Header>
        <Card.Content class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <Label for="first_name">First Name *</Label>
                    <Input 
                        id="first_name" 
                        bind:value={form.first_name}
                        placeholder="Enter first name"
                        required
                        disabled={isSubmitting}
                    />
                    {#if form.errors.first_name}
                        <p class="text-sm text-red-600">{form.errors.first_name}</p>
                    {/if}
                </div>
                <div class="space-y-2">
                    <Label for="last_name">Last Name *</Label>
                    <Input 
                        id="last_name" 
                        bind:value={form.last_name}
                        placeholder="Enter last name"
                        required
                        disabled={isSubmitting}
                    />
                    {#if form.errors.last_name}
                        <p class="text-sm text-red-600">{form.errors.last_name}</p>
                    {/if}
                </div>
            </div>

            <div class="space-y-2">
                <Label for="email">Email Address *</Label>
                <Input 
                    id="email" 
                    type="email"
                    bind:value={form.email}
                    placeholder="Enter email address"
                    required
                    disabled={isSubmitting}
                />
                {#if form.errors.email}
                    <p class="text-sm text-red-600">{form.errors.email}</p>
                {/if}
            </div>

            <div class="space-y-2">
                <Label for="phone">Phone Number</Label>
                <Input 
                    id="phone" 
                    bind:value={form.phone}
                    placeholder="Enter phone number"
                    disabled={isSubmitting}
                />
                {#if form.errors.phone}
                    <p class="text-sm text-red-600">{form.errors.phone}</p>
                {/if}
            </div>
        </Card.Content>
    </Card.Root>

    <!-- Company Information -->
    <Card.Root>
        <Card.Header>
            <Card.Title>Company Information</Card.Title>
            <Card.Description>Optional business details</Card.Description>
        </Card.Header>
        <Card.Content class="space-y-4">
            <div class="space-y-2">
                <Label for="company">Company Name</Label>
                <Input 
                    id="company" 
                    bind:value={form.company}
                    placeholder="Enter company name"
                    disabled={isSubmitting}
                />
                {#if form.errors.company}
                    <p class="text-sm text-red-600">{form.errors.company}</p>
                {/if}
            </div>

            <div class="space-y-2">
                <Label for="vat_id">VAT ID</Label>
                <Input 
                    id="vat_id" 
                    bind:value={form.vat_id}
                    placeholder="Enter VAT identification number"
                    disabled={isSubmitting}
                />
                {#if form.errors.vat_id}
                    <p class="text-sm text-red-600">{form.errors.vat_id}</p>
                {/if}
            </div>
        </Card.Content>
    </Card.Root>

    <!-- External Information -->
    <Card.Root>
        <Card.Header>
            <Card.Title>External Information</Card.Title>
            <Card.Description>Integration and external system details</Card.Description>
        </Card.Header>
        <Card.Content class="space-y-4">
            <div class="space-y-2">
                <Label for="external_id">External ID</Label>
                <Input 
                    id="external_id" 
                    bind:value={form.external_id}
                    placeholder="Enter external system ID"
                    disabled={isSubmitting}
                />
                {#if form.errors.external_id}
                    <p class="text-sm text-red-600">{form.errors.external_id}</p>
                {/if}
            </div>

            <div class="flex items-center space-x-2">
                <Checkbox 
                    id="is_active" 
                    bind:checked={form.is_active}
                    disabled={isSubmitting}
                />
                <Label for="is_active" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                    Active Client
                </Label>
            </div>
            {#if form.errors.is_active}
                <p class="text-sm text-red-600">{form.errors.is_active}</p>
            {/if}
        </Card.Content>
    </Card.Root>

    <!-- Form Actions -->
    <div class="flex justify-end space-x-4">
        <Button 
            type="button" 
            variant="outline" 
            onclick={handleCancel}
            disabled={isSubmitting}
        >
            <ArrowLeft class="h-4 w-4 mr-2" />
            Cancel
        </Button>
        <Button 
            type="submit" 
            disabled={isSubmitting}
        >
            <Save class="h-4 w-4 mr-2" />
            {mode === 'create' ? 'Create Client' : 'Save Changes'}
        </Button>
    </div>
</form>