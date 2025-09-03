import { createInertiaApp, type ResolvedComponent } from '@inertiajs/svelte';
import { QueryClient } from '@tanstack/svelte-query';
import { hydrate, mount } from 'svelte';
import AppWithQueryClient from './components/AppWithQueryClient.svelte';
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
        if (el && el.dataset.serverRendered === 'true') {
            hydrate(AppWithQueryClient, { 
                target: el, 
                props: { 
                    App, 
                    props, 
                    queryClient 
                } 
            });
        } else if (el) {
            mount(AppWithQueryClient, { 
                target: el, 
                props: { 
                    App, 
                    props, 
                    queryClient 
                } 
            });
        }
    },
});
