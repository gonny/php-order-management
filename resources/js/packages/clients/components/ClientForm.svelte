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

    // Create Inertia form first
    const inertiaForm = useForm({
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

    // Create reactive state using Svelte 5 runes
    let formData = $state({
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

    // Sync reactive state to Inertia form
    $effect(() => {
        if (inertiaForm?.data && formData) {
            Object.assign(inertiaForm.data, formData);
        }
    });

    function handleSubmit(event: Event) {
        event.preventDefault();
        
        if (mode === 'create') {
            inertiaForm.post('/spa/v1/clients', {
                onSuccess: () => {
                    router.visit('/clients');
                }
            });
        } else if (client?.id) {
            inertiaForm.put(`/spa/v1/clients/${client.id}`, {
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

    let isSubmitting = $derived(inertiaForm.processing);
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
                        bind:value={formData.first_name}
                        placeholder="Enter first name"
                        required
                        disabled={isSubmitting}
                    />
                    {#if inertiaForm.errors.first_name}
                        <p class="text-sm text-red-600">{inertiaForm.errors.first_name}</p>
                    {/if}
                </div>
                <div class="space-y-2">
                    <Label for="last_name">Last Name *</Label>
                    <Input 
                        id="last_name" 
                        bind:value={formData.last_name}
                        placeholder="Enter last name"
                        required
                        disabled={isSubmitting}
                    />
                    {#if inertiaForm.errors.last_name}
                        <p class="text-sm text-red-600">{inertiaForm.errors.last_name}</p>
                    {/if}
                </div>
            </div>

            <div class="space-y-2">
                <Label for="email">Email Address *</Label>
                <Input 
                    id="email" 
                    type="email"
                    bind:value={formData.email}
                    placeholder="Enter email address"
                    required
                    disabled={isSubmitting}
                />
                {#if inertiaForm.errors.email}
                    <p class="text-sm text-red-600">{inertiaForm.errors.email}</p>
                {/if}
            </div>

            <div class="space-y-2">
                <Label for="phone">Phone Number</Label>
                <Input 
                    id="phone" 
                    bind:value={formData.phone}
                    placeholder="Enter phone number"
                    disabled={isSubmitting}
                />
                {#if inertiaForm.errors.phone}
                    <p class="text-sm text-red-600">{inertiaForm.errors.phone}</p>
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
                    bind:value={formData.company}
                    placeholder="Enter company name"
                    disabled={isSubmitting}
                />
                {#if inertiaForm.errors.company}
                    <p class="text-sm text-red-600">{inertiaForm.errors.company}</p>
                {/if}
            </div>

            <div class="space-y-2">
                <Label for="vat_id">VAT ID</Label>
                <Input 
                    id="vat_id" 
                    bind:value={formData.vat_id}
                    placeholder="Enter VAT identification number"
                    disabled={isSubmitting}
                />
                {#if inertiaForm.errors.vat_id}
                    <p class="text-sm text-red-600">{inertiaForm.errors.vat_id}</p>
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
                    bind:value={formData.external_id}
                    placeholder="Enter external system ID"
                    disabled={isSubmitting}
                />
                {#if inertiaForm.errors.external_id}
                    <p class="text-sm text-red-600">{inertiaForm.errors.external_id}</p>
                {/if}
            </div>

            <div class="flex items-center space-x-2">
                <Checkbox 
                    id="is_active" 
                    bind:checked={formData.is_active}
                    disabled={isSubmitting}
                />
                <Label for="is_active" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                    Active Client
                </Label>
            </div>
            {#if inertiaForm.errors.is_active}
                <p class="text-sm text-red-600">{inertiaForm.errors.is_active}</p>
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