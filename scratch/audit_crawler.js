const { chromium, devices } = require('playwright');
const iPhone13 = devices['iPhone 13'];

(async () => {
    const browser = await chromium.launch({ headless: true });

    async function testRole(roleName, email, password, viewport, isMobile) {
        console.log(`\n=== Crawling as ${roleName} on ${viewport.width}x${viewport.height} ===`);
        const context = await browser.newContext({
            viewport: viewport,
            hasTouch: isMobile,
            isMobile: isMobile
        });
        const page = await context.newPage();
        const errors = [];
        const failedRequests = [];
        const consoleWarnings = [];

        page.on('console', msg => {
            if (msg.type() === 'error') errors.push(msg.text());
            if (msg.type() === 'warning') consoleWarnings.push(msg.text());
        });
        
        page.on('response', response => {
            if (response.status() >= 400 && response.status() < 600) {
                // Ignore font/image 404s for the sake of logic audit
                if (!response.url().endsWith('.woff2') && !response.url().endsWith('.png')) {
                    failedRequests.push(`${response.status()} ${response.url()}`);
                }
            }
        });

        await page.goto('http://127.0.0.1:8001/login');
        await page.fill('input[name="email"]', email);
        await page.fill('input[name="password"]', password);
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard');

        // Extract all sidebar links
        const links = await page.evaluate(() => {
            return Array.from(document.querySelectorAll('#sidebar a.sidebar-link'))
                .map(a => a.href)
                .filter(href => !href.includes('logout'));
        });

        console.log(`Found ${links.length} routes to check.`);

        for (const link of links) {
            try {
                await page.goto(link, { waitUntil: 'domcontentloaded' });
                
                // Check for PHP errors displayed on page
                const hasPhpError = await page.evaluate(() => {
                    return document.body.innerText.includes('Fatal error') || 
                           document.body.innerText.includes('Parse error') ||
                           document.body.innerText.includes('PDOException');
                });

                if (hasPhpError) {
                    errors.push(`PHP Error found on ${link}`);
                }
                
                // Check if any button labeled 'Tambah' or 'Create' exists and test its modal/form rendering
                const addBtn = await page.$('a.btn-primary, button.btn-primary');
                if (addBtn) {
                   // We won't click to avoid modifying data, but we verify it's present.
                }

            } catch (e) {
                errors.push(`Navigation failed to ${link}: ${e.message}`);
            }
        }
        
        console.log(`Finished ${roleName}. Errors: ${errors.length}, Failed Requests: ${failedRequests.length}`);
        if (errors.length > 0) console.log('Errors:', errors);
        if (failedRequests.length > 0) console.log('Failed Requests:', failedRequests);

        await context.close();
        return { errors, failedRequests };
    }

    const res1 = await testRole('SUPERADMIN', 'superadmin@ptbig.com', 'superadmin123', { width: 1366, height: 768 }, false);
    const res2 = await testRole('ADMIN', 'admin@ptbig.com', 'admin123', { width: 1366, height: 768 }, false);
    const res3 = await testRole('TEKNISI', 'teknisi@ptbig.com', 'teknisi123', { width: 375, height: 812 }, true);
    
    // Quick unauthorized test
    console.log(`\n=== Testing TEKNISI Unauthorized Access ===`);
    const context = await browser.newContext();
    const page = await context.newPage();
    await page.goto('http://127.0.0.1:8001/login');
    await page.fill('input[name="email"]', 'teknisi@ptbig.com');
    await page.fill('input[name="password"]', 'teknisi123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
    
    const response = await page.goto('http://127.0.0.1:8001/items'); // Admin route
    console.log(`Status for /items as TEKNISI: ${response.status()}`);
    
    await browser.close();
})();
