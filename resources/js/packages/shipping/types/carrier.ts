// Shipping carrier types
export type Carrier = 'balikovna' | 'dpd';

export interface CarrierInfo {
  id: Carrier;
  name: string;
  displayName: string;
  supportedServices: CarrierService[];
  requiresPickupPoint?: boolean;
}

export interface CarrierService {
  id: string;
  name: string;
  description?: string;
  requiresPickupPoint: boolean;
}

// Standard carrier services
export const CARRIER_SERVICES = {
  DPD: {
    HOME: 'DPD_Home',
    PICKUP_POINT: 'DPD_PickupPoint',
  },
  BALIKOVNA: {
    PICKUP_POINT: 'Balikovna_PickupPoint',
  },
} as const;

export type CarrierServiceType = typeof CARRIER_SERVICES[keyof typeof CARRIER_SERVICES][keyof typeof CARRIER_SERVICES[keyof typeof CARRIER_SERVICES]];