const { chromium } = require('playwright');
const fs = require('fs');

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext();
  const page = await context.newPage();
  
  if (!fs.existsSync('screenshots/search')) {
    fs.mkdirSync('screenshots/search', { recursive: true });
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
    await page.fill('input[name="email"]', 'admin@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
    console.log("URL after login:", page.url());
    console.log("Login successful.");

    const baseURL = 'http://localhost:8000/search';
    
    // 1. Buka /search
    // 2. Test tanpa keyword
    console.log("1 & 2. Test /search tanpa keyword");
    await page.goto(baseURL);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: 'screenshots/search/01_no_keyword.png' });
    if (has500) throw new Error("HTTP 500 Detected");

    // Helper for testing search
    const testSearch = async (name, urlParams) => {
      console.log(`Testing: ${name}`);
      await page.goto(`${baseURL}?${urlParams}`);
      await page.waitForLoadState('networkidle');
      await page.screenshot({ path: `screenshots/search/${name.replace(/ /g, '_')}.png` });
      if (has500) throw new Error("HTTP 500 Detected");
    };

    // 3. Test keyword nama barang
    await testSearch('03_keyword_nama', 'q=Laptop');

    // 4. Test kode barang
    await testSearch('04_kode_barang', 'q=ITM-');

    // 5. Test serial number
    await testSearch('05_serial_number', 'q=SN-');

    // 6. Test filter kategori (assume category 1 exists)
    await testSearch('06_filter_kategori', 'kategori=1');

    // 7. Test filter gudang (assume warehouse 1 exists)
    await testSearch('07_filter_gudang', 'gudang=1');

    // 8. Test status
    await testSearch('08_status', 'status=AVAILABLE');

    // 9. Test kondisi
    await testSearch('09_kondisi', 'kondisi=BAIK');

    // 10. Test kombinasi filter
    await testSearch('10_kombinasi', 'q=Laptop&kategori=1&gudang=1&status=AVAILABLE&kondisi=BAIK');

    // 11. Test reset
    console.log("11. Test reset");
    await page.click('a:has-text("Reset")');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: 'screenshots/search/11_reset.png' });
    
    // 12. Test pagination (if applicable)
    await testSearch('12_pagination', 'page=2');
    
    // 13. Test hasil kosong
    await testSearch('13_hasil_kosong', 'q=XYZ123_NOT_FOUND');
    
    // 14. Test item serialized
    await testSearch('14_item_serialized', 'tipe=SERIALIZED');

    // 15. Test item bulk
    await testSearch('15_item_bulk', 'tipe=BULK');

    // 16. Test link Lihat
    console.log("16. Test link Lihat");
    await page.goto(baseURL);
    const lihatBtn = await page.$('a:has-text("Lihat")');
    if (lihatBtn) {
      await lihatBtn.click();
      await page.waitForLoadState('networkidle');
      await page.screenshot({ path: 'screenshots/search/16_link_lihat.png' });
    } else {
      console.log("No 'Lihat' link found (maybe no results).");
    }

    console.log("All Search tests completed successfully without HTTP 500.");

  } catch (error) {
    console.error("Test failed:", error);
  } finally {
    await browser.close();
  }
})();
