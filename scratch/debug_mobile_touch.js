const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch({ headless: true });
    
    // Test 1: isMobile: true, hasTouch: true
    const mobileContext = await browser.newContext({
        viewport: { width: 375, height: 812 },
        hasTouch: true,
        isMobile: true
    });
    const page = await mobileContext.newPage();
    await page.goto('http://127.0.0.1:8080/login');
    await page.fill('input[name="email"]', 'admin@ptbig.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
    await page.waitForTimeout(500);

    console.log('Mobile initial scrollY:', await page.evaluate(() => window.scrollY));

    // Try wheel on mobile context
    await page.mouse.move(200, 400);
    await page.mouse.wheel(0, 400);
    await page.waitForTimeout(300);
    console.log('Mobile scrollY after mouse.wheel:', await page.evaluate(() => window.scrollY));

    // Try touchscreen drag (down at 400, move to 100)
    // In CDP / Playwright touchscreen
    await page.touchscreen.tap(200, 400);
    await page.waitForTimeout(100);

    // Let's inspect element at (200, 400) on mobile
    const el = await page.evaluate(() => {
        const target = document.elementFromPoint(200, 400);
        return {
            tagName: target ? target.tagName : null,
            id: target ? target.id : null,
            className: target ? target.className : null,
            computedOverflowY: target ? getComputedStyle(target).overflowY : null,
            computedTouchAction: target ? getComputedStyle(target).touchAction : null
        };
    });
    console.log('Element at (200, 400) on mobile:', el);

    await browser.close();
})();
