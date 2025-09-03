import type { ResolvedComponent } from '@inertiajs/svelte';
import { createInertiaApp } from '@inertiajs/svelte';
import createServer from '@inertiajs/svelte/server';
import { QueryClient } from '@tanstack/svelte-query';
import type { LegacyComponentType } from 'svelte/legacy';
import { render } from 'svelte/server';
import AppWithQueryClient from './components/AppWithQueryClient.svelte';

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
            
            // Use AppWithQueryClient to properly provide TanStack Query context during SSR
            return render(AppWithQueryClient, { 
                props: { 
                    App, 
                    props, 
                    queryClient 
                } 
            });
        },
    }),
);
