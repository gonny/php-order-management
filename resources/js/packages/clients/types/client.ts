export interface Client {
  id: string;
  external_id?: string;
  email: string;
  phone?: string;
  first_name: string;
  last_name: string;
  company?: string;
  vat_id?: string;
  is_active: boolean;
  meta?: Record<string, any>;
  created_at: string;
  updated_at: string;
  orders_count?: number;
  total_spent?: number;
  last_order_date?: string;
  orders?: Order[];
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