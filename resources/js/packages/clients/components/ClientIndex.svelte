<script lang="ts">
    import ClientList from '@/packages/clients/components/ClientList.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { type BreadcrumbItem, type ClientFilters } from '@/types';
    import { SvelteURLSearchParams } from 'svelte/reactivity';

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Clients', href: '/clients' },
    ];

    // Initialize filters from URL
    const searchParams = new SvelteURLSearchParams(window.location.search);
    let filters: ClientFilters = $state({
        page: parseInt(searchParams.get('page') || '1'),
        per_page: parseInt(searchParams.get('per_page') || '20'),
        search: searchParams.get('search') || '',
        sort: searchParams.get('sort') || 'created_at',
        direction: (searchParams.get('direction') as 'asc' | 'desc') || 'desc',
    });

    function handleFiltersChange(newFilters: ClientFilters) {
        filters = newFilters;
        navigateToClients();
    }

    function navigateToClients() {
        const params = new SvelteURLSearchParams();
        
        if (filters.page > 1) params.set('page', filters.page.toString());
        if (filters.per_page !== 20) params.set('per_page', filters.per_page.toString());
        if (filters.search) params.set('search', filters.search);
        if (filters.sort !== 'created_at') params.set('sort', filters.sort);
        if (filters.direction !== 'desc') params.set('direction', filters.direction);
        
        const queryString = params.toString();
        const url = `/clients${queryString ? `?${queryString}` : ''}`;
        
        window.history.pushState({}, '', url);
    }
</script>

<svelte:head>
    <title>Clients - Order Management</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <div class="flex-1 space-y-4 p-4 md:p-8 pt-6">
        <ClientList {filters} onFiltersChange={handleFiltersChange} />
    </div>
</AppLayout>