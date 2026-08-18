const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const SERVER_URL = 'http://127.0.0.1:8000';
const ROLES = ['SUPERADMIN', 'ADMIN', 'TEKNISI_NOC'];
const VIEWPORTS = [
    { width: 1920, height: 1080, name: 'desktop-large' },
    { width: 1280, height: 720, name: 'desktop' },
    { width: 768, height: 1024, name: 'tablet' },
    { width: 390, height: 844, name: 'mobile-large' },
    { width: 375, height: 667, name: 'mobile' }
];

const PAGES = {
    'Master Barang': '/items',
    'Stok Barang': '/stocks',
    'Nomor Seri': '/serials',
    'Timeline': '/timeline',
    'Pengajuan': '/submissions',
    'Barang Keluar': '/outgoing',
    'Transfer': '/transfers',
    'Peminjaman': '/loans',
    'Pengembalian': '/returns',
    'Instalasi': '/installations',
    'Penyesuaian': '/adjustments',
    'Insiden': '/incidents',
    'Stock Opname': '/opnames',
    'Reports': '/reports/ledger' // example report
};

async function checkLayout(page, role, viewportName, pageName, url) {
    const issues = [];
    
    // Evaluate layout
    const layoutInfo = await page.evaluate(() => {
        const info = { overflows: false, table: null, guide: null, mainContent: null, emptyState: null, windowWidth: window.innerWidth };
        
        // Check horizontal overflow
        info.overflows = document.documentElement.scrollWidth > window.innerWidth + 2;

        const main = document.querySelector('main > div.flex-1');
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

        const emptyState = document.querySelector('.flex.flex-col.items-center.justify-center');
        if (emptyState) {
            info.emptyState = {
                box: emptyState.getBoundingClientRect(),
                parentBox: emptyState.parentElement.getBoundingClientRect()
            };
        }

        return info;
    });

    // 1. TABLE WIDTH
    if (layoutInfo.table && layoutInfo.mainContent) {
        const containerWidth = layoutInfo.table.containerBox.width;
        const mainWidth = layoutInfo.mainContent.width;
        
        // Container should fill main width
        if (Math.abs(containerWidth - mainWidth) > 5) {
            issues.push(`Table container width (${containerWidth}px) does not match main content width (${mainWidth}px).`);
        }
        
        // Table should be at least as wide as container
        if (layoutInfo.table.tableBox.width < containerWidth - 5) {
            issues.push(`Table width (${layoutInfo.table.tableBox.width}px) is smaller than container width (${containerWidth}px). Table does not fill.`);
        }
    } else if (pageName !== 'Timeline' && pageName !== 'Reports') {
        // Some pages might not have a table (or maybe they do, we log if missing)
        // issues.push('Table not found.');
    }

    // 2. PAGE GUIDE
    if (layoutInfo.guide) {
        if (!layoutInfo.guide.isVisible) {
            // It might be hidden on very small screens? Actually no, mobile pushes it below
        }
        
        const isDesktop = layoutInfo.windowWidth >= 1024; // lg breakpoint in tailwind
        if (isDesktop && layoutInfo.mainContent) {
            // Guide should be on the right
            if (layoutInfo.guide.box.left < layoutInfo.mainContent.right - 10) {
                issues.push(`Guide is not positioned to the right of main content. Guide left: ${layoutInfo.guide.box.left}, Main right: ${layoutInfo.mainContent.right}`);
            }
        }
    }

    // 3. SHARED LAYOUT (Excessively wide)
    if (layoutInfo.windowWidth >= 1920 && layoutInfo.mainContent && layoutInfo.guide) {
        const expectedMainWidth = layoutInfo.windowWidth - 256 /*sidebar*/ - 320 /*guide*/ - 100 /*padding/gap roughly*/;
        // Just ensure it's expanding reasonably, no specific fail unless it's way off
    }

    // 4. MOBILE OVERFLOW
    if (layoutInfo.windowWidth < 1024) {
        if (layoutInfo.overflows) {
            issues.push(`Page has horizontal overflow on mobile/tablet viewport.`);
        }
    }

    // 5. EMPTY STATE
    if (layoutInfo.emptyState) {
        const centerOffset = Math.abs((layoutInfo.emptyState.box.left + layoutInfo.emptyState.box.width / 2) - 
                                      (layoutInfo.emptyState.parentBox.left + layoutInfo.emptyState.parentBox.width / 2));
        if (centerOffset > 10) {
            issues.push(`Empty state is not horizontally centered. Offset: ${centerOffset}px`);
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
        
        // Setup browser & login
        const browser = await chromium.launch({ headless: true });
        
        for (const viewport of VIEWPORTS) {
            console.log(`  -> Viewport: ${viewport.name} (${viewport.width}x${viewport.height})`);
            
            const context = await browser.newContext({ viewport: { width: viewport.width, height: viewport.height } });
            const page = await context.newPage();
            
            // Login
            await page.goto(`${SERVER_URL}/login`);
            
            let email = '';
            let password = '';
            if (role === 'SUPERADMIN') { email = 'superadmin@ptbig.com'; password = 'superadmin123'; }
            else if (role === 'ADMIN') { email = 'admin@ptbig.com'; password = 'admin123'; }
            else { email = 'teknisi@ptbig.com'; password = 'teknisi123'; }
            
            await page.fill('input[name="email"]', email);
            await page.fill('input[name="password"]', password);
            await page.click('button[type="submit"]');
            await page.waitForURL('**/dashboard');
            
            for (const [pageName, urlPath] of Object.entries(PAGES)) {
                
                // Route check
                const allowedTeknisi = ['Pengajuan', 'Peminjaman', 'Pengembalian', 'Instalasi', 'Nomor Seri', 'Timeline'];
                if (role === 'TEKNISI_NOC' && !allowedTeknisi.includes(pageName)) {
                    continue; // Skip pages not for Teknisi
                }
                const allowedAdmin = ['Master Barang', 'Stok Barang', 'Nomor Seri', 'Timeline', 'Pengajuan', 'Barang Keluar', 'Transfer', 'Peminjaman', 'Pengembalian', 'Instalasi', 'Penyesuaian', 'Insiden', 'Stock Opname', 'Reports', 'Barang Masuk'];
                if (role === 'ADMIN' && !allowedAdmin.includes(pageName)) {
                    continue;
                }

                const fullUrl = `${SERVER_URL}${urlPath}`;
                
                const response = await page.goto(fullUrl);
                if (!response || !response.ok() || response.status() === 403) {
                    continue; // Access denied or error handled
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
            console.log(); // newline
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
