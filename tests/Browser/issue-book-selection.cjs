const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

function section(view, start, end) {
    const from = view.indexOf(start);
    const to = view.indexOf(end, from);
    assert.ok(from >= 0 && to > from, `Missing issue form section: ${start}`);
    return view.slice(from, to);
}

function installedChromium() {
    const root = process.env.LOCALAPPDATA && path.join(process.env.LOCALAPPDATA, 'ms-playwright');
    if (!root || !fs.existsSync(root)) return undefined;
    const versions = fs.readdirSync(root).filter(name => /^chromium_headless_shell-\d+$/.test(name)).sort().reverse();
    return versions.map(name => path.join(root, name, 'chrome-headless-shell-win64', 'chrome-headless-shell.exe')).find(fs.existsSync);
}

async function checkPortal(browser, role, file) {
    const view = fs.readFileSync(file, 'utf8');
    // Run the production selection and search handlers with isolated catalogue responses.
    const script = [
        section(view, 'function escapeHtml(value)', 'function formatCurrency('),
        section(view, 'window.addBookToSelection = function(book)', 'window.updateAvailableBooksInfo = function()'),
        section(view, "searchBookInput.addEventListener('input'", role === 'admin'
            ? "accessionNumberInput?.addEventListener('input'" : "issueForm.addEventListener('submit'"),
        section(view, "accessionNumberInput?.addEventListener('input'", "accessionNumberInput?.addEventListener('keydown'"),
    ].join('\n').replace(/\{\{\s*route\('[^']+'\)\s*\}\}/g,
        route => route.includes('book-copies.search') ? '/copies' : '/books');

    const page = await browser.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    try {
        await page.setContent(`<!doctype html><html><body>
            <input id="searchBook"><div id="bookResults"></div>
            <input id="accessionNumber"><button id="clearAccessionBtn">Clear</button>
            <div id="accessionResults"></div><div id="accessionDetails"></div>
            <div id="selectedBooksList"><div id="selectedBooksContainer"></div></div>
            <button id="issueButton"></button>
            <script>
                let selectedBooks = [];
                let issueSearchMode = 'book';
                let accessionLookupTimer;
                let accessionLookupRequest = 0;
                let issueBookSearchRequest = 0;
                const selectedStudent = { id: 1, name: 'Test Student' };
                const rules = { max_books: 6, borrowing_allowed: true };
                const searchBookInput = document.getElementById('searchBook');
                const bookResults = document.getElementById('bookResults');
                const accessionNumberInput = document.getElementById('accessionNumber');
                const accessionResults = document.getElementById('accessionResults');
                const accessionDetails = document.getElementById('accessionDetails');
                const clearAccessionBtn = document.getElementById('clearAccessionBtn');
                const selectedBooksContainer = document.getElementById('selectedBooksContainer');
                const selectedBooksList = document.getElementById('selectedBooksList');
                const issueButton = document.getElementById('issueButton');
                const book = { id: 11, title: 'The Second World War', author: 'Winston Churchill', category: 'History' };
                const copies = [
                    { id: 101, book_id: 11, accession_number: 'ACC-000101', book },
                    { id: 102, book_id: '11', accession_number: 'ACC-000102', book },
                    { id: 103, book_id: 12, accession_number: 'ACC-000103', book: { ...book, id: 12 } },
                ].map(copy => ({ ...copy, status: 'available', book_type: 'borrowing', condition: 'good' }));
                const books = [
                    { ...book, copies: copies.slice(0, 2) },
                    { ...book, id: 12, copies: copies.slice(2) },
                ];
                function getIssueRules() { return rules; }
                function getIssueCapacity() { return rules.max_books; }
                function getRemainingIssueCapacity() { return rules.max_books - selectedBooks.length; }
                function updateAvailableBooksInfo() {}
                function updateBookCounter() {}
                function showTransactionToast() {}
                function showCustomAlert(title, message) { window.lastAlert = { title, message }; }
                window.pendingBookSearches = [];
                window.completedBookSearches = 0;
                window.activeBooksByStudent = {};
                window.fetch = async url => {
                    const params = new URL(String(url), 'https://library.test').searchParams;
                    const studentId = params.get('student_id') || params.get('studentId');
                    const activeBookIds = window.activeBooksByStudent[studentId] || [];
                    const copyPayload = copy => ({ ...copy,
                        already_issued_to_student: activeBookIds.includes(Number(copy.book_id)) });
                    if (String(url).startsWith('/copies')) {
                        return { ok: true, json: async () => ({ data: copies.map(copy => ({
                            copy: { ...copyPayload(copy), active_issue: window.legacyCopyResponse ? undefined : copy.active_issue },
                            issue: copy.active_issue,
                        })) }) };
                    }
                    if (window.deferBookSearch) {
                        return new Promise(resolve => window.pendingBookSearches.push({ url, resolve }));
                    }
                    return { ok: true, json: async () => books.map(book => ({ ...book,
                        already_issued_to_student: activeBookIds.includes(book.id),
                        copies: book.copies.map(copyPayload) })) };
                };
                window.resolveBookSearch = (index, payload = books) => {
                    pendingBookSearches[index].resolve({ ok: true, json: async () => {
                        window.completedBookSearches++;
                        return payload;
                    } });
                };
                ${script}
            </script></body></html>`);

        await page.evaluate(() => { window.deferBookSearch = true; });
        await page.locator('#searchBook').fill('Wor');
        await page.locator('#searchBook').fill('World');
        await page.evaluate(() => { resolveBookSearch(0); resolveBookSearch(1); });
        await page.waitForFunction(() => window.completedBookSearches === 2);
        assert.equal(await page.locator('#bookResults .book-search-result').count(), 2,
            `${role}: overlapping responses must not append duplicate books`);

        await page.locator('#searchBook').fill('Optics');
        await page.locator('#searchBook').fill('World');
        await page.evaluate(() => resolveBookSearch(3));
        await page.waitForFunction(() => window.completedBookSearches === 3);
        await page.evaluate(() => resolveBookSearch(2, [{ ...books[0], id: 99, title: 'Obsolete result' }]));
        await page.waitForFunction(() => window.completedBookSearches === 4);
        assert.doesNotMatch(await page.locator('#bookResults').innerText(), /Obsolete result/,
            `${role}: a slower previous response must not replace current results`);

        await page.locator('#searchBook').fill('Optics');
        await page.locator('#searchBook').fill('');
        await page.evaluate(() => resolveBookSearch(4));
        await page.waitForFunction(() => window.completedBookSearches === 5);
        assert.equal(await page.locator('#bookResults').isVisible(), false,
            `${role}: a response arriving after clearing the input must stay hidden`);

        await page.locator('#searchBook').fill('World');
        await page.evaluate(() => resolveBookSearch(5, [books[0], books[0], books[1]]));
        await page.waitForFunction(() => window.completedBookSearches === 6);
        assert.equal(await page.locator('#bookResults .book-search-result').count(), 2,
            `${role}: each catalogue book ID must appear once`);
        await page.evaluate(() => { window.deferBookSearch = false; });

        // A different student's active loan must identify the borrower and block selection.
        await page.evaluate(() => {
            copies[0].status = 'issued';
            copies[0].active_issue = { id: 134, student: { id: 9, name: 'Other Borrower <b>Test</b>', roll_no: 'CS-2023-001' } };
        });
        await page.locator('#searchBook').fill('World');
        await page.waitForFunction(() => document.querySelector('#bookResults [data-id="11"]')
            ?.textContent.includes('Already issued to another student: Other Borrower <b>Test</b> (CS-2023-001)'));
        assert.equal(await page.getByRole('button', { name: 'Add ACC-000101', exact: true }).count(), 0);
        assert.equal(await page.getByRole('button', { name: 'Add ACC-000102', exact: true }).count(), 1);
        assert.equal(await page.locator('#bookResults b').count(), 0, `${role}: borrower names must be escaped`);

        await page.evaluate(() => { issueSearchMode = 'accession'; window.legacyCopyResponse = true; });
        await page.locator('#accessionNumber').fill('ACC');
        const otherBorrowersCopy = page.locator('#accessionResults .result-item').filter({ hasText: 'ACC-000101' });
        await page.waitForFunction(() => [...document.querySelectorAll('#accessionResults .result-item')]
            .some(element => element.textContent.includes('ACC-000101')
                && element.textContent.includes('Already issued to another student: Other Borrower <b>Test</b> (CS-2023-001)')));
        assert.equal(await otherBorrowersCopy.evaluate(element => element.style.cursor), 'not-allowed');
        assert.equal(await page.locator('#accessionResults b').count(), 0);
        await otherBorrowersCopy.click();
        assert.equal(await page.locator('.selected-book-item').count(), 0);

        // Switching to the actual borrower must change the label in both search modes.
        await page.evaluate(() => {
            selectedStudent.id = '9';
            accessionNumberInput.dispatchEvent(new Event('input', { bubbles: true }));
        });
        await page.waitForFunction(() => [...document.querySelectorAll('#accessionResults .result-item')]
            .some(element => element.textContent.includes('ACC-000101')
                && element.textContent.includes('Already issued to this student')));
        assert.doesNotMatch(await otherBorrowersCopy.innerText(), /another student/);
        await page.evaluate(() => { issueSearchMode = 'book'; });
        await page.locator('#searchBook').fill('World');
        await page.waitForFunction(() => document.querySelector('#bookResults [data-id="11"]')
            ?.textContent.includes('ACC-000101 — Already issued to this student'));

        // Older responses without borrower details must still explain that another student has it.
        await page.evaluate(() => {
            selectedStudent.id = 1;
            delete copies[0].active_issue;
        });
        await page.locator('#searchBook').fill('World');
        await page.waitForFunction(() => document.querySelector('#bookResults [data-id="11"]')
            ?.textContent.includes('ACC-000101 — Already issued to another student'));
        await page.evaluate(() => {
            issueSearchMode = 'accession';
            accessionNumberInput.dispatchEvent(new Event('input', { bubbles: true }));
        });
        await page.waitForFunction(() => [...document.querySelectorAll('#accessionResults .result-item')]
            .some(element => element.textContent.includes('ACC-000101')
                && element.textContent.includes('Status: Already issued to another student')));
        await page.evaluate(() => {
            copies[0].status = 'available';
            delete copies[0].active_issue;
            window.legacyCopyResponse = false;
            issueSearchMode = 'book';
            accessionNumberInput.value = '';
            accessionResults.innerHTML = '';
        });

        // An available accession is still blocked when this borrower has another copy.
        await page.evaluate(() => { window.activeBooksByStudent[1] = [11]; });
        await page.locator('#searchBook').fill('World');
        await page.waitForFunction(() => document.querySelector('#bookResults [data-id="11"]')
            ?.textContent.includes('Already issued to this student'));
        assert.equal(await page.getByRole('button', { name: 'Add ACC-000101', exact: true }).count(), 0);
        assert.equal(await page.getByRole('button', { name: 'Add ACC-000102', exact: true }).count(), 0);
        assert.equal(await page.getByRole('button', { name: 'Add ACC-000103', exact: true }).count(), 1);
        assert.equal(await page.evaluate(() => addBookToSelection({ ...copies[1], already_issued_to_student: true })), false);
        assert.equal(await page.evaluate(() => window.lastAlert.title), 'Book Already Issued');

        await page.evaluate(() => { issueSearchMode = 'accession'; });
        await page.locator('#accessionNumber').fill('ACC');
        const activeCopyResult = page.locator('#accessionResults .result-item').filter({ hasText: 'ACC-000102' });
        await page.waitForFunction(() => [...document.querySelectorAll('#accessionResults .result-item')]
            .some(element => element.textContent.includes('ACC-000102') && element.textContent.includes('Already issued to this student')));
        assert.equal(await activeCopyResult.evaluate(element => element.style.cursor), 'not-allowed');
        await activeCopyResult.click();
        assert.equal(await page.locator('.selected-book-item').count(), 0);

        await page.evaluate(() => {
            selectedStudent.id = 2;
            accessionNumberInput.dispatchEvent(new Event('input', { bubbles: true }));
        });
        await page.waitForFunction(() => [...document.querySelectorAll('#accessionResults .result-item')]
            .some(element => element.textContent.includes('ACC-000102') && element.style.cursor !== 'not-allowed'));
        assert.doesNotMatch(await activeCopyResult.innerText(), /Already issued to this student/);

        // Once returned, the book is available for the original borrower again.
        await page.evaluate(() => {
            selectedStudent.id = 1;
            window.activeBooksByStudent[1] = [];
            issueSearchMode = 'book';
            accessionNumberInput.value = '';
            accessionResults.innerHTML = '';
            searchBookInput.dispatchEvent(new Event('input', { bubbles: true }));
        });
        await page.getByRole('button', { name: 'Add ACC-000101', exact: true }).waitFor();

        await page.locator('#searchBook').fill('World');
        await page.getByRole('button', { name: 'Add ACC-000101', exact: true }).click();
        assert.equal(await page.locator('.selected-book-item').count(), 1);

        await page.locator('#searchBook').fill('World');
        await page.waitForFunction(() => document.querySelector('#bookResults').textContent.includes('Book already selected'));
        assert.equal(await page.getByRole('button', { name: 'Add ACC-000102', exact: true }).count(), 0);
        // The final add guard also rejects stale clicks and repeated scans.
        assert.equal(await page.evaluate(() => addBookToSelection(copies[1])), false);
        assert.equal(await page.evaluate(() => addBookToSelection(copies[0])), false);
        assert.equal(await page.evaluate(() => window.lastAlert.title), 'Book Already Selected');
        assert.equal(await page.locator('.selected-book-item').count(), 1);

        // Separate catalogue books remain selectable even with identical titles.
        await page.getByRole('button', { name: 'Add ACC-000103', exact: true }).click();
        assert.equal(await page.locator('.selected-book-item').count(), 2);
        await page.locator('#searchBook').fill('World');
        await page.waitForFunction(() => document.querySelectorAll('#bookResults .book-search-result').length === 2);
        await page.locator('[data-book-copy-id="101"]').click();
        await page.getByRole('button', { name: 'Add ACC-000102', exact: true }).click();
        assert.equal(await page.locator('.selected-book-item').count(), 2);

        await page.locator('[data-book-copy-id="102"]').click();
        await page.evaluate(() => { issueSearchMode = 'accession'; });
        await page.locator('#accessionNumber').fill('ACC');
        await page.locator('#accessionResults .result-item').filter({ hasText: 'ACC-000101' }).click();
        assert.equal(await page.locator('.selected-book-item').count(), 2);
        await page.locator('#accessionNumber').fill('ACC');
        await page.waitForFunction(() => document.querySelector('#accessionResults').textContent.includes('ACC-000102'));
        const duplicateResult = page.locator('#accessionResults .result-item').filter({ hasText: 'ACC-000102' });
        assert.match(await duplicateResult.innerText(), /Book already selected/);
        assert.equal(await duplicateResult.evaluate(element => element.style.cursor), 'not-allowed');

        await page.locator('[data-book-copy-id="101"]').click();
        await page.waitForFunction(() => [...document.querySelectorAll('#accessionResults .result-item')]
            .some(element => element.textContent.includes('ACC-000102') && element.style.cursor !== 'not-allowed'));
        await duplicateResult.click();
        assert.equal(await page.locator('.selected-book-item').count(), 2);
        assert.match(await page.locator('#issueButton').innerText(), /\(2\/6\)/);
        assert.deepEqual(errors, []);
        console.log(`PASS ${role}: current borrower identified in both search modes; active loans blocked, returned copies allowed; overlapping searches and duplicate selections blocked.`);
    } finally {
        await page.close();
    }
}

(async () => {
    const browser = await chromium.launch({ headless: true, executablePath: installedChromium() });
    try {
        await checkPortal(browser, 'admin', 'resources/views/Admin/Transactions.blade.php');
        await checkPortal(browser, 'staff', 'resources/views/Staff/IssueBook.blade.php');
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
