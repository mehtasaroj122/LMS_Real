// Render real Laravel pages against SQLite in memory first:
// $env:STUDENT_PORTAL_RENDER_DIR = 'storage/framework/testing/student-portal'
// php artisan test tests/Feature/StudentPortalDesignSystemTest.php
// node tests/Browser/student-ui-design-system.cjs
const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const output = path.resolve('storage/framework/testing/student-portal');
const publicRoot = path.resolve('public');
const routes = { '/student/dashboard': 'dashboard', '/student/search': 'search', '/student/my-books': 'my-books', '/student/my-requests': 'requests', '/student/my-fines': 'fines', '/student/profile': 'profile' };
const types = { '.js': 'text/javascript', '.css': 'text/css', '.json': 'application/json', '.svg': 'image/svg+xml', '.woff2': 'font/woff2', '.ttf': 'font/ttf', '.png': 'image/png' };
const rgb = value => value.replace(/\s+/g, '');
const color = (locator, property = 'backgroundColor') => locator.evaluate((el, prop) => getComputedStyle(el)[prop], property).then(rgb);
const installedChromium = () => {
    const root = process.env.LOCALAPPDATA && path.join(process.env.LOCALAPPDATA, 'ms-playwright');
    if (!root || !fs.existsSync(root)) return undefined;
    return fs.readdirSync(root).filter(name => /^chromium_headless_shell-\d+$/.test(name)).sort().reverse()
        .map(name => path.join(root, name, 'chrome-headless-shell-win64', 'chrome-headless-shell.exe')).find(fs.existsSync);
};

(async () => {
    const browser = await chromium.launch({ headless: true, executablePath: installedChromium() });
    try {
        const context = await browser.newContext({ viewport: { width: 1440, height: 1000 }, serviceWorkers: 'block' });
        const page = await context.newPage();
        const errors = [];
        const writes = [];
        let releaseWrite;
        let searchRequests = 0;
        page.on('pageerror', error => errors.push(error.message));
        await page.route('**/*', async route => {
            const request = route.request();
            const url = new URL(request.url());
            if (!['localhost', '127.0.0.1'].includes(url.hostname)) return route.continue();
            if (url.pathname === '/student/search' && request.headers()['x-requested-with'] === 'XMLHttpRequest') {
                searchRequests++;
                const catalog = JSON.parse(fs.readFileSync(path.join(output, 'catalog.json')));
                const query = (url.searchParams.get('q') || '').toLowerCase();
                catalog.books = catalog.books.filter(book => `${book.title} ${book.author}`.toLowerCase().includes(query));
                catalog.count = catalog.books.length;
                return route.fulfill({ json: catalog });
            }
            if (request.method() === 'POST' && (url.pathname === '/student/book-request' || /\/my-requests\/\d+\/cancel$/.test(url.pathname))) {
                writes.push({ path: url.pathname, headers: request.headers(), body: request.postData() });
                await new Promise(resolve => { releaseWrite = resolve; });
                return route.fulfill({ json: { success: true, message: 'Request updated successfully.', processedBy: 'Student UI Reviewer (you)' } });
            }
            if (url.pathname === '/student/notifications/unread-count') return route.fulfill({ json: { success: true, unread_count: 0 } });
            if (url.pathname === '/student/notifications') return route.fulfill({ json: { success: true, data: [], unread_count: 0 } });
            if (routes[url.pathname]) return route.fulfill({ body: fs.readFileSync(path.join(output, `${routes[url.pathname]}.html`)), contentType: 'text/html' });
            const filename = path.resolve(publicRoot, '.' + decodeURIComponent(url.pathname));
            if (filename.startsWith(publicRoot + path.sep) && fs.existsSync(filename) && fs.statSync(filename).isFile()) {
                return route.fulfill({ body: fs.readFileSync(filename), contentType: types[path.extname(filename)] || 'application/octet-stream' });
            }
            return route.fulfill({ json: { success: true, available: true, data: [] } });
        });

        const go = async pathname => {
            await page.mouse.move(1, 1);
            await page.goto(`http://localhost:8000${pathname}`, { waitUntil: 'networkidle' });
            if (!['/student/search', '/student/profile'].includes(pathname)) {
                await page.waitForFunction(() => document.querySelector('.admin-ui-tooltip'));
            }
            await page.evaluate(() => document.fonts.ready);
        };
        for (const [pathname, name] of Object.entries(routes)) {
            await go(pathname);
            if (['search', 'profile'].includes(name)) {
                assert.equal(await page.locator('body').evaluate(el => el.classList.contains('student-portal')), false, `${name}: committed presentation restored`);
                assert.equal(await page.locator('link[href*="design-system.css"], script[src*="admin-ui.js"]').count(), 0);
                await page.screenshot({ path: path.join(output, `${name}-desktop.png`) });
                await page.setViewportSize({ width: 390, height: 844 });
                await page.screenshot({ path: path.join(output, `${name}-mobile.png`) });
                await page.setViewportSize({ width: 1440, height: 1000 });
                continue;
            }
            assert.equal(await color(page.locator('body')), 'rgb(248,250,252)', name);
            assert.equal(await color(page.locator('.navy-sidebar')), 'rgb(17,24,39)', name);
            const unnamedFields = await page.locator('main input:not([type="hidden"]), main select, main textarea').evaluateAll(fields => fields.filter(field => !field.labels?.length && !field.getAttribute('aria-label') && !field.getAttribute('aria-labelledby')).map(field => field.id));
            assert.deepEqual(unnamedFields, [], `${name}: fields have accessible names`);
            const lowContrast = await page.locator('.admin-ui-badge, .admin-ui-button:not(:disabled)').evaluateAll(elements => {
                const luminance = color => color.match(/[\d.]+/g).slice(0, 3).map(Number).map(value => {
                    const channel = value / 255;
                    return channel <= .04045 ? channel / 12.92 : ((channel + .055) / 1.055) ** 2.4;
                }).reduce((sum, value, index) => sum + value * [.2126, .7152, .0722][index], 0);
                return elements.filter(el => el.getClientRects().length && !el.closest('[hidden], [aria-hidden="true"]')).flatMap(el => {
                    const style = getComputedStyle(el);
                    const background = style.backgroundColor;
                    if (background === 'rgba(0, 0, 0, 0)') return [];
                    const values = [luminance(style.color), luminance(background)].sort((a, b) => b - a);
                    const ratio = (values[0] + .05) / (values[1] + .05);
                    const target = el.classList.contains('admin-ui-icon-button') ? 3 : 4.5;
                    return ratio < target ? [{ text: el.textContent.trim(), className: el.className, ratio }] : [];
                });
            });
            assert.deepEqual(lowContrast, [], `${name}: button and badge contrast`);
            if (['my-books', 'requests'].includes(name)) {
                const searchBox = page.locator('.search-box');
                assert.ok((await searchBox.boundingBox()).width <= 260, `${name}: compact desktop search`);
            }
            if (await page.locator('thead th').count()) {
                assert.equal(await color(page.locator('thead th').first()), 'rgb(248,250,252)', name);
                assert.equal(await page.locator('thead th').first().getAttribute('scope'), 'col');
                const second = page.locator('tbody tr').nth(1);
                assert.equal(await color(second), 'rgb(250,252,254)', name);
                await second.hover();
                await page.waitForTimeout(180);
                assert.equal(await color(second), 'rgb(241,245,249)', name);
            }
            await page.screenshot({ path: path.join(output, `${name}-desktop.png`) });
            await page.setViewportSize({ width: 390, height: 844 });
            if (['my-books', 'requests'].includes(name)) {
                const searchBox = await page.locator('.search-box').boundingBox();
                const toolbarWidth = await page.locator('.search-filter-container').evaluate(el => {
                    const style = getComputedStyle(el);
                    return el.clientWidth - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight);
                });
                assert.ok(Math.abs(searchBox.width - toolbarWidth) <= 1, `${name}: search fills its mobile toolbar`);
            }
            await page.screenshot({ path: path.join(output, `${name}-mobile.png`) });
            assert.equal(await page.locator('body').evaluate(el => el.scrollWidth <= el.clientWidth + 1), true, `${name}: no viewport overflow`);
            const overflow = await page.locator('.pwa-shell-content').evaluate(el => ({
                width: el.clientWidth, scroll: el.scrollWidth,
                wide: [...el.querySelectorAll('*')].filter(node => node.getBoundingClientRect().right > el.getBoundingClientRect().right + 1 && !node.closest('.books-table-container, .fines-table-container')).slice(0, 8).map(node => node.className),
            }));
            assert.ok(overflow.scroll <= overflow.width + 1, `${name}: tables scroll inside their container: ${JSON.stringify(overflow)}`);
            await page.setViewportSize({ width: 320, height: 844 });
            await page.screenshot({ path: path.join(output, `${name}-320.png`) });
            const narrowOverflow = await page.locator('.pwa-shell-content').evaluate(el => ({
                width: el.clientWidth, scroll: el.scrollWidth,
                wide: [...el.querySelectorAll('*')].filter(node => node.getBoundingClientRect().right > el.getBoundingClientRect().right + 1 && !node.closest('.books-table-container, .fines-table-container')).slice(0, 8).map(node => node.className),
            }));
            assert.ok(narrowOverflow.scroll <= narrowOverflow.width + 1, `${name}: 320px content fits: ${JSON.stringify(narrowOverflow)}`);
            assert.equal(await page.locator('.header').evaluate(el => el.scrollWidth <= el.clientWidth + 1), true, `${name}: 320px header controls fit`);
            await page.setViewportSize({ width: 1440, height: 1000 });
        }

        await go('/student/my-books');
        await page.locator('#searchInput').fill('no matching book');
        assert.equal(await page.locator('#emptyState').isVisible(), true);
        await page.locator('#resetFiltersBtn').click();
        assert.equal(await page.locator('#booksTableBody tr').count(), 10);
        await page.locator('#paginationButtons').getByRole('button', { name: 'Page 2', exact: true }).click();
        assert.equal(await page.locator('#booksTableBody tr').count(), 2);
        await page.locator('#entriesPerPage').selectOption('20');
        assert.equal(await page.locator('#booksTableBody tr').count(), 12);

        await go('/student/search');
        await page.locator('#searchInput').fill('Library Book 01');
        await page.waitForFunction(() => document.querySelectorAll('.book-card').length === 1);
        assert.ok(searchRequests > 0);
        const requestButton = page.locator('.request-btn').first();
        const width = (await requestButton.boundingBox()).width;
        await requestButton.click();
        await page.waitForFunction(() => document.querySelector('.request-btn').disabled);
        assert.equal(await requestButton.isDisabled(), true);
        assert.ok(Math.abs((await requestButton.boundingBox()).width - width) <= 1);
        assert.equal(writes.length, 1);
        assert.ok(writes[0].headers['x-csrf-token']);
        releaseWrite();
        await page.locator('#successModal').waitFor({ state: 'visible' });
        const dialog = page.locator('#successModal .modal-content');
        await page.screenshot({ path: path.join(output, 'search-dialog.png') });
        await dialog.locator('button').click();
        assert.equal(await page.locator('#successModal').isVisible(), false);

        await go('/student/my-requests');
        const cancel = page.locator('tbody .cancel-btn').first();
        assert.equal(await color(cancel), 'rgb(254,242,242)');
        await cancel.click();
        const confirmation = page.locator('#actionFeedbackConfirmModal');
        await confirmation.waitFor({ state: 'visible' });
        await page.waitForTimeout(180);
        assert.equal(await color(confirmation.locator('#actionFeedbackConfirmSubmitBtn')), 'rgb(220,38,38)');
        await page.screenshot({ path: path.join(output, 'request-confirmation.png') });
        await confirmation.getByRole('button', { name: 'No, Keep It' }).click();
        await page.waitForFunction(() => document.activeElement.matches('tbody .cancel-btn'));
        assert.equal(await cancel.evaluate(el => el === document.activeElement), true);
        assert.equal(writes.length, 1);
        await cancel.click();
        await confirmation.locator('#actionFeedbackConfirmSubmitBtn').click();
        await page.waitForFunction(() => document.querySelector('tbody .cancel-btn[aria-busy="true"]'));
        assert.equal(writes.length, 2);
        releaseWrite();
        await page.waitForFunction(() => document.querySelector('tbody .status-cancelled'));

        await go('/student/profile');
        await page.locator('.profile-avatar-upload').click();
        assert.equal(await page.locator('#photoTabPanel').isVisible(), true);
        await page.locator('#profileTabButton').click();
        await page.locator('#editProfileBtn').click();
        await page.locator('#name').fill('');
        await page.locator('#name').blur();
        assert.ok((await page.locator('#error_name').innerText()).length > 0);
        await page.locator('#cancelProfileBtn').click();
        assert.equal(await page.locator('#name').inputValue(), 'Student UI Reviewer');
        await page.locator('#photoTabButton').click();
        assert.equal(await page.locator('#photoTabPanel').isVisible(), true);
        await page.locator('#securityTabButton').click();
        await page.locator('#showPasswordFormBtn').click();
        await page.locator('#newPassword').fill('Password!123');
        await page.getByRole('button', { name: 'Toggle new password visibility', exact: true }).focus();
        await page.keyboard.press('Enter');
        assert.equal(await page.locator('#newPassword').getAttribute('type'), 'text');
        await go('/student/dashboard');
        await page.locator('#themeToggle').focus();
        await page.keyboard.press('Enter');
        assert.equal(await page.locator('body').evaluate(el => el.classList.contains('dark-theme')), true);
        await page.waitForFunction(() => getComputedStyle(document.querySelector('.snapshot-card')).backgroundColor === 'rgb(17, 24, 39)');
        await page.screenshot({ path: path.join(output, 'dashboard-dark.png') });
        await page.emulateMedia({ reducedMotion: 'reduce' });
        assert.equal(await page.locator('.snapshot-card').first().evaluate(el => getComputedStyle(el).transitionDuration), '0s');
        assert.deepEqual(errors, []);
        console.log('PASS: all six student pages; restored Search Books/Profile presentation and interactions; compact responsive search fields; tables, filters, pagination and empty states; AJAX request/cancel and loading; confirmation colors and focus; profile validation/tabs/password toggle; theme and reduced motion.');
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
