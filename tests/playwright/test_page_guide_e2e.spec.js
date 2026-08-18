const { test, expect } = require('@playwright/test');

async function login(page, email, password = 'password') {
  await page.goto('http://localhost:8000/logout');
  await page.goto('http://localhost:8000/login');
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/.*\/dashboard/);
}

test.describe('Global Page Guide E2E', () => {

  test('Panel Panduan Cepat tampil dengan section standard di halaman representatif', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com');

    const pages = [
      '/dashboard',
      '/search',
      '/categories',
      '/stocks',
      '/loans',
      '/incidents',
      '/users',
      '/timeline',
      '/reports',
    ];

    for (const path of pages) {
      await page.goto('http://localhost:8000' + path);
      await expect(page.locator('main').getByText('Panduan Cepat').first(), `guide di ${path}`).toBeVisible();
      await expect(page.locator('main').getByText('Tujuan Halaman').first(), `section tujuan di ${path}`).toBeVisible();
      // Layout tidak rusak: heading konten utama tetap tampil (dashboard memakai h2)
      await expect(page.locator('main h1, main h2').first(), `heading di ${path}`).toBeVisible();
    }
  });

  test('Konten panduan relevan per modul (bukan template generik)', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com');

    // Master Data: kategori
    await page.goto('http://localhost:8000/categories');
    await expect(page.locator('main').getByText(/klasifikasi barang inventaris/i)).toBeVisible();

    // Asset Lifecycle: incidents
    await page.goto('http://localhost:8000/incidents');
    await expect(page.locator('main').getByText(/kerusakan\/kehilangan aset/i)).toBeVisible();

    // Users/System: workflow approval
    await page.goto('http://localhost:8000/users');
    await expect(page.locator('main').getByText(/Approved\/Rejected/i)).toBeVisible();

    // Transaksi: transfer double-counting rule
    await page.goto('http://localhost:8000/transfers');
    await expect(page.locator('main').getByText(/double counting/i)).toBeVisible();

    // Stock Opname != Adjustment
    await page.goto('http://localhost:8000/opnames');
    await expect(page.locator('main').getByText(/Stock Opname.*Adjustment/i).first()).toBeVisible();
  });

  test('Micro-guidance tampil pada halaman form (/users/create)', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com');
    await page.goto('http://localhost:8000/users/create');
    await expect(page.locator('main').getByText('Panduan Cepat')).toBeVisible();
    await expect(page.locator('main').getByText('Micro-guidance')).toBeVisible();
    await expect(page.locator('main').getByText(/Field bertanda \* wajib diisi/i)).toBeVisible();
  });

  test('Role visibility: Admin & Teknisi melihat panduan yang relevan', async ({ page }) => {
    // Admin: modul operasional
    await login(page, 'admin@ptbig.com');
    await page.goto('http://localhost:8000/loans');
    await expect(page.locator('main').getByText('Panduan Cepat').first()).toBeVisible();
    await expect(page.locator('main').getByText(/custody/i).first()).toBeVisible();

    // Teknisi: halaman personal tetap ada panduan
    await login(page, 'teknisia@ptbig.com');
    await page.goto('http://localhost:8000/timeline');
    await expect(page.locator('main').getByText('Panduan Cepat').first()).toBeVisible();
    await expect(page.locator('main').getByText(/Teknisi hanya melihat barang yang pernah ia pegang/i)).toBeVisible();
  });

  test('Dark mode: panduan tetap tampil dan layout tidak rusak', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com');
    await page.goto('http://localhost:8000/incidents');

    // Light mode
    await expect(page.locator('main').getByText('Panduan Cepat').first()).toBeVisible();
    await expect(page.locator('html')).not.toHaveClass(/dark/);

    // Dark mode
    await page.click('button[aria-label="Toggle dark mode"]');
    await expect(page.locator('html')).toHaveClass(/dark/);
    await expect(page.locator('main').getByText('Panduan Cepat').first()).toBeVisible();
    await expect(page.locator('main h1').first()).toBeVisible();
  });

  test('Responsive: panduan tetap tampil pada viewport mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await login(page, 'superadmin@ptbig.com');
    await page.goto('http://localhost:8000/stocks');
    await expect(page.locator('main').getByText('Panduan Cepat').first()).toBeVisible();
    await expect(page.locator('main').getByText('Tujuan Halaman').first()).toBeVisible();
  });

  test('No console errors pada halaman ber-panduan', async ({ page }) => {
    const errors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    page.on('pageerror', err => errors.push(err.message));

    await login(page, 'superadmin@ptbig.com');
    for (const path of ['/categories', '/incidents', '/users', '/timeline', '/reports']) {
      await page.goto('http://localhost:8000' + path);
    }
    await page.waitForLoadState('networkidle');

    expect(errors.filter(e => !e.includes('favicon'))).toEqual([]);
  });

});
