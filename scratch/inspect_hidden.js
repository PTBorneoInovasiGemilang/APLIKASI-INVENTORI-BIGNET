const { chromium, devices } = require('playwright');
const fs = require('fs');
const iPhone13 = devices['iPhone 13'];

(async () => {
    const appPhpPath = 'resources/views/layouts/app.php';
    let appPhp = fs.readFileSync(appPhpPath, 'utf8');
    
    // Remove overflow-x-clip from main
    appPhp = appPhp.replace('main class="flex-1 overflow-x-clip', 'main class="flex-1 overflow-x-hidden');
    fs.writeFileSync(appPhpPath, appPhp);

    try {
        const browser = await chromium.launch({ headless: true });
        
        // Desktop Wheel Test
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
        const mainScroll = await page1.evaluate(() => document.querySelector('main').scrollTop);
        console.log('WITH OVERFLOW-X-HIDDEN on MAIN:');
        console.log('window.scrollY:', wheelY);
        console.log('main.scrollTop:', mainScroll);
        
        await context1.close();
        await browser.close();
    } finally {
        // Restore
        appPhp = fs.readFileSync(appPhpPath, 'utf8');
        appPhp = appPhp.replace('main class="flex-1 overflow-x-hidden', 'main class="flex-1 overflow-x-clip');
        fs.writeFileSync(appPhpPath, appPhp);
    }
})();
