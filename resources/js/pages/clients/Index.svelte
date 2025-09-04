<script lang="ts">
    import { useClients } from '@/hooks/use-clients';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { type BreadcrumbItem, type ClientFilters } from '@/types';
    import { SvelteURLSearchParams } from 'svelte/reactivity';
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

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Clients', href: '/clients' },
    ];

    // Initialize filters
    let filters: ClientFilters = $state({
        page: 1,
        per_page: 20,
        search: '',
        sort: 'created_at',
        direction: 'desc',
    });

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

    function navigateToClients() {
        const params = new SvelteURLSearchParams();
        
        Object.entries(filters).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                params.append(key, value.toString());
            }
        });

        const queryString = params.toString();
        router.visit(`/clients${queryString ? `?${queryString}` : ''}`, {
            preserveState: true,
            preserveScroll: true,
        });
    }

    function handleSearch() {
        filters.page = 1; // Reset to first page
        navigateToClients();
    }

    function changePage(newPage: number) {
        filters.page = newPage;
        navigateToClients();
    }

    function handleSort(column: string) {
        if (filters.sort === column) {
            filters.direction = filters.direction === 'asc' ? 'desc' : 'asc';
        } else {
            filters.sort = column;
            filters.direction = 'asc';
        }
        navigateToClients();
    }

    function clearFilters() {
        filters = {
            page: 1,
            per_page: 20,
            search: '',
            sort: 'created_at',
            direction: 'desc',
        };
        navigateToClients();
    }

    function navigateToClient(clientId: string) {
        router.visit(`/clients/${clientId}`);
    }

    function navigateToCreateClient() {
        router.visit('/clients/create');
    }
</script>

<svelte:head>
    <title>Clients - Order Management</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <div class="flex-1 space-y-4 p-4 md:p-8 pt-6">
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
                                class="pl-8"
                                onkeydown={(e) => e.key === 'Enter' && handleSearch()}
                            />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-2 md:col-span-2">
                        <Label>&nbsp;</Label>
                        <div class="flex space-x-2">
                            <Button onclick={handleSearch} class="flex-1">
                                <Search class="mr-2 h-4 w-4" />
                                Search
                            </Button>
                            <Button variant="outline" onclick={clearFilters}>
                                Clear
                            </Button>
                        </div>
                    </div>
                </div>
            </Card.Content>
        </Card.Root>

        <!-- Clients Table -->
        <Card.Root>
            <Card.Content class="p-0">
                {#if $clientsQuery.isLoading}
                    <!-- Loading state -->
                    <div class="p-6">
                        <div class="space-y-4">
                            <!-- eslint-disable-next-line @typescript-eslint/no-unused-vars -->
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
                                        {client.external_id || '-'}
                                    </Table.Cell>
                                    <Table.Cell>
                                        {formatDateTime(client.created_at)}
                                    </Table.Cell>
                                    <Table.Cell class="text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onclick={() => navigateToClient(client.id)}
                                            >
                                                <Eye class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
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
                    {#if clientsData.meta.last_page > 1}
                        <div class="flex items-center justify-between px-6 py-4 border-t">
                            <div class="text-sm text-muted-foreground">
                                Showing {clientsData.meta.from} to {clientsData.meta.to} of {clientsData.meta.total} clients
                            </div>
                            <div class="flex items-center space-x-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onclick={() => changePage(clientsData.meta.current_page - 1)}
                                    disabled={clientsData.meta.current_page === 1}
                                >
                                    <ChevronLeft class="h-4 w-4" />
                                    Previous
                                </Button>
                                
                                <div class="flex items-center space-x-1">
                                    {#each Array.from({length: Math.min(5, clientsData.meta.last_page)}, (_, i) => {
                                        const start = Math.max(1, clientsData.meta.current_page - 2);
                                        return start + i;
                                    }).filter(page => page <= clientsData.meta.last_page) as pageNum (pageNum)}
                                        <Button
                                            variant={pageNum === clientsData.meta.current_page ? "default" : "outline"}
                                            size="sm"
                                            onclick={() => changePage(pageNum)}
                                        >
                                            {pageNum}
                                        </Button>
                                    {/each}
                                </div>

                                <Button
                                    variant="outline"
                                    size="sm"
                                    onclick={() => changePage(clientsData.meta.current_page + 1)}
                                    disabled={clientsData.meta.current_page === clientsData.meta.last_page}
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
</AppLayout>