const { test, expect } = require('@playwright/test');
const path = require('path');

async function login(page, email) {
  await page.goto('http://localhost:8000/login');
  if (!page.url().includes('/login')) {
    await page.goto('http://localhost:8000/logout');
    await page.goto('http://localhost:8000/login');
  }
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/.*\/dashboard/);
}

test.describe('Incidents (Damage/Lost) E2E — role-aware', () => {

  test('Teknisi melapor, Admin memproses; Superadmin read-only', async ({ page }) => {
    // ===== Phase 1: TEKNISI membuat laporan insiden (pelapor wajib) =====
    await login(page, 'teknisia@ptbig.com');

    await page.goto('http://localhost:8000/incidents');
    await expect(page.locator('h1').last()).toContainText('Laporan Kerusakan & Kehilangan');

    await page.click('text=Buat Laporan');
    await expect(page).toHaveURL(/.*\/incidents\/create/);

    await page.selectOption('select[name="incident_type"]', 'DAMAGE');
    await page.selectOption('select[name="warehouse_id"]', { index: 1 });
    await page.fill('textarea[name="notes"]', 'E2E Testing Damage');

    await page.selectOption('select.item-select', { index: 1 });
    const isReadonly = await page.evaluate(() => document.querySelector('input.qty-input').readOnly);
    if (!isReadonly) {
        await page.fill('input.qty-input', '2');
    }
    await page.fill('input[name="items[0][last_holder_name]"]', 'John Doe E2E');
    await page.fill('textarea[name="items[0][reason]"]', 'Barang jatuh saat diangkut');

    const fs = require('fs');
    const dummyPath = path.join(__dirname, 'dummy_evidence.png');
    if (!fs.existsSync(dummyPath)) {
        fs.writeFileSync(dummyPath, 'dummy image content');
    }
    await page.setInputFiles('input[type="file"]', dummyPath);

    await page.click('button[id="submitBtn"]');
    await expect(page).toHaveURL(/.*\/incidents\/show\?id=\d+/);
    await expect(page.locator('.bg-green-50')).toContainText('berhasil dibuat');

    // Teknisi TIDAK melihat form proses (hanya Admin)
    await expect(page.locator('text=Proses Investigasi')).toHaveCount(0);

    // Simpan URL show sebelum re-login
    const showUrl = page.url();

    // ===== Phase 2: ADMIN memproses insiden =====
    await login(page, 'admin@ptbig.com');
    await page.goto(showUrl);
    await expect(page.locator('text=Proses Investigasi')).toBeVisible();

    await page.selectOption('select[name="action"]', 'RESOLVED');
    await page.fill('textarea[name="resolution"]', 'Disetujui. Potong stok E2E.');
    page.on('dialog', dialog => dialog.accept());
    await page.click('button:has-text("Simpan Keputusan")');

    await expect(page).toHaveURL(/.*\/incidents\/show\?id=\d+/);
    await expect(page.locator('.bg-green-50')).toContainText('berhasil diupdate');
  });

  test('Superadmin: hanya bisa melihat, tidak bisa create/process', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com');

    // Index (monitoring) tampil
    await page.goto('http://localhost:8000/incidents');
    await expect(page.locator('h1').last()).toContainText('Laporan Kerusakan & Kehilangan');
    // Tidak ada tombol create
    await expect(page.getByRole('link', { name: /Buat Laporan/ })).toHaveCount(0);
    // Direct route create ditolak
    const resp = await page.goto('http://localhost:8000/incidents/create');
    expect(resp.status()).toBe(403);
  });

});
