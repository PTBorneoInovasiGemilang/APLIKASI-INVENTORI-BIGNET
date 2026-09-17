const { chromium, devices } = require('playwright');
const iPhone13 = devices['iPhone 13'];

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        ...iPhone13,
        hasTouch: true,
        isMobile: true
    });
    const page = await context.newPage();
    
    // Login
    await page.goto('http://127.0.0.1:8001/login');
    await page.fill('input[name="email"]', 'admin@ptbig.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
    await page.waitForTimeout(500);

    const { width, height } = page.viewportSize();
    await page.mouse.move(width / 2, height / 2);
    await page.mouse.wheel(0, 500);
    await page.waitForTimeout(500);

    const scrollYAfter = await page.evaluate(() => window.scrollY);
    console.log('MOBILE WHEEL scrollY:', scrollYAfter);

    await browser.close();
})();
