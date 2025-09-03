import { createMutation, useQueryClient } from '@tanstack/svelte-query';
import { apiClient, handleApiError } from '@/packages/shared/lib/api';
import { orderKeys } from '@/packages/orders/hooks/use-orders';
import type { LabelCreateDTO } from '../types';

// Generic label management hooks for all carriers
export function useCreateLabel() {
  const queryClient = useQueryClient();
  
  return createMutation({
    mutationFn: ({ orderId, data }: { orderId: string; data?: LabelCreateDTO }) =>
      apiClient.createLabel(orderId, data || {}),
    onSuccess: (_, { orderId }) => {
      // Invalidate the order to refresh label data
      queryClient.invalidateQueries({ queryKey: orderKeys.detail(orderId) });
      queryClient.invalidateQueries({ queryKey: orderKeys.lists() });
    },
    onError: (error) => {
      console.error('Failed to create label:', handleApiError(error));
    },
  });
}

export function useVoidLabel() {
  const queryClient = useQueryClient();
  
  return createMutation({
    mutationFn: ({ labelId }: { labelId: string; orderId: string }) =>
      apiClient.voidLabel(labelId),
    onSuccess: (_, { orderId }) => {
      // Invalidate the order to refresh label data
      queryClient.invalidateQueries({ queryKey: orderKeys.detail(orderId) });
      queryClient.invalidateQueries({ queryKey: orderKeys.lists() });
    },
    onError: (error) => {
      console.error('Failed to void label:', handleApiError(error));
    },
  });
}

export function useDownloadLabel() {
  return createMutation({
    mutationFn: (labelId: string) => apiClient.downloadLabel(labelId),
    onSuccess: (blob, labelId) => {
      // Create download link
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `shipping-label-${labelId}.pdf`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
    },
    onError: (error) => {
      console.error('Failed to download label:', handleApiError(error));
    },
  });
}