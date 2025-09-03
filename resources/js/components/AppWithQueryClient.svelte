<script lang="ts">
    import { QueryClientProvider } from '@tanstack/svelte-query';
    import type { QueryClient } from '@tanstack/svelte-query';

    interface Props {
        App: any;
        props: Record<string, any>;
        queryClient: QueryClient;
    }

    let { App, props, queryClient }: Props = $props();
</script>

<QueryClientProvider client={queryClient}>
    <App {...props} />
</QueryClientProvider>
