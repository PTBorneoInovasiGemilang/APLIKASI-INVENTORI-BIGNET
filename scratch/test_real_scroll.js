const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

(async () => {
    const scratchDir = path.join(__dirname);

    console.log('=== STARTING REAL USER INPUT SCROLL VERIFICATION ===');
    const browser = await chromium.launch({ headless: true });
    
    // 1. Desktop Test (1366x768)
    const page = await browser.newPage({ viewport: { width: 1366, height: 768 } });
    await page.goto('http://127.0.0.1:8080/login');
    await page.fill('input[name="email"]', 'admin@ptbig.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
    await page.waitForTimeout(500);

    // Initial state
    const scrollYInitial = await page.evaluate(() => window.scrollY);
    console.log('Initial window.scrollY:', scrollYInitial);
    await page.screenshot({ path: path.join(scratchDir, 'dashboard-before-scroll.png') });

    // Wheel 1: Wheel down 500px
    await page.mouse.move(500, 300);
    await page.mouse.wheel(0, 500);
    await page.waitForTimeout(300);
    const scrollYAfter1 = await page.evaluate(() => window.scrollY);
    console.log('After wheel (0, 500) window.scrollY:', scrollYAfter1);
    await page.screenshot({ path: path.join(scratchDir, 'dashboard-after-wheel.png') });

    // Wheel 2: Wheel down another 500px
    await page.mouse.wheel(0, 500);
    await page.waitForTimeout(300);
    const scrollYAfter2 = await page.evaluate(() => window.scrollY);
    console.log('After wheel 2 (0, 500) window.scrollY:', scrollYAfter2);
    await page.screenshot({ path: path.join(scratchDir, 'dashboard-after-wheel-2.png') });

    // Wheel 3: Wheel back UP 600px
    await page.mouse.wheel(0, -600);
    await page.waitForTimeout(300);
    const scrollYAfterUp = await page.evaluate(() => window.scrollY);
    console.log('After wheel UP (0, -600) window.scrollY:', scrollYAfterUp);
    await page.screenshot({ path: path.join(scratchDir, 'dashboard-after-wheel-up.png') });

    // Verify Sidebar Scroll Position
    const sidebarScrollTop = await page.evaluate(() => document.getElementById('sidebarScrollArea')?.scrollTop);
    console.log('Sidebar scrollTop on dashboard:', sidebarScrollTop);

    await page.close();

    // 2. Mobile Touch Swipe Test (375x812)
    const mobileContext = await browser.newContext({
        viewport: { width: 375, height: 812 },
        hasTouch: true,
        isMobile: true
    });
    const mobilePage = await mobileContext.newPage();
    await mobilePage.goto('http://127.0.0.1:8080/login');
    await mobilePage.fill('input[name="email"]', 'admin@ptbig.com');
    await mobilePage.fill('input[name="password"]', 'admin123');
    await mobilePage.click('button[type="submit"]');
    await mobilePage.waitForURL('**/dashboard');
    await mobilePage.waitForTimeout(500);

    await mobilePage.screenshot({ path: path.join(scratchDir, 'mobile-dashboard-before.png') });

    // Simulate real touch swipe up (drag from y:500 up to y:100)
    await mobilePage.mouse.move(187, 500);
    await mobilePage.mouse.down();
    await mobilePage.mouse.move(187, 100, { steps: 15 });
    await mobilePage.mouse.up();
    await mobilePage.waitForTimeout(300);

    const mobileScrollY = await mobilePage.evaluate(() => window.scrollY);
    console.log('Mobile window.scrollY after touch swipe up:', mobileScrollY);
    await mobilePage.screenshot({ path: path.join(scratchDir, 'mobile-dashboard-after-swipe.png') });

    await browser.close();
    console.log('=== REAL USER INPUT SCROLL VERIFICATION COMPLETED ===');
})();
