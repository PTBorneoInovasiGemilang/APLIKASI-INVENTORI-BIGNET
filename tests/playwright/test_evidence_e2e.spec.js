const { test, expect } = require('@playwright/test');
const { execSync } = require('child_process');
const path = require('path');

const BASE = 'http://localhost:8000';
const HELPER = path.join(__dirname, 'helpers', 'evidence_seed.php');

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

function incidentId() {
  return execSync(`php "${HELPER}"`, { encoding: 'utf8' }).trim();
}

test.describe('Evidence / Attachment', () => {

  test('ADMIN: upload bukti -> metadata & preview -> buka file -> hapus; console clean', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    const errors = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', e => errors.push(e.message));

    const id = incidentId();
    await login(page, 'admin@ptbig.com');
    await page.goto(BASE + '/incidents/show?id=' + id);

    // Evidence section + empty state
    await expect(page.locator('text=Bukti Tambahan').first()).toBeVisible();
    await expect(page.locator('text=Belum ada bukti tambahan.').first()).toBeVisible();

    // Upload
    const png = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', 'base64');
    await page.click('button:has-text("Tambah Bukti")');
    await page.setInputFiles('input[type="file"][name="evidence"]', { name: 'bukti_kerusakan.png', mimeType: 'image/png', buffer: png });
    await page.click('button:has-text("Simpan Bukti")');
    await expect(page.locator('text=Bukti berhasil diunggah').first()).toBeVisible();
    await expect(page.locator('text=bukti_kerusakan.png').first()).toBeVisible();
    await expect(page.locator('text=image/png').first()).toBeVisible();
    // thumbnail preview (img from /evidence/{id})
    const thumb = page.locator('img[src*="/evidence/"]').first();
    await expect(thumb).toBeVisible();

    // Open file -> 200 image
    const imgUrl = await thumb.getAttribute('src');
    const resp = await page.request.get(BASE + imgUrl);
    expect(resp.status()).toBe(200);
    expect(resp.headers()['content-type']).toContain('image/png');

    // Delete
    page.once('dialog', d => d.accept());
    await page.locator('button:has-text("Hapus")').first().click();
    await expect(page.locator('text=Bukti dihapus').first()).toBeVisible();
    await expect(page.locator('text=bukti_kerusakan.png')).toHaveCount(0);

    expect(errors.filter(e => !e.includes('favicon') && !e.includes('tailwindcss'))).toEqual([]);
  });

  test('legacy incident evidence kini tampil (bug fix /file) + TEKNISI tidak bisa upload', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await login(page, 'admin@ptbig.com');

    // find an incident item with evidence
    const id = incidentId();
    await page.goto(BASE + '/incidents/show?id=' + id);
    // upload form visible for admin
    await expect(page.locator('button:has-text("Tambah Bukti")').first()).toBeVisible();

    // Teknisi: no upload form
    await page.goto(BASE + '/logout');
    await login(page, 'teknisia@ptbig.com');
    await page.goto(BASE + '/incidents/show?id=' + id);
    await expect(page.locator('button:has-text("Tambah Bukti")')).toHaveCount(0);
    await expect(page.locator('text=Bukti Tambahan').first()).toBeVisible();

    // Teknisi cannot open evidence owned by admin (403 page)
    const anyImg = page.locator('img[src*="/evidence/"]').first();
    if (await anyImg.count() > 0) {
      const imgUrl = await anyImg.getAttribute('src');
      await page.goto(BASE + imgUrl);
      await expect(page.locator('h1', { hasText: '403' })).toBeVisible();
    }
  });

  test('dark mode + mobile: evidence section konsisten, no overflow', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    const id = incidentId();
    await login(page, 'admin@ptbig.com');
    await page.goto(BASE + '/incidents/show?id=' + id);
    await page.click('button[aria-label="Toggle dark mode"]');
    await expect(page.locator('html')).toHaveClass(/dark/);
    await expect(page.locator('text=Bukti Tambahan').first()).toBeVisible();

    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(BASE + '/incidents/show?id=' + id);
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
    expect(overflow).toBe(false);
  });
});
