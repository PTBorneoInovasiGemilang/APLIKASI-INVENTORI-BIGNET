const { chromium, devices } = require('playwright');
const fs = require('fs');
const iPhone13 = devices['iPhone 13'];

(async () => {
    const appPhpPath = 'resources/views/layouts/app.php';
    let appPhp = fs.readFileSync(appPhpPath, 'utf8');
    
    // Remove overflow-x-clip from main
    appPhp = appPhp.replace('main class="flex-1 overflow-x-clip', 'main class="flex-1 ');
    fs.writeFileSync(appPhpPath, appPhp);

    try {
        const browser = await chromium.launch({ headless: true });
        
        const context2 = await browser.newContext({
            ...iPhone13,
            hasTouch: true,
            isMobile: true
        });
        const page2 = await context2.newPage();
        await page2.goto('http://127.0.0.1:8001/login');
        await page2.fill('input[name="email"]', 'admin@ptbig.com');
        await page2.fill('input[name="password"]', 'admin123');
        await page2.click('button[type="submit"]');
        await page2.waitForURL('**/dashboard');
        
        // Wait for page to render fully
        await page2.waitForTimeout(1000);

        // Check horizontal overflow
        const overflowResult = await page2.evaluate(() => {
            return {
                docScrollWidth: document.documentElement.scrollWidth,
                windowInnerWidth: window.innerWidth,
                bodyScrollWidth: document.body.scrollWidth,
                mainScrollWidth: document.querySelector('main').scrollWidth
            };
        });
        console.log('Mobile Dashboard Horizontal Overflow:', overflowResult);
        
        // Let's test a few other pages just in case
        const pagesToTest = ['/categories', '/incidents', '/opnames'];
        for(let p of pagesToTest) {
            await page2.goto('http://127.0.0.1:8001' + p);
            await page2.waitForTimeout(500);
            const res = await page2.evaluate(() => {
                return {
                    docScrollWidth: document.documentElement.scrollWidth,
                    windowInnerWidth: window.innerWidth
                };
            });
            console.log('Mobile', p, 'Horizontal Overflow:', res);
        }

        await context2.close();
        await browser.close();
    } finally {
        // Restore
        appPhp = fs.readFileSync(appPhpPath, 'utf8');
        appPhp = appPhp.replace('main class="flex-1  bg-gray-50', 'main class="flex-1 overflow-x-clip bg-gray-50');
        fs.writeFileSync(appPhpPath, appPhp);
    }
})();
