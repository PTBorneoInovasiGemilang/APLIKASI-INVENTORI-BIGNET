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
  await page.waitForURL('**/dashboard');
}

test.describe.configure({ mode: 'serial' });

test.describe('DEDICATED BROWSER UAT — SUBMISSION WAREHOUSE FULFILLMENT', () => {

  let submissionUrl = '';
  let submissionId = '';
  let selectedWarehouseId = '';
  let selectedWarehouseName = '';

  test('Teknisi makes a submission and selects warehouse per item', async ({ page }) => {
    await trackConsole(page);
    await login(page, TEKNISI);

    await page.goto(BASE + '/submissions/create');

    // Wait for the item card to be rendered
    await page.waitForSelector('.item-card');

    // Select the first available item
    const itemSelect = page.locator('.item-card select[name="item_ids[]"]').first();
    const itemOptions = await itemSelect.locator('option').all();
    let itemId = '';
    for (const opt of itemOptions) {
      const val = await opt.getAttribute('value');
      if (val) {
        itemId = val;
        break;
      }
    }
    expect(itemId).not.toBe('');
    await itemSelect.selectOption(itemId);

    // Select the first available warehouse
    const whSelect = page.locator('.item-card select[name="warehouse_ids[]"]').first();
    const whOptions = await whSelect.locator('option').all();
    for (const opt of whOptions) {
      const val = await opt.getAttribute('value');
      if (val) {
        selectedWarehouseId = val;
        selectedWarehouseName = await opt.textContent();
        break;
      }
    }
    expect(selectedWarehouseId).not.toBe('');
    await whSelect.selectOption(selectedWarehouseId);

    // Fill quantity
    await page.fill('.item-card input[name="quantities[]"]', '1');
    await page.fill('textarea[name="header_note"]', 'E2E Warehouse Fulfillment Request');

    // Submit
    await page.click('button:has-text("Kirim Pengajuan")');
    await page.waitForURL('**/submissions');

    // Go to the newly created submission
    await page.click('a[href*="/submissions/show"]:first-of-type');
    await page.waitForURL('**/submissions/show*');

    submissionUrl = page.url();
    const urlObj = new URL(submissionUrl);
    submissionId = urlObj.searchParams.get('id');
    expect(submissionId).not.toBeNull();

    // Verify UI shows the warehouse name
    await expect(page.locator('body')).toContainText(selectedWarehouseName);
  });

  test('Admin approves the submission scoped to warehouse', async ({ page }) => {
    await trackConsole(page);
    await login(page, ADMIN);
    await page.goto(submissionUrl);

    // Check Gudang Pemenuhan is displayed correctly
    await expect(page.locator('body')).toContainText(selectedWarehouseName);

    // Approve
    const approveQtyInput = page.locator('input[name^="approved_quantities["]').first();
    await approveQtyInput.fill('1');

    const prosesBtn = page.locator('button:has-text("Proses Item")').first();
    await prosesBtn.evaluate(node => node.removeAttribute('data-confirm'));
    await prosesBtn.click();
    
    // Check it is approved
    await expect(page.locator('body')).toContainText('APPROVED');
  });

  test('Admin creates outgoing and verifies warehouse mismatch rejection', async ({ page }) => {
    await trackConsole(page);
    await login(page, ADMIN);
    await page.goto(BASE + '/outgoing/create?submission_id=' + submissionId);

    // Wait for Gudang Asal to be preselected
    const whSelect = page.locator('select[name="warehouse_id"]');
    const preselectedValue = await whSelect.inputValue();
    
    // 1. Verify Outgoing automatically preselects the fulfillment warehouse
    expect(preselectedValue).toBe(selectedWarehouseId);

    // Find another warehouse to test mismatch
    const whOptions = await whSelect.locator('option').all();
    let mismatchWhId = '';
    for (const opt of whOptions) {
      const val = await opt.getAttribute('value');
      if (val && val !== selectedWarehouseId) {
        mismatchWhId = val;
        break;
      }
    }

    if (mismatchWhId) {
      // 2. Test warehouse mismatch rejection
      // Remove disabled attribute so we can change it for the test
      await whSelect.evaluate(node => node.removeAttribute('disabled'));
      await whSelect.selectOption(mismatchWhId);

      // Attempt to submit
      page.on('dialog', dialog => dialog.accept());
      await page.click('button:has-text("Proses Barang Keluar")');

      // Verify the backend rejected it (should show error and remain on create page or show flash)
      await expect(page.locator('body')).toContainText('Gudang asal tidak sesuai Gudang Pemenuhan pengajuan');
      
      // Go back to the correct form
      await page.goto(BASE + '/outgoing/create?submission_id=' + submissionId);
    }

    // 3. Normal path E2E
    // Submit normally
    page.on('dialog', dialog => dialog.accept());
    await page.click('button:has-text("Proses Barang Keluar")');

    await page.waitForURL('**/outgoing');

    // Verify 0 console errors during all tests
    expect(consoleErrors.length).toBe(0);
  });
});
