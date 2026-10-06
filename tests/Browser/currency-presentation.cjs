// Run after npm run build: node tests/Browser/currency-presentation.cjs
// Uses the production fine-history renderer, report exporter and built PDF font loader.
const { chromium } = require('playwright');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const read = file => fs.readFileSync(file, 'utf8');
const student = read('resources/views/Admin/StudentView.blade.php');
const reports = read('resources/views/Admin/ReportsDynamic.blade.php');
const helpers = student.slice(student.indexOf('        function formatCurrency(value)'), student.indexOf('        function savePrivilegeSettings()'));
const pdfExport = reports.slice(reports.indexOf('        function sanitizeReportFilenamePart('), reports.indexOf('        function openReportPdfPreviewModal('));
const manifest = JSON.parse(read('public/build/manifest.json'));
const output = 'storage/framework/testing/currency';
fs.mkdirSync(output, { recursive: true });
const rows = [['Per day rate', 'रु 3.50'], ['Subtotal', 'रु 29.00'], ['Adjustment', '+रु 6.00'], ['Final amount', 'रु 35.00'], ['Paid amount', 'रु 500.00'], ['Outstanding', 'रु 0.00'], ['Total', 'रु 1,500.00']];
const html = `<!doctype html><html><head><meta charset="utf-8"><meta name="csrf-token" content="test"><style>${student.match(/<style>([\s\S]*?)<\/style>/)[1]} body{font-family:Arial,sans-serif;margin:0}</style></head><body>${read('resources/views/partials/admin-fine-history-modal.blade.php').replace(/\{\{--[\s\S]*?--\}\}/g, '')}<script>${read('public/shared/currency.js')}</script><script>${helpers}function closeFineModal(){window.closeFineHistoryPanel()}function formatDisplayLabel(v){return String(v)}${read('public/admin/JS/fine-history.js')}</script><script type="module" src="/build/${manifest['resources/js/app.js'].file}"></script></body></html>`;

(async () => {
    const browser = await chromium.launch({ headless: true, channel: 'msedge' });
    try {
        const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        await page.route('http://currency.test/**', route => {
            const url = new URL(route.request().url());
            if (url.pathname === '/') return route.fulfill({ body: html, contentType: 'text/html' });
            if (url.pathname.endsWith('/history')) return route.fulfill({ json: {
                success: true,
                fineDetails: { id: 1, status: 'paid', currentAmount: 35, originalAmount: 29, bookTitle: 'HTTP/2 in Action', daysLate: 17 },
                calculation: { baseRate: 3.5, daysLate: 17, subtotal: 29, adjustments: 6, finalAmount: 35 },
                paymentDetails: { amount: 35, outstanding: 0, method: 'cash', recordedBy: 'Admin' },
                history: [{ actionType: 'adjusted', oldAmount: 29, newAmount: 35, amountChange: 6, userRole: 'admin' }],
            } });
            const file = path.join('public', decodeURIComponent(url.pathname));
            if (fs.existsSync(file)) return route.fulfill({ body: fs.readFileSync(file), contentType: file.endsWith('.js') ? 'text/javascript' : 'font/ttf' });
            return route.abort();
        });
        await page.goto('http://currency.test/');
        await page.waitForFunction(() => window.loadReportPdfFonts && window.PDFLib);
        await page.evaluate(() => viewFineHistory(1));
        await page.locator('#fineHistoryContent').waitFor({ state: 'visible' });
        assert.equal(await page.locator('#historyCalcBaseRate').innerText(), 'रु 3.50');
        assert.equal(await page.locator('#historyCalcSubtotal').innerText(), 'रु 29.00');
        assert.equal(await page.locator('#historyCalcAdjustments').innerText(), '+रु 6.00');
        assert.equal(await page.locator('#historyCalcFinalAmount').innerText(), 'रु 35.00');
        assert.match(await page.locator('#historyPaymentDetails').innerText(), /रु 35.00[\s\S]*रु 0.00/);
        assert.match(await page.locator('#fineHistoryList').innerText(), /रु 29.00 → रु 35.00[\s\S]*\+रु 6.00/);
        const decrease = await page.evaluate(() => renderFineHistoryItem({ actionType: 'adjusted', oldAmount: 500, newAmount: 300, amountChange: -200 }));
        assert.match(decrease, /रु 500.00 → रु 300.00/);
        assert.match(decrease, /-रु 200.00/);
        for (const width of [1280, 390, 320]) {
            await page.setViewportSize({ width, height: 900 });
            assert.equal(await page.locator('.fine-history-modal-body').evaluate(el => el.scrollWidth > el.clientWidth), false);
            await page.screenshot({ path: `${output}/fine-history-${width}.png` });
        }

        await page.addScriptTag({ content: `
            const reportBranding = {};
            const getReportSystemTitle = () => 'Library Management System';
            const getReportDocumentTitle = () => 'Fine Report';
            const getReportBrandingFallbackText = () => 'LMS';
            const getActiveReportTypeLabel = () => 'Fines';
            const buildReportGeneratedLabel = () => 'Currency presentation verification';
            const formatNumber = value => String(value);
            const buildReportExportDocumentDetails = () => [{ label: 'Currency', value: 'NPR' }];
            const buildReportSnapshot = (scope, generatedAt) => ({ generatedAt, reportTypeLabel: 'Fines', heading: { title: 'Fine and Payment Report', description: 'Financial display verification' }, stats: [{ label: 'Pending', value: 'रु 1,500.00' }, { label: 'Collected', value: 'रु 500.00' }], charts: [], tables: [{ title: 'Fine Breakdown', description: 'Amounts retain existing precision', headers: ['Description', 'Amount'], rows: ${JSON.stringify(rows)} }] });
            ${pdfExport}
        ` });
        const [downloaded] = await Promise.all([
            page.waitForEvent('download'),
            page.evaluate(() => downloadReportPdf()),
        ]);
        await downloaded.saveAs(`${output}/report.pdf`);

        // Browser print uses the same two local font assets with Latin typography preserved.
        const regular = `/build/${manifest['resources/fonts/NotoSansDevanagari-Regular.ttf'].file}`;
        const bold = `/build/${manifest['resources/fonts/NotoSansDevanagari-Bold.ttf'].file}`;
        await page.setContent(`<html><head><meta charset="utf-8"><style>@font-face{font-family:LmsCurrencyPrint;src:url(http://currency.test${regular})} @font-face{font-family:LmsCurrencyPrint;src:url(http://currency.test${bold});font-weight:700}body{font-family:Arial,LmsCurrencyPrint,sans-serif;margin:40px}td{padding:10px}th{text-align:left}</style></head><body><h2>Fine Payment Receipt</h2><table>${rows.map(row => `<tr><th>${row[0]}</th><td>${row[1]}</td></tr>`).join('')}</table></body></html>`);
        await page.evaluate(() => document.fonts.ready);
        await page.pdf({ path: `${output}/print.pdf`, format: 'A4' });
        assert.deepEqual(errors, []);
        console.log('PASS: fine calculation, signed adjustment history, payment totals, desktop/mobile layouts, production PDF export and browser print.');
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
