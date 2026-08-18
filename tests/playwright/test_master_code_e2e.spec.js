const { test, expect } = require('@playwright/test');
const path = require('path');
const BASE = 'http://localhost:8000';
const SEED = path.join(__dirname, 'helpers', 'user_seed.php');
const MC = path.join(__dirname, 'helpers', 'master_code_state.php');
const { execSync } = require('child_process');

const ts = Date.now();
const PREFIX = 'MC ' + ts;
const CAT = PREFIX + ' Cat';
const UNIT1 = PREFIX + ' Unit A';
const UNIT2 = PREFIX + ' Unit B';
const WH = PREFIX + ' Wh';
const LOC1 = PREFIX + ' Loc A';
const LOC2 = PREFIX + ' Loc B';
const ITEM = PREFIX + ' Item';

function mc(mode, ...args) {
  return execSync(`php "${MC}" ${mode} ${args.map(a => `"${a}"`).join(' ')}`, { encoding: 'utf8' }).trim();
}
function getCodes(table, like) { return mc('get', table, like); }
function getIds(table, like) { return mc('getid', table, like); }

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

test('Master Data auto code — Satuan/Kategori/Gudang/Lokasi/Barang (Test A-I)', async ({ page }) => {
  // Pre-clean residu run sebelumnya (idempotent)
  execSync(`php "${MC}" cleanup "${'MC '}"`, { encoding: 'utf8' });

  // Setup ADMIN sementara
  await login(page, 'superadmin@ptbig.com', 'superadmin123');
  const adminEmail = `mcadmin_${ts}@ptbig.com`;
  await page.goto(BASE + '/users/create');
  await page.fill('input[name="name"]', 'MC Admin');
  await page.fill('input[name="email"]', adminEmail);
  await page.fill('input[name="phone"]', '0813');
  await page.selectOption('select[name="role"]', 'ADMIN');
  await page.fill('input[name="password"]', 'mcadmin123');
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/\/users$/);
  await page.goto(BASE + '/logout');

  try {
    await login(page, adminEmail, 'mcadmin123');

    // TEST D — Kategori: kode auto KAT-xxxxxx (tanpa input kode)
    await page.goto(BASE + '/categories');
    await page.click('button:has-text("Tambah Kategori")');
    await expect(page.locator('#addModal input[name="code"]')).toHaveCount(0);
    await page.fill('#addModal input[name="name"]', CAT);
    await page.click('#addModal form button[type="submit"]');
    const catCode = getCodes('item_categories', PREFIX + ' Cat');
    expect(catCode).toMatch(/^KAT-\d{6}$/);

    // TEST A — Satuan: kode auto UNT-xxxxxx tanpa input kode
    await page.goto(BASE + '/units');
    await page.click('button:has-text("Tambah Satuan")');
    await expect(page.locator('#addModal input[name="code"]')).toHaveCount(0);
    await page.fill('#addModal input[name="name"]', UNIT1);
    await page.click('#addModal form button[type="submit"]');
    let unitCodes = getCodes('units', PREFIX + ' Unit A');
    expect(unitCodes).toMatch(/^UNT-\d{6}$/);
    const unit1Code = unitCodes;

    // TEST C — Duplicate/collision: unit kedua mendapat kode berbeda (sequential)
    await page.click('button:has-text("Tambah Satuan")');
    await page.fill('#addModal input[name="name"]', UNIT2);
    await page.click('#addModal form button[type="submit"]');
    const unit2Code = getCodes('units', PREFIX + ' Unit B');
    expect(unit2Code).toMatch(/^UNT-\d{6}$/);
    expect(unit2Code).not.toBe(unit1Code);

    // TEST B — Edit Satuan: ganti nama, kode tetap
    const unit1Id = getIds('units', PREFIX + ' Unit A');
    await page.goto(BASE + '/units/edit/' + unit1Id);
    await page.fill('input[name="name"]', PREFIX + ' Unit A Renamed');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/units$/);
    expect(getCodes('units', PREFIX + ' Unit A Renamed')).toBe(unit1Code);
    expect(getCodes('units', PREFIX + ' Unit A')).toBe('');

    // TEST E — Gudang: kode auto GDG-xxxxxx, stabil saat nama diubah
    await page.goto(BASE + '/warehouses');
    await page.click('button:has-text("Tambah Gudang")');
    await expect(page.locator('#addModal input[name="code"]')).toHaveCount(0);
    await page.fill('#addModal input[name="name"]', WH);
    await page.click('#addModal form button[type="submit"]');
    const whCode = getCodes('warehouses', PREFIX + ' Wh');
    expect(whCode).toMatch(/^GDG-\d{6}$/);
    const whId = getIds('warehouses', PREFIX + ' Wh');
    await page.goto(BASE + '/warehouses/edit/' + whId);
    await page.fill('input[name="name"]', PREFIX + ' Wh Renamed');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/warehouses$/);
    expect(getCodes('warehouses', PREFIX + ' Wh Renamed')).toBe(whCode);

    // TEST F — Lokasi: kode auto LOK-xxxxxx, unik global (dua lokasi gudang sama)
    await page.goto(BASE + '/locations');
    await page.click('button:has-text("Tambah Lokasi")');
    await expect(page.locator('#addModal input[name="code"]')).toHaveCount(0);
    await page.selectOption('#addModal select[name="warehouse_id"]', { label: PREFIX + ' Wh Renamed' });
    await page.fill('#addModal input[name="name"]', LOC1);
    await page.click('#addModal form button[type="submit"]');
    const loc1Code = getCodes('locations', PREFIX + ' Loc A');
    expect(loc1Code).toMatch(/^LOK-\d{6}$/);
    await page.click('button:has-text("Tambah Lokasi")');
    await page.selectOption('#addModal select[name="warehouse_id"]', { label: PREFIX + ' Wh Renamed' });
    await page.fill('#addModal input[name="name"]', LOC2);
    await page.click('#addModal form button[type="submit"]');
    const loc2Code = getCodes('locations', PREFIX + ' Loc B');
    expect(loc2Code).toMatch(/^LOK-\d{6}$/);
    expect(loc2Code).not.toBe(loc1Code);

    // TEST G — Master Barang: kode auto {CAT}-xxxxxx, stabil saat nama diubah
    await page.goto(BASE + '/items');
    await page.click('button:has-text("Tambah Barang")');
    await expect(page.locator('#addModal input[name="code"]')).toHaveCount(0);
    await page.fill('#addModal input[name="nama"]', ITEM);
    await page.selectOption('#addModal select[name="item_category_id"]', { label: CAT });
    await page.selectOption('#addModal select[name="unit_id"]', { label: PREFIX + ' Unit A Renamed' });
    await page.fill('#addModal input[name="stok_minimum"]', '0');
    await page.click('#addModal form button[type="submit"]');
    const itemCode = getCodes('master_items', PREFIX + ' Item');
    expect(itemCode).toMatch(new RegExp('^' + catCode.replace(/[.+?^${}()|[\]\\]/g, '\\$&') + '-\\d{6}$'));
    const itemId = getIds('master_items', PREFIX + ' Item');
    await page.goto(BASE + '/items/edit/' + itemId);
    await page.fill('input[name="nama"]', PREFIX + ' Item Renamed');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/items$/);
    expect(getCodes('master_items', PREFIX + ' Item Renamed')).toBe(itemCode);
    expect(getCodes('master_items', PREFIX + ' Item')).toBe('');

    // TEST H — Search: cari berdasarkan kode auto-generated (satuan)
    await page.goto(BASE + '/units?search=' + encodeURIComponent(unit1Code));
    await expect(page.locator(`tbody tr:has-text("${unit1Code}")`)).toHaveCount(1);

    // TEST I — Report/list menampilkan kode: kode barang tampil di tabel master barang
    await page.goto(BASE + '/items?search=' + encodeURIComponent(itemCode));
    await expect(page.locator(`tbody tr:has-text("${itemCode}")`)).toHaveCount(1);

    // TEST A-E (UI): form tambah tidak lagi menampilkan input kode editable
    await page.goto(BASE + '/categories');
    await page.click('button:has-text("Tambah Kategori")');
    await expect(page.locator('#addModal input[value="[Auto Generated]"]')).toHaveCount(1);
  } finally {
    // Cleanup data uji (FK-safe)
    execSync(`php "${MC}" cleanup "${'MC '}"`, { encoding: 'utf8' });
    const adminId = execSync(`php "${SEED}" get ${adminEmail}`, { encoding: 'utf8' }).trim().split('|')[0];
    if (adminId && adminId !== '') execSync(`php "${SEED}" cleanup ${adminId}`, { encoding: 'utf8' });
  }
});
