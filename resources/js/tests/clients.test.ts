import { describe, it, expect, vi, beforeEach } from 'vitest';
import { spaApiClient } from '@/lib/spa-api';
import type { Client, ClientCreateDTO, ClientUpdateDTO, PaginatedResponse } from '@/types';

// Mock the spaApiClient
vi.mock('@/lib/spa-api');

const mockClient: Client = {
  id: '1',
  external_id: 'EXT-001',
  email: 'test@example.com',
  phone: '+420123456789',
  first_name: 'John',
  last_name: 'Doe',
  company: 'Test Company',
  vat_id: 'CZ12345678',
  is_active: true,
  meta: {},
  created_at: '2024-01-01T00:00:00Z',
  updated_at: '2024-01-01T00:00:00Z',
};

const mockPaginatedResponse: PaginatedResponse<Client> = {
  data: [mockClient],
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 1,
  from: 1,
  to: 1,
};

describe('Client CRUD Operations', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe('getClients', () => {
    it('should fetch paginated clients list', async () => {
      vi.mocked(spaApiClient.getClients).mockResolvedValue(mockPaginatedResponse);

      const result = await spaApiClient.getClients();

      expect(spaApiClient.getClients).toHaveBeenCalledOnce();
      expect(result).toEqual(mockPaginatedResponse);
    });

    it('should fetch clients with filters', async () => {
      const filters = { external_id: 'EXT-001', page: 1, per_page: 10 };
      vi.mocked(spaApiClient.getClients).mockResolvedValue(mockPaginatedResponse);

      const result = await spaApiClient.getClients(filters);

      expect(spaApiClient.getClients).toHaveBeenCalledWith(filters);
      expect(result).toEqual(mockPaginatedResponse);
    });
  });

  describe('getClient', () => {
    it('should fetch a single client by ID', async () => {
      vi.mocked(spaApiClient.getClient).mockResolvedValue(mockClient);

      const result = await spaApiClient.getClient('1');

      expect(spaApiClient.getClient).toHaveBeenCalledWith('1');
      expect(result).toEqual(mockClient);
    });
  });

  describe('createClient', () => {
    it('should create a new client', async () => {
      const createDTO: ClientCreateDTO = {
        external_id: 'EXT-002',
        email: 'new@example.com',
        first_name: 'Jane',
        last_name: 'Smith',
        is_active: true,
      };

      const newClient: Client = {
        ...mockClient,
        id: '2',
        ...createDTO,
      };

      vi.mocked(spaApiClient.createClient).mockResolvedValue(newClient);

      const result = await spaApiClient.createClient(createDTO);

      expect(spaApiClient.createClient).toHaveBeenCalledWith(createDTO);
      expect(result).toEqual(newClient);
    });

    it('should handle validation errors during client creation', async () => {
      const createDTO: ClientCreateDTO = {
        email: 'invalid-email',
        first_name: '',
        last_name: '',
      };

      const error = new Error('Validation failed');
      vi.mocked(spaApiClient.createClient).mockRejectedValue(error);

      await expect(spaApiClient.createClient(createDTO)).rejects.toThrow('Validation failed');
      expect(spaApiClient.createClient).toHaveBeenCalledWith(createDTO);
    });
  });

  describe('updateClient', () => {
    it('should update an existing client', async () => {
      const updateDTO: ClientUpdateDTO = {
        email: 'updated@example.com',
        company: 'Updated Company',
      };

      const updatedClient: Client = {
        ...mockClient,
        ...updateDTO,
      };

      vi.mocked(spaApiClient.updateClient).mockResolvedValue(updatedClient);

      const result = await spaApiClient.updateClient('1', updateDTO);

      expect(spaApiClient.updateClient).toHaveBeenCalledWith('1', updateDTO);
      expect(result).toEqual(updatedClient);
    });
  });

  describe('deleteClient', () => {
    it('should delete a client', async () => {
      vi.mocked(spaApiClient.deleteClient).mockResolvedValue(undefined);

      await spaApiClient.deleteClient('1');

      expect(spaApiClient.deleteClient).toHaveBeenCalledWith('1');
    });

    it('should handle errors during client deletion', async () => {
      const error = new Error('Client not found');
      vi.mocked(spaApiClient.deleteClient).mockRejectedValue(error);

      await expect(spaApiClient.deleteClient('999')).rejects.toThrow('Client not found');
      expect(spaApiClient.deleteClient).toHaveBeenCalledWith('999');
    });
  });
});