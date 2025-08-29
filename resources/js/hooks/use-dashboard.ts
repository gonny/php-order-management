import { createQuery, createMutation, useQueryClient } from '@tanstack/svelte-query';
import { apiClient, handleApiError } from '@/lib/api';
import type { DashboardMetrics } from '@/types';

// Query keys
export const dashboardKeys = {
  all: ['dashboard'] as const,
  metrics: () => [...dashboardKeys.all, 'metrics'] as const,
};

// Queries
export function useDashboardMetrics() {
  return createQuery({
    queryKey: dashboardKeys.metrics(),
    queryFn: () => apiClient.getDashboardMetrics(),
    staleTime: 1000 * 60 * 2, // 2 minutes
    refetchInterval: 1000 * 60 * 5, // Refetch every 5 minutes
  });
}