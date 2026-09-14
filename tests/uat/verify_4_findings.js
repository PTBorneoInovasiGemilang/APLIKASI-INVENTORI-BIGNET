const { chromium } = require('playwright');
const fs = require('fs');

const BASE_URL = 'https://detailed-antiques-daisy-upper.trycloudflare.com';
const EVIDENCE_DIR = 'C:\\xampp\\htdocs\\APLIKASI-INVENTORI-BIGNET-CLEAN\\tests\\evidence\\uat';

if (!fs.existsSync(EVIDENCE_DIR)) {
  fs.mkdirSync(EVIDENCE_DIR, { recursive: true });
}

(async () => {
  const browser = await chromium.launch({ headless: true });
  const results = [];

  // Helper to test a role + direct URL
  async function testRoleAccess(testId, email, password, targetPath, expectedBehavior) {
    const context = await browser.newContext();
    const page = await context.newPage();
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });

    console.log(`\n=== Running Test ${testId}: ${email} -> ${targetPath} ===`);
    
    // Step 1: Login
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle' });
    await page.fill('input[type=email], input[name=email]', email);
    await page.fill('input[type=password], input[name=password]', password);
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle' }),
      page.click('button[type=submit]')
    ]);

    const loginUrl = page.url();
    console.log(`Login final URL: ${loginUrl}`);

    // Step 2: Navigate directly to target URL
    const response = await page.goto(`${BASE_URL}${targetPath}`, { waitUntil: 'networkidle' });
    const statusCode = response ? response.status() : 0;
    const finalUrl = page.url();
    const isRedirect = finalUrl !== `${BASE_URL}${targetPath}`;
    const pageTitle = await page.title();
    const bodyText = await page.innerText('body');
    const screenshotPath = `${EVIDENCE_DIR}\\VERIFY_${testId}_${email.split('@')[0]}.png`;

    await page.screenshot({ path: screenshotPath, fullPage: true });

    console.log(`Target Status Code: ${statusCode}`);
    console.log(`Target Final URL: ${finalUrl}`);
    console.log(`Is Redirect: ${isRedirect}`);
    console.log(`Page Title: ${pageTitle}`);
    console.log(`Console Errors: ${consoleErrors.length}`);

    results.push({
      testId,
      email,
      targetPath,
      statusCode,
      finalUrl,
      isRedirect,
      pageTitle,
      bodyTextExcerpt: bodyText.substring(0, 300).replace(/\n/g, ' '),
      screenshotPath,
      consoleErrors
    });

    await context.close();
  }

  // 1. ADMIN -> /users
  await testRoleAccess('VERIFY-1', 'admin@ptbig.com', 'admin123', '/users', '403 / access denied');

  // 2. ADMIN -> /settings
  await testRoleAccess('VERIFY-2', 'admin@ptbig.com', 'admin123', '/settings', '403 / access denied');

  // 3. TEKNISI_NOC -> /audit
  await testRoleAccess('VERIFY-3', 'teknisi@ptbig.com', 'teknisi123', '/audit', '403 / access denied');

  // 4. TEKNISI_NOC -> /installations/create
  await testRoleAccess('VERIFY-4', 'teknisi@ptbig.com', 'teknisi123', '/installations/create', '200 + form rendered');

  await browser.close();

  fs.writeFileSync(
    `${EVIDENCE_DIR}\\verify_4_results.json`,
    JSON.stringify(results, null, 2)
  );
  console.log('\nAll 4 verification tests completed! Results written to verify_4_results.json.');
})();
