const { test, expect } = require('@playwright/test');

async function login(page, email, password = 'password') {
  await page.goto('http://localhost:8000/login');
  // Jika masih login dari test sebelumnya, /login me-redirect ke /dashboard -> logout dulu
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

test.describe('Global Dark Mode + Role-Based UI', () => {

  test('Superadmin: monitoring/system sidebar, dark mode on table page', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com');

    // Sidebar: monitoring + system (bukan CRUD operasional harian)
    for (const menu of ['Dasbor', 'Pencarian Barang', 'Pengguna', 'Pengaturan Sistem', 'Log Aktivitas', 'Riwayat Timeline', 'Laporan Stok & Mutasi']) {
      await expect(sidebar(page).getByText(menu, { exact: true })).toBeVisible();
    }

    // Table page (users) — dark mode
    await page.goto('http://localhost:8000/users');
    await expect(page.locator('main h1, main h2').first()).toBeVisible();
    await expect(page.locator('html')).not.toHaveClass(/dark/);
    await page.click('button[aria-label="Toggle dark mode"]');
    await expect(page.locator('html')).toHaveClass(/dark/);
    // Tabel + toolbar tetap render di dark mode
    await expect(page.locator('table')).toBeVisible();
    await expect(page.locator('input[type="search"], input[name="q"], input[placeholder*="cari" i]').first()).toBeVisible();
  });

  test('Admin: operational sidebar without system menus', async ({ page }) => {
    await login(page, 'admin@ptbig.com');

    // Sidebar: master data + transaksi + import
    for (const menu of ['Master Barang', 'Barang Masuk', 'Barang Keluar', 'Transfer Gudang', 'Penyesuaian Stok', 'Import Data']) {
      await expect(sidebar(page).getByText(menu, { exact: true }).first()).toBeVisible();
    }
    // TIDAK ada menu sistem (hanya SUPERADMIN)
    for (const menu of ['Pengguna', 'Pengaturan Sistem', 'Log Aktivitas']) {
      await expect(sidebar(page).getByText(menu, { exact: true })).toHaveCount(0);
    }
  });

  test('Teknisi: personal sidebar without admin/system menus', async ({ page }) => {
    await login(page, 'teknisia@ptbig.com');

    for (const menu of ['Pengajuan Barang Saya', 'Barang Dipinjam', 'Nomor Seri Saya', 'Barang Terpasang', 'Foto Instalasi', 'Riwayat Saya']) {
      await expect(sidebar(page).getByText(menu, { exact: true })).toBeVisible();
    }
    for (const menu of ['Master Barang', 'Barang Masuk', 'Pengguna', 'Pengaturan Sistem', 'Laporan Stok & Mutasi', 'Import Data']) {
      await expect(sidebar(page).getByText(menu, { exact: true })).toHaveCount(0);
    }
  });

  test('Backend authorization: direct unauthorized routes -> 403', async ({ page }) => {
    // Teknisi -> /users (hanya SUPERADMIN)
    await login(page, 'teknisia@ptbig.com');
    await page.goto('http://localhost:8000/users');
    await expect(page.getByText('Akses Ditolak')).toBeVisible();

    // Teknisi -> /incoming (hanya SUPERADMIN/ADMIN)
    await page.goto('http://localhost:8000/incoming');
    await expect(page.getByText('Akses Ditolak')).toBeVisible();

    // Admin -> /settings (hanya SUPERADMIN)
    await login(page, 'admin@ptbig.com');
    await page.goto('http://localhost:8000/settings');
    await expect(page.getByText('Akses Ditolak')).toBeVisible();

    // Superadmin -> mutation route /incoming/create (read-only untuk modul operasional)
    await login(page, 'superadmin@ptbig.com');
    await page.goto('http://localhost:8000/incoming/create');
    await expect(page.getByText('Akses Ditolak')).toBeVisible();
  });

  test('Dark mode on form + detail page with audit timeline', async ({ page }) => {
    await login(page, 'admin@ptbig.com');

    // Form
    await page.goto('http://localhost:8000/incoming/create');
    await expect(page.locator('form').first()).toBeVisible();
    await page.click('button[aria-label="Toggle dark mode"]');
    await expect(page.locator('html')).toHaveClass(/dark/);
    await expect(page.locator('form').first()).toBeVisible();

    // Detail + audit timeline component
    await page.goto('http://localhost:8000/submissions/show?id=4');
    await expect(page.getByText('Riwayat Status & Audit')).toBeVisible();
    await expect(page.locator('html')).toHaveClass(/dark/);
    await expect(page.locator('table').first()).toBeVisible();

    // Timeline index tetap render di dark mode
    await page.goto('http://localhost:8000/timeline');
    await expect(page.locator('html')).toHaveClass(/dark/);
    await expect(page.locator('table')).toBeVisible();
  });

  test('Mobile viewport: sidebar hidden, hamburger toggles it', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await login(page, 'superadmin@ptbig.com');

    // Sidebar tersembunyi di mobile (translate-x-full)
    await expect(sidebar(page)).toHaveClass(/-translate-x-full/);
    await page.click('button[aria-label="Toggle menu"]');
    await expect(sidebar(page)).not.toHaveClass(/-translate-x-full/);
    // Menu tetap terlihat setelah dibuka
    await expect(sidebar(page).getByText('Pengguna', { exact: true })).toBeVisible();
  });

  test('No console errors across roles and pages', async ({ page }) => {
    const errors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    page.on('pageerror', err => errors.push(err.message));

    for (const email of ['superadmin@ptbig.com', 'admin@ptbig.com', 'teknisia@ptbig.com']) {
      await login(page, email);
      for (const path of ['/dashboard', '/timeline']) {
        await page.goto('http://localhost:8000' + path);
        await page.waitForLoadState('networkidle');
      }
    }
    // Dark mode di halaman /users sebagai superadmin (halaman yang diizinkan)
    await login(page, 'superadmin@ptbig.com');
    await page.click('button[aria-label="Toggle dark mode"]');
    await page.goto('http://localhost:8000/users');
    await page.waitForTimeout(300);

    expect(errors.filter(e => !e.includes('favicon'))).toEqual([]);
  });

});
