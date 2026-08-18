const { test, expect } = require('@playwright/test');
const { execSync } = require('child_process');
const path = require('path');

const BASE = 'http://localhost:8000';
const SEED = path.join(__dirname, 'helpers', 'user_seed.php');
const PHOTO = path.join(__dirname, '..', 'fixtures', 'avatar.png');

function runPhp(args) {
  return execSync(`php "${SEED}" ${args}`, { encoding: 'utf8' }).trim();
}
function getUser(email) {
  const raw = runPhp(`get ${email}`);
  if (!raw) return null;
  const [id, status, active, role, reason] = raw.split('|');
  return { id: Number(id), status, active: Number(active), role, reason };
}
function cleanupById(id) { if (id) runPhp(`cleanup ${id}`); }
function cleanupByEmail(email) { if (email) runPhp(`cleanup_by_email ${email}`); }

async function loginSuperadmin(page) {
  await page.goto(BASE + '/login');
  if (!page.url().includes('/login')) {
    await page.goto(BASE + '/logout');
    await page.goto(BASE + '/login');
  }
  await page.fill('input[name="email"]', 'superadmin@ptbig.com');
  await page.fill('input[name="password"]', 'superadmin123');
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/.*\/dashboard/);
}

async function loginUser(page, email, password) {
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

async function loginExpectFail(page, email, password) {
  await page.goto(BASE + '/login');
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/.*\/login/);
}

async function createUserViaUI(page, { name, email, password, role = 'TEKNISI_NOC' }) {
  await page.goto(BASE + '/users/create');
  await page.fill('input[name="name"]', name);
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="phone"]', '0812');
  await page.selectOption('select[name="role"]', role);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/\/users$/);
}

async function createActiveUserViaUI(page, email, password) {
  await loginSuperadmin(page);
  await createUserViaUI(page, { name: 'WF User', email, password, role: 'TEKNISI_NOC' });
  return getUser(email);
}

test.describe('User Management Workflow (Superadmin)', () => {

  test('A: Superadmin create user -> langsung Aktif (APPROVED+active), tanpa approval, bisa login', async ({ page }) => {
    const ts = Date.now();
    const email = `wfa_${ts}@ptbig.com`;
    const pw = 'wfa123456';
    await loginSuperadmin(page);
    await createUserViaUI(page, { name: 'WF User A', email, password: pw, role: 'ADMIN' });
    try {
      const u = getUser(email);
      expect(u).not.toBeNull();
      expect(u.status).toBe('APPROVED');
      expect(u.active).toBe(1);
      expect(u.role).toBe('ADMIN');

      // Di index: badge Aktif, TANPA tombol Tinjau (bukan public registration)
      const row = page.locator(`tr:has-text("${email}")`);
      await expect(row).toHaveCount(1);
      await expect(row.locator('button:has-text("Tinjau")')).toHaveCount(0);
      await expect(row.locator('span', { hasText: 'Aktif' }).first()).toBeVisible();

      // User baru bisa langsung login
      await page.goto(BASE + '/logout');
      await loginUser(page, email, pw);
      await page.goto(BASE + '/logout');
    } finally {
      cleanupByEmail(email);
    }
  });

  test('B: Superadmin ganti password -> status tetap Aktif, login pakai password baru berhasil', async ({ page }) => {
    const ts = Date.now();
    const email = `wfb_${ts}@ptbig.com`;
    const oldPw = 'wfb123456';
    const newPw = 'wfbNEW123';
    const before = await createActiveUserViaUI(page, email, oldPw);
    try {
      expect(before.status).toBe('APPROVED');
      expect(before.active).toBe(1);

      await page.goto(BASE + '/users/edit/' + before.id);
      await page.fill('input[name="password"]', newPw);
      await page.click('button[type="submit"]');
      await expect(page).toHaveURL(/\/users$/);

      const after = getUser(email);
      expect(after.status).toBe('APPROVED'); // TIDAK Nonaktif
      expect(after.active).toBe(1);

      await page.goto(BASE + '/logout');
      await loginUser(page, email, newPw);
      await page.goto(BASE + '/logout');
    } finally {
      cleanupByEmail(email);
    }
  });

  test('C: Superadmin ganti role -> status tetap Aktif', async ({ page }) => {
    const ts = Date.now();
    const email = `wfc_${ts}@ptbig.com`;
    const pw = 'wfc123456';
    const u = await createActiveUserViaUI(page, email, pw);
    try {
      expect(u.role).toBe('TEKNISI_NOC');
      await page.goto(BASE + '/users/edit/' + u.id);
      await page.selectOption('select[name="role"]', 'ADMIN');
      await page.click('button[type="submit"]');
      await expect(page).toHaveURL(/\/users$/);

      const after = getUser(email);
      expect(after.role).toBe('ADMIN');
      expect(after.status).toBe('APPROVED'); // TIDAK Nonaktif
      expect(after.active).toBe(1);
    } finally {
      cleanupByEmail(email);
    }
  });

  test('D: Superadmin ubah nama/email -> status tetap Aktif', async ({ page }) => {
    const ts = Date.now();
    const email = `wfd_${ts}@ptbig.com`;
    const pw = 'wfd123456';
    const newEmail = `wfd_new_${ts}@ptbig.com`;
    const u = await createActiveUserViaUI(page, email, pw);
    try {
      await page.goto(BASE + '/users/edit/' + u.id);
      await page.fill('input[name="name"]', 'WF User D Baru');
      await page.fill('input[name="email"]', newEmail);
      await page.click('button[type="submit"]');
      await expect(page).toHaveURL(/\/users$/);

      const after = getUser(newEmail);
      expect(after).not.toBeNull();
      expect(after.status).toBe('APPROVED'); // TIDAK Nonaktif
      expect(after.active).toBe(1);
    } finally {
      cleanupByEmail(email);
      cleanupByEmail(newEmail);
    }
  });

  test('E: Superadmin set status Nonaktif eksplisit -> SUSPENDED/inactive, login ditolak', async ({ page }) => {
    const ts = Date.now();
    const email = `wfe_${ts}@ptbig.com`;
    const pw = 'wfe123456';
    const u = await createActiveUserViaUI(page, email, pw);
    try {
      await page.goto(BASE + '/users/edit/' + u.id);
      await page.selectOption('select[name="status"]', '0');
      await page.click('button[type="submit"]');
      await expect(page).toHaveURL(/\/users$/);

      const after = getUser(email);
      expect(after.status).toBe('SUSPENDED');
      expect(after.active).toBe(0);

      await page.goto(BASE + '/logout');
      await loginExpectFail(page, email, pw);
    } finally {
      cleanupByEmail(email);
    }
  });

  test('F: Superadmin set status Aktif eksplisit -> APPROVED/active, login berhasil', async ({ page }) => {
    const ts = Date.now();
    const email = `wff_${ts}@ptbig.com`;
    const pw = 'wff123456';
    const u = await createActiveUserViaUI(page, email, pw);
    try {
      // Nonaktifkan dulu via edit
      await page.goto(BASE + '/users/edit/' + u.id);
      await page.selectOption('select[name="status"]', '0');
      await page.click('button[type="submit"]');
      await expect(page).toHaveURL(/\/users$/);
      expect(getUser(email).status).toBe('SUSPENDED');

      // Aktifkan kembali via edit
      await page.goto(BASE + '/users/edit/' + u.id);
      await page.selectOption('select[name="status"]', '1');
      await page.click('button[type="submit"]');
      await expect(page).toHaveURL(/\/users$/);

      const after = getUser(email);
      expect(after.status).toBe('APPROVED');
      expect(after.active).toBe(1);

      await page.goto(BASE + '/logout');
      await loginUser(page, email, pw);
      await page.goto(BASE + '/logout');
    } finally {
      cleanupByEmail(email);
    }
  });

  test('G: Public registration -> Menunggu Persetujuan (PENDING_APPROVAL), tidak bisa login', async ({ page }) => {
    const ts = Date.now();
    const email = `wfg_${ts}@ptbig.com`;
    const pw = 'wfg123456';
    await page.goto(BASE + '/register');
    await page.fill('input[name="name"]', 'WF Public G');
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="phone"]', '0813');
    await page.selectOption('select[name="role"]', 'TEKNISI_NOC');
    await page.setInputFiles('input[name="photo"]', PHOTO);
    await page.fill('input[name="password"]', pw);
    await page.fill('input[name="password_confirmation"]', pw);
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/.*\/login/); // sukses -> kembali ke login
    try {
      const u = getUser(email);
      expect(u).not.toBeNull();
      expect(u.status).toBe('PENDING_APPROVAL'); // tetap pending, bukan aktif
      expect(u.active).toBe(0);
      await loginExpectFail(page, email, pw);
    } finally {
      cleanupByEmail(email);
    }
  });

  test('H: Approve public registration -> Aktif, bisa login', async ({ page }) => {
    const ts = Date.now();
    const email = `wfh_${ts}@ptbig.com`;
    const id = Number(runPhp(`seed ${email}`));
    try {
      await loginSuperadmin(page);
      await page.goto(BASE + '/users/show/' + id);
      await expect(page.locator('button', { hasText: 'Setujui' })).toBeVisible();
      await page.click('button[type="submit"]:has-text("Setujui")');
      // Konfirmasi via modal custom global (AppModal) — standard UI 2026-08-17
      await expect(page.locator('[data-appmodal="backdrop"]')).toBeVisible();
      await page.locator('[data-appmodal="backdrop"] button:has-text("Setujui")').click();
      await expect(page).toHaveURL(new RegExp('/users/show/' + id));

      const u = getUser(email);
      expect(u.status).toBe('APPROVED');
      expect(u.active).toBe(1);

      await page.goto(BASE + '/logout');
      await loginUser(page, email, 'temp123456');
      await page.goto(BASE + '/logout');
    } finally {
      cleanupById(id);
    }
  });

  test('I: Reject public registration -> REJECTED + alasan tersimpan, login ditolak', async ({ page }) => {
    const ts = Date.now();
    const email = `wfi_${ts}@ptbig.com`;
    const id = Number(runPhp(`seed ${email}`));
    try {
      await loginSuperadmin(page);
      await page.goto(BASE + '/users/show/' + id);
      await page.fill('input[name="rejection_reason"]', 'Data tidak valid untuk uji workflow');
      await page.click('button:has-text("Tolak")');
      // Konfirmasi via modal custom global (AppModal) — standard UI 2026-08-17
      await expect(page.locator('[data-appmodal="backdrop"]')).toBeVisible();
      await page.locator('[data-appmodal="backdrop"] button:has-text("Tolak")').click();
      await expect(page).toHaveURL(new RegExp('/users/show/' + id));

      const u = getUser(email);
      expect(u.status).toBe('REJECTED');
      expect(u.active).toBe(0);
      expect(u.reason).toContain('Data tidak valid');

      await page.goto(BASE + '/logout');
      await loginExpectFail(page, email, 'temp123456');
    } finally {
      cleanupById(id);
    }
  });
});
