// Run: node tests/Browser/book-copy-edit.cjs
const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');

const script = fs.readFileSync('resources/views/shared/book-copies/scripts.blade.php', 'utf8');
const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
}[char]));

function rows(copies) {
    return copies.map(copy => `<tr><td>${escape(copy.shelf_location)}</td><td>${escape(copy.remarks)}</td>
        <td><button class="edit-copy-btn" data-copy-id="${copy.id}">Edit</button></td></tr>
        <tr class="book-copies-edit-row" id="edit-row-${copy.id}" style="display:none"><td>
            <form class="copy-edit-form" data-copy-id="${copy.id}">
                <input name="entry_date" type="date" required value="${copy.entry_date}">
                <select name="book_type">${['borrowing', 'reference'].map(value => `<option ${value === copy.book_type ? 'selected' : ''}>${value}</option>`).join('')}</select>
                <select name="condition">${['new', 'good', 'fair', 'damaged', 'lost'].map(value => `<option ${value === copy.condition ? 'selected' : ''}>${value}</option>`).join('')}</select>
                <input name="shelf_location" value="${escape(copy.shelf_location)}">
                <input name="price" type="number" min="0" step="0.01" value="${escape(copy.price)}">
                <textarea name="remarks">${escape(copy.remarks)}</textarea>
                <button type="submit">Save Changes</button>
            </form>
        </td></tr>`).join('');
}

async function checkPortal(browser, role) {
    const original = { entry_date: '2026-10-01', book_type: 'borrowing', condition: 'good',
        shelf_location: 'A-01', price: '50.00', remarks: 'Original remarks' };
    const copies = [{ id: 101, ...original }, { id: 102, ...original }];
    const updates = [];
    let listRequests = 0;
    let responseMode = 'success';
    const page = await browser.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    const html = () => `<!doctype html><html><head><meta name="csrf-token" content="test-csrf"></head><body>
        <div id="bookCopiesPage" data-base-url="http://copy-edit.test/${role}/book-copies">
            <form id="copyFilters" action="http://copy-edit.test/${role}/books/11/copies">
                <input id="copySearch"><select id="copyStatusFilter"><option>all</option></select>
                <select id="copyTypeFilter"><option>all</option></select>
                <select id="copyEntriesSelect"><option>10</option></select>
                <button id="resetCopyFilters" type="button">Reset</button>
            </form>
            <input id="selectAllCopies" type="checkbox"><select id="copyBulkAction"><option value="">Choose</option></select>
            <button id="applyCopyBulkAction">Apply</button><button id="clearCopySelection">Clear</button>
            <span id="copySelectionCount"></span><div id="copyPagination"></div>
            <template id="copyTableSkeleton"><tr><td>Loading...</td></tr></template>
            <table><tbody id="copyTableBody">${rows(copies)}</tbody></table>
        </div><script>
            window.toasts = [];
            window.ActionFeedbackUI = class { showToast(type, title, message) { window.toasts.push({ type, title, message }); } };
        </script>${script}</body></html>`;

    await page.route('http://copy-edit.test/**', async route => {
        const request = route.request();
        if (request.method() === 'PUT') {
            // PHP does not parse multipart PUT bodies into request fields.
            const isJson = request.headers()['content-type']?.includes('application/json');
            const payload = isJson ? request.postDataJSON() : {};
            updates.push({ url: request.url(), headers: request.headers(), payload });
            if (responseMode === 'validation') return route.fulfill({ status: 422,
                json: { errors: { remarks: ['These remarks cannot be saved.'] } } });
            if (responseMode === 'rejected') return route.fulfill({ json: { success: false, message: 'Update rejected.' } });
            if (responseMode === 'invalidJson') return route.fulfill({ contentType: 'text/html', body: '<html>Login required</html>' });
            Object.assign(copies.find(copy => request.url().endsWith(`/${copy.id}`)), payload);
            return route.fulfill({ json: { success: true, data: { copy: copies[0] } } });
        }
        if (new URL(request.url()).searchParams.has('ajax_copies')) {
            listRequests++;
            return route.fulfill({ json: { success: true, tableRows: rows(copies), pagination: '',
                current_page: 1, per_page: 10, total: copies.length, summary: {} } });
        }
        return route.fulfill({ contentType: 'text/html', body: html() });
    });

    const form = page.locator('#edit-row-101 form');
    const open = () => page.locator('.edit-copy-btn[data-copy-id="101"]').click();
    const fill = async fields => {
        for (const [name, value] of Object.entries(fields)) {
            const control = form.locator(`[name="${name}"]`);
            if (['book_type', 'condition'].includes(name)) await control.selectOption(value);
            else await control.fill(value);
        }
    };
    const save = async () => {
        const count = await page.evaluate(() => toasts.length);
        await form.getByRole('button', { name: 'Save Changes' }).click();
        await page.waitForFunction(previous => toasts.length > previous, count);
        return page.evaluate(() => toasts.at(-1));
    };

    try {
        await page.goto(`http://copy-edit.test/${role}/books/11/copies`);
        await open();
        const changes = { entry_date: '2026-10-02', book_type: 'reference', condition: 'fair',
            shelf_location: 'R-05', price: '125.50', remarks: "Reader's copy & notes" };
        await fill(changes);
        assert.equal((await save()).type, 'success');
        assert.deepEqual(updates[0].payload, changes, `${role}: every edited field must reach the server`);
        assert.equal(updates[0].headers['x-csrf-token'], 'test-csrf');
        assert.equal(updates[0].url, `http://copy-edit.test/${role}/book-copies/101`);
        assert.deepEqual(copies[0], { id: 101, ...changes });
        assert.deepEqual(copies[1], { id: 102, ...original }, 'Other copies must remain unchanged');
        assert.equal(listRequests, 1, 'A successful save must refresh the table');

        await page.reload();
        await open();
        for (const [name, value] of Object.entries(changes)) {
            assert.equal(await form.locator(`[name="${name}"]`).inputValue(), value, `${name} survives reload`);
        }

        for (const mode of ['validation', 'rejected', 'invalidJson']) {
            responseMode = mode;
            await form.locator('[name="remarks"]').fill('Keep my unsaved edit');
            const listsBefore = listRequests;
            const toast = await save();
            assert.equal(toast.type, 'error', `${mode}: a failed response must never show success`);
            if (mode === 'validation') assert.equal(toast.message, 'These remarks cannot be saved.');
            assert.equal(listRequests, listsBefore, 'A failed save must not discard the form by refreshing');
            assert.equal(await form.locator('[name="remarks"]').inputValue(), 'Keep my unsaved edit');
            assert.equal(await form.getByRole('button', { name: 'Save Changes' }).isEnabled(), true);
            assert.deepEqual(copies[0], { id: 101, ...changes });
        }

        responseMode = 'success';
        await fill({ shelf_location: '', price: '0', remarks: '' });
        assert.equal((await save()).type, 'success', 'Retry after a failure must work');
        assert.equal(copies[0].price, '0', 'A zero price must be submitted');
        await open();
        await form.locator('[name="price"]').fill('');
        assert.equal((await save()).type, 'success');
        assert.equal(copies[0].price, '', 'An optional price can be cleared');
        assert.deepEqual(errors, []);
        console.log(`PASS ${role}: edits survive refresh and reload; failures retain edits and allow retry; optional fields and zero price are submitted.`);
    } finally {
        await page.close();
    }
}

(async () => {
    const browser = await chromium.launch({ headless: true, channel: 'msedge' });
    try {
        for (const role of ['admin', 'staff']) await checkPortal(browser, role);
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
