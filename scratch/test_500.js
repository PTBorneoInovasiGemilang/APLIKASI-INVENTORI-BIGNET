const { chromium } = require('playwright');
(async () => {
    const browser = await chromium.launch();
    const context = await browser.newContext();
    const page = await context.newPage();
    await page.goto('http://127.0.0.1:8001/login');
    await page.fill('input[name="email"]', 'superadmin@ptbig.com');
    await page.fill('input[name="password"]', 'superadmin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
    
    // Attempt reports/transfers
    const res = await page.goto('http://127.0.0.1:8001/reports/transfers');
    console.log(`Status: ${res.status()}`);
    if (res.status() === 500) {
        const text = await page.evaluate(() => document.body.innerText);
        console.log(`Error body:\n${text}`);
    }
    await browser.close();
})();
