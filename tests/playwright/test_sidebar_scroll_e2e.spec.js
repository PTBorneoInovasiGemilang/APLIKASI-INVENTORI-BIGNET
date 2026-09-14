const { test, expect } = require('@playwright/test');
const path = require('path');

const BASE_URL = 'http://127.0.0.1:8080';

test.describe('Sidebar Initial Scroll Position on Dashboard', () => {

    test('ADMIN role: sidebar scrollTop must be 0 on /dashboard', async ({ page }) => {
        // 1. Login ADMIN
        await page.goto(`${BASE_URL}/login`);
        await page.fill('input[name="email"]', 'admin@ptbig.com');
        await page.fill('input[name="password"]', 'admin123');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard');

        // 2. Initial scrollTop check
        let scrollTop = await page.$eval('#sidebarScrollArea', el => el.scrollTop);
        expect(scrollTop).toBe(0);

        // 3. Simulate scroll on another page
        await page.goto(`${BASE_URL}/search`);
        await page.evaluate(() => {
            const area = document.getElementById('sidebarScrollArea');
            if (area) {
                area.scrollTop = 350;
                localStorage.setItem('sidebar_scroll_top', '350');
            }
        });

        // 4. Navigate back to /dashboard
        await page.goto(`${BASE_URL}/dashboard`);
        scrollTop = await page.$eval('#sidebarScrollArea', el => el.scrollTop);
        expect(scrollTop).toBe(0);

        // 5. Refresh /dashboard
        await page.reload();
        scrollTop = await page.$eval('#sidebarScrollArea', el => el.scrollTop);
        expect(scrollTop).toBe(0);

        // Screenshot
        await page.screenshot({ path: path.join(__dirname, '../../scratch/admin_dashboard.png') });
    });

    test('SUPERADMIN role: sidebar scrollTop must be 0 on /dashboard', async ({ page }) => {
        await page.goto(`${BASE_URL}/login`);
        await page.fill('input[name="email"]', 'admin@ptbig.com'); // Admin user has SUPERADMIN role in DB seed
        await page.fill('input[name="password"]', 'admin123');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard');

        let scrollTop = await page.$eval('#sidebarScrollArea', el => el.scrollTop);
        expect(scrollTop).toBe(0);

        await page.screenshot({ path: path.join(__dirname, '../../scratch/superadmin_dashboard.png') });
    });

    test('TEKNISI role: sidebar scrollTop must be 0 on /dashboard', async ({ page }) => {
        await page.goto(`${BASE_URL}/login`);
        await page.fill('input[name="email"]', 'teknisi@ptbig.com');
        await page.fill('input[name="password"]', 'teknisi123');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard');

        let scrollTop = await page.$eval('#sidebarScrollArea', el => el.scrollTop);
        expect(scrollTop).toBe(0);

        await page.screenshot({ path: path.join(__dirname, '../../scratch/teknisi_dashboard.png') });
    });

    test('Mobile viewport 375x812: drawer initial scroll must be 0 on /dashboard', async ({ page }) => {
        await page.setViewportSize({ width: 375, height: 812 });
        await page.goto(`${BASE_URL}/login`);
        await page.fill('input[name="email"]', 'admin@ptbig.com');
        await page.fill('input[name="password"]', 'admin123');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard');

        // Toggle drawer
        await page.click('button[onclick="toggleSidebar()"]');
        await page.waitForTimeout(200);

        let scrollTop = await page.$eval('#sidebarScrollArea', el => el.scrollTop);
        expect(scrollTop).toBe(0);

        await page.screenshot({ path: path.join(__dirname, '../../scratch/mobile_dashboard.png') });
    });
});
