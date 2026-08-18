const { test, expect } = require('@playwright/test');
const { execSync } = require('child_process');
const path = require('path');

const BASE = 'http://localhost:8000';
const SEED = path.join(__dirname, 'helpers', 'user_seed.php');
const ITEM = path.join(__dirname, 'helpers', 'item_state.php');
const STOCK_DATA = path.join(__dirname, 'helpers', 'stock_data.php');

function runPhp(file, args) {
  return execSync(`php "${file}" ${args}`, { encoding: 'utf8' }).trim();
}
function getItem(kode) {
  const raw = runPhp(ITEM, `get ${kode}`);
  if (!raw) return null;
  const [id, active, used] = raw.split('|');
  return { id: Number(id), active: Number(active), used: Number(used) };
}
function cleanupItem(id) { if (id) runPhp(ITEM, `cleanup ${id}`); }
// Global Row Action UI Standard: aksi status/delete berada di menu [⋮]. Buka menu row sebelum interaksi (idempotent).
async function openMenu(row) {
  const toggle = row.locator('[data-actions-toggle]');
  const expanded = await toggle.getAttribute('aria-expanded');
  if (expanded !== 'true') {
    await toggle.click();
  }
  await expect(row.locator('[data-actions-menu]')).toBeVisible();
}

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

async function createTempAdmin(page) {
  const email = `stkadmin_${Date.now()}@ptbig.com`;
  const pw = 'stkadmin123';
  await login(page, 'superadmin@ptbig.com', 'superadmin123');
  await page.goto(BASE + '/users/create');
  await page.fill('input[name="name"]', 'STK Admin');
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="phone"]', '0815');
  await page.selectOption('select[name="role"]', 'ADMIN');
  await page.fill('input[name="password"]', pw);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/\/users$/);
  await page.goto(BASE + '/logout');
  return { email, pw };
}

async function createBulkItemViaUI(page, name) {
  await page.goto(BASE + '/items');
  await page.click('button:has-text("Tambah Barang")');
  await page.fill('#addModal input[name="nama"]', name);
  await page.selectOption('#addModal select[name="item_category_id"]', { index: 1 });
  await page.selectOption('#addModal select[name="unit_id"]', { index: 1 });
  await page.fill('#addModal input[name="stok_minimum"]', '0');
  await page.click('#addModal form button[type="submit"]');
  await expect(page).toHaveURL(/\/items$/);
}

async function csrfFrom(page, path) {
  await page.goto(BASE + path);
  return page.locator('input[name="csrf_token"]').first().getAttribute('value');
}

// POST form dengan encoding PHP yang benar (array -> key[]). Pakai fetch dalam konteks
// halaman agar session/cookie & CSRF konsisten; redirect diikuti otomatis.
async function postForm(page, url, fields) {
  const body = new URLSearchParams();
  for (const [k, v] of Object.entries(fields)) {
    if (Array.isArray(v)) { for (const item of v) body.append(k + '[]', item); }
    else { body.append(k, v); }
  }
  return page.evaluate(async ({ url, body }) => {
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body
    }).then(r => r.status);
  }, { url, body: body.toString() });
}

test.describe('Stok Barang Monitoring & Master Item Lifecycle', () => {

  test('1: ADMIN — Stok Barang lengkap (kolom, search, filter, Lihat)', async ({ page }) => {
    const admin = await createTempAdmin(page);
    try {
      await login(page, admin.email, admin.pw);
      await page.goto(BASE + '/stocks');

      // Kolom lengkap
      for (const col of ['Tipe', 'Stok Fisik', 'Available', 'Reserved', 'Allocated', 'Satuan', 'Status', 'Aksi']) {
        await expect(page.locator('th', { hasText: col }).first()).toBeVisible();
      }
      await expect(page.locator('tbody tr a:has-text("Lihat")').first()).toBeVisible();

      // Search bekerja
      const searchVal = await page.locator('tbody tr').first().locator('td').nth(2).textContent();
      await page.fill('input[name="search"]', searchVal.trim());
      await page.click('form[action*="/stocks"] button[type="submit"]');
      await expect(page).toHaveURL(/search=/);
      await expect(page.locator('tbody tr').first()).toContainText(searchVal.trim());

      // Filter gudang bekerja
      await page.selectOption('select[name="warehouse_id"]', { index: 1 });
      await page.click('form[action*="/stocks"] button[type="submit"]');
      await expect(page).toHaveURL(/warehouse_id=/);
    } finally {
      cleanupItem(0);
      execSync(`php "${SEED}" cleanup_by_email ${admin.email}`);
    }
  });

  test('2: SUPERADMIN — Stok Barang sama, Lihat tersedia', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com', 'superadmin123');
    await page.goto(BASE + '/stocks');
    await expect(page.locator('th', { hasText: 'Stok Fisik' })).toBeVisible();
    await expect(page.locator('th', { hasText: 'Aksi' })).toBeVisible();
    await expect(page.locator('tbody tr a:has-text("Lihat")').first()).toBeVisible();
  });

  test('3: Klik Lihat -> Inventory 360 (items/show)', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com', 'superadmin123');
    await page.goto(BASE + '/stocks');
    await page.locator('tbody tr a:has-text("Lihat")').first().click();
    await expect(page).toHaveURL(/\/items\/show\/\d+/);
    await expect(page.locator('body')).toContainText('Identitas Barang');
  });

  test('4: Perhitungan stok existing tidak berubah (Fisik & Available = DB)', async ({ page }) => {
    const data = runPhp(STOCK_DATA, '');
    expect(data).not.toBe('');
    const [kode, whId, whName, physical, available] = data.split('|');
    await login(page, 'superadmin@ptbig.com', 'superadmin123');
    await page.goto(BASE + '/stocks');

    const row = page.locator(`tbody tr:has-text("${kode}")`).filter({ hasText: whName }).first();
    await expect(row).toHaveCount(1);
    const fisikCell = Number((await row.locator('td').nth(6).textContent()).replace(/[^\d]/g, ''));
    const availCell = Number((await row.locator('td').nth(7).textContent()).replace(/[^\d]/g, ''));
    expect(fisikCell).toBe(Number(physical));
    expect(availCell).toBe(Number(available));
  });

  test('5: Hapus permanen barang yang belum pernah digunakan (modal + sukses)', async ({ page }) => {
    const admin = await createTempAdmin(page);
    const name = 'STK Dummy ' + Date.now();
    const dialogs = [];
    page.on('dialog', d => { dialogs.push(d.message()); d.dismiss().catch(() => {}); });
    let kode = '';
    try {
      await login(page, admin.email, admin.pw);
      await createBulkItemViaUI(page, name);

      // Ambil kode barang dari baris
      const row = page.locator(`tbody tr:has-text("${name}")`).first();
      await expect(row).toHaveCount(1);
      kode = (await row.locator('td').nth(2).textContent()).trim();
      expect(getItem(kode).used).toBe(0);

      // Hapus tersedia + modal custom (aksi ada di menu [⋮])
      await openMenu(row);
      await expect(row.locator('form[data-confirm-title="Hapus Master Barang?"]')).toBeVisible();
      await row.locator('button:has-text("Hapus")').click();
      await expect(page.locator('[data-appmodal="backdrop"]')).toBeVisible();
      await expect(page.locator('#appModalTitle')).toHaveText('Hapus Master Barang?');
      await page.locator('[data-appmodal="backdrop"] button:has-text("Hapus")').click();

      // Barang hilang dari daftar & DB
      await expect(page).toHaveURL(/\/items$/);
      await expect(page.locator(`tbody tr:has-text("${name}")`)).toHaveCount(0);
      expect(getItem(kode)).toBeNull();
      expect(dialogs).toEqual([]);
    } finally {
      const it = getItem(kode || name);
      cleanupItem(it ? it.id : 0);
      execSync(`php "${SEED}" cleanup_by_email ${admin.email}`);
    }
  });

  test('6: Barang sudah dipakai -> Nonaktifkan tersedia, Hapus tidak tampil', async ({ page }) => {
    const admin = await createTempAdmin(page);
    try {
      await login(page, admin.email, admin.pw);
      await page.goto(BASE + '/items');
      // Ambil barang yang jelas sudah dipakai (stok > 0 di DB), lalu cari agar pasti tampil
      const data = runPhp(STOCK_DATA, '');
      const kode = data.split('|')[0];
      await page.fill('input[name="search"]', kode);
      await page.click('form[action*="/items"] button[type="submit"]');
      await expect(page).toHaveURL(/search=/);
      const row = page.locator(`tbody tr:has-text("${kode}")`).first();
      await expect(row).toHaveCount(1);
      await openMenu(row);
      await expect(row.locator('form[data-confirm-title="Nonaktifkan Barang?"]')).toBeVisible();
      await expect(row.locator('form[data-confirm-title="Hapus Master Barang?"]')).toHaveCount(0);
    } finally {
      execSync(`php "${SEED}" cleanup_by_email ${admin.email}`);
    }
  });

  test('7: Hit endpoint delete langsung untuk barang berhistory -> backend menolak, data aman', async ({ page }) => {
    const admin = await createTempAdmin(page);
    try {
      const data = runPhp(STOCK_DATA, '');
      const kode = data.split('|')[0];
      const before = getItem(kode);
      const stockBefore = runPhp(ITEM, `stock ${before.id}`);

      await login(page, admin.email, admin.pw);
      const csrf = await csrfFrom(page, '/items');
      // POST langsung ke endpoint delete (simulasi bypass UI) — redirect manual agar flash error terbaca
      await page.evaluate(async ({ url, csrf }) => {
        await fetch(url, {
          method: 'POST',
          redirect: 'manual',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'csrf_token=' + encodeURIComponent(csrf)
        });
      }, { url: BASE + '/items/delete/' + before.id, csrf });
      await page.goto(BASE + '/items');
      await expect(page.locator('body')).toContainText('Barang tidak dapat dihapus karena sudah memiliki riwayat transaksi. Gunakan Nonaktifkan Barang.');

      // Data & histori aman
      const after = getItem(kode);
      expect(after).not.toBeNull();
      expect(after.id).toBe(before.id);
      expect(after.active).toBe(1);
      expect(runPhp(ITEM, `stock ${before.id}`)).toBe(stockBefore);
    } finally {
      execSync(`php "${SEED}" cleanup_by_email ${admin.email}`);
    }
  });

  test('8: Nonaktifkan barang berhistory (stok habis) -> is_active=0, tidak dipakai transaksi baru', async ({ page }) => {
    const admin = await createTempAdmin(page);
    const name = 'STK Lifecycle ' + Date.now();
    const dialogs = [];
    page.on('dialog', d => { dialogs.push(d.message()); d.dismiss().catch(() => {}); });
    let kode = '';
    try {
      await login(page, admin.email, admin.pw);

      // Buat barang bulk, masuk 1, keluar 1 -> history ada, stok 0
      await createBulkItemViaUI(page, name);
      kode = (await page.locator(`tbody tr:has-text("${name}")`).first().locator('td').nth(2).textContent()).trim();
      let it = getItem(kode);
      expect(it).not.toBeNull();

      const whId = runPhp(STOCK_DATA, '').split('|')[1];
      const csrf = await csrfFrom(page, '/incoming/create');
      const s1 = await postForm(page, BASE + '/incoming/store', {
        csrf_token: csrf, warehouse_id: whId, supplier_info: 'Test', notes: '',
        item_ids: [String(it.id)], quantities: ['1']
      });
      const csrf2 = await csrfFrom(page, '/outgoing/create');
      const s2 = await postForm(page, BASE + '/outgoing/store', {
        csrf_token: csrf2, warehouse_id: whId, submission_id: '', notes: '',
        item_ids: [String(it.id)], quantities: ['1']
      });
      expect(s1).toBe(200);
      expect(s2).toBe(200);

      it = getItem(kode);
      expect(it.used).toBe(1);
      expect(Number(runPhp(ITEM, `stock ${it.id}`))).toBe(0);

      // UI: Nonaktifkan tersedia (bukan Hapus)
      await page.goto(BASE + '/items');
      const row = page.locator(`tbody tr:has-text("${kode}")`).first();
      await openMenu(row);
      await expect(row.locator('form[data-confirm-title="Nonaktifkan Barang?"]')).toBeVisible();
      await row.locator('button:has-text("Nonaktifkan")').click();
      await expect(page.locator('#appModalTitle')).toHaveText('Nonaktifkan Barang?');
      await page.locator('[data-appmodal="backdrop"] button:has-text("Nonaktifkan")').click();
      await expect(page).toHaveURL(/\/items$/);

      expect(getItem(kode).active).toBe(0);
      await expect(page.locator('body')).toContainText('berhasil dinonaktifkan');
      expect(dialogs).toEqual([]);

      // Barang nonaktif tidak bisa dipilih untuk incoming baru (master_items WHERE is_active = 1)
      await page.goto(BASE + '/incoming/create');
      const html = await page.content();
      expect(html).not.toContain(kode);
    } finally {
      const it = getItem(kode || name);
      cleanupItem(it ? it.id : 0);
      execSync(`php "${SEED}" cleanup_by_email ${admin.email}`);
    }
  });

  test('9: Stok Barang TIDAK punya aksi Delete (stok hanya berubah via transaksi)', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com', 'superadmin123');
    await page.goto(BASE + '/stocks');
    await expect(page.locator('button:has-text("Hapus"), a:has-text("Hapus")')).toHaveCount(0);
    // Panduan menegaskan aturan
    await expect(page.locator('body')).toContainText('bukan tempat menghapus Master Barang');
  });

  test('10: Regression — halaman transaksi & serial tetap bisa dibuka', async ({ page }) => {
    const admin = await createTempAdmin(page);
    try {
      await login(page, admin.email, admin.pw);
      for (const [url, heading] of [
        ['/incoming', 'Barang Masuk'],
        ['/outgoing', 'Barang Keluar'],
        ['/transfers', 'Transfer Gudang'],
        ['/adjustments', 'Penyesuaian Stok'],
        ['/serials', 'Nomor Seri'],
        ['/search', 'Pencarian Barang'],
      ]) {
        await page.goto(BASE + url);
        await expect(page.locator('body')).not.toContainText('500');
        await expect(page.locator('h1, h2').first()).toBeVisible();
      }
    } finally {
      execSync(`php "${SEED}" cleanup_by_email ${admin.email}`);
    }
  });
});
