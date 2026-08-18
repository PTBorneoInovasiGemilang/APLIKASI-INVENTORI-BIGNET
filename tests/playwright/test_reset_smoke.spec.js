const { test, expect } = require('@playwright/test');
const path = require('path');

const BASE = 'http://localhost:8000';
const SEED = path.join(__dirname, 'helpers', 'user_seed.php');
const SMOKE_CLEANUP = path.join(__dirname, 'helpers', 'smoke_cleanup.php');
const { execSync } = require('child_process');

const ts = Date.now();
const CAT = 'SMK Cat ' + ts;
const UNIT = 'SMK Unit ' + ts;
const WH_CODE = 'SMK' + String(ts).slice(-6);
const WH = 'SMK Wh ' + ts;
const ITEM = 'SMK Item ' + ts;

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

// Global Row Action UI Standard: aksi delete item berada di menu [⋮]. Buka menu row sebelum interaksi (idempotent).
async function openMenu(row) {
  const toggle = row.locator('[data-actions-toggle]');
  const expanded = await toggle.getAttribute('aria-expanded');
  if (expanded !== 'true') {
    await toggle.click();
  }
  await expect(row.locator('[data-actions-menu]')).toBeVisible();
}

// Bersihkan residu run sebelumnya (test idempotent) — hanya data dummy SMK.
execSync(`php "${SMOKE_CLEANUP}"`, { encoding: 'utf8' });

test('Smoke post-reset: create kategori/satuan/gudang/barang dummy, verify, delete via workflow resmi', async ({ page }) => {
  // 1. Buat ADMIN sementara via workflow resmi (Superadmin -> Pengguna -> Tambah)
  await login(page, 'superadmin@ptbig.com', 'superadmin123');
  const adminEmail = `smkadmin_${ts}@ptbig.com`;
  const adminPw = 'smkadmin123';
  await page.goto(BASE + '/users/create');
  await page.fill('input[name="name"]', 'SMK Admin');
  await page.fill('input[name="email"]', adminEmail);
  await page.fill('input[name="phone"]', '0816');
  await page.selectOption('select[name="role"]', 'ADMIN');
  await page.fill('input[name="password"]', adminPw);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/\/users$/);
  await page.goto(BASE + '/logout');

  try {
    await login(page, adminEmail, adminPw);

    // 2. Kategori
    await page.goto(BASE + '/categories');
    await page.click('button:has-text("Tambah Kategori")');
    await page.fill('#addModal input[name="name"]', CAT);
    await page.click('#addModal form button[type="submit"]');
    await expect(page).toHaveURL(/\/categories$/);
    await expect(page.locator(`tbody tr:has-text("${CAT}")`)).toHaveCount(1);

    // 3. Satuan (kode auto-generated UNT-xxxxxx — tidak ada input kode manual)
    await page.goto(BASE + '/units');
    await page.click('button:has-text("Tambah Satuan")');
    await expect(page.locator('#addModal input[name="code"]')).toHaveCount(0);
    await page.fill('#addModal input[name="name"]', UNIT);
    await page.click('#addModal form button[type="submit"]');
    await expect(page).toHaveURL(/\/units$/);
    const unitRow = page.locator(`tbody tr:has-text("${UNIT}")`);
    await expect(unitRow).toHaveCount(1);
    await expect(unitRow).toContainText(/UNT-\d{6}/);

    // 4. Gudang (kode auto-generated GDG-xxxxxx)
    await page.goto(BASE + '/warehouses');
    await page.click('button:has-text("Tambah Gudang")');
    await expect(page.locator('#addModal input[name="code"]')).toHaveCount(0);
    await page.fill('#addModal input[name="name"]', WH);
    await page.click('#addModal form button[type="submit"]');
    await expect(page).toHaveURL(/\/warehouses$/);
    const whRow = page.locator(`tbody tr:has-text("${WH}")`);
    await expect(whRow).toHaveCount(1);
    await expect(whRow).toContainText(/GDG-\d{6}/);

    // 5. Master Barang (pilih kategori & satuan dummy yang barusan dibuat)
    await page.goto(BASE + '/items');
    await page.click('button:has-text("Tambah Barang")');
    await page.fill('#addModal input[name="nama"]', ITEM);
    await page.selectOption('#addModal select[name="item_category_id"]', { label: CAT });
    await page.selectOption('#addModal select[name="unit_id"]', { label: UNIT });
    await page.fill('#addModal input[name="stok_minimum"]', '0');
    await page.click('#addModal form button[type="submit"]');
    await expect(page).toHaveURL(/\/items$/);
    const itemRow = page.locator(`tbody tr:has-text("${ITEM}")`);
    await expect(itemRow).toHaveCount(1);

    // 6. Hapus item via workflow resmi (Hapus — barang belum pernah digunakan) + modal konfirmasi (aksi di menu [⋮])
    await openMenu(itemRow);
    await expect(itemRow.locator('form[data-confirm-title="Hapus Master Barang?"]')).toBeVisible();
    await itemRow.locator('button:has-text("Hapus")').click();
    await expect(page.locator('#appModalTitle')).toHaveText('Hapus Master Barang?');
    await page.locator('[data-appmodal="backdrop"] button:has-text("Hapus")').click();
    await expect(page.locator('body')).toContainText('berhasil dihapus permanen');
    await expect(page.locator(`tbody tr:has-text("${ITEM}")`)).toHaveCount(0);

    // 7. Hapus kategori/satuan/gudang via bulk delete resmi + modal konfirmasi
    for (const [name, bulkId, pageUrl] of [[CAT, 'bulkCat', '/categories'], [UNIT, 'bulkUnit', '/units'], [WH, 'bulkWh', '/warehouses']]) {
      await page.goto(BASE + pageUrl);
      await page.locator(`tbody tr:has-text("${name}")`).locator('.bulk-checkbox').check();
      await page.click(`#${bulkId}-submit`);
      await expect(page.locator('#appModalTitle')).toHaveText('Hapus Data');
      await page.locator('[data-appmodal="backdrop"] button:has-text("Hapus")').click();
      await expect(page.locator('body')).toContainText('berhasil dihapus');
      await expect(page.locator(`tbody tr:has-text("${name}")`)).toHaveCount(0);
    }

    // 8. Verifikasi record uji kita sudah terhapus kembali (workflow create -> delete)
    await page.goto(BASE + '/items');
    await expect(page.locator(`tbody tr:has-text("${ITEM}")`)).toHaveCount(0);
    await page.goto(BASE + '/categories');
    await expect(page.locator(`tbody tr:has-text("${CAT}")`)).toHaveCount(0);
    await page.goto(BASE + '/units');
    await expect(page.locator(`tbody tr:has-text("${UNIT}")`)).toHaveCount(0);
    await page.goto(BASE + '/warehouses');
    await expect(page.locator(`tbody tr:has-text("${WH}")`)).toHaveCount(0);
  } finally {
    // Admin sementara dihapus lewat utility reset (tidak ada workflow hapus user di aplikasi)
    execSync(`php "${SEED}" cleanup_by_email ${adminEmail}`, { encoding: 'utf8' });
  }
});
