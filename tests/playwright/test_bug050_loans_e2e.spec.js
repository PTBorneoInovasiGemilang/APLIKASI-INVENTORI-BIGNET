// BUG-050 E2E — Loan tersimpan tanpa item aktif (display bug di kolom "Barang Dipinjam")
// Flow: Teknisi submit → Admin approve → Admin handover (custody) → Teknisi return → Admin proses return.
// Verifikasi: item tampil di UI (index + detail), stock fisik tidak dimutasi (custody model),
// outgoing terbentuk, audit/timeline tercatat, return menutup loan (RETURNED).
// Semua state change via UI (browser), tanpa SQL/API mutation.
import { test, expect } from '@playwright/test';

const BASE = 'http://localhost:8001';
const TEKNISI = { email: 'teknisi@ptbig.com', password: 'teknisi123' };
const ADMIN = { email: 'admin@ptbig.com', password: 'admin123' };

const consoleErrors = [];

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
let itemName = null;
let stockBefore = null;

test('BUG-050 A: Teknisi submit loan → item tersimpan dan TAMPIL di index & detail (PENDING_APPROVAL)', async ({ page }) => {
  await trackConsole(page);
  await login(page, TEKNISI);

  // Stok fisik sebelum (read-only API, data tidak diubah)
  const stockRes = await page.request.get(BASE + '/api/stock?item_id=1&warehouse_id=1');
  const stockJson = await stockRes.json();
  stockBefore = Number(stockJson.quantity);

  await page.goto(BASE + '/loans/create');
  await expect(page.getByRole('main').getByRole('heading', { name: 'Ajukan Peminjaman' })).toBeVisible();
  await expect(page.locator('#body .rounded-2xl')).toHaveCount(1); // addRow() awal

  await page.selectOption('select[name="warehouse_id"]', { index: 1 });
  await page.selectOption('select[name="item_ids[0]"]', { index: 1 });
  itemName = await page.locator('select[name="item_ids[0]"] option:checked').innerText();
  await page.fill('input[name="quantities[0]"]', '3');
  await page.fill('textarea[name="notes"]', 'UAT BUG-050 — verifikasi item loan tampil.');
  await page.click('button[type="submit"]');

  await page.waitForURL('**/loans');
  await expect(page.getByText('Peminjaman berhasil diajukan.')).toBeVisible();

  // === INTI BUG-050: baris pertama TIDAK boleh "Tidak ada barang aktif." ===
  const firstCell = page.locator('.loan-items-cell').first();
  loanId = await firstCell.getAttribute('data-loan-id');
  expect(Number(loanId)).toBeGreaterThan(0);
  await expect(firstCell).not.toContainText('Tidak ada barang aktif.');
  await expect(firstCell).toContainText(itemName);
  await expect(firstCell).toContainText('3 diminta');

  const row = firstCell.locator('xpath=ancestor::tr[1]');
  await expect(row).toContainText('Menunggu Persetujuan');

  // Detail: bagian "Barang Diajukan" tampil dengan barang + jumlah
  await page.goto(BASE + '/loans/show?id=' + loanId);
  await expect(page.getByText('Barang Diajukan')).toBeVisible();
  await expect(page.locator('.loan-ui')).toContainText(itemName);
  await expect(page.locator('.loan-ui')).toContainText('Diminta 3');

  expect(consoleErrors, 'Console errors: ' + consoleErrors.join(' | ')).toHaveLength(0);
});

test('BUG-050 B: Admin review → setujui (status APPROVED, item keputusan tampil)', async ({ page }) => {
  await trackConsole(page);
  await login(page, ADMIN);

  await page.goto(BASE + '/loans/show?id=' + loanId + '#review');
  await expect(page.getByRole('main').getByRole('heading', { name: 'Detail Peminjaman' })).toBeVisible();
  await expect(page.locator('#review')).toBeVisible();

  // Admin menyertakan barang yang diminta (jumlah 3 sudah terisi)
  await page.check('.review-include');
  await expect(page.locator('.review-qty')).toHaveValue('3');
  await page.click('#review button[type="submit"]');

  await page.waitForURL('**/loans/show?id=' + loanId);
  await expect(page.getByText('Peminjaman disetujui')).toBeVisible();
  await expect(page.locator('.loan-ui')).toContainText('Siap Diproses');

  await expect(page.getByText('Barang Peminjaman')).toBeVisible();
  await expect(page.locator('.loan-ui')).toContainText('Diberikan 3');

  // Index: chip kini memakai keputusan admin (sisa dipinjam)
  await page.goto(BASE + '/loans');
  const firstCell = page.locator('.loan-items-cell').first();
  await expect(firstCell).toContainText(itemName);
  await expect(firstCell).toContainText('3 sisa dipinjam');

  expect(consoleErrors, 'Console errors: ' + consoleErrors.join(' | ')).toHaveLength(0);
});

test('BUG-050 C: Admin handover → Barang Keluar dibuat (custody), stock fisik tetap', async ({ page }) => {
  await trackConsole(page);
  await login(page, ADMIN);

  await page.goto(BASE + '/loans/show?id=' + loanId);
  await expect(page.locator('#handover')).toBeVisible();
  await page.locator('#handover button[type="button"]').click(); // openHandoverModal
  await page.locator('#loanModalPrimary').click(); // Proses & Serahkan

  await expect(page.getByText('Barang Keluar peminjaman berhasil dibuat')).toBeVisible();
  await expect(page.locator('.loan-ui')).toContainText('Sedang Dipinjam');
  await expect(page.getByText('Lihat transaksi pengeluaran')).toBeVisible();

  // Verifikasi stok fisik tidak berubah (custody model: availability berkurang, stok tetap)
  const stockRes = await page.request.get(BASE + '/api/stock?item_id=1&warehouse_id=1');
  const stockJson = await stockRes.json();
  expect(Number(stockJson.quantity)).toBe(stockBefore);

  // Dokumen Barang Keluar menampilkan barang yang diserahkan
  await page.getByText('Lihat transaksi pengeluaran').click();
  await page.waitForURL('**/outgoing/show**');
  await expect(page.locator('body')).toContainText(itemName);

  expect(consoleErrors, 'Console errors: ' + consoleErrors.join(' | ')).toHaveLength(0);
});

test('BUG-050 D: Teknisi ajukan pengembalian barang yang masih dipegang', async ({ page }) => {
  await trackConsole(page);
  await login(page, TEKNISI);

  await page.goto(BASE + '/returns/create?loan_id=' + loanId);
  await page.waitForSelector('.return-check');
  await expect(page.locator('body')).toContainText(itemName);
  await page.selectOption('select[name="warehouse_id"]', { index: 1 });
  await page.check('.return-check');
  await expect(page.locator('#submitReturnBtn')).toBeEnabled();
  await page.click('#submitReturnBtn');

  await page.waitForURL('**/returns');
  await expect(page.getByText('Pengajuan pengembalian barang berhasil dikirim')).toBeVisible();

  expect(consoleErrors, 'Console errors: ' + consoleErrors.join(' | ')).toHaveLength(0);
});

test('BUG-050 E: Admin proses pengembalian → loan RETURNED + timeline/audit lengkap', async ({ page }) => {
  await trackConsole(page);
  await login(page, ADMIN);

  await page.goto(BASE + '/returns');
  await page.locator('a[href*="/returns/show?id="]').first().click();
  await page.waitForURL('**/returns/show**');
  await expect(page.locator('body')).toContainText(itemName);
  await page.click('button:has-text("Proses Pengembalian")');

  await expect(page.getByText('Pengembalian berhasil diproses.')).toBeVisible();

  // Loan selesai: status Dikembalikan + jejak timeline (CREATE/APPROVE/HANDOVER terlihat di audit timeline)
  await page.goto(BASE + '/loans/show?id=' + loanId);
  await expect(page.locator('.loan-ui')).toContainText('Dikembalikan');
  await expect(page.getByText('Peminjaman diajukan')).toBeVisible();
  await expect(page.getByText('Admin menentukan barang, jumlah, mapping manual')).toBeVisible();
  await expect(page.getByText('Peminjaman dieksekusi melalui Barang Keluar')).toBeVisible();

  // Index: chip tidak lagi kosong
  await page.goto(BASE + '/loans');
  const firstCell = page.locator('.loan-items-cell').first();
  await expect(firstCell).toContainText(itemName);
  await expect(firstCell).not.toContainText('Tidak ada barang aktif.');

  expect(consoleErrors, 'Console errors: ' + consoleErrors.join(' | ')).toHaveLength(0);
});
