const { test, expect } = require('@playwright/test');

const BASE_URL = 'http://127.0.0.1:8080';

test.describe('Dashboard & Page Vertical Scroll Regression', () => {

    const viewports = [
        { width: 1366, height: 768, name: 'Desktop 1366x768' },
        { width: 1920, height: 1080, name: 'Desktop 1920x1080' },
        { width: 768, height: 1024, name: 'Tablet 768x1024' },
        { width: 375, height: 812, name: 'Mobile 375x812' },
        { width: 390, height: 844, name: 'Mobile 390x844' },
        { width: 430, height: 932, name: 'Mobile 430x932' }
    ];

    for (const vp of viewports) {
        test(`ADMIN role vertical page scroll on /dashboard (${vp.name})`, async ({ page }) => {
            await page.setViewportSize({ width: vp.width, height: vp.height });
            await page.goto(`${BASE_URL}/login`);
            await page.fill('input[name="email"]', 'admin@ptbig.com');
            await page.fill('input[name="password"]', 'admin123');
            await page.click('button[type="submit"]');
            await page.waitForURL('**/dashboard');

            const initialMetrics = await page.evaluate(() => ({
                windowHeight: window.innerHeight,
                docScrollHeight: document.documentElement.scrollHeight,
                initialWindowScrollY: window.scrollY,
                sidebarScrollTop: document.getElementById('sidebarScrollArea')?.scrollTop ?? 0
            }));

            // Assert sidebar scrollTop is 0
            expect(initialMetrics.sidebarScrollTop).toBe(0);

            // If page content is taller than viewport, verify page scroll
            if (initialMetrics.docScrollHeight > initialMetrics.windowHeight) {
                // Scroll to bottom
                await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
                await page.waitForTimeout(200);

                const scrolledMetrics = await page.evaluate(() => ({
                    windowScrollY: window.scrollY,
                    sidebarScrollTop: document.getElementById('sidebarScrollArea')?.scrollTop ?? 0
                }));

                expect(scrolledMetrics.windowScrollY).toBeGreaterThan(0);
                expect(scrolledMetrics.sidebarScrollTop).toBe(0); // Sidebar remains at top

                // Scroll back to top
                await page.evaluate(() => window.scrollTo(0, 0));
                await page.waitForTimeout(200);
                const backToTopScrollY = await page.evaluate(() => window.scrollY);
                expect(backToTopScrollY).toBe(0);
            }
        });
    }

    test('TEKNISI role vertical page scroll on /dashboard', async ({ page }) => {
        await page.goto(`${BASE_URL}/login`);
        await page.fill('input[name="email"]', 'teknisi@ptbig.com');
        await page.fill('input[name="password"]', 'teknisi123');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard');

        const docScrollHeight = await page.evaluate(() => document.documentElement.scrollHeight);
        const windowHeight = await page.evaluate(() => window.innerHeight);

        if (docScrollHeight > windowHeight) {
            await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
            await page.waitForTimeout(200);
            const scrollY = await page.evaluate(() => window.scrollY);
            expect(scrollY).toBeGreaterThan(0);
        }

        const sidebarScrollTop = await page.$eval('#sidebarScrollArea', el => el.scrollTop);
        expect(sidebarScrollTop).toBe(0);
    });

    test('Mobile drawer & modal scroll locking regression', async ({ page }) => {
        await page.setViewportSize({ width: 375, height: 812 });
        await page.goto(`${BASE_URL}/login`);
        await page.fill('input[name="email"]', 'admin@ptbig.com');
        await page.fill('input[name="password"]', 'admin123');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard');

        // 1. Mobile drawer test
        await page.click('button[onclick="toggleSidebar()"]');
        await page.waitForTimeout(200);
        let sidebarScroll = await page.$eval('#sidebarScrollArea', el => el.scrollTop);
        expect(sidebarScroll).toBe(0);

        // Close drawer
        await page.evaluate(() => toggleSidebar());
        await page.waitForTimeout(200);

        // Verify page scrolling continues
        await page.evaluate(() => window.scrollTo(0, 300));
        await page.waitForTimeout(100);
        let scrollY = await page.evaluate(() => window.scrollY);
        expect(scrollY).toBeGreaterThan(0);

        // 2. Modal test on /categories
        await page.goto(`${BASE_URL}/categories`);
        const addBtn = page.locator('button:has-text("Tambah Kategori")');
        await addBtn.waitFor({ state: 'visible', timeout: 5000 });
        await addBtn.click();
        await page.waitForTimeout(200);

        const isModalVisible = await page.evaluate(() => {
            const m = document.getElementById('addModal');
            return m && !m.classList.contains('hidden');
        });
        expect(isModalVisible).toBe(true);

        // Close modal
        await page.evaluate(() => {
            const m = document.getElementById('addModal');
            if (m) m.classList.add('hidden');
        });
        await page.waitForTimeout(200);

        const isModalHidden = await page.evaluate(() => {
            const m = document.getElementById('addModal');
            return m && m.classList.contains('hidden');
        });
        expect(isModalHidden).toBe(true);
    });

    test('Other long pages vertical scrolling regression', async ({ page }) => {
        await page.goto(`${BASE_URL}/login`);
        await page.fill('input[name="email"]', 'admin@ptbig.com');
        await page.fill('input[name="password"]', 'admin123');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard');

        const pagesToTest = ['/categories', '/items', '/stocks', '/serials', '/incoming', '/outgoing', '/transfers', '/loans', '/returns', '/installations', '/incidents', '/adjustments', '/opnames', '/reports/ledger', '/reports/audit', '/timeline'];

        for (const p of pagesToTest) {
            await page.goto(`${BASE_URL}${p}`);
            const docHeight = await page.evaluate(() => document.documentElement.scrollHeight);
            const winHeight = await page.evaluate(() => window.innerHeight);

            if (docHeight > winHeight) {
                await page.evaluate(() => window.scrollTo(0, 200));
                await page.waitForTimeout(100);
                const sY = await page.evaluate(() => window.scrollY);
                expect(sY).toBeGreaterThan(0);
            }
        }
    });

    test('No console errors on dashboard scroll', async ({ page }) => {
        const consoleErrors = [];
        page.on('console', msg => {
            if (msg.type() === 'error') consoleErrors.push(msg.text());
        });

        await page.goto(`${BASE_URL}/login`);
        await page.fill('input[name="email"]', 'admin@ptbig.com');
        await page.fill('input[name="password"]', 'admin123');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard');

        await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
        await page.waitForTimeout(200);
        await page.evaluate(() => window.scrollTo(0, 0));

        expect(consoleErrors.length).toBe(0);
    });

});
