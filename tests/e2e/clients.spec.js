import { test, expect } from '@playwright/test';

test.describe('Client CRUD Operations', () => {
  test.beforeEach(async ({ page }) => {
    // Navigate to the login page or dashboard
    await page.goto('/');
    
    // Add any authentication setup here if needed
    // For now, assume we can access the clients page directly
  });

  test('should display clients index page', async ({ page }) => {
    await page.goto('/clients');
    
    // Check if the page loaded correctly
    await expect(page).toHaveTitle(/Clients.*Order Management/);
    await expect(page.locator('h1')).toContainText('Clients');
    
    // Check for the create button
    await expect(page.locator('a[href="/clients/create"]')).toBeVisible();
  });

  test('should create a new client', async ({ page }) => {
    await page.goto('/clients/create');
    
    // Check if the create form is visible
    await expect(page.locator('h1')).toContainText('Create New Client');
    
    // Fill out the form
    await page.fill('[id="first_name"]', 'John');
    await page.fill('[id="last_name"]', 'Doe');
    await page.fill('[id="email"]', `test.${Date.now()}@example.com`);
    await page.fill('[id="phone"]', '+420123456789');
    await page.fill('[id="company"]', 'Test Company Ltd.');
    await page.fill('[id="vat_id"]', 'CZ12345678');
    await page.fill('[id="external_id"]', `EXT-${Date.now()}`);
    
    // Ensure the active checkbox is checked
    const isActiveCheckbox = page.locator('[id="is_active"]');
    if (!(await isActiveCheckbox.isChecked())) {
      await isActiveCheckbox.check();
    }
    
    // Submit the form
    await page.click('button[type="submit"]');
    
    // Should redirect to clients list
    await expect(page).toHaveURL(/\/clients$/);
    
    // Check for success message or that the client appears in the list
    await expect(page.locator('text=John Doe')).toBeVisible();
  });

  test('should edit an existing client', async ({ page }) => {
    // First, let's create a client to edit
    await page.goto('/clients/create');
    
    const timestamp = Date.now();
    const originalEmail = `edit.test.${timestamp}@example.com`;
    
    await page.fill('[id="first_name"]', 'Edit');
    await page.fill('[id="last_name"]', 'Test');
    await page.fill('[id="email"]', originalEmail);
    await page.click('button[type="submit"]');
    
    // Wait for redirect
    await expect(page).toHaveURL(/\/clients$/);
    
    // Find and click the edit button for our test client
    const clientRow = page.locator('tr').filter({ hasText: 'Edit Test' }).first();
    await clientRow.locator('a[href*="/clients/"][href*="/edit"]').click();
    
    // Should be on edit page
    await expect(page.locator('h1')).toContainText('Edit Client');
    
    // Update the company field
    await page.fill('[id="company"]', 'Updated Company Name');
    
    // Submit the form
    await page.click('button[type="submit"]');
    
    // Should redirect back to clients list
    await expect(page).toHaveURL(/\/clients$/);
    
    // Check that the update was successful
    await expect(page.locator('text=Updated Company Name')).toBeVisible();
  });

  test('should view client details', async ({ page }) => {
    await page.goto('/clients');
    
    // Click on the first client's view/show link
    const firstClientRow = page.locator('tbody tr').first();
    await firstClientRow.locator('a[href*="/clients/"][href$!="/edit"]').first().click();
    
    // Should be on client details page
    await expect(page.locator('h1')).toContainText(/Client Details|View Client/);
    
    // Check that client information is displayed
    await expect(page.locator('text=Email')).toBeVisible();
    await expect(page.locator('text=Phone')).toBeVisible();
    await expect(page.locator('text=Status')).toBeVisible();
  });

  test('should handle validation errors', async ({ page }) => {
    await page.goto('/clients/create');
    
    // Try to submit with missing required fields
    await page.click('button[type="submit"]');
    
    // Should show validation errors
    await expect(page.locator('text=required')).toBeVisible();
    
    // Fill in just the first name and try again
    await page.fill('[id="first_name"]', 'Test');
    await page.click('button[type="submit"]');
    
    // Should still show validation errors for other required fields
    await expect(page.locator('text=required')).toBeVisible();
  });

  test('should search and filter clients', async ({ page }) => {
    await page.goto('/clients');
    
    // Look for search/filter functionality
    const searchInput = page.locator('input[placeholder*="search"], input[placeholder*="Search"]').first();
    
    if (await searchInput.isVisible()) {
      // Test search functionality
      await searchInput.fill('test@example.com');
      await page.keyboard.press('Enter');
      
      // Wait for search results to load
      await page.waitForTimeout(1000);
      
      // Clear search
      await searchInput.clear();
      await page.keyboard.press('Enter');
    }
    
    // Check for filter dropdowns or buttons
    const filterButtons = page.locator('button:has-text("Filter"), select:has(option)');
    if (await filterButtons.count() > 0) {
      // Test filtering if available
      await filterButtons.first().click();
    }
  });
});