const { test, expect } = require('@playwright/test');

const BASE = 'http://localhost:8000';

async function login(page, email, password = 'password') {
  await page.goto(BASE + '/login');
  if (!page.url().includes('/login')) {
    await page.goto(BASE + '/logout');
    await page.goto(BASE + '/login');
  }
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/.*\/dashboard/);
}

test.describe('Live Search', () => {

  test('ketik -> debounce -> hasil update otomatis; URL sinkron; empty state; detail tetap jalan', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    const errors = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', e => errors.push(e.message));

    await login(page, 'admin@ptbig.com');
    await page.goto(BASE + '/search');

    // initial: default list
    await expect(page.locator('#searchResults tbody tr').first()).toBeVisible();

    // type a query -> live update (debounced)
    const q = page.locator('input[name="q"]');
    await q.fill('cisco');
    await expect(page.locator('#searchResults')).toContainText('Cisco', { timeout: 5000 });
    await expect(page).toHaveURL(/.*q=cisco/);

    // no-result -> empty state (shared pattern text)
    await q.fill('zzzznothing');
    await expect(page.locator('#searchResults')).toContainText('Tidak ada barang yang cocok dengan pencarian.', { timeout: 5000 });
    await expect(page.locator('#searchResults tbody tr')).toHaveCount(0);

    // back to results, then BULK filter (clear query so BULK rows show)
    await q.fill('');
    await page.selectOption('select[name="tipe"]', 'BULK');
    await expect(page.locator('#searchResults tbody tr').first()).toBeVisible({ timeout: 5000 });
    await expect(page).toHaveURL(/tipe=BULK/);

    // Lihat link works after live-rendered rows
    const firstLink = page.locator('#searchResults tbody tr a', { hasText: 'Lihat' }).first();
    await expect(firstLink).toBeVisible();
    await firstLink.click();
    await expect(page).toHaveURL(/.*\/items\/show\/\d+/);
    await expect(page.locator('h1').first()).toBeVisible();

    expect(errors.filter(e => !e.includes('favicon') && !e.includes('tailwindcss'))).toEqual([]);
  });

  test('filter change live, reset kembali ke daftar default, dark mode', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await login(page, 'admin@ptbig.com');
    await page.goto(BASE + '/search');

    // filter gudang -> live (URL sync via auto-wait)
    const whVal = await page.locator('select[name="gudang"] option').nth(1).getAttribute('value');
    await page.selectOption('select[name="gudang"]', whVal);
    await expect(page).toHaveURL(/gudang=/);
    await expect(page.locator('#searchResults tbody tr, #searchResults .py-16').first()).toBeVisible();

    // reset -> default list & empty filters
    await page.click('a:has-text("Reset")');
    await page.waitForLoadState('domcontentloaded');
    await expect(page.locator('#searchResults tbody tr').first()).toBeVisible();
    await expect(page).toHaveURL(/\/search$/);

    // dark mode still renders
    await page.click('button[aria-label="Toggle dark mode"]');
    await page.fill('input[name="q"]', 'cisco');
    await expect(page.locator('#searchResults')).toContainText('Cisco', { timeout: 5000 });
    await expect(page.locator('html')).toHaveClass(/dark/);
  });
});
