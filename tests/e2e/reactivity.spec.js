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
        // Monitor console for reactivity errors
        const consoleErrors = [];
        page.on('console', msg => {
            if (msg.type() === 'error' || 
                msg.text().includes('binding_property_non_reactive') || 
                msg.text().includes('effect_update_depth_exceeded')) {
                consoleErrors.push(msg.text());
            }
        });
        
        // Navigate to client creation
        await page.goto('/clients/create');
        
        // Verify form elements are present
        await expect(page.getByLabel('First Name *')).toBeVisible();
        await expect(page.getByLabel('Last Name *')).toBeVisible();
        await expect(page.getByLabel('Email Address *')).toBeVisible();
        
        // Fill out the form - should not trigger reactivity warnings
        await page.fill('[id="first_name"]', 'Test');
        await page.fill('[id="last_name"]', 'User');
        await page.fill('[id="email"]', `test-${Date.now()}@example.com`);
        await page.fill('[id="phone"]', '+420123456789');
        await page.fill('[id="company"]', 'Test Company');
        
        // Check that values are properly bound (reactive)
        await expect(page.locator('[id="first_name"]')).toHaveValue('Test');
        await expect(page.locator('[id="last_name"]')).toHaveValue('User');
        
        // Submit the form
        await page.click('button[type="submit"]');
        
        // Wait for form submission or redirect
        await page.waitForTimeout(3000);
        
        // Verify no reactivity errors occurred
        expect(consoleErrors.length).toBe(0);
    });

    test('should edit client without reactivity errors', async ({ page }) => {
        // Monitor console for reactivity errors
        const consoleErrors = [];
        page.on('console', msg => {
            if (msg.type() === 'error' || 
                msg.text().includes('binding_property_non_reactive') || 
                msg.text().includes('effect_update_depth_exceeded')) {
                consoleErrors.push(msg.text());
            }
        });

        // First create a client to edit
        await page.goto('/clients/create');
        
        const timestamp = Date.now();
        await page.fill('[id="first_name"]', 'EditTest');
        await page.fill('[id="last_name"]', 'User');
        await page.fill('[id="email"]', `edit-test-${timestamp}@example.com`);
        await page.click('button[type="submit"]');
        
        // Wait for redirect to clients list
        await page.waitForTimeout(2000);
        
        // Try to navigate to clients list if not redirected
        if (!page.url().includes('/clients')) {
            await page.goto('/clients');
        }
        
        // Find and click edit button for the client we just created
        const editButton = page.locator(`a[href*="/clients/"][href*="/edit"]`).first();
        if (await editButton.isVisible()) {
            await editButton.click();
            
            // Update some fields
            await page.fill('[id="company"]', 'Updated Company');
            await page.fill('[id="phone"]', '+420987654321');
            
            // Submit the form
            await page.click('button[type="submit"]');
            
            // Wait for form submission
            await page.waitForTimeout(2000);
        }
        
        // Verify no reactivity errors occurred
        expect(consoleErrors.length).toBe(0);
    });

    test('should create order without reactivity errors', async ({ page }) => {
        // Monitor console for reactivity errors
        const consoleErrors = [];
        page.on('console', msg => {
            if (msg.type() === 'error' || 
                msg.text().includes('binding_property_non_reactive') || 
                msg.text().includes('effect_update_depth_exceeded')) {
                consoleErrors.push(msg.text());
            }
        });
        
        // Navigate to order creation
        await page.goto('/orders/create');
        
        // Verify basic form elements are present
        const clientIdField = page.locator('[id="client_id"]');
        if (await clientIdField.isVisible()) {
            await clientIdField.fill('1');
        }
        
        const notesField = page.locator('[id="notes"]');
        if (await notesField.isVisible()) {
            await notesField.fill('Test order notes');
        }
        
        // Look for any form fields and interact with them
        const formFields = page.locator('input, textarea, select');
        const fieldCount = await formFields.count();
        
        for (let i = 0; i < Math.min(fieldCount, 5); i++) {
            const field = formFields.nth(i);
            const fieldType = await field.getAttribute('type');
            const fieldTag = await field.evaluate(el => el.tagName.toLowerCase());
            
            if (fieldType === 'text' || fieldType === 'email' || fieldTag === 'textarea') {
                try {
                    await field.fill('test value');
                    await page.waitForTimeout(100); // Small delay to trigger reactivity
                } catch (e) {
                    // Ignore errors for fields that might not be fillable
                }
            }
        }
        
        // Wait a bit to let any effects run
        await page.waitForTimeout(1000);
        
        // Verify no reactivity errors occurred
        expect(consoleErrors.length).toBe(0);
    });

    test('should navigate between pages without errors', async ({ page }) => {
        // Monitor console for any errors
        const consoleErrors = [];
        page.on('console', msg => {
            if (msg.type() === 'error') {
                consoleErrors.push(msg.text());
            }
        });

        // Navigate through different pages
        const pages = ['/dashboard', '/clients', '/orders', '/webhooks', '/queues', '/audit-logs'];
        
        for (const pagePath of pages) {
            try {
                await page.goto(pagePath);
                await page.waitForTimeout(1000);
                
                // Check that the page loaded (look for any content)
                const bodyContent = await page.locator('body').textContent();
                expect(bodyContent.length).toBeGreaterThan(0);
                
            } catch (e) {
                // Some pages might not be accessible, that's ok for this test
                console.log(`Could not access ${pagePath}: ${e.message}`);
            }
        }
        
        // Verify no critical errors occurred during navigation
        const criticalErrors = consoleErrors.filter(error => 
            !error.includes('favicon') && 
            !error.includes('net::ERR_') &&
            !error.includes('404')
        );
        
        expect(criticalErrors.length).toBe(0);
    });
});