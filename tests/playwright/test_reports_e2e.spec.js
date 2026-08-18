const { test, expect } = require('@playwright/test');

test.describe('Batch D - Reports E2E', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="email"]', 'superadmin@ptbig.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/.*\/dashboard/);
  });

  test('Report index renders all report cards', async ({ page }) => {
    await page.goto('http://localhost:8000/reports');
    await expect(page.locator('main h1').first()).toContainText('Laporan');
    // Cards for each report module (scoped to main content, avoid sidebar duplicates)
    await expect(page.locator('main a[href*="/reports/ledger"]').first()).toBeVisible();
    await expect(page.locator('main a[href*="/reports/serials"]').first()).toBeVisible();
    await expect(page.locator('main a[href*="/reports/audit"]').first()).toBeVisible();
  });

  test('Stock Ledger report: toolbar, row numbering, guide, pagination, reset', async ({ page }) => {
    await page.goto('http://localhost:8000/reports/ledger');

    // Header
    await expect(page.locator('main h1').first()).toContainText('Laporan Stok & Mutasi');

    // Page Guide panel
    await expect(page.locator('text=Panduan Cepat')).toBeVisible();

    // Toolbar: search + date filters + reset
    await expect(page.locator('input[name="search"]')).toBeVisible();
    await expect(page.locator('input[name="start_date"]')).toBeVisible();
    await expect(page.locator('input[name="end_date"]')).toBeVisible();
    await expect(page.locator('a:has-text("Atur Ulang")')).toBeVisible();

    // Table: kolom checkbox (selection) lalu No
    await expect(page.locator('table thead th').nth(1)).toHaveText('No');

    // Row numbering starts at 1 on first page (kolom ke-2 setelah checkbox)
    const firstCell = page.locator('table tbody tr').first().locator('td').nth(1);
    if (await page.locator('table tbody tr').count() > 0) {
      await expect(firstCell).toHaveText('1');
    }

    // Summary box
    await expect(page.locator('text=Total Baris Data')).toBeVisible();

    // Reset: apply search then reset, verify URL cleared
    await page.fill('input[name="search"]', 'KAT-');
    await page.click('button:has-text("Cari")');
    await page.waitForLoadState('networkidle');
    await page.click('a:has-text("Atur Ulang")');
    await expect(page).toHaveURL(/\/reports\/ledger\??$/);

    // Empty state with nonsense search (no crash / HTTP 500)
    await page.fill('input[name="search"]', 'zzz_tidak_ada_data_xyz');
    await page.click('button:has-text("Cari")');
    await page.waitForLoadState('networkidle');
    await expect(page.locator('text=Belum ada data').first()).toBeVisible();
  });

  test('Audit Log report renders with action filter and table', async ({ page }) => {
    await page.goto('http://localhost:8000/reports/audit');

    await expect(page.locator('main h1').first()).toContainText('Log Aktivitas');
    await expect(page.locator('table thead th').nth(1)).toHaveText('No');
    await expect(page.locator('select[name="action_type"]')).toBeVisible();
    await expect(page.locator('select[name="user_id"]')).toBeVisible();
  });

  test('Serials report renders', async ({ page }) => {
    await page.goto('http://localhost:8000/reports/serials');
    await expect(page.locator('main h1').first()).toContainText('Laporan Nomor Seri');
    await expect(page.locator('table thead th').nth(1)).toHaveText('No');
  });

  test('No console errors on report pages', async ({ page }) => {
    const errors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    page.on('pageerror', err => errors.push(err.message));

    await page.goto('http://localhost:8000/reports/ledger');
    await page.goto('http://localhost:8000/reports/audit');
    await page.waitForLoadState('networkidle');

    expect(errors.filter(e => !e.includes('favicon'))).toEqual([]);
  });

});
