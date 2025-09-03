import type { OrderStatus, OrderTransition } from '../types/order';

/**
 * Status transition logic and utilities
 */

// Valid transitions for each status
export const validTransitions: Record<OrderStatus, OrderTransition[]> = {
  new: ['confirm', 'cancel'],
  confirmed: ['pay', 'cancel', 'hold'],
  paid: ['fulfill', 'cancel', 'hold'],
  fulfilled: ['complete', 'hold'],
  completed: [],
  cancelled: ['restart'],
  on_hold: ['restart', 'cancel'],
  failed: ['restart', 'cancel'],
};

// Transition labels for UI
export const transitionLabels: Record<OrderTransition, string> = {
  confirm: 'Confirm Order',
  pay: 'Mark as Paid',
  fulfill: 'Mark as Fulfilled', 
  complete: 'Mark as Completed',
  cancel: 'Cancel Order',
  hold: 'Put on Hold',
  fail: 'Mark as Failed',
  restart: 'Restart Order',
};

// Get valid transitions for a status
export function getValidTransitions(status: OrderStatus): OrderTransition[] {
  return validTransitions[status] || [];
}

// Check if a transition is valid for a status
export function isValidTransition(status: OrderStatus, transition: OrderTransition): boolean {
  return getValidTransitions(status).includes(transition);
}

// Get the target status for a transition
export function getTargetStatus(transition: OrderTransition): OrderStatus {
  const targetMap: Record<OrderTransition, OrderStatus> = {
    confirm: 'confirmed',
    pay: 'paid',
    fulfill: 'fulfilled',
    complete: 'completed',
    cancel: 'cancelled',
    hold: 'on_hold',
    fail: 'failed',
    restart: 'new',
  };
  
  return targetMap[transition];
}

// Get transition label for UI display
export function getTransitionLabel(transition: OrderTransition): string {
  return transitionLabels[transition] || transition;
}

// Check if status requires confirmation
export function requiresConfirmation(transition: OrderTransition): boolean {
  return ['cancel', 'fail'].includes(transition);
}

// Get transition button variant
export function getTransitionVariant(transition: OrderTransition): 'default' | 'destructive' | 'secondary' {
  switch (transition) {
    case 'cancel':
    case 'fail':
      return 'destructive';
    case 'hold':
      return 'secondary';
    default:
      return 'default';
  }
}

// Check if transition changes workflow significantly
export function isSignificantTransition(transition: OrderTransition): boolean {
  return ['cancel', 'complete', 'fail'].includes(transition);
}