import { createMutation, useQueryClient } from '@tanstack/svelte-query';
import { orderKeys } from '@/packages/orders/hooks/use-orders';
import type { DpdShipmentCreateRequest } from '../types';

// DPD-specific label creation and management
export function useCreateDpdLabel() {
  const queryClient = useQueryClient();
  
  return createMutation({
    mutationFn: async ({ orderId, data }: { orderId: string; data: DpdShipmentCreateRequest }) => {
      const response = await fetch(`/api/v1/orders/${orderId}/label/dpd`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(data),
      });
      
      const result = await response.json();
      
      if (result.error) {
        throw new Error(result.message || 'DPD label creation failed');
      }
      
      return result;
    },
    onSuccess: (_, { orderId }) => {
      // Refresh order data to show new DPD shipment
      queryClient.invalidateQueries({ queryKey: orderKeys.detail(orderId) });
      queryClient.invalidateQueries({ queryKey: orderKeys.lists() });
    },
    onError: (error) => {
      console.error('DPD label creation error:', error);
    },
  });
}

export function useDownloadDpdLabel() {
  return createMutation({
    mutationFn: (orderId: string) => {
      // Open PDF download in new tab
      window.open(`/orders/${orderId}/label/dpd/download`, '_blank');
      return Promise.resolve();
    },
    onError: (error) => {
      console.error('Failed to download DPD label:', error);
    },
  });
}

export function useDeleteDpdShipment() {
  const queryClient = useQueryClient();
  
  return createMutation({
    mutationFn: async (orderId: string) => {
      const response = await fetch(`/api/v1/orders/${orderId}/shipment/dpd`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });
      
      const result = await response.json();
      
      if (result.error) {
        throw new Error(result.message || 'DPD shipment deletion failed');
      }
      
      return result;
    },
    onSuccess: (_, orderId) => {
      // Refresh order data to remove DPD shipment info
      queryClient.invalidateQueries({ queryKey: orderKeys.detail(orderId) });
      queryClient.invalidateQueries({ queryKey: orderKeys.lists() });
    },
    onError: (error) => {
      console.error('DPD shipment deletion error:', error);
    },
  });
}