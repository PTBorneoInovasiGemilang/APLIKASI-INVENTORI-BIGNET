const { test, expect } = require('@playwright/test');

test.describe('Batch D - Timeline E2E', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="email"]', 'superadmin@ptbig.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/.*\/dashboard/);
  });

  test('Timeline index renders toolbar, row numbering, guide, and search flow', async ({ page }) => {
    await page.goto('http://localhost:8000/timeline');

    await expect(page.locator('main h1').first()).toContainText('Riwayat Perjalanan Barang');
    await expect(page.locator('text=Panduan Cepat')).toBeVisible();

    // Shared toolbar: search + reset
    await expect(page.locator('input[name="search"]')).toBeVisible();
    await expect(page.locator('button:has-text("Cari")')).toBeVisible();
    await expect(page.locator('a:has-text("Reset")')).toBeVisible();

    // Search flow: empty result should NOT crash (no HTTP 500)
    await page.fill('input[name="search"]', 'zzz_tidak_ada_serial_xyz');
    await page.click('button:has-text("Cari")');
    await page.waitForLoadState('networkidle');
    await expect(page.locator('main h1').first()).toContainText('Riwayat Perjalanan Barang');
    await expect(page.locator('text=Data Tidak Ditemukan')).toBeVisible();

    // Reset returns to clean URL
    await page.click('a:has-text("Reset")');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/\/timeline\??$/);
  });

  test('Timeline table has No column and row numbering', async ({ page }) => {
    await page.goto('http://localhost:8000/timeline');

    // Table header includes No
    await expect(page.locator('table thead th').first()).toHaveText('No');

    const rowCount = await page.locator('table tbody tr').count();
    if (rowCount > 0) {
      // Row numbering starts at 1
      await expect(page.locator('table tbody tr').first().locator('td').first()).toHaveText('1');
    }
  });

  test('No console errors on timeline pages', async ({ page }) => {
    const errors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    page.on('pageerror', err => errors.push(err.message));

    await page.goto('http://localhost:8000/timeline');
    await page.fill('input[name="search"]', 'a');
    await page.click('button:has-text("Cari")');
    await page.waitForLoadState('networkidle');

    expect(errors.filter(e => !e.includes('favicon'))).toEqual([]);
  });

});
