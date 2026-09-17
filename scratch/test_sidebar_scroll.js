const { chromium, devices } = require('playwright');
const iPhone13 = devices['iPhone 13'];

(async () => {
    const browser = await chromium.launch({ headless: true });
    
    // Desktop Context
    const context = await browser.newContext({ viewport: { width: 1366, height: 768 } });
    const page = await context.newPage();
    
    // Login
    await page.goto('http://127.0.0.1:8001/login');
    await page.fill('input[name="email"]', 'admin@ptbig.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
    await page.waitForTimeout(500);

    console.log('--- PHASE 2: INSPECT ---');
    const css = await page.evaluate(() => {
        const el = document.getElementById('sidebarScrollArea');
        const comp = getComputedStyle(el);
        return {
            scrollHeight: el.scrollHeight,
            clientHeight: el.clientHeight,
            scrollTop: el.scrollTop,
            overflow: comp.overflow,
            overflowY: comp.overflowY,
            height: comp.height,
            maxHeight: comp.maxHeight,
            minHeight: comp.minHeight,
            position: comp.position,
            display: comp.display,
            flex: comp.flex,
            flexDirection: comp.flexDirection,
        };
    });
    console.log(css);

    console.log('--- PHASE 3: MOUSE WHEEL ---');
    await page.mouse.move(120, 700);
    await page.mouse.wheel(0, 600);
    await page.waitForTimeout(500);

    const scrollTopAfter = await page.evaluate(() => document.getElementById('sidebarScrollArea').scrollTop);
    console.log('sidebar.scrollTop after wheel:', scrollTopAfter);

    console.log('--- PHASE 4: EVENT OWNER ---');
    const elFromPoint = await page.evaluate(() => {
        const el = document.elementFromPoint(120, 700);
        return el ? el.className : 'null';
    });
    console.log('Element from point (120, 700):', elFromPoint);

    await browser.close();
})();
