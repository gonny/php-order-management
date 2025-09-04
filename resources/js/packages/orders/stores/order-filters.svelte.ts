import type { OrderFilters, OrderFilterState } from '../types/filters';

/**
 * Reactive store for order filters using Svelte 5 runes
 */

// Default filter values
const defaultFilters: OrderFilters = {
  page: 1,
  per_page: 20,
  search: '',
  status: [],
  carrier: [],
  sort: 'created_at',
  direction: 'desc',
};

// Create reactive filter state
export function createOrderFiltersStore(initialFilters: Partial<OrderFilters> = {}) {
  let filters = $state<OrderFilters>({
    ...defaultFilters,
    ...initialFilters,
  });

  // Computed filter state
  const filterState = $derived<OrderFilterState>({
    isActive: hasActiveFilters(filters),
    hasSearch: Boolean(filters.search?.trim()),
    hasDateRange: Boolean(filters.date_from || filters.date_to),
    hasStatusFilter: Boolean(filters.status?.length),
    hasCarrierFilter: Boolean(filters.carrier?.length),
    activeCount: getActiveFilterCount(filters),
  });

  return {
    // Reactive getters
    get filters() { return filters; },
    get filterState() { return filterState; },
    
    // Actions
    updateFilters(newFilters: Partial<OrderFilters>) {
      filters = { ...filters, ...newFilters };
    },

    setSearch(search: string) {
      filters = { ...filters, search, page: 1 };
    },

    setStatus(status: string[]) {
      filters = { ...filters, status, page: 1 };
    },

    setCarrier(carrier: string[]) {
      filters = { ...filters, carrier, page: 1 };
    },

    setSort(sort: string, direction: 'asc' | 'desc' = 'desc') {
      filters = { ...filters, sort, direction, page: 1 };
    },

    setDateRange(date_from?: string, date_to?: string) {
      filters = { ...filters, date_from, date_to, page: 1 };
    },

    setPagination(page: number, per_page?: number) {
      filters = { ...filters, page, ...(per_page && { per_page }) };
    },

    clearFilters() {
      filters = { ...defaultFilters };
    },

    clearSearch() {
      filters = { ...filters, search: '', page: 1 };
    },

    clearStatus() {
      filters = { ...filters, status: [], page: 1 };
    },

    clearCarrier() {
      filters = { ...filters, carrier: [], page: 1 };
    },

    clearDateRange() {
      filters = { ...filters, date_from: undefined, date_to: undefined, page: 1 };
    },

    reset() {
      filters = { ...defaultFilters };
    },
  };
}

// Helper functions
function hasActiveFilters(filters: OrderFilters): boolean {
  return !!(
    filters.search?.trim() ||
    filters.status?.length ||
    filters.carrier?.length ||
    filters.date_from ||
    filters.date_to ||
    filters.min_total ||
    filters.max_total ||
    filters.client_id
  );
}

function getActiveFilterCount(filters: OrderFilters): number {
  let count = 0;
  
  if (filters.search?.trim()) count++;
  if (filters.status?.length) count++;
  if (filters.carrier?.length) count++;
  if (filters.date_from || filters.date_to) count++;
  if (filters.min_total) count++;
  if (filters.max_total) count++;
  if (filters.client_id) count++;
  
  return count;
}

// Export type for the store
export type OrderFiltersStore = ReturnType<typeof createOrderFiltersStore>;