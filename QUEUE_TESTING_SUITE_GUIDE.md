# Laravel Queue Testing Suite with HMAC Authentication

This testing suite provides comprehensive tools for testing Laravel queues and API endpoints with HMAC authentication.

## Features

### 1. Queue Dashboard
- Real-time queue monitoring
- Failed and pending job statistics  
- Queue management actions (clear failed jobs, retry all)
- Recent job history with detailed information

### 2. API Testing Interface
- Interactive API endpoint testing
- HMAC authentication header generation
- JSON payload templates for common operations
- Request/response logging and analysis
- Test history tracking

### 3. Payload Templates
Pre-configured JSON templates for:
- **Order Creation**: Customer data, items, pricing, shipping details
- **Client Creation**: Contact info, preferences, billing data
- **Shipping Label Generation**: Addresses, package details, carrier info
- **PDF Generation**: Document type, template selection, data binding

## Setup and Usage

### 1. Generate Test Data
Use the Artisan command to create test data:

```bash
# Create test data with default counts
php artisan queue:seed-test-data

# Customize the amounts
php artisan queue:seed-test-data --clients=5 --orders=10 --jobs=3 --api-clients=2

# Clear existing test data first
php artisan queue:seed-test-data --clear --clients=3 --orders=5
```

This command creates:
- API clients with HMAC credentials for testing
- Test clients and orders
- Sample queue jobs (PDF generation, shipping labels, state changes)

### 2. Access the Testing Interface

#### Queue Dashboard
Navigate to `/testing/queue-dashboard` to:
- Monitor queue statistics (pending, failed, completed jobs)
- View recent failed and pending jobs
- Clear failed jobs or retry all failed jobs
- See available API clients for testing

#### API Testing Interface  
Navigate to `/testing/api-testing` to:
- Test API endpoints with HMAC authentication
- Use pre-built payload templates
- Generate proper HMAC headers automatically
- View request/response details
- Track test history

### 3. API Testing Workflow

1. **Select API Client**: Choose from available test API clients
2. **Choose Endpoint**: Select HTTP method and endpoint path
3. **Configure Payload**: Use templates or write custom JSON
4. **Execute Test**: The system automatically generates HMAC headers
5. **Review Results**: Analyze response status, body, headers, and timing

### 4. HMAC Authentication Testing

The testing suite automatically handles HMAC signature generation:
- **X-Key-Id**: API client key identifier
- **X-Signature**: HMAC-SHA256 signature
- **X-Timestamp**: Request timestamp
- **Digest**: SHA-256 hash of request body

## Available API Endpoints

### Orders
- `GET /api/v1/orders` - List all orders
- `POST /api/v1/orders` - Create new order
- `GET /api/v1/orders/{id}` - Get order details
- `PUT /api/v1/orders/{id}` - Update order
- `POST /api/v1/orders/{id}/pdf` - Generate order PDF
- `POST /api/v1/orders/{id}/label` - Generate shipping label

### Clients
- `GET /api/v1/clients` - List all clients
- `POST /api/v1/clients` - Create new client
- `GET /api/v1/clients/{id}` - Get client details
- `PUT /api/v1/clients/{id}` - Update client

### Queue Management
- `GET /api/v1/queues/stats` - Get queue statistics
- `GET /api/v1/queues/failed` - List failed jobs
- `POST /api/v1/queues/failed/{id}/retry` - Retry failed job

### Dashboard
- `GET /api/v1/dashboard/metrics` - Get dashboard metrics
- `GET /api/v1/health` - Health check endpoint

## Testing Commands

### Run Backend Tests
```bash
# Run all queue testing suite tests
php artisan test tests/Feature/Testing/

# Run specific test file
php artisan test tests/Feature/Testing/QueueTestingBackendTest.php

# Run with coverage
php artisan test --coverage
```

### Laravel Pint (Code Style)
```bash
# Check code style
./vendor/bin/pint --test

# Fix code style
./vendor/bin/pint
```

## Payload Template Examples

### Order Creation
```json
{
  "client_id": "{{client_id}}",
  "delivery_address": {
    "company_name": "{{company_name}}",
    "contact_person": "{{contact_person}}",
    "street": "{{street_address}}",
    "city": "{{city}}",
    "postal_code": "{{postal_code}}",
    "country": "{{country_code}}",
    "phone": "{{phone_number}}",
    "email": "{{email_address}}"
  },
  "items": [
    {
      "name": "{{item_name}}",
      "quantity": "{{quantity}}",
      "price": "{{unit_price}}",
      "sku": "{{sku_code}}"
    }
  ],
  "total_amount": "{{total_amount}}",
  "currency": "CZK",
  "shipping_method": "{{shipping_method}}",
  "notes": "{{order_notes}}"
}
```

### Client Creation
```json
{
  "company_name": "{{company_name}}",
  "contact_person": "{{contact_person}}",
  "email": "{{email_address}}",
  "phone": "{{phone_number}}",
  "billing_address": {
    "street": "{{billing_street}}",
    "city": "{{billing_city}}",
    "postal_code": "{{billing_postal_code}}",
    "country": "{{billing_country}}"
  },
  "tax_id": "{{tax_id}}",
  "preferences": {
    "preferred_shipping": "{{preferred_shipping}}",
    "payment_terms": "{{payment_terms}}"
  }
}
```

## Security Notes

- All API testing routes require authentication (`auth` middleware)
- HMAC signatures are properly validated using existing middleware
- Test API clients are clearly marked and should not be used in production
- Test data is isolated and can be safely cleared

## Troubleshooting

### Common Issues

1. **"Missing required authentication headers"**
   - Ensure you've selected a valid API client
   - Check that the API client is active

2. **"Invalid HMAC signature"**
   - The system automatically generates signatures - this usually indicates an issue with the API client secret
   - Try creating a new test API client

3. **"Validation errors"**
   - Check the payload JSON syntax
   - Ensure required fields are included for the endpoint

4. **Queue jobs not appearing**
   - Run `php artisan queue:work` to process jobs
   - Check that queue tables are properly migrated

### Getting Help

- Check the Laravel logs for detailed error information
- Use the queue dashboard to monitor job failures
- Test with the health endpoint first to verify basic connectivity

## Development

### Adding New Payload Templates

Edit `QueueTestingController::getPayloadTemplates()` to add new templates:

```php
'my_template' => [
    'name' => 'My Custom Template',
    'description' => 'Description of what this template does',
    'template' => [
        'field1' => '{{variable1}}',
        'field2' => '{{variable2}}'
    ]
]
```

### Adding New API Endpoints

Edit `QueueTestingController::getApiEndpoints()` to document new endpoints:

```php
[
    'group' => 'My API Group',
    'endpoints' => [
        ['method' => 'POST', 'path' => '/api/v1/my-endpoint', 'description' => 'My endpoint description']
    ]
]
```