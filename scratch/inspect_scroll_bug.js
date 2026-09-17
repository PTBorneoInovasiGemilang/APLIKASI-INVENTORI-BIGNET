const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage({ viewport: { width: 1366, height: 768 } });
    
    // Login
    await page.goto('http://127.0.0.1:8001/login');
    await page.fill('input[name="email"]', 'admin@ptbig.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');

    console.log('=== BEFORE WHEEL ===');
    const diagBefore = await page.evaluate(() => {
        const main = document.querySelector('main');
        const sidebar = document.getElementById('sidebarScrollArea');
        const targetEl = document.elementFromPoint(500, 300);
        return {
            windowInnerHeight: window.innerHeight,
            docScrollHeight: document.documentElement.scrollHeight,
            bodyScrollHeight: document.body.scrollHeight,
            windowScrollY: window.scrollY,
            htmlComputedOverflow: getComputedStyle(document.documentElement).overflow,
            bodyComputedOverflow: getComputedStyle(document.body).overflow,
            bodyComputedHeight: getComputedStyle(document.body).height,
            main: {
                clientHeight: main ? main.clientHeight : null,
                scrollHeight: main ? main.scrollHeight : null,
                scrollTop: main ? main.scrollTop : null,
                computedOverflow: main ? getComputedStyle(main).overflow : null,
                computedOverflowX: main ? getComputedStyle(main).overflowX : null,
                computedOverflowY: main ? getComputedStyle(main).overflowY : null,
                computedPosition: main ? getComputedStyle(main).position : null,
                computedHeight: main ? getComputedStyle(main).height : null,
            },
            elementFromPoint: targetEl ? {
                tagName: targetEl.tagName,
                id: targetEl.id,
                className: targetEl.className
            } : null
        };
    });
    console.log(JSON.stringify(diagBefore, null, 2));

    // Try real mouse wheel at x: 500, y: 300
    await page.mouse.move(500, 300);
    await page.mouse.wheel(0, 500);
    await page.waitForTimeout(500);

    console.log('=== AFTER WHEEL (0, 500) ===');
    const scrollYAfter = await page.evaluate(() => window.scrollY);
    const mainScrollTopAfter = await page.evaluate(() => document.querySelector('main')?.scrollTop);
    console.log('window.scrollY:', scrollYAfter);
    console.log('main.scrollTop:', mainScrollTopAfter);

    await browser.close();
})();
