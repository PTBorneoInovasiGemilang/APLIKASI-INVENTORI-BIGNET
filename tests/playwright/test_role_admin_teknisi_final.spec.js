const { test, expect } = require('@playwright/test');

const BASE = 'http://localhost:8001';
const ADMIN = { email: 'admin@ptbig.com', password: 'admin123' };
const TEKNISI = { email: 'teknisi@ptbig.com', password: 'teknisi123' };

const consoleErrors = [];

async function trackConsole(page) {
  page.on('console', msg => {
    const text = msg.text();
    if (msg.type() === 'error' && !/favicon/i.test(text) && !text.includes('status of 403') && !text.includes('status of 404')) {
      consoleErrors.push(`[${msg.type()}] ${text}`);
    }
  });
  page.on('pageerror', err => consoleErrors.push(`[pageerror] ${err.message}`));
}

async function login(page, user) {
  await page.goto(BASE + '/login');
  if (!page.url().includes('/login')) {
    await page.goto(BASE + '/logout');
    await page.goto(BASE + '/login');
  }
  await page.fill('input[name="email"]', user.email);
  await page.fill('input[name="password"]', user.password);
  await page.click('button[type="submit"]');
  await page.waitForURL('**/dashboard');
}

const sidebar = page => page.locator('#sidebar');

test.describe.configure({ mode: 'serial' });

test.describe('FINAL RBAC & FUNCTIONAL VERIFICATION: ROLE ADMIN & ROLE TEKNISI', () => {

  // =========================================================================
  // A. ROLE ADMIN VERIFICATION
  // =========================================================================

  test('ADMIN-1: Dashboard & Sidebar Menu Visibility', async ({ page }) => {
    await trackConsole(page);
    await login(page, ADMIN);

    // Dashboard renders properly
    await expect(page.locator('main')).toBeVisible();
    await expect(page.getByText('Dasbor', { exact: true }).first()).toBeVisible();

    // Verify authorized ADMIN menus are in sidebar
    const expectedAdminMenus = [
      'Dasbor',
      'Pencarian Barang',
      'Kategori Barang',
      'Satuan',
      'Gudang',
      'Lokasi',
      'Master Barang',
      'Stok Barang',
      'Nomor Seri',
      'Cetak Label Massal',
      'Riwayat Timeline',
      'Pengajuan Barang',
      'Barang Masuk',
      'Barang Keluar',
      'Transfer Gudang',
      'Peminjaman',
      'Pengembalian',
      'Instalasi',
      'Penyesuaian Stok',
      'Insiden (Damage/Lost)',
      'Stock Opname',
      'Laporan Stok & Mutasi',
      'Import Data'
    ];

    for (const menu of expectedAdminMenus) {
      await expect(sidebar(page).getByText(menu, { exact: true }).first()).toBeVisible();
    }

    // Verify SUPERADMIN-only menus DO NOT appear in ADMIN sidebar
    const superadminOnlyMenus = ['Pengguna', 'Pengaturan Sistem', 'Log Aktivitas'];
    for (const menu of superadminOnlyMenus) {
      await expect(sidebar(page).getByText(menu, { exact: true })).toHaveCount(0);
    }

    // Direct route to superadmin settings should be blocked (403)
    const settingsRes = await page.goto(BASE + '/settings');
    expect(settingsRes.status()).toBe(403);
    await expect(page.getByText('Akses Ditolak')).toBeVisible();

    // Direct route to superadmin users should be blocked (403)
    const usersRes = await page.goto(BASE + '/users');
    expect(usersRes.status()).toBe(403);
    await expect(page.getByText('Akses Ditolak')).toBeVisible();
  });

  test('ADMIN-2: Master Data Management (Items, Categories, Units, Warehouses, Locations)', async ({ page }) => {
    await trackConsole(page);
    await login(page, ADMIN);

    // 1. Master Barang (/items)
    await page.goto(BASE + '/items');
    await expect(page.locator('table')).toBeVisible();
    await expect(page.getByRole('button', { name: /Tambah Barang/i }).or(page.getByRole('link', { name: /Tambah Barang/i }))).toBeVisible();

    // 2. Kategori (/categories)
    await page.goto(BASE + '/categories');
    await expect(page.locator('table')).toBeVisible();
    const addCatBtn = page.getByRole('button', { name: /Tambah Kategori/i });
    await expect(addCatBtn).toBeVisible();
    await addCatBtn.click();
    await expect(page.locator('#addModal, [id*="modal"], form[action*="/categories"]').first()).toBeVisible();
    await page.keyboard.press('Escape');

    // 3. Satuan (/units)
    await page.goto(BASE + '/units');
    await expect(page.locator('table')).toBeVisible();
    const addUnitBtn = page.getByRole('button', { name: /Tambah Satuan/i });
    await expect(addUnitBtn).toBeVisible();
    await addUnitBtn.click();
    await expect(page.locator('#addModal, [id*="modal"], form[action*="/units"]').first()).toBeVisible();
    await page.keyboard.press('Escape');

    // 4. Gudang (/warehouses)
    await page.goto(BASE + '/warehouses');
    await expect(page.locator('table')).toBeVisible();
    const addWhBtn = page.getByRole('button', { name: /Tambah Gudang/i });
    await expect(addWhBtn).toBeVisible();
    await addWhBtn.click();
    await expect(page.locator('#addModal, [id*="modal"], form[action*="/warehouses"]').first()).toBeVisible();
    await page.keyboard.press('Escape');

    // 5. Lokasi (/locations)
    await page.goto(BASE + '/locations');
    await expect(page.locator('table')).toBeVisible();
    const addLocBtn = page.getByRole('button', { name: /Tambah Lokasi/i });
    await expect(addLocBtn).toBeVisible();
    await addLocBtn.click();
    await expect(page.locator('#addModal, [id*="modal"], form[action*="/locations"]').first()).toBeVisible();
    await page.keyboard.press('Escape');
  });

  test('ADMIN-3: Inventaris & Stock Monitoring (/stocks, /serials, /labels/create)', async ({ page }) => {
    await trackConsole(page);
    await login(page, ADMIN);

    // 1. Stok Barang (/stocks)
    await page.goto(BASE + '/stocks');
    await expect(page.locator('table')).toBeVisible();
    await expect(page.getByText('Ringkasan Stok').first()).toBeVisible();
    await expect(page.getByText('tersedia dan siap digunakan').first()).toBeVisible();

    // 2. Nomor Seri (/serials)
    await page.goto(BASE + '/serials');
    await expect(page.locator('table')).toBeVisible();

    // 3. Cetak Label Massal (/labels/create)
    await page.goto(BASE + '/labels/create');
    await expect(page.locator('form, table').first()).toBeVisible();
    await expect(page.getByText('Cetak Label Massal').first()).toBeVisible();
  });

  test('ADMIN-4: Operational Transactions & Forms Access', async ({ page }) => {
    await trackConsole(page);
    await login(page, ADMIN);

    // 1. Barang Masuk (/incoming -> /incoming/create)
    await page.goto(BASE + '/incoming');
    await expect(page.locator('table')).toBeVisible();
    await page.goto(BASE + '/incoming/create');
    await expect(page.locator('form')).toBeVisible();

    // 2. Barang Keluar (/outgoing -> /outgoing/create)
    await page.goto(BASE + '/outgoing');
    await expect(page.locator('table')).toBeVisible();
    await page.goto(BASE + '/outgoing/create');
    await expect(page.locator('form')).toBeVisible();

    // 3. Transfer Gudang (/transfers -> /transfers/create)
    await page.goto(BASE + '/transfers');
    await expect(page.locator('table')).toBeVisible();
    await page.goto(BASE + '/transfers/create');
    await expect(page.locator('form')).toBeVisible();

    // 4. Stock Opname (/opnames -> /opnames/create)
    await page.goto(BASE + '/opnames');
    await expect(page.locator('table')).toBeVisible();
    await page.goto(BASE + '/opnames/create');
    await expect(page.locator('form')).toBeVisible();

    // 5. Penyesuaian Stok (/adjustments -> /adjustments/create)
    await page.goto(BASE + '/adjustments');
    await expect(page.locator('table')).toBeVisible();
    await page.goto(BASE + '/adjustments/create');
    await expect(page.locator('form')).toBeVisible();

    // 6. Insiden (/incidents -> /incidents/create)
    await page.goto(BASE + '/incidents');
    await expect(page.locator('table')).toBeVisible();
    await page.goto(BASE + '/incidents/create');
    await expect(page.locator('form')).toBeVisible();

    // 7. Peminjaman (/loans)
    await page.goto(BASE + '/loans');
    await expect(page.locator('table')).toBeVisible();

    // 8. Pengembalian (/returns)
    await page.goto(BASE + '/returns');
    await expect(page.locator('table')).toBeVisible();

    // 9. Instalasi (/installations)
    await page.goto(BASE + '/installations');
    await expect(page.locator('table')).toBeVisible();
  });

  test('ADMIN-5: Report Viewer & Timeline Access', async ({ page }) => {
    await trackConsole(page);
    await login(page, ADMIN);

    const reportRoutes = [
      '/reports/ledger',
      '/reports/serials',
      '/reports/submissions',
      '/reports/incoming',
      '/reports/outgoing',
      '/reports/transfers',
      '/reports/loans',
      '/reports/returns',
      '/reports/installations',
      '/reports/adjustments'
    ];

    for (const r of reportRoutes) {
      const resp = await page.goto(BASE + r);
      expect(resp.status(), `Route ${r} should be 200`).toBe(200);
      await expect(page.locator('table, h3:has-text("Belum ada data"), [class*="empty"]').first()).toBeVisible();
    }

    // Timeline (/timeline)
    const timeResp = await page.goto(BASE + '/timeline');
    expect(timeResp.status()).toBe(200);
    await expect(page.locator('table').first()).toBeVisible();
  });

  test('ADMIN-6: Regression Business Flow Verification on Existing State', async ({ page }) => {
    await trackConsole(page);
    await login(page, ADMIN);

    // Verify submission detail & approval access (id=1)
    await page.goto(BASE + '/submissions/show?id=1');
    await expect(page.locator('body')).toContainText('Proses Persetujuan');
    await expect(page.locator('body')).toContainText('UAT Teknisi');

    // Verify loan detail (id=6 partial returned)
    await page.goto(BASE + '/loans/show?id=6');
    const loanBody = await page.locator('body').textContent();
    expect(/kembali sebagian|partial_returned|dipinjam/i.test(loanBody)).toBeTruthy();

    // Verify return detail (id=1)
    await page.goto(BASE + '/returns/show?id=1');
    const returnBody = await page.locator('body').textContent();
    expect(/pengembalian|selesai|completed/i.test(returnBody)).toBeTruthy();

    // Verify installation detail (id=1)
    await page.goto(BASE + '/installations/show?id=1');
    await expect(page.locator('body')).toContainText('UAT Customer Bignet Install');

    // Verify incident detail (id=1)
    await page.goto(BASE + '/incidents/show?id=1');
    await expect(page.locator('body')).toContainText('Detail Laporan Insiden');
    // Admin has process / investigation form
    await expect(page.locator('select[name="action"], button:has-text("Update Status"), button:has-text("Simpan")').first()).toBeVisible();

    // Verify stock opname detail (id=1)
    await page.goto(BASE + '/opnames/show?id=1');
    await expect(page.locator('body')).toContainText('Detail Stock Opname');

    // Verify adjustment detail (id=1)
    await page.goto(BASE + '/adjustments/show?id=1');
    await expect(page.locator('body')).toContainText('Penyesuaian');
  });

  // =========================================================================
  // B. ROLE TEKNISI VERIFICATION
  // =========================================================================

  test('TEKNISI-1: Dashboard & Sidebar Menu Visibility (Personal Scope Only)', async ({ page }) => {
    await trackConsole(page);
    await login(page, TEKNISI);

    // Personal dashboard
    await expect(page.locator('main')).toBeVisible();
    await expect(page.getByText('Dasbor Saya', { exact: true }).first()).toBeVisible();

    // Authorized menus for Teknisi
    const expectedTeknisiMenus = [
      'Dasbor Saya',
      'Pengajuan Barang Saya',
      'Barang Dipinjam',
      'Pengembalian Barang',
      'Nomor Seri Saya',
      'Barang Terpasang',
      'Riwayat Saya',
      'Instalasi',
      'Foto Instalasi',
      'Lapor Kerusakan/Kehilangan'
    ];

    for (const menu of expectedTeknisiMenus) {
      await expect(sidebar(page).getByText(menu, { exact: true }).first()).toBeVisible();
    }

    // Unauthorized Admin/Superadmin menus MUST NOT appear
    const forbiddenAdminMenus = [
      'Master Barang',
      'Kategori Barang',
      'Satuan',
      'Gudang',
      'Lokasi',
      'Barang Masuk',
      'Barang Keluar',
      'Transfer Gudang',
      'Penyesuaian Stok',
      'Stock Opname',
      'Cetak Label Massal',
      'Laporan Stok & Mutasi',
      'Pengguna',
      'Pengaturan Sistem',
      'Import Data'
    ];

    for (const menu of forbiddenAdminMenus) {
      await expect(sidebar(page).getByText(menu, { exact: true })).toHaveCount(0);
    }
  });

  test('TEKNISI-2: Backend RBAC / Direct Forbidden Routes (HTTP 403 Rejections)', async ({ page }) => {
    await trackConsole(page);
    await login(page, TEKNISI);

    const forbiddenRoutes = [
      '/adjustments',
      '/adjustments/create',
      '/opnames',
      '/opnames/create',
      '/transfers',
      '/transfers/create',
      '/incoming',
      '/incoming/create',
      '/outgoing',
      '/outgoing/create',
      '/categories',
      '/units',
      '/warehouses',
      '/locations',
      '/labels',
      '/labels/create',
      '/users',
      '/settings',
      '/reports'
    ];

    for (const route of forbiddenRoutes) {
      const resp = await page.goto(BASE + route);
      expect(resp.status(), `Teknisi accessing ${route} should receive 403`).toBe(403);
      await expect(page.getByText('Akses Ditolak')).toBeVisible();
    }
  });

  test('TEKNISI-3: Personal Operations & Flow Permissions (No Admin Actions)', async ({ page }) => {
    await trackConsole(page);
    await login(page, TEKNISI);

    // 1. Pengajuan: /submissions/create opens form
    await page.goto(BASE + '/submissions/create');
    await expect(page.locator('form')).toBeVisible();

    // 2. Submissions detail: Teknisi visiting /submissions/show?id=1 DOES NOT see Admin approval form
    await page.goto(BASE + '/submissions/show?id=1');
    await expect(page.locator('button:has-text("Setujui"), button:has-text("Proses Item"), select[name*="approval"]')).toHaveCount(0);

    // 3. Peminjaman: /loans/create opens form
    await page.goto(BASE + '/loans/create');
    await expect(page.locator('form')).toBeVisible();

    // 4. Return: /returns has "Ajukan Pengembalian" button
    await page.goto(BASE + '/returns');
    await expect(page.getByRole('link', { name: /Ajukan Pengembalian/i })).toBeVisible();

    // 5. Return detail: Teknisi DOES NOT see "Proses Pengembalian" form
    await page.goto(BASE + '/returns/show?id=1');
    await expect(page.locator('button:has-text("Proses Pengembalian"), form[action*="/returns/process"]')).toHaveCount(0);

    // 6. Instalasi: /installations/create opens form
    await page.goto(BASE + '/installations/create');
    await expect(page.locator('form').first()).toBeVisible();
    // 7. Insiden: /incidents/create opens form
    await page.goto(BASE + '/incidents/create');
    await expect(page.locator('form')).toBeVisible();

    // 8. Incident detail: Teknisi DOES NOT see "Proses Investigasi" resolution form
    await page.goto(BASE + '/incidents/show?id=1');
    await expect(page.locator('select[name="action"], button:has-text("Update Status")')).toHaveCount(0);

    // 9. Notifications: /notifications is accessible
    await page.goto(BASE + '/notifications');
    await expect(page.locator('main')).toBeVisible();

    // 10. Timeline: /timeline is accessible
    await page.goto(BASE + '/timeline');
    await expect(page.locator('main')).toBeVisible();
  });

  // =========================================================================
  // C. CONSOLE HYGIENE (0 ERRORS)
  // =========================================================================

  test('HYGIENE: 0 Console Errors Throughout All RBAC & Functional Flows', async () => {
    expect(consoleErrors, `Console errors encountered:\n${consoleErrors.join('\n')}`).toHaveLength(0);
  });

});
