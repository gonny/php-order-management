import { test, expect } from '@playwright/test';

test.describe('Order CRUD Operations', () => {
  test.beforeEach(async ({ page }) => {
    // Navigate to the application
    await page.goto('/');
    
    // Create a test client first for order creation
    await page.goto('/clients/create');
    
    const timestamp = Date.now();
    await page.fill('[id="first_name"]', 'Order');
    await page.fill('[id="last_name"]', 'Test');
    await page.fill('[id="email"]', `order.test.${timestamp}@example.com`);
    await page.click('button[type="submit"]');
    
    // Wait for redirect and extract client ID if possible
    await expect(page).toHaveURL(/\/clients$/);
  });

  test('should display orders index page without reactivity errors', async ({ page }) => {
    // Monitor console for reactivity errors
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.text().includes('effect_update_depth_exceeded') || 
          msg.text().includes('binding_property_non_reactive')) {
        consoleErrors.push(msg.text());
      }
    });

    await page.goto('/orders');
    
    // Check if the page loaded correctly
    await expect(page).toHaveTitle(/Orders.*Order Management/);
    await expect(page.locator('h2')).toContainText('Orders');
    
    // Check for the create button
    await expect(page.locator('button:has-text("New Order")')).toBeVisible();
    
    // Verify no reactivity errors occurred
    expect(consoleErrors.length).toBe(0);
  });

  test('should filter orders properly', async ({ page }) => {
    await page.goto('/orders');
    
    // Test search functionality
    await page.fill('[id="search"]', 'test');
    await page.press('[id="search"]', 'Enter');
    
    // Test status filter
    await page.click('[data-testid="status-filter-trigger"]');
    await page.click('[data-value="new"]');
    
    // Test carrier filter  
    await page.click('[data-testid="carrier-filter-trigger"]');
    await page.click('[data-value="dpd"]');
    
    // Apply filters
    await page.click('button:has-text("Apply")');
    
    // Clear filters
    await page.click('button:has-text("Clear")');
  });

  test('should create a new order using OrderForm component', async ({ page }) => {
    await page.goto('/orders/create');
    
    // Check if the create form is visible
    await expect(page.locator('h1')).toContainText('Create New Order');
    
    // Fill client ID
    await page.fill('[id="client_id"]', '1'); // Assuming client ID 1 exists
    
    // Fill out basic order information
    await page.click('[data-testid="status-select-trigger"]');
    await page.click('[data-value="new"]');
    await page.fill('[id="notes"]', 'Test order created via E2E test');
    
    // Add order items
    const firstItemName = page.locator('input[placeholder="Product name"]').first();
    await firstItemName.fill('Test Product');
    
    const firstItemDescription = page.locator('input[placeholder="Description"]').first(); 
    await firstItemDescription.fill('Test product description');
    
    const firstItemQuantity = page.locator('input[type="number"]').filter({ hasText: 'Quantity' }).first();
    await firstItemQuantity.fill('2');
    
    const firstItemPrice = page.locator('input[type="number"]').filter({ hasText: 'Unit Price' }).first();
    await firstItemPrice.fill('500');
    
    // Trigger calculation by changing focus
    await firstItemPrice.blur();
    
    // Fill billing address
    await page.locator('input').filter({ hasText: 'First Name' }).first().fill('John');
    await page.locator('input').filter({ hasText: 'Last Name' }).first().fill('Doe');
    await page.locator('input').filter({ hasText: 'Street Address' }).first().fill('123 Test Street');
    await page.locator('input').filter({ hasText: 'City' }).first().fill('Prague');
    await page.locator('input').filter({ hasText: 'Postal Code' }).first().fill('10000');
    await page.locator('input').filter({ hasText: 'Country' }).first().fill('CZ');
    await page.locator('input[type="email"]').first().fill('billing@test.com');
    
    // Copy billing to shipping
    await page.click('button:has-text("Copy from billing")');
    
    // Submit the form
    await page.click('button[type="submit"]');
    
    // Should redirect to orders list
    await expect(page).toHaveURL(/\/orders$/);
  });

  test('should edit an existing order using OrderForm component', async ({ page }) => {
    // First, create an order to edit
    await page.goto('/orders/create');
    
    await page.fill('[id="client_id"]', '1');
    await page.fill('input[placeholder="Product name"]', 'Edit Test Product');
    await page.fill('input[placeholder="Description"]', 'Product to be edited');
    
    // Set quantity and price
    const quantityInput = page.locator('input[type="number"]').nth(0);
    await quantityInput.fill('1');
    const priceInput = page.locator('input[type="number"]').nth(1);
    await priceInput.fill('100');
    
    // Fill required address fields for billing
    await page.locator('input').filter({ hasText: 'First Name' }).first().fill('Edit');
    await page.locator('input').filter({ hasText: 'Last Name' }).first().fill('Test');
    await page.locator('input').filter({ hasText: 'Street Address' }).first().fill('123 Edit Street');
    await page.locator('input').filter({ hasText: 'City' }).first().fill('Prague');
    await page.locator('input').filter({ hasText: 'Postal Code' }).first().fill('10000');
    await page.locator('input').filter({ hasText: 'Country' }).first().fill('CZ');
    
    await page.click('button:has-text("Copy from billing")');
    await page.click('button[type="submit"]');
    
    // Wait for redirect
    await expect(page).toHaveURL(/\/orders$/);
    
    // Find and click the edit button for our test order
    const editButton = page.locator('button').filter({ has: page.locator('[data-lucide="edit"]') }).first();
    await editButton.click();
    
    // Should be on edit page
    await expect(page.locator('h1')).toContainText('Edit Order');
    
    // Update the notes field
    await page.fill('[id="notes"]', 'Updated order notes via E2E test');
    
    // Submit the form
    await page.click('button[type="submit"]');
    
    // Should redirect back to orders list
    await expect(page).toHaveURL(/\/orders$/);
  });

  test('should view order details', async ({ page }) => {
    await page.goto('/orders');
    
    // Click on the first order's view button
    const viewButton = page.locator('button').filter({ has: page.locator('[data-lucide="eye"]') }).first();
    await viewButton.click();
    
    // Should be on order details page
    await expect(page.locator('h1')).toContainText(/Order|Details/);
    
    // Check that order information is displayed
    await expect(page.locator('text=Status')).toBeVisible();
    await expect(page.locator('text=Total')).toBeVisible();
  });

  test('should handle order item calculations correctly', async ({ page }) => {
    await page.goto('/orders/create');
    
    await page.fill('[id="client_id"]', '1');
    
    // Fill order item with specific values
    await page.fill('input[placeholder="Product name"]', 'Calculation Test');
    
    const quantityInput = page.locator('input[type="number"]').nth(0);
    await quantityInput.fill('3');
    
    const priceInput = page.locator('input[type="number"]').nth(1);
    await priceInput.fill('250');
    
    // Trigger calculation by blurring the price input
    await priceInput.blur();
    
    // Wait for calculation
    await page.waitForTimeout(500);
    
    // Check that total was calculated correctly (3 * 250 = 750)
    const totalInput = page.locator('input[readonly]').filter({ hasText: '750' });
    await expect(totalInput).toBeVisible();
    
    // Check overall order total
    await expect(page.locator('text=Total: 750')).toBeVisible();
  });

  test('should add and remove order items', async ({ page }) => {
    await page.goto('/orders/create');
    
    await page.fill('[id="client_id"]', '1');
    
    // Add an additional item
    await page.click('button:has-text("Add Item")');
    
    // Should now have 2 item rows
    const itemRows = page.locator('.grid.grid-cols-12');
    await expect(itemRows).toHaveCount(2);
    
    // Fill both items with names
    const productNameInputs = page.locator('input[placeholder="Product name"]');
    await productNameInputs.nth(0).fill('First Item');
    await productNameInputs.nth(1).fill('Second Item');
    
    // Remove the second item
    const removeButtons = page.locator('button').filter({ has: page.locator('[data-lucide="x"]') });
    if ((await removeButtons.count()) > 0) {
      await removeButtons.last().click();
      
      // Should be back to 1 item
      await expect(itemRows).toHaveCount(1);
    }
  });

  test('should handle validation errors properly', async ({ page }) => {
    await page.goto('/orders/create');
    
    // Try to submit with missing required fields
    await page.click('button[type="submit"]');
    
    // Should show validation errors or stay on the same page
    await expect(page).toHaveURL(/\/orders\/create$/);
    
    // Check for HTML5 validation or error messages
    const clientIdInput = page.locator('[id="client_id"]');
    await expect(clientIdInput).toBeVisible();
  });

  test('should copy billing address to shipping address', async ({ page }) => {
    await page.goto('/orders/create');
    
    // Fill billing address first  
    const billingFirstName = page.locator('input').filter({ hasText: 'First Name' }).first();
    await billingFirstName.fill('Copy');
    
    const billingLastName = page.locator('input').filter({ hasText: 'Last Name' }).first();
    await billingLastName.fill('Test');
    
    const billingStreet = page.locator('input').filter({ hasText: 'Street Address' }).first();
    await billingStreet.fill('456 Copy Street');
    
    const billingCity = page.locator('input').filter({ hasText: 'City' }).first();
    await billingCity.fill('Brno');
    
    const billingPostal = page.locator('input').filter({ hasText: 'Postal Code' }).first();
    await billingPostal.fill('60000');
    
    // Copy to shipping
    await page.click('button:has-text("Copy from billing")');
    
    // Check that shipping address was populated
    const shippingInputs = page.locator('input').filter({ hasText: 'First Name' });
    await expect(shippingInputs.nth(1)).toHaveValue('Copy');
    
    const shippingLastNames = page.locator('input').filter({ hasText: 'Last Name' });
    await expect(shippingLastNames.nth(1)).toHaveValue('Test');
  });

  test('should verify no Svelte reactivity warnings in form', async ({ page }) => {
    // Monitor console for reactivity warnings
    const reactivityWarnings = [];
    page.on('console', msg => {
      if (msg.text().includes('binding_property_non_reactive') ||
          msg.text().includes('effect_update_depth_exceeded')) {
        reactivityWarnings.push(msg.text());
      }
    });

    await page.goto('/orders/create');
    
    // Interact with form fields to trigger reactivity
    await page.fill('[id="client_id"]', '1');
    await page.fill('[id="notes"]', 'Testing reactivity');
    
    // Add and interact with order items
    await page.fill('input[placeholder="Product name"]', 'Reactivity Test');
    await page.fill('input[type="number"]', '5');
    
    // Wait for any effects to settle
    await page.waitForTimeout(1000);
    
    // Verify no reactivity warnings were logged
    expect(reactivityWarnings.length).toBe(0);
  });
});