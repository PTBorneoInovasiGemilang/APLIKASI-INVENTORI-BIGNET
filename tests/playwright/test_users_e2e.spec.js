const { test, expect } = require('@playwright/test');

test.describe('Batch D - Users E2E', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="email"]', 'superadmin@ptbig.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/.*\/dashboard/);
  });

  test('Users index: toolbar (search, status filter, reset), No column, row numbering', async ({ page }) => {
    await page.goto('http://localhost:8000/users');

    await expect(page.locator('main h1').first()).toContainText('Users');

    // Shared toolbar
    await expect(page.locator('input[name="search"]')).toBeVisible();
    await expect(page.locator('select[name="status"]')).toBeVisible();
    await expect(page.locator('button:has-text("Cari")')).toBeVisible();
    await expect(page.locator('a:has-text("Reset")')).toBeVisible();

    // No column + row numbering
    await expect(page.locator('table thead th').first()).toHaveText('No');
    const rowCount = await page.locator('table tbody tr').count();
    if (rowCount > 0) {
      await expect(page.locator('table tbody tr').first().locator('td').first()).toHaveText('1');
    }

    // Search for nonsense -> empty state (no crash)
    await page.fill('input[name="search"]', 'zzz_tidak_ada_user_xyz');
    await page.click('button:has-text("Cari")');
    await page.waitForLoadState('networkidle');
    await expect(page.locator('text=Belum ada data user.')).toBeVisible();

    // Reset
    await page.click('a:has-text("Reset")');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/\/users\??$/);
  });

  test('Users index: approval modal markup exists and guide panel present', async ({ page }) => {
    await page.goto('http://localhost:8000/users');

    await expect(page.locator('text=Panduan Approval & Manajemen User')).toBeVisible();
    await expect(page.locator('#approvalModal')).toHaveCount(1);
  });

  test('No console errors on users page', async ({ page }) => {
    const errors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    page.on('pageerror', err => errors.push(err.message));

    await page.goto('http://localhost:8000/users');
    await page.fill('input[name="search"]', 'a');
    await page.click('button:has-text("Cari")');
    await page.waitForLoadState('networkidle');

    expect(errors.filter(e => !e.includes('favicon'))).toEqual([]);
  });

});
