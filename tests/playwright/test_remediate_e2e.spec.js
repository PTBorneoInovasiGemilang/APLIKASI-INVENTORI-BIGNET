const { test, expect } = require('@playwright/test');
const { execSync } = require('child_process');
const path = require('path');

const BASE = 'http://localhost:8000';
const RETURN_HELPER = path.join(__dirname, 'helpers', 'return_seed.php');

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

function seedReturn() {
  return execSync(`php "${RETURN_HELPER}" seed`, { encoding: 'utf8' }).trim();
}
function cleanupReturn(id, itemId, whId, oldStock) {
  execSync(`php "${RETURN_HELPER}" cleanup ${id} ${itemId} ${whId} ${oldStock}`, { encoding: 'utf8' });
}

test.describe('MASTER FINAL REMEDIATION', () => {

  test('Adjustment: index + show render tanpa 500 (bug stock_adjustment_id)', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    const errors = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', e => errors.push(e.message));

    await login(page, 'admin@ptbig.com');
    await page.goto(BASE + '/adjustments');
    await expect(page.getByRole('heading', { name: 'Penyesuaian Stok' }).first()).toBeVisible();

    // open first detail
    const detailLink = page.locator('a[href*="/adjustments/show?id="]').first();
    await expect(detailLink).toBeVisible();
    await detailLink.click();
    await expect(page).toHaveURL(/\/adjustments\/show\?id=/);
    await expect(page.getByRole('heading', { name: /Detail Penyesuaian/ }).first()).toBeVisible();
    // item table rendered (SQL join with stock_adjustment_items)
    await expect(page.locator('table').first()).toBeVisible();
    await expect(page.getByText('Panduan Cepat')).toBeVisible();

    expect(errors.filter(e => !e.includes('favicon'))).toEqual([]);
  });

  test('Outgoing: Lihat membuka detail (bug 404)', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    const errors = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', e => errors.push(e.message));

    await login(page, 'admin@ptbig.com');
    await page.goto(BASE + '/outgoing');
    await expect(page.getByRole('heading', { name: 'Barang Keluar' }).first()).toBeVisible();

    const detailLink = page.locator('a[href*="/outgoing/show?id="]').first();
    await expect(detailLink).toBeVisible();
    await detailLink.click();
    await expect(page).toHaveURL(/\/outgoing\/show\?id=/);
    await expect(page.getByRole('heading', { name: /Detail Barang Keluar/ }).first()).toBeVisible();
    await expect(page.locator('table').first()).toBeVisible();
    await expect(page.getByText('Panduan Cepat')).toBeVisible();

    expect(errors.filter(e => !e.includes('favicon'))).toEqual([]);
  });

  test('Return: ADMIN proses pengembalian berhasil (bukan 403); SUPERADMIN diblokir', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    const seeded = seedReturn();
    const [rid, itemId, whId, oldStock] = seeded.split('|');
    try {
      // ADMIN: button visible -> proses -> COMPLETED + stok naik
      await login(page, 'admin@ptbig.com');
      await page.goto(BASE + '/returns/show?id=' + rid);
      await expect(page.getByRole('heading', { name: /Detail Pengembalian:/ })).toBeVisible();
      const processBtn = page.locator('button:has-text("Proses Pengembalian")');
      await expect(processBtn).toBeVisible();
      await processBtn.click();
      await expect(page).toHaveURL(/\/returns\/show\?id=/);
      await expect(page.getByText('Pengembalian berhasil diproses dan stok telah diupdate.')).toBeVisible();
      await expect(page.getByText('Selesai')).toBeVisible();

      // SUPERADMIN: button hidden + direct POST ditolak 403 (dengan token CSRF valid)
      await page.goto(BASE + '/logout');
      await login(page, 'superadmin@ptbig.com');
      await page.goto(BASE + '/returns/show?id=' + rid);
      await expect(page.locator('button:has-text("Proses Pengembalian")')).toHaveCount(0);
      const csrf = await page.locator('form[action*="/returns/process"] input[name="csrf_token"]').first().inputValue();
      const status = await page.evaluate(async ({ rid, csrf }) => {
        const r = await fetch('/returns/process', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'id=' + rid + '&csrf_token=' + encodeURIComponent(csrf),
          credentials: 'same-origin'
        });
        return r.status;
      }, { rid, csrf });
      expect(status).toBe(403);
    } finally {
      cleanupReturn(rid, itemId, whId, oldStock);
    }
  });

  test('TEKNISI: /loans/create tidak 500 (bug submissions.user_id -> submitted_by)', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    const errors = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', e => errors.push(e.message));

    await login(page, 'teknisia@ptbig.com');
    await page.goto(BASE + '/loans/create');
    await expect(page.getByRole('heading', { name: 'Peminjaman Baru' }).first()).toBeVisible();
    await expect(page.getByText('Panduan Cepat')).toBeVisible();
    // form elements
    await expect(page.locator('form').first()).toBeVisible();
    await expect(page.locator('select[name="warehouse_id"]').first()).toBeVisible();
    expect(errors.filter(e => !e.includes('favicon') && !e.includes('tailwindcss'))).toEqual([]);
  });

  test('Enum raw tidak bocor ke UI (labels terjemahan)', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await login(page, 'admin@ptbig.com');

    const rawEnums = ['PARTIAL_APPROVED', 'IN_TRANSIT', 'PENDING_APPROVAL'];
    const pages = ['/submissions', '/loans', '/transfers'];
    for (const p of pages) {
      await page.goto(BASE + p);
      for (const raw of rawEnums) {
        const inMain = await page.locator('main').getByText(raw, { exact: true }).count();
        expect(inMain, `${p} leaks raw ${raw}`).toBe(0);
      }
    }

    // Detail pengajuan: tidak boleh bocor blok PHP mentah / label MANUAL
    const subLink = page.locator('a[href*="/submissions/show?id="]').first();
    if (await subLink.count() > 0) {
      await subLink.click();
      await expect(page).toHaveURL(/\/submissions\/show\?id=/);
      await expect(page.locator('main').getByText('bin2hex')).toHaveCount(0);
      await expect(page.locator('main').getByText(/if \(empty\(/)).toHaveCount(0);
      await expect(page.locator('main').getByText('[MANUAL]', { exact: false })).toHaveCount(0);
    }
  });

  test('Page title benar (bukan Dashboard) untuk halaman non-dashboard', async ({ page }) => {
    await login(page, 'admin@ptbig.com');
    const cases = [
      ['/items', /Master Barang/],
      ['/serials', /Nomor Seri/],
      ['/stocks', /Stok Barang/],
      ['/transfers', /Transfer Gudang/],
      ['/loans', /Peminjaman/],
      ['/outgoing', /Barang Keluar/],
      ['/adjustments', /Penyesuaian Stok/],
    ];
    for (const [p, re] of cases) {
      await page.goto(BASE + p);
      await expect(page, p).toHaveTitle(re);
    }
  });

  test('Page Guide posisi KANAN pada desktop (guide.left > content.right)', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await login(page, 'admin@ptbig.com');
    const pages = ['/transfers/create', '/adjustments/create', '/outgoing/create'];
    for (const p of pages) {
      await page.goto(BASE + p);
      await expect(page.getByText('Panduan Cepat')).toBeVisible();
      const contentBox = await page.locator('main > div').first().boundingBox();
      const guideBox = await page.locator('main > div').nth(1).boundingBox();
      expect(contentBox, p + ' content box').not.toBeNull();
      expect(guideBox, p + ' guide box').not.toBeNull();
      expect(guideBox.x, p + ' guide harus kanan').toBeGreaterThan(contentBox.x + contentBox.width - 1);
    }

    // index pages yang tadinya punya div tidak seimbang: guide tetap kanan
    for (const p of ['/adjustments', '/installations', '/returns']) {
      await page.goto(BASE + p);
      await expect(page.getByText('Panduan Cepat')).toBeVisible();
      const contentBox = await page.locator('main > div').first().boundingBox();
      const guideBox = await page.locator('main > div').nth(1).boundingBox();
      expect(guideBox.x, p + ' guide harus kanan').toBeGreaterThan(contentBox.x + contentBox.width - 1);
    }

    // Halaman yang dilaporkan Antigravity: guide wajib KANAN pada desktop (SUPERADMIN)
    await page.goto(BASE + '/logout');
    await login(page, 'superadmin@ptbig.com');
    const saPages = ['/categories', '/units', '/warehouses', '/locations', '/items', '/stocks',
      '/serials', '/submissions', '/incoming', '/outgoing', '/transfers', '/loans', '/returns',
      '/installations', '/adjustments', '/timeline', '/users', '/settings', '/reports', '/labels',
      '/scan', '/notifications', '/import-export', '/opnames', '/incidents', '/reports/ledger'];
    for (const p of saPages) {
      await page.goto(BASE + p);
      await expect(page.getByText('Panduan Cepat'), p + ' harus punya guide').toBeVisible();
      const cb = await page.locator('main > div').first().boundingBox();
      const gb = await page.locator('main > div').nth(1).boundingBox();
      expect(gb, p + ' guide box').not.toBeNull();
      expect(gb.x, p + ' guide harus kanan').toBeGreaterThan(cb.x + cb.width - 1);
    }
  });

  test('Report: checkbox selection + export CSV/PDF terpilih + cetak terpilih (konsisten)', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    const errors = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', e => errors.push(e.message));

    await login(page, 'admin@ptbig.com');
    await page.goto(BASE + '/reports/ledger');

    // Row checkboxes + toolbar
    const rowCbs = page.locator('input[name="selected_ids[]"]');
    const n = await rowCbs.count();
    expect(n).toBeGreaterThan(0);

    // Pilih Semua Tampil -> count matches
    await page.click('#btnSelectPage');
    const checked = await page.locator('input[name="selected_ids[]"]:checked').count();
    expect(checked).toBe(n);
    await expect(page.locator('#selectedCount')).toHaveText(n + ' data dipilih');

    // CSV terpilih: pilih hanya 1 baris -> download 1 row
    await page.click('#btnClearSelection');
    await rowCbs.first().check();
    await expect(page.locator('#selectedCount')).toHaveText('1 data dipilih');

    const [csvDownload] = await Promise.all([
      page.waitForEvent('download'),
      page.click('button[type="submit"]:has-text("Export CSV Terpilih")'),
    ]);
    expect(csvDownload.suggestedFilename()).toContain('.csv');

    const [pdfDownload] = await Promise.all([
      page.waitForEvent('download'),
      page.click('button[type="submit"]:has-text("Export PDF Terpilih")'),
    ]);
    const pdfPath = await pdfDownload.path();
    const { readFileSync } = require('fs');
    const buf = readFileSync(pdfPath);
    const body = buf.toString('latin1');
    expect(body.startsWith('%PDF-1.4')).toBe(true);
    // Body berisi data (header tabel laporan, bukan kosong)
    expect(body).toContain('Laporan Stok & Mutasi');
    expect(body).toContain('Tanggal');
    expect(body).toContain('Kode Barang');

    // print_selected: tanpa pilihan -> JS warning, form tidak submit
    await page.click('#btnClearSelection');
    page.once('dialog', d => { expect(d.message()).toContain('Belum ada baris yang dipilih'); d.dismiss(); });
    await page.click('button[type="submit"]:has-text("Cetak Terpilih")');
    await page.waitForTimeout(300);
    expect(page.url()).not.toContain('print_selected=1');

    expect(errors.filter(e => !e.includes('favicon'))).toEqual([]);
  });
});
