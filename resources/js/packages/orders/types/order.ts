// Order-specific types extracted from shared types
export type OrderStatus = 
  | 'new' 
  | 'confirmed' 
  | 'paid' 
  | 'fulfilled' 
  | 'completed' 
  | 'cancelled' 
  | 'on_hold' 
  | 'failed';

export type OrderTransition = 
  | 'confirm' 
  | 'pay' 
  | 'fulfill' 
  | 'complete' 
  | 'cancel' 
  | 'hold' 
  | 'fail' 
  | 'restart';

export type Carrier = 'balikovna' | 'dpd';
export type Currency = 'EUR' | 'CZK' | 'USD';

// Base entity interface - shared across packages
export interface BaseEntity {
  id: string;
  created_at: string;
  updated_at: string;
}

// Order-specific interfaces
export interface OrderItem extends BaseEntity {
  order_id: string;
  sku: string;
  name: string;
  qty: number;
  price: number;
  tax_rate: number;
  total: number;
}

export interface OrderItemCreateDTO {
  sku?: string;
  name: string;
  description?: string;
  qty: number;
  quantity?: number; // For form compatibility
  price: number;
  unit_price?: number; // For form compatibility
  total_price?: number; // For form compatibility  
  tax_rate?: number;
}

// Form-specific interface for better UX
export interface OrderItemForm {
  id?: string;
  name: string;
  description: string;
  quantity: number;
  unit_price: number;
  total_price: number;
}

export interface Order extends BaseEntity {
  number: string;
  pmi_id?: string;
  status: OrderStatus;
  client_id: string;
  client?: any; // Will be properly typed when client package is created
  shipping_address_id: string;
  billing_address_id?: string;
  shipping_address?: any; // Will be properly typed when shared address types are created
  billing_address?: any;
  carrier: Carrier;
  currency: Currency;
  subtotal: number;
  tax_total: number;
  total: number;
  total_amount: number;           // Used in: orders/Show.svelte:412
  shipping_method?: string;       // Used in: orders/Show.svelte:394
  pickup_point_id?: string;       // Used in: orders/Show.svelte:400
  dpd_shipment_id?: string;       // Used in: orders/Show.svelte:414
  tracking_number?: string;       // Used in multiple places
  parcel_group_id?: string;       // Used in: orders/Show.svelte:624
  pdf_label_path?: string;        // Used in: orders/Show.svelte:660
  notes?: string;
  meta?: Record<string, any>;
  items?: OrderItem[];
  shipping_labels?: any[];        // Will be properly typed when shipping package is created
  audit_logs?: any[];             // Will be properly typed when shared types are created
  webhooks?: any[];               // Will be properly typed when shared types are created
}

export interface OrderCreateDTO {
  client?: any;                   // Will be properly typed when client types are available
  client_id?: string;
  shipping_address: any;          // Will be properly typed when address types are available
  billing_address?: any;
  items: OrderItemCreateDTO[];
  carrier: Carrier;
  currency: Currency;
  notes?: string;
  meta?: Record<string, any>;
}

export interface OrderUpdateDTO {
  status?: OrderStatus;
  notes?: string;
  meta?: Record<string, any>;
}