# Order Management System API Documentation

## Overview

This is a comprehensive Laravel 12 order management system featuring robust state machine, HMAC authentication, queue-based automation, and webhook processing. The system supports complete order lifecycle management from creation through fulfillment and completion.

## Features

- **Complete Order Lifecycle Management** - New → Confirmed → Paid → Fulfilled → Completed
- **Secure HMAC Authentication** - Enterprise-grade API security with signature validation
- **Background Job Processing** - Queue-based webhook and automation handling
- **Multi-Carrier Shipping** - Support for Balíkovna and DPD carriers
- **Comprehensive Audit Logging** - Full audit trail for all operations
- **Flexible API Design** - RESTful endpoints with multiple lookup methods

## Authentication

All API endpoints require HMAC-SHA256 authentication using the following headers:

```bash
X-Key-Id: your-api-key-id
X-Signature: base64-encoded-hmac-signature
X-Timestamp: unix-timestamp
Digest: SHA-256=base64-encoded-body-hash
```

### Signature Generation

```php
$method = 'POST';
$path = '/api/v1/orders';
$timestamp = time();
$body = json_encode($payload);
$bodyHash = base64_encode(hash('sha256', $body, true));

$stringToSign = $method . $path . $timestamp . hash('sha256', $body);
$signature = base64_encode(hash_hmac('sha256', $stringToSign, $secret, true));
```

## Core API Endpoints

### Orders

#### Create Order
```bash
POST /api/v1/orders
```

**Request Body:**
```json
{
  "client": {
    "external_id": "CLIENT_001",
    "email": "customer@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+420123456789"
  },
  "shipping_address": {
    "name": "John Doe",
    "street1": "123 Main St",
    "city": "Prague",
    "postal_code": "10000",
    "country_code": "CZ"
  },
  "items": [
    {
      "sku": "ITEM-001",
      "name": "Test Product",
      "qty": 2,
      "price": 29.99,
      "tax_rate": 0.21
    }
  ],
  "carrier": "balikovna",
  "currency": "EUR"
}
```

#### List Orders
```bash
GET /api/v1/orders?status=new&client_id=01234567-89ab-cdef-0123-456789abcdef
```

#### Get Order
```bash
GET /api/v1/orders/{id|number|pmi_id}
```

#### Update Order
```bash
PATCH /api/v1/orders/{id}
```

#### Delete Order (New orders only)
```bash
DELETE /api/v1/orders/{id}
```

### Order State Management

#### Transition Order Status
```bash
POST /api/v1/orders/{id}/transition
```

**Request Body:**
```json
{
  "status": "confirmed",
  "reason": "Inventory verified",
  "metadata": {
    "admin_user": "admin@example.com"
  }
}
```

**Available Status Transitions:**
- `new` → `confirmed`, `cancelled`, `on_hold`, `failed`
- `confirmed` → `paid`, `cancelled`, `on_hold`, `failed`
- `paid` → `fulfilled`, `cancelled`, `on_hold`, `failed`
- `fulfilled` → `completed`, `cancelled`, `failed`
- `on_hold` → `new`, `confirmed`, `paid`, `cancelled`, `failed`
- `failed` → `new`, `on_hold`

### Shipping Labels

#### Generate Shipping Label
```bash
POST /api/v1/orders/{id}/label
```

**Request Body:**
```json
{
  "carrier": "balikovna",
  "options": {
    "weight": 1000,
    "length": 20,
    "width": 15,
    "height": 10,
    "pickup_point_id": "CZ001"
  }
}
```

#### Void Shipping Label
```bash
POST /api/v1/labels/{id}/void
```

### Clients

#### Create Client
```bash
POST /api/v1/clients
```

#### Get Client
```bash
GET /api/v1/clients/{id}
```

### Webhooks

#### Receive Generic Webhook
```bash
POST /api/v1/webhooks/incoming/{source}
```

#### Carrier-Specific Webhooks
```bash
POST /api/v1/webhooks/balikovna
POST /api/v1/webhooks/dpd
POST /api/v1/webhooks/payment
```

## Order State Machine

The system implements a robust state machine with comprehensive validation:

```
    new ──────→ confirmed ──────→ paid ──────→ fulfilled ──────→ completed
     │              │              │              │
     ↓              ↓              ↓              ↓
  cancelled ←── on_hold ←─── failed ←──────────────┘
     ↑              │              │
     └──────────────┴──────────────┘
```

### State Validation Rules

- **Confirmation**: Validates client, items, addresses, and total amount
- **Payment**: Requires valid payment method identifier (pmi_id)
- **Fulfillment**: Ensures carrier assignment and shipping label generation
- **Completion**: Tracks delivery confirmation

## Background Job Processing

The system uses Laravel queues (database driver) for background processing:

### Queue Jobs

- **ProcessWebhook** - Handles incoming webhooks from carriers and payment providers
- **GenerateShippingLabel** - Creates shipping labels with carrier APIs
- **ProcessOrderStateChange** - Handles post-transition automation and notifications

### Queue Workers

Run queue workers to process background jobs:

```bash
php artisan queue:work database --sleep=3 --tries=3 --timeout=90
```

## Database Schema

### Core Tables

- **orders** - Order records with ULID primary keys
- **order_items** - Individual order line items
- **clients** - Customer records
- **addresses** - Shipping and billing addresses
- **shipping_labels** - Generated shipping labels
- **api_clients** - API authentication credentials
- **audit_logs** - Complete audit trail
- **webhooks** - Incoming webhook records
- **jobs** - Queue job processing

### Indexing Strategy

- Status and client_id fields for fast filtering
- ULIDs for distributed-friendly primary keys
- JSON metadata columns for flexible data storage

## Error Handling

### HTTP Status Codes

- `200` - Success
- `201` - Created
- `202` - Accepted (async processing)
- `400` - Bad Request
- `401` - Unauthorized
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

### Error Response Format

```json
{
  "error": "Validation failed",
  "message": "Detailed error message",
  "errors": {
    "field": ["Specific validation error"]
  }
}
```

## Rate Limiting

API endpoints are rate limited to 60 requests per minute per API client. Rate limit headers are included in responses:

- `X-RateLimit-Limit` - Request limit
- `X-RateLimit-Remaining` - Remaining requests
- `X-RateLimit-Reset` - Reset timestamp

## Testing

### Running Tests

```bash
# All tests
php artisan test

# Unit tests only
php artisan test --testsuite=Unit

# Specific test file
php artisan test tests/Unit/Services/OrderManagement/OrderStateMachineTest.php
```

### Test Coverage

- State machine transitions (14 test scenarios)
- API endpoint validation
- HMAC authentication
- Webhook processing
- Database factories for all models

## Deployment

### Environment Variables

```bash
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=order_management
DB_USERNAME=root
DB_PASSWORD=

# Queue
QUEUE_CONNECTION=database

# Shipping Carriers
BALIKOVNA_API_KEY=your-api-key
BALIKOVNA_WEBHOOK_SECRET=your-webhook-secret
DPD_API_KEY=your-api-key
DPD_WEBHOOK_SECRET=your-webhook-secret

# Security
SHIPPING_VERIFY_WEBHOOK_SIGNATURES=true
```

### Setup Commands

```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Create API client
php artisan tinker
> App\Models\ApiClient::create([
    'key_id' => 'client_production',
    'secret' => 'your-secret-key',
    'name' => 'Production Client',
    'is_active' => true
  ]);

# Start queue worker
php artisan queue:work database --daemon
```

## Security Considerations

- HMAC signatures prevent request tampering
- Timestamp validation prevents replay attacks
- IP allowlists for additional security
- Rate limiting prevents abuse
- Complete audit logging for compliance
- Secure webhook signature validation

## Monitoring

### Queue Monitoring

```bash
# Check queue status
php artisan queue:monitor database

# Failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Logs

Monitor application logs for:
- Authentication failures
- Webhook processing errors
- State transition failures
- API rate limit violations

### Database Metrics

Monitor key metrics:
- Order creation rate
- State transition patterns
- Webhook processing latency
- Failed job rate

This completes the comprehensive order management system implementation with full documentation for production deployment.