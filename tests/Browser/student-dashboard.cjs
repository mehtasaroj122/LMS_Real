// Render real Laravel fixtures first (SQLite in memory):
// $env:STUDENT_DASHBOARD_RENDER_DIR = 'storage/framework/testing/student-dashboard'
// php artisan test tests/Feature/StudentDashboardPresentationTest.php
// node tests/Browser/student-dashboard.cjs
const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const output = path.resolve('storage/framework/testing/student-dashboard');
const publicRoot = path.resolve('public');
const types = { '.js': 'text/javascript', '.css': 'text/css', '.json': 'application/json', '.png': 'image/png', '.jpg': 'image/jpeg', '.svg': 'image/svg+xml', '.woff2': 'font/woff2', '.ttf': 'font/ttf' };
const installedChromium = () => {
    const root = process.env.LOCALAPPDATA && path.join(process.env.LOCALAPPDATA, 'ms-playwright');
    if (!root || !fs.existsSync(root)) return undefined;
    return fs.readdirSync(root).filter(name => /^chromium_headless_shell-\d+$/.test(name)).sort().reverse()
        .map(name => path.join(root, name, 'chrome-headless-shell-win64', 'chrome-headless-shell.exe')).find(fs.existsSync);
};
const readFixture = name => fs.readFileSync(path.join(output, `${name}.html`), 'utf8');
const populated = readFixture('populated');
let fixture = 'populated';
let notifications = JSON.parse(populated.match(/notifications: \{\s+data: (\[[^\n]*\])/)[1]);
let deletionRequest;
let deleteShouldFail = false;
let finishDelete;

(async () => {
    const browser = await chromium.launch({ headless: true, executablePath: installedChromium() });
    let context;
    let page;
    try {
        context = await browser.newContext({ viewport: { width: 1440, height: 1100 }, serviceWorkers: 'block' });
        page = await context.newPage();
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        await page.route('**/*', async route => {
            const request = route.request();
            const url = new URL(request.url());
            if (!['localhost', '127.0.0.1'].includes(url.hostname)) return route.continue();
            if (url.pathname === '/student/dashboard') return route.fulfill({ body: readFixture(fixture), contentType: 'text/html' });
            if (url.pathname === '/student/notifications/unread-count') return route.fulfill({ json: { success: true, unread_count: notifications.filter(item => item.is_unread).length } });
            if (url.pathname === '/student/notifications') return route.fulfill({ json: {
                success: true, unread_count: notifications.filter(item => item.is_unread).length,
                data: notifications.map(item => ({ ...item, created_at: '2026-10-07T08:59:00+05:45', read_at: item.is_unread ? null : '2026-10-07T08:00:00+05:45' })),
            } });
            if (request.method() === 'DELETE' && /^\/student\/notifications\/\d+$/.test(url.pathname)) {
                deletionRequest = { url: url.pathname, headers: request.headers(), method: request.method() };
                await new Promise(resolve => {
                    const timeout = setTimeout(resolve, 5000);
                    finishDelete = () => { clearTimeout(timeout); resolve(); };
                });
                if (deleteShouldFail) return route.fulfill({ status: 500, json: { success: false, message: 'Test failure' } });
                notifications = notifications.filter(item => item.id !== Number(url.pathname.split('/').at(-1)));
                return route.fulfill({ json: { success: true } });
            }
            const filename = path.resolve(publicRoot, '.' + decodeURIComponent(url.pathname));
            if (filename.startsWith(publicRoot + path.sep) && fs.existsSync(filename) && fs.statSync(filename).isFile()) {
                return route.fulfill({ body: fs.readFileSync(filename), contentType: types[path.extname(filename)] || 'application/octet-stream' });
            }
            return route.fulfill({ json: { success: true, data: [] } });
        });
        await page.goto('http://localhost:8000/student/dashboard', { waitUntil: 'networkidle' });
        await page.waitForFunction(() => typeof Chart !== 'undefined' && Chart.getChart('activityChart') && document.querySelectorAll('#issuedBooksList .data-item').length === 7);
        assert.equal(await page.locator('#notificationsList .notification-card').count(), 6);
        assert.equal(await page.locator('.snapshot-card').count(), 6);
        assert.equal(await page.locator('a.snapshot-card').count(), 4);
        assert.equal(await page.locator('.snapshot-card').filter({ hasText: 'Due Soon' }).locator('.snapshot-value').innerText(), '2');
        assert.equal(await page.locator('.snapshot-card').filter({ hasText: 'Remaining Slots' }).locator('.snapshot-value').innerText(), '6');
        assert.equal(await page.getByText('Student Portal / Overview', { exact: true }).count(), 0);
        assert.equal(await page.locator('.action-btn').filter({ hasText: 'Pay Fines' }).locator('.quick-action-currency-mark').innerText(), 'रु');
        assert.match(await page.locator('.student-id-card').innerText(), /CS-2023-001[\s\S]*Semester 2[\s\S]*Batch 2023/);
        assert.equal(await page.locator('.id-card-initials').innerText(), 'SM');
        assert.equal(await page.locator('.id-card-photo img').count(), 0);
        assert.equal(await page.locator('.charts-row .chart-card').count(), 2);
        assert.equal(await page.locator('.tables-row .data-card').count(), 3);
        assert.equal(await page.locator('.dashboard-bottom-row').count(), 1);
        assert.equal(await page.locator('.important-alerts').count(), 0);
        assert.equal(await page.locator('.profile-card').count(), 0);
        assert.equal(await page.evaluate(() => dashboardListStates.notifications.batchSize), 10);
        assert.equal(await page.evaluate(() => dashboardListStates.issuedBooks.batchSize), 10);
        assert.equal(await page.evaluate(() => Chart.getChart('requestStatusChart').options.cutout), '65%');

        for (const width of [1920, 1440, 1280, 1279, 1024, 1023, 960, 900, 899, 768, 640, 480, 479, 390, 320]) {
            await page.setViewportSize({ width, height: 1100 });
            await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
            const layout = await page.locator('.dashboard-identity-section').evaluate(root => {
                const bounds = root.getBoundingClientRect();
                const parent = root.parentElement;
                const styles = getComputedStyle(parent);
                return {
                    fullWidth: Math.abs(bounds.width - (parent.clientWidth - parseFloat(styles.paddingLeft) - parseFloat(styles.paddingRight))) < 1,
                    overflow: [...root.querySelectorAll('*')].filter(el => {
                        const rect = el.getBoundingClientRect();
                        return rect.width > 0 && (rect.right > bounds.right + 1 || rect.left < bounds.left - 1) && !el.classList.contains('sr-only');
                    }).map(el => ({ tag: el.tagName, class: el.className })),
                };
            });
            assert.equal(layout.fullWidth, true, `Top section must use full available width at ${width}px`);
            assert.deepEqual(layout.overflow, [], `Identity section overflow at ${width}px`);
            const snapshotLayout = await page.locator('.snapshot-card').evaluateAll(cards => cards.every(card => {
                const labelNode = card.querySelector('.snapshot-label');
                const label = labelNode.getBoundingClientRect();
                const value = card.querySelector('.snapshot-value').getBoundingClientRect();
                const caption = card.querySelector('.snapshot-caption').getBoundingClientRect();
                const icon = card.querySelector('.icon-tile').getBoundingClientRect();
                const bounds = card.getBoundingClientRect();
                const style = getComputedStyle(card);
                return labelNode.scrollWidth <= labelNode.clientWidth + 1
                    && label.bottom <= value.top && value.bottom <= caption.top
                    && Math.abs(icon.top - bounds.top - parseFloat(style.paddingTop) - 1) < 1
                    && Math.abs(bounds.right - icon.right - parseFloat(style.paddingRight) - 1) < 1;
            }));
            assert.equal(snapshotLayout, true, `Snapshot labels precede live values and captions, with top-right icons at ${width}px`);
            if (width <= 390) assert.equal(await page.locator('.id-card-person').evaluate(el => getComputedStyle(el).flexDirection), 'column');
            if ([1920, 1440, 1280, 960, 480, 390, 320].includes(width)) {
                await page.locator('.pwa-shell-content').evaluate(el => { el.scrollTo({ top: 0, behavior: 'instant' }); });
                await page.screenshot({ path: path.join(output, `dashboard-${width}.png`) });
            }
        }
        await page.setViewportSize({ width: 1440, height: 1100 });
        const firstStat = page.locator('a.snapshot-card').first();
        await firstStat.focus();
        assert.equal(await firstStat.evaluate(el => getComputedStyle(el).outlineStyle), 'solid');
        await firstStat.hover();
        assert.equal(await firstStat.evaluate(el => getComputedStyle(el).cursor), 'pointer');

        // The lower dashboard's committed DELETE behavior remains functional.
        const deleteButton = page.locator('.dashboard-notification-delete-btn').first();
        const deletedId = await deleteButton.getAttribute('data-notification-delete');
        const badgeBefore = Number(await page.locator('#notificationBadge').innerText());
        await deleteButton.click();
        await page.waitForFunction(() => document.querySelector('.dashboard-notification-delete-btn:disabled'));
        assert.equal(deletionRequest.method, 'DELETE');
        assert.equal(deletionRequest.url, `/student/notifications/${deletedId}`);
        assert.ok(deletionRequest.headers['x-csrf-token']);
        finishDelete();
        await page.waitForFunction(() => document.querySelectorAll('#notificationsList .notification-card').length === 5);
        assert.equal(await page.locator('#notificationsCount').innerText(), '5');
        assert.equal(Number(await page.locator('#notificationBadge').innerText()), badgeBefore - 1);

        await page.locator('#themeToggle').click();
        await page.waitForFunction(() => document.body.classList.contains('dark-theme') && getComputedStyle(document.querySelector('.snapshot-card')).backgroundColor === 'rgb(17, 24, 39)');
        await page.locator('.pwa-shell-content').evaluate(el => { el.scrollTo({ top: 0, behavior: 'instant' }); });
        await page.screenshot({ path: path.join(output, 'dashboard-dark.png') });

        fixture = 'empty';
        await page.goto('http://localhost:8000/student/dashboard', { waitUntil: 'networkidle' });
        assert.equal(await page.locator('.important-alerts').count(), 0);
        assert.match(await page.locator('#dueSoonList').innerText(), /No books due soon\. Great job!/);
        assert.match(await page.locator('#issuedBooksList').innerText(), /No books currently issued/);
        assert.match(await page.locator('#notificationsList').innerText(), /No notifications yet/);
        assert.equal(await page.locator('.snapshot-value').first().innerText(), '0');
        assert.equal(await page.locator('.snapshot-card').filter({ hasText: 'Due Soon' }).locator('.snapshot-value').innerText(), '0');

        fixture = 'restricted';
        await page.goto('http://localhost:8000/student/dashboard', { waitUntil: 'networkidle' });
        assert.match(await page.locator('.id-card-details').innerText(), /Borrowing Permission\s+Restricted/);
        assert.match(await page.locator('.snapshot-card').filter({ hasText: 'Remaining Slots' }).innerText(), /0\s+Borrowing restricted/);
        assert.deepEqual(errors, []);
        console.log('PASS: six compact snapshot cards show live data and respect borrowing restrictions at fifteen viewport sizes (320–1920px), with top-right icons, keyboard focus and dark theme; original charts, list sizes, empty states and notification DELETE remain functional.');
    } catch (error) {
        console.error(error);
        throw error;
    } finally {
        finishDelete?.();
        await page?.unrouteAll({ behavior: 'ignoreErrors' });
        await context?.close();
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
