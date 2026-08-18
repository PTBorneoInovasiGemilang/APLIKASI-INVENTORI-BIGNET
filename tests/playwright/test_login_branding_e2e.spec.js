const { test, expect } = require('@playwright/test');
const { execSync } = require('child_process');
const path = require('path');

const BASE = 'http://localhost:8000';
const HELPER = path.join(__dirname, 'helpers', 'settings_set.php');
const ORIG_LOGO = 'logo_4c941c4e21ac79f0.png';

function getSetting(key) {
  return execSync(`php "${HELPER}" get ${key}`, { encoding: 'utf8' }).trim();
}
function setSetting(key, value) {
  execSync(`php "${HELPER}" set ${key} "${value}"`, { encoding: 'utf8' });
}

test.describe('Login branding mengikuti Pengaturan Superadmin', () => {

  test('headline "Welcome to [Nama Aplikasi]" dari app_name; nama perusahaan & logo dari settings; tanpa hardcode/broken image/console error', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    const appName = getSetting('app_name') || 'GUDANG PT.BIG';
    const companyName = getSetting('company_name');
    const errors = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', err => errors.push('pageerror: ' + err.message));

    await page.goto(BASE + '/login');

    // Headline: Welcome to <Nama Aplikasi>
    await expect(page.locator('h1')).toContainText('Welcome to');
    await expect(page.locator('h1')).toContainText(appName);
    // Branding nama perusahaan di samping logo (tetap dari company_name)
    await expect(page.locator('span', { hasText: companyName }).first()).toBeVisible();
    // Tidak ada hardcode lama
    await expect(page.getByText('PT.BIG WMS')).toHaveCount(0);

    // Logo dimuat (tidak broken)
    const logo = page.locator('img[src="/logo"]').first();
    await expect(logo).toBeVisible();
    const naturalWidth = await logo.evaluate(el => el.naturalWidth);
    expect(naturalWidth).toBeGreaterThan(0);

    // Favicon ter-load
    const favResp = await page.request.get(BASE + '/favicon');
    expect(favResp.status()).toBe(200);

    expect(errors).toEqual([]);
  });

  test('perubahan Nama Aplikasi, Nama Perusahaan & Logo Utama langsung ter-refleksi, lalu dikembalikan', async ({ page }) => {
    const origAppName = getSetting('app_name');
    const origCompany = getSetting('company_name');
    const origLogo = getSetting('logo_path');
    const testApp = 'TEST WMS BIGNET ' + Date.now();
    const testCompany = 'PT UJI BRANDING ' + Date.now();
    try {
      // Nama Aplikasi -> headline h1
      setSetting('app_name', testApp);
      await page.goto(BASE + '/login');
      await expect(page.locator('h1')).toContainText('Welcome to');
      await expect(page.locator('h1')).toContainText(testApp);

      // Nama Perusahaan -> branding span (bukan headline)
      setSetting('company_name', testCompany);
      await page.goto(BASE + '/login');
      await expect(page.locator('span', { hasText: testCompany }).first()).toBeVisible();
      await expect(page.locator('h1')).not.toContainText(testCompany);

      // Logo Utama -> ganti ke file lain yang valid
      setSetting('logo_path', 'favicon_b1219910bd827bd7.png');
      await page.goto(BASE + '/login');
      const logo = page.locator('img[src="/logo"]').first();
      await expect(logo).toBeVisible();
      const ok = await logo.evaluate(el => el.naturalWidth > 0);
      expect(ok).toBe(true);
    } finally {
      setSetting('app_name', origAppName);
      setSetting('company_name', origCompany);
      setSetting('logo_path', origLogo);
    }
    // Setelah restore, nilai asli kembali
    await page.goto(BASE + '/login');
    await expect(page.locator('h1')).toContainText(origAppName);
    await expect(page.locator('span', { hasText: origCompany }).first()).toBeVisible();
  });

  test('fallback ikon ketika logo utama kosong / file tidak ada, dan login tetap berfungsi', async ({ page }) => {
    const origLogo = getSetting('logo_path');
    try {
      // kosong -> ikon fallback, tanpa <img>
      setSetting('logo_path', '');
      await page.goto(BASE + '/login');
      await expect(page.locator('img[src="/logo"]')).toHaveCount(0);
      await expect(page.locator('.fa-boxes').first()).toBeVisible();
      const resp = await page.request.get(BASE + '/logo');
      expect(resp.status()).toBe(404);

      // file tidak ada -> tetap fallback ikon (tidak ada broken image)
      setSetting('logo_path', 'logo_tidak_ada.png');
      await page.goto(BASE + '/login');
      await expect(page.locator('img[src="/logo"]')).toHaveCount(0);
      await expect(page.locator('.fa-boxes').first()).toBeVisible();
    } finally {
      setSetting('logo_path', origLogo);
    }

    // Login flow tetap berfungsi (SUPERADMIN seeded)
    await page.goto(BASE + '/login');
    await page.fill('input[name="email"]', 'superadmin@ptbig.com');
    await page.fill('input[name="password"]', 'superadmin123');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/.*\/dashboard/);
  });
});
