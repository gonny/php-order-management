export type AddressType = 'billing' | 'shipping';

export interface BillingAddress extends Address {
  type: 'billing' 
}

export interface ShippingAddress extends Address {
  type: 'shipping' 
}
export interface Address {
    // columns
    id: string;
    type: AddressType;
    client_id: string | null;
    name: string;
    street1: string;
    street2: string | null;
    city: string;
    postal_code: string;
    country_code: string;
    state: string | null;
    company: string | null;
    phone: string | null;
    email: string | null;
    created_at: string | null;
    updated_at: string | null;
    // mutators
    full_address: string;
    // relations
    client: Client;
    // counts
    // exists
    client_exists: boolean;
}

export interface Client {
    // columns
    id: string;
    external_id: string;
    email: string;
    phone: string | null;
    first_name: string;
    last_name: string;
    company: string | null;
    vat_id: string | null;
    meta: string[] | null;
    created_at: string | null;
    updated_at: string | null;
    is_active: boolean;
    // mutators
    full_name: string;
    // relations
    addresses: Address[];
    orders: Order[];
    shipping_addresses: Address[];
    billing_addresses: Address[];
    // counts
    addresses_count: number;
    orders_count: number;
    shipping_addresses_count: number;
    billing_addresses_count: number;
    // exists
    addresses_exists: boolean;
    orders_exists: boolean;
    shipping_addresses_exists: boolean;
    billing_addresses_exists: boolean;
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

export interface ClientUpdateDTO {
  external_id?: string;
  email?: string;
  phone?: string;
  first_name?: string;
  last_name?: string;
  company?: string;
  vat_id?: string;
  is_active?: boolean;
  meta?: Record<string, any>;
}

export interface ClientFilters {
  page?: number;
  per_page?: number;
  search?: string;
  sort?: string;
  direction?: 'asc' | 'desc';
  is_active?: boolean;
}

export interface ClientResponse {
  data: Client[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

// Order interface for client orders
interface Order {
  id: string;
  order_number: string;
  status: string;
  total_amount: number;
  created_at: string;
}