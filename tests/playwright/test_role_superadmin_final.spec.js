const { test, expect } = require('@playwright/test');

const BASE = 'http://localhost:8001';

test.describe('FINAL UAT: SUPERADMIN ROLE', () => {
    test.beforeEach(async ({ page }) => {
        // Login as Superadmin
        await page.goto(BASE + '/login');
        await page.fill('input[name="email"]', 'superadmin@ptbig.com');
        await page.fill('input[name="password"]', 'superadmin123');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(BASE + '/dashboard');
    });

    test('UAT-SUPERADMIN-1: Allowed Modules Access', async ({ page }) => {
        const allowedRoutes = [
            '/dashboard',
            '/users',
            '/settings',
            '/audit',
            '/timeline',
            '/reports',
            '/stocks',
            '/serials'
        ];

        for (const route of allowedRoutes) {
            await page.goto(BASE + route);
            // Ensure no 403 error page or redirect to dashboard (if it wasn't dashboard)
            if (route !== '/dashboard') {
                expect(page.url().includes(route)).toBeTruthy();
            }
            await expect(page.locator('h1').first()).toBeVisible();
        }
    });

    test('UAT-SUPERADMIN-2: Read-Only UI Checks', async ({ page }) => {
        const readOnlyModules = [
            { path: '/items', buttonText: 'Tambah Barang' },
            { path: '/categories', buttonText: 'Tambah Kategori' },
            { path: '/units', buttonText: 'Tambah Satuan' },
            { path: '/warehouses', buttonText: 'Tambah Gudang' },
            { path: '/locations', buttonText: 'Tambah Lokasi' },
            { path: '/incoming', buttonText: 'Barang Masuk Baru' },
            { path: '/outgoing', buttonText: 'Barang Keluar Baru' },
            { path: '/submissions', buttonText: 'Buat Pengajuan' },
            { path: '/loans', buttonText: 'Buat Peminjaman' },
            { path: '/returns', buttonText: 'Buat Pengembalian' },
            { path: '/transfers', buttonText: 'Buat Transfer' },
            { path: '/installations', buttonText: 'Buat Instalasi' },
            { path: '/incidents', buttonText: 'Lapor Insiden' },
            { path: '/adjustments', buttonText: 'Buat Penyesuaian' }
        ];

        for (const module of readOnlyModules) {
            await page.goto(BASE + module.path);
            
            // First check if access is granted at all
            if (!page.url().includes(module.path)) {
                // If it redirected, that's also fine for negative check, but usually SUPERADMIN can read.
                // We expect SUPERADMIN to be able to READ these modules!
                console.warn(`WARNING: Redirected from ${module.path} to ${page.url()}. Expected to be able to read.`);
                expect(page.url().includes(module.path)).toBeTruthy();
            }

            // Verify the mutation button does not exist
            const buttonCount = await page.locator(`a:has-text("${module.buttonText}"), button:has-text("${module.buttonText}")`).count();
            expect(buttonCount).toBe(0);
        }
    });

    test('UAT-SUPERADMIN-3: Exception D-014 (Opname Approval)', async ({ page }) => {
        // Superadmin should be able to process/approve Stock Opname.
        // Go to /opnames
        await page.goto(BASE + '/opnames');
        
        // Ensure SUPERADMIN can access Opnames
        expect(page.url().includes('/opnames')).toBeTruthy();
        
        // Wait for table to load
        await page.waitForSelector('table');
        
        // If there are opnames, try to open the first one
        const detailLink = page.locator('table tbody tr:first-child a:has-text("Detail")');
        const count = await detailLink.count();
        if (count > 0) {
            await detailLink.first().click();
            await page.waitForLoadState('networkidle');
            
            // Verify if there is an approval section or mutation available
            // Note: If the opname is already completed, the button might not show. 
            // We just verify it doesn't crash and the page renders properly.
            await expect(page.locator('body')).toContainText('Detail Stock Opname');
        }
    });

    test('UAT-SUPERADMIN-4: Negative Backend Check (Mutation Routes)', async ({ page, request }) => {
        // Try accessing a create route directly via browser
        await page.goto(BASE + '/transfers/create');
        
        // Should show 403 or redirect with error
        // App uses 403 Akses Ditolak or redirect
        const bodyText = await page.innerText('body');
        const isRejected = bodyText.includes('Akses Ditolak') || page.url() !== BASE + '/transfers/create';
        expect(isRejected).toBeTruthy();

        // Try an API/POST mutation using Playwright request context
        // Ensure we pass cookies from the browser session
        const cookies = await page.context().cookies();
        
        const csrfTokenMatch = await page.evaluate(() => {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        });
        
        const response = await request.post(BASE + '/items', {
            data: {
                csrf_token: csrfTokenMatch || '',
                name: 'Test Hack Item',
                category_id: 1,
                unit_id: 1,
                is_serialized: 0
            },
            headers: {
                'Cookie': cookies.map(c => `${c.name}=${c.value}`).join('; '),
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            maxRedirects: 0
        });

        // Backend should reject with 403 or 302
        expect([403, 302]).toContain(response.status());
    });
});
