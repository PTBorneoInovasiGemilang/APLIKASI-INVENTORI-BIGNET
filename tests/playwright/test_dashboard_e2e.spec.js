const { test, expect } = require('@playwright/test');

async function login(page, email, password = 'password') {
  await page.goto('http://localhost:8000/login');
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/.*\/dashboard/);
}

test.describe('Dashboard Analytics E2E', () => {

  test('Superadmin: KPI, charts, lists, alerts, system quick actions, page guide', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com');

    // Welcome + guide
    await expect(page.locator('main h2').first()).toContainText('Selamat datang');
    await expect(page.locator('text=Panduan Cepat')).toBeVisible();

    // KPI cards
    for (const kpi of ['Total Barang', 'Stok Rendah', 'Masuk Hari Ini', 'Keluar Hari Ini', 'Dalam Transfer', 'Pengajuan Aktif', 'Peminjaman Aktif', 'Terpasang']) {
      await expect(page.locator('main').getByText(kpi, { exact: true }).first()).toBeVisible();
    }

    // Charts (real data)
    await expect(page.locator('text=Trend Pergerakan')).toBeVisible();
    for (const chart of ['Barang Masuk', 'Barang Keluar', 'Transfer']) {
      await expect(page.locator('main').getByText(chart, { exact: true }).first()).toBeVisible();
    }
    // At least one chart bar rendered (style height set) — data nyata, bukan placeholder kosong
    const barCount = await page.locator('div[style*="height:"]').count();
    expect(barCount).toBeGreaterThan(0);

    // Lists
    await expect(page.locator('text=Pergerakan Stok Terakhir')).toBeVisible();
    await expect(page.locator('text=Pengajuan Aktif').first()).toBeVisible();
    await expect(page.locator('text=Stok Rendah').first()).toBeVisible();
    await expect(page.locator('h3:has-text("Belum Dikembalikan")')).toBeVisible();
    await expect(page.locator('text=Instalasi Terpasang')).toBeVisible();
    await expect(page.locator('text=Insiden (Damage / Lost)')).toBeVisible();
    await expect(page.locator('text=Aktivitas Terbaru')).toBeVisible();

    // Alerts panel (data-driven: low stock exists in seed -> alert muncul)
    await expect(page.locator('text=Perhatian').first()).toBeVisible();

    // Superadmin quick actions: monitoring/system focus, BUKAN CRUD operasional gudang
    for (const action of ['Kelola Pengguna', 'Pengaturan Sistem', 'Log Aktivitas', 'Laporan', 'Timeline']) {
      await expect(page.locator('main').getByText(action, { exact: true }).first()).toBeVisible();
    }
    // Superadmin quick actions TIDAK memuat shortcut CRUD operasional gudang
    await expect(page.locator('main a[href*="/incoming/create"]')).toHaveCount(0);
    await expect(page.locator('main a[href*="/outgoing/create"]')).toHaveCount(0);
  });

  test('Admin: operational quick actions & KPI visible', async ({ page }) => {
    await login(page, 'admin@ptbig.com');

    await expect(page.locator('main h2').first()).toContainText('Selamat datang');
    await expect(page.locator('main').getByText('Total Barang', { exact: true })).toBeVisible();

    // Admin: operational CRUD quick actions
    for (const action of ['Barang Masuk', 'Barang Keluar', 'Transfer Gudang', 'Proses Pengajuan', 'Stock Opname', 'Damage / Lost']) {
      await expect(page.locator('main').getByText(action, { exact: true }).first()).toBeVisible();
    }
    // Admin TIDAK melihat quick action sistem (Kelola Pengguna)
    await expect(page.locator('main').getByText('Kelola Pengguna', { exact: true })).toHaveCount(0);

    // Chart + activity untuk admin
    await expect(page.locator('text=Trend Pergerakan')).toBeVisible();
    await expect(page.locator('text=Aktivitas Terbaru')).toBeVisible();
  });

  test('Teknisi: personal dashboard, role-scoped data & quick actions', async ({ page }) => {
    await login(page, 'teknisia@ptbig.com');

    await expect(page.locator('main h2').first()).toContainText('Selamat datang');
    await expect(page.locator('main').getByText('Pengajuan Aktif Saya', { exact: true })).toBeVisible();
    await expect(page.locator('main').getByText('Barang Dipegang', { exact: true })).toBeVisible();

    // Teknisi quick actions
    for (const action of ['Buat Pengajuan', 'Pengembalian', 'Barang Saya', 'Instalasi', 'Riwayat Saya']) {
      await expect(page.locator('main').getByText(action, { exact: true }).first()).toBeVisible();
    }
    // Teknisi tidak melihat KPI sistem gudang
    await expect(page.locator('main').getByText('Total Barang', { exact: true })).toHaveCount(0);

    // List personal
    await expect(page.locator('text=Pengajuan Saya')).toBeVisible();
    await expect(page.locator('text=Peminjaman Saya')).toBeVisible();
    await expect(page.locator('text=Instalasi Saya')).toBeVisible();
  });

  test('Empty states render when no data (outstanding returns)', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com');
    await expect(page.locator('text=Tidak ada pinjaman yang belum dikembalikan.')).toBeVisible();
  });

  test('Dark mode toggle works on dashboard', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com');
    await expect(page.locator('html')).not.toHaveClass(/dark/);
    await page.click('button[aria-label="Toggle dark mode"]');
    await expect(page.locator('html')).toHaveClass(/dark/);
    // Konten tetap render setelah toggle
    await expect(page.locator('text=Trend Pergerakan')).toBeVisible();
  });

  test('No console errors on dashboard', async ({ page }) => {
    const errors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    page.on('pageerror', err => errors.push(err.message));

    await login(page, 'superadmin@ptbig.com');
    await page.waitForLoadState('networkidle');
    await page.click('button[aria-label="Toggle dark mode"]');
    await page.waitForTimeout(300);

    expect(errors.filter(e => !e.includes('favicon'))).toEqual([]);
  });

});
