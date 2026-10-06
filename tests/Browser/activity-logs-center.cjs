// Generate isolated SQLite fixtures first:
// $env:AUDIT_BROWSER_FIXTURES='1'; php artisan test tests/Feature/Admin/ActivityLogsCenterTest.php
// Then: node tests/Browser/activity-logs-center.cjs
const { chromium } = require('playwright');
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const assert = require('node:assert/strict');
const read = file => fs.readFileSync(file, 'utf8');
const fixture = name => read(`storage/framework/testing/audit-center${name}.html`);
const scripts = ['audit-event-details', 'activity-logs', 'admin-ui'];
function html(source) {
    // Retain the real shell, CSS, Blade output and audit scripts; other modules use live APIs.
    const manifest = JSON.parse(read('public/build/manifest.json'));
    return source.replaceAll('localhost:8000', 'localhost')
        .replace(/http:\/\/127\.0\.0\.1:5173\/resources\/css\/app.css/g, `/build/${manifest['resources/css/app.css'].file}`)
        .replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi, tag => /data-audit-payload/.test(tag) ? tag : '')
        .replace('</body>', `<script src="https://unpkg.com/lucide@latest"></script>${scripts.map(name => `<script src="/admin/JS/${name}.js"></script>`).join('')}</body>`);
}
let delay = 0;
(async () => {
    const browser = await chromium.launch({ headless: true, channel: 'msedge' });
    try {
        const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
        const errors = []; page.on('pageerror', error => errors.push(error.message));
        await page.route('http://localhost/**', async route => {
            const url = new URL(route.request().url());
            if (url.pathname === '/admin/activity-logs') {
                const params = new URLSearchParams(url.search); params.delete('per_page'); params.delete('page');
                const query = params.toString();
                const name = query ? `-${crypto.createHash('md5').update(query).digest('hex')}` : url.searchParams.get('page') === '2' ? '-page2' : url.searchParams.get('per_page') === '20' ? '-all' : '';
                if (delay) await new Promise(resolve => setTimeout(resolve, delay));
                return route.fulfill({ body: html(fixture(name)), contentType: 'text/html' });
            }
            const file = path.join('public', decodeURIComponent(url.pathname));
            if (fs.existsSync(file) && fs.statSync(file).isFile()) return route.fulfill({ path: file });
            return route.fulfill({ status: 204 });
        });
        await page.goto('http://localhost/admin/activity-logs');
        await page.waitForFunction(() => window.LMSAuditDetails && document.querySelector('.activity-event svg'));
        assert.equal(await page.locator('.activity-stat-value').first().innerText(), '12');
        const expected = { 'Fine Applied': ['rgb(194, 65, 12)', 'rgb(255, 237, 213)'], Login: ['rgb(109, 40, 217)', 'rgb(237, 233, 254)'], 'Book Created': ['rgb(29, 78, 216)', 'rgb(219, 234, 254)'], 'Book Returned': ['rgb(22, 101, 52)', 'rgb(220, 252, 231)'], 'User Created': ['rgb(15, 118, 110)', 'rgb(204, 251, 241)'], 'Privilege Updated': ['rgb(67, 56, 202)', 'rgb(224, 231, 255)'], 'Book Deleted': ['rgb(153, 27, 27)', 'rgb(254, 226, 226)'] };
        for (const [event, colors] of Object.entries(expected)) {
            const row = page.locator('tbody tr').filter({ has: page.locator('.activity-event', { hasText: event }) });
            const actual = await row.locator('.activity-event').evaluate(el => { const s = getComputedStyle(el); return [s.color, s.backgroundColor]; });
            assert.deepEqual(actual, colors, event);
            await row.locator('[data-audit-details]').click();
            assert.equal(await page.locator('#auditEventTitle').innerText(), event);
            assert.deepEqual(await page.locator('#auditEventSeverity').evaluate(el => { const s = getComputedStyle(el); return [s.color, s.backgroundColor]; }), colors);
            assert.equal(await page.locator('#activityDetailsResourceLink').isVisible(), event !== 'Book Deleted');
            await page.keyboard.press('Escape');
            assert.equal(await row.locator('[data-audit-details]').evaluate(el => el === document.activeElement), true);
        }
        assert.ok(await page.locator('.context-description strong').count() > 0);
        // WCAG normal-text contrast for every event/category/role badge actually rendered.
        const failures = await page.locator('.activity-event, .activity-category, .activity-role').evaluateAll(elements => {
            const luminance = color => {
                const channels = color.match(/[\d.]+/g).slice(0, 3).map(Number).map(value => {
                    const s = value / 255; return s <= .04045 ? s / 12.92 : ((s + .055) / 1.055) ** 2.4;
                });
                return channels[0] * .2126 + channels[1] * .7152 + channels[2] * .0722;
            };
            return elements.map(el => {
                const style = getComputedStyle(el);
                const values = [luminance(style.color), luminance(style.backgroundColor)].sort((a, b) => b - a);
                return { text: el.textContent.trim(), ratio: (values[0] + .05) / (values[1] + .05) };
            }).filter(item => item.ratio < 4.5);
        });
        assert.deepEqual(failures, [], 'Badge contrast');
        assert.equal(await page.locator('.activity-frequency-percent').first().innerText(), '8.3%');
        await page.evaluate(() => { document.activeElement.blur(); document.querySelector('.pwa-shell-content').scrollTo({ top: 0, behavior: 'instant' }); });
        await page.evaluate(() => window.scrollTo(0, 0));
        await page.screenshot({ path: 'storage/framework/testing/audit-center-desktop.png' });
        await page.locator('.pwa-shell-content').evaluate(el => el.scrollTo({ top: el.scrollHeight, behavior: 'instant' }));
        await page.screenshot({ path: 'storage/framework/testing/audit-center-analytics.png' });
        const wait = () => page.waitForFunction(() => document.getElementById('activityLogAsyncRoot').getAttribute('aria-busy') === 'false');
        delay = 250;
        await page.selectOption('#role', 'admin');
        assert.equal(await page.locator('.activity-table-wrapper').isVisible(), false);
        await wait();
        assert.equal(await page.locator('.activity-stat-value').nth(1).innerText(), '6');
        assert.equal(await page.evaluate(() => document.activeElement.id), 'role');
        assert.equal(await page.locator('[data-clear-filter="role"]').count(), 1);
        await page.click('[data-clear-filter="role"]'); await wait();
        assert.match(await page.locator('.activity-page-note').innerText(), /12 total audit entries/);
        await page.selectOption('#event_type', 'fine_applied'); await wait();
        assert.equal(await page.locator('tbody tr').count(), 1);
        await page.click('#resetFiltersBtn'); await wait();
        await page.fill('#search', 'Spectroscopy');
        await page.waitForURL('**?search=Spectroscopy&per_page=10'); await wait();
        assert.equal(await page.locator('tbody tr').count(), 4);
        await page.click('#resetFiltersBtn'); await wait();
        await page.locator('.admin-table-pagination a[rel="next"]').click(); await wait();
        assert.equal(await page.locator('tbody tr').count(), 2);
        await page.goBack(); await wait();
        assert.equal(await page.locator('tbody tr').count(), 10);
        await page.selectOption('#per_page', '20'); await wait();
        assert.equal(await page.locator('tbody tr').count(), 12);
        await page.fill('#search', 'does-not-exist'); await page.waitForURL('**search=does-not-exist**'); await wait();
        assert.equal(await page.getByText('No activity logs found', { exact: true }).count(), 1);
        await page.getByRole('button', { name: 'Reset Filters', exact: true }).click(); await wait();
        for (const width of [1440, 1024, 768, 390, 320]) {
            await page.setViewportSize({ width, height: 900 });
            assert.equal(await page.locator('.activity-page').evaluate(el => el.scrollWidth <= el.clientWidth + 1), true, `Page overflow at ${width}`);
            assert.equal(await page.locator('.activity-filter-panel').evaluate(el => el.scrollWidth <= el.clientWidth + 1), true, `Filter overflow at ${width}`);
            if (width === 390) {
                await page.locator('.pwa-shell-content').evaluate(el => el.scrollTo({ top: 0, behavior: 'instant' }));
                await page.evaluate(() => window.scrollTo(0, 0));
                await page.screenshot({ path: 'storage/framework/testing/audit-center-mobile.png' });
            }
        }
        await page.setViewportSize({ width: 1440, height: 1000 });
        await page.evaluate(() => { document.body.classList.remove('light-theme'); document.body.classList.add('dark-theme'); });
        await page.locator('.pwa-shell-content').evaluate(el => el.scrollTo({ top: 0, behavior: 'instant' }));
        await page.evaluate(() => window.scrollTo(0, 0));
        await page.screenshot({ path: 'storage/framework/testing/audit-center-dark.png' });
        assert.deepEqual(errors, []);
        console.log('PASS: semantic colors, shared modal, resource availability, focus/Escape, AJAX loading/search/filters/chips/reset/pagination/history/page size, counts, empty state, responsive shell and dark theme.');
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
