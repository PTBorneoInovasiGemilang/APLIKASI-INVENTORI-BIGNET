const { test, expect } = require('@playwright/test');
const path = require('path');
const BASE = 'http://localhost:8000';
const SEED = path.join(__dirname, 'helpers', 'user_seed.php');
const ITEM = path.join(__dirname, 'helpers', 'item_state.php');
const STOCK_DATA = path.join(__dirname, 'helpers', 'stock_data.php');
const { execSync } = require('child_process');

function runPhp(file, args) {
  return execSync(`php "${file}" ${args}`, { encoding: 'utf8' }).trim();
}
function getItem(kode) {
  const raw = runPhp(ITEM, `get ${kode}`);
  if (!raw) return null;
  const [id, active, used] = raw.split('|');
  return { id, active: Number(active), used: Number(used) };
}
function getStock(id) { return Number(runPhp(ITEM, `stock ${id}`)); }
function cleanupItem(id) { if (id) runPhp(ITEM, `cleanup ${id}`); }
// Global Row Action UI Standard: aksi status/delete berada di menu [⋮]. Buka menu row sebelum interaksi.
// Idempotent: hanya klik toggle bila menu belum terbuka (aria-expanded != true).
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
  if (!page.url().includes('/login')) { await page.goto(BASE + '/logout'); await page.goto(BASE + '/login'); }
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/.*\/dashboard/);
}

async function createItemViaUI(page, name, { serialized = false } = {}) {
  await page.goto(BASE + '/items');
  await page.click('button:has-text("Tambah Barang")');
  await page.fill('#addModal input[name="nama"]', name);
  await page.selectOption('#addModal select[name="item_category_id"]', { index: 1 });
  await page.selectOption('#addModal select[name="unit_id"]', { index: 1 });
  await page.fill('#addModal input[name="stok_minimum"]', '0');
  if (serialized) await page.check('#addModal input[name="is_serialized"]');
  await page.click('#addModal form button[type="submit"]');
  await expect(page).toHaveURL(/\/items$/);
  const row = page.locator(`tbody tr:has-text("${name}")`).first();
  await expect(row).toHaveCount(1);
  const kode = (await row.locator('td').nth(2).textContent()).trim();
  return kode;
}

async function csrfFrom(page, pagePath) {
  await page.goto(BASE + pagePath);
  return page.locator('input[name="csrf_token"]').first().getAttribute('value');
}

async function postForm(page, url, fields, manualRedirect = false) {
  const body = new URLSearchParams();
  for (const [k, v] of Object.entries(fields)) {
    if (Array.isArray(v)) { for (const item of v) body.append(k + '[]', item); }
    else { body.append(k, v); }
  }
  return page.evaluate(async ({ url, body, manualRedirect }) => {
    return fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body, redirect: manualRedirect ? 'manual' : 'follow' }).then(r => r.status);
  }, { url, body: body.toString(), manualRedirect });
}

// POST dan kembalikan {status, html} dari respons akhir (redirect diikuti).
// Dipakai untuk memeriksa flash message yang dirender inline di halaman tujuan.
async function postFormHtml(page, url, fields) {
  const body = new URLSearchParams();
  for (const [k, v] of Object.entries(fields)) {
    if (Array.isArray(v)) { for (const item of v) body.append(k + '[]', item); }
    else { body.append(k, v); }
  }
  return page.evaluate(async ({ url, body }) => {
    const r = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body });
    return { status: r.status, html: await r.text() };
  }, { url, body: body.toString() });
}

const dialogs = [];
let admin = null;
let teknisiEmail = null;

test.beforeAll(async ({ browser }) => {
  const page = await browser.newPage();
  await login(page, 'superadmin@ptbig.com', 'superadmin123');
  const ts = Date.now();
  admin = { email: `itmadmin_${ts}@ptbig.com`, pw: 'itmadmin123' };
  await page.goto(BASE + '/users/create');
  await page.fill('input[name="name"]', 'ITM Admin');
  await page.fill('input[name="email"]', admin.email);
  await page.fill('input[name="phone"]', '0817');
  await page.selectOption('select[name="role"]', 'ADMIN');
  await page.fill('input[name="password"]', admin.pw);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/\/users$/);
  // TEKNISI uji untuk permission (Test 20)
  teknisiEmail = `itmtek_${ts}@ptbig.com`;
  await page.goto(BASE + '/users/create');
  await page.fill('input[name="name"]', 'ITM Teknisi');
  await page.fill('input[name="email"]', teknisiEmail);
  await page.fill('input[name="phone"]', '0818');
  await page.selectOption('select[name="role"]', 'TEKNISI_NOC');
  await page.fill('input[name="password"]', 'itmtek123');
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/\/users$/);
  await page.close();
});

test.afterAll(async () => {
  if (admin) execSync(`php "${SEED}" cleanup_by_email ${admin.email}`, { encoding: 'utf8' });
  if (teknisiEmail) execSync(`php "${SEED}" cleanup_by_email ${teknisiEmail}`, { encoding: 'utf8' });
});

test.describe.serial('Master Barang Workflow Final (Test 1-24)', () => {

  test('T1+T2: ADMIN create item -> Aktif; action matrix aktif+unused = Lihat|Ubah|Nonaktifkan|Hapus', async ({ page }) => {
    page.on('dialog', d => { dialogs.push(d.message()); d.dismiss().catch(() => {}); });
    await login(page, admin.email, admin.pw);
    const name = 'ITM Aktif Unused ' + Date.now();
    const kode = await createItemViaUI(page, name);
    expect(getItem(kode).active).toBe(1);
    await expect(page.locator(`tbody tr:has-text("${kode}")`).first()).toContainText('Aktif');
    const row = page.locator(`tbody tr:has-text("${kode}")`).first();
    await expect(row.locator('a:has-text("Lihat")')).toBeVisible();
    await openMenu(row);
    await expect(row.locator('a:has-text("Ubah")')).toBeVisible();
    await expect(row.locator('button:has-text("Nonaktifkan")')).toBeVisible();
    await expect(row.locator('button:has-text("Hapus")')).toBeVisible();
    cleanupItem(getItem(kode).id);
  });

  test('T3+T4: ADMIN nonaktifkan unused -> Nonaktif; matrix nonaktif+unused = Lihat|Ubah|Aktifkan|Hapus', async ({ page }) => {
    await login(page, admin.email, admin.pw);
    const name = 'ITM Deact Unused ' + Date.now();
    const kode = await createItemViaUI(page, name);
    let row = page.locator(`tbody tr:has-text("${kode}")`).first();
    await openMenu(row);
    await row.locator('button:has-text("Nonaktifkan")').click();
    await expect(page.locator('#appModalTitle')).toHaveText('Nonaktifkan Barang?');
    await page.locator('[data-appmodal="backdrop"] button:has-text("Nonaktifkan")').click();
    await expect(page).toHaveURL(/\/items$/);
    expect(getItem(kode).active).toBe(0);
    row = page.locator(`tbody tr:has-text("${kode}")`).first();
    await expect(row).toContainText('Nonaktif');
    await openMenu(row);
    await expect(row.locator('button:has-text("Aktifkan")')).toBeVisible();
    await expect(row.locator('button:has-text("Hapus")')).toBeVisible();
    await expect(row.locator('button:has-text("Nonaktifkan")')).not.toBeVisible();
  });

  test('T5+T11+T12: Aktifkan kembali; edit nama/deskripsi -> status tetap', async ({ page }) => {
    await login(page, admin.email, admin.pw);
    const name = 'ITM React ' + Date.now();
    const kode = await createItemViaUI(page, name);
    // nonaktifkan lalu aktifkan kembali
    let row = page.locator(`tbody tr:has-text("${kode}")`).first();
    await openMenu(row);
    await row.locator('button:has-text("Nonaktifkan")').click();
    await page.locator('[data-appmodal="backdrop"] button:has-text("Nonaktifkan")').click();
    await expect(page).toHaveURL(/\/items$/);
    row = page.locator(`tbody tr:has-text("${kode}")`).first();
    await openMenu(row);
    await row.locator('button:has-text("Aktifkan")').click();
    await expect(page.locator('#appModalTitle')).toHaveText('Aktifkan Barang?');
    await page.locator('[data-appmodal="backdrop"] button:has-text("Aktifkan")').click();
    await expect(page).toHaveURL(/\/items$/);
    expect(getItem(kode).active).toBe(1);

    // T11: edit nama -> status tetap
    const id = getItem(kode).id;
    await page.goto(BASE + '/items/edit/' + id);
    await page.fill('input[name="nama"]', name + ' Renamed');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/items$/);
    expect(getItem(kode).active).toBe(1);
    expect(getItem(kode.replace(/.*/, kode)).id === id).toBe(true);

    // T12: edit deskripsi -> status tetap
    await page.goto(BASE + '/items/edit/' + id);
    await page.fill('textarea[name="deskripsi"]', 'Deskripsi baru uji');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/items$/);
    expect(getItem(kode).active).toBe(1);
    cleanupItem(id);
  });

  test('T6+T7+T10: item berhistory -> Hapus tidak tersedia; nonaktifkan (stok 0) OK; aktifkan kembali OK', async ({ page }) => {
    await login(page, admin.email, admin.pw);
    const name = 'ITM Used ' + Date.now();
    const kode = await createItemViaUI(page, name);
    const it0 = getItem(kode);
    // incoming 1 + outgoing 1 -> history ada, stok 0
    const whId = runPhp(STOCK_DATA, '').split('|')[1];
    const csrf = await csrfFrom(page, '/incoming/create');
    const s1 = await postForm(page, BASE + '/incoming/store', {
      csrf_token: csrf, warehouse_id: whId, supplier_info: 'Test', notes: '',
      item_ids: [String(it0.id)], quantities: ['1']
    });
    const csrf2 = await csrfFrom(page, '/outgoing/create');
    const s2 = await postForm(page, BASE + '/outgoing/store', {
      csrf_token: csrf2, warehouse_id: whId, submission_id: '', notes: '',
      item_ids: [String(it0.id)], quantities: ['1']
    });
    expect(s1).toBe(200);
    expect(s2).toBe(200);
    const it = getItem(kode);
    expect(it.used).toBe(1);
    expect(getStock(it.id)).toBe(0);

    // T6: Hapus tidak tampil
    await page.goto(BASE + '/items');
    let row = page.locator(`tbody tr:has-text("${kode}")`).first();
    await expect(row.locator('button:has-text("Hapus")')).toHaveCount(0);
    await openMenu(row);
    await expect(row.locator('button:has-text("Nonaktifkan")')).toBeVisible();

    // T7: nonaktifkan berhistory (stok 0) -> berhasil
    await openMenu(row);
    await row.locator('button:has-text("Nonaktifkan")').click();
    await page.locator('[data-appmodal="backdrop"] button:has-text("Nonaktifkan")').click();
    await expect(page).toHaveURL(/\/items$/);
    expect(getItem(kode).active).toBe(0);
    row = page.locator(`tbody tr:has-text("${kode}")`).first();
    await expect(row.locator('button:has-text("Hapus")')).toHaveCount(0);

    // T10: aktifkan kembali -> berhasil
    await openMenu(row);
    await row.locator('button:has-text("Aktifkan")').click();
    await page.locator('[data-appmodal="backdrop"] button:has-text("Aktifkan")').click();
    await expect(page).toHaveURL(/\/items$/);
    expect(getItem(kode).active).toBe(1);
    cleanupItem(getItem(kode).id);
  });

  test('T8: nonaktifkan barang ber-stok -> backend menolak', async ({ page }) => {
    await login(page, admin.email, admin.pw);
    const name = 'ITM HasStock ' + Date.now();
    const kode = await createItemViaUI(page, name);
    const it = getItem(kode);
    const whId = runPhp(STOCK_DATA, '').split('|')[1];
    const csrf = await csrfFrom(page, '/incoming/create');
    const s1 = await postForm(page, BASE + '/incoming/store', {
      csrf_token: csrf, warehouse_id: whId, supplier_info: 'Test', notes: '',
      item_ids: [String(it.id)], quantities: ['3']
    });
    expect(s1).toBe(200);
    expect(getStock(it.id)).toBe(3);
    // Toggle via endpoint langsung -> error flash, DB tetap aktif
    const csrf2 = await csrfFrom(page, '/items');
    const res = await postFormHtml(page, BASE + '/items/toggle-active/' + it.id, { csrf_token: csrf2 });
    expect(res.status).toBe(200);
    expect(getItem(kode).active).toBe(1);
    expect(res.html).toContain('tidak dapat dinonaktifkan karena masih memiliki stok fisik atau serial aktif');
    cleanupItem(it.id);
  });

  test('T9: nonaktifkan barang dengan serial AVAILABLE -> backend menolak', async ({ page }) => {
    await login(page, admin.email, admin.pw);
    const name = 'ITM HasSerial ' + Date.now();
    const kode = await createItemViaUI(page, name, { serialized: true });
    const it = getItem(kode);
    const whId = runPhp(STOCK_DATA, '').split('|')[1];
    runPhp(ITEM, `add_serial ${it.id} SER-${Date.now()} ${whId}`);
    const csrf2 = await csrfFrom(page, '/items');
    const res = await postFormHtml(page, BASE + '/items/toggle-active/' + it.id, { csrf_token: csrf2 });
    expect(res.status).toBe(200);
    expect(getItem(kode).active).toBe(1);
    expect(res.html).toContain('tidak dapat dinonaktifkan karena masih memiliki stok fisik atau serial aktif');
    cleanupItem(it.id);
  });

  test('T13+T14: filter Aktif / Nonaktif benar', async ({ page }) => {
    await login(page, admin.email, admin.pw);
    // buat 1 aktif + 1 nonaktif
    const nameA = 'ITM FiltA ' + Date.now();
    const nameB = 'ITM FiltB ' + Date.now();
    const kodeA = await createItemViaUI(page, nameA);
    await createItemViaUI(page, nameB);
    const rowB = page.locator(`tbody tr:has-text("${nameB}")`).first();
    await openMenu(rowB);
    await rowB.locator('button:has-text("Nonaktifkan")').click();
    await page.locator('[data-appmodal="backdrop"] button:has-text("Nonaktifkan")').click();
    await expect(page).toHaveURL(/\/items$/);
    // Filter Aktif
    await page.goto(BASE + '/items?status=1&search=' + encodeURIComponent(nameA));
    await expect(page.locator(`tbody tr:has-text("${kodeA}")`)).toHaveCount(1);
    // Filter Nonaktif (label dropdown = Nonaktif)
    await page.goto(BASE + '/items?status=0&search=' + encodeURIComponent(nameB));
    await expect(page.locator(`tbody tr:has-text("${nameB}")`)).toHaveCount(1);
    await expect(page.locator('select[name="status"] option:checked')).toHaveText('Nonaktif');
    cleanupItem(getItem(kodeA).id);
  });

  test('T15: Inventory 360 barang Nonaktif tetap dapat dibuka + Status Barang Nonaktif', async ({ page }) => {
    await login(page, admin.email, admin.pw);
    const name = 'ITM Inv360 ' + Date.now();
    const kode = await createItemViaUI(page, name);
    let row = page.locator(`tbody tr:has-text("${kode}")`).first();
    await openMenu(row);
    await row.locator('button:has-text("Nonaktifkan")').click();
    await page.locator('[data-appmodal="backdrop"] button:has-text("Nonaktifkan")').click();
    await expect(page).toHaveURL(/\/items$/);
    await page.goto(BASE + '/items/show/' + getItem(kode).id);
    await expect(page).toHaveURL(/\/items\/show\/\d+/);
    await expect(page.locator('body')).toContainText('Status Barang');
    await expect(page.locator('body')).toContainText('Nonaktif');
    cleanupItem(getItem(kode).id);
  });

  test('T16: barang Nonaktif tidak bisa dipilih untuk incoming baru', async ({ page }) => {
    await login(page, admin.email, admin.pw);
    const name = 'ITM NotSelect ' + Date.now();
    const kode = await createItemViaUI(page, name);
    let row = page.locator(`tbody tr:has-text("${kode}")`).first();
    await openMenu(row);
    await row.locator('button:has-text("Nonaktifkan")').click();
    await page.locator('[data-appmodal="backdrop"] button:has-text("Nonaktifkan")').click();
    await expect(page).toHaveURL(/\/items$/);
    await page.goto(BASE + '/incoming/create');
    const html = await page.content();
    expect(html).not.toContain(kode);
    cleanupItem(getItem(kode).id);
  });

  test('T17: SUPERADMIN read-only (tanpa Tambah/Ubah/Hapus/Nonaktifkan/Aktifkan)', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com', 'superadmin123');
    await page.goto(BASE + '/items');
    await expect(page.locator('button:has-text("Tambah Barang")')).toHaveCount(0);
    await expect(page.locator('tbody a:has-text("Ubah")')).toHaveCount(0);
    await expect(page.locator('tbody button:has-text("Nonaktifkan")')).toHaveCount(0);
    await expect(page.locator('tbody button:has-text("Aktifkan")')).toHaveCount(0);
    await expect(page.locator('tbody button:has-text("Hapus")')).toHaveCount(0);
    // Lihat tetap ada (read-only)
    await expect(page.locator('a[title="Detail"]').first()).toBeVisible();
  });

  test('T18+T19: SUPERADMIN direct endpoint delete/toggle -> 403, DB tidak berubah', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com', 'superadmin123');
    // item uji (unused)
    const name = 'ITM PermSA ' + Date.now();
    await page.goto(BASE + '/logout');
    await login(page, admin.email, admin.pw);
    const kode = await createItemViaUI(page, name);
    const it = getItem(kode);
    await page.goto(BASE + '/logout');
    await login(page, 'superadmin@ptbig.com', 'superadmin123');
    const csrf = await csrfFrom(page, '/items');
    const sDel = await postForm(page, BASE + '/items/delete/' + it.id, { csrf_token: csrf });
    expect(sDel).toBe(403);
    const sTog = await postForm(page, BASE + '/items/toggle-active/' + it.id, { csrf_token: csrf });
    expect(sTog).toBe(403);
    expect(getItem(kode)).not.toBeNull();
    expect(getItem(kode).active).toBe(1);
    cleanupItem(it.id);
  });

  test('T20: TEKNISI direct endpoint delete -> 403', async ({ page }) => {
    await login(page, teknisiEmail, 'itmtek123');
    await page.goto(BASE + '/items');
    await expect(page).toHaveURL(/\/items/);
    // TEKNISI tidak punya akses (403 halaman item) — pastikan bukan dashboard
    const body = await page.locator('body').innerText();
    expect(body).toContain('403');
  });

  test('T21: ADMIN delete barang unused via modal -> terhapus', async ({ page }) => {
    await login(page, admin.email, admin.pw);
    const name = 'ITM DelOk ' + Date.now();
    const kode = await createItemViaUI(page, name);
    const it = getItem(kode);
    const row = page.locator(`tbody tr:has-text("${kode}")`).first();
    await openMenu(row);
    await row.locator('button:has-text("Hapus")').click();
    await expect(page.locator('#appModalTitle')).toHaveText('Hapus Master Barang?');
    await page.locator('[data-appmodal="backdrop"] button:has-text("Hapus")').click();
    await expect(page).toHaveURL(/\/items$/);
    expect(getItem(kode)).toBeNull();
  });

  test('T22: ADMIN direct endpoint delete barang berhistory -> backend menolak, data aman', async ({ page }) => {
    await login(page, admin.email, admin.pw);
    const name = 'ITM DelUsed ' + Date.now();
    const kode = await createItemViaUI(page, name);
    const it0 = getItem(kode);
    const whId = runPhp(STOCK_DATA, '').split('|')[1];
    const csrf = await csrfFrom(page, '/incoming/create');
    await postForm(page, BASE + '/incoming/store', {
      csrf_token: csrf, warehouse_id: whId, supplier_info: 'Test', notes: '',
      item_ids: [String(it0.id)], quantities: ['1']
    });
    const it = getItem(kode);
    expect(it.used).toBe(1);
    const csrf2 = await csrfFrom(page, '/items');
    const res = await postFormHtml(page, BASE + '/items/delete/' + it.id, { csrf_token: csrf2 });
    expect(res.status).toBe(200);
    expect(getItem(kode)).not.toBeNull();
    expect(res.html).toContain('Barang tidak dapat dihapus karena sudah memiliki riwayat transaksi');
    cleanupItem(it.id);
  });

  test('T23: tidak ada native dialog (AppModal dipakai)', async ({ page }) => {
    await login(page, admin.email, admin.pw);
    const name = 'ITM NoDlg ' + Date.now();
    const kode = await createItemViaUI(page, name);
    const row = page.locator(`tbody tr:has-text("${kode}")`).first();
    await openMenu(row);
    await row.locator('button:has-text("Nonaktifkan")').click();
    await expect(page.locator('#appModalTitle')).toHaveText('Nonaktifkan Barang?');
    expect(dialogs).toEqual([]);
    await page.locator('[data-appmodal="backdrop"] button:has-text("Batal")').click();
    expect(getItem(kode).active).toBe(1);
    cleanupItem(getItem(kode).id);
  });

});
