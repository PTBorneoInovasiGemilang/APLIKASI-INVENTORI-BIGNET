const { chromium } = require('playwright');
const fs = require('fs');

(async () => {
    const browser = await chromium.launch();
    const context = await browser.newContext({ acceptDownloads: true });
    const page = await context.newPage();

    // Login
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('input[name="email"]', 'superadmin@ptbig.com');
    await page.fill('input[name="password"]', 'superadmin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');

    const downloadPdf = async (url, filename) => {
        const [download] = await Promise.all([
            page.waitForEvent('download'),
            page.goto(url)
        ]);
        await download.saveAs(`tests/artifacts/${filename}`);
        console.log(`Saved: tests/artifacts/${filename}`);
    };

    await downloadPdf('http://127.0.0.1:8000/reports/outgoing?export=pdf', 'export_outgoing.pdf');
    await downloadPdf('http://127.0.0.1:8000/reports/adjustments?export=pdf', 'export_adjustments.pdf');
    await downloadPdf('http://127.0.0.1:8000/reports/stocks?export=pdf', 'export_stocks.pdf'); // Note: stocks is Inventory, ledger is mutasi

    // Mutasi is /reports/ledger
    await downloadPdf('http://127.0.0.1:8000/reports/ledger?export=pdf', 'export_ledger.pdf');

    await browser.close();
})();
