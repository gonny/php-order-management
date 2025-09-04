// DPD-specific API types
export interface DpdShipmentCreateRequest {
  shipping_method: 'DPD_Home' | 'DPD_PickupPoint';
  pickup_point_id?: string;
  recipient_address?: {
    name: string;
    company?: string;
    address_line_1: string;
    address_line_2?: string;
    city: string;
    postal_code: string;
    country: string;
    phone?: string;
    email?: string;
  };
}

export interface DpdShipmentResponse {
  shipment_id: string;
  tracking_number?: string;
  label_url?: string;
  status: 'created' | 'shipped' | 'delivered' | 'error';
  error_message?: string;
}

export interface DpdTrackingInfo {
  shipment_id: string;
  tracking_number: string;
  status: DpdTrackingStatus;
  events: DpdTrackingEvent[];
  estimated_delivery?: string;
  pickup_point?: DpdPickupPoint;
}

export type DpdTrackingStatus = 
  | 'created'
  | 'picked_up'
  | 'in_transit'
  | 'out_for_delivery'
  | 'delivered'
  | 'failed_delivery'
  | 'returned';

export interface DpdTrackingEvent {
  timestamp: string;
  status: DpdTrackingStatus;
  location?: string;
  description: string;
}

export interface DpdPickupPoint {
  id: string;
  name: string;
  address: {
    street: string;
    city: string;
    postal_code: string;
    country: string;
  };
  opening_hours?: Array<{
    day: string;
    hours: string;
  }>;
  phone?: string;
  coordinates?: {
    latitude: number;
    longitude: number;
  };
}