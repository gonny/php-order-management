import { test, expect } from '@playwright/test';

test.describe('Svelte Form Reactivity Tests', () => {
    test.beforeEach(async ({ page }) => {
        // Navigate to login page and authenticate
        await page.goto('/login');
        await page.fill('[name="email"]', 'test@example.com');
        await page.fill('[name="password"]', 'Passw0rd1!');
        await page.click('button[type="submit"]');
        
        // Wait for redirect to dashboard
        await page.waitForURL('/dashboard');
    });

    test('should create client without reactivity errors', async ({ page }) => {
        // Navigate to client creation
        await page.goto('/clients/create');
        
        // Verify form elements are present
        await expect(page.getByLabel('First Name *')).toBeVisible();
        await expect(page.getByLabel('Last Name *')).toBeVisible();
        await expect(page.getByLabel('Email Address *')).toBeVisible();
        
        // Fill out the form - should not trigger reactivity warnings
        await page.fill('[id="first_name"]', 'Test');
        await page.fill('[id="last_name"]', 'User');
        await page.fill('[id="email"]', 'test-user@example.com');
        await page.fill('[id="phone"]', '+420123456789');
        await page.fill('[id="company"]', 'Test Company');
        
        // Check that values are properly bound (reactive)
        await expect(page.locator('[id="first_name"]')).toHaveValue('Test');
        await expect(page.locator('[id="last_name"]')).toHaveValue('User');
        await expect(page.locator('[id="email"]')).toHaveValue('test-user@example.com');
        
        // Check console for errors
        const consoleErrors = [];
        page.on('console', msg => {
            if (msg.type() === 'error' || msg.text().includes('binding_property_non_reactive') || msg.text().includes('effect_update_depth_exceeded')) {
                consoleErrors.push(msg.text());
            }
        });
        
        // Submit the form
        await page.click('button[type="submit"]');
        
        // Wait for redirect or success
        await page.waitForTimeout(2000);
        
        // Verify no reactivity errors occurred
        expect(consoleErrors.length).toBe(0);
    });

    test('should create order without reactivity errors', async ({ page }) => {
        // Navigate to order creation
        await page.goto('/orders/create');
        
        // Verify form elements are present
        await expect(page.getByLabel('Client ID')).toBeVisible();
        await expect(page.getByText('Order Items')).toBeVisible();
        
        // Fill basic information
        await page.fill('[id="client_id"]', '1');
        await page.fill('[id="notes"]', 'Test order notes');
        
        // Fill billing address
        await page.fill('input[placeholder="Test Company"]', 'Test Company'); // Look for company field
        const firstNameInputs = page.locator('input').filter({ hasText: /first.*name/i }).or(page.locator('label:has-text("First Name") + input'));
        await firstNameInputs.first().fill('John');
        
        // Check console for errors
        const consoleErrors = [];
        page.on('console', msg => {
            if (msg.type() === 'error' || msg.text().includes('binding_property_non_reactive') || msg.text().includes('effect_update_depth_exceeded')) {
                consoleErrors.push(msg.text());
            }
        });
        
        // Interact with form elements to trigger reactivity
        await page.fill('[id="client_id"]', '2');
        await page.waitForTimeout(1000);
        
        // Verify no reactivity errors occurred
        expect(consoleErrors.length).toBe(0);
    });
});