<script lang="ts">
    import ClientShow from '@/packages/clients/components/ClientShow.svelte';
    
    // Props from Inertia
    let { client } = $props<{ client: any }>();
</script>

{#if client?.id}
    <ClientShow clientId={client.id} />
{:else}
    <div class="p-8">
        <p class="text-red-600">Error: Client data not found.</p>
        <a href="/clients" class="text-blue-600 hover:underline">Return to clients list</a>
    </div>
{/if}