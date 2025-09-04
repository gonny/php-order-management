import type { DpdTrackingStatus } from '../types';

// DPD-specific utility functions
export function getDpdStatusColor(status: DpdTrackingStatus): string {
  const statusColorMap: Record<DpdTrackingStatus, string> = {
    created: 'warning',
    picked_up: 'default',
    in_transit: 'default',
    out_for_delivery: 'warning',
    delivered: 'success',
    failed_delivery: 'destructive',
    returned: 'secondary',
  };
  
  return statusColorMap[status];
}

export function getDpdStatusLabel(status: DpdTrackingStatus): string {
  const statusLabelMap: Record<DpdTrackingStatus, string> = {
    created: 'Created',
    picked_up: 'Picked Up',
    in_transit: 'In Transit',
    out_for_delivery: 'Out for Delivery',
    delivered: 'Delivered',
    failed_delivery: 'Failed Delivery',
    returned: 'Returned',
  };
  
  return statusLabelMap[status];
}

export function isDpdShipmentActive(status: DpdTrackingStatus): boolean {
  return !['delivered', 'failed_delivery', 'returned'].includes(status);
}

export function formatDpdShipmentId(shipmentId: string): string {
  return `DPD-${shipmentId.slice(-8).toUpperCase()}`;
}

export function validatePickupPointRequired(shippingMethod: string, pickupPointId?: string): boolean {
  if (shippingMethod === 'DPD_PickupPoint') {
    return !!pickupPointId;
  }
  return true;
}