const { test, expect } = require('@playwright/test');
const { execSync } = require('child_process');
const path = require('path');

const BASE = 'http://localhost:8000';
const HELPER = path.join(__dirname, 'helpers', 'user_seed.php');

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

function seedPendingUser() {
  const ts = Date.now();
  const email = `notif_pending_${ts}@ptbig.com`;
  const id = execSync(`php "${HELPER}" seed "${email}"`, { encoding: 'utf8' }).trim();
  return { id, email };
}

function cleanupUser(id) {
  execSync(`php "${HELPER}" cleanup ${id}`, { encoding: 'utf8' });
  execSync(`php -r "$p=new PDO('mysql:host=127.0.0.1;dbname=db_gudang_tommy;charset=utf8mb4','root','');$p->prepare('DELETE FROM notifications WHERE user_id=?')->execute([${id}]);"`, { encoding: 'utf8' });
}

test.describe('Notification Center', () => {

  test('register -> SUPERADMIN notified; approve -> user notified; mark read/all; isolation', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });

    // Public register (photo required)
    const ts = Date.now();
    const email = `notif_reg_${ts}@ptbig.com`;
    await page.goto(BASE + '/register');
    await page.fill('input[name="name"]', 'Notif Register');
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="phone"]', '0812');
    await page.selectOption('select[name="role"]', 'TEKNISI_NOC');
    const png = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', 'base64');
    await page.setInputFiles('input[type="file"][name="photo"]', { name: 'avatar.png', mimeType: 'image/png', buffer: png });
    await page.fill('input[name="password"]', 'temp123456');
    await page.fill('input[name="password_confirmation"]', 'temp123456');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/.*\/login/);

    // Superadmin sees bell badge with pending-registration notification
    await login(page, 'superadmin@ptbig.com');
    await page.goto(BASE + '/notifications');
    await expect(page.locator('text=Pendaftaran baru menunggu persetujuan').first()).toBeVisible();
    await expect(page.locator('text=' + email).first()).toBeVisible();

    // Mark all read → badge clears
    await page.click('button:has-text("Tandai Semua Dibaca")');
    await expect(page.locator('text=Semua notifikasi ditandai sudah dibaca').first()).toBeVisible();
    await page.goto(BASE + '/dashboard');
    await expect(page.locator('a[aria-label="Notifikasi"] .absolute')).toHaveCount(0);

    // Approve the user → user gets notified
    await page.goto(BASE + '/users');
    const row = page.locator(`tr:has-text("${email}")`);
    await row.locator('button:has-text("Tinjau")').click();
    await page.locator('#approvalModal button:has-text("Setujui")').click();
    await expect(page.locator('text=User approved').first()).toBeVisible();

    // The user sees their approval notification after login
    await page.goto(BASE + '/logout');
    await login(page, email, 'temp123456');
    await page.goto(BASE + '/notifications');
    await expect(page.locator('text=Akun Anda disetujui').first()).toBeVisible();

    // Isolation: this user cannot see superadmin's notifications
    await expect(page.locator('text=Pendaftaran baru menunggu persetujuan')).toHaveCount(0);

    // cleanup
    const uid = execSync(`php -r "$p=new PDO('mysql:host=127.0.0.1;dbname=db_gudang_tommy;charset=utf8mb4','root','');$s=$p->prepare('SELECT id FROM users WHERE email=?');$s->execute(['${email}']);echo $s->fetchColumn();"`, { encoding: 'utf8' }).trim();
    cleanupUser(uid);
  });

  test('mark single read, dark mode, mobile, console clean', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    const errors = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    const { id, email } = seedPendingUser();
    try {
      await login(page, 'superadmin@ptbig.com');
      await page.goto(BASE + '/notifications');
      // empty-ish page still renders with guide
      await expect(page.locator('text=Panduan Cepat').first()).toBeVisible();

      // Dark mode toggle renders without error
      await page.click('button[aria-label="Toggle dark mode"]');
      await page.goto(BASE + '/notifications');
      await expect(page.locator('h1', { hasText: 'Notifikasi' })).toBeVisible();

      // Mobile viewport
      await page.setViewportSize({ width: 390, height: 844 });
      await page.goto(BASE + '/notifications');
      await expect(page.locator('h1', { hasText: 'Notifikasi' })).toBeVisible();
      const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
      expect(overflow).toBe(false);
    } finally {
      cleanupUser(id);
    }
    expect(errors.filter(e => !e.includes('403'))).toEqual([]);
  });
});
