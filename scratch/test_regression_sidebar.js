const { chromium, devices } = require('playwright');
const iPhone13 = devices['iPhone 13'];
const iPhone12Pro = devices['iPhone 12 Pro'];

(async () => {
    const browser = await chromium.launch({ headless: true });

    async function testRole(roleName, email, password, viewport, isMobile) {
        console.log(`\n=== Testing ${roleName} on ${viewport.width}x${viewport.height} (Mobile: ${isMobile}) ===`);
        const context = await browser.newContext({
            viewport: viewport,
            hasTouch: isMobile,
            isMobile: isMobile
        });
        const page = await context.newPage();
        
        await page.goto('http://127.0.0.1:8001/login');
        await page.fill('input[name="email"]', email);
        await page.fill('input[name="password"]', password);
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard');
        await page.waitForTimeout(500);

        if (isMobile) {
            // Open sidebar
            await page.click('button[aria-label="Toggle menu"]');
            await page.waitForTimeout(500);

            // Test sidebar touch scroll
            await page.mouse.move(viewport.width / 2, viewport.height / 2);
            await page.mouse.wheel(0, 500);
            await page.waitForTimeout(200);
            const sidebarScroll = await page.evaluate(() => document.getElementById('sidebarScrollArea').scrollTop);
            console.log('Mobile sidebar scroll after wheel:', sidebarScroll);

            // Test dashboard scroll (should not scroll while sidebar is open/handling touch... wait, on mobile sidebar overlay is active)
            
            // Close sidebar
            await page.click('#sidebarOverlay');
            await page.waitForTimeout(500);
        } else {
            // Desktop
            // 1. Sidebar scroll down
            await page.mouse.move(120, 500); // Over sidebar
            await page.mouse.wheel(0, 600);
            await page.waitForTimeout(200);
            const sidebarScroll = await page.evaluate(() => document.getElementById('sidebarScrollArea').scrollTop);
            console.log('Desktop sidebar scroll after wheel down:', sidebarScroll);

            // 2. Sidebar scroll up
            await page.mouse.wheel(0, -600);
            await page.waitForTimeout(200);
            const sidebarScrollUp = await page.evaluate(() => document.getElementById('sidebarScrollArea').scrollTop);
            console.log('Desktop sidebar scroll after wheel up:', sidebarScrollUp);

            // 3. Dashboard scroll
            await page.mouse.move(500, 500); // Over dashboard
            await page.mouse.wheel(0, 500);
            await page.waitForTimeout(200);
            const dashboardScroll = await page.evaluate(() => window.scrollY);
            console.log('Desktop dashboard scroll after wheel:', dashboardScroll);

            const errors = [];
            page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()) });
            if (errors.length > 0) console.log('Errors:', errors);
        }
        await context.close();
    }

    await testRole('SUPERADMIN', 'superadmin@ptbig.com', 'superadmin123', { width: 1366, height: 768 }, false);
    await testRole('ADMIN', 'admin@ptbig.com', 'admin123', { width: 375, height: 812 }, true);
    await testRole('TEKNISI', 'teknisi@ptbig.com', 'teknisi123', { width: 390, height: 844 }, true);

    await browser.close();
})();
