const { test, expect } = require('@playwright/test');
const { execSync } = require('child_process');
const path = require('path');

const BASE = 'http://localhost:8000';
const DATA = path.join(__dirname, 'helpers', 'barcode_data.php');
const SEED = path.join(__dirname, 'helpers', 'user_seed.php');

// real barcode test data from DB
const [KODE_SER, NAMA_SER, SER1, KODE_BULK, NAMA_BULK] = execSync(`php "${DATA}"`, { encoding: 'utf8' }).trim().split('|');

function cleanupByEmail(email) { if (email) execSync(`php "${SEED}" cleanup_by_email ${email}`, { encoding: 'utf8' }); }

async function login(page, email, password) {
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

async function openSearch(page) {
  await page.goto(BASE + '/search');
  await expect(page.locator('#scanInput')).toBeVisible();
}

async function scan(page, code) {
  await page.fill('#scanInput', code);
  await page.press('#scanInput', 'Enter');
  // Tunggu handler scan mengosongkan input -> scan selesai diproses (deterministik utk scan beruntun)
  await expect(page.locator('#scanInput')).toHaveValue('', { timeout: 10000 });
}

test.describe('Pencarian Barang - Scan Barcode (Admin & Superadmin)', () => {

  test('A: Exact match barcode (kode barang) -> hasil otomatis tanpa klik tombol Cari', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com', 'superadmin123');
    await openSearch(page);
    await scan(page, KODE_SER); // contoh: KAT-000001-000001 + ENTER
    // hasil muncul otomatis (tanpa klik Cari)
    await expect(page.locator('#searchResults')).toContainText(NAMA_SER);
    await expect(page.locator('#searchResults')).toContainText(KODE_SER);
    await expect(page.locator('#searchResults a', { hasText: 'Lihat' }).first()).toBeVisible();
  });

  test('B: Barcode tidak ditemukan -> pesan jelas, tanpa 500', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com', 'superadmin123');
    await openSearch(page);
    await scan(page, 'BARCODE-TIDAK-ADA');
    await expect(page.locator('#searchResults')).toContainText('Barcode tidak ditemukan.');
  });

  test('C: Scan berulang tanpa reload & tanpa klik field', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com', 'superadmin123');
    await openSearch(page);
    // Scan A -> kode barang serialized
    await scan(page, KODE_SER);
    await expect(page.locator('#searchResults')).toContainText(NAMA_SER);
    await expect(page.locator('#searchResults')).not.toContainText(NAMA_BULK);
    // Scan B -> kode barang bulk (hasil diganti)
    await scan(page, KODE_BULK);
    await expect(page.locator('#searchResults')).toContainText(NAMA_BULK);
    await expect(page.locator('#searchResults')).not.toContainText(NAMA_SER);
    // Scan C -> serial number (hasil diganti, 1 baris)
    await scan(page, SER1);
    await expect(page.locator('#searchResults')).toContainText(SER1);
    await expect(page.locator('#searchResults tbody tr')).toHaveCount(1);
  });

  test('D: Manual search regression (nama, kode, SN) tetap berfungsi', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com', 'superadmin123');
    await openSearch(page);
    // Nama
    await page.fill('input[name="q"]', NAMA_SER);
    await expect(page.locator('#searchResults')).toContainText(NAMA_SER, { timeout: 5000 });
    // Kode barang
    await page.fill('input[name="q"]', KODE_SER);
    await expect(page.locator('#searchResults')).toContainText(KODE_SER, { timeout: 5000 });
    // SN
    await page.fill('input[name="q"]', SER1);
    await expect(page.locator('#searchResults')).toContainText(SER1, { timeout: 5000 });
  });

  test('E: ADMIN - scanner tersedia dan berfungsi', async ({ page }) => {
    const ts = Date.now();
    const email = `scanadmin_${ts}@ptbig.com`;
    const pw = 'scan123456';
    // Buat admin sementara via Superadmin (alur create user existing)
    await login(page, 'superadmin@ptbig.com', 'superadmin123');
    await page.goto(BASE + '/users/create');
    await page.fill('input[name="name"]', 'Scan Admin Test');
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="phone"]', '0812');
    await page.selectOption('select[name="role"]', 'ADMIN');
    await page.fill('input[name="password"]', pw);
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/users$/);
    try {
      // Login sebagai ADMIN
      await page.goto(BASE + '/logout');
      await login(page, email, pw);
      await page.goto(BASE + '/search');
      await expect(page.locator('#scanInput')).toBeVisible();
      await expect(page.locator('#scanInput')).toBeFocused();
      await scan(page, KODE_BULK);
      await expect(page.locator('#searchResults')).toContainText(NAMA_BULK);
    } finally {
      cleanupByEmail(email);
    }
  });

  test('F: SUPERADMIN - scanner tersedia dan berfungsi', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com', 'superadmin123');
    await openSearch(page);
    await expect(page.locator('#scanInput')).toBeFocused();
    await scan(page, SER1);
    await expect(page.locator('#searchResults')).toContainText(SER1);
  });

  test('G: Setelah scan, tombol Lihat membuka Inventory 360', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com', 'superadmin123');
    await openSearch(page);
    await scan(page, SER1);
    await expect(page.locator('#searchResults')).toContainText(SER1);
    await page.locator('#searchResults a', { hasText: 'Lihat' }).first().click();
    await expect(page).toHaveURL(/.*\/items\/show\/\d+/);
    await expect(page.locator('body')).not.toContainText('500');
  });

  test('H: UI - tanpa console error, auto-focus saat dibuka, fokus kembali setelah scan', async ({ page }) => {
    const errors = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', err => errors.push('pageerror: ' + err.message));

    await login(page, 'superadmin@ptbig.com', 'superadmin123');
    await page.goto(BASE + '/search');
    await expect(page.locator('#scanInput')).toBeFocused(); // auto-focus saat dibuka
    await scan(page, KODE_SER);
    await expect(page.locator('#searchResults')).toContainText(NAMA_SER);
    await expect(page.locator('#scanInput')).toBeFocused(); // fokus kembali setelah hasil
    // scan berikutnya langsung bisa tanpa klik (scan B)
    await scan(page, KODE_BULK);
    await expect(page.locator('#searchResults')).toContainText(NAMA_BULK);
    expect(errors).toEqual([]); // tanpa console error
  });
});
