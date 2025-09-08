import { test, expect } from '@playwright/test';

test.describe('Order CRUD Operations', () => {
  let testClientId;
  
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

  test('should display orders index page', async ({ page }) => {
    await page.goto('/orders');
    
    // Check if the page loaded correctly
    await expect(page).toHaveTitle(/Orders.*Order Management/);
    await expect(page.locator('h1')).toContainText('Orders');
    
    // Check for the create button
    await expect(page.locator('a[href="/orders/create"]')).toBeVisible();
  });

  test('should create a new order', async ({ page }) => {
    await page.goto('/orders/create');
    
    // Check if the create form is visible
    await expect(page.locator('h1')).toContainText('Create New Order');
    
    // Get a client ID - for now, we'll use a simple approach
    // In a real test, you might create a client via API or find an existing one
    await page.fill('[id="client_id"]', '1'); // Assuming client ID 1 exists
    
    // Fill out basic order information
    await page.selectOption('select:has(option[value="new"])', 'new'); // Status
    await page.fill('textarea[id="notes"]', 'Test order created via E2E test');
    
    // Add order items
    const firstItemName = page.locator('input[placeholder="Product name"]').first();
    await firstItemName.fill('Test Product');
    
    const firstItemDescription = page.locator('input[placeholder="Description"]').first();
    await firstItemDescription.fill('Test product description');
    
    const firstItemQuantity = page.locator('input[type="number"][bind\\:value*="quantity"]').first();
    await firstItemQuantity.fill('2');
    
    const firstItemPrice = page.locator('input[type="number"][bind\\:value*="unit_price"]').first();
    await firstItemPrice.fill('500');
    
    // Trigger calculation by clicking outside or pressing tab
    await firstItemPrice.press('Tab');
    
    // Fill billing address
    await page.fill('input[bind\\:value*="billing_address.first_name"]', 'John');
    await page.fill('input[bind\\:value*="billing_address.last_name"]', 'Doe');
    await page.fill('input[bind\\:value*="billing_address.street"]', '123 Test Street');
    await page.fill('input[bind\\:value*="billing_address.city"]', 'Prague');
    await page.fill('input[bind\\:value*="billing_address.postal_code"]', '10000');
    await page.fill('input[bind\\:value*="billing_address.country"]', 'CZ');
    await page.fill('input[bind\\:value*="billing_address.email"]', 'billing@test.com');
    
    // Copy billing to shipping
    await page.click('button:has-text("Copy from billing")');
    
    // Submit the form
    await page.click('button[type="submit"]');
    
    // Should redirect to orders list
    await expect(page).toHaveURL(/\/orders$/);
    
    // Check for success message or that the order appears in the list
    await expect(page.locator('text=Test Product')).toBeVisible();
  });

  test('should edit an existing order', async ({ page }) => {
    // First, create an order to edit
    await page.goto('/orders/create');
    
    await page.fill('[id="client_id"]', '1');
    await page.fill('input[placeholder="Product name"]', 'Edit Test Product');
    await page.fill('input[placeholder="Description"]', 'Product to be edited');
    await page.fill('input[type="number"][bind\\:value*="quantity"]', '1');
    await page.fill('input[type="number"][bind\\:value*="unit_price"]', '100');
    
    // Fill required address fields
    await page.fill('input[bind\\:value*="billing_address.first_name"]', 'Edit');
    await page.fill('input[bind\\:value*="billing_address.last_name"]', 'Test');
    await page.fill('input[bind\\:value*="billing_address.street"]', '123 Edit Street');
    await page.fill('input[bind\\:value*="billing_address.city"]', 'Prague');
    await page.fill('input[bind\\:value*="billing_address.postal_code"]', '10000');
    await page.fill('input[bind\\:value*="billing_address.country"]', 'CZ');
    
    await page.click('button:has-text("Copy from billing")');
    await page.click('button[type="submit"]');
    
    // Wait for redirect
    await expect(page).toHaveURL(/\/orders$/);
    
    // Find and click the edit button for our test order
    const orderRow = page.locator('tr').filter({ hasText: 'Edit Test Product' }).first();
    await orderRow.locator('a[href*="/orders/"][href*="/edit"]').click();
    
    // Should be on edit page
    await expect(page.locator('h1')).toContainText('Edit Order');
    
    // Update the notes field
    await page.fill('textarea[id="notes"]', 'Updated order notes via E2E test');
    
    // Submit the form
    await page.click('button[type="submit"]');
    
    // Should redirect back to orders list
    await expect(page).toHaveURL(/\/orders$/);
    
    // Check that the update was successful
    await expect(page.locator('text=Updated order notes')).toBeVisible();
  });

  test('should view order details', async ({ page }) => {
    await page.goto('/orders');
    
    // Click on the first order's view/show link
    const firstOrderRow = page.locator('tbody tr').first();
    await firstOrderRow.locator('a[href*="/orders/"][href$!="/edit"]').first().click();
    
    // Should be on order details page
    await expect(page.locator('h1')).toContainText(/Order Details|View Order|Order/);
    
    // Check that order information is displayed
    await expect(page.locator('text=Status')).toBeVisible();
    await expect(page.locator('text=Total')).toBeVisible();
    await expect(page.locator('text=Items')).toBeVisible();
  });

  test('should handle order item calculations', async ({ page }) => {
    await page.goto('/orders/create');
    
    await page.fill('[id="client_id"]', '1');
    
    // Fill order item with specific values
    await page.fill('input[placeholder="Product name"]', 'Calculation Test');
    await page.fill('input[type="number"][bind\\:value*="quantity"]', '3');
    await page.fill('input[type="number"][bind\\:value*="unit_price"]', '250');
    
    // Trigger calculation
    await page.press('input[type="number"][bind\\:value*="unit_price"]', 'Tab');
    
    // Wait for calculation
    await page.waitForTimeout(500);
    
    // Check that total was calculated correctly (3 * 250 = 750)
    const totalInput = page.locator('input[readonly][value*="750"]');
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
    
    // Fill both items
    await page.fill('input[placeholder="Product name"]', 'First Item');
    await page.fill('input[placeholder="Product name"]', 'Second Item');
    
    // Remove the second item (if remove button is available)
    const removeButtons = page.locator('button:has([class*="x-"])'); // X icon button
    if ((await removeButtons.count()) > 0) {
      await removeButtons.last().click();
      
      // Should be back to 1 item
      await expect(itemRows).toHaveCount(1);
    }
  });

  test('should handle validation errors', async ({ page }) => {
    await page.goto('/orders/create');
    
    // Try to submit with missing required fields
    await page.click('button[type="submit"]');
    
    // Should show validation errors or stay on the same page
    await expect(page).toHaveURL(/\/orders\/create$/);
    
    // Fill in client ID and try again
    await page.fill('[id="client_id"]', '1');
    await page.click('button[type="submit"]');
    
    // Should still require item and address information
    await expect(page).toHaveURL(/\/orders\/create$/);
  });

  test('should copy billing address to shipping', async ({ page }) => {
    await page.goto('/orders/create');
    
    // Fill billing address
    await page.fill('input[bind\\:value*="billing_address.first_name"]', 'Copy');
    await page.fill('input[bind\\:value*="billing_address.last_name"]', 'Test');
    await page.fill('input[bind\\:value*="billing_address.street"]', '456 Copy Street');
    await page.fill('input[bind\\:value*="billing_address.city"]', 'Brno');
    await page.fill('input[bind\\:value*="billing_address.postal_code"]', '60000');
    
    // Copy to shipping
    await page.click('button:has-text("Copy from billing")');
    
    // Check that shipping address was populated
    await expect(page.locator('input[bind\\:value*="shipping_address.first_name"]')).toHaveValue('Copy');
    await expect(page.locator('input[bind\\:value*="shipping_address.last_name"]')).toHaveValue('Test');
    await expect(page.locator('input[bind\\:value*="shipping_address.street"]')).toHaveValue('456 Copy Street');
    await expect(page.locator('input[bind\\:value*="shipping_address.city"]')).toHaveValue('Brno');
    await expect(page.locator('input[bind\\:value*="shipping_address.postal_code"]')).toHaveValue('60000');
  });
});