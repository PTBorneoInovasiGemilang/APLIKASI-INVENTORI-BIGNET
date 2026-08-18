const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const users = {
    'SUPERADMIN': { email: 'superadmin@ptbig.com', pass: 'superadmin123' },
    'ADMIN': { email: 'admin@ptbig.com', pass: 'admin123' },
    'TEKNISI_NOC': { email: 'teknisi@ptbig.com', pass: 'teknisi123' }
};

const baseUrl = 'http://127.0.0.1:8000';
const results = [];

async function runTests() {
    if (!fs.existsSync('screenshots/uat')) {
        fs.mkdirSync('screenshots/uat', { recursive: true });
    }

    const browser = await chromium.launch({ headless: true });

    for (const [role, creds] of Object.entries(users)) {
        console.log(`\n=== Testing Role: ${role} ===`);
        const context = await browser.newContext({ viewport: { width: 1280, height: 720 } });
        const page = await context.newPage();

        let currentErrors = [];
        page.on('response', (response) => {
            const status = response.status();
            if (status >= 400 && response.request().resourceType() === 'document') {
                currentErrors.push({ url: response.url(), status });
            }
        });

        // Login
        await page.goto(`${baseUrl}/login`);
        await page.fill('input[name="email"]', creds.email);
        await page.fill('input[name="password"]', creds.pass);
        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');

        if (page.url().includes('login')) {
            console.error(`Failed to login as ${role}`);
            continue;
        }

        let routes = [];
        if (role === 'SUPERADMIN' || role === 'ADMIN') {
            routes = [
                { name: 'Dashboard', path: '/' },
                { name: 'Master Barang', path: '/items' },
                { name: 'Stok', path: '/stocks' },
                { name: 'Pengajuan', path: '/submissions' },
                { name: 'Barang Masuk', path: '/incoming' },
                { name: 'Barang Keluar', path: '/outgoing' },
                { name: 'Peminjaman', path: '/loans' },
                { name: 'Pengembalian', path: '/returns' },
                { name: 'Instalasi', path: '/installations' },
                { name: 'Penyesuaian', path: '/adjustments' },
                { name: 'Log Audit', path: '/audit' },
            ];
        } else if (role === 'TEKNISI_NOC') {
            routes = [
                { name: 'Dashboard', path: '/' },
                { name: 'Pengajuan', path: '/submissions' },
                { name: 'Peminjaman', path: '/loans' },
                { name: 'Pengembalian', path: '/returns' },
                { name: 'Instalasi', path: '/installations' }
            ];
        }

        for (const route of routes) {
            console.log(`  -> Visiting ${route.name} (${route.path})`);
            currentErrors = [];
            const url = `${baseUrl}${route.path}`;
            await page.goto(url);
            await page.waitForLoadState('networkidle');

            if (currentErrors.length > 0) {
                for (let err of currentErrors) {
                    const scName = `screenshots/uat/${role}_error_${err.status}_${route.name}.png`.replace(/\//g, '_');
                    await page.screenshot({ path: scName });
                    results.push({
                        role, menu: route.name, url: err.url, action: 'Load Page',
                        expected: 'HTTP 200', actual: `HTTP ${err.status}`, screenshot: scName
                    });
                }
            }

            const bodyText = await page.locator('body').innerText().catch(() => '');
            
            // 4. Check Raw Enums or TEST strings
            const badWords = ['INSTALLED', 'TEST', 'UAT', 'DEBUG', 'TODO', 'FIXME'];
            const found = badWords.filter(w => new RegExp(`\\b${w}\\b`).test(bodyText));
            if (found.length > 0 && route.path !== '/audit') { // ignore audit log which might naturally contain it
                const scName = `screenshots/uat/${role}_${route.name}_raw_enum.png`.replace(/\//g, '_');
                await page.screenshot({ path: scName });
                results.push({
                    role, menu: route.name, url, action: 'Check Raw Text/Enum',
                    expected: 'No Raw Text/Enum',
                    actual: `Found: ${found.join(', ')}`,
                    screenshot: scName
                });
            }

            // 1. Installations Specific
            if (route.path === '/installations') {
                if (!bodyText.includes('Terpasang') && bodyText.trim().length > 0) {
                    const hasTable = await page.locator('table').count() > 0;
                    if(hasTable) {
                        const trCount = await page.locator('tbody tr').count();
                        if(trCount > 0 && !bodyText.includes('Belum ada data')) {
                            const scName = `screenshots/uat/${role}_${route.name}_no_terpasang.png`.replace(/\//g, '_');
                            await page.screenshot({ path: scName });
                            results.push({
                                role, menu: route.name, url, action: 'Check Translation',
                                expected: 'Tampil "Terpasang"',
                                actual: `Could not find "Terpasang"`,
                                screenshot: scName
                            });
                        }
                    }
                }
            }

            // 2. Returns Specific
            if (route.path === '/returns') {
                const createBtnCount = await page.locator('a:has-text("Ajukan Pengembalian"), a[href*="/returns/create"]').count();
                
                if (role === 'TEKNISI_NOC') {
                    if (createBtnCount === 0) {
                        const scName = `screenshots/uat/${role}_returns_no_create_btn.png`;
                        await page.screenshot({ path: scName });
                        results.push({
                            role, menu: 'Pengembalian', url, action: 'Check Create Button',
                            expected: 'Tombol "Ajukan Pengembalian" terlihat',
                            actual: 'Tombol tidak terlihat',
                            screenshot: scName
                        });
                    }

                    // Test /returns/create
                    currentErrors = [];
                    await page.goto(`${baseUrl}/returns/create`);
                    await page.waitForLoadState('networkidle');
                    if (currentErrors.length > 0) {
                        const scName = `screenshots/uat/${role}_returns_create_error.png`;
                        await page.screenshot({ path: scName });
                        results.push({
                            role, menu: 'Pengembalian', url: `${baseUrl}/returns/create`, action: 'Create Return',
                            expected: 'HTTP 200',
                            actual: `HTTP ${currentErrors[0].status}`,
                            screenshot: scName
                        });
                    }
                } else if (role === 'ADMIN') {
                    if (createBtnCount > 0) {
                        const scName = `screenshots/uat/${role}_returns_has_create_btn.png`;
                        await page.screenshot({ path: scName });
                        results.push({
                            role, menu: 'Pengembalian', url, action: 'Check Create Button',
                            expected: 'Tombol TIDAK terlihat',
                            actual: 'Tombol terlihat',
                            screenshot: scName
                        });
                    }
                    
                    // Test /returns/create -> 403
                    currentErrors = [];
                    await page.goto(`${baseUrl}/returns/create`);
                    await page.waitForLoadState('networkidle');
                    const is403 = currentErrors.some(e => e.status === 403) || (await page.locator('text="403"').count() > 0) || (await page.locator('text="Forbidden"').count() > 0);
                    if (!is403) {
                        const scName = `screenshots/uat/${role}_returns_create_not_403.png`;
                        await page.screenshot({ path: scName });
                        results.push({
                            role, menu: 'Pengembalian', url: `${baseUrl}/returns/create`, action: 'Create Return',
                            expected: 'HTTP 403',
                            actual: currentErrors.length > 0 ? `HTTP ${currentErrors[0].status}` : 'HTTP 200 / Form render',
                            screenshot: scName
                        });
                    }

                    // Go back and check pending return process button
                    await page.goto(url);
                    await page.waitForLoadState('networkidle');
                    const detailBtn = await page.locator('a[href*="/returns/show?id="], a[href*="/returns/show/"]').first();
                    if (await detailBtn.count() > 0) {
                        const href = await detailBtn.getAttribute('href');
                        await page.goto(`${baseUrl}${href}`);
                        await page.waitForLoadState('networkidle');
                        const processBtnCount = await page.locator('button:has-text("Proses Pengembalian"), form[action*="/process"] button').count();
                        if (processBtnCount === 0) {
                            const pendingText = await page.locator('body').innerText();
                            if(pendingText.includes('PENDING') || pendingText.includes('Menunggu') || pendingText.includes('Diajukan')) {
                                const scName = `screenshots/uat/${role}_returns_no_process_btn.png`;
                                await page.screenshot({ path: scName });
                                results.push({
                                    role, menu: 'Pengembalian', url: `${baseUrl}${href}`, action: 'Check Process Button',
                                    expected: 'Tombol "Proses Pengembalian" terlihat pada status pending',
                                    actual: 'Tombol tidak terlihat',
                                    screenshot: scName
                                });
                            }
                        }
                    }
                } else if (role === 'SUPERADMIN') {
                     if (createBtnCount > 0) {
                        const scName = `screenshots/uat/${role}_returns_has_create_btn.png`;
                        await page.screenshot({ path: scName });
                        results.push({
                            role, menu: 'Pengembalian', url, action: 'Check Create Button',
                            expected: 'Tombol TIDAK terlihat',
                            actual: 'Tombol terlihat',
                            screenshot: scName
                        });
                    }
                }
            }
        }
        await context.close();
    }

    await browser.close();
    
    fs.writeFileSync('uat_results.json', JSON.stringify(results, null, 2));
    console.log("Tests done. Found issues:", results.length);
}

runTests().catch(console.error);
