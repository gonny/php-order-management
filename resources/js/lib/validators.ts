import { z } from 'zod';

// Enum schemas
export const orderStatusSchema = z.enum([
  'new',
  'confirmed', 
  'paid',
  'fulfilled',
  'completed',
  'cancelled',
  'on_hold',
  'failed'
]);

export const carrierSchema = z.enum(['balikovna', 'dpd']);

export const currencySchema = z.enum(['EUR', 'CZK', 'USD']);

export const addressTypeSchema = z.enum(['shipping', 'billing']);

export const webhookSourceSchema = z.enum(['balikovna', 'dpd', 'payments', 'custom']);

export const webhookStatusSchema = z.enum(['pending', 'processing', 'completed', 'failed']);

// Address schemas
export const addressCreateSchema = z.object({
  type: addressTypeSchema,
  name: z.string().min(1, 'Name is required').max(255),
  company: z.string().max(255).optional(),
  street1: z.string().min(1, 'Street address is required').max(255),
  street2: z.string().max(255).optional(),
  city: z.string().min(1, 'City is required').max(255),
  state: z.string().max(255).optional(),
  postal_code: z.string().min(1, 'Postal code is required').max(20),
  country_code: z.string().length(2, 'Country code must be 2 characters'),
  phone: z.string().max(255).optional(),
  is_default: z.boolean().optional().default(false)
});

export const addressSchema = addressCreateSchema.extend({
  id: z.string(),
  client_id: z.string(),
  created_at: z.string(),
  updated_at: z.string()
});

// Client schemas
export const clientCreateSchema = z.object({
  external_id: z.string().max(255).optional(),
  email: z.string().email('Invalid email address').max(255),
  phone: z.string().max(255).optional(),
  first_name: z.string().min(1, 'First name is required').max(255),
  last_name: z.string().min(1, 'Last name is required').max(255),
  company: z.string().max(255).optional(),
  vat_id: z.string().max(255).optional(),
  meta: z.record(z.string(), z.any()).optional()
});

export const clientUpdateSchema = clientCreateSchema.partial();

export const clientSchema = clientCreateSchema.extend({
  id: z.string(),
  created_at: z.string(),
  updated_at: z.string(),
  orders: z.array(z.any()).optional(),
  addresses: z.array(addressSchema).optional()
});

// Order Item schemas
export const orderItemCreateSchema = z.object({
  sku: z.string().min(1, 'SKU is required').max(255),
  name: z.string().min(1, 'Item name is required').max(255),
  qty: z.number().positive('Quantity must be positive'),
  price: z.number().nonnegative('Price must be non-negative'),
  tax_rate: z.number().min(0).max(1, 'Tax rate must be between 0 and 1')
});

export const orderItemSchema = orderItemCreateSchema.extend({
  id: z.string(),
  order_id: z.string(),
  total: z.number(),
  created_at: z.string(),
  updated_at: z.string()
});

// Order schemas
export const orderCreateSchema = z.object({
  client: clientCreateSchema.optional(),
  client_id: z.string().optional(),
  shipping_address: addressCreateSchema,
  billing_address: addressCreateSchema.optional(),
  items: z.array(orderItemCreateSchema).min(1, 'At least one item is required'),
  carrier: carrierSchema,
  currency: currencySchema,
  notes: z.string().max(1000).optional(),
  meta: z.record(z.string(), z.any()).optional()
}).refine(data => data.client || data.client_id, {
  message: 'Either client or client_id must be provided',
  path: ['client']
});

export const orderUpdateSchema = z.object({
  status: orderStatusSchema.optional(),
  notes: z.string().max(1000).optional(),
  meta: z.record(z.string(), z.any()).optional()
});

export const orderSchema = z.object({
  id: z.string(),
  number: z.string(),
  pmi_id: z.string().nullable(),
  status: orderStatusSchema,
  client_id: z.string(),
  client: clientSchema.optional(),
  shipping_address_id: z.string(),
  billing_address_id: z.string().nullable(),
  shipping_address: addressSchema.optional(),
  billing_address: addressSchema.optional(),
  carrier: carrierSchema,
  currency: currencySchema,
  subtotal: z.number(),
  tax_total: z.number(),
  total: z.number(),
  notes: z.string().nullable(),
  meta: z.record(z.string(), z.any()).nullable(),
  items: z.array(orderItemSchema).optional(),
  created_at: z.string(),
  updated_at: z.string()
});

// Shipping Label schemas
export const labelCreateSchema = z.object({
  carrier_specific_data: z.record(z.string(), z.any()).optional()
});

export const shippingLabelSchema = z.object({
  id: z.string(),
  order_id: z.string(),
  carrier: carrierSchema,
  label_id: z.string(),
  tracking_number: z.string(),
  label_url: z.string().nullable(),
  status: z.enum(['pending', 'generated', 'printed', 'voided']),
  carrier_data: z.record(z.string(), z.any()).nullable(),
  created_at: z.string(),
  updated_at: z.string()
});

// Webhook schemas
export const webhookSchema = z.object({
  id: z.string(),
  source: webhookSourceSchema,
  event_type: z.string(),
  status: webhookStatusSchema,
  payload: z.record(z.string(), z.any()),
  processed_at: z.string().nullable(),
  error_message: z.string().nullable(),
  retry_count: z.number(),
  related_order_id: z.string().nullable(),
  created_at: z.string(),
  updated_at: z.string()
});

// Filter schemas
export const orderFiltersSchema = z.object({
  status: z.array(orderStatusSchema).optional(),
  carrier: z.array(carrierSchema).optional(),
  pmi_id: z.string().optional(),
  number: z.string().optional(),
  client_id: z.string().optional(),
  date_from: z.string().optional(),
  date_to: z.string().optional(),
  search: z.string().optional(),
  page: z.number().positive().optional(),
  per_page: z.number().positive().max(100).optional(),
  sort: z.string().optional(),
  direction: z.enum(['asc', 'desc']).optional()
});

export const clientFiltersSchema = z.object({
  search: z.string().optional(),
  email: z.string().optional(),
  external_id: z.string().optional(),
  page: z.number().positive().optional(),
  per_page: z.number().positive().max(100).optional(),
  sort: z.string().optional(),
  direction: z.enum(['asc', 'desc']).optional()
});

export const webhookFiltersSchema = z.object({
  source: z.array(webhookSourceSchema).optional(),
  status: z.array(webhookStatusSchema).optional(),
  event_type: z.string().optional(),
  date_from: z.string().optional(),
  date_to: z.string().optional(),
  related_order_id: z.string().optional(),
  page: z.number().positive().optional(),
  per_page: z.number().positive().max(100).optional(),
  sort: z.string().optional(),
  direction: z.enum(['asc', 'desc']).optional()
});

// API Response schemas
export const paginationMetaSchema = z.object({
  current_page: z.number(),
  from: z.number(),
  last_page: z.number(),
  per_page: z.number(),
  to: z.number(),
  total: z.number()
});

export const paginationLinksSchema = z.object({
  first: z.string(),
  last: z.string(),
  prev: z.string().nullable(),
  next: z.string().nullable()
});

export const paginatedResponseSchema = <T extends z.ZodTypeAny>(dataSchema: T) =>
  z.object({
    data: z.array(dataSchema),
    meta: paginationMetaSchema,
    links: paginationLinksSchema
  });

export const apiResponseSchema = <T extends z.ZodTypeAny>(dataSchema: T) =>
  z.object({
    data: dataSchema,
    message: z.string().optional()
  });

export const apiErrorSchema = z.object({
  error: z.string(),
  message: z.string().optional(),
  errors: z.record(z.string(), z.array(z.string())).optional()
});

// Dashboard schemas
export const dashboardMetricsSchema = z.object({
  order_counts: z.record(orderStatusSchema, z.number()),
  total_revenue: z.number(),
  recent_orders: z.array(orderSchema),
  failed_jobs_count: z.number(),
  api_response_time_p95: z.number(),
  queue_sizes: z.record(z.string(), z.number()),
  recent_activities: z.array(z.any()) // AuditLog schema would go here
});

// Order transition schema
export const orderTransitionSchema = z.enum([
  'confirm',
  'pay', 
  'fulfill',
  'complete',
  'cancel',
  'hold',
  'fail',
  'restart'
]);

// Type inference exports
export type OrderStatus = z.infer<typeof orderStatusSchema>;
export type Carrier = z.infer<typeof carrierSchema>;
export type Currency = z.infer<typeof currencySchema>;
export type AddressType = z.infer<typeof addressTypeSchema>;
export type WebhookSource = z.infer<typeof webhookSourceSchema>;
export type WebhookStatus = z.infer<typeof webhookStatusSchema>;
export type OrderTransition = z.infer<typeof orderTransitionSchema>;

export type AddressCreateDTO = z.infer<typeof addressCreateSchema>;
export type Address = z.infer<typeof addressSchema>;
export type ClientCreateDTO = z.infer<typeof clientCreateSchema>;
export type ClientUpdateDTO = z.infer<typeof clientUpdateSchema>;
export type Client = z.infer<typeof clientSchema>;
export type OrderItemCreateDTO = z.infer<typeof orderItemCreateSchema>;
export type OrderItem = z.infer<typeof orderItemSchema>;
export type OrderCreateDTO = z.infer<typeof orderCreateSchema>;
export type OrderUpdateDTO = z.infer<typeof orderUpdateSchema>;
export type Order = z.infer<typeof orderSchema>;
export type LabelCreateDTO = z.infer<typeof labelCreateSchema>;
export type ShippingLabel = z.infer<typeof shippingLabelSchema>;
export type Webhook = z.infer<typeof webhookSchema>;

export type OrderFilters = z.infer<typeof orderFiltersSchema>;
export type ClientFilters = z.infer<typeof clientFiltersSchema>;
export type WebhookFilters = z.infer<typeof webhookFiltersSchema>;

export type PaginationMeta = z.infer<typeof paginationMetaSchema>;
export type PaginationLinks = z.infer<typeof paginationLinksSchema>;
export type PaginatedResponse<T> = {
  data: T[];
  meta: PaginationMeta;
  links: PaginationLinks;
};
export type ApiResponse<T> = {
  data: T;
  message?: string;
};
export type ApiError = z.infer<typeof apiErrorSchema>;
export type DashboardMetrics = z.infer<typeof dashboardMetricsSchema>;