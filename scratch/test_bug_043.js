const { chromium } = require('playwright');
const assert = require('assert');

(async () => {
    let success = true;
    const browser = await chromium.launch();
    
    async function testRole(role, email, pass) {
        console.log(`\n=== Testing ${role} ===`);
        const context = await browser.newContext();
        const page = await context.newPage();
        const errors = [];
        page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
        page.on('pageerror', err => errors.push(err.message));
        
        await page.goto('http://127.0.0.1:8001/login');
        await page.fill('input[name="email"]', email);
        await page.fill('input[name="password"]', pass);
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard');
        
        // Regression test other reports
        const reports = [
            '/reports',
            '/reports/audit',
            '/reports/ledger',
            '/reports/serials',
            '/reports/submissions',
            '/reports/incoming',
            '/reports/outgoing',
            '/reports/transfers',
            '/reports/loans',
            '/reports/returns',
            '/reports/installations',
            '/reports/adjustments'
        ];
        
        for (const url of reports) {
            const res = await page.goto(`http://127.0.0.1:8001${url}`, { waitUntil: 'domcontentloaded' });
            if (res.status() !== 200) {
                console.log(`FAIL: ${url} returned ${res.status()}`);
                success = false;
            }
        }
        
        // Deep test transfers
        await page.goto('http://127.0.0.1:8001/reports/transfers');
        
        // Test search
        await page.fill('input[name="search"]', 'TEST');
        await Promise.all([
            page.waitForNavigation(),
            page.evaluate(() => document.querySelector('form').submit())
        ]);
        let res = await page.evaluate(() => document.body.innerText.includes('Fatal error') || document.body.innerText.includes('PDOException'));
        if (res) { console.log('FAIL: Search caused PHP error'); success = false; }
        
        // Reset
        await page.goto('http://127.0.0.1:8001/reports/transfers');
        
        // Test Export CSV
        const [downloadCSV] = await Promise.all([
            page.waitForEvent('download'),
            page.click('button[name="export"][value="csv"]')
        ]);
        if (!downloadCSV.suggestedFilename().endsWith('.csv')) {
            console.log('FAIL: CSV export failed'); success = false;
        }
        
        // Test Export PDF
        const [downloadPDF] = await Promise.all([
            page.waitForEvent('download'),
            page.click('button[name="export"][value="pdf"]')
        ]);
        if (!downloadPDF.suggestedFilename().endsWith('.pdf')) {
            console.log('FAIL: PDF export failed'); success = false;
        }

        if (errors.length > 0) {
            console.log('FAIL: Console errors found', errors);
            success = false;
        } else {
            console.log(`${role} tests PASSED`);
        }
        
        await context.close();
    }
    
    await testRole('SUPERADMIN', 'superadmin@ptbig.com', 'superadmin123');
    await testRole('ADMIN', 'admin@ptbig.com', 'admin123');
    
    await browser.close();
    
    if (success) {
        console.log("\nALL TESTS PASSED");
    } else {
        console.log("\nSOME TESTS FAILED");
    }
})();
