<script lang="ts">
    import ClientEdit from '@/packages/clients/components/ClientEdit.svelte';
    
    // Props from Inertia
    let { client } = $props<{ client: any }>();
</script>

{#if client?.id}
    <ClientEdit clientId={client.id} />
{:else}
    <div class="p-8">
        <p class="text-red-600">Error: Client data not found.</p>
        <a href="/clients" class="text-blue-600 hover:underline">Return to clients list</a>
    </div>
{/if}