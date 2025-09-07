import type {
  ApiError,
  ApiResponse,
  Client,
  ClientCreateDTO,
  ClientFilters,
  ClientUpdateDTO,
  DashboardMetrics,
  LabelCreateDTO,
  Order,
  OrderCreateDTO,
  OrderFilters,
  OrderTransition,
  OrderUpdateDTO,
  PaginatedResponse,
  ShippingLabel,
  Webhook,
  WebhookFilters,
} from '@/types';

// API Configuration
interface ApiConfig {
  baseUrl: string;
  keyId?: string;
  secret?: string;
  timeout?: number;
}

// HMAC Authentication utilities
class HMACAuth {
  constructor(private keyId: string, private secret: string) {}

  private async generateBodyHash(body: string): Promise<string> {
    const encoder = new TextEncoder();
    const data = encoder.encode(body);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    return btoa(String.fromCharCode(...new Uint8Array(hashBuffer)));
  }

  private async generateSignature(
    method: string,
    path: string,
    timestamp: number,
    digest: string
  ): Promise<string> {
    // Match backend format: method\npath\ntimestamp\ndigest
    const stringToSign = [method, path, timestamp.toString(), digest].join('\n');
    const encoder = new TextEncoder();
    const keyData = encoder.encode(this.secret);
    const messageData = encoder.encode(stringToSign);
    
    const cryptoKey = await crypto.subtle.importKey(
      'raw',
      keyData,
      { name: 'HMAC', hash: 'SHA-256' },
      false,
      ['sign']
    );
    
    const signature = await crypto.subtle.sign('HMAC', cryptoKey, messageData);
    return btoa(String.fromCharCode(...new Uint8Array(signature)));
  }

  async generateHeaders(
    method: string,
    path: string,
    body: string = ''
  ): Promise<Record<string, string>> {
    const timestamp = Math.floor(Date.now() / 1000);
    const bodyHash = await this.generateBodyHash(body);
    const digest = `SHA-256=${bodyHash}`;
    const signature = await this.generateSignature(method, path, timestamp, digest);

    return {
      'X-Key-Id': this.keyId,
      'X-Signature': signature,
      'X-Timestamp': timestamp.toString(),
      'Digest': digest,
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
  }
}

// Main API Client class
export class ApiClient {
  private baseUrl: string;
  private auth?: HMACAuth;
  private timeout: number;

  constructor(config: ApiConfig) {
    this.baseUrl = config.baseUrl.replace(/\/$/, ''); // Remove trailing slash
    this.timeout = config.timeout || 30000;
    
    if (config.keyId && config.secret) {
      this.auth = new HMACAuth(config.keyId, config.secret);
    }
  }

  private async request<T>(
    method: string,
    endpoint: string,
    data?: any,
    options: RequestInit = {}
  ): Promise<T> {
    const url = `${this.baseUrl}${endpoint}`;
    const body = data ? JSON.stringify(data) : '';
    
    let headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    // Add HMAC authentication if configured
    if (this.auth) {
      const authHeaders = await this.auth.generateHeaders(
        method,
        endpoint,
        body
      );
      headers = { ...headers, ...authHeaders };
    }

    // Add CSRF token for Laravel
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
        body: body || options.body,
        signal: controller.signal,
        ...options,
      });

      clearTimeout(timeoutId);

      if (!response.ok) {
        const errorData: ApiError = await response.json().catch(() => ({
          error: 'Network Error',
          message: `HTTP ${response.status}: ${response.statusText}`,
        }));
        throw new ApiClientError(errorData, response.status);
      }

      return await response.json();
    } catch (error) {
      clearTimeout(timeoutId);
      
      if (error instanceof ApiClientError) {
        throw error;
      }
      
      if (error instanceof Error && error.name === 'AbortError') {
        throw new ApiClientError({ error: 'Request Timeout' }, 408);
      }
      
      throw new ApiClientError({ 
        error: 'Network Error',
        message: error instanceof Error ? error.message : 'Unknown error'
      }, 0);
    }
  }

  // Dashboard
  async getDashboardMetrics(): Promise<DashboardMetrics> {
    const response = await this.request<ApiResponse<DashboardMetrics>>(
      'GET',
      '/api/v1/dashboard/metrics'
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
    const endpoint = `/api/v1/orders${queryString ? `?${queryString}` : ''}`;
    
    return await this.request<PaginatedResponse<Order>>('GET', endpoint);
  }

  async getOrder(id: string): Promise<Order> {
    const response = await this.request<ApiResponse<Order>>(
      'GET',
      `/api/v1/orders/${id}`
    );
    return response.data;
  }

  async createOrder(data: OrderCreateDTO): Promise<Order> {
    const response = await this.request<ApiResponse<Order>>(
      'POST',
      '/api/v1/orders',
      data
    );
    return response.data;
  }

  async updateOrder(id: string, data: OrderUpdateDTO): Promise<Order> {
    const response = await this.request<ApiResponse<Order>>(
      'PATCH',
      `/api/v1/orders/${id}`,
      data
    );
    return response.data;
  }

  async deleteOrder(id: string): Promise<void> {
    await this.request<void>('DELETE', `/api/v1/orders/${id}`);
  }

  async transitionOrder(id: string, transition: OrderTransition): Promise<Order> {
    const response = await this.request<ApiResponse<Order>>(
      'POST',
      `/api/v1/orders/${id}/transitions/${transition}`
    );
    return response.data;
  }

  // Shipping Labels
  async createLabel(orderId: string, data: LabelCreateDTO = {}): Promise<ShippingLabel> {
    const response = await this.request<ApiResponse<ShippingLabel>>(
      'POST',
      `/api/v1/orders/${orderId}/labels`,
      data
    );
    return response.data;
  }

  async voidLabel(labelId: string): Promise<boolean> {
    await this.request<void>('DELETE', `/api/v1/labels/${labelId}`);
    return true;
  }

  async downloadLabel(labelId: string): Promise<Blob> {
    const response = await fetch(`${this.baseUrl}/api/v1/labels/${labelId}/download`, {
      method: 'GET',
      headers: await this.auth?.generateHeaders('GET', `/api/v1/labels/${labelId}/download`) || {}
    });

    if (!response.ok) {
      throw new ApiClientError({ error: 'Download failed' }, response.status);
    }

    return await response.blob();
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
    const endpoint = `/api/v1/clients${queryString ? `?${queryString}` : ''}`;
    
    return await this.request<PaginatedResponse<Client>>('GET', endpoint);
  }

  async getClient(id: string): Promise<Client> {
    const response = await this.request<ApiResponse<Client>>(
      'GET',
      `/api/v1/clients/${id}`
    );
    return response.data;
  }

  async createClient(data: ClientCreateDTO): Promise<Client> {
    const response = await this.request<ApiResponse<Client>>(
      'POST',
      '/api/v1/clients',
      data
    );
    return response.data;
  }

  async updateClient(id: string, data: ClientUpdateDTO): Promise<Client> {
    const response = await this.request<ApiResponse<Client>>(
      'PATCH',
      `/api/v1/clients/${id}`,
      data
    );
    return response.data;
  }

  async deleteClient(id: string): Promise<void> {
    await this.request<void>('DELETE', `/api/v1/clients/${id}`);
  }

  // Webhooks
  async getWebhooks(filters: WebhookFilters = {}): Promise<PaginatedResponse<Webhook>> {
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
    const endpoint = `/api/v1/webhooks${queryString ? `?${queryString}` : ''}`;
    
    return await this.request<PaginatedResponse<Webhook>>('GET', endpoint);
  }

  async getWebhook(id: string): Promise<Webhook> {
    const response = await this.request<ApiResponse<Webhook>>(
      'GET',
      `/api/v1/webhooks/${id}`
    );
    return response.data;
  }

  async reprocessWebhook(id: string): Promise<Webhook> {
    const response = await this.request<ApiResponse<Webhook>>(
      'POST',
      `/api/v1/webhooks/${id}/reprocess`
    );
    return response.data;
  }
}

// Custom Error class
export class ApiClientError extends Error {
  constructor(
    public apiError: ApiError,
    public status: number
  ) {
    super(apiError.message || apiError.error);
    this.name = 'ApiClientError';
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

// Default API client instance
export const apiClient = new ApiClient({
  baseUrl: window.location.origin,
  timeout: 30000,
});

// Helper function to handle API errors in components
export function handleApiError(error: unknown): string {
  if (error instanceof ApiClientError) {
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