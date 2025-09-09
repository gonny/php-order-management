// Core API Types based on Laravel backend
export type OrderStatus = 
  | 'new' 
  | 'confirmed' 
  | 'paid' 
  | 'fulfilled' 
  | 'completed' 
  | 'cancelled' 
  | 'on_hold' 
  | 'failed';

export type Carrier = 'balikovna' | 'dpd';

export type Currency = 'EUR' | 'CZK' | 'USD';

export type AddressType = 'shipping' | 'billing';

export type WebhookSource = 'balikovna' | 'dpd' | 'payments' | 'custom';

export type WebhookStatus = 'pending' | 'processing' | 'completed' | 'failed';

// Base entity interface
export interface BaseEntity {
  id: string;
  created_at: string;
  updated_at: string;
}

// Client interfaces
export interface Client extends BaseEntity {
  external_id?: string;
  email: string;
  phone?: string;
  first_name: string;
  last_name: string;
  company?: string;
  vat_id?: string;
  is_active?: boolean;
  meta?: Record<string, any>;
  orders?: Order[];
  addresses?: Address[];
}

export interface ClientCreateDTO {
  external_id?: string;
  email: string;
  phone?: string;
  first_name: string;
  last_name: string;
  company?: string;
  vat_id?: string;
  is_active?: boolean;
  meta?: Record<string, any>;
}

// eslint-disable-next-line @typescript-eslint/no-empty-object-type
export interface ClientUpdateDTO extends Partial<ClientCreateDTO> {
  // Explicitly extends ClientCreateDTO with all fields optional
}

// Address interfaces
export interface Address extends BaseEntity {
  client_id: string;
  type: AddressType;
  name: string;
  company?: string;
  street1: string;
  street2?: string;
  city: string;
  state?: string;
  postal_code: string;
  country_code: string;
  phone?: string;
  is_default: boolean;
}

export interface AddressCreateDTO {
  type: AddressType;
  name: string;
  company?: string;
  street1: string;
  street2?: string;
  city: string;
  state?: string;
  postal_code: string;
  country_code: string;
  phone?: string;
  is_default?: boolean;
}

// Order interfaces
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
  sku: string;
  name: string;
  qty: number;
  price: number;
  tax_rate: number;
}

export interface Order extends BaseEntity {
  number: string;
  pmi_id?: string;
  status: OrderStatus;
  client_id: string;
  client?: Client;
  shipping_address_id: string;
  billing_address_id?: string;
  shipping_address?: Address;
  billing_address?: Address;
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
  shipping_labels?: ShippingLabel[];
  audit_logs?: AuditLog[];
  webhooks?: Webhook[];
}

export interface OrderCreateDTO {
  client?: ClientCreateDTO;
  client_id?: string;
  shipping_address: AddressCreateDTO;
  billing_address?: AddressCreateDTO;
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

// Shipping Label interfaces
export interface ShippingLabel extends BaseEntity {
  order_id: string;
  carrier: Carrier;
  label_id: string;
  tracking_number: string;
  label_url?: string;
  status: 'pending' | 'generated' | 'printed' | 'voided';
  carrier_data?: Record<string, any>;
  carrier_shipment_id?: string;   // Used in: orders/Show.svelte:632
  meta?: Record<string, any>;     // Used in: orders/Show.svelte:637
  file_path?: string;             // Used in: orders/Show.svelte:740
}

export interface LabelCreateDTO {
  carrier_specific_data?: Record<string, any>;
}

// Webhook interfaces
export interface Webhook extends BaseEntity {
  source: WebhookSource;
  event_type: string;
  status: WebhookStatus;
  payload: Record<string, any>;
  processed_at?: string;
  error_message?: string;
  retry_count: number;
  related_order_id?: string;
}

// Audit Log interfaces
export interface AuditLog extends BaseEntity {
  user_id?: string;
  order_id?: string;
  action: string;
  old_values?: Record<string, any>;
  new_values?: Record<string, any>;
  ip_address?: string;
  user_agent?: string;
}

// API Client interfaces
export interface ApiClient extends BaseEntity {
  name: string;
  key_id: string;
  secret_hash: string;
  is_active: boolean;
  allowed_ips?: string[];
  rate_limit: number;
  last_used_at?: string;
}

// API Response interfaces
export interface PaginationMeta {
  current_page: number;
  from: number;
  last_page: number;
  per_page: number;
  to: number;
  total: number;
}

export interface PaginationLinks {
  first: string;
  last: string;
  prev?: string;
  next?: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: PaginationMeta;
  links: PaginationLinks;
  // Laravel pagination properties (for direct access)
  current_page: number;
  from: number;
  last_page: number;
  per_page: number;
  to: number;
  total: number;
}

export interface ApiResponse<T> {
  data: T;
  message?: string;
}

export interface ApiError {
  error: string;
  message?: string;
  errors?: Record<string, string[]>;
}

// Filter interfaces
export interface OrderFilters {
  status?: OrderStatus[];
  carrier?: Carrier[];
  pmi_id?: string;
  number?: string;
  client_id?: string;
  date_from?: string;
  date_to?: string;
  search?: string;
  page?: number;
  per_page?: number;
  sort?: string;
  direction?: 'asc' | 'desc';
}

export interface ClientFilters {
  search?: string;
  email?: string;
  external_id?: string;
  page?: number;
  per_page?: number;
  sort?: string;
  direction?: 'asc' | 'desc';
}

export interface WebhookFilters {
  source?: string;
  status?: string;
  event_type?: string;
  date_from?: string;
  date_to?: string;
  related_order_id?: string;
  page?: number;
  per_page?: number;
  sort?: string;
  direction?: 'asc' | 'desc';
}

// State transition types
export type OrderTransition = 
  | 'confirm'
  | 'pay'
  | 'fulfill'
  | 'complete'
  | 'cancel'
  | 'hold'
  | 'fail'
  | 'restart';

// Dashboard metrics
export interface DashboardMetrics {
  order_counts: Record<OrderStatus, number>;
  total_revenue: number;
  recent_orders: Order[];
  failed_jobs_count: number;
  api_response_time_p95: number;
  queue_sizes: Record<string, number>;
  recent_activities: AuditLog[];
}

// Settings interfaces
export interface CarrierConfig {
  carrier: Carrier;
  is_sandbox: boolean;
  api_endpoint: string;
  credentials: Record<string, string>;
}

export interface SystemSettings {
  carriers: CarrierConfig[];
  api_clients: ApiClient[];
  allowed_ips: string[];
  rate_limits: Record<string, number>;
}