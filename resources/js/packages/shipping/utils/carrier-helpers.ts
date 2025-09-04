import type { Carrier, CarrierInfo } from '../types';
import { CARRIER_SERVICES } from '../types';

// Carrier management utilities
export function getCarrierInfo(carrier: Carrier): CarrierInfo {
  const carrierMap: Record<Carrier, CarrierInfo> = {
    dpd: {
      id: 'dpd',
      name: 'dpd',
      displayName: 'DPD',
      supportedServices: [
        {
          id: CARRIER_SERVICES.DPD.HOME,
          name: 'Home Delivery',
          description: 'Direct delivery to customer address',
          requiresPickupPoint: false,
        },
        {
          id: CARRIER_SERVICES.DPD.PICKUP_POINT,
          name: 'Pickup Point',
          description: 'Delivery to DPD pickup point',
          requiresPickupPoint: true,
        },
      ],
      requiresPickupPoint: false,
    },
    balikovna: {
      id: 'balikovna',
      name: 'balikovna',
      displayName: 'Balíkovna',
      supportedServices: [
        {
          id: CARRIER_SERVICES.BALIKOVNA.PICKUP_POINT,
          name: 'Pickup Point',
          description: 'Delivery to Balíkovna pickup point',
          requiresPickupPoint: true,
        },
      ],
      requiresPickupPoint: true,
    },
  };

  return carrierMap[carrier];
}

export function getAvailableCarriers(): CarrierInfo[] {
  return [
    getCarrierInfo('dpd'),
    getCarrierInfo('balikovna'),
  ];
}

export function isPickupPointRequired(carrier: Carrier, serviceType?: string): boolean {
  const carrierInfo = getCarrierInfo(carrier);
  
  if (serviceType) {
    const service = carrierInfo.supportedServices.find(s => s.id === serviceType);
    return service?.requiresPickupPoint ?? false;
  }
  
  return carrierInfo.requiresPickupPoint ?? false;
}