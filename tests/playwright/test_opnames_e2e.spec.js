const { test, expect } = require('@playwright/test');

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

test.describe('Stock Opname E2E — role-aware', () => {

  test('Admin membuat & mengisi opname, Superadmin menyetujui variance (locked approval)', async ({ page }) => {
    // ===== Phase 1: ADMIN membuat stock opname =====
    await login(page, 'admin@ptbig.com');

    await page.goto('http://localhost:8000/opnames');
    await expect(page.locator('h1').last()).toContainText('Stock Opname');

    await page.click('text=Buat Stock Opname');
    await expect(page).toHaveURL(/.*\/opnames\/create/);

    await page.selectOption('select[name="warehouse_id"]', { index: 1 });
    await page.fill('textarea[name="notes"]', 'E2E Testing Stock Opname');

    const rowCount = await page.locator('select.item-select').count();
    if (rowCount === 0) {
        await page.click('#addItemBtn');
    }
    await page.evaluate(() => {
        const select = document.querySelector('select.item-select');
        if (select) {
            if (select.options.length <= 1) {
                const opt = document.createElement('option');
                opt.value = '1';
                opt.text = 'Dummy Item';
                opt.setAttribute('data-qty', '10');
                select.appendChild(opt);
            }
            select.selectedIndex = 1;
            select.dispatchEvent(new Event('change'));
        }
    });
    const sysQty = await page.inputValue('input.sys-qty');
    const physQty = (parseInt(sysQty) || 0) + 5;
    await page.fill('input.phys-qty', physQty.toString());
    await page.fill('input.reason', 'Barang susut di gudang');

    await page.click('button[id="submitBtn"]');

    await page.waitForTimeout(1000);
    const errorAlert = await page.locator('.bg-red-50').count();
    if (errorAlert > 0) {
        console.log("SERVER ERROR: ", await page.locator('.bg-red-50').textContent());
    }
    await expect(page).toHaveURL(/.*\/opnames\/show\?id=\d+/);
    await expect(page.locator('.bg-green-50')).toContainText('berhasil dibuat');

    // Admin TIDAK melihat form keputusan review (hanya Superadmin)
    await expect(page.locator('text=Keputusan Review')).toHaveCount(0);

    // Simpan URL show sebelum re-login
    const showUrl = page.url();

    // ===== Phase 2: SUPERADMIN menyetujui variance (D-014 locked approval) =====
    await login(page, 'superadmin@ptbig.com');
    await page.goto(showUrl);

    await expect(page.locator('text=Keputusan Review')).toBeVisible();
    await page.selectOption('select[name="action"]', 'APPROVED');
    await page.fill('textarea[name="resolution_notes"]', 'Setuju. Lakukan penyesuaian otomatis.');
    page.on('dialog', dialog => dialog.accept());
    await page.click('button:has-text("Simpan Keputusan")');

    await page.waitForTimeout(1000);
    const errorAlertProcess = await page.locator('.bg-red-50').count();
    if (errorAlertProcess > 0) {
        console.log("SERVER ERROR ON PROCESS: ", await page.locator('.bg-red-50').textContent());
    }
    await expect(page).toHaveURL(/.*\/opnames\/show\?id=\d+/);
    await expect(page.locator('.bg-green-50')).toContainText('berhasil diupdate');
  });

  test('Superadmin: bisa monitoring, tidak bisa membuat opname', async ({ page }) => {
    await login(page, 'superadmin@ptbig.com');

    await page.goto('http://localhost:8000/opnames');
    await expect(page.locator('h1').last()).toContainText('Stock Opname');
    await expect(page.getByRole('link', { name: /Buat Stock Opname/ })).toHaveCount(0);

    const resp = await page.goto('http://localhost:8000/opnames/create');
    expect(resp.status()).toBe(403);
  });

});
