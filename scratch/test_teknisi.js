const { chromium } = require('playwright');
(async () => {
    const browser = await chromium.launch();
    const context = await browser.newContext();
    const page = await context.newPage();
    await page.goto('http://127.0.0.1:8001/login');
    await page.fill('input[name="email"]', 'teknisi@ptbig.com');
    await page.fill('input[name="password"]', 'teknisi123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
    
    const routes = [
        '/dashboard',
        '/submissions',
        '/loans',
        '/returns',
        '/serials',
        '/installations/my_items',
        '/timeline',
        '/installations',
        '/installations/photos',
        '/incidents/create'
    ];
    
    for (const route of routes) {
        const res = await page.goto(`http://127.0.0.1:8001${route}`);
        console.log(`TEKNISI ${route} : ${res.status()}`);
    }
    await browser.close();
})();
