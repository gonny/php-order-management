// Order filter types for listing and searching
export interface OrderFilters {
  page?: number;
  per_page?: number;
  search?: string;
  status?: string[];
  carrier?: string[];
  sort?: string;
  direction?: 'asc' | 'desc';
  date_from?: string;
  date_to?: string;
  client_id?: string;
  min_total?: number;
  max_total?: number;
}

export interface OrderSortOption {
  value: string;
  label: string;
}

export interface OrderStatusOption {
  value: string;
  label: string;
  color: string;
}

export interface OrderCarrierOption {
  value: string;
  label: string;
}

// Filter state for reactive UI
export interface OrderFilterState {
  isActive: boolean;
  hasSearch: boolean;
  hasDateRange: boolean;
  hasStatusFilter: boolean;
  hasCarrierFilter: boolean;
  activeCount: number;
}