const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const rgb = (value) => value.replace(/\s+/g, '');
const installedChromium = () => {
    const root = process.env.LOCALAPPDATA && path.join(process.env.LOCALAPPDATA, 'ms-playwright');
    if (!root || !fs.existsSync(root)) return undefined;
    const versions = fs.readdirSync(root).filter(name => /^chromium_headless_shell-\d+$/.test(name)).sort().reverse();
    return versions.map(name => path.join(root, name, 'chrome-headless-shell-win64', 'chrome-headless-shell.exe')).find(fs.existsSync);
};

(async () => {
    const browser = await chromium.launch({ headless: true, executablePath: installedChromium() });
    try {
        const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));

        await page.setContent(`<!doctype html>
            <html><head><style>
                * { box-sizing: border-box; }
                body { margin: 0; font-family: Inter, Arial, sans-serif; }
                main { padding: 24px; }
                .search-filter-container { display: flex; align-items: center; }
                .search-container { position: relative; width: min(420px, 100%); margin-top: 16px; }
                .search-container .search-input { width: 100%; padding-left: 32px; }
                .table-container { margin-top: 18px; }
                .action-buttons, .modal-footer { display: flex; }
                .modal-overlay { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; }
                .modal-dialog { width: 720px; }
                .modal-header, .modal-footer { display: flex; align-items: center; justify-content: space-between; }
                .table-card { margin-top: 18px; }
            </style></head>
            <body class="light-theme admin-portal staff-portal">
                <main>
                    <section class="search-filter-container">
                        <label for="search">Search</label>
                        <input id="search" class="search-input" placeholder="Search by title, author, ISBN...">
                        <label for="status">Status</label>
                        <select id="status" class="filter-select"><option>All statuses</option></select>
                        <button class="btn btn-outline">Reset</button>
                        <button class="btn btn-primary">Add Book</button>
                        <button id="savingButton" class="btn btn-primary" disabled>Saving...</button>
                    </section>
                    <div class="search-container">
                        <input id="clearableSearch" class="search-input search-input-clearable" value="Selected student">
                        <button id="insetClear" type="button" class="clear-btn">Clear</button>
                    </div>
                    <section class="table-container">
                        <div class="table-wrapper">
                            <table>
                                <thead><tr><th>Title</th><th>Status</th><th>Actions</th></tr></thead>
                                <tbody id="bookRows">
                                    <tr><td><strong>Clean Code</strong><small>Prentice Hall</small><span id="tableMutedData" class="text-muted">student@example.com</span></td><td><span class="status-badge available">Available</span></td><td><div class="action-buttons"><button class="action-btn view" title="View"><i class="fa-eye"></i></button><button class="action-btn delete" title="Delete"><i class="fa-trash"></i></button></div></td></tr>
                                    <tr id="stripedRow"><td><strong>Refactoring</strong><small>Addison-Wesley</small></td><td><span class="status-badge pending">Pending</span></td><td></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <nav class="admin-table-pagination" aria-label="Pagination"><a class="admin-table-pagination-link is-active" href="#">1</a><a class="admin-table-pagination-link" href="#">2</a><span class="admin-table-pagination-link is-disabled">Next</span></nav>
                    <section aria-label="Recent Activity"><span id="adminRole" class="dashboard-role-badge dashboard-role-admin">Admin</span><span id="staffRole" class="dashboard-role-badge dashboard-role-staff">Staff</span><span id="studentRole" class="dashboard-role-badge dashboard-role-student">Student</span></section>
                    <section class="table-card"><div class="table-card-header"><h3 class="table-card-title">Recent Issues</h3></div><div class="table-list"><div class="table-item"><div class="table-item-title">Designing Data-Intensive Applications</div><div class="table-item-subtitle">Martin Kleppmann</div></div><div class="table-item"><div class="table-item-title">Domain-Driven Design</div><div class="table-item-subtitle">Eric Evans</div></div></div></section>
                </main>
                <div class="modal-overlay" id="confirmModal" style="display: none">
                    <div class="modal-dialog">
                        <div class="modal-header"><h3 class="modal-title">Delete Book?</h3><button class="modal-close" aria-label="Close dialog" onclick="document.getElementById('confirmModal').style.display='none'">×</button></div>
                        <div class="modal-body"><p>This action cannot be undone.</p></div>
                        <div class="modal-footer"><button onclick="document.getElementById('confirmModal').style.display='none'">Cancel</button><button class="btn btn-danger">Delete</button></div>
                    </div>
                </div>
            </body></html>`);
        await page.addStyleTag({ path: 'public/admin/CSS/admin-design-system.css' });
        await page.addScriptTag({ path: 'public/admin/JS/admin-ui.js' });
        await page.waitForTimeout(80);

        assert.equal(rgb(await page.locator('body').evaluate(el => getComputedStyle(el).backgroundColor)), 'rgb(248,250,252)');
        assert.equal(rgb(await page.locator('thead th').first().evaluate(el => getComputedStyle(el).backgroundColor)), 'rgb(248,250,252)');
        assert.equal(await page.locator('thead th').first().getAttribute('scope'), 'col');
        assert.equal(rgb(await page.locator('#bookRows td').first().evaluate(el => getComputedStyle(el).color)), 'rgb(15,23,42)');
        assert.equal(rgb(await page.locator('#bookRows small').first().evaluate(el => getComputedStyle(el).color)), 'rgb(15,23,42)');
        assert.equal(rgb(await page.locator('#tableMutedData').evaluate(el => getComputedStyle(el).color)), 'rgb(15,23,42)');
        assert.equal(rgb(await page.locator('#stripedRow').evaluate(el => getComputedStyle(el).backgroundColor)), 'rgb(250,252,254)');
        await page.locator('#stripedRow').hover();
        await page.waitForTimeout(180);
        assert.equal(rgb(await page.locator('#stripedRow').evaluate(el => getComputedStyle(el).backgroundColor)), 'rgb(241,245,249)');

        const primaryButton = page.getByRole('button', { name: 'Add Book' });
        const primary = await primaryButton.evaluate(el => getComputedStyle(el).backgroundColor);
        assert.equal(rgb(primary), 'rgb(37,99,235)');
        await primaryButton.hover();
        await page.waitForTimeout(180);
        assert.equal(rgb(await primaryButton.evaluate(el => getComputedStyle(el).backgroundColor)), 'rgb(29,78,216)');
        await page.mouse.down();
        await page.waitForTimeout(180);
        assert.equal(rgb(await primaryButton.evaluate(el => getComputedStyle(el).backgroundColor)), 'rgb(30,64,175)');
        await page.mouse.up();

        await page.locator('#search').focus();
        await page.waitForTimeout(180);
        const searchFocus = await page.locator('#search').evaluate(el => ({ border: getComputedStyle(el).borderColor, shadow: getComputedStyle(el).boxShadow }));
        assert.equal(rgb(searchFocus.border), 'rgb(37,99,235)');
        assert.match(searchFocus.shadow, /rgba\(37, 99, 235, 0\.12\)/);
        const viewBox = await page.getByRole('button', { name: 'View' }).boundingBox();
        assert.deepEqual([Math.round(viewBox.width), Math.round(viewBox.height)], [32, 32]);
        assert.equal(rgb(await page.getByRole('button', { name: 'Delete', exact: true }).first().evaluate(el => getComputedStyle(el).backgroundColor)), 'rgb(254,242,242)');
        assert.equal(await page.locator('#savingButton').getAttribute('aria-busy'), 'true');

        const insetControl = await page.locator('#clearableSearch').boundingBox();
        const insetClear = await page.locator('#insetClear').boundingBox();
        assert.ok(insetClear.x >= insetControl.x && insetClear.x + insetClear.width <= insetControl.x + insetControl.width);
        assert.ok(insetClear.y >= insetControl.y && insetClear.y + insetClear.height <= insetControl.y + insetControl.height);
        assert.ok(parseFloat(await page.locator('#clearableSearch').evaluate(el => getComputedStyle(el).paddingRight)) >= 76);

        const viewButton = page.getByRole('button', { name: 'View' });
        assert.equal(await viewButton.getAttribute('title'), null);
        assert.equal(await viewButton.getAttribute('data-admin-ui-tooltip'), 'View');
        assert.equal(await viewButton.getAttribute('aria-label'), 'View');

        assert.ok(await page.locator('.status-badge.available').evaluate(el => el.classList.contains('admin-ui-badge-success')));
        assert.ok(await page.locator('.status-badge.pending').evaluate(el => el.classList.contains('admin-ui-badge-warning')));
        assert.equal(rgb(await page.locator('.admin-table-pagination-link.is-active').evaluate(el => getComputedStyle(el).backgroundColor)), 'rgb(37,99,235)');
        const adminRole = await page.locator('#adminRole').evaluate(el => ({ color: getComputedStyle(el).color, background: getComputedStyle(el).backgroundColor }));
        const staffRole = await page.locator('#staffRole').evaluate(el => ({ color: getComputedStyle(el).color, background: getComputedStyle(el).backgroundColor }));
        assert.deepEqual([rgb(adminRole.color), rgb(adminRole.background)], ['rgb(153,27,27)', 'rgb(254,226,226)']);
        assert.deepEqual([rgb(staffRole.color), rgb(staffRole.background)], ['rgb(30,64,175)', 'rgb(219,234,254)']);

        await viewButton.focus();
        assert.equal(await page.locator('#adminUiTooltip').innerText(), 'View');
        assert.equal(await page.locator('#adminUiTooltip').isVisible(), true);

        await page.locator('#bookRows').evaluate(body => {
            body.insertAdjacentHTML('beforeend', '<tr id="ajaxRow"><td><strong>AJAX Book</strong></td><td><span class="status-badge overdue">Overdue</span></td><td><button class="action-btn edit" title="Edit"><i class="fa-edit"></i></button></td></tr>');
        });
        await page.waitForTimeout(80);
        assert.equal(await page.locator('#ajaxRow th').count(), 0);
        assert.ok(await page.locator('#ajaxRow .status-badge').evaluate(el => el.classList.contains('admin-ui-badge-danger')));
        assert.equal(await page.getByRole('button', { name: 'Edit' }).getAttribute('aria-label'), 'Edit');
        assert.equal(await page.getByRole('button', { name: 'Edit' }).getAttribute('title'), null);
        assert.equal(await page.getByRole('button', { name: 'Edit' }).getAttribute('data-admin-ui-tooltip'), 'Edit');

        await page.getByRole('button', { name: 'Edit' }).evaluate(el => el.setAttribute('title', 'Edit book'));
        await page.waitForTimeout(80);
        assert.equal(await page.getByRole('button', { name: 'Edit' }).getAttribute('title'), null);
        assert.equal(await page.getByRole('button', { name: 'Edit' }).getAttribute('data-admin-ui-tooltip'), 'Edit book');

        await page.locator('#confirmModal').evaluate(el => { el.style.display = 'flex'; });
        await page.waitForTimeout(80);
        assert.equal(await page.locator('.modal-dialog').getAttribute('role'), 'dialog');
        assert.equal(await page.locator('.modal-dialog').getAttribute('aria-modal'), 'true');
        assert.ok((await page.locator('.modal-dialog').boundingBox()).width <= 480);
        assert.equal(rgb(await page.locator('.modal-header').evaluate(el => getComputedStyle(el).backgroundColor)), 'rgb(239,246,255)');

        await page.screenshot({ path: 'storage/framework/testing/staff-ui-design-system-desktop.png', fullPage: true });
        await page.keyboard.press('Escape');
        assert.equal(await page.locator('#confirmModal').isVisible(), false);

        await page.setViewportSize({ width: 390, height: 844 });
        assert.equal(await page.locator('body').evaluate(el => el.scrollWidth <= el.clientWidth + 1), true);
        await page.screenshot({ path: 'storage/framework/testing/staff-ui-design-system-mobile.png', fullPage: true });
        await page.emulateMedia({ reducedMotion: 'reduce' });
        assert.equal(await primaryButton.evaluate(el => getComputedStyle(el).transitionDuration), '0s');

        assert.deepEqual(errors, []);
        console.log('PASS: staff tables, stripes, hover, actions, buttons, loading, forms, badges, pagination, modal semantics, tooltip, AJAX decoration, Escape and responsive layout.');
    } finally {
        await browser.close();
    }
})().catch(error => {
    console.error(error);
    process.exitCode = 1;
});
