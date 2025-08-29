import { createInertiaApp, type ResolvedComponent } from '@inertiajs/svelte';
import { QueryClient } from '@tanstack/svelte-query';
import { hydrate, mount } from 'svelte';
import { setQueryClient } from '@/contexts/query-client';
import '../css/app.css';
import './bootstrap';

// Create a global query client
const queryClient = new QueryClient({
    defaultOptions: {
        queries: {
            staleTime: 1000 * 60 * 5, // 5 minutes
            retry: (failureCount, error: any) => {
                // Don't retry on 4xx errors except timeout
                if (error?.status >= 400 && error?.status < 500 && error?.status !== 408) {
                    return false;
                }
                return failureCount < 3;
            },
        },
        mutations: {
            retry: false,
        },
    },
});

createInertiaApp({
    resolve: (name: string) => {
        const pages = import.meta.glob<ResolvedComponent>('./pages/**/*.svelte', { eager: true });
        return pages[`./pages/${name}.svelte`];
    },
    setup({ el, App, props }) {
        // Set the query client in context before mounting
        setQueryClient(queryClient);
        
        if (el && el.dataset.serverRendered === 'true') {
            hydrate(App, { target: el, props });
        } else if (el) {
            mount(App, { target: el, props });
        }
    },
});
