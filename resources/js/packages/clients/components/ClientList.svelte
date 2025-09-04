<script lang="ts">
    import { useClients } from '../hooks/use-clients';
    import type { BreadcrumbItem, ClientFilters } from '@/types';
    import * as Card from '@/components/ui/card';
    import * as Table from '@/components/ui/table';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Skeleton } from '@/components/ui/skeleton';
    import { Label } from '@/components/ui/label';
    import { 
        Search, 
        Plus, 
        Eye, 
        Edit, 
        RefreshCw,
        ChevronLeft,
        ChevronRight,
        User,
        Building2,
        Mail,
        Phone
    } from 'lucide-svelte';
    import { router } from '@inertiajs/svelte';

    interface Props {
        filters: ClientFilters;
        onFiltersChange: (filters: ClientFilters) => void;
    }

    let { filters, onFiltersChange }: Props = $props();

    // Get clients using TanStack Query
    let clientsQuery = $derived(useClients(filters));

    function formatDateTime(dateString: string): string {
        return new Intl.DateTimeFormat('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(new Date(dateString));
    }

    function updateFilters(updates: Partial<ClientFilters>) {
        onFiltersChange({ ...filters, ...updates });
    }

    function handleSearch() {
        updateFilters({ page: 1 }); // Reset to first page
    }

    function changePage(newPage: number) {
        updateFilters({ page: newPage });
    }

    function handleSort(column: string) {
        if (filters.sort === column) {
            updateFilters({ direction: filters.direction === 'asc' ? 'desc' : 'asc' });
        } else {
            updateFilters({ sort: column, direction: 'asc' });
        }
    }

    function clearFilters() {
        onFiltersChange({
            page: 1,
            per_page: 20,
            search: '',
            sort: 'created_at',
            direction: 'desc',
        });
    }

    function navigateToClient(clientId: string) {
        router.visit(`/clients/${clientId}`);
    }

    function navigateToCreateClient() {
        router.visit('/clients/create');
    }
</script>

<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between space-y-2">
        <h2 class="text-3xl font-bold tracking-tight">Clients</h2>
        <div class="flex items-center space-x-2">
            <Button
                variant="outline"
                size="sm"
                onclick={() => $clientsQuery.refetch()}
                disabled={$clientsQuery.isFetching}
            >
                <RefreshCw class="mr-2 h-4 w-4 {$clientsQuery.isFetching ? 'animate-spin' : ''}" />
                Refresh
            </Button>
            <Button onclick={navigateToCreateClient}>
                <Plus class="mr-2 h-4 w-4" />
                New Client
            </Button>
        </div>
    </div>

    <!-- Filters -->
    <Card.Root>
        <Card.Header>
            <Card.Title class="text-lg">Search & Filters</Card.Title>
        </Card.Header>
        <Card.Content>
            <div class="grid gap-4 md:grid-cols-4">
                <!-- Search -->
                <div class="space-y-2 md:col-span-2">
                    <Label for="search">Search</Label>
                    <div class="relative">
                        <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            id="search"
                            placeholder="Name, email, external ID..."
                            bind:value={filters.search}
                            onkeydown={(e) => e.key === 'Enter' && handleSearch()}
                            class="pl-8"
                        />
                    </div>
                </div>

                <!-- Actions -->
                <div class="space-y-2 md:col-span-2 flex items-end justify-end space-x-2">
                    <Button variant="outline" onclick={clearFilters}>
                        Clear Filters
                    </Button>
                    <Button onclick={handleSearch}>
                        <Search class="mr-2 h-4 w-4" />
                        Search
                    </Button>
                </div>
            </div>
        </Card.Content>
    </Card.Root>

    <!-- Results -->
    <Card.Root>
        <Card.Header>
            <Card.Title class="text-lg">
                {#if $clientsQuery.isLoading}
                    Loading clients...
                {:else if $clientsQuery.data}
                    {@const clientsData = $clientsQuery.data}
                    Clients ({clientsData.total})
                {:else}
                    Clients
                {/if}
            </Card.Title>
        </Card.Header>
        <Card.Content class="p-0">
            {#if $clientsQuery.isLoading}
                <!-- Loading skeleton -->
                <div class="p-6">
                    <div class="space-y-4">
                        {#each Array(5) as _, index (index)}
                            <div class="flex items-center space-x-4">
                                <Skeleton class="h-12 w-12 rounded" />
                                <div class="space-y-2 flex-1">
                                    <Skeleton class="h-4 w-[200px]" />
                                    <Skeleton class="h-4 w-[150px]" />
                                </div>
                                <Skeleton class="h-8 w-[100px]" />
                            </div>
                        {/each}
                    </div>
                </div>
            {:else if $clientsQuery.error}
                <!-- Error state -->
                <div class="p-6 text-center">
                    <p class="text-red-600 dark:text-red-400">
                        {$clientsQuery.error?.message || 'Failed to load clients'}
                    </p>
                    <Button 
                        class="mt-4" 
                        variant="outline" 
                        onclick={() => $clientsQuery.refetch()}
                    >
                        Try Again
                    </Button>
                </div>
            {:else if $clientsQuery.data}
                {@const clientsData = $clientsQuery.data}
                
                <Table.Root>
                    <Table.Header>
                        <Table.Row>
                            <Table.Head 
                                class="cursor-pointer"
                                onclick={() => handleSort('first_name')}
                            >
                                Name
                                {#if filters.sort === 'first_name'}
                                    {filters.direction === 'asc' ? '↑' : '↓'}
                                {/if}
                            </Table.Head>
                            <Table.Head 
                                class="cursor-pointer"
                                onclick={() => handleSort('email')}
                            >
                                Email
                                {#if filters.sort === 'email'}
                                    {filters.direction === 'asc' ? '↑' : '↓'}
                                {/if}
                            </Table.Head>
                            <Table.Head>Phone</Table.Head>
                            <Table.Head>Company</Table.Head>
                            <Table.Head>External ID</Table.Head>
                            <Table.Head 
                                class="cursor-pointer"
                                onclick={() => handleSort('created_at')}
                            >
                                Created
                                {#if filters.sort === 'created_at'}
                                    {filters.direction === 'asc' ? '↑' : '↓'}
                                {/if}
                            </Table.Head>
                            <Table.Head class="text-right">Actions</Table.Head>
                        </Table.Row>
                    </Table.Header>
                    <Table.Body>
                        {#each clientsData.data as client (client.id)}
                            <Table.Row>
                                <Table.Cell>
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-muted rounded-full flex items-center justify-center">
                                                <User class="h-4 w-4 text-muted-foreground" />
                                            </div>
                                        </div>
                                        <div>
                                            <p class="font-medium">
                                                {client.first_name} {client.last_name}
                                            </p>
                                            {#if client.vat_id}
                                                <p class="text-sm text-muted-foreground">VAT: {client.vat_id}</p>
                                            {/if}
                                        </div>
                                    </div>
                                </Table.Cell>
                                <Table.Cell>
                                    <div class="flex items-center space-x-2">
                                        <Mail class="h-4 w-4 text-muted-foreground" />
                                        <span>{client.email}</span>
                                    </div>
                                </Table.Cell>
                                <Table.Cell>
                                    {#if client.phone}
                                        <div class="flex items-center space-x-2">
                                            <Phone class="h-4 w-4 text-muted-foreground" />
                                            <span>{client.phone}</span>
                                        </div>
                                    {:else}
                                        <span class="text-muted-foreground">-</span>
                                    {/if}
                                </Table.Cell>
                                <Table.Cell>
                                    {#if client.company}
                                        <div class="flex items-center space-x-2">
                                            <Building2 class="h-4 w-4 text-muted-foreground" />
                                            <span>{client.company}</span>
                                        </div>
                                    {:else}
                                        <span class="text-muted-foreground">-</span>
                                    {/if}
                                </Table.Cell>
                                <Table.Cell>
                                    {#if client.external_id}
                                        <code class="text-sm bg-muted px-1 py-0.5 rounded">
                                            {client.external_id}
                                        </code>
                                    {:else}
                                        <span class="text-muted-foreground">-</span>
                                    {/if}
                                </Table.Cell>
                                <Table.Cell>
                                    <span class="text-sm text-muted-foreground">
                                        {formatDateTime(client.created_at)}
                                    </span>
                                </Table.Cell>
                                <Table.Cell class="text-right">
                                    <div class="flex justify-end space-x-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onclick={() => navigateToClient(client.id)}
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            variant="outline" 
                                            size="sm"
                                            onclick={() => router.visit(`/clients/${client.id}/edit`)}
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </Table.Cell>
                            </Table.Row>
                        {/each}
                    </Table.Body>
                </Table.Root>

                <!-- Pagination -->
                {#if clientsData.last_page > 1}
                    <div class="flex items-center justify-between px-6 py-4 border-t">
                        <div class="text-sm text-muted-foreground">
                            Showing {((clientsData.current_page - 1) * clientsData.per_page) + 1} to {Math.min(clientsData.current_page * clientsData.per_page, clientsData.total)} of {clientsData.total} entries
                        </div>
                        <div class="flex items-center space-x-2">
                            <Button
                                variant="outline"
                                size="sm"
                                onclick={() => changePage(clientsData.current_page - 1)}
                                disabled={clientsData.current_page <= 1}
                            >
                                <ChevronLeft class="h-4 w-4" />
                                Previous
                            </Button>
                            <div class="flex items-center space-x-1">
                                {#each Array.from({ length: Math.min(5, clientsData.last_page) }, (_, i) => {
                                    const start = Math.max(1, clientsData.current_page - 2);
                                    return start + i;
                                }).filter(page => page <= clientsData.last_page) as page}
                                    <Button
                                        variant={page === clientsData.current_page ? "default" : "outline"}
                                        size="sm"
                                        onclick={() => changePage(page)}
                                    >
                                        {page}
                                    </Button>
                                {/each}
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                onclick={() => changePage(clientsData.current_page + 1)}
                                disabled={clientsData.current_page >= clientsData.last_page}
                            >
                                Next
                                <ChevronRight class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                {/if}
            {/if}
        </Card.Content>
    </Card.Root>
</div>