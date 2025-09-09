<script lang="ts">
    import WebhookList from '@/packages/webhooks/components/WebhookList.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { type BreadcrumbItem, type WebhookFilters } from '@/types';
    import { SvelteURLSearchParams } from 'svelte/reactivity';

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Webhooks', href: '/webhooks' },
    ];

    // Initialize filters from URL
    const searchParams = new SvelteURLSearchParams(window.location.search);
    let filters: WebhookFilters = $state({
        page: parseInt(searchParams.get('page') || '1'),
        per_page: parseInt(searchParams.get('per_page') || '20'),
        sort: searchParams.get('sort') || 'created_at',
        direction: (searchParams.get('direction') as 'asc' | 'desc') || 'desc',
        source: searchParams.get('source') || '',
        status: searchParams.get('status') || '',
        event_type: searchParams.get('event_type') || '',
        related_order_id: searchParams.get('related_order_id') || '',
    });

    function handleFiltersChange(newFilters: WebhookFilters) {
        filters = newFilters;
        navigateToWebhooks();
    }

    function navigateToWebhooks() {
        const params = new SvelteURLSearchParams();
        
        if (filters.page > 1) params.set('page', filters.page.toString());
        if (filters.per_page !== 20) params.set('per_page', filters.per_page.toString());
        if (filters.sort !== 'created_at') params.set('sort', filters.sort);
        if (filters.direction !== 'desc') params.set('direction', filters.direction);
        if (filters.source) params.set('source', filters.source);
        if (filters.status) params.set('status', filters.status);
        if (filters.event_type) params.set('event_type', filters.event_type);
        if (filters.related_order_id) params.set('related_order_id', filters.related_order_id);
        
        const queryString = params.toString();
        const url = `/webhooks${queryString ? `?${queryString}` : ''}`;
        
        window.history.pushState({}, '', url);
    }
</script>

<svelte:head>
    <title>Webhooks - Order Management</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <div class="flex-1 space-y-4 p-4 md:p-8 pt-6">
        <WebhookList {filters} onFiltersChange={handleFiltersChange} />
    </div>
</AppLayout>