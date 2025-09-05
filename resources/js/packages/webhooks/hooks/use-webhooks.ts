import { createQuery, createMutation, useQueryClient } from '@tanstack/svelte-query';
import { apiClient, handleApiError } from '@/lib/api';
import type {
  WebhookFilters,
} from '@/types';

// Query keys
export const webhookKeys = {
  all: ['webhooks'] as const,
  lists: () => [...webhookKeys.all, 'list'] as const,
  list: (filters: WebhookFilters) => [...webhookKeys.lists(), filters] as const,
  details: () => [...webhookKeys.all, 'detail'] as const,
  detail: (id: string) => [...webhookKeys.details(), id] as const,
};

// Queries
export function useWebhooks(filters: WebhookFilters = {}) {
  return createQuery({
    queryKey: webhookKeys.list(filters),
    queryFn: () => apiClient.getWebhooks(filters),
    staleTime: 1000 * 60 * 2, // 2 minutes
  });
}

export function useWebhook(id: string) {
  return createQuery({
    queryKey: webhookKeys.detail(id),
    queryFn: () => apiClient.getWebhook(id),
    enabled: !!id,
    staleTime: 1000 * 60 * 1, // 1 minute
  });
}

// Mutations
export function useReprocessWebhook() {
  const queryClient = useQueryClient();
  
  return createMutation({
    mutationFn: (id: string) => apiClient.reprocessWebhook(id),
    onSuccess: (_, id) => {
      // Invalidate the specific webhook and the list
      queryClient.invalidateQueries({ queryKey: webhookKeys.detail(id) });
      queryClient.invalidateQueries({ queryKey: webhookKeys.lists() });
    },
    onError: (error) => {
      console.error('Failed to reprocess webhook:', handleApiError(error));
    },
  });
}