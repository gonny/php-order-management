import { QueryClient } from '@tanstack/svelte-query';
import { getContext, setContext } from 'svelte';

const QUERY_CLIENT_KEY = Symbol('queryClient');

export function setQueryClient(queryClient: QueryClient): QueryClient {
    return setContext(QUERY_CLIENT_KEY, queryClient);
}

export function getQueryClient(): QueryClient {
    const queryClient = getContext<QueryClient>(QUERY_CLIENT_KEY);
    if (!queryClient) {
        throw new Error('Query client not found. Make sure to provide it in the app.');
    }
    return queryClient;
}