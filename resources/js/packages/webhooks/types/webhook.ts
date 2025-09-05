export interface Webhook {
  id: string;
  source: string;
  event_type: string;
  status: 'pending' | 'processing' | 'completed' | 'failed' | 'cancelled';
  payload: Record<string, any>;
  related_order_id?: string;
  attempts: number;
  max_attempts?: number;
  error_message?: string;
  processed_at?: string;
  created_at: string;
  updated_at: string;
}

export interface WebhookFilters {
  page?: number;
  per_page?: number;
  sort?: string;
  direction?: 'asc' | 'desc';
  source?: string;
  status?: string;
  event_type?: string;
  related_order_id?: string;
}

export interface WebhookResponse {
  data: Webhook[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}