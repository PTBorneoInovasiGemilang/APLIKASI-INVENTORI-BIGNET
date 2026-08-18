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

test.describe('Barcode / QR', () => {

  test('item detail: barcode Code128 render, QR modal, cetak label link', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    const errors = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', e => errors.push(e.message));

    await login(page, 'admin@ptbig.com');
    await page.goto(BASE + '/items/show/2?sn_id=11');

    // Code 128 SVG present
    const barcodeSvg = page.locator('.barcode-svg').first();
    await expect(barcodeSvg).toBeVisible();
    const rectCount = await barcodeSvg.locator('rect').count();
    expect(rectCount).toBeGreaterThan(20);

    // QR modal (client-side generated)
    await page.click('button:has-text("QR")');
    const modal = page.locator('#qrModal:not(.hidden)');
    await expect(modal).toBeVisible();
    await expect(modal.locator('svg')).toBeVisible();
    await page.click('button:has-text("Tutup")');

    // Cetak label link -> labels page
    await page.click('main a:has-text("Cetak Label")');
    await expect(page).toHaveURL(/.*\/labels\?serial=SN-CSC-0001/);
    await expect(page.locator('.barcode-svg').first()).toBeVisible();
    await expect(page.locator('.qr-box svg').first()).toBeVisible();
    await expect(page.locator('text=SN-CSC-0001').first()).toBeVisible();

    expect(errors.filter(e => !e.includes('favicon') && !e.includes('tailwindcss'))).toEqual([]);
  });

  test('labels sheet: batch via serials Cetak Semua + dark mode + mobile no overflow + print button', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await login(page, 'admin@ptbig.com');

    // serials index has Label + Cetak Semua
    await page.goto(BASE + '/serials');
    await expect(page.locator('a:has-text("Cetak Semua Label")').first()).toBeVisible();
    await expect(page.locator('a:has-text("Label")').first()).toBeVisible();

    // batch print page
    await page.click('a:has-text("Cetak Semua Label")');
    await expect(page).toHaveURL(/.*\/labels\?serial_ids=/);
    const labels = await page.locator('.label-card').count();
    expect(labels).toBeGreaterThan(1);
    await expect(page.locator('.qr-box svg').first()).toBeVisible();
    await expect(page.locator('button:has-text("Cetak")')).toBeVisible();

    // dark mode
    await page.click('button[aria-label="Toggle dark mode"]');
    await expect(page.locator('html')).toHaveClass(/dark/);
    await expect(page.locator('.label-card').first()).toBeVisible();

    // mobile no overflow
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(BASE + '/labels?serial=SN-CSC-0001');
    await expect(page.locator('.barcode-svg').first()).toBeVisible();
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
    expect(overflow).toBe(false);
  });

  test('scan page: lookup SN dan kode redirect ke detail; not-found ke search', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await login(page, 'admin@ptbig.com');

    await page.goto(BASE + '/scan');
    await expect(page.locator('h1', { hasText: 'Scan Barcode / QR' })).toBeVisible();
    await expect(page.locator('text=Panduan Cepat').first()).toBeVisible();

    // serial lookup
    await page.fill('#scanInput', 'SN-CSC-0001');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/.*\/items\/show\/\d+\?sn_id=\d+/);
    await expect(page.locator('h1').first()).toBeVisible();

    // kode lookup
    await page.goto(BASE + '/scan');
    await page.fill('#scanInput', 'ITM-SW01');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/.*\/items\/show\/\d+/);

    // not found -> search
    await page.goto(BASE + '/scan');
    await page.fill('#scanInput', 'ZZZ-UNKNOWN-CODE');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/.*\/search\?q=ZZZ-UNKNOWN-CODE/);
  });

  test('TEKNISI: /labels dan /scan ditolak 403', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await login(page, 'teknisia@ptbig.com');
    await page.goto(BASE + '/labels?serial=SN-CSC-0001');
    await expect(page.locator('h1', { hasText: '403' })).toBeVisible();
    await page.goto(BASE + '/scan');
    await expect(page.locator('h1', { hasText: '403' })).toBeVisible();
  });
});
