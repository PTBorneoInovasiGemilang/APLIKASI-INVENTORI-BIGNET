const { test, expect } = require('@playwright/test');

const BASE = 'http://localhost:8000';

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

async function expectNoOverflow(page) {
  const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
  expect(overflow, 'horizontal overflow terdeteksi').toBe(false);
}

test.describe('Mobile-first TEKNISI', () => {

  const workflowPaths = [
    ['/dashboard', 'Dasbor'],
    ['/submissions', 'Pengajuan'],
    ['/loans', 'Peminjaman'],
    ['/returns', 'Pengembalian'],
    ['/serials', 'Nomor Seri Saya'],
    ['/installations/my_items', 'Barang Terpasang'],
    ['/timeline', 'Riwayat'],
    ['/notifications', 'Notifikasi'],
    ['/incidents/create', 'Lapor Kerusakan/Kehilangan'],
  ];

  for (const [path, label] of workflowPaths) {
    test(`375x812: workflow ${label} usable, no overflow, guide di bawah konten`, async ({ page }) => {
      await page.setViewportSize({ width: 375, height: 812 });
      const errors = [];
      page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
      page.on('pageerror', e => errors.push(e.message));

      await login(page, 'teknisia@ptbig.com');
      await page.goto(BASE + path);
      await page.waitForLoadState('domcontentloaded');
      await expectNoOverflow(page);
      // Page Guide rendered (mobile: below content — guide panel exists)
      if (path !== '/dashboard') {
        await expect(page.locator('text=Panduan Cepat').first()).toBeVisible();
      }
      // main heading present
      await expect(page.locator('main h1, main h2').first()).toBeVisible();

      expect(errors.filter(e => !e.includes('favicon') && !e.includes('tailwindcss'))).toEqual([]);
    });
  }

  test('375x812: sidebar hamburger membuka menu + pengajuan form usable + dark mode', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await login(page, 'teknisia@ptbig.com');

    // hamburger opens sidebar
    await page.click('button[aria-label="Toggle menu"]');
    await expect(page.locator('#sidebar')).toBeVisible();
    await expect(page.locator('#sidebar a', { hasText: 'Lapor Kerusakan/Kehilangan' }).first()).toBeVisible();

    // pengajuan form (create) — fields visible & usable on mobile
    await page.goto(BASE + '/submissions/create');
    await page.waitForLoadState('domcontentloaded');
    await expectNoOverflow(page);
    const inputs = await page.locator('main input, main select, main textarea').count();
    expect(inputs).toBeGreaterThan(0);

    // dark mode
    await page.click('button[aria-label="Toggle dark mode"]');
    await expect(page.locator('html')).toHaveClass(/dark/);
    await expectNoOverflow(page);
  });

  test('390x844 + 768x1024: no overflow pada dashboard & daftar', async ({ page }) => {
    for (const [w, h] of [[390, 844], [768, 1024]]) {
      await page.setViewportSize({ width: w, height: h });
      await login(page, 'teknisia@ptbig.com');
      await page.goto(BASE + '/dashboard');
      await page.waitForLoadState('domcontentloaded');
      await expectNoOverflow(page);
      await page.goto(BASE + '/loans');
      await page.waitForLoadState('domcontentloaded');
      await expectNoOverflow(page);
      await page.goto(BASE + '/timeline');
      await page.waitForLoadState('domcontentloaded');
      await expectNoOverflow(page);
    }
  });

  test('desktop 1280: teknisi workflows tetap utuh', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await login(page, 'teknisia@ptbig.com');
    await page.goto(BASE + '/dashboard');
    await expect(page.locator('main h1, main h2').first()).toBeVisible();
    // Guide di panel kanan desktop (guide panel posisi setelah konten)
    const guide = page.locator('text=Panduan Cepat').first();
    await expect(guide).toBeVisible();
  });
});
