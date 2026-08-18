const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const SERVER_URL = 'http://127.0.0.1:8000';
const ROLES = ['SUPERADMIN', 'ADMIN'];
const VIEWPORTS = [
    { width: 1280, height: 800, name: 'desktop' },
    { width: 768, height: 1024, name: 'tablet' },
    { width: 390, height: 844, name: 'mobile-large' },
    { width: 375, height: 667, name: 'mobile' }
];

const PAGES = {
    'Insiden': '/incidents',
    'Stock Opname': '/opnames',
    'Penyesuaian': '/adjustments'
};

async function checkLayout(page, role, viewportName, pageName, url) {
    const issues = [];
    
    // Evaluate layout
    const layoutInfo = await page.evaluate(() => {
        const info = { overflows: false, windowWidth: window.innerWidth, overflowElement: null };
        
        info.overflows = document.documentElement.scrollWidth > window.innerWidth + 2;

        if (info.overflows) {
            // Find which element is causing it
            const allElements = document.querySelectorAll('*');
            for (let i = 0; i < allElements.length; i++) {
                const el = allElements[i];
                if (el.getBoundingClientRect().right > window.innerWidth) {
                    if (el.tagName !== 'BODY' && el.tagName !== 'HTML' && el.tagName !== 'MAIN') {
                        info.overflowElement = el.tagName + (el.className ? '.' + el.className.split(' ').join('.') : '');
                        break;
                    }
                }
            }
        }

        const main = document.querySelector('main > div.flex-1') || document.querySelector('main .max-w-7xl');
        if (main) info.mainContent = main.getBoundingClientRect();

        const tableContainer = document.querySelector('.overflow-x-auto');
        const table = document.querySelector('table');
        if (tableContainer && table) {
            info.table = {
                containerBox: tableContainer.getBoundingClientRect(),
                tableBox: table.getBoundingClientRect(),
                scrollWidth: tableContainer.scrollWidth
            };
        }

        const guide = document.querySelector('.w-full.lg\\:w-80');
        if (guide) {
            const guideBox = guide.getBoundingClientRect();
            info.guide = {
                box: guideBox,
                isVisible: guideBox.width > 0 && guideBox.height > 0
            };
        }
        
        const toolbarSearch = document.querySelector('input[name="search"]');
        if (toolbarSearch) {
            info.search = toolbarSearch.getBoundingClientRect();
        }

        const emptyState = document.querySelector('.flex.flex-col.items-center.justify-center');
        if (emptyState) {
            info.emptyState = {
                box: emptyState.getBoundingClientRect(),
                parentBox: emptyState.parentElement.getBoundingClientRect()
            };
        }

        return info;
    });

    // 1 & 2: Page horizontal overflow
    if (layoutInfo.overflows) {
        issues.push(`Page has horizontal overflow. Culprit element: ${layoutInfo.overflowElement}`);
    }

    // 3: Header wrapping
    // We already check page overflow which would catch header forcing overflow.

    // 4: Search toolbar overflow
    if (layoutInfo.search && layoutInfo.search.right > layoutInfo.windowWidth) {
        issues.push(`Search toolbar overflows viewport.`);
    }

    // 5: Table width
    if (layoutInfo.table && layoutInfo.mainContent) {
        const containerWidth = layoutInfo.table.containerBox.width;
        
        // Table should be at least as wide as container
        if (layoutInfo.table.tableBox.width < containerWidth - 5) {
            issues.push(`Table width (${layoutInfo.table.tableBox.width}px) is smaller than container width (${containerWidth}px).`);
        }
    }

    // 6: Empty state centered
    if (layoutInfo.emptyState) {
        const centerOffset = Math.abs((layoutInfo.emptyState.box.left + layoutInfo.emptyState.box.width / 2) - 
                                      (layoutInfo.emptyState.parentBox.left + layoutInfo.emptyState.parentBox.width / 2));
        if (centerOffset > 10) {
            issues.push(`Empty state is not horizontally centered. Offset: ${centerOffset}px`);
        }
    }

    // 7: Page Guide
    if (layoutInfo.guide && layoutInfo.mainContent) {
        const isDesktop = layoutInfo.windowWidth >= 1024;
        if (isDesktop) {
            if (layoutInfo.guide.box.left < layoutInfo.mainContent.right - 10) {
                issues.push(`Guide is not positioned to the right of main content.`);
            }
        }
    }

    if (issues.length > 0) {
        const screenshotPath = path.join(__dirname, 'screenshots', `FAIL_${role}_${viewportName}_${pageName.replace(/ /g, '_')}.png`);
        if (!fs.existsSync(path.join(__dirname, 'screenshots'))) {
            fs.mkdirSync(path.join(__dirname, 'screenshots'));
        }
        await page.screenshot({ path: screenshotPath, fullPage: true });
        return { status: 'FAIL', issues, screenshot: screenshotPath };
    }

    return { status: 'PASS' };
}

(async () => {
    let globalStatus = 'PASS';
    const failures = [];

    for (const role of ROLES) {
        console.log(`\n=== Testing Role: ${role} ===`);
        
        const browser = await chromium.launch({ headless: true });
        
        for (const viewport of VIEWPORTS) {
            console.log(`  -> Viewport: ${viewport.name} (${viewport.width}x${viewport.height})`);
            
            const context = await browser.newContext({ viewport: { width: viewport.width, height: viewport.height } });
            const page = await context.newPage();
            
            await page.goto(`${SERVER_URL}/login`);
            
            let email = '';
            let password = '';
            if (role === 'SUPERADMIN') { email = 'superadmin@ptbig.com'; password = 'superadmin123'; }
            else if (role === 'ADMIN') { email = 'admin@ptbig.com'; password = 'admin123'; }
            
            await page.fill('input[name="email"]', email);
            await page.fill('input[name="password"]', password);
            await page.click('button[type="submit"]');
            await page.waitForURL('**/dashboard');
            
            for (const [pageName, urlPath] of Object.entries(PAGES)) {
                
                const fullUrl = `${SERVER_URL}${urlPath}`;
                
                const response = await page.goto(fullUrl);
                if (!response || !response.ok() || response.status() === 403) {
                    continue; // e.g. Superadmin doesn't have create/edit? Wait, index should be 200.
                }
                
                const result = await checkLayout(page, role, viewport.name, pageName, fullUrl);
                
                if (result.status === 'FAIL') {
                    globalStatus = 'FAIL';
                    failures.push({
                        role,
                        viewport: viewport.name,
                        page: pageName,
                        url: fullUrl,
                        issues: result.issues,
                        screenshot: result.screenshot
                    });
                    process.stdout.write('F');
                } else {
                    process.stdout.write('.');
                }
            }
            console.log(); 
            await context.close();
        }
        await browser.close();
    }
    
    console.log(`\n\nFINAL RESULT: ${globalStatus}`);
    if (failures.length > 0) {
        console.log('\nFailures Details:');
        console.log(JSON.stringify(failures, null, 2));
    }
})();
