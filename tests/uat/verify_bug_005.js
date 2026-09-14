const { chromium } = require('playwright');
const fs = require('fs');

const BASE_URL = 'https://detailed-antiques-daisy-upper.trycloudflare.com';
const EVIDENCE_DIR = 'C:\\xampp\\htdocs\\APLIKASI-INVENTORI-BIGNET-CLEAN\\tests\\evidence\\uat';

if (!fs.existsSync(EVIDENCE_DIR)) {
  fs.mkdirSync(EVIDENCE_DIR, { recursive: true });
}

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext();
  const page = await context.newPage();

  console.log('=== VERIFICATION BUG-UAT-005 ===');

  // Step 1: Login as ADMIN
  await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle' });
  await page.fill('input[type=email], input[name=email]', 'admin@ptbig.com');
  await page.fill('input[type=password], input[name=password]', 'admin123');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle' }),
    page.click('button[type=submit]')
  ]);

  console.log(`LoggedIn URL: ${page.url()}`);

  // Step 2: Open /categories
  await page.goto(`${BASE_URL}/categories`, { waitUntil: 'networkidle' });
  console.log(`Categories Page URL: ${page.url()}`);

  // Screenshot before click
  await page.screenshot({ path: `${EVIDENCE_DIR}\\BUG005_BEFORE_CLICK.png`, fullPage: true });

  // Step 3: Locate "Tambah Kategori" button & record attributes
  const btn = page.locator('button:has-text("Tambah Kategori"), a:has-text("Tambah Kategori")').first();
  const tagName = await btn.evaluate(el => el.tagName.toLowerCase());
  const hrefAttr = await btn.getAttribute('href');
  const onclickAttr = await btn.getAttribute('onclick');

  console.log(`Button Tag Name: ${tagName}`);
  console.log(`Button Href Attr: ${hrefAttr}`);
  console.log(`Button Onclick Attr: ${onclickAttr}`);

  // Step 4: Click button
  await btn.click();
  await page.waitForTimeout(1000);

  const finalUrl = page.url();
  const modalVisible = await page.locator('#addModal').isVisible();

  console.log(`Final URL after click: ${finalUrl}`);
  console.log(`Is #addModal Visible: ${modalVisible}`);

  // Screenshot after click
  await page.screenshot({ path: `${EVIDENCE_DIR}\\BUG005_AFTER_CLICK.png`, fullPage: true });

  const result = {
    tagName,
    hrefAttr,
    onclickAttr,
    finalUrl,
    modalVisible,
    screenshotBefore: `${EVIDENCE_DIR}\\BUG005_BEFORE_CLICK.png`,
    screenshotAfter: `${EVIDENCE_DIR}\\BUG005_AFTER_CLICK.png`
  };

  fs.writeFileSync(
    `${EVIDENCE_DIR}\\bug005_results.json`,
    JSON.stringify(result, null, 2)
  );

  console.log('\nBUG-UAT-005 verification finished! Written to bug005_results.json.');
  await browser.close();
})();
