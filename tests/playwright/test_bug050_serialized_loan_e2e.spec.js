// BUG-050 follow-up — UAT SERIALIZED LOAN (full SN custody chain via UI)
// Flow: (seed via UI) Master Barang serialized → Barang Masuk + SN →
//       Teknisi loan → Admin approve + pilih SN → Handover (custody SN) →
//       Teknisi return → Admin proses → SN kembali AVAILABLE.
// Verifikasi in-test: UI + endpoint read-only /api/stock (GET, tanpa mutasi).
// Verifikasi state DB dalam (holder/warehouse/movements) dilakukan lewat
// SELECT read-only di terminal SETELAH suite, memakai konteks ID yang
// ditulis test ke test-results/bug050_serial_context.json.
import { test, expect } from '@playwright/test';
import fs from 'fs';

const BASE = 'http://localhost:8001';
const TEKNISI = { email: 'teknisi@ptbig.com', password: 'teknisi123' };
const ADMIN = { email: 'admin@ptbig.com', password: 'admin123' };

const consoleErrors = [];
const runId = Date.now();
const SN_A = `SNLOAN-${runId}-A`;
const SN_B = `SNLOAN-${runId}-B`;
const itemName = 'UAT Loan Serial ' + runId;
const ctxPath = 'test-results/bug050_serial_context.json';
const ctx = { runId, SN_A, SN_B, itemName };

function saveCtx() {
  fs.mkdirSync('test-results', { recursive: true });
  fs.writeFileSync(ctxPath, JSON.stringify(ctx, null, 2));
}

async function trackConsole(page) {
  page.on('console', msg => {
    if (msg.type() === 'error' && !/favicon/i.test(msg.text())) consoleErrors.push(msg.text());
  });
  page.on('pageerror', err => consoleErrors.push('pageerror: ' + err.message));
}

async function login(page, user) {
  await page.goto(BASE + '/login');
  await page.fill('input[name="email"]', user.email);
  await page.fill('input[name="password"]', user.password);
  await page.click('button[type="submit"]');
  await page.waitForURL('**/dashboard');
}

test.describe.configure({ mode: 'serial' });

let loanId = null;

test('S-0: Seed via UI — Master Barang serialized + Barang Masuk 2 SN', async ({ page }) => {
  await trackConsole(page);
  await login(page, ADMIN);

  // 1) Master Barang serialized via modal form /items
  await page.goto(BASE + '/items');
  await page.getByRole('button', { name: /Tambah Barang/ }).click();
  await page.fill('#addModal input[name="nama"]', itemName);
  await page.selectOption('#addModal select[name="item_category_id"]', { index: 1 });
  await page.selectOption('#addModal select[name="unit_id"]', { index: 1 });
  await page.check('#addModal input[name="is_serialized"]');
  await page.locator('#addModal button[type="submit"]').click();
  await page.waitForLoadState('networkidle');

  // 2) Ambil ID item baru dari daftar (search by nama)
  await page.goto(BASE + '/items?search=' + encodeURIComponent(itemName));
  const href = await page.locator('a[href*="/items/show/"]').first().getAttribute('href');
  ctx.itemId = parseInt(href.match(/\/items\/show\/(\d+)/)[1], 10);
  expect(ctx.itemId).toBeGreaterThan(0);

  // 3) Barang Masuk + 2 SN via form /incoming/create
  await page.goto(BASE + '/incoming/create');
  await page.selectOption('select[name="warehouse_id"]', { index: 1 });
  await page.selectOption('select[name="item_ids[]"]', { index: 1 });
  // pastikan baris memilih item serialized yang baru dibuat
  await page.selectOption('select[name="item_ids[]"]', String(ctx.itemId));
  await page.fill('input[name="quantities[]"]', '2');
  await page.fill('textarea[name="sn_list[]"]', SN_A + '\n' + SN_B);
  await page.click('button[type="submit"]');
  await page.waitForURL('**/incoming');
  await expect(page.getByText('Barang Masuk berhasil diproses.')).toBeVisible();

  // 4) Verifikasi read-only via /api/stock: 2 serial AVAILABLE di gudang 1
  const res = await page.request.get(`${BASE}/api/stock?item_id=${ctx.itemId}&warehouse_id=1`);
  const stock = await res.json();
  expect(Number(stock.quantity)).toBe(2);
  expect(Array.isArray(stock.serials)).toBe(true);
  const sns = stock.serials.map(s => s.serial_number).sort();
  expect(sns).toEqual([SN_A, SN_B].sort());

  saveCtx();
  expect(consoleErrors, 'Console errors: ' + consoleErrors.join(' | ')).toHaveLength(0);
});

test('S-1: Teknisi ajukan loan serialized (tanpa input SN di form — by design)', async ({ page }) => {
  await trackConsole(page);
  await login(page, TEKNISI);

  await page.goto(BASE + '/loans/create');
  await page.selectOption('select[name="warehouse_id"]', { index: 1 });
  const optValue = await page.locator('select[name="item_ids[0]"] option', { hasText: itemName }).getAttribute('value');
  expect(Number(optValue)).toBe(ctx.itemId);
  await page.selectOption('select[name="item_ids[0]"]', optValue);
  await page.fill('input[name="quantities[0]"]', '2');
  await page.fill('textarea[name="notes"]', 'UAT serialized loan — verifikasi custody SN.');
  await page.click('button[type="submit"]');

  await page.waitForURL('**/loans');
  await expect(page.getByText('Peminjaman berhasil diajukan.')).toBeVisible();

  const firstCell = page.locator('.loan-items-cell').first();
  loanId = Number(await firstCell.getAttribute('data-loan-id'));
  ctx.loanId = loanId;
  await expect(firstCell).toContainText(itemName);
  await expect(firstCell).toContainText('2 diminta');
  saveCtx();
  expect(consoleErrors, 'Console errors: ' + consoleErrors.join(' | ')).toHaveLength(0);
});

test('S-2: Admin review — SN AVAILABLE tampil, approve dengan memilih 2 SN', async ({ page }) => {
  await trackConsole(page);
  await login(page, ADMIN);

  await page.goto(BASE + '/loans/show?id=' + loanId);
  const box = page.locator('.review-serial-box.is-visible').first();
  await expect(box).toBeVisible(); // diisi otomatis via /api/stock

  const sel = box.locator('select.review-serial');
  await expect(sel.locator('option')).toHaveCount(2);
  await expect(sel).toContainText(SN_A);
  await expect(sel).toContainText(SN_B);

  await page.check('.review-include');
  await expect(page.locator('.review-qty')).toHaveValue('2');
  await sel.selectOption([{ label: SN_A }, { label: SN_B }]);
  await page.click('#review button[type="submit"]');

  await page.waitForURL('**/loans/show?id=' + loanId);
  await expect(page.getByText('Peminjaman disetujui')).toBeVisible();

  // Detail (status APPROVED): badge serial memuat SN dengan status PENDING (link table)
  await expect(page.locator('.loan-ui')).toContainText(SN_A);
  await expect(page.locator('.loan-ui')).toContainText(SN_B);
  await expect(page.locator('.loan-ui')).toContainText('PENDING');
  saveCtx();
  expect(consoleErrors, 'Console errors: ' + consoleErrors.join(' | ')).toHaveLength(0);
});

test('S-3: Handover — Barang Keluar dibuat, SN keluar dari stok tersedia (custody)', async ({ page }) => {
  await trackConsole(page);
  await login(page, ADMIN);

  await page.goto(BASE + '/loans/show?id=' + loanId);
  await page.locator('#handover button[type="button"]').click();
  await page.locator('#loanModalPrimary').click();
  await expect(page.getByText('Barang Keluar peminjaman berhasil dibuat')).toBeVisible();
  await expect(page.locator('.loan-ui')).toContainText('Sedang Dipinjam');
  await expect(page.getByText('Lihat transaksi pengeluaran')).toBeVisible();

  // Badge serial di detail kini BORROWED
  await expect(page.locator('.loan-ui')).toContainText('BORROWED');

  // /api/stock (read-only): serial AVAILABLE di gudang habis — SN sedang dipinjam
  const res = await page.request.get(`${BASE}/api/stock?item_id=${ctx.itemId}&warehouse_id=1`);
  const stock = await res.json();
  expect(stock.serials ?? []).toHaveLength(0);

  // Dokumen Barang Keluar memuat barang yang diserahkan
  await page.getByText('Lihat transaksi pengeluaran').click();
  await page.waitForURL('**/outgoing/show**');
  await expect(page.locator('body')).toContainText(itemName);
  saveCtx();
  expect(consoleErrors, 'Console errors: ' + consoleErrors.join(' | ')).toHaveLength(0);
});

test('S-4: Teknisi ajukan return — SN yang dipinjam muncul sebagai pilihan', async ({ page }) => {
  await trackConsole(page);
  await login(page, TEKNISI);

  await page.goto(BASE + '/returns/create?loan_id=' + loanId);
  await page.waitForSelector('.return-check');
  await expect(page.locator('body')).toContainText(itemName);
  await expect(page.locator('body')).toContainText(SN_A);
  await expect(page.locator('body')).toContainText(SN_B);
  await page.selectOption('select[name="warehouse_id"]', { index: 1 });
  await page.check('.return-check');
  // Pilih kedua SN yang dikembalikan (counter harus sama dengan qty)
  await page.check('.sn-option-0 >> nth=0');
  await page.check('.sn-option-0 >> nth=1');
  await expect(page.locator('#sn-counter-0')).toContainText('2 / 2');
  await expect(page.locator('#submitReturnBtn')).toBeEnabled();
  await page.click('#submitReturnBtn');

  await page.waitForURL('**/returns');
  await expect(page.getByText('Pengajuan pengembalian barang berhasil dikirim')).toBeVisible();
  saveCtx();
  expect(consoleErrors, 'Console errors: ' + consoleErrors.join(' | ')).toHaveLength(0);
});

test('S-5: Admin proses return — loan Dikembalikan, SN kembali tersedia di gudang', async ({ page }) => {
  await trackConsole(page);
  await login(page, ADMIN);

  await page.goto(BASE + '/returns');
  await page.locator('a[href*="/returns/show?id="]').first().click();
  await page.waitForURL('**/returns/show**');
  ctx.returnId = Number(new URL(page.url()).searchParams.get('id'));

  await expect(page.locator('body')).toContainText(SN_A);
  await page.click('button:has-text("Proses Pengembalian")');
  await expect(page.getByText('Pengembalian berhasil diproses.')).toBeVisible();

  // Loan selesai + timeline audit terlihat
  await page.goto(BASE + '/loans/show?id=' + loanId);
  await expect(page.locator('.loan-ui')).toContainText('Dikembalikan');
  await expect(page.getByText('Peminjaman diajukan')).toBeVisible();
  await expect(page.getByText('Admin menentukan barang, jumlah, mapping manual')).toBeVisible();
  await expect(page.getByText('Peminjaman dieksekusi melalui Barang Keluar')).toBeVisible();

  // Badge serial di detail kini RETURNED
  await expect(page.locator('.loan-ui')).toContainText('RETURNED');

  // /api/stock (read-only): serial kembali tersedia 2 di gudang 1
  const res = await page.request.get(`${BASE}/api/stock?item_id=${ctx.itemId}&warehouse_id=1`);
  const stock = await res.json();
  const sns = (stock.serials ?? []).map(s => s.serial_number).sort();
  expect(sns).toEqual([SN_A, SN_B].sort());

  saveCtx();
  expect(consoleErrors, 'Console errors: ' + consoleErrors.join(' | ')).toHaveLength(0);
});
