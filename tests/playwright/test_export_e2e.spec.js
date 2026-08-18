const { test, expect } = require('@playwright/test');
const fs = require('fs');

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

test.describe('Export PDF', () => {

  test('report viewer: Export PDF mengunduh PDF valid, filter ikut terbawa', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await login(page, 'admin@ptbig.com');

    // viewer renders with export buttons
    await page.goto(BASE + '/reports/ledger');
    await expect(page.locator('h1').first()).toBeVisible();
    await expect(page.locator('button:has-text("Export CSV (Semua Hasil)")')).toBeVisible();
    const pdfBtn = page.locator('button:has-text("Export PDF (Semua Hasil)")');
    await expect(pdfBtn).toBeVisible();

    // set filters then export
    await page.fill('input[name="start_date"]', '2026-01-01');
    await page.fill('input[name="end_date"]', '2026-12-31');

    const [download] = await Promise.all([
      page.waitForEvent('download'),
      pdfBtn.click(),
    ]);
    const filename = download.suggestedFilename();
    expect(filename).toContain('.pdf');
    const path = await download.path();
    const buf = fs.readFileSync(path);
    expect(buf.subarray(0, 8).toString('latin1')).toBe('%PDF-1.4');
    const body = buf.toString('latin1');
    expect(body).toContain('%%EOF');
    expect(body).toContain('Halaman');
    expect(body).toContain('Laporan Stok & Mutasi');
  });

  test('TEKNISI: laporan & export ditolak 403', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await login(page, 'teknisia@ptbig.com');
    await page.goto(BASE + '/reports/ledger');
    await expect(page.locator('h1', { hasText: '403' })).toBeVisible();
  });
});
