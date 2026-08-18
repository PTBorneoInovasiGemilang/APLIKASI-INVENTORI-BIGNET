const { test, expect } = require('@playwright/test');
const path = require('path');
const BASE = 'http://localhost:8000';
const SEED = path.join(__dirname, 'helpers', 'user_seed.php');
const ROLE_STATE = path.join(__dirname, 'helpers', 'role_state.php');
const { execSync } = require('child_process');

const ts = Date.now();
const TEK_EMAIL = `teknisi_${ts}@ptbig.com`;
const ADMIN_EMAIL = `roleadmin_${ts}@ptbig.com`;
const TEK_NAME = 'Teknisi Role Test';
const ADMIN_NAME = 'Admin Role Test';

async function login(page, email, password) {
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

async function createUser(page, name, email, role) {
  await page.goto(BASE + '/users/create');
  await page.fill('input[name="name"]', name);
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="phone"]', '0812');
  await page.selectOption('select[name="role"]', role);
  await page.fill('input[name="password"]', 'rolepass123');
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/\/users$/);
}

function dbRole(email) {
  return execSync(`php "${ROLE_STATE}" ${email}`, { encoding: 'utf8' }).trim();
}

test('Role Display Label — TEKNISI_NOC => Teknisi/NOC (Test A-J)', async ({ page }) => {
  // Pre-clean: hapus residu run sebelumnya (test idempotent)
  execSync(`php "${SEED}" cleanup_role_test`, { encoding: 'utf8' });

  // Setup: buat user TEKNISI_NOC + ADMIN via workflow resmi (Superadmin -> Buat Pengguna)
  await login(page, 'superadmin@ptbig.com', 'superadmin123');

  // TEST A — Create User: dropdown menampilkan Teknisi/NOC (bukan TEKNISI NOC)
  await page.goto(BASE + '/users/create');
  const roleSelect = page.locator('select[name="role"]');
  const optionTexts = await roleSelect.locator('option').allTextContents();
  await expect(roleSelect.locator('option[value="TEKNISI_NOC"]')).toHaveText('Teknisi/NOC');
  await expect(roleSelect.locator('option[value="ADMIN"]')).toHaveText('Admin');
  await expect(roleSelect.locator('option[value="SUPERADMIN"]')).toHaveText('Superadmin');
  // TEST I (sebagian): halaman create tidak menampilkan label lama
  const createBody = await page.locator('body').innerText();
  expect(createBody).not.toContain('TEKNISI NOC');
  expect(createBody).not.toContain('TEKNISI_NOC');

  // TEST A (lanjut): buat user teknisi + admin
  await createUser(page, TEK_NAME, TEK_EMAIL, 'TEKNISI_NOC');
  await createUser(page, ADMIN_NAME, ADMIN_EMAIL, 'ADMIN');    // TEST F (sebagian): nilai internal DB tetap TEKNISI_NOC (format: id|role|account_status|is_active)
    const tekState = dbRole(TEK_EMAIL).split('|');
    expect(tekState[1]).toBe('TEKNISI_NOC');
    expect(tekState[2]).toBe('APPROVED');
    expect(tekState[3]).toBe('1');

  try {
    // TEST C — User List: badge menampilkan Teknisi/NOC
    await page.goto(BASE + '/users');
    const tekRow = page.locator(`tbody tr:has-text("${TEK_NAME}")`);
    await expect(tekRow).toHaveCount(1);
    await expect(tekRow).toContainText('Teknisi/NOC');
    const adminRow = page.locator(`tbody tr:has-text("${ADMIN_NAME}")`);
    await expect(adminRow).toContainText('Admin');
    const listBody = await page.locator('body').innerText();
    expect(listBody).not.toContain('TEKNISI NOC');
    expect(listBody).not.toContain('TEKNISI_NOC');

    // TEST E — Role Filter: aplikasi tidak memiliki filter role (hanya status).
    // Verifikasi search tetap menemukan user teknisi dan nilai internal tetap TEKNISI_NOC.
    await page.fill('input[placeholder*="Cari Nama"]', TEK_NAME);
    await page.click('button[type="submit"]');
    await expect(page.locator(`tbody tr:has-text("${TEK_NAME}")`)).toHaveCount(1);
    expect(dbRole(TEK_EMAIL).split('|')[1]).toBe('TEKNISI_NOC');
    await page.goto(BASE + '/users');

    // TEST B — Edit User: opsi terpilih menampilkan Teknisi/NOC
    const tekIdReal = dbRole(TEK_EMAIL).split('|')[0];
    await page.goto(BASE + '/users/edit/' + tekIdReal);
    const editSelect = page.locator('select[name="role"]');
    await expect(editSelect.locator('option:checked')).toHaveText('Teknisi/NOC');
    const editBody = await page.locator('body').innerText();
    expect(editBody).not.toContain('TEKNISI NOC');

    // TEST D — User Detail: badge Teknisi/NOC
    await page.goto(BASE + '/users/show/' + tekIdReal);
    const detailBody = await page.locator('body').innerText();
    expect(detailBody).toContain('Teknisi/NOC');
    expect(detailBody).not.toContain('TEKNISI NOC');
    expect(detailBody).not.toContain('TEKNISI_NOC');

    // TEST H — Audit/Activity: header (dropdown user) menampilkan label role Teknisi/NOC
    await page.goto(BASE + '/dashboard');
    await expect(page.locator('body')).toContainText('Teknisi/NOC');

    // TEST J — Semua Role konsisten: cek badge Admin di detail
    const adminIdReal = dbRole(ADMIN_EMAIL).split('|')[0];
    await page.goto(BASE + '/users/show/' + adminIdReal);
    await expect(page.locator('body')).toContainText('Admin');
    await expect(page.locator('body')).not.toContainText('Admin Gudang');

    // TEST G & F — Login Regression + Permission: teknisi tetap bisa login & aksesnya sama
    await page.goto(BASE + '/logout');
    await login(page, TEK_EMAIL, 'rolepass123');
    await expect(page.locator('body')).toContainText('Teknisi/NOC'); // header role label
    await page.goto(BASE + '/submissions');
    await expect(page).toHaveURL(/\/submissions/);
    await page.goto(BASE + '/users');
    // TEKNISI tetap 403 (permission tidak berubah)
    await expect(page).toHaveURL(/\/users/);
    const tekUsersBody = await page.locator('body').innerText();
    expect(tekUsersBody).toContain('403');
  } finally {
    // Cleanup user uji (FK-safe via helper)
    const tekIdC = dbRole(TEK_EMAIL).split('|')[0];
    const adminIdC = dbRole(ADMIN_EMAIL).split('|')[0];
    if (tekIdC && tekIdC !== '') execSync(`php "${SEED}" cleanup ${tekIdC}`, { encoding: 'utf8' });
    if (adminIdC && adminIdC !== '') execSync(`php "${SEED}" cleanup ${adminIdC}`, { encoding: 'utf8' });
  }
});

test('Register publik — opsi role Teknisi/NOC (Test A tambahan)', async ({ page }) => {
  await page.goto(BASE + '/register');
  const roleSelect = page.locator('select[name="role"]');
  await expect(roleSelect.locator('option[value="TEKNISI_NOC"]')).toHaveText('Teknisi/NOC');
  await expect(roleSelect.locator('option[value="ADMIN"]')).toHaveText('Admin');
  const body = await page.locator('body').innerText();
  expect(body).not.toContain('Teknisi NOC');
  expect(body).not.toContain('TEKNISI_NOC');
});
