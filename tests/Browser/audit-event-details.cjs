// Run from the project root: node tests/Browser/audit-event-details.cjs
// Uses the real modal, page styles, renderer, and shared keyboard behavior.
const { chromium } = require('playwright');
const fs = require('node:fs');
const assert = require('node:assert/strict');
const read = path => fs.readFileSync(path, 'utf8');
const view = read('resources/views/Admin/StudentView.blade.php');
const partial = read('resources/views/partials/admin-audit-event-details-modal.blade.php').replace(/\{\{--[\s\S]*?--\}\}/g, '');
const show = view.slice(view.indexOf('        function showActivityDetails('), view.indexOf('        function getActivityTypeConfig('));
const close = view.slice(view.indexOf('        function closeActivityDetails('), view.indexOf('        // Back button functionality'));
const log = { id: '55', type: 'fine-applied', title: 'Fine Adjusted', status: 'warning', userName: 'Saroj Mehta', userRole: 'admin', accessionNumber: 'ACC-000034', description: 'Fine for “HTTP/2 in Action” (ISBN: 0001000033) adjusted for Rajkumar Roy from ₹29 to ₹35.', fullTimestamp: 'Oct 06, 2026 10:02:06 AM', resourceType: 'Fine', resourceId: '55', resourceAvailable: true, resourceUrl: 'https://lms.test/admin/fines?student=4', ipAddress: '127.0.0.1', deviceType: 'Desktop', browser: 'Firefox', sessionId: 'I74VYPhG8GEzjajNk92'.repeat(10), metadata: { amount: 35, old_amount: 29, new_amount: 35, amount_change: 6, book_name: 'HTTP/2 in Action', book_title: 'HTTP/2 in Action', isbn: '0001000033', student_label: 'Rajkumar Roy (CHEM-2023-070)', student_name: 'Rajkumar Roy', fine_id: 55, issued_book_id: 34, days_late: 17, status: 'pending', fine_status: 'pending', action_type: 'adjusted', remarks: 'Adjusted from ₹29.00 to ₹35.00', session_id: 'I74VYPhG8GEzjajNk92'.repeat(10), password: 'DO_NOT_EXPOSE', nested: { accessToken: 'DO_NOT_EXPOSE', enabled: false }, serialized: '{"api_key":"DO_NOT_EXPOSE","reviewed":true}' } };
const unknown = { id: '56', type: 'custom-event', title: 'Custom Review', status: 'info', userName: 'System', userRole: 'system', resourceType: 'Resource', description: '<img src=x onerror="window.injected=true">', metadata: { optional: null, zero: 0, flag: false, array: ['One', { secret_key: 'DO_NOT_EXPOSE', ok: true }], long_text: 'x'.repeat(1200) } };
const styles = read('public/admin/CSS/audit-event-details.css') + view.match(/<style>([\s\S]*?)<\/style>/)[1] + read('public/admin/CSS/admin-design-system.css');
const html = `<!doctype html><html><head><meta charset="utf-8"><style>body{margin:0;font-family:Arial,sans-serif}*{box-sizing:border-box}${styles}</style></head><body class="admin-portal light-theme"><button id="more55" onclick="showActivityDetails('55',this)">More Details</button><button id="more56" onclick="showActivityDetails('56',this)">More Details</button>${partial}<script>${read('public/admin/JS/audit-event-details.js')}</script><script>const activityLogLookup=new Map(${JSON.stringify([['55', log], ['56', unknown]]).replaceAll('<','\\u003c')}); function showToast(m){window.lastToast=m};${show}${close}document.getElementById('closeActivityDetailsBtn').addEventListener('click',closeActivityDetails);document.getElementById('activityDetailsOverlay').addEventListener('click',e=>{if(e.target.id==='activityDetailsOverlay')closeActivityDetails()});</script><script>${read('public/admin/JS/admin-ui.js')}</script></body></html>`;
(async () => {
    const browser = await chromium.launch({ headless: true, channel: 'msedge' });
    try {
        const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
        const errors = []; page.on('pageerror', error => errors.push(error.message));
        await page.route('https://lms.test/**', route => route.fulfill({ body: html, contentType: 'text/html' }));
        await page.goto('https://lms.test/audit-preview');
        await page.click('#more55');
        assert.equal(await page.locator('#auditEventTitle').innerText(), 'Fine Adjusted');
        assert.equal(await page.locator('#activityDetailsTitle').innerText(), 'Audit Event Details');
        assert.equal(await page.locator('#auditEventSeverity').innerText(), 'Warning');
        assert.equal(await page.locator('#auditActorName').innerText(), 'Saroj Mehta');
        assert.equal(await page.locator('#auditActorRole').innerText(), 'Admin');
        assert.equal(await page.locator('#auditEventTimestamp').innerText(), log.fullTimestamp);
        assert.equal(await page.locator('#auditResourceName').innerText(), 'Fine #55');
        assert.match(await page.locator('#auditResourceFields').innerText(), /Accession Number[\s\S]*ACC-000034/);
        assert.equal(await page.getByRole('button', { name: 'Copy Accession Number', exact: true }).count(), 1);
        assert.match(await page.locator('#auditDescription').innerText(), /Accession Number: ACC-000034/);
        assert.doesNotMatch(await page.locator('#activityDetailsOverlay').textContent(), /ISBN|0001000033/);
        const noCopy = await page.evaluate(log => window.LMSAuditDetails.model({ ...log, accessionNumber: null }), log);
        assert.equal(noCopy.resource.find(row => row.label === 'Accession Number').value, 'Not recorded');
        assert.doesNotMatch(noCopy.description, /ISBN|0001000033/);
        const recordedCopy = await page.evaluate(log => window.LMSAuditDetails.model({ ...log, metadata: { ...log.metadata, accession_number: 'HISTORIC-001' } }), log);
        assert.equal(recordedCopy.resource.find(row => row.label === 'Accession Number').value, 'HISTORIC-001');
        assert.ok(!recordedCopy.business.some(row => row.label === 'Accession Number'));
        assert.match(await page.locator('#auditChanges').innerText(), /₹29\.00[\s\S]*₹35\.00[\s\S]*\+₹6\.00/);
        assert.equal(await page.locator('#auditResourceFields').getByText('HTTP/2 in Action', { exact: true }).count(), 1);
        assert.equal(await page.locator('#auditBusinessFields').getByText('Fine Status', { exact: true }).count(), 1);
        assert.doesNotMatch(await page.locator('#auditBusinessFields').innerText(), /Old Amount|New Amount|Amount Changed|Book Title|Student Label/);
        assert.equal(await page.locator('#auditTechnicalSection').getAttribute('open'), null);
        assert.equal(await page.locator('#activityDetailsResourceLink').getAttribute('href'), log.resourceUrl);
        assert.doesNotMatch(await page.locator('#activityDetailsOverlay').textContent(), /DO_NOT_EXPOSE|accessToken|secret_key|api_key/);
        await page.locator('#auditAdditionalSection summary').click();
        assert.match(await page.locator('#auditAdditionalFields').innerText(), /Enabled: No|Reviewed: Yes/);
        const dimensions = await page.locator('.activity-modal').evaluate(el => ({ width: el.offsetWidth, height: el.offsetHeight }));
        assert.equal(dimensions.width, 860); assert.ok(dimensions.height <= 802);
        const screenshotDir = 'storage/framework/testing'; fs.mkdirSync(screenshotDir, { recursive: true });
        await page.locator('#auditAdditionalSection summary').click();
        await page.locator('.activity-modal-body').evaluate(el => { el.scrollTop = 0; });
        await page.screenshot({ path: `${screenshotDir}/audit-details-desktop.png` });
        await page.locator('#auditTechnicalSection summary').click();
        assert.equal(await page.locator('.audit-session').innerText(), log.sessionId);
        await page.context().grantPermissions(['clipboard-read', 'clipboard-write']);
        await page.getByRole('button', { name: 'Copy Session ID', exact: true }).click();
        assert.equal(await page.evaluate(() => navigator.clipboard.readText()), log.sessionId);
        assert.equal(await page.getByRole('button', { name: 'Copy Session ID', exact: true }).innerText(), 'Copied');
        await page.locator('.activity-modal-actions button').focus(); await page.keyboard.press('Tab');
        assert.equal(await page.evaluate(() => document.activeElement.id), 'closeActivityDetailsBtn');
        await page.keyboard.press('Shift+Tab');
        assert.equal(await page.evaluate(() => document.activeElement.textContent.trim()), 'Close');
        await page.keyboard.press('Escape');
        assert.equal(await page.evaluate(() => document.activeElement.id), 'more55');
        await page.click('#more56');
        assert.equal(await page.locator('.activity-modal-body').evaluate(el => el.scrollTop), 0);
        assert.equal(await page.locator('#auditEventTitle').innerText(), 'Custom Review');
        assert.equal(await page.locator('#auditChangeSection').isVisible(), false);
        assert.equal(await page.locator('#auditTechnicalSection').isVisible(), false);
        assert.equal(await page.locator('#activityDetailsResourceLink').isVisible(), false);
        assert.equal(await page.locator('#auditRemarks').innerText(), 'No remarks recorded.');
        assert.equal(await page.locator('#auditDescription img').count(), 0);
        assert.equal(await page.evaluate(() => window.injected), undefined);
        assert.match(await page.locator('#auditAdditionalFields').textContent(), /Not recorded|No|0/);
        await page.keyboard.press('Escape');
        // Stored difference is authoritative even when it differs from a computed subtraction.
        const derived = await page.evaluate(log => window.LMSAuditDetails.model({ ...log, metadata: { old_amount: 29, new_amount: 35, amount_change: 4 } }), log);
        assert.equal(derived.changes[0].delta, 4);
        const decreased = await page.evaluate(log => window.LMSAuditDetails.model({ ...log, metadata: { old_amount: 35, new_amount: 29, amount_change: -6 } }), log);
        assert.equal(decreased.changes[0].delta, -6);
        for (const type of ['book-issued', 'book-returned', 'account-status', 'privilege-change']) {
            const event = await page.evaluate(type => window.LMSAuditDetails.model({ type, metadata: { accession_number: 'ACC-34', issue_date: '2026-10-06', due_date: '2026-10-20', condition: 'good', old_value: false, new_value: true, setting: 'borrowing_allowed' } }), type);
            assert.equal(event.changes[0].old, 'No'); assert.equal(event.changes[0].next, 'Yes');
            assert.ok(event.resource.some(row => row.label === 'Accession Number'));
        }
        for (const width of [768, 390, 320]) {
            await page.setViewportSize({ width, height: 844 }); await page.click('#more55');
            await page.locator('#auditTechnicalSection summary').click();
            const fits = await page.locator('.activity-modal').evaluate(el => ({ width: el.offsetWidth, overflow: el.scrollWidth > el.clientWidth, bodyOverflow: el.querySelector('.activity-modal-body').scrollWidth > el.querySelector('.activity-modal-body').clientWidth }));
            assert.ok(fits.width <= width * .94 + 1); assert.equal(fits.overflow, false); assert.equal(fits.bodyOverflow, false);
            if (width === 390) {
                await page.locator('.activity-modal-body').evaluate(el => { el.scrollTop = 0; });
                await page.screenshot({ path: `${screenshotDir}/audit-details-mobile.png` });
            }
            await page.keyboard.press('Escape');
        }
        await page.setViewportSize({ width: 1280, height: 900 }); await page.evaluate(() => document.body.classList.replace('light-theme', 'dark-theme'));
        await page.click('#more55'); await page.screenshot({ path: `${screenshotDir}/audit-details-dark.png` });
        assert.deepEqual(errors, []);
        console.log('PASS: audit event content, changes, metadata safety, resource links, clipboard, stale-content reset, desktop/mobile layout, dark theme, keyboard focus and Escape.');
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
