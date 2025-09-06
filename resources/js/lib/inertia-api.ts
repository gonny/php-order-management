import type {
  ApiError,
  ApiResponse,
  PaginatedResponse,
} from '../types/api';

// Import order types from the orders package
import type {
  Order,
  OrderFilters,
} from '@/packages/orders/types/order';

// Import client types
import type { Client, ClientFilters } from '@/types';

// Dashboard metrics type
interface DashboardMetrics {
  order_counts: Record<string, number>;
  total_revenue: number;
  failed_jobs_count: number;
  api_response_time_p95: number;
  queue_sizes: Record<string, number>;
  recent_orders: Array<{
    id: string;
    number: string;
    client_name: string;
    status: string;
    total: number;
    created_at: string;
  }>;
  recent_activities: Array<{
    id: string;
    action: string;
    created_at: string;
  }>;
}

/**
 * Session-authenticated API Client for Inertia.js components
 * 
 * This client uses traditional Laravel session authentication with CSRF protection.
 * It's designed for Inertia.js components that need to make AJAX requests for dynamic data
 * while staying within the same-server session-based authentication flow.
 */
export class InertiaApiClient {
  private baseUrl: string;
  private timeout: number;

  constructor(baseUrl: string = window.location.origin, timeout: number = 30000) {
    this.baseUrl = baseUrl.replace(/\/$/, ''); // Remove trailing slash
    this.timeout = timeout;
  }

  /**
   * Make a session-authenticated request
   */
  private async request<T>(
    method: string,
    endpoint: string,
    data?: any,
    options: RequestInit = {}
  ): Promise<T> {
    const url = `${this.baseUrl}${endpoint}`;
    const body = data ? JSON.stringify(data) : undefined;
    
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest', // Important for Laravel to recognize as AJAX
    };

    // Add CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
      headers['X-CSRF-TOKEN'] = csrfToken;
    }

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), this.timeout);

    try {
      const response = await fetch(url, {
        method,
        headers: { ...headers, ...options.headers },
        body,
        signal: controller.signal,
        credentials: 'same-origin', // Include session cookies
        ...options,
      });

      clearTimeout(timeoutId);

      if (!response.ok) {
        const errorData: ApiError = await response.json().catch(() => ({
          error: 'Network Error',
          message: `HTTP ${response.status}: ${response.statusText}`,
        }));
        throw new InertiaApiClientError(errorData, response.status);
      }

      // Handle 204 No Content responses
      if (response.status === 204) {
        return undefined as T;
      }

      return await response.json();
    } catch (error) {
      clearTimeout(timeoutId);
      
      if (error instanceof InertiaApiClientError) {
        throw error;
      }
      
      if (error instanceof Error && error.name === 'AbortError') {
        throw new InertiaApiClientError({ error: 'Request Timeout' }, 408);
      }
      
      throw new InertiaApiClientError({ 
        error: 'Network Error',
        message: error instanceof Error ? error.message : 'Unknown error'
      }, 0);
    }
  }

  // Dashboard

  async getDashboardMetrics(): Promise<DashboardMetrics> {
    return await this.request<DashboardMetrics>(
      'GET',
      '/inertia-api/dashboard/metrics'
    );
  }

  // Orders

  async getOrders(filters: OrderFilters = {}): Promise<PaginatedResponse<Order>> {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        if (Array.isArray(value)) {
          value.forEach(v => params.append(`${key}[]`, v.toString()));
        } else {
          params.append(key, value.toString());
        }
      }
    });

    const queryString = params.toString();
    const endpoint = `/inertia-api/orders${queryString ? `?${queryString}` : ''}`;
    
    return await this.request<PaginatedResponse<Order>>('GET', endpoint);
  }

  async getOrder(id: string): Promise<Order> {
    const response = await this.request<ApiResponse<Order>>(
      'GET',
      `/inertia-api/orders/${id}`
    );
    return response.data;
  }

  // Clients

  async getClients(filters: ClientFilters = {}): Promise<PaginatedResponse<Client>> {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        params.append(key, value.toString());
      }
    });

    const queryString = params.toString();
    const endpoint = `/inertia-api/clients${queryString ? `?${queryString}` : ''}`;
    
    return await this.request<PaginatedResponse<Client>>('GET', endpoint);
  }

  async getClient(id: string): Promise<Client> {
    const response = await this.request<ApiResponse<Client>>(
      'GET',
      `/inertia-api/clients/${id}`
    );
    return response.data;
  }
}

// Custom Error class for Inertia API
export class InertiaApiClientError extends Error {
  constructor(
    public apiError: ApiError,
    public status: number
  ) {
    super(apiError.message || apiError.error);
    this.name = 'InertiaApiClientError';
  }

  get isValidationError(): boolean {
    return this.status === 422;
  }

  get isNotFound(): boolean {
    return this.status === 404;
  }

  get isUnauthorized(): boolean {
    return this.status === 401;
  }

  get isForbidden(): boolean {
    return this.status === 403;
  }

  get isServerError(): boolean {
    return this.status >= 500;
  }

  get validationErrors(): Record<string, string[]> | undefined {
    return this.apiError.errors;
  }
}

// Default Inertia API client instance
export const inertiaApiClient = new InertiaApiClient();

// Helper function to handle Inertia API errors
export function handleInertiaApiError(error: unknown): string {
  if (error instanceof InertiaApiClientError) {
    if (error.isValidationError && error.validationErrors) {
      const firstError = Object.values(error.validationErrors)[0];
      return firstError?.[0] || error.message;
    }
    return error.message;
  }
  
  if (error instanceof Error) {
    return error.message;
  }
  
  return 'An unexpected error occurred';
}