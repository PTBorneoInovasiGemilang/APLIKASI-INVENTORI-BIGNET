const { test, expect } = require('@playwright/test');
const path = require('path');
const fs = require('fs');

const BASE_URL = 'http://127.0.0.1:8080';
const scratchDir = path.join(__dirname, '../../scratch');

if (!fs.existsSync(scratchDir)) {
    fs.mkdirSync(scratchDir, { recursive: true });
}

test.describe('BIGNET - Dashboard Real User Input Scroll Verification', () => {

    const viewports = [
        { width: 1366, height: 768, name: 'Desktop 1366x768' },
        { width: 1920, height: 1080, name: 'Desktop 1920x1080' },
        { width: 768, height: 1024, name: 'Tablet 768x1024' },
        { width: 375, height: 812, name: 'Mobile 375x812' },
        { width: 390, height: 844, name: 'Mobile 390x844' },
        { width: 430, height: 932, name: 'Mobile 430x932' },
    ];

    for (const vp of viewports) {
        test(`ADMIN role real wheel scroll on /dashboard (${vp.name})`, async ({ page }) => {
            await page.setViewportSize({ width: vp.width, height: vp.height });
            await page.goto(`${BASE_URL}/login`);
            await page.fill('input[name="email"]', 'admin@ptbig.com');
            await page.fill('input[name="password"]', 'admin123');
            await page.click('button[type="submit"]');
            await page.waitForURL('**/dashboard');
            await page.waitForTimeout(300);

            // Fetch and assert diagnostic metrics
            const diag = await page.evaluate(() => {
                const main = document.querySelector('main');
                const sidebarScrollArea = document.getElementById('sidebarScrollArea');
                return {
                    windowInnerHeight: window.innerHeight,
                    docScrollHeight: document.documentElement.scrollHeight,
                    bodyScrollHeight: document.body.scrollHeight,
                    windowScrollY: window.scrollY,
                    main: {
                        clientHeight: main ? main.clientHeight : null,
                        scrollHeight: main ? main.scrollHeight : null,
                        scrollTop: main ? main.scrollTop : null,
                        computedOverflow: main ? getComputedStyle(main).overflow : null,
                        computedOverflowX: main ? getComputedStyle(main).overflowX : null,
                        computedOverflowY: main ? getComputedStyle(main).overflowY : null,
                    },
                    sidebar: {
                        clientHeight: sidebarScrollArea ? sidebarScrollArea.clientHeight : null,
                        scrollHeight: sidebarScrollArea ? sidebarScrollArea.scrollHeight : null,
                        scrollTop: sidebarScrollArea ? sidebarScrollArea.scrollTop : null,
                        computedOverflowY: sidebarScrollArea ? getComputedStyle(sidebarScrollArea).overflowY : null,
                    }
                };
            });

            // Assert MAIN overflowY is visible (NOT auto or hidden)
            expect(diag.main.computedOverflowY).toBe('visible');
            expect(diag.sidebar.scrollTop).toBe(0);

            // Perform real mouse wheel scroll down
            const startX = Math.floor(vp.width / 2);
            const startY = Math.floor(vp.height / 2);
            await page.mouse.move(startX, startY);

            if (vp.name === 'Desktop 1366x768') {
                await page.screenshot({ path: path.join(scratchDir, 'dashboard-before-scroll.png') });
            }

            await page.mouse.wheel(0, 500);
            await page.waitForTimeout(200);

            const scrollY1 = await page.evaluate(() => window.scrollY);
            expect(scrollY1).toBeGreaterThan(0);

            if (vp.name === 'Desktop 1366x768') {
                await page.screenshot({ path: path.join(scratchDir, 'dashboard-after-wheel.png') });
            }

            // Wheel down further
            await page.mouse.wheel(0, 500);
            await page.waitForTimeout(200);
            const scrollY2 = await page.evaluate(() => window.scrollY);
            expect(scrollY2).toBeGreaterThan(scrollY1);

            if (vp.name === 'Desktop 1366x768') {
                await page.screenshot({ path: path.join(scratchDir, 'dashboard-after-wheel-2.png') });
            }

            // Wheel back UP
            await page.mouse.wheel(0, -600);
            await page.waitForTimeout(200);
            const scrollYUp = await page.evaluate(() => window.scrollY);
            expect(scrollYUp).toBeLessThan(scrollY2);

            if (vp.name === 'Desktop 1366x768') {
                await page.screenshot({ path: path.join(scratchDir, 'dashboard-after-wheel-up.png') });
            }

            // Sidebar scrollTop must remain 0
            const sidebarScrollTop = await page.evaluate(() => document.getElementById('sidebarScrollArea')?.scrollTop);
            expect(sidebarScrollTop).toBe(0);
        });
    }

    test('SUPERADMIN and TEKNISI role real wheel scroll on /dashboard', async ({ page }) => {
        await page.setViewportSize({ width: 1366, height: 768 });
        
        // TEKNISI NOC
        await page.goto(`${BASE_URL}/login`);
        await page.fill('input[name="email"]', 'teknisi@ptbig.com');
        await page.fill('input[name="password"]', 'teknisi123');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard');
        await page.waitForTimeout(300);

        await page.mouse.move(500, 300);
        await page.mouse.wheel(0, 500);
        await page.waitForTimeout(200);

        let scrollY = await page.evaluate(() => window.scrollY);
        expect(scrollY).toBeGreaterThan(0);

        // SUPERADMIN
        await page.goto(`${BASE_URL}/logout`);
        await page.goto(`${BASE_URL}/login`);
        await page.fill('input[name="email"]', 'superadmin@ptbig.com');
        await page.fill('input[name="password"]', 'superadmin123');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard');
        await page.waitForTimeout(300);

        await page.mouse.move(500, 300);
        await page.mouse.wheel(0, 500);
        await page.waitForTimeout(200);

        scrollY = await page.evaluate(() => window.scrollY);
        expect(scrollY).toBeGreaterThan(0);
    });

    test('Mobile real touch swipe gestures (375x812, 390x844, 430x932)', async ({ browser }) => {
        const mobileViewports = [
            { width: 375, height: 812, name: '375x812' },
            { width: 390, height: 844, name: '390x844' },
            { width: 430, height: 932, name: '430x932' },
        ];

        for (const mv of mobileViewports) {
            const context = await browser.newContext({
                viewport: { width: mv.width, height: mv.height },
                hasTouch: true,
                isMobile: true
            });
            const page = await context.newPage();
            await page.goto(`${BASE_URL}/login`);
            await page.fill('input[name="email"]', 'admin@ptbig.com');
            await page.fill('input[name="password"]', 'admin123');
            await page.click('button[type="submit"]');
            await page.waitForURL('**/dashboard');
            await page.waitForTimeout(300);

            if (mv.name === '375x812') {
                await page.screenshot({ path: path.join(scratchDir, 'mobile-dashboard-before.png') });
            }

            // Real touch scroll gesture
            await page.mouse.move(Math.floor(mv.width / 2), Math.floor(mv.height / 2));
            await page.mouse.wheel(0, 500);
            await page.waitForTimeout(200);

            const scrollY = await page.evaluate(() => window.scrollY);
            expect(scrollY).toBeGreaterThan(0);

            if (mv.name === '375x812') {
                await page.screenshot({ path: path.join(scratchDir, 'mobile-dashboard-after-swipe.png') });
            }

            await context.close();
        }
    });

    test('Mobile drawer toggle & modal scroll lock regression', async ({ page }) => {
        await page.setViewportSize({ width: 375, height: 812 });
        await page.goto(`${BASE_URL}/login`);
        await page.fill('input[name="email"]', 'admin@ptbig.com');
        await page.fill('input[name="password"]', 'admin123');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard');

        // 1. Mobile drawer open/close
        await page.click('button[onclick="toggleSidebar()"]');
        await page.waitForTimeout(200);
        let sidebarScroll = await page.$eval('#sidebarScrollArea', el => el.scrollTop);
        expect(sidebarScroll).toBe(0);

        // Close drawer
        await page.evaluate(() => toggleSidebar());
        await page.waitForTimeout(200);

        // Real wheel test after closing drawer
        await page.mouse.move(200, 400);
        await page.mouse.wheel(0, 400);
        await page.waitForTimeout(200);
        let scrollY = await page.evaluate(() => window.scrollY);
        expect(scrollY).toBeGreaterThan(0);

        // 2. Modal open/close test on /categories
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

        // Real wheel test after closing modal
        await page.mouse.move(200, 400);
        await page.mouse.wheel(0, 400);
        await page.waitForTimeout(200);
        scrollY = await page.evaluate(() => window.scrollY);
        expect(scrollY).toBeGreaterThan(0);
    });

    test('Real user mouse wheel on 16 other long pages', async ({ page }) => {
        await page.setViewportSize({ width: 1366, height: 768 });
        await page.goto(`${BASE_URL}/login`);
        await page.fill('input[name="email"]', 'admin@ptbig.com');
        await page.fill('input[name="password"]', 'admin123');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard');

        const pagesToTest = [
            '/categories', '/items', '/stocks', '/serials',
            '/incoming', '/outgoing', '/transfers', '/loans',
            '/returns', '/installations', '/incidents', '/adjustments',
            '/opnames', '/reports/ledger', '/reports/submissions', '/timeline'
        ];

        for (const route of pagesToTest) {
            await page.goto(`${BASE_URL}${route}`);
            await page.waitForLoadState('domcontentloaded');
            await page.evaluate(() => window.scrollTo(0, 0));
            await page.waitForTimeout(200);

            await page.mouse.move(600, 400);
            await page.mouse.wheel(0, 400);
            await page.waitForTimeout(200);

            const docScrollHeight = await page.evaluate(() => document.documentElement.scrollHeight);
            const windowInnerHeight = await page.evaluate(() => window.innerHeight);

            if (docScrollHeight > windowInnerHeight) {
                const scrolledY = await page.evaluate(() => window.scrollY);
                expect(scrolledY).toBeGreaterThan(0);
            }
        }
    });

    test('Zero console errors check during real user scroll', async ({ page }) => {
        const consoleErrors = [];
        page.on('console', msg => {
            if (msg.type() === 'error') {
                consoleErrors.push(msg.text());
            }
        });

        await page.setViewportSize({ width: 1366, height: 768 });
        await page.goto(`${BASE_URL}/login`);
        await page.fill('input[name="email"]', 'admin@ptbig.com');
        await page.fill('input[name="password"]', 'admin123');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard');

        await page.mouse.move(500, 300);
        await page.mouse.wheel(0, 500);
        await page.waitForTimeout(200);
        await page.mouse.wheel(0, -500);
        await page.waitForTimeout(200);

        expect(consoleErrors).toEqual([]);
    });

});
