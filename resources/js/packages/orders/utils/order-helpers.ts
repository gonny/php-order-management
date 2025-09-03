import type { Order, OrderStatus } from '../types/order';

/**
 * Order business logic and helper functions
 */

// Status display configuration
export const statusColors: Record<OrderStatus, string> = {
  new: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
  confirmed: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
  paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
  fulfilled: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
  completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
  cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
  on_hold: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
  failed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
};

export const statusLabels: Record<OrderStatus, string> = {
  new: 'New',
  confirmed: 'Confirmed',
  paid: 'Paid',
  fulfilled: 'Fulfilled',
  completed: 'Completed',
  cancelled: 'Cancelled',
  on_hold: 'On Hold',
  failed: 'Failed',
};

// Business logic functions
export function getStatusColor(status: OrderStatus): string {
  return statusColors[status] || 'bg-gray-100 text-gray-800';
}

export function getStatusLabel(status: OrderStatus): string {
  return statusLabels[status] || status;
}

export function isOrderEditable(order: Order): boolean {
  return ['new', 'confirmed'].includes(order.status);
}

export function isOrderCancellable(order: Order): boolean {
  return ['new', 'confirmed', 'paid'].includes(order.status);
}

export function canCreateLabel(order: Order): boolean {
  return ['confirmed', 'paid'].includes(order.status) && !order.shipping_labels?.length;
}

export function canVoidLabel(order: Order): boolean {
  return order.shipping_labels?.some(label => label.status === 'generated') ?? false;
}

export function getOrderProgress(order: Order): number {
  const statusProgress: Record<OrderStatus, number> = {
    new: 10,
    confirmed: 25,
    paid: 50,
    fulfilled: 75,
    completed: 100,
    cancelled: 0,
    on_hold: 25,
    failed: 0,
  };
  
  return statusProgress[order.status] || 0;
}

export function formatOrderNumber(order: Order): string {
  return `#${order.number}`;
}

export function getShippingInfo(order: Order): {
  method?: string;
  carrier: string;
  trackingNumber?: string;
} {
  return {
    method: order.shipping_method,
    carrier: order.carrier,
    trackingNumber: order.tracking_number,
  };
}

export function hasShippingLabels(order: Order): boolean {
  return Boolean(order.shipping_labels?.length);
}

export function getActiveShippingLabel(order: Order) {
  return order.shipping_labels?.find(label => 
    ['generated', 'printed'].includes(label.status)
  );
}

export function calculateOrderItemTotal(qty: number, price: number, taxRate: number = 0): number {
  const subtotal = qty * price;
  const tax = subtotal * (taxRate / 100);
  return subtotal + tax;
}