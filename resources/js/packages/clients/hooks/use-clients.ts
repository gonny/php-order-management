import { createQuery, createMutation, useQueryClient } from '@tanstack/svelte-query';
import { inertiaApiClient, handleInertiaApiError } from '@/lib/inertia-api';
import type {
  Client,
  ClientCreateDTO,
  ClientFilters,
  ClientUpdateDTO,
} from '@/types';

// For mutations, we'll use the original API client with HMAC auth
import { apiClient, handleApiError } from '@/lib/api';

// Query keys
export const clientKeys = {
  all: ['clients'] as const,
  lists: () => [...clientKeys.all, 'list'] as const,
  list: (filters: ClientFilters) => [...clientKeys.lists(), filters] as const,
  details: () => [...clientKeys.all, 'detail'] as const,
  detail: (id: string) => [...clientKeys.details(), id] as const,
};

// Queries (using session-authenticated API)
export function useClients(filters: ClientFilters = {}) {
  return createQuery({
    queryKey: clientKeys.list(filters),
    queryFn: () => inertiaApiClient.getClients(filters),
    staleTime: 1000 * 60 * 5, // 5 minutes
  });
}

export function useClient(id: string) {
  return createQuery({
    queryKey: clientKeys.detail(id),
    queryFn: () => inertiaApiClient.getClient(id),
    enabled: !!id,
    staleTime: 1000 * 60 * 2, // 2 minutes
  });
}

// Mutations
export function useCreateClient() {
  const queryClient = useQueryClient();
  
  return createMutation({
    mutationFn: (data: ClientCreateDTO) => apiClient.createClient(data),
    onSuccess: (newClient) => {
      // Invalidate clients list
      queryClient.invalidateQueries({ queryKey: clientKeys.lists() });
      // Add to cache
      queryClient.setQueryData(clientKeys.detail(newClient.id), newClient);
    },
    onError: (error) => {
      console.error('Failed to create client:', handleApiError(error));
    },
  });
}

export function useUpdateClient() {
  const queryClient = useQueryClient();
  
  return createMutation({
    mutationFn: ({ id, data }: { id: string; data: ClientUpdateDTO }) =>
      apiClient.updateClient(id, data),
    onMutate: async ({ id, data }) => {
      // Cancel outgoing queries
      await queryClient.cancelQueries({ queryKey: clientKeys.detail(id) });
      
      // Snapshot previous value
      const previousClient = queryClient.getQueryData<Client>(clientKeys.detail(id));
      
      // Optimistically update
      if (previousClient) {
        queryClient.setQueryData(clientKeys.detail(id), {
          ...previousClient,
          ...data,
          updated_at: new Date().toISOString(),
        });
      }
      
      return { previousClient };
    },
    onError: (error, { id }, context) => {
      // Rollback on error
      if (context?.previousClient) {
        queryClient.setQueryData(clientKeys.detail(id), context.previousClient);
      }
      console.error('Failed to update client:', handleApiError(error));
    },
    onSettled: (data, error, { id }) => {
      // Refetch to ensure consistency
      queryClient.invalidateQueries({ queryKey: clientKeys.detail(id) });
      queryClient.invalidateQueries({ queryKey: clientKeys.lists() });
    },
  });
}

export function useDeleteClient() {
  const queryClient = useQueryClient();
  
  return createMutation({
    mutationFn: (id: string) => apiClient.deleteClient(id),
    onSuccess: (_, id) => {
      // Remove from cache
      queryClient.removeQueries({ queryKey: clientKeys.detail(id) });
      // Invalidate lists
      queryClient.invalidateQueries({ queryKey: clientKeys.lists() });
    },
    onError: (error) => {
      console.error('Failed to delete client:', handleApiError(error));
    },
  });
}