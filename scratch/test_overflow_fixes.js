const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage({ viewport: { width: 1366, height: 768 } });
    
    await page.goto('http://127.0.0.1:8080/login');
    await page.fill('input[name="email"]', 'admin@ptbig.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');

    console.log('--- TEST 1: Current computed styles ---');
    let res = await page.evaluate(() => {
        const main = document.querySelector('main');
        return {
            overflowX: getComputedStyle(main).overflowX,
            overflowY: getComputedStyle(main).overflowY,
            overflow: getComputedStyle(main).overflow
        };
    });
    console.log('Current main styles:', res);

    console.log('--- TEST 2: Setting style overflowX = "clip", overflowY = "visible" ---');
    res = await page.evaluate(() => {
        const main = document.querySelector('main');
        main.style.overflowX = 'clip';
        main.style.overflowY = 'visible';
        return {
            overflowX: getComputedStyle(main).overflowX,
            overflowY: getComputedStyle(main).overflowY,
            overflow: getComputedStyle(main).overflow
        };
    });
    console.log('With overflow-x: clip, overflow-y: visible:', res);

    console.log('--- TEST 3: Setting style overflow = "visible" ---');
    res = await page.evaluate(() => {
        const main = document.querySelector('main');
        main.style.overflow = 'visible';
        return {
            overflowX: getComputedStyle(main).overflowX,
            overflowY: getComputedStyle(main).overflowY,
            overflow: getComputedStyle(main).overflow
        };
    });
    console.log('With overflow: visible:', res);

    await browser.close();
})();
