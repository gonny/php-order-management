# Recommended Package Structure Migration Guide

## Current File Organization Analysis

### Orders Management (Well-developed)
**Current Files:**
- `pages/orders/Index.svelte` - Order listing with filtering (358 lines)
- `pages/orders/Show.svelte` - Order details and status management (649 lines)
- `pages/Orders/PdfGeneration.svelte` - PDF generation interface (258 lines)
- `hooks/use-orders.ts` - Order API queries and mutations

**Assessment:** Core functionality well-implemented but contains TypeScript errors and some mixed concerns.

### Client Management (Mature)
**Current Files:**
- `pages/clients/Index.svelte` - Client listing and search (401 lines)
- `hooks/use-clients.ts` - Client API integration

**Assessment:** Well-structured with good filtering and pagination.

### Webhook Management (Basic)
**Current Files:**
- `pages/webhooks/Index.svelte` - Webhook listing and management
- `hooks/use-webhooks.ts` - Webhook API queries

**Assessment:** Basic functionality, potential for expansion.

### Shipping/Labels (Scattered)
**Current Files:**
- `hooks/use-labels.ts` - Shipping label operations
- DPD logic mixed into order components

**Assessment:** Needs consolidation and dedicated structure.

## Proposed Package Structure

```
resources/js/
├── packages/
│   ├── orders/
│   │   ├── components/
│   │   │   ├── OrderList.svelte              # Extract from current Index.svelte
│   │   │   ├── OrderDetails.svelte           # Extract from current Show.svelte
│   │   │   ├── OrderStatusBadge.svelte       # Reusable status display
│   │   │   ├── OrderFilters.svelte           # Extract filtering logic
│   │   │   ├── OrderActions.svelte           # Action buttons and dropdowns
│   │   │   └── OrderTransitionDialog.svelte  # Status transition UI
│   │   ├── hooks/
│   │   │   ├── use-orders.ts                 # Move from hooks/
│   │   │   ├── use-order-transitions.ts      # Extract transition logic
│   │   │   └── use-order-filters.ts          # Extract filter management
│   │   ├── types/
│   │   │   ├── order.ts                      # Order-specific types
│   │   │   └── filters.ts                    # Filter-specific types
│   │   ├── utils/
│   │   │   ├── order-helpers.ts              # Business logic helpers
│   │   │   ├── status-utils.ts               # Status-related utilities
│   │   │   └── currency-formatters.ts        # Formatting utilities
│   │   └── stores/
│   │       └── order-filters.svelte.ts       # Filter state management
│   │
│   ├── pdf-generator/
│   │   ├── components/
│   │   │   ├── PdfGenerationForm.svelte      # Main PDF generation interface
│   │   │   ├── ImageUploadGrid.svelte        # Image upload component
│   │   │   ├── PdfPreview.svelte             # PDF preview display
│   │   │   └── PdfSettings.svelte            # PDF configuration options
│   │   ├── hooks/
│   │   │   ├── use-pdf-generation.ts         # PDF generation API calls
│   │   │   └── use-image-upload.ts           # Image handling logic
│   │   ├── types/
│   │   │   └── pdf.ts                        # PDF-specific types
│   │   └── utils/
│   │       ├── pdf-helpers.ts                # PDF processing utilities
│   │       └── image-validation.ts           # Image validation logic
│   │
│   ├── dpd-courier/
│   │   ├── components/
│   │   │   ├── DpdTracking.svelte            # Extract from order components
│   │   │   ├── DpdLabelGeneration.svelte     # DPD-specific label creation
│   │   │   ├── DpdWebhookStatus.svelte       # DPD webhook information
│   │   │   └── DpdShipmentDetails.svelte     # DPD shipment display
│   │   ├── hooks/
│   │   │   ├── use-dpd-tracking.ts           # DPD tracking API
│   │   │   ├── use-dpd-labels.ts             # DPD label management
│   │   │   └── use-dpd-webhooks.ts           # DPD webhook handling
│   │   ├── types/
│   │   │   ├── dpd-api.ts                    # DPD API response types
│   │   │   └── dpd-tracking.ts               # DPD tracking types
│   │   └── utils/
│   │       ├── dpd-helpers.ts                # DPD-specific utilities
│   │       └── tracking-formatters.ts        # Tracking display formatting
│   │
│   ├── shipping/
│   │   ├── components/
│   │   │   ├── AddressForm.svelte            # Extract address creation
│   │   │   ├── CarrierSelector.svelte        # Carrier selection component
│   │   │   ├── ShippingLabelList.svelte      # Label display and management
│   │   │   ├── ShippingCalculator.svelte     # Shipping cost calculation
│   │   │   └── PickupPointSelector.svelte    # Pickup point selection
│   │   ├── hooks/
│   │   │   ├── use-labels.ts                 # Move from hooks/
│   │   │   ├── use-carriers.ts               # Carrier management
│   │   │   ├── use-addresses.ts              # Address management
│   │   │   └── use-pickup-points.ts          # Pickup point logic
│   │   ├── types/
│   │   │   ├── address.ts                    # Address types
│   │   │   ├── carrier.ts                    # Carrier types
│   │   │   └── shipping-label.ts             # Label types
│   │   └── utils/
│   │       ├── address-validation.ts         # Address validation
│   │       ├── shipping-calculators.ts       # Cost calculations
│   │       └── label-formatters.ts           # Label formatting
│   │
│   └── shared/
│       ├── components/
│       │   ├── forms/
│       │   │   ├── FormField.svelte          # Reusable form field
│       │   │   ├── FormActions.svelte        # Form action buttons
│       │   │   ├── SearchInput.svelte        # Search input component
│       │   │   └── DateRangePicker.svelte    # Date range selection
│       │   ├── tables/
│       │   │   ├── DataTable.svelte          # Generic data table
│       │   │   ├── TableFilters.svelte       # Table filtering UI
│       │   │   ├── TablePagination.svelte    # Pagination component
│       │   │   └── TableActions.svelte       # Table action buttons
│       │   ├── layout/
│       │   │   ├── PageHeader.svelte         # Standard page header
│       │   │   ├── ContentWrapper.svelte     # Content container
│       │   │   ├── LoadingSpinner.svelte     # Loading indicators
│       │   │   └── ErrorBoundary.svelte      # Error handling
│       │   └── feedback/
│       │       ├── StatusBadge.svelte        # Generic status display
│       │       ├── ProgressIndicator.svelte  # Progress display
│       │       └── NotificationToast.svelte  # Toast notifications
│       ├── hooks/
│       │   ├── use-clients.ts                # Move from hooks/
│       │   ├── use-dashboard.ts              # Move from hooks/
│       │   ├── use-webhooks.ts               # Move from hooks/
│       │   ├── use-pagination.ts             # Generic pagination
│       │   ├── use-search.ts                 # Generic search logic
│       │   └── use-filters.ts                # Generic filter management
│       ├── types/
│       │   ├── api.ts                        # Move from types/
│       │   ├── global.ts                     # Global application types
│       │   ├── ui.ts                         # UI component types
│       │   └── pagination.ts                 # Pagination types
│       ├── lib/
│       │   ├── api.ts                        # Move from lib/
│       │   ├── utils.ts                      # Move from lib/
│       │   ├── validators.ts                 # Move from lib/
│       │   ├── date-utils.ts                 # Date manipulation
│       │   └── currency-utils.ts             # Currency handling
│       └── stores/
│           ├── app-state.svelte.ts           # Global app state
│           ├── ui-state.svelte.ts            # UI state management
│           ├── user-preferences.svelte.ts    # User preferences
│           └── notification-state.svelte.ts  # Notification management
│
├── components/                               # Keep current UI components
│   └── ui/                                   # shadcn-ui components
├── layouts/                                  # Keep current layouts
├── pages/                                    # Keep for gradual migration
├── contexts/                                 # Keep current contexts
└── types/                                    # Keep during migration
```

## Migration Strategy

### Phase 1: Foundation (Week 1)
1. **Create Package Structure**
   ```bash
   mkdir -p resources/js/packages/{orders,pdf-generator,dpd-courier,shipping,shared}/{components,hooks,types,utils,stores}
   ```

2. **Move Shared Types and Utilities**
   - Move `lib/api.ts` → `packages/shared/lib/api.ts`
   - Move `lib/utils.ts` → `packages/shared/lib/utils.ts`
   - Move `lib/validators.ts` → `packages/shared/lib/validators.ts`
   - Move `types/api.ts` → `packages/shared/types/api.ts`

### Phase 2: Extract Order Management (Week 2)
1. **Create Order Types**
   - Extract order-specific types from shared types
   - Create `packages/orders/types/order.ts`

2. **Move Order Hooks**
   - Move `hooks/use-orders.ts` → `packages/orders/hooks/use-orders.ts`
   - Create specialized hooks for transitions and filters

3. **Refactor Order Components**
   - Split large components into smaller, focused components
   - Extract reusable parts to shared package

### Phase 3: Shipping and Courier Packages (Week 3)
1. **Extract Shipping Logic**
   - Move `hooks/use-labels.ts` → `packages/shipping/hooks/use-labels.ts`
   - Create carrier-specific components

2. **Create DPD Package**
   - Extract DPD-specific logic from order components
   - Create dedicated DPD tracking and webhook components

### Phase 4: PDF Generation and Final Migration (Week 4)
1. **PDF Package Creation**
   - Move PDF generation component
   - Create specialized PDF utilities

2. **Complete Migration**
   - Update all imports to use new package structure
   - Remove old files after verification
   - Update build configuration if needed

## Benefits of This Structure

### 1. **Separation of Concerns**
- Each package handles a specific business domain
- Reduces coupling between unrelated functionality
- Makes testing and maintenance easier

### 2. **Reusability**
- Shared components in `packages/shared/`
- Domain-specific utilities in their respective packages
- Clear interface definitions for cross-package communication

### 3. **Scalability**
- Easy to add new shipping providers as separate packages
- New order features contained within orders package
- Clear boundaries for team ownership

### 4. **Developer Experience**
- Easier to navigate and understand codebase
- Faster development with focused, smaller components
- Better IDE support with clear module boundaries

### 5. **Bundle Optimization**
- Enables better tree-shaking
- Allows for lazy loading of entire packages
- Reduces initial bundle size

## Package Dependencies

```
orders → shared, shipping, dpd-courier
pdf-generator → shared, orders
dpd-courier → shared, shipping
shipping → shared
shared → (no internal dependencies)
```

This structure ensures clean dependency flow and prevents circular dependencies while maintaining functionality.