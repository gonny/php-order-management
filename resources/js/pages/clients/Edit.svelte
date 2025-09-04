<script lang="ts">
    import ClientEdit from '@/packages/clients/components/ClientEdit.svelte';
    
    // Props from Inertia
    let { client } = $props<{ client: any }>();
</script>

<ClientEdit clientId={client.id} />