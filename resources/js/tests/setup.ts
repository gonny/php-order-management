import { beforeEach, vi } from 'vitest';
import '@testing-library/jest-dom';

// Mock the Inertia router
vi.mock('@inertiajs/svelte', () => ({
  router: {
    visit: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
  },
  useForm: vi.fn(() => ({
    processing: false,
    errors: {},
    post: vi.fn(),
    put: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
  })),
  page: vi.fn(() => ({
    component: 'Test',
    props: {},
    url: '/test',
    version: '1',
  })),
}));

// Mock the SPA API client
vi.mock('@/lib/spa-api', () => ({
  spaApiClient: {
    getClients: vi.fn(),
    getClient: vi.fn(),
    createClient: vi.fn(),
    updateClient: vi.fn(),
    deleteClient: vi.fn(),
    getOrders: vi.fn(),
    getOrder: vi.fn(),
    createOrder: vi.fn(),
    updateOrder: vi.fn(),
    deleteOrder: vi.fn(),
  },
  handleSpaApiError: vi.fn(),
}));

// Mock TanStack Query
vi.mock('@tanstack/svelte-query', () => ({
  createQuery: vi.fn(),
  createMutation: vi.fn(),
  useQueryClient: vi.fn(),
}));

beforeEach(() => {
  vi.clearAllMocks();
});