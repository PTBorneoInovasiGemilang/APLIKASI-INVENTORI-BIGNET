const { test, expect } = require('@playwright/test');
const { execSync } = require('child_process');

const HELPER = 'php tests/playwright/helpers/category_seed.php';

function phpHelper(action) {
  return execSync(`${HELPER} ${action}`).toString().trim();
}

async function login(page, email, password = 'password') {
  await page.goto('http://localhost:8000/login');
  if (!page.url().includes('/login')) {
    await page.goto('http://localhost:8000/logout');
    await page.goto('http://localhost:8000/login');
  }
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/.*\/dashboard/);
}

const guidePanel = page => page.locator('main .w-full.lg\\:w-80').first();
const contentCol = page => page.locator('main .flex-1.min-w-0').first();

test.describe('Refinement: Page Guide + Empty State + CRUD + Bulk Action', () => {

  test.afterAll(() => { try { phpHelper('cleanup'); } catch (e) {} });

  test('Page Guide: panel di KANAN konten di desktop', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await login(page, 'admin@ptbig.com');
    await page.goto('http://localhost:8000/stocks', { waitUntil: 'domcontentloaded' });

    const guide = guidePanel(page);
    await expect(guide).toBeVisible();
    await expect(guide.getByText('Panduan Cepat')).toBeVisible();

    const pos = await page.evaluate(() => {
      const content = document.querySelector('main .flex-1.min-w-0');
      const g = document.querySelector('main .w-full.lg\\:w-80');
      return { contentRight: content.getBoundingClientRect().right, guideLeft: g.getBoundingClientRect().left };
    });
    expect(pos.guideLeft).toBeGreaterThanOrEqual(pos.contentRight - 5);
  });

  test('Page Guide: turun di bawah konten di mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await login(page, 'admin@ptbig.com');
    await page.goto('http://localhost:8000/stocks', { waitUntil: 'domcontentloaded' });

    const guide = guidePanel(page);
    await expect(guide).toBeVisible();

    const pos = await page.evaluate(() => {
      const content = document.querySelector('main .flex-1.min-w-0');
      const g = document.querySelector('main .w-full.lg\\:w-80');
      return { contentBottom: content.getBoundingClientRect().bottom, guideTop: g.getBoundingClientRect().top };
    });
    expect(pos.guideTop).toBeGreaterThanOrEqual(pos.contentBottom - 5);
  });

  test('Edit User: panduan tidak lagi menempel di kiri form (ada di panel kanan)', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await login(page, 'superadmin@ptbig.com');

    const id = phpHelper('firstuserid');
    await page.goto(`http://localhost:8000/users/edit/${id}`, { waitUntil: 'domcontentloaded' });

    // Tidak ada panel info inline di dalam kartu form
    await expect(page.locator('form .bg-blue-50').first()).toHaveCount(0);
    // Guide dari layout (panel kanan) tetap tampil
    await expect(guidePanel(page).getByText('Panduan Cepat')).toBeVisible();
  });

  test('Empty State: ikon + judul + deskripsi terpusat horizontal', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 800 });
    await login(page, 'admin@ptbig.com');
    await page.goto('http://localhost:8000/loans', { waitUntil: 'domcontentloaded' });

    const emptyCell = page.locator('main tbody tr td[colspan].align-middle');
    await expect(emptyCell).toBeVisible();
    const empty = await page.evaluate(() => {
      const td = document.querySelector('main tbody tr td[colspan].align-middle');
      const h3 = td.querySelector('h3');
      const p = td.querySelector('p');
      const icon = td.querySelector('i.fas');
      return {
        textAlign: getComputedStyle(td).textAlign,
        h3Centered: (h3.getBoundingClientRect().left + h3.getBoundingClientRect().width / 2) -
                    (td.getBoundingClientRect().left + td.getBoundingClientRect().width / 2),
        iconPresent: !!icon,
        pPresent: !!p
      };
    });
    expect(empty.textAlign).toBe('center');
    expect(Math.abs(empty.h3Centered)).toBeLessThanOrEqual(2);
    expect(empty.iconPresent).toBe(true);
    expect(empty.pPresent).toBe(true);
  });

  test('CRUD visibility: Admin melihat aksi, Superadmin tidak melihat mutation', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 800 });

    // Admin: tombol Tambah + Aksi + checkbox bulk
    await login(page, 'admin@ptbig.com');
    await page.goto('http://localhost:8000/categories', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('button', { name: /Tambah Kategori/ })).toBeVisible();
    await expect(page.locator('main tbody a:has-text("Ubah")').first()).toBeVisible();
    await expect(page.locator('.bulk-checkbox').first()).toBeVisible();

    // Superadmin: TIDAK ada tombol Tambah / Aksi / checkbox
    await login(page, 'superadmin@ptbig.com');
    await page.goto('http://localhost:8000/categories', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('button', { name: /Tambah Kategori/ })).toHaveCount(0);
    await expect(page.locator('.bulk-checkbox')).toHaveCount(0);
    await expect(page.locator('main tbody a:has-text("Ubah")')).toHaveCount(0);

    // Route langsung mutation tetap ditolak backend (Router: SUPERADMIN read-only -> 403)
    const csrf = await page.locator('input[name="csrf_token"]').first().getAttribute('value')
      || await page.evaluate(() => window.csrf_token || '');
    const resp = await page.request.post('http://localhost:8000/categories/bulk-delete', {
      form: { csrf_token: csrf || 'invalid', ids: '1' },
      redirect: 'manual'
    });
    expect([403, 302]).toContain(resp.status());
  });

  test('Bulk action: select-all, count, confirm, delete, success feedback', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 800 });
    phpHelper('cleanup');
    const firstId = phpHelper('seed');
    const secondId = String(parseInt(firstId, 10) + 1);

    await login(page, 'admin@ptbig.com');
    await page.goto('http://localhost:8000/categories', { waitUntil: 'domcontentloaded' });

    // Select-all: semua checkbox tercentang, bar bulk muncul, count benar
    await page.check('.bulk-select-all');
    const checked = await page.locator('.bulk-checkbox:checked').count();
    expect(checked).toBeGreaterThanOrEqual(1);
    await expect(page.locator('#bulkCat-bar')).toBeVisible();
    const countText = await page.locator('#bulkCat-count').textContent();
    expect(parseInt(countText, 10)).toBe(checked);

    // Batal: bar bersembunyi & checkbox reset
    await page.click('#bulkCat-cancel');
    await expect(page.locator('#bulkCat-bar')).toBeHidden();

    // Pilih 2 baris test saja lalu hapus dengan konfirmasi
    await page.locator(`.bulk-checkbox.bulkCat[value="${firstId}"]`).check();
    await page.locator(`.bulk-checkbox.bulkCat[value="${secondId}"]`).check();
    await expect(page.locator('#bulkCat-count')).toHaveText('2');

    page.once('dialog', async dialog => {
      expect(dialog.message()).toContain('Hapus 2 data terpilih');
      await dialog.accept();
    });
    await page.click('#bulkCat-submit');

    await page.waitForURL(/\/categories/);
    await expect(page.getByText(/berhasil dihapus/)).toBeVisible();
    expect(phpHelper('count')).toBe('0');
  });

  test('Bulk action: data terpakai dilewati (guard backend)', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 800 });
    phpHelper('cleanup');
    phpHelper('seed');

    const usedId = phpHelper('usedcat');
    const testId = phpHelper('testcat');

    await login(page, 'admin@ptbig.com');
    await page.goto('http://localhost:8000/categories', { waitUntil: 'domcontentloaded' });

    await page.locator(`.bulk-checkbox.bulkCat[value="${usedId}"]`).check();
    await page.locator(`.bulk-checkbox.bulkCat[value="${testId}"]`).check();
    page.once('dialog', d => d.accept());
    await page.click('#bulkCat-submit');
    await page.waitForURL(/\/categories/);

    await expect(page.getByText(/dilewati karena masih digunakan/)).toBeVisible();
    // 1 dari 2 baris test terhapus; 1 baris test lain tidak dipilih; yang terpakai tetap ada
    expect(phpHelper('count')).toBe('1');
  });

  test('Bulk action: dark mode + no console error', async ({ page }) => {
    const errors = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', err => errors.push(err.message));

    await page.setViewportSize({ width: 1280, height: 800 });
    await login(page, 'admin@ptbig.com');
    await page.goto('http://localhost:8000/categories', { waitUntil: 'domcontentloaded' });
    await page.click('button[aria-label="Toggle dark mode"]');
    await expect(page.locator('html')).toHaveClass(/dark/);

    await page.locator('.bulk-checkbox.bulkCat').first().check();
    await expect(page.locator('#bulkCat-bar')).toBeVisible();
    await page.click('#bulkCat-cancel');
    await expect(page.locator('#bulkCat-bar')).toBeHidden();

    expect(errors.filter(e => !e.includes('favicon'))).toEqual([]);
  });

});
