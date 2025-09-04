<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import type { BreadcrumbItem } from '@/types';
    import * as Card from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Button } from '@/components/ui/button';
    import { Textarea } from '@/components/ui/textarea';
    import { Checkbox } from '@/components/ui/checkbox';
    import { ArrowLeft, Save } from 'lucide-svelte';
    import { router } from '@inertiajs/svelte';
    import { useForm } from '@inertiajs/svelte';

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Clients', href: '/clients' },
        { title: 'Create Client', href: '/clients/create' },
    ];

    // Form data
    let form = useForm({
        external_id: '',
        email: '',
        phone: '',
        first_name: '',
        last_name: '',
        company: '',
        vat_id: '',
        is_active: true,
        meta: {},
    });

    function handleSubmit() {
        form.post('/api/v1/clients', {
            onSuccess: () => {
                router.visit('/clients');
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
                    onclick={() => router.visit('/clients')}
                >
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Back to Clients
                </Button>
                <div>
                    <h1 class="text-2xl font-bold">Create New Client</h1>
                    <p class="text-muted-foreground">Add a new client to the system</p>
                </div>
            </div>
        </div>

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
                        />
                        {#if form.errors.vat_id}
                            <p class="text-sm text-red-600">{form.errors.vat_id}</p>
                        {/if}
                    </div>
                </Card.Content>
            </Card.Root>

            <!-- System Information -->
            <Card.Root>
                <Card.Header>
                    <Card.Title>System Information</Card.Title>
                    <Card.Description>External system integration and status</Card.Description>
                </Card.Header>
                <Card.Content class="space-y-4">
                    <div class="space-y-2">
                        <Label for="external_id">External ID</Label>
                        <Input 
                            id="external_id" 
                            bind:value={form.external_id}
                            placeholder="External system identifier"
                        />
                        <p class="text-sm text-muted-foreground">
                            Optional identifier from external systems (e.g., CRM, ERP)
                        </p>
                        {#if form.errors.external_id}
                            <p class="text-sm text-red-600">{form.errors.external_id}</p>
                        {/if}
                    </div>

                    <div class="flex items-center space-x-2">
                        <Checkbox 
                            id="is_active" 
                            bind:checked={form.is_active}
                        />
                        <Label for="is_active">Active Client</Label>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Inactive clients cannot place new orders but retain access to existing data
                    </p>
                </Card.Content>
            </Card.Root>

            <!-- Actions -->
            <div class="flex justify-end gap-4">
                <Button type="button" variant="outline" onclick={() => router.visit('/clients')}>
                    Cancel
                </Button>
                <Button type="submit" disabled={form.processing}>
                    <Save class="h-4 w-4 mr-2" />
                    {form.processing ? 'Creating...' : 'Create Client'}
                </Button>
            </div>
        </form>
    </div>
</AppLayout>