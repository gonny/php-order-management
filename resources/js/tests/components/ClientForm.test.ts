import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen } from '@testing-library/svelte';
import userEvent from '@testing-library/user-event';
import ClientForm from '@/packages/clients/components/ClientForm.svelte';

// Mock the Inertia hooks - need to mock the actual return value structure
const mockForm = {
  external_id: '',
  email: '',
  phone: '',
  first_name: '',
  last_name: '',
  company: '',
  vat_id: '',
  is_active: true,
  meta: {},
  errors: {},
  processing: false,
  post: vi.fn(),
  put: vi.fn(),
};

vi.mock('@inertiajs/svelte', () => ({
  useForm: vi.fn(() => mockForm),
  router: {
    visit: vi.fn(),
  },
}));

describe('ClientForm Component', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    // Reset form values
    Object.assign(mockForm, {
      external_id: '',
      email: '',
      phone: '',
      first_name: '',
      last_name: '',
      company: '',
      vat_id: '',
      is_active: true,
      meta: {},
      errors: {},
      processing: false,
    });
  });

  it('should render create form correctly', () => {
    render(ClientForm, {
      props: {
        mode: 'create'
      }
    });

    expect(screen.getByText('Basic Information')).toBeInTheDocument();
    expect(screen.getByLabelText('First Name *')).toBeInTheDocument();
    expect(screen.getByLabelText('Last Name *')).toBeInTheDocument();
    expect(screen.getByLabelText('Email Address *')).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /create client/i })).toBeInTheDocument();
  });

  it('should render edit form with client prop', () => {
    const mockClient = {
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

    // Set up the form to use client data
    Object.assign(mockForm, {
      external_id: mockClient.external_id,
      email: mockClient.email,
      phone: mockClient.phone,
      first_name: mockClient.first_name,
      last_name: mockClient.last_name,
      company: mockClient.company,
      vat_id: mockClient.vat_id,
      is_active: mockClient.is_active,
      meta: mockClient.meta,
    });

    render(ClientForm, {
      props: {
        mode: 'edit',
        client: mockClient
      }
    });

    expect(screen.getByRole('button', { name: /save changes/i })).toBeInTheDocument();
  });

  it('should handle form input changes', async () => {
    const user = userEvent.setup();
    render(ClientForm, {
      props: {
        mode: 'create'
      }
    });

    const firstNameInput = screen.getByLabelText('First Name *');
    await user.type(firstNameInput, 'Jane');
    
    // In the actual app, this would trigger binding updates
    // For testing, we're focusing on UI interaction
    expect(firstNameInput).toHaveValue('Jane');
  });

  it('should handle form submission', async () => {
    const user = userEvent.setup();
    render(ClientForm, {
      props: {
        mode: 'create'
      }
    });

    // Fill required fields
    await user.type(screen.getByLabelText('First Name *'), 'John');
    await user.type(screen.getByLabelText('Last Name *'), 'Doe');
    await user.type(screen.getByLabelText('Email Address *'), 'john@example.com');

    // Submit form
    await user.click(screen.getByRole('button', { name: /create client/i }));

    // Verify the form post method was called
    expect(mockForm.post).toHaveBeenCalled();
  });

  it('should handle cancel action', async () => {
    const user = userEvent.setup();
    const mockOnCancel = vi.fn();
    
    render(ClientForm, {
      props: {
        mode: 'create',
        onCancel: mockOnCancel
      }
    });

    await user.click(screen.getByRole('button', { name: /cancel/i }));
    
    expect(mockOnCancel).toHaveBeenCalled();
  });

  it('should display form sections', () => {
    render(ClientForm, {
      props: {
        mode: 'create'
      }
    });

    // Check that all major form sections are present
    expect(screen.getByText('Basic Information')).toBeInTheDocument();
    expect(screen.getByText('Company Information')).toBeInTheDocument();
    expect(screen.getByText('External Information')).toBeInTheDocument();
    
    // Check that key form fields are present
    expect(screen.getByLabelText('First Name *')).toBeInTheDocument();
    expect(screen.getByLabelText('Last Name *')).toBeInTheDocument();
    expect(screen.getByLabelText('Email Address *')).toBeInTheDocument();
    expect(screen.getByLabelText('Phone Number')).toBeInTheDocument();
    expect(screen.getByLabelText('Company Name')).toBeInTheDocument();
    expect(screen.getByLabelText('VAT ID')).toBeInTheDocument();
    expect(screen.getByLabelText('External ID')).toBeInTheDocument();
    expect(screen.getByLabelText('Active Client')).toBeInTheDocument();
  });
});