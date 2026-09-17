const { chromium } = require('playwright');
(async () => {
    const browser = await chromium.launch();
    const context = await browser.newContext();
    const page = await context.newPage();
    await page.goto('http://127.0.0.1:8001/login');
    await page.fill('input[name="email"]', 'teknisi@ptbig.com');
    await page.fill('input[name="password"]', 'teknisi123');
    await page.click('button[type="submit"]');
    try {
        await page.waitForURL('**/dashboard', {timeout: 5000});
        console.log('success');
    } catch(e) {
        console.log('timeout. current url: ' + page.url());
        const text = await page.evaluate(() => document.body.innerText);
        console.log(text);
    }
    await browser.close();
})();
