import { describe, it, expect, vi, beforeEach } from 'vitest';
import { spaApiClient } from '@/lib/spa-api';
import type { Order, OrderCreateDTO, OrderUpdateDTO, PaginatedResponse } from '@/types';

// Mock the spaApiClient
vi.mock('@/lib/spa-api');

const mockOrder: Order = {
  id: '1',
  order_number: 'ORD-001',
  client_id: '1',
  status: 'new',
  currency: 'CZK',
  total_amount: 1000,
  items: [
    {
      id: '1',
      order_id: '1',
      sku: 'PROD-001',
      name: 'Test Product',
      qty: 2,
      price: 500,
      tax_rate: 0.21,
      total: 1000,
      created_at: '2024-01-01T00:00:00Z',
      updated_at: '2024-01-01T00:00:00Z',
    }
  ],
  shipping_address: {
    id: '1',
    client_id: '1',
    type: 'shipping',
    name: 'John Doe',
    street1: '123 Main St',
    city: 'Prague',
    postal_code: '10000',
    country_code: 'CZ',
    is_default: true,
    created_at: '2024-01-01T00:00:00Z',
    updated_at: '2024-01-01T00:00:00Z',
  },
  billing_address: {
    id: '2',
    client_id: '1',
    type: 'billing',
    name: 'John Doe',
    street1: '123 Main St',
    city: 'Prague',
    postal_code: '10000',
    country_code: 'CZ',
    is_default: true,
    created_at: '2024-01-01T00:00:00Z',
    updated_at: '2024-01-01T00:00:00Z',
  },
  created_at: '2024-01-01T00:00:00Z',
  updated_at: '2024-01-01T00:00:00Z',
};

const mockPaginatedResponse: PaginatedResponse<Order> = {
  data: [mockOrder],
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 1,
  from: 1,
  to: 1,
};

describe('Order CRUD Operations', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe('getOrders', () => {
    it('should fetch paginated orders list', async () => {
      vi.mocked(spaApiClient.getOrders).mockResolvedValue(mockPaginatedResponse);

      const result = await spaApiClient.getOrders();

      expect(spaApiClient.getOrders).toHaveBeenCalledOnce();
      expect(result).toEqual(mockPaginatedResponse);
    });

    it('should fetch orders with filters', async () => {
      const filters = { status: ['new'], client_id: '1', page: 1, per_page: 10 };
      vi.mocked(spaApiClient.getOrders).mockResolvedValue(mockPaginatedResponse);

      const result = await spaApiClient.getOrders(filters);

      expect(spaApiClient.getOrders).toHaveBeenCalledWith(filters);
      expect(result).toEqual(mockPaginatedResponse);
    });
  });

  describe('getOrder', () => {
    it('should fetch a single order by ID', async () => {
      vi.mocked(spaApiClient.getOrder).mockResolvedValue(mockOrder);

      const result = await spaApiClient.getOrder('1');

      expect(spaApiClient.getOrder).toHaveBeenCalledWith('1');
      expect(result).toEqual(mockOrder);
    });
  });

  describe('createOrder', () => {
    it('should create a new order', async () => {
      const createDTO: OrderCreateDTO = {
        client_id: '1',
        currency: 'CZK',
        items: [
          {
            sku: 'PROD-002',
            name: 'New Product',
            qty: 1,
            price: 750,
            tax_rate: 0.21,
          }
        ],
        shipping_address: {
          type: 'shipping',
          name: 'Jane Smith',
          street1: '456 Oak Ave',
          city: 'Brno',
          postal_code: '60000',
          country_code: 'CZ',
        },
      };

      const newOrder: Order = {
        ...mockOrder,
        id: '2',
        order_number: 'ORD-002',
        client_id: createDTO.client_id,
      };

      vi.mocked(spaApiClient.createOrder).mockResolvedValue(newOrder);

      const result = await spaApiClient.createOrder(createDTO);

      expect(spaApiClient.createOrder).toHaveBeenCalledWith(createDTO);
      expect(result).toEqual(newOrder);
    });

    it('should handle validation errors during order creation', async () => {
      const createDTO: OrderCreateDTO = {
        client_id: '',
        currency: 'CZK',
        items: [],
      };

      const error = new Error('Validation failed: client_id is required');
      vi.mocked(spaApiClient.createOrder).mockRejectedValue(error);

      await expect(spaApiClient.createOrder(createDTO)).rejects.toThrow('Validation failed');
      expect(spaApiClient.createOrder).toHaveBeenCalledWith(createDTO);
    });
  });

  describe('updateOrder', () => {
    it('should update an existing order', async () => {
      const updateDTO: OrderUpdateDTO = {
        status: 'confirmed',
        notes: 'Updated order notes',
      };

      const updatedOrder: Order = {
        ...mockOrder,
        ...updateDTO,
      };

      vi.mocked(spaApiClient.updateOrder).mockResolvedValue(updatedOrder);

      const result = await spaApiClient.updateOrder('1', updateDTO);

      expect(spaApiClient.updateOrder).toHaveBeenCalledWith('1', updateDTO);
      expect(result).toEqual(updatedOrder);
    });
  });

  describe('deleteOrder', () => {
    it('should delete an order', async () => {
      vi.mocked(spaApiClient.deleteOrder).mockResolvedValue(undefined);

      await spaApiClient.deleteOrder('1');

      expect(spaApiClient.deleteOrder).toHaveBeenCalledWith('1');
    });

    it('should handle errors during order deletion', async () => {
      const error = new Error('Order not found');
      vi.mocked(spaApiClient.deleteOrder).mockRejectedValue(error);

      await expect(spaApiClient.deleteOrder('999')).rejects.toThrow('Order not found');
      expect(spaApiClient.deleteOrder).toHaveBeenCalledWith('999');
    });
  });

  describe('transitionOrder', () => {
    it('should transition order status', async () => {
      const transition = 'confirm';
      const transitionedOrder: Order = {
        ...mockOrder,
        status: 'confirmed',
      };

      vi.mocked(spaApiClient.transitionOrder).mockResolvedValue(transitionedOrder);

      const result = await spaApiClient.transitionOrder('1', transition);

      expect(spaApiClient.transitionOrder).toHaveBeenCalledWith('1', transition);
      expect(result).toEqual(transitionedOrder);
    });

    it('should handle invalid transitions', async () => {
      const transition = 'invalid_transition';
      const error = new Error('Invalid transition');
      vi.mocked(spaApiClient.transitionOrder).mockRejectedValue(error);

      await expect(spaApiClient.transitionOrder('1', transition)).rejects.toThrow('Invalid transition');
      expect(spaApiClient.transitionOrder).toHaveBeenCalledWith('1', transition);
    });
  });
});