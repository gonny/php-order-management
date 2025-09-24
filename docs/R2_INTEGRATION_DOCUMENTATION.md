# Cloudflare R2 Integration Documentation

This document describes the Cloudflare R2 integration with the Laravel Order Management system, implementing a microservice architecture using Laravel queues.

## Overview

The R2 integration extends the existing PDF generation system to support downloading photos from Cloudflare R2 storage and generating PDFs using a microservice pattern with separate queue jobs.

## Configuration

### Environment Variables

Add the following R2 configuration to your `.env` file:

```env
# Cloudflare R2 Configuration
CLOUDFLARE_R2_ACCOUNT_ID=your_account_id
CLOUDFLARE_R2_ACCESS_KEY_ID=your_access_key
CLOUDFLARE_R2_SECRET_ACCESS_KEY=your_secret_key
CLOUDFLARE_R2_BUCKET=your_bucket_name
CLOUDFLARE_R2_REGION=auto
```

### File System Configuration

The system includes three storage disks:

- `r2`: Cloudflare R2 storage for source photos
- `uploads`: Local storage for downloaded photos (`storage/app/uploads`)
- `pdfs`: Local storage for generated PDFs (`storage/app/pdfs`)

## API Endpoints

### 1. Create Order with R2 Workflow

**POST** `/api/v1/orders`

Enhanced to optionally accept R2 photo links and trigger the R2 workflow.

#### Request Body

```json
{
  "client": {
    "external_id": "CLIENT_001",
    "email": "client@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+1234567890"
  },
  "shipping_address": {
    "name": "John Doe",
    "street1": "123 Main St",
    "city": "New York",
    "postal_code": "10001",
    "country_code": "US"
  },
  "items": [
    {
      "sku": "ITEM-001",
      "name": "Product Name",
      "qty": 1,
      "price": 29.99
    }
  ],
  "r2_photo_links": [
    "https://account.r2.cloudflarestorage.com/bucket/session123/photo1.jpg",
    "https://account.r2.cloudflarestorage.com/bucket/session123/photo2.jpg"
  ],
  "remote_session_id": "session123"
}
```

#### Response

```json
{
  "data": {
    "id": "01k4say3rvrx0s9htdevqbxfnq",
    "number": "ORD-66F68F2C",
    "status": "new",
    "client": {...},
    "items": [...]
  },
  "message": "Order created successfully",
  "r2_workflow": {
    "status": "processing",
    "remote_session_id": "session123",
    "photos_count": 2
  }
}
```

### 2. Generate PDF from R2 for Existing Order

**POST** `/api/v1/orders/{order}/pdf/r2`

Triggers R2 photo download and PDF generation for an existing order.

#### Request Body

```json
{
  "r2_photo_links": [
    "https://account.r2.cloudflarestorage.com/bucket/session456/photo1.jpg",
    "https://account.r2.cloudflarestorage.com/bucket/session456/photo2.jpg",
    "https://account.r2.cloudflarestorage.com/bucket/session456/photo3.jpg"
  ],
  "remote_session_id": "session456"
}
```

#### Response

```json
{
  "message": "R2 PDF generation started. Photos will be downloaded and PDF generated.",
  "order_id": "01k4say3rvrx0s9htdevqbxfnq",
  "remote_session_id": "session456",
  "status": "processing"
}
```

## Microservice Architecture

The R2 integration implements a microservice pattern using two separate queue jobs:

### 1. DownloadR2PhotosJob

**Purpose**: Downloads photos from Cloudflare R2 to local storage

**Features**:
- Downloads up to 9 photos per order
- Stores photos in `uploads/{remote_session_id}/{photo_name}` structure
- Validates photo existence in R2
- Updates order with local photo paths
- Chains to PDF generation job upon completion

**Configuration**:
- Tries: 3
- Timeout: 10 minutes
- Queue: default

### 2. GeneratePdfFromOrderJob

**Purpose**: Generates PDF from locally downloaded photos

**Features**:
- Validates local photo existence
- Generates 3x3 grid PDF layout
- Saves PDF in same directory as photos
- Updates order with PDF path
- Comprehensive error handling

**Configuration**:
- Tries: 3
- Timeout: 5 minutes
- Queue: default

## Database Schema

The following fields were added to the `orders` table:

```php
$table->string('remote_session_id')->nullable();
$table->json('r2_photo_links')->nullable();
$table->json('local_photo_paths')->nullable();
```

## File Structure

Photos and PDFs are organized as follows:

```
storage/app/uploads/
├── session123/
│   ├── photo1.jpg
│   ├── photo2.jpg
│   └── order_01k4say3rvrx0s9htdevqbxfnq.pdf
└── session456/
    ├── photo1.jpg
    ├── photo2.jpg
    ├── photo3.jpg
    └── order_01j9xy7abc8defghijklmnop.pdf
```

## Error Handling

### Common Error Scenarios

1. **R2 Connection Issues**: Jobs retry with exponential backoff
2. **Missing Photos**: Jobs fail with detailed error messages
3. **Storage Issues**: Comprehensive logging for debugging
4. **PDF Generation**: Graceful failure with audit logging

### Monitoring

All R2 workflow events are logged through the audit system:

- `r2_workflow_triggered_on_creation`
- `r2_pdf_generation_requested`
- Job failures and retries

## Usage Examples

### Basic R2 Workflow

```php
// Create order with R2 photos
$order = Order::create([...]);

// Dispatch R2 workflow
DownloadR2PhotosJob::dispatch(
    $order,
    $r2PhotoLinks,
    $remoteSessionId
);
```

### Queue Monitoring

```bash
# Monitor queue jobs
php artisan queue:work

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

## Testing

The integration includes comprehensive test coverage:

- **DownloadR2PhotosJobTest**: 8 tests covering job functionality
- **GeneratePdfFromOrderJobTest**: 8 tests for PDF generation
- **R2PdfGenerationApiTest**: 4 API integration tests

Run tests:

```bash
php artisan test tests/Unit/DownloadR2PhotosJobTest.php
php artisan test tests/Unit/GeneratePdfFromOrderJobTest.php
php artisan test tests/Unit/R2PdfGenerationApiTest.php
```

## Client Compatibility

The R2 integration maintains full backward compatibility:

- Existing PDF generation endpoints continue to work
- Client matching by email remains unchanged
- All existing functionality is preserved
- R2 features are opt-in via request parameters