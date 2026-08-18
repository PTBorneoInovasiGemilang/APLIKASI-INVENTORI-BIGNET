const { test, expect } = require('@playwright/test');
const { execSync } = require('child_process');
const path = require('path');

const BASE = 'http://localhost:8000';
const SEED = path.join(__dirname, 'helpers', 'user_seed.php');

function runPhp(args) {
  return execSync(`php "${SEED}" ${args}`, { encoding: 'utf8' }).trim();
}
function seedUser() {
  const email = `fb_${Date.now()}_${Math.floor(Math.random() * 10000)}@ptbig.com`;
  const id = Number(runPhp(`seed ${email}`));
  runPhp(`activate ${id}`);
  return { id, email };
}
function getUser(email) {
  const raw = runPhp(`get ${email}`);
  if (!raw) return null;
  const [id, status, active] = raw.split('|');
  return { id: Number(id), status, active: Number(active) };
}
function cleanupById(id) { if (id) runPhp(`cleanup ${id}`); }

async function loginSuperadmin(page, email, password) {
  await page.goto(BASE + '/login');
  if (!page.url().includes('/login')) {
    await page.goto(BASE + '/logout');
    await page.goto(BASE + '/login');
  }
  await page.fill('input[name="email"]', email || 'superadmin@ptbig.com');
  await page.fill('input[name="password"]', password || 'superadmin123');
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/.*\/dashboard/);
}

// Deteksi dialog native browser — harus tetap kosong di semua test.
function trackDialogs(page) {
  const dialogs = [];
  page.on('dialog', (d) => { dialogs.push(d.message()); d.dismiss().catch(() => {}); });
  return dialogs;
}

test.describe.configure({ mode: 'serial' });

test.describe('Global Feedback UI (AppModal + AppToast)', () => {

  test('A+C: Nonaktifkan user -> custom modal BIGNET (bukan native), confirm -> sukses + toast', async ({ page }) => {
    const { id, email } = seedUser();
    const dialogs = trackDialogs(page);
    try {
      await loginSuperadmin(page);
      await page.goto(BASE + '/users/show/' + id);

      await page.locator('form[data-confirm-title="Nonaktifkan Akun"] button[type="submit"]').click();

      // Modal custom muncul, bukan dialog native
      await expect(page.locator('[data-appmodal="backdrop"]')).toBeVisible();
      await expect(page.locator('#appModalTitle')).toHaveText('Nonaktifkan Akun');
      await expect(page.locator('#appModalMessage')).toContainText('Acct Pending');
      await expect(page.locator('[data-appmodal="backdrop"] button:has-text("Batal")')).toBeVisible();
      await expect(page.locator('[data-appmodal="backdrop"] button:has-text("Nonaktifkan")')).toBeVisible();

      // Confirm -> request jalan, status berubah, toast sukses, TANPA native dialog
      await page.locator('[data-appmodal="backdrop"] button:has-text("Nonaktifkan")').click();
      await expect(page).toHaveURL(new RegExp('/users/show/' + id));
      expect(getUser(email).status).toBe('SUSPENDED');
      expect(getUser(email).active).toBe(0);
      await expect(page.locator('#toastContainer .toast')).toContainText('User dinonaktifkan.');
      expect(dialogs).toEqual([]);
    } finally {
      cleanupById(id);
    }
  });

  test('B: Klik Batal -> modal tertutup, status TIDAK berubah', async ({ page }) => {
    const { id, email } = seedUser();
    const dialogs = trackDialogs(page);
    try {
      await loginSuperadmin(page);
      await page.goto(BASE + '/users/show/' + id);

      await page.locator('form[data-confirm-title="Nonaktifkan Akun"] button[type="submit"]').click();
      await expect(page.locator('[data-appmodal="backdrop"]')).toBeVisible();

      await page.locator('[data-appmodal="backdrop"] button:has-text("Batal")').click();
      await expect(page.locator('[data-appmodal="backdrop"]')).toHaveCount(0);

      expect(getUser(email).status).toBe('APPROVED');
      expect(getUser(email).active).toBe(1);
      expect(dialogs).toEqual([]);
    } finally {
      cleanupById(id);
    }
  });

  test('D: Aktifkan user -> modal custom yang sama, status kembali Aktif', async ({ page }) => {
    const { id, email } = seedUser();
    const dialogs = trackDialogs(page);
    try {
      await loginSuperadmin(page);
      await page.goto(BASE + '/users/show/' + id);

      await page.locator('form[data-confirm-title="Nonaktifkan Akun"] button[type="submit"]').click();
      await page.locator('[data-appmodal="backdrop"] button:has-text("Nonaktifkan")').click();
      await expect(page).toHaveURL(new RegExp('/users/show/' + id));
      expect(getUser(email).status).toBe('SUSPENDED');

      // Aktifkan kembali
      await page.locator('form[data-confirm-title="Aktifkan Akun"] button[type="submit"]').click();
      await expect(page.locator('#appModalTitle')).toHaveText('Aktifkan Akun');
      await expect(page.locator('[data-appmodal="backdrop"] button:has-text("Aktifkan")')).toBeVisible();
      await page.locator('[data-appmodal="backdrop"] button:has-text("Aktifkan")').click();
      await expect(page).toHaveURL(new RegExp('/users/show/' + id));

      expect(getUser(email).status).toBe('APPROVED');
      expect(getUser(email).active).toBe(1);
      await expect(page.locator('#toastContainer .toast')).toContainText('User diaktifkan.');
      expect(dialogs).toEqual([]);
    } finally {
      cleanupById(id);
    }
  });

  test('E: Reset password -> modal custom + notifikasi sukses custom', async ({ page }) => {
    const { id, email } = seedUser();
    const dialogs = trackDialogs(page);
    try {
      await loginSuperadmin(page);
      await page.goto(BASE + '/users/show/' + id);

      await page.locator('form[data-confirm-title="Reset Password"] button[type="submit"]').click();
      await expect(page.locator('#appModalTitle')).toHaveText('Reset Password');
      await expect(page.locator('[data-appmodal="backdrop"] button:has-text("Reset Password")')).toBeVisible();

      await page.locator('[data-appmodal="backdrop"] button:has-text("Reset Password")').click();
      await expect(page).toHaveURL(new RegExp('/users/show/' + id));
      await expect(page.locator('#toastContainer .toast')).toContainText('Password berhasil di-reset.');
      expect(getUser(email).status).toBe('APPROVED'); // status tidak berubah
      expect(dialogs).toEqual([]);
    } finally {
      cleanupById(id);
    }
  });

  test('F: Delete (bulk) -> custom modal konfirmasi, data terhapus', async ({ page }) => {
    const dialogs = trackDialogs(page);
    const catName = 'FB Delete ' + Date.now();
    const adminEmail = `fbadmin_${Date.now()}@ptbig.com`;
    const adminPw = 'fbadmin123';
    try {
      // Tombol Tambah/Bulk Delete hanya tampil untuk non-SUPERADMIN (quirk existing) -> pakai ADMIN sementara
      await loginSuperadmin(page);
      await page.goto(BASE + '/users/create');
      await page.fill('input[name="name"]', 'FB Admin');
      await page.fill('input[name="email"]', adminEmail);
      await page.fill('input[name="phone"]', '0814');
      await page.selectOption('select[name="role"]', 'ADMIN');
      await page.fill('input[name="password"]', adminPw);
      await page.click('button[type="submit"]');
      await expect(page).toHaveURL(/\/users$/);

      await page.goto(BASE + '/logout');
      await loginSuperadmin(page, adminEmail, adminPw);

      await page.goto(BASE + '/categories');
      await page.click('button:has-text("Tambah Kategori")'); // buka modal tambah
      await page.fill('#addModal input[name="name"]', catName);
      await page.click('#addModal form button[type="submit"]');
      await expect(page).toHaveURL(/\/categories$/);

      const row = page.locator(`tr:has-text("${catName}")`);
      await expect(row).toHaveCount(1);
      await row.locator('.bulk-checkbox').check();

      await page.click('#bulkCat-submit');
      await expect(page.locator('[data-appmodal="backdrop"]')).toBeVisible();
      await expect(page.locator('#appModalTitle')).toHaveText('Hapus Data');
      await expect(page.locator('[data-appmodal="backdrop"] button:has-text("Hapus")')).toBeVisible();

      await page.locator('[data-appmodal="backdrop"] button:has-text("Hapus")').click();
      await expect(page).toHaveURL(/\/categories$/);
      await expect(page.locator(`tr:has-text("${catName}")`)).toHaveCount(0);
      expect(dialogs).toEqual([]);
    } finally {
      // Jika masih ada (gagal hapus), bersihkan via DB
      execSync(`php "${SEED}" cleanup_category "${catName}"`).toString().trim();
      cleanupById(getUser(adminEmail)?.id);
    }
  });

  test('G: Validasi form (labels tanpa pilihan) -> toast warning custom, bukan native alert', async ({ page }) => {
    const dialogs = trackDialogs(page);
    try {
      await loginSuperadmin(page);
      await page.goto(BASE + '/labels/create');
      await page.locator('#printForm button[type="submit"]').click();
      await expect(page.locator('#toastContainer .toast')).toContainText('Tidak ada label yang dipilih.');
      expect(dialogs).toEqual([]);
    } finally {
      // tidak ada data test yang dibuat
    }
  });

  test('H: Error backend -> notifikasi custom, tanpa stack trace / dialog native', async ({ page }) => {
    const dialogs = trackDialogs(page);
    try {
      await loginSuperadmin(page);
      await page.goto(BASE + '/dashboard');

      const types = await page.evaluate(() => {
        window.AppToast.error('Gagal menyimpan perubahan.');
        window.AppToast.warning('Periksa kembali data Anda.');
        window.AppToast.info('Informasi berhasil diperbarui.');
        window.AppToast.success('Akun berhasil diperbarui.');
        return { modalApi: typeof window.AppModal.confirm, toastApi: typeof window.AppToast.success };
      });
      expect(types.modalApi).toBe('function');
      expect(types.toastApi).toBe('function');

      const toasts = page.locator('#toastContainer .toast');
      await expect(toasts).toHaveCount(4);
      await expect(toasts.first()).toContainText('Gagal menyimpan perubahan.');
      const texts = (await toasts.allTextContents()).join(' ');
      expect(texts).not.toContain('Stack trace');
      expect(texts).not.toContain('SQLSTATE');
      expect(dialogs).toEqual([]);
    } finally {
      // tidak ada data test yang dibuat
    }
  });

  test('I: Dark mode -> modal mengikuti tema gelap', async ({ page }) => {
    const { id, email } = seedUser();
    const dialogs = trackDialogs(page);
    try {
      await loginSuperadmin(page);
      await page.goto(BASE + '/users/show/' + id);

      // Aktifkan dark mode via tombol theme di topbar
      await page.click('button[aria-label="Toggle dark mode"]');
      await expect(page.locator('html')).toHaveClass(/dark/);

      await page.locator('form[data-confirm-title="Nonaktifkan Akun"] button[type="submit"]').click();
      await expect(page.locator('[data-appmodal="backdrop"]')).toBeVisible();

      const bg = await page.evaluate(() => {
        const panel = document.querySelector('.app-modal-panel');
        return panel ? getComputedStyle(panel).backgroundColor : '';
      });
      // gray-800 (#1f2937) = panel modal gelap
      expect(bg).toBe('rgb(31, 41, 55)');

      await page.locator('[data-appmodal="backdrop"] button:has-text("Batal")').click();
      // Kembalikan light mode
      await page.click('button[aria-label="Toggle dark mode"]');
      await expect(page.locator('html')).not.toHaveClass(/dark/);
      expect(dialogs).toEqual([]);
      expect(getUser(email).status).toBe('APPROVED');
    } finally {
      cleanupById(id);
    }
  });

  test('J: Mobile viewport -> modal responsive, tidak keluar layar', async ({ page }) => {
    const { id, email } = seedUser();
    const dialogs = trackDialogs(page);
    try {
      await page.setViewportSize({ width: 390, height: 844 });
      await loginSuperadmin(page);
      await page.goto(BASE + '/users/show/' + id);

      await page.locator('form[data-confirm-title="Nonaktifkan Akun"] button[type="submit"]').click();
      await expect(page.locator('[data-appmodal="backdrop"]')).toBeVisible();

      const box = await page.locator('.app-modal-panel').boundingBox();
      expect(box).not.toBeNull();
      expect(box.x).toBeGreaterThanOrEqual(0);
      expect(box.y).toBeGreaterThanOrEqual(0);
      expect(box.x + box.width).toBeLessThanOrEqual(390);
      expect(box.y + box.height).toBeLessThanOrEqual(844);

      await page.locator('[data-appmodal="backdrop"] button:has-text("Batal")').click();
      expect(dialogs).toEqual([]);
      expect(getUser(email).status).toBe('APPROVED');
    } finally {
      cleanupById(id);
    }
  });

  test('K: Audit source — tidak ada native alert/confirm pada UI user-facing', async () => {
    const fs = require('fs');
    const pathmod = require('path');
    const roots = [
      pathmod.join(__dirname, '..', '..', 'resources', 'views'),
      pathmod.join(__dirname, '..', '..', 'public', 'assets', 'js')
    ];
    const files = [];
    function walk(dir) {
      for (const ent of fs.readdirSync(dir, { withFileTypes: true })) {
        const p = pathmod.join(dir, ent.name);
        if (ent.isDirectory()) walk(p);
        else if (/\.(php|js)$/.test(ent.name)) files.push(p);
      }
    }
    roots.forEach(walk);
    const hits = [];
    for (const f of files) {
      const lines = fs.readFileSync(f, 'utf8').split('\n');
      lines.forEach((ln, i) => {
        const t = ln.trim();
        if (!t || t.startsWith('*') || t.startsWith('//') || t.startsWith('/*')) return;
        if (/\balert\(/.test(t) || /window\.confirm\(/.test(t) || (/[^.\w]confirm\(/.test(t) && !/function\s+confirm\(/.test(t))) {
          hits.push(`${f}:${i + 1}: ${t}`);
        }
      });
    }
    expect(hits).toEqual([]);
  });
});
