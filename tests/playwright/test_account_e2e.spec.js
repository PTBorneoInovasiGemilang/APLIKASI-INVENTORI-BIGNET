const { test, expect } = require('@playwright/test');
const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

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

const HELPER = path.join(__dirname, 'helpers', 'user_seed.php');

function seedPendingUser() {
  const ts = Date.now();
  const email = `acct_pending_${ts}@ptbig.com`;
  const id = execSync(`php "${HELPER}" seed "${email}"`, { encoding: 'utf8' }).trim();
  return { id, email };
}

function cleanupUser(id) {
  execSync(`php "${HELPER}" cleanup ${id}`, { encoding: 'utf8' });
}

test.describe('Account / User Management', () => {

  test('SUPERADMIN: user detail, pending approve flow, status-aware actions', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    const { id, email } = seedPendingUser();
    try {
      await login(page, 'superadmin@ptbig.com');

      // Index shows Lihat action
      await page.goto(BASE + '/users');
      await expect(page.locator('a[href*="/users/show/"]').first()).toBeVisible();

      // Detail page
      await page.goto(BASE + '/users/show/' + id);
      await expect(page.locator('h1', { hasText: 'Detail Pengguna' })).toBeVisible();
      await expect(page.getByText(email, { exact: true })).toBeVisible();
      // Pending → Setujui/Tolak
      await expect(page.locator('button', { hasText: 'Setujui' })).toBeVisible();
      await expect(page.locator('button', { hasText: 'Tolak' })).toBeVisible();
      await expect(page.locator('button', { hasText: 'Reset Password' })).toHaveCount(0);
      // Page guide rendered on the right
      await expect(page.locator('text=Panduan Cepat').first()).toBeVisible();

      // Approve
      page.once('dialog', d => d.accept());
      await page.click('button[type="submit"]:has-text("Setujui")');
      await expect(page).toHaveURL(new RegExp('/users/show/' + id));
      await expect(page.locator('button', { hasText: 'Nonaktifkan' })).toBeVisible();
      await expect(page.locator('button', { hasText: 'Reset Password' })).toBeVisible();
      // Audit history entry visible
      await expect(page.locator('text=APPROVE_USER').first()).toBeVisible();

      // New password from reset works; old rejected
      page.once('dialog', d => d.accept());
      await page.click('button:has-text("Reset Password")');
      await expect(page).toHaveURL(new RegExp('/users/show/' + id));
      const flash = await page.locator('.toast, .bg-green-100').first().textContent();
      const m = flash.match(/sementara[^:]*:\s*([A-Za-z0-9]+)/);
      expect(m).not.toBeNull();
      const tempPw = m[1];

      // Login with temp password
      await page.goto(BASE + '/logout');
      await page.goto(BASE + '/login');
      await page.fill('input[name="email"]', email);
      await page.fill('input[name="password"]', tempPw);
      await page.click('button[type="submit"]');
      await expect(page).toHaveURL(/.*\/dashboard/);
      await page.goto(BASE + '/logout');

      // Old password rejected
      await loginExpectFail(page, email, 'temp123456');
    } finally {
      cleanupUser(id);
    }
  });

  test('SUPERADMIN: deactivate blocks login, reactivate restores', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    const { id, email } = seedPendingUser();
    try {
      await login(page, 'superadmin@ptbig.com');

      // approve via index modal flow (Tinjau) then deactivate via detail
      await page.goto(BASE + '/users');
      await page.locator(`tr:has-text("${email}") button:has-text("Tinjau")`).click();
      await page.locator('#approvalModal button:has-text("Setujui")').click();
      await expect(page.locator('text=User approved').first()).toBeVisible();

      await page.goto(BASE + '/users/show/' + id);
      page.once('dialog', d => d.accept());
      await page.click('button:has-text("Nonaktifkan")');
      await expect(page.locator('button', { hasText: 'Aktifkan' })).toBeVisible();

      // Suspended cannot login
      await page.goto(BASE + '/logout');
      await loginExpectFail(page, email, 'password');

      // Reactivate
      await login(page, 'superadmin@ptbig.com');
      await page.goto(BASE + '/users/show/' + id);
      page.once('dialog', d => d.accept());
      await page.click('button:has-text("Aktifkan")');
      await expect(page.locator('button', { hasText: 'Nonaktifkan' })).toBeVisible();
      await page.goto(BASE + '/logout');
      await login(page, email, 'temp123456');
      await expect(page).toHaveURL(/.*\/dashboard/);
    } finally {
      cleanupUser(id);
    }
  });

  test('TEKNISI: /users/* ditolak 403/blocked, pending tidak bisa login, public register tidak bisa SUPERADMIN', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });

    // Teknisi blocked from user management (403 page rendered inline)
    await login(page, 'teknisia@ptbig.com');
    await page.goto(BASE + '/users');
    await expect(page.locator('h1', { hasText: '403' })).toBeVisible();
    await page.goto(BASE + '/users/show/1');
    await expect(page.locator('h1', { hasText: '403' })).toBeVisible();
    await page.goto(BASE + '/logout');

    // Pending user cannot login
    const { id, email } = seedPendingUser();
    try {
      await page.goto(BASE + '/login');
      await page.fill('input[name="email"]', email);
      await page.fill('input[name="password"]', 'temp123456');
      await page.click('button[type="submit"]');
      await expect(page).toHaveURL(/.*\/login/);
    } finally {
      cleanupUser(id);
    }

    // Public register cannot request SUPERADMIN
    await page.goto(BASE + '/register');
    await expect(page.locator('select[name="role"] option[value="SUPERADMIN"]')).toHaveCount(0);
  });
});

async function loginExpectFail(page, email, password) {
  await page.goto(BASE + '/login');
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/.*\/login/);
}
