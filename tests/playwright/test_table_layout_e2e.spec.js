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

// Scrolling container of the first table in main
const scrollBox = page => page.locator('main .overflow-x-auto').first();

test.describe('Global Table Layout Rule', () => {

  test('Desktop: tabel lebar punya scroll terkontrol, aksi terlihat, header/body sejajar', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 800 });
    await login(page, 'admin@ptbig.com');
    await page.goto('http://localhost:8000/items');

    const box = scrollBox(page);
    await expect(box).toBeVisible();
    // Konten tidak keluar viewport (tidak ada horizontal overflow di level halaman)
    const pageOverflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
    expect(pageOverflow).toBeLessThanOrEqual(1);

    // Kolom No (kiri) sticky & Aksi (kanan) sticky
    const firstTdPos = await page.evaluate(() => {
      const td = document.querySelector('main tbody tr td');
      const th = document.querySelector('main thead th');
      return { td: getComputedStyle(td).position, th: getComputedStyle(th).position };
    });
    expect(firstTdPos.td).toBe('sticky');
    expect(firstTdPos.th).toBe('sticky');

    // Header & body tetap sejajar saat scroll horizontal
    await box.evaluate(el => { el.scrollLeft = el.scrollWidth; });
    const aligned = await page.evaluate(() => {
      const th = document.querySelectorAll('main thead th');
      const td = document.querySelectorAll('main tbody tr td');
      const th2 = th[1].getBoundingClientRect();
      const td2 = td[1].getBoundingClientRect();
      return Math.abs(th2.left - td2.left) <= 1;
    });
    expect(aligned).toBe(true);

    // Aksi (kolom terakhir) tetap terlihat setelah scroll ke kanan
    const aksi = await page.evaluate(() => {
      const td = document.querySelector('main tbody tr td:last-of-type');
      return td.getBoundingClientRect().right;
    });
    expect(aksi).toBeGreaterThan(0);
  });

  test('Mobile 375px: No tetap kiri, Aksi tetap kanan, tanpa overflow halaman', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await login(page, 'admin@ptbig.com');
    await page.goto('http://localhost:8000/outgoing');

    const box = scrollBox(page);
    // Tabel memang lebih lebar dari kontainer -> bisa scroll horizontal
    const scrollable = await box.evaluate(el => el.scrollWidth > el.clientWidth + 1);
    expect(scrollable).toBe(true);

    // Tidak ada overflow di level halaman
    const pageOverflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
    expect(pageOverflow).toBeLessThanOrEqual(1);

    // Kolom No terlihat di kiri
    const noVisible = await page.evaluate(() => {
      const td = document.querySelector('main tbody tr td');
      return td.getBoundingClientRect().left >= 0;
    });
    expect(noVisible).toBe(true);

    // Scroll ke kanan: Aksi tetap terlihat (sticky right) dan No tetap terlihat (sticky left)
    await box.evaluate(el => { el.scrollLeft = el.scrollWidth; });
    const sticky = await page.evaluate(() => {
      const tds = document.querySelectorAll('main tbody tr td');
      const first = tds[0].getBoundingClientRect();
      const last = tds[tds.length - 1].getBoundingClientRect();
      return { firstLeft: first.left, lastRight: last.right };
    });
    expect(sticky.firstLeft).toBeGreaterThanOrEqual(0);
    expect(sticky.lastRight).toBeLessThanOrEqual(375 + 1);
  });

  test('Empty state: baris kosong (colspan) tidak rusak oleh sticky', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 800 });
    await login(page, 'admin@ptbig.com');
    await page.goto('http://localhost:8000/loans');

    // /loans kosong -> empty state tampil
    await expect(page.locator('main').getByText(/Belum ada|Tidak ada/).first()).toBeVisible();

    // Sel empty-state (satu-satunya sel di baris) TIDAK sticky
    const emptyPos = await page.evaluate(() => {
      const td = document.querySelector('main tbody tr td[colspan]');
      if (!td) return null;
      return getComputedStyle(td).position;
    });
    expect(emptyPos).not.toBe('sticky');
  });

  test('Halaman yang sebelumnya terpotong kini punya scroll kontainer', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await login(page, 'admin@ptbig.com');

    for (const path of ['/incoming', '/submissions/show?id=4']) {
      await page.goto('http://localhost:8000' + path);
      const box = scrollBox(page);
      await expect(box).toBeVisible();
      // Kontainer scroll punya potensi scroll (atau setidaknya overflow-x-auto terpasang)
      const hasOverflow = await page.evaluate(() => {
        const el = document.querySelector('main .overflow-x-auto');
        return el && getComputedStyle(el).overflowX === 'auto';
      });
      expect(hasOverflow).toBe(true);
    }
  });

  test('Dark mode: sticky cells punya background solid, layout utuh, tanpa console error', async ({ page }) => {
    const errors = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', err => errors.push(err.message));

    await page.setViewportSize({ width: 768, height: 900 });
    await login(page, 'admin@ptbig.com');
    await page.goto('http://localhost:8000/items');
    await page.click('button[aria-label="Toggle dark mode"]');
    await expect(page.locator('html')).toHaveClass(/dark/);

    const sticky = await page.evaluate(() => {
      const td = document.querySelector('main tbody tr td');
      const th = document.querySelector('main thead th');
      return {
        tdBg: getComputedStyle(td).backgroundColor,
        thBg: getComputedStyle(th).backgroundColor,
        position: getComputedStyle(td).position
      };
    });
    expect(sticky.position).toBe('sticky');
    // Background solid (bukan transparan) agar konten di belakang tidak tembus
    expect(sticky.tdBg).not.toBe('rgba(0, 0, 0, 0)');
    expect(sticky.thBg).not.toBe('rgba(0, 0, 0, 0)');

    // Tabel tetap berisi data & tidak ada error konsol
    await expect(page.locator('main tbody tr').first()).toBeVisible();
    expect(errors.filter(e => !e.includes('favicon'))).toEqual([]);
  });

});
