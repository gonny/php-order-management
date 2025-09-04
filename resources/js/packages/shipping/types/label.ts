import type { BaseEntity } from '@/packages/shared/types/api';
import type { Carrier } from './carrier';

// Shipping label types
export interface ShippingLabel extends BaseEntity {
  order_id: string;
  carrier: Carrier;
  carrier_shipment_id?: string;
  tracking_number?: string;
  label_url?: string;
  file_path?: string;
  status: LabelStatus;
  shipment_type?: string;
  pickup_point_id?: string;
  weight?: number;
  dimensions?: LabelDimensions;
  shipping_cost?: number;
  currency?: string;
  meta?: Record<string, any>;
  error_message?: string;
  voided_at?: string;
}

export type LabelStatus = 'pending' | 'generated' | 'printed' | 'voided' | 'error';

export interface LabelDimensions {
  length: number;
  width: number;
  height: number;
  unit: 'cm' | 'in';
}

export interface LabelCreateDTO {
  carrier?: Carrier;
  shipment_type?: string;
  pickup_point_id?: string;
  weight?: number;
  dimensions?: LabelDimensions;
  shipping_address?: ShippingAddress;
  return_address?: ShippingAddress;
  meta?: Record<string, any>;
}

export interface ShippingAddress {
  name: string;
  company?: string;
  address_line_1: string;
  address_line_2?: string;
  city: string;
  state?: string;
  postal_code: string;
  country: string;
  phone?: string;
  email?: string;
}