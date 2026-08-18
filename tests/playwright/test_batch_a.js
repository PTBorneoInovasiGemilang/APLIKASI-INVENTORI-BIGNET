const { chromium } = require('playwright');
const fs = require('fs');

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext();
  const page = await context.newPage();
  
  if (!fs.existsSync('screenshots/batch_a')) {
    fs.mkdirSync('screenshots/batch_a', { recursive: true });
  }

  let has500 = false;
  page.on('response', response => {
    if (response.status() >= 500) {
      has500 = true;
      console.error(`HTTP ${response.status()} on ${response.url()}`);
    }
  });

  page.on('console', msg => {
    if (msg.type() === 'error') {
      console.error(`Console Error: ${msg.text()}`);
    }
  });

  try {
    console.log("Logging in...");
    await page.goto('http://localhost:8000/login');
    // Using default admin credentials from earlier observation
    await page.fill('input[name="email"]', 'admin@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
    console.log("URL after login:", page.url());

    const testModule = async (name, path) => {
      console.log(`Testing Module: ${name} (${path})`);
      const url = `http://localhost:8000${path}`;
      
      // 1. Load module
      await page.goto(url);
      await page.waitForLoadState('networkidle');
      await page.screenshot({ path: `screenshots/batch_a/${name}_01_index.png` });
      if (has500) throw new Error(`HTTP 500 Detected on ${name}`);

      // 2. Test Search
      console.log(`  - Testing Search on ${name}`);
      await page.goto(`${url}?search=test_search_query_123`);
      await page.waitForLoadState('networkidle');
      await page.screenshot({ path: `screenshots/batch_a/${name}_02_search.png` });

      // 3. Test Reset
      console.log(`  - Testing Reset on ${name}`);
      const resetLink = await page.$('a:has-text("Reset")');
      if (resetLink) {
        await resetLink.click();
        await page.waitForLoadState('networkidle');
        await page.screenshot({ path: `screenshots/batch_a/${name}_03_reset.png` });
      } else {
        console.log(`  - WARNING: No reset link found on ${name}`);
      }
    };

    await testModule('Units', '/units');
    await testModule('Warehouses', '/warehouses');
    await testModule('Locations', '/locations');
    await testModule('Serials', '/serials');

    console.log("Batch A UI Test completed successfully without HTTP 500.");

  } catch (error) {
    console.error("Test failed:", error);
  } finally {
    await browser.close();
  }
})();
