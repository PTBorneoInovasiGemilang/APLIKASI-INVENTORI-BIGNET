const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage({ viewport: { width: 1366, height: 768 } });
    
    await page.goto('http://127.0.0.1:8080/login');
    await page.fill('input[name="email"]', 'admin@ptbig.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');

    const metrics = await page.evaluate(() => {
        const getMetrics = (el, name) => {
            if (!el) return { name, exists: false };
            const cs = getComputedStyle(el);
            return {
                name,
                exists: true,
                tagName: el.tagName,
                id: el.id,
                className: el.className,
                position: cs.position,
                display: cs.display,
                overflow: cs.overflow,
                overflowX: cs.overflowX,
                overflowY: cs.overflowY,
                zIndex: cs.zIndex,
                pointerEvents: cs.pointerEvents,
                clientHeight: el.clientHeight,
                scrollHeight: el.scrollHeight,
                scrollTop: el.scrollTop,
                height: cs.height
            };
        };

        return {
            windowInnerHeight: window.innerHeight,
            windowScrollY: window.scrollY,
            docScrollHeight: document.documentElement.scrollHeight,
            bodyScrollHeight: document.body.scrollHeight,
            html: getMetrics(document.documentElement, 'html'),
            body: getMetrics(document.body, 'body'),
            sidebarOverlay: getMetrics(document.getElementById('sidebarOverlay'), '#sidebarOverlay'),
            sidebar: getMetrics(document.getElementById('sidebar'), '#sidebar'),
            sidebarScrollArea: getMetrics(document.getElementById('sidebarScrollArea'), '#sidebarScrollArea'),
            pageWrapper: getMetrics(document.querySelector('.lg\\:ml-64'), '.lg:ml-64'),
            header: getMetrics(document.querySelector('header'), 'header'),
            main: getMetrics(document.querySelector('main'), 'main'),
            footer: getMetrics(document.querySelector('footer'), 'footer')
        };
    });

    console.log(JSON.stringify(metrics, null, 2));
    await browser.close();
})();
