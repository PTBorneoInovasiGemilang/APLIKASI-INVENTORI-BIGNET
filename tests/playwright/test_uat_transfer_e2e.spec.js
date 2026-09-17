const { test, expect } = require('@playwright/test');

const BASE = 'http://localhost:8001';
const ADMIN = { email: 'admin@ptbig.com', password: 'admin123' };
const TEKNISI = { email: 'teknisi@ptbig.com', password: 'teknisi123' };
const consoleErrors = [];

async function trackConsole(page) {
  page.on('console', msg => {
    const text = msg.text();
    if (msg.type() === 'error' && !/favicon/i.test(text) && !text.includes('status of 403') && !text.includes('status of 404')) {
      consoleErrors.push(`[${msg.type()}] ${text}`);
    }
  });
  page.on('pageerror', err => consoleErrors.push(`[pageerror] ${err.message}`));
}

async function login(page, user) {
  await page.goto(BASE + '/login');
  if (!page.url().includes('/login')) {
    await page.goto(BASE + '/logout');
    await page.goto(BASE + '/login');
  }
  await page.fill('input[name="email"]', user.email);
  await page.fill('input[name="password"]', user.password);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(BASE + '/dashboard');
}

test.describe('FINAL UAT: TRANSFER ANTAR GUDANG', () => {

  test('UAT-TRANSFER-1: Negative Cases & Dispatch', async ({ page }) => {
    await trackConsole(page);
    await login(page, ADMIN);

    // Buka create transfer
    await page.goto(BASE + '/transfers/create');
    await expect(page.locator('select#source_warehouse_id')).toBeVisible();

    const whOptions = await page.locator('select#source_warehouse_id option').allTextContents();
    const validWH = whOptions.filter(t => t && !t.includes('Pilih'));
    if (validWH.length < 2) {
      throw new Error(`Test requires at least 2 warehouses. Found: ${validWH.length}`);
    }

    const sourceWHName = validWH[0];
    const destWHName = validWH[1];

    const sourceWHId = await page.locator(`select#source_warehouse_id option:has-text("${sourceWHName}")`).getAttribute('value');
    const destWHId = await page.locator(`select#destination_warehouse_id option:has-text("${destWHName}")`).getAttribute('value');

    // Negative: Source = Destination
    await page.selectOption('select#source_warehouse_id', sourceWHId);
    
    // Check that the same warehouse option is disabled
    const isSourceDisabledInDest = await page.locator(`select#destination_warehouse_id option[value="${sourceWHId}"]`).isDisabled();
    expect(isSourceDisabledInDest).toBeTruthy();
    
    // Fill required info
    await page.fill('input[name="driver_name"]', 'Budi Driver');
    
    // We will do valid Dispatch now.
    await page.selectOption('select#destination_warehouse_id', destWHId);

    // Add Item
    await page.click('button:has-text("Tambah Barang")');

    // Submit
    await page.setInputFiles('input[name="dispatch_photo"]', 'tests/playwright/fake.jpg');

    // Find a non-serialized item if possible, or serialized.
    // Let's just pick the first available item.
    const itemSelect = page.locator('select[name^="item_ids["]').first();
    const isSerialized = await itemSelect.evaluate(el => el.options[el.selectedIndex].getAttribute('data-serialized'));
    
    if (isSerialized === '1') {
      // Open Serial modal and pick SN
      await page.click('button.sn-button');
      await page.waitForTimeout(500);
      // Wait for the modal checkboxes
      const checkboxes = page.locator('.sn-checkbox:not(:disabled)');
      const count = await checkboxes.count();
      if (count < 1) {
        throw new Error('No SN available for transfer.');
      }
      await checkboxes.first().check();
      await page.click('button:has-text("Simpan Pilihan")');
    } else {
      await page.fill('input[name^="quantities["]', '1');
    }

    // Submit
    // Take a screenshot to debug
    await page.screenshot({ path: 'test-results/debug-submit.png', fullPage: true });

    await page.click('button:has-text("Simpan Transfer")');

    // Wait a bit to see if there's an alert or validation error
    await page.waitForTimeout(1000);

    // Should redirect to /transfers
    if (page.url().includes('/transfers/create')) {
        const phpErrors = await page.locator('.bg-red-50').allTextContents();
        console.error('Submit failed. PHP Errors:', phpErrors);
    }
    await expect(page).toHaveURL(BASE + '/transfers', { timeout: 15000 });

    // Navigate to the newly created transfer (first row in the table)
    const detailLink = page.locator('table tbody tr:first-child a:has-text("Detail")');
    const href = await detailLink.getAttribute('href');
    const transferId = new URL(href, BASE).searchParams.get('id');

    await page.goto(BASE + '/transfers/show?id=' + transferId);
    // Check source and destination warehouses are displayed
    await expect(page.locator('body')).toContainText(sourceWHName);
    await expect(page.locator('body')).toContainText(destWHName);
    await expect(page.locator('body')).toContainText('Budi Driver');
    
    await page.fill('input[name="receiver_name"]', 'Budi Penerima');
    
    // Add photo
    await page.setInputFiles('input[name="receive_photo"]', 'tests/playwright/fake.jpg');

    // Handle serialized auto receive if present
    const autoReceiveBtns = page.locator('button:has-text("Pilih Otomatis")');
    const autoCount = await autoReceiveBtns.count();
    for(let i=0; i<autoCount; i++) {
        await autoReceiveBtns.nth(i).click();
    }

    // Submit Receive
    await page.click('button:has-text("Simpan Penerimaan")');
    
    // Verify status is completed (redirects back to /transfers/show)
    await expect(page).toHaveURL(new RegExp(BASE + '/transfers/show\\?id=' + transferId));
    // And it should not show the receive form anymore
    await expect(page.locator('button:has-text("Simpan Penerimaan")')).toHaveCount(0);
    
    // Check stock ledger or DB? "DB SELECT hanya untuk verifikasi akhir" in PowerShell.
    // Let's check report transfers
    await page.goto(BASE + '/reports/transfers');
    await expect(page.locator('table')).toContainText(sourceWHName);
    await expect(page.locator('table')).toContainText(destWHName);

    expect(consoleErrors).toHaveLength(0);
  });

});
