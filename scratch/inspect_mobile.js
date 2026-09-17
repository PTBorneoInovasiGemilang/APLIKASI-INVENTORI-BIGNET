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

    // Touch swipe gesture (drag from bottom to top)
    const { width, height } = page.viewportSize();
    await page.mouse.move(width / 2, height - 100);
    await page.mouse.down();
    await page.mouse.move(width / 2, 100, { steps: 10 });
    await page.mouse.up();
    await page.waitForTimeout(500);

    const scrollYAfter = await page.evaluate(() => window.scrollY);
    const mainScrollAfter = await page.evaluate(() => document.querySelector('main').scrollTop);
    const wrapperScrollAfter = await page.evaluate(() => document.querySelector('.lg\\:ml-64').scrollTop);
    const htmlScrollAfter = await page.evaluate(() => document.documentElement.scrollTop);
    console.log('window.scrollY:', scrollYAfter);
    console.log('html.scrollTop:', htmlScrollAfter);
    console.log('main.scrollTop:', mainScrollAfter);
    console.log('wrapper.scrollTop:', wrapperScrollAfter);

    await browser.close();
})();
