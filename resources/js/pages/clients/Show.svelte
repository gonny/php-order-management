<script lang="ts">
    import ClientShow from '@/packages/clients/components/ClientShow.svelte';
    
    // Props from Inertia
    let { client } = $props<{ client: any }>();
</script>

<ClientShow clientId={client.id} />