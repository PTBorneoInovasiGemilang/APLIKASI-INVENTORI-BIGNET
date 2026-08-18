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

test.describe('D-017 Bulk Label Printing', () => {

  test('workspace: filter + seleksi SN + qty bulk + selected count + print sheet', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    const errors = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', e => errors.push(e.message));

    await login(page, 'admin@ptbig.com');
    await page.goto(BASE + '/labels/create');

    // Workspace elements
    await expect(page.getByRole('main').getByRole('heading', { name: 'Cetak Label Massal' })).toBeVisible();
    await expect(page.locator('input[name="search"]')).toBeVisible();
    await expect(page.locator('select[name="category_id"]')).toBeVisible();
    await expect(page.locator('select[name="warehouse_id"]')).toBeVisible();
    // Page Guide kanan
    await expect(page.getByText('Panduan Cepat')).toBeVisible();

    // Serial table present
    const serialCbs = page.locator('.serial-cb');
    const firstSerial = serialCbs.first();
    await expect(firstSerial).toBeVisible();
    await firstSerial.check();

    // Bulk item: set qty 3
    const bulkCb = page.locator('.bulk-cb').first();
    await expect(bulkCb).toBeVisible();
    await bulkCb.check();
    const qtyInput = bulkCb.locator('xpath=ancestor::tr').locator('.bulk-qty');
    await qtyInput.fill('3');

    // Selected count = 1 SN + 3 bulk = 4
    await expect(page.locator('#selectedCount')).toHaveText('4');

    // Submit
    await page.click('#printForm button[type="submit"]');
    await expect(page).toHaveURL(/\/labels\?/);

    // Print sheet labels = 4
    await expect(page.locator('.label-card')).toHaveCount(4);
    await expect(page.locator('.qr-box').first()).toBeVisible();
    await expect(page.locator('.barcode-svg').first()).toBeVisible();

    expect(errors.filter(e => !e.includes('favicon'))).toEqual([]);
  });

  test('select all tampil + bersihkan + empty state filter', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    const errors = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', e => errors.push(e.message));

    await login(page, 'admin@ptbig.com');
    await page.goto(BASE + '/labels/create');

    const count = await page.locator('.serial-cb').count();
    expect(count).toBeGreaterThan(0);

    await page.click('text=Pilih Semua Tampil');
    const checked = await page.locator('.serial-cb:checked').count();
    expect(checked).toBe(count);
    await expect(page.locator('#selectedCount')).toHaveText(String(count));

    await page.click('text=Bersihkan');
    await expect(page.locator('.serial-cb:checked')).toHaveCount(0);
    await expect(page.locator('#selectedCount')).toHaveText('0');

    // Empty state via filter (kata tidak ada)
    await page.fill('input[name="search"]', 'zzz_tidak_ada_12345');
    await page.click('button[type="submit"]');
    await expect(page.getByText('Tidak ada nomor seri yang cocok')).toBeVisible();
    await expect(page.getByText('Tidak ada item non-serialized yang cocok')).toBeVisible();

    expect(errors.filter(e => !e.includes('favicon'))).toEqual([]);
  });

  test('teknisi 403 di /labels/create', async ({ page }) => {
    await login(page, 'teknisia@ptbig.com');
    await page.goto(BASE + '/labels/create');
    await expect(page.getByRole('heading', { name: '403' })).toBeVisible();
  });

  test('dark mode: workspace tetap usable tanpa console error', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    const errors = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', e => errors.push(e.message));

    await login(page, 'admin@ptbig.com');
    await page.evaluate(() => localStorage.setItem('theme', 'dark'));
    await page.goto(BASE + '/labels/create');

    await expect(page.locator('.serial-cb').first()).toBeVisible();
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
    expect(overflow).toBe(false);
    expect(errors.filter(e => !e.includes('favicon'))).toEqual([]);
  });
});
