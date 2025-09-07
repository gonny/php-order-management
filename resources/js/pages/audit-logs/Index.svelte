<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import type { BreadcrumbItem } from '@/types';
    import * as Card from '@/components/ui/card';
    import * as Table from '@/components/ui/table';
    import * as Tabs from '@/components/ui/tabs';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import * as Select from '@/components/ui/select';
    import { RefreshCw, Filter, FileText, User, Activity, Clock } from 'lucide-svelte';
    import { onMount } from 'svelte';
    import { spaApiClient } from '@/lib/spa-api';
    import { SvelteURLSearchParams } from 'svelte/reactivity';

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Audit Logs', href: '/audit-logs' },
    ];

    let auditLogs: any[] = [];
    let stats = {
        total_entries: 0,
        entries_today: 0,
        entries_this_week: 0,
        by_entity_type: {},
        by_action: {},
        by_actor_type: {}
    };
    let meta = {
        current_page: 1,
        per_page: 20,
        total: 0,
        last_page: 1,
        from: 0,
        to: 0
    };
    let isLoading = true;
    let activeTab = 'logs';

    // Filters
    let filters = {
        entity_type: '',
        entity_id: '',
        actor_type: '',
        actor_id: '',
        action: '',
        from_date: '',
        to_date: '',
        page: 1,
        per_page: 20
    };

    async function loadAuditLogs() {
        isLoading = true;
        try {
            const params = new SvelteURLSearchParams();
            Object.entries(filters).forEach(([key, value]) => {
                if (value) params.append(key, value.toString());
            });

            const response = await spaApiClient.get(`/audit-logs?${params.toString()}`);
            auditLogs = response.data;
            meta = response.meta;
        } catch (error) {
            console.error('Failed to load audit logs:', error);
        } finally {
            isLoading = false;
        }
    }

    async function loadStats() {
        try {
            const response = await spaApiClient.get('/audit-logs/stats');
            stats = response.data;
        } catch (error) {
            console.error('Failed to load audit log stats:', error);
        }
    }

    function applyFilters() {
        filters.page = 1;
        loadAuditLogs();
    }

    function clearFilters() {
        filters = {
            entity_type: '',
            entity_id: '',
            actor_type: '',
            actor_id: '',
            action: '',
            from_date: '',
            to_date: '',
            page: 1,
            per_page: 20
        };
        loadAuditLogs();
    }

    function changePage(page: number) {
        filters.page = page;
        loadAuditLogs();
    }

    function formatDate(dateString: string) {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleString();
    }

    function getActionColor(action: string) {
        const colors = {
            'created': 'bg-green-100 text-green-800',
            'updated': 'bg-blue-100 text-blue-800',
            'deleted': 'bg-red-100 text-red-800',
            'status_change': 'bg-purple-100 text-purple-800',
            'login': 'bg-yellow-100 text-yellow-800',
            'logout': 'bg-gray-100 text-gray-800',
        };
        return colors[action] || 'bg-gray-100 text-gray-800';
    }

    function getActorIcon(actorType: string) {
        switch (actorType) {
            case 'user': return User;
            case 'api': return Activity;
            default: return Activity;
        }
    }

    onMount(() => {
        loadAuditLogs();
        loadStats();
    });
</script>

<AppLayout {breadcrumbs}>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Audit Logs</h1>
                <p class="text-muted-foreground">Track all system activities and changes</p>
            </div>
            <Button onclick={loadAuditLogs} disabled={isLoading}>
                <RefreshCw class="h-4 w-4 mr-2 {isLoading ? 'animate-spin' : ''}" />
                Refresh
            </Button>
        </div>

        <!-- Tabs -->
        <Tabs.Root bind:value={activeTab} class="w-full">
            <Tabs.List class="grid w-full grid-cols-2">
                <Tabs.Trigger value="logs">Audit Logs</Tabs.Trigger>
                <Tabs.Trigger value="stats">Statistics</Tabs.Trigger>
            </Tabs.List>

            <!-- Audit Logs Tab -->
            <Tabs.Content value="logs" class="space-y-4">
                <!-- Filters -->
                <Card.Root>
                    <Card.Header>
                        <Card.Title class="flex items-center gap-2">
                            <Filter class="h-5 w-5" />
                            Filters
                        </Card.Title>
                    </Card.Header>
                    <Card.Content>
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                            <div>
                                <Label for="entity_type">Entity Type</Label>
                                <Select.Root bind:selected={filters.entity_type}>
                                    <Select.Trigger>
                                        <Select.Value placeholder="All entities" />
                                    </Select.Trigger>
                                    <Select.Content>
                                        <Select.Item value="">All entities</Select.Item>
                                        <Select.Item value="order">Orders</Select.Item>
                                        <Select.Item value="client">Clients</Select.Item>
                                        <Select.Item value="user">Users</Select.Item>
                                    </Select.Content>
                                </Select.Root>
                            </div>

                            <div>
                                <Label for="entity_id">Entity ID</Label>
                                <Input bind:value={filters.entity_id} placeholder="Entity ID" />
                            </div>

                            <div>
                                <Label for="actor_type">Actor Type</Label>
                                <Select.Root bind:selected={filters.actor_type}>
                                    <Select.Trigger>
                                        <Select.Value placeholder="All actors" />
                                    </Select.Trigger>
                                    <Select.Content>
                                        <Select.Item value="">All actors</Select.Item>
                                        <Select.Item value="user">User</Select.Item>
                                        <Select.Item value="api">API</Select.Item>
                                        <Select.Item value="system">System</Select.Item>
                                    </Select.Content>
                                </Select.Root>
                            </div>

                            <div>
                                <Label for="action">Action</Label>
                                <Select.Root type="single" bind:value={filters.action}>
                                    <Select.Trigger>
                                        <Select.Value placeholder="All actions" />
                                    </Select.Trigger>
                                    <Select.Content>
                                        <Select.Item value="">All actions</Select.Item>
                                        <Select.Item value="created">Created</Select.Item>
                                        <Select.Item value="updated">Updated</Select.Item>
                                        <Select.Item value="deleted">Deleted</Select.Item>
                                        <Select.Item value="status_change">Status Change</Select.Item>
                                    </Select.Content>
                                </Select.Root>
                            </div>

                            <div class="flex gap-2">
                                <Button onclick={applyFilters} class="flex-1">
                                    <Filter class="h-4 w-4 mr-2" />
                                    Apply
                                </Button>
                                <Button variant="outline" onclick={clearFilters}>
                                    Clear
                                </Button>
                            </div>
                        </div>
                    </Card.Content>
                </Card.Root>

                <!-- Audit Logs Table -->
                <Card.Root>
                    <Card.Header>
                        <Card.Title>Activity Log</Card.Title>
                        <Card.Description>
                            Showing {meta.from || 0} to {meta.to || 0} of {meta.total} entries
                        </Card.Description>
                    </Card.Header>
                    <Card.Content>
                        {#if isLoading}
                            <div class="flex justify-center py-8">
                                <RefreshCw class="h-6 w-6 animate-spin" />
                            </div>
                        {:else if auditLogs.length > 0}
                            <Table.Root>
                                <Table.Header>
                                    <Table.Row>
                                        <Table.Head>Timestamp</Table.Head>
                                        <Table.Head>Actor</Table.Head>
                                        <Table.Head>Action</Table.Head>
                                        <Table.Head>Entity</Table.Head>
                                        <Table.Head>Changes</Table.Head>
                                    </Table.Row>
                                </Table.Header>
                                <Table.Body>
                                    {#each auditLogs as log (log.id)}
                                        <Table.Row>
                                            <Table.Cell>
                                                <div class="flex items-center gap-2">
                                                    <Clock class="h-4 w-4 text-muted-foreground" />
                                                    <span class="text-sm">{formatDate(log.created_at)}</span>
                                                </div>
                                            </Table.Cell>
                                            <Table.Cell>
                                                <div class="flex items-center gap-2">
                                                    <svelte:component this={getActorIcon(log.actor_type)} class="h-4 w-4 text-muted-foreground" />
                                                    <div>
                                                        <div class="font-medium">{log.actor_id}</div>
                                                        <div class="text-sm text-muted-foreground">{log.actor_type}</div>
                                                    </div>
                                                </div>
                                            </Table.Cell>
                                            <Table.Cell>
                                                <Badge class={getActionColor(log.action)}>
                                                    {log.action}
                                                </Badge>
                                            </Table.Cell>
                                            <Table.Cell>
                                                <div>
                                                    <div class="font-medium">{log.entity_type}</div>
                                                    <div class="text-sm text-muted-foreground">{log.entity_id}</div>
                                                </div>
                                            </Table.Cell>
                                            <Table.Cell>
                                                {#if log.action === 'status_change' && log.before?.status && log.after?.status}
                                                    <div class="text-sm">
                                                        <span class="text-muted-foreground">{log.before.status}</span>
                                                        <span class="mx-2">→</span>
                                                        <span class="font-medium">{log.after.status}</span>
                                                    </div>
                                                {:else}
                                                    <span class="text-sm text-muted-foreground">
                                                        {log.action === 'created' ? 'Entity created' : 
                                                         log.action === 'deleted' ? 'Entity deleted' : 
                                                         'Entity modified'}
                                                    </span>
                                                {/if}
                                            </Table.Cell>
                                        </Table.Row>
                                    {/each}
                                </Table.Body>
                            </Table.Root>

                            <!-- Pagination -->
                            {#if meta.last_page > 1}
                                <div class="flex items-center justify-between mt-4">
                                    <Button 
                                        variant="outline" 
                                        disabled={meta.current_page <= 1}
                                        onclick={() => changePage(meta.current_page - 1)}
                                    >
                                        Previous
                                    </Button>
                                    <span class="text-sm text-muted-foreground">
                                        Page {meta.current_page} of {meta.last_page}
                                    </span>
                                    <Button 
                                        variant="outline" 
                                        disabled={meta.current_page >= meta.last_page}
                                        onclick={() => changePage(meta.current_page + 1)}
                                    >
                                        Next
                                    </Button>
                                </div>
                            {/if}
                        {:else}
                            <div class="text-center py-8">
                                <FileText class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                                <h3 class="text-lg font-medium">No audit logs found</h3>
                                <p class="text-muted-foreground">No activities match your current filters</p>
                            </div>
                        {/if}
                    </Card.Content>
                </Card.Root>
            </Tabs.Content>

            <!-- Statistics Tab -->
            <Tabs.Content value="stats" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Summary Stats -->
                    <Card.Root>
                        <Card.Content class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Total Entries</p>
                                    <p class="text-2xl font-bold">{stats.total_entries}</p>
                                </div>
                                <FileText class="h-8 w-8 text-blue-600" />
                            </div>
                        </Card.Content>
                    </Card.Root>

                    <Card.Root>
                        <Card.Content class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Today</p>
                                    <p class="text-2xl font-bold">{stats.entries_today}</p>
                                </div>
                                <Clock class="h-8 w-8 text-green-600" />
                            </div>
                        </Card.Content>
                    </Card.Root>

                    <Card.Root>
                        <Card.Content class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">This Week</p>
                                    <p class="text-2xl font-bold">{stats.entries_this_week}</p>
                                </div>
                                <Activity class="h-8 w-8 text-purple-600" />
                            </div>
                        </Card.Content>
                    </Card.Root>
                </div>

                <!-- Entity Types Breakdown -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card.Root>
                        <Card.Header>
                            <Card.Title>By Entity Type</Card.Title>
                        </Card.Header>
                        <Card.Content>
                            <div class="space-y-2">
                                {#each Object.entries(stats.by_entity_type) as [entityType, count] (entityType)}
                                    <div class="flex justify-between items-center">
                                        <span class="capitalize">{entityType}</span>
                                        <Badge variant="secondary">{count}</Badge>
                                    </div>
                                {/each}
                            </div>
                        </Card.Content>
                    </Card.Root>

                    <Card.Root>
                        <Card.Header>
                            <Card.Title>By Action</Card.Title>
                        </Card.Header>
                        <Card.Content>
                            <div class="space-y-2">
                                {#each Object.entries(stats.by_action) as [action, count] (action)}
                                    <div class="flex justify-between items-center">
                                        <Badge class={getActionColor(action)}>{action}</Badge>
                                        <Badge variant="secondary">{count}</Badge>
                                    </div>
                                {/each}
                            </div>
                        </Card.Content>
                    </Card.Root>

                    <Card.Root>
                        <Card.Header>
                            <Card.Title>By Actor Type</Card.Title>
                        </Card.Header>
                        <Card.Content>
                            <div class="space-y-2">
                                {#each Object.entries(stats.by_actor_type) as [actorType, count] (actorType)}
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-2">
                                            <svelte:component this={getActorIcon(actorType)} class="h-4 w-4" />
                                            <span class="capitalize">{actorType}</span>
                                        </div>
                                        <Badge variant="secondary">{count}</Badge>
                                    </div>
                                {/each}
                            </div>
                        </Card.Content>
                    </Card.Root>
                </div>
            </Tabs.Content>
        </Tabs.Root>
    </div>
</AppLayout>