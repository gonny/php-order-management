// Orders package public API
// This file provides a clean interface for importing orders functionality

// Types
export type {
  Order,
  OrderItem,
  OrderStatus,
  OrderTransition,
  OrderCreateDTO,
  OrderUpdateDTO,
  OrderItemCreateDTO,
  Carrier,
  Currency,
} from './types/order';

export type {
  OrderFilters,
  OrderFilterState,
  OrderSortOption,
  OrderStatusOption,
  OrderCarrierOption,
} from './types/filters';

// Hooks
export {
  useOrders,
  useOrder,
  useCreateOrder,
  useUpdateOrder,
  useDeleteOrder,
  useTransitionOrder,
  orderKeys,
} from './hooks/use-orders';

// Utilities
export {
  statusColors,
  statusLabels,
  getStatusColor,
  getStatusLabel,
  isOrderEditable,
  isOrderCancellable,
  canCreateLabel,
  canVoidLabel,
  getOrderProgress,
  formatOrderNumber,
  getShippingInfo,
  hasShippingLabels,
  getActiveShippingLabel,
  calculateOrderItemTotal,
} from './utils/order-helpers';

export {
  validTransitions,
  transitionLabels,
  getValidTransitions,
  isValidTransition,
  getTargetStatus,
  getTransitionLabel,
  requiresConfirmation,
  getTransitionVariant,
  isSignificantTransition,
} from './utils/status-utils';

export {
  formatCurrency,
  formatCurrencyPlain,
  getCurrencySymbol,
  parseCurrency,
  calculateTotalWithTax,
  calculateTaxAmount,
  formatPercentage,
} from './utils/currency-formatters';

// Stores
export {
  createOrderFiltersStore,
  type OrderFiltersStore,
} from './stores/order-filters.svelte';

// Components will be exported as they are created
// export { default as OrderList } from './components/OrderList.svelte';
// export { default as OrderDetails } from './components/OrderDetails.svelte';
// export { default as OrderStatusBadge } from './components/OrderStatusBadge.svelte';
// export { default as OrderFilters } from './components/OrderFilters.svelte';
// export { default as OrderActions } from './components/OrderActions.svelte';