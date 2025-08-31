import type { ResolvedComponent } from '@inertiajs/svelte';
import { createInertiaApp } from '@inertiajs/svelte';
import createServer from '@inertiajs/svelte/server';
import { QueryClient } from '@tanstack/svelte-query';
import type { LegacyComponentType } from 'svelte/legacy';
import { render } from 'svelte/server';
import { setQueryClient } from '@/contexts/query-client';

createServer((page) =>
    createInertiaApp({
        page,
        resolve: async (name: string): Promise<ResolvedComponent> => {
            const pages = import.meta.glob<{ default: LegacyComponentType }>('./pages/**/*.svelte', { eager: true });
            return pages[`./pages/${name}.svelte`].default as unknown as ResolvedComponent;
        },
        setup({ App, props }) {
            // Create a query client for SSR
            const queryClient = new QueryClient({
                defaultOptions: {
                    queries: {
                        staleTime: 1000 * 60 * 5,
                        retry: false, // Don't retry during SSR
                    },
                },
            });
            
            // Set the query client in context
            setQueryClient(queryClient);
            
            return render(App, { props });
        },
    }),
);
