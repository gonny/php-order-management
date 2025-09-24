import type {
  ApiError,
  ApiResponse,
  PaginatedResponse,
} from '../types/api';

import Spa  from '../actions/App/Http/Controllers/Spa';

// Import order types from the orders package
import type {
  Order,
  OrderCreateDTO,
  OrderTransition,
  OrderUpdateDTO,
} from '@/packages/orders/types/order';
import type { OrderFilters } from '@/packages/orders/types/filters';

// Temporary types - these will be moved to their respective packages later
type DashboardMetrics = any;
type Client = any;
type ClientCreateDTO = any;
type ClientFilters = any;
type ClientUpdateDTO = any;
type ShippingLabel = any;
type LabelCreateDTO = any;

// User authentication types
interface User {
  id: string;
  name: string;
  email: string;
}

// SPA API Configuration
interface SpaApiConfig {
  baseUrl: string;
  timeout?: number;
}

/**
 * Sanctum-based API Client for SPA (Single Page Application)
 * 
 * This client uses Laravel Sanctum for stateful session-based authentication.
 * It leverages the existing Laravel session authentication - no separate login needed.
 * Users authenticate via standard Laravel auth (/login), then this client automatically
 * works with the existing session via Sanctum's stateful authentication.
 */
export class SpaApiClient {
  private baseUrl: string;
  private timeout: number;
  private csrfInitialized = false;

  constructor(config: SpaApiConfig) {
    this.baseUrl = config.baseUrl.replace(/\/$/, ''); // Remove trailing slash
    this.timeout = config.timeout || 30000;
  }

  /**
   * Initialize CSRF protection by fetching CSRF cookie from Sanctum
   * This should be called when the app starts for proper session authentication
   */
  async initializeCsrf(): Promise<void> {
    const url = `${this.baseUrl}/sanctum/csrf-cookie`;
    await fetch(url, {
      method: 'GET',
      credentials: 'include', // Important to include session cookies
    });
  }

  /**
   * Get CSRF token from meta tag
   */
  private getCsrfToken(): string | null {
    const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    return metaToken || null;
  }

  /**
   * Make an authenticated request to the SPA API
   */
  private async request<T>(
    method: string,
    endpoint: string,
    data?: any,
    options: RequestInit = {}
  ): Promise<T> {
    // Initialize CSRF on first request if not already done
    if (!this.csrfInitialized) {
      await this.initializeCsrf();
      this.csrfInitialized = true;
    }

    const url = `${this.baseUrl}${endpoint}`;
    const body = data ? JSON.stringify(data) : undefined;
    
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest', // Important for Laravel to recognize as AJAX
    };

    // Add CSRF token from meta tag
    const csrfToken = this.getCsrfToken();
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
        credentials: 'include', // Important for session-based auth
        ...options,
      });

      clearTimeout(timeoutId);

      if (!response.ok) {
        const errorData: ApiError = await response.json().catch(() => ({
          error: 'Network Error',
          message: `HTTP ${response.status}: ${response.statusText}`,
        }));
        throw new SpaApiClientError(errorData, response.status);
      }

      // Handle 204 No Content responses
      if (response.status === 204) {
        return undefined as T;
      }

      return await response.json();
    } catch (error) {
      clearTimeout(timeoutId);
      
      if (error instanceof SpaApiClientError) {
        throw error;
      }
      
      if (error instanceof Error && error.name === 'AbortError') {
        throw new SpaApiClientError({ error: 'Request Timeout' }, 408);
      }
      
      throw new SpaApiClientError({ 
        error: 'Network Error',
        message: error instanceof Error ? error.message : 'Unknown error'
      }, 0);
    }
  }

  // User info methods

  /**
   * Get current authenticated user (uses existing session)
   */
  async getUser(): Promise<User> {
    const response = await this.request<{ user: User }>(
      'GET',
      '/spa/v1/auth/user'
    );
    return response.user;
  }

  // Dashboard

  async getDashboardMetrics(): Promise<DashboardMetrics> {
    const response = await this.request<ApiResponse<DashboardMetrics>>(
      'GET',
      '/spa/v1/dashboard/metrics'
    );
    return response.data;
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
    const endpoint = `/spa/v1/orders${queryString ? `?${queryString}` : ''}`;
    
    return await this.request<PaginatedResponse<Order>>('GET', endpoint);
  }

  async getOrder(id: string): Promise<Order> {
    const response = await this.request<ApiResponse<Order>>(
      Spa.OrderController.show(id).method, 
      Spa.OrderController.show(id).url);
    return response.data;
  }

  async createOrder(data: OrderCreateDTO): Promise<Order> {
    const response = await this.request<ApiResponse<Order>>(
      'POST',
      '/spa/v1/orders',
      data
    );
    return response.data;
  }

  async updateOrder(id: string, data: OrderUpdateDTO): Promise<Order> {
    const response = await this.request<ApiResponse<Order>>(
      'PATCH',
      `/spa/v1/orders/${id}`,
      data
    );
    return response.data;
  }

  async deleteOrder(id: string): Promise<void> {
    await this.request<void>('DELETE', `/spa/v1/orders/${id}`);
  }

  async transitionOrder(id: string, transition: OrderTransition): Promise<Order> {
    const response = await this.request<ApiResponse<Order>>(
      'POST',
      `/spa/v1/orders/${id}/transitions/${transition}`
    );
    return response.data;
  }

  // Shipping Labels

  async createLabel(orderId: string, data: LabelCreateDTO = {}): Promise<ShippingLabel> {
    const response = await this.request<ApiResponse<ShippingLabel>>(
      'POST',
      `/spa/v1/orders/${orderId}/label`,
      data
    );
    return response.data;
  }

  async voidLabel(labelId: string): Promise<boolean> {
    await this.request<void>('DELETE', `/spa/v1/labels/${labelId}`);
    return true;
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
    const endpoint = `/spa/v1/clients${queryString ? `?${queryString}` : ''}`;
    
    return await this.request<PaginatedResponse<Client>>('GET', endpoint);
  }

  async getClient(id: string): Promise<Client> {
    const response = await this.request<ApiResponse<Client>>(
      'GET',
      `/spa/v1/clients/${id}`
    );
    return response.data;
  }

  async createClient(data: ClientCreateDTO): Promise<Client> {
    const response = await this.request<ApiResponse<Client>>(
      'POST',
      '/spa/v1/clients',
      data
    );
    return response.data;
  }

  async updateClient(id: string, data: ClientUpdateDTO): Promise<Client> {
    const response = await this.request<ApiResponse<Client>>(
      'PATCH',
      `/spa/v1/clients/${id}`,
      data
    );
    return response.data;
  }

  async deleteClient(id: string): Promise<void> {
    await this.request<void>('DELETE', `/spa/v1/clients/${id}`);
  }

  // Webhooks

  async getWebhooks(filters: any = {}): Promise<PaginatedResponse<any>> {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        params.append(key, value.toString());
      }
    });

    const queryString = params.toString();
    const endpoint = `/spa/v1/webhooks${queryString ? `?${queryString}` : ''}`;
    
    return await this.request<PaginatedResponse<any>>('GET', endpoint);
  }

  async getWebhook(id: string): Promise<any> {
    const response = await this.request<ApiResponse<any>>(
      'GET',
      `/spa/v1/webhooks/${id}`
    );
    return response.data;
  }

  async reprocessWebhook(id: string): Promise<any> {
    const response = await this.request<ApiResponse<any>>(
      'POST',
      `/spa/v1/webhooks/${id}/reprocess`
    );
    return response.data;
  }

  // Queues

  async getQueueStats(): Promise<any> {
    const response = await this.request<ApiResponse<any>>(
      'GET',
      '/spa/v1/queues/stats'
    );
    return response.data;
  }

  async getFailedJobs(filters: any = {}): Promise<PaginatedResponse<any>> {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        params.append(key, value.toString());
      }
    });

    const queryString = params.toString();
    const endpoint = `/spa/v1/queues/failed${queryString ? `?${queryString}` : ''}`;
    
    return await this.request<PaginatedResponse<any>>('GET', endpoint);
  }

  async getRecentJobs(): Promise<any[]> {
    const response = await this.request<ApiResponse<any[]>>(
      'GET',
      '/spa/v1/queues/recent'
    );
    return response.data;
  }

  async retryFailedJob(jobId: string): Promise<void> {
    await this.request<void>('POST', `/spa/v1/queues/failed/${jobId}/retry`);
  }

  async deleteFailedJob(jobId: string): Promise<void> {
    await this.request<void>('DELETE', `/spa/v1/queues/failed/${jobId}`);
  }

  // Audit Logs

  async getAuditLogs(filters: any = {}): Promise<PaginatedResponse<any>> {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        params.append(key, value.toString());
      }
    });

    const queryString = params.toString();
    const endpoint = `/spa/v1/audit-logs${queryString ? `?${queryString}` : ''}`;
    
    return await this.request<PaginatedResponse<any>>('GET', endpoint);
  }

  async getAuditLogStats(): Promise<any> {
    const response = await this.request<ApiResponse<any>>(
      'GET',
      '/spa/v1/audit-logs/stats'
    );
    return response.data;
  }

  async getOrderAuditLogs(orderId: string): Promise<any[]> {
    const response = await this.request<ApiResponse<any[]>>(
      'GET',
      `/spa/v1/orders/${orderId}/audit-logs`
    );
    return response.data;
  }

  // Generic HTTP methods for flexibility
  async get<T = any>(endpoint: string): Promise<T> {
    return this.request<T>('GET', endpoint);
  }

  async post<T = any>(endpoint: string, data?: any): Promise<T> {
    return this.request<T>('POST', endpoint, data);
  }

  async put<T = any>(endpoint: string, data?: any): Promise<T> {
    return this.request<T>('PUT', endpoint, data);
  }

  async patch<T = any>(endpoint: string, data?: any): Promise<T> {
    return this.request<T>('PATCH', endpoint, data);
  }

  async delete<T = any>(endpoint: string): Promise<T> {
    return this.request<T>('DELETE', endpoint);
  }
}

// Custom Error class for SPA API
export class SpaApiClientError extends Error {
  constructor(
    public apiError: ApiError,
    public status: number
  ) {
    super(apiError.message || apiError.error);
    this.name = 'SpaApiClientError';
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

// Default SPA API client instance
export const spaApiClient = new SpaApiClient({
  baseUrl: window.location.origin,
  timeout: 30000,
});

// Helper function to initialize the SPA API client
// Call this when your app starts to ensure CSRF protection is set up
export async function initializeSpaApi(): Promise<void> {
  await spaApiClient.initializeCsrf();
}

// Helper function to handle SPA API errors
export function handleSpaApiError(error: unknown): string {
  if (error instanceof SpaApiClientError) {
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