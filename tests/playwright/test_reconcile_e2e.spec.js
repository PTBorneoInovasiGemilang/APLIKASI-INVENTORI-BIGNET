const { test, expect } = require('@playwright/test');

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

const sidebar = page => page.locator('#sidebar');

test.describe('Role & Page UX Reconciliation', () => {

  test('SUPERADMIN: monitoring menu ada, mutation button TIDAK tampil, route mutation ditolak', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await login(page, 'superadmin@ptbig.com');

    // Sidebar: monitoring entry insiden & opname tersedia (read-only)
    await expect(sidebar(page).getByText('Insiden (Damage/Lost)', { exact: true })).toBeVisible();
    await expect(sidebar(page).getByText('Stock Opname', { exact: true })).toBeVisible();

    // Penyesuaian Stok: TIDAK ada "Penyesuaian Baru"
    await page.goto('http://localhost:8000/adjustments');
    await expect(page.getByRole('link', { name: /Penyesuaian Baru/ })).toHaveCount(0);

    // Insiden: TIDAK ada "Buat Laporan"
    await page.goto('http://localhost:8000/incidents');
    await expect(page.getByRole('link', { name: /Buat Laporan/ })).toHaveCount(0);

    // Opname: TIDAK ada "Buat Stock Opname"
    await page.goto('http://localhost:8000/opnames');
    await expect(page.getByRole('link', { name: /Buat Stock Opname/ })).toHaveCount(0);

    // Direct route mutation -> 403
    for (const path of ['/incidents/create', '/opnames/create', '/adjustments/create']) {
      const resp = await page.goto('http://localhost:8000' + path);
      expect(resp.status()).toBe(403);
    }
  });

  test('ADMIN: insiden & opname bisa dibuat/diproses (CRUD sah tetap ada)', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await login(page, 'admin@ptbig.com');

    // Sidebar admin kini punya insiden + opname
    await expect(sidebar(page).getByText('Insiden (Damage/Lost)', { exact: true })).toBeVisible();
    await expect(sidebar(page).getByText('Stock Opname', { exact: true })).toBeVisible();

    // Insiden: tombol Buat Laporan tampil + form terbuka
    await page.goto('http://localhost:8000/incidents');
    await expect(page.getByRole('link', { name: /Buat Laporan/ })).toBeVisible();

    // Opname: tombol Buat Stock Opname tampil + form terbuka
    await page.goto('http://localhost:8000/opnames');
    await expect(page.getByRole('link', { name: /Buat Stock Opname/ })).toBeVisible();
    await page.click('text=Buat Stock Opname');
    await expect(page).toHaveURL(/.*\/opnames\/create/);

    // Penyesuaian: tombol Penyesuaian Baru tampil
    await page.goto('http://localhost:8000/adjustments');
    await expect(page.getByRole('link', { name: /Penyesuaian Baru/ })).toBeVisible();
  });

  test('TEKNISI: lapor insiden (pelapor), tanpa proses/admin; tanpa akses opname', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await login(page, 'teknisia@ptbig.com');

    // Insiden: bisa buat laporan
    await page.goto('http://localhost:8000/incidents');
    await expect(page.getByRole('link', { name: /Buat Laporan/ })).toBeVisible();

    // Opname: TIDAK bisa akses (bukan wewenang teknisi) -> 403
    const resp = await page.goto('http://localhost:8000/opnames');
    expect(resp.status()).toBe(403);

    // Detail insiden: tidak melihat form proses (hanya Admin)
    const incidentId = await page.evaluate(() => {
      const link = document.querySelector('a[href*="/incidents/show?id="]');
      return link ? link.getAttribute('href').split('=')[1] : null;
    });
    if (incidentId) {
      await page.goto(`http://localhost:8000/incidents/show?id=${incidentId}`);
      await expect(page.locator('text=Proses Investigasi')).toHaveCount(0);
    }
  });

  test('Empty state: search no-result & loans kosong memakai shared component terpusat', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 800 });
    await login(page, 'admin@ptbig.com');

    // Search no-result -> panel shared (ikon + judul + deskripsi)
    await page.goto('http://localhost:8000/search?q=zzzznothing');
    await expect(page.getByText('Tidak ada barang yang cocok dengan pencarian.')).toBeVisible();
    const centered = await page.evaluate(() => {
      const h3 = Array.from(document.querySelectorAll('h3')).find(el => el.textContent.includes('Tidak ada barang'));
      if (!h3) return null;
      const box = h3.parentElement;
      const icon = box.querySelector('i.fas');
      return { hasIcon: !!icon, h3: getComputedStyle(h3.parentElement).alignItems };
    });
    expect(centered).not.toBeNull();
    expect(centered.hasIcon).toBe(true);

    // Loans kosong -> empty state shared di dalam tabel (bukan di sisi kiri)
    await page.goto('http://localhost:8000/loans');
    const emptyCell = page.locator('main tbody tr td[colspan].align-middle');
    await expect(emptyCell).toBeVisible();
    await expect(emptyCell.getByText('Belum ada transaksi peminjaman.')).toBeVisible();
    // Tidak ada wrapper <td> dobel yang memecah layout
    const nested = await page.evaluate(() => {
      const cells = document.querySelectorAll('main tbody tr td[colspan]');
      return cells.length;
    });
    expect(nested).toBe(1);
  });

  test('Auth: forgot-password adalah halaman internal (D-016, tanpa guide penuh)', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await page.goto('http://localhost:8000/forgot-password');
    await expect(page.getByText(/Hubungi Superadmin untuk mereset kata sandi/)).toBeVisible();
    await expect(page.getByText('Panduan Cepat')).toHaveCount(0);
    await expect(page.getByText('Kirim Tautan Reset')).toHaveCount(0);
  });

  test('Dark mode: reconciliation pages tanpa console error', async ({ page }) => {
    const errors = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', err => errors.push(err.message));

    await page.setViewportSize({ width: 1280, height: 900 });
    await login(page, 'admin@ptbig.com');
    for (const path of ['/adjustments', '/incidents', '/opnames', '/loans', '/search?q=zzzznothing']) {
      await page.goto('http://localhost:8000' + path, { waitUntil: 'domcontentloaded' });
    }
    await page.click('button[aria-label="Toggle dark mode"]');
    await page.goto('http://localhost:8000/incidents', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('html')).toHaveClass(/dark/);
    await expect(page.locator('main h1').last()).toBeVisible();

    expect(errors.filter(e => !e.includes('favicon'))).toEqual([]);
  });

});
