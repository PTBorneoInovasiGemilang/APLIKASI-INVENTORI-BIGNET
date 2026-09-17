const { chromium, devices } = require('playwright');
const fs = require('fs');
const iPhone13 = devices['iPhone 13'];

(async () => {
    // Modify app.php
    const appPhpPath = 'resources/views/layouts/app.php';
    let appPhp = fs.readFileSync(appPhpPath, 'utf8');
    appPhp = appPhp.replace('overflow-x-clip', '');
    fs.writeFileSync(appPhpPath, appPhp);

    try {
        const browser = await chromium.launch({ headless: true });
        
        // 1. Desktop Wheel Test
        const context1 = await browser.newContext();
        const page1 = await context1.newPage({ viewport: { width: 1366, height: 768 } });
        await page1.goto('http://127.0.0.1:8001/login');
        await page1.fill('input[name="email"]', 'admin@ptbig.com');
        await page1.fill('input[name="password"]', 'admin123');
        await page1.click('button[type="submit"]');
        await page1.waitForURL('**/dashboard');
        
        await page1.mouse.move(500, 300);
        await page1.mouse.wheel(0, 500);
        await page1.waitForTimeout(500);
        const wheelY = await page1.evaluate(() => window.scrollY);
        console.log('DESKTOP WHEEL scrollY:', wheelY);
        await context1.close();

        // 2. Mobile Swipe Test
        const context2 = await browser.newContext({
            ...iPhone13,
            hasTouch: true,
            isMobile: true
        });
        const page2 = await context2.newPage();
        await page2.goto('http://127.0.0.1:8001/dashboard');
        await page2.waitForTimeout(500);
        
        const { width, height } = page2.viewportSize();
        await page2.mouse.move(width / 2, height - 100);
        await page2.mouse.down();
        await page2.mouse.move(width / 2, 100, { steps: 10 });
        await page2.mouse.up();
        await page2.waitForTimeout(500);

        const swipeY = await page2.evaluate(() => window.scrollY);
        console.log('MOBILE SWIPE scrollY:', swipeY);
        
        await context2.close();
        await browser.close();
    } finally {
        // Restore app.php
        appPhp = fs.readFileSync(appPhpPath, 'utf8');
        appPhp = appPhp.replace('main class="flex-1  bg-gray-50', 'main class="flex-1 overflow-x-clip bg-gray-50');
        fs.writeFileSync(appPhpPath, appPhp);
    }
})();
