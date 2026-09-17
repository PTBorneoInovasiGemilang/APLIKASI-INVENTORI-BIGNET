const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch({ headless: true });
    
    const viewports = [
        { width: 1920, height: 1080, name: '1920x1080' },
        { width: 1366, height: 768, name: '1366x768' },
        { width: 1024, height: 768, name: '1024x768' },
        { width: 768, height: 1024, name: '768x1024' },
        { width: 390, height: 844, name: '390x844' },
        { width: 375, height: 812, name: '375x812' }
    ];

    const routes = [
        '/loans',
        '/submissions',
        '/outgoing',
        '/transfers',
        '/reports/transfers'
    ];

    const context = await browser.newContext();
    const page = await context.newPage();

    // Login as SUPERADMIN first
    await page.goto('http://127.0.0.1:8001/login');
    await page.fill('input[name="username"]', 'superadmin');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');

    let allPass = true;

    for (const route of routes) {
        console.log(`\nTesting Route: ${route}`);
        for (const vp of viewports) {
            await page.setViewportSize({ width: vp.width, height: vp.height });
            await page.goto(`http://127.0.0.1:8001${route}`);
            
            // Wait a bit for render
            await page.waitForTimeout(500);

            // Check body overflow
            const hasBodyOverflow = await page.evaluate(() => {
                return document.documentElement.scrollWidth > window.innerWidth;
            });

            // Check if guide forces table narrow (measure table wrapper width vs table width)
            const tableWidthInfo = await page.evaluate(() => {
                const table = document.querySelector('table');
                if (!table) return null;
                const wrapper = table.parentElement;
                return {
                    tableWidth: table.scrollWidth,
                    wrapperWidth: wrapper.clientWidth,
                    overflowsWrapper: table.scrollWidth > wrapper.clientWidth
                };
            });
            
            const guidePos = await page.evaluate(() => {
                const guide = document.querySelector('.sticky.top-6');
                if (!guide) return 'none';
                const rect = guide.getBoundingClientRect();
                return rect.top > 300 ? 'bottom' : 'right';
            });

            console.log(`  Viewport: ${vp.name} -> BodyOverflow: ${hasBodyOverflow ? 'FAIL' : 'PASS'} | GuidePos: ${guidePos} | TableScrollsInsideWrapper: ${tableWidthInfo ? tableWidthInfo.overflowsWrapper : 'N/A'}`);
            if (hasBodyOverflow) allPass = false;
        }
    }

    await browser.close();
    process.exit(allPass ? 0 : 1);
})();
