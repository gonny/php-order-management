import { createQuery, createMutation, useQueryClient } from '@tanstack/svelte-query';
import { inertiaApiClient, handleInertiaApiError } from '@/lib/inertia-api';
import type {
  Order,
  OrderFilters,
} from '@/packages/orders/types/order';

// For mutations that need server-side handling, we'll still use the original API client
import { apiClient, handleApiError } from '@/lib/api';
import type {
  OrderCreateDTO,
  OrderTransition,
  OrderUpdateDTO,
} from '@/types';

// Query keys
export const orderKeys = {
  all: ['orders'] as const,
  lists: () => [...orderKeys.all, 'list'] as const,
  list: (filters: OrderFilters) => [...orderKeys.lists(), filters] as const,
  details: () => [...orderKeys.all, 'detail'] as const,
  detail: (id: string) => [...orderKeys.details(), id] as const,
};

// Queries (using session-authenticated API)
export function useOrders(filters: OrderFilters = {}) {
  return createQuery({
    queryKey: orderKeys.list(filters),
    queryFn: () => inertiaApiClient.getOrders(filters),
    staleTime: 1000 * 60 * 5, // 5 minutes
  });
}

export function useOrder(id: string) {
  return createQuery({
    queryKey: orderKeys.detail(id),
    queryFn: () => inertiaApiClient.getOrder(id),
    enabled: !!id,
    staleTime: 1000 * 60 * 2, // 2 minutes
  });
}

// Mutations (still use HMAC API for create/update/delete operations)
export function useCreateOrder() {
  const queryClient = useQueryClient();
  
  return createMutation({
    mutationFn: (data: OrderCreateDTO) => apiClient.createOrder(data),
    onSuccess: (newOrder) => {
      // Invalidate orders list
      queryClient.invalidateQueries({ queryKey: orderKeys.lists() });
      // Add to cache
      queryClient.setQueryData(orderKeys.detail(newOrder.id), newOrder);
    },
    onError: (error) => {
      console.error('Failed to create order:', handleApiError(error));
    },
  });
}

export function useUpdateOrder() {
  const queryClient = useQueryClient();
  
  return createMutation({
    mutationFn: ({ id, data }: { id: string; data: OrderUpdateDTO }) =>
      apiClient.updateOrder(id, data),
    onMutate: async ({ id, data }) => {
      // Cancel outgoing queries
      await queryClient.cancelQueries({ queryKey: orderKeys.detail(id) });
      
      // Snapshot previous value
      const previousOrder = queryClient.getQueryData<Order>(orderKeys.detail(id));
      
      // Optimistically update
      if (previousOrder) {
        queryClient.setQueryData(orderKeys.detail(id), {
          ...previousOrder,
          ...data,
          updated_at: new Date().toISOString(),
        });
      }
      
      return { previousOrder };
    },
    onError: (error, { id }, context) => {
      // Rollback on error
      if (context?.previousOrder) {
        queryClient.setQueryData(orderKeys.detail(id), context.previousOrder);
      }
      console.error('Failed to update order:', handleApiError(error));
    },
    onSettled: (data, error, { id }) => {
      // Refetch to ensure consistency
      queryClient.invalidateQueries({ queryKey: orderKeys.detail(id) });
      queryClient.invalidateQueries({ queryKey: orderKeys.lists() });
    },
  });
}

export function useDeleteOrder() {
  const queryClient = useQueryClient();
  
  return createMutation({
    mutationFn: (id: string) => apiClient.deleteOrder(id),
    onSuccess: (_, id) => {
      // Remove from cache
      queryClient.removeQueries({ queryKey: orderKeys.detail(id) });
      // Invalidate lists
      queryClient.invalidateQueries({ queryKey: orderKeys.lists() });
    },
    onError: (error) => {
      console.error('Failed to delete order:', handleApiError(error));
    },
  });
}

export function useTransitionOrder() {
  const queryClient = useQueryClient();
  
  return createMutation({
    mutationFn: ({ id, transition }: { id: string; transition: OrderTransition }) =>
      apiClient.transitionOrder(id, transition),
    onMutate: async ({ id, transition }) => {
      // Cancel outgoing queries
      await queryClient.cancelQueries({ queryKey: orderKeys.detail(id) });
      
      // Snapshot previous value
      const previousOrder = queryClient.getQueryData<Order>(orderKeys.detail(id));
      
      // Optimistically update status (basic mapping)
      if (previousOrder) {
        const statusMap: Record<OrderTransition, string> = {
          confirm: 'confirmed',
          pay: 'paid', 
          fulfill: 'fulfilled',
          complete: 'completed',
          cancel: 'cancelled',
          hold: 'on_hold',
          fail: 'failed',
          restart: 'new',
        };
        
        queryClient.setQueryData(orderKeys.detail(id), {
          ...previousOrder,
          status: statusMap[transition] as any,
          updated_at: new Date().toISOString(),
        });
      }
      
      return { previousOrder };
    },
    onError: (error, { id }, context) => {
      // Rollback on error
      if (context?.previousOrder) {
        queryClient.setQueryData(orderKeys.detail(id), context.previousOrder);
      }
      console.error('Failed to transition order:', handleApiError(error));
    },
    onSettled: (data, error, { id }) => {
      // Refetch to ensure consistency
      queryClient.invalidateQueries({ queryKey: orderKeys.detail(id) });
      queryClient.invalidateQueries({ queryKey: orderKeys.lists() });
    },
  });
}