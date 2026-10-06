@php
    $physicalRoutePrefix = auth()->user()?->role === 'admin' ? 'admin' : 'staff';
    $physicalSearchUrl = route($physicalRoutePrefix . '.book-copies.search-books');
    $physicalNextAccessionUrl = route($physicalRoutePrefix . '.book-copies.next-accession');
    $physicalStoreUrl = route($physicalRoutePrefix . '.book-copies.store-selected');
    $physicalFixedBook = $physicalFixedBook ?? null;
@endphp

<style>
    .physical-book-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .physical-book-results { margin-top: 6px; border: 1px solid #dbe3ef; border-radius: 8px; overflow: hidden; background: #fff; max-height: 260px; overflow-y: auto; }
    .physical-book-result { display: block; width: 100%; border: 0; border-bottom: 1px solid #eef2f7; background: transparent; padding: 10px 12px; text-align: left; cursor: pointer; }
    .physical-book-result:last-child { border-bottom: 0; }
    .physical-book-result:hover, .physical-book-result:focus { background: #eff6ff; outline: none; }
    .physical-book-result-title { display: block; color: #111827; font-weight: 700; }
    .physical-book-result-meta { display: block; margin-top: 3px; color: #64748b; font-size: 12px; }
    .physical-book-help { margin: 5px 0 0; color: #64748b; font-size: 12px; }
    .physical-book-message { margin: 10px 0 0; border-radius: 7px; padding: 9px 10px; font-size: 13px; }
    .physical-book-message.error { color: #991b1b; background: #fef2f2; }
    .physical-book-message.success { color: #166534; background: #f0fdf4; }
    .physical-book-message.info { color: #1e40af; background: #eff6ff; }
    .physical-book-selected { margin-top: 8px; border-radius: 7px; padding: 9px 10px; background: #f8fafc; color: #334155; font-size: 13px; }
    .physical-book-preview { margin-top: 14px; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px; background: #eff6ff; color: #1e3a8a; }
    .physical-book-preview-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 18px; margin-top: 8px; }
    .physical-book-preview-label { display: block; font-size: 11px; color: #64748b; }
    .physical-book-preview-value { display: block; margin-top: 2px; font-weight: 700; color: #1e3a8a; }
    .physical-field-error { display: block; min-height: 17px; margin-top: 4px; color: #dc2626; font-size: 12px; line-height: 1.35; }
    .physical-invalid { border-color: #dc2626 !important; box-shadow: 0 0 0 3px rgba(220, 38, 38, .12) !important; }
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, .5); z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
    .modal-overlay.active { display: flex; }
    .modal { width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; border-radius: 16px; animation: physicalModalSlideIn .3s ease; }
    @keyframes physicalModalSlideIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
    body.light-theme .modal { background: #fff; box-shadow: 0 10px 25px rgba(0, 0, 0, .2); }
    body.dark-theme .modal { background: #1e293b; box-shadow: 0 10px 25px rgba(0, 0, 0, .4); }
    .modal-header { display: flex; align-items: center; justify-content: space-between; padding: 24px; border-bottom: 1px solid #e5e7eb; }
    body.dark-theme .modal-header { border-color: #334155; }
    .modal-title { font-size: 20px; font-weight: 600; }
    .modal-close-btn { width: 36px; height: 36px; border: 0; border-radius: 8px; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #64748b; }
    .modal-close-btn:hover { background: #f3f4f6; }
    .modal-body { padding: 24px; }
    .modal-description { margin-bottom: 24px; font-size: 14px; color: #64748b; }
    body.dark-theme .modal-description { color: #94a3b8; }
    .modal-footer { display: flex; justify-content: flex-end; gap: 12px; padding: 20px 24px; border-top: 1px solid #e5e7eb; }
    body.dark-theme .modal-footer { border-color: #334155; }
    .form-group { position: relative; margin-bottom: 20px; }
    .form-label { display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px; }
    .required::after { content: '*'; color: #ef4444; margin-left: 4px; }
    .form-control { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
    body.dark-theme .form-control { background: #1e293b; border-color: #475569; color: #e2e8f0; }
    .form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, .1); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 14px; border: 1px solid transparent; border-radius: 7px; cursor: pointer; font-size: 14px; }
    .btn-primary { background: #2563eb; color: #fff; }
    .btn-outline { background: #fff; color: #2563eb; border-color: #2563eb; }
    @media (max-width: 640px) { .form-row { grid-template-columns: 1fr; } .modal-overlay { padding: 12px; } .modal-header, .modal-body { padding: 18px; } .modal-footer { padding: 16px 18px; } }
</style>

<div id="addPhysicalBookModal" class="modal-overlay" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="addPhysicalBookTitle">
        <div class="modal-header">
            <h3 class="modal-title" id="addPhysicalBookTitle">{{ $physicalModalTitle ?? 'Add Physical Book' }}</h3>
            <button class="modal-close-btn" id="closeAddPhysicalBookModal" type="button" aria-label="Close add physical book dialog"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p class="modal-description">{{ $physicalModalDescription ?? 'Select one existing book title, then generate and save its borrowing and reference copies.' }}</p>
            <form id="addPhysicalBookForm" novalidate>
                <div class="form-group">
                    <label class="form-label required" for="physicalBookSearch">Book Name</label>
                    <input type="text" id="physicalBookSearch" class="form-control" autocomplete="off" placeholder="Search by book name, ISBN, or author..." value="{{ $physicalFixedBook?->title }}" @readonly($physicalFixedBook) required>
                    <input type="hidden" name="book_id" id="physicalBookId" value="{{ $physicalFixedBook?->id }}">
                    <div id="physicalBookResults" class="physical-book-results" hidden></div>
                    <p class="physical-book-help">{{ $physicalFixedBook ? 'Copies will be added to this book.' : 'Select an existing book. A typed title cannot create a relationship.' }}</p>
                    <div id="physicalBookSelected" class="physical-book-selected" @if(!$physicalFixedBook) hidden @endif>{{ $physicalFixedBook ? 'Selected book ID: ' . $physicalFixedBook->id . ' · ' . $physicalFixedBook->title : '' }}</div>
                    <small id="physicalBookSearchError" class="physical-field-error" role="alert"></small>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label" for="physicalPrice">Price</label><input type="number" name="price" id="physicalPrice" class="form-control" min="0" max="99999999.99" step="0.01" placeholder="0.00"><small id="physicalPriceError" class="physical-field-error" role="alert"></small></div>
                    <div class="form-group"><label class="form-label required" for="physicalTotalCopies">Total Number of Copies</label><input type="number" name="total_copies" id="physicalTotalCopies" class="form-control" min="1" max="500" step="1" placeholder="10" required><small id="physicalTotalCopiesError" class="physical-field-error" role="alert"></small></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label required" for="physicalBorrowingCopies">Copies for Borrowing</label><input type="number" name="borrowing_copies" id="physicalBorrowingCopies" class="form-control" min="0" max="500" step="1" placeholder="8" required><small id="physicalBorrowingCopiesError" class="physical-field-error" role="alert"></small></div>
                    <div class="form-group"><label class="form-label required" for="physicalReferenceCopies">Copies for Reference</label><input type="number" name="reference_copies" id="physicalReferenceCopies" class="form-control" min="0" max="500" step="1" placeholder="2" required><small id="physicalReferenceCopiesError" class="physical-field-error" role="alert"></small></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label required" for="physicalEntryDate">Entry Date</label><input type="date" name="entry_date" id="physicalEntryDate" class="form-control" value="{{ today()->toDateString() }}" max="{{ today()->toDateString() }}" data-today-label="{{ today()->format('d-M-Y') }}" required><small id="physicalEntryDateError" class="physical-field-error" role="alert"></small></div>
                    <div class="form-group"><label class="form-label" for="physicalShelfLocation">Shelf Location</label><input type="text" name="shelf_location" id="physicalShelfLocation" class="form-control" placeholder="A-12" maxlength="100"><small id="physicalShelfLocationError" class="physical-field-error" role="alert"></small></div>
                </div>
                <div class="form-group"><label class="form-label required" for="physicalCondition">Condition</label><select name="condition" id="physicalCondition" class="form-control" required><option value="">Select condition</option><option value="new">New</option><option value="good" selected>Good</option><option value="fair">Fair</option><option value="damaged">Damaged</option></select><small id="physicalConditionError" class="physical-field-error" role="alert"></small></div>
                <div class="form-group"><label class="form-label" for="physicalRemarks">Remarks</label><textarea name="remarks" id="physicalRemarks" class="form-control" rows="3" maxlength="1000" placeholder="Optional remarks"></textarea><small id="physicalRemarksError" class="physical-field-error" role="alert"></small></div>
                <div id="physicalBookPreview" class="physical-book-preview" hidden>
                    <strong>Copies to be created</strong>
                    <div class="physical-book-preview-grid">
                        <div><span class="physical-book-preview-label">Book</span><span id="previewBookName" class="physical-book-preview-value"></span></div>
                        <div><span class="physical-book-preview-label">Total Copies</span><span id="previewTotalCopies" class="physical-book-preview-value"></span></div>
                        <div><span class="physical-book-preview-label">Borrowing Copies</span><span id="previewBorrowingCopies" class="physical-book-preview-value"></span></div>
                        <div><span class="physical-book-preview-label">Reference Copies</span><span id="previewReferenceCopies" class="physical-book-preview-value"></span></div>
                        <div><span class="physical-book-preview-label">Accession From</span><span id="previewAccessionFrom" class="physical-book-preview-value"></span></div>
                        <div><span class="physical-book-preview-label">Accession To</span><span id="previewAccessionTo" class="physical-book-preview-value"></span></div>
                    </div>
                </div>
                <div id="physicalBookMessage" class="physical-book-message" hidden></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="cancelAddPhysicalBook" type="button">Cancel</button>
            <button class="btn btn-primary" id="generatePhysicalBookCopies" type="button"><i class="fas fa-wand-magic-sparkles"></i> Generate Copies</button>
            <button class="btn btn-primary" id="confirmAddPhysicalBook" type="submit" form="addPhysicalBookForm" hidden><i class="fas fa-check"></i> Confirm &amp; Save</button>
        </div>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('addPhysicalBookModal');
    if (!modal || modal.dataset.initialized === 'true') return;
    modal.dataset.initialized = 'true';
    const form = document.getElementById('addPhysicalBookForm'), search = document.getElementById('physicalBookSearch'), bookId = document.getElementById('physicalBookId'), results = document.getElementById('physicalBookResults'), selected = document.getElementById('physicalBookSelected'), previewPanel = document.getElementById('physicalBookPreview'), message = document.getElementById('physicalBookMessage'), generate = document.getElementById('generatePhysicalBookCopies'), confirm = document.getElementById('confirmAddPhysicalBook');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content, searchUrl = @json($physicalSearchUrl), nextAccessionUrl = @json($physicalNextAccessionUrl), storeUrl = @json($physicalStoreUrl);
    const fixedBook = @json($physicalFixedBook ? ['book_id' => $physicalFixedBook->id, 'title' => $physicalFixedBook->title] : null);
    let searchTimer, selectedBook = fixedBook;
    const field = (id) => document.getElementById(id);
    const errorIds = { physicalBookSearch: 'physicalBookSearchError', physicalTotalCopies: 'physicalTotalCopiesError', physicalBorrowingCopies: 'physicalBorrowingCopiesError', physicalReferenceCopies: 'physicalReferenceCopiesError', physicalPrice: 'physicalPriceError', physicalEntryDate: 'physicalEntryDateError', physicalShelfLocation: 'physicalShelfLocationError', physicalCondition: 'physicalConditionError', physicalRemarks: 'physicalRemarksError' };
    const setFieldError = (id, text = '') => { const input = field(id), error = field(errorIds[id] || `${id}Error`); if (error) error.textContent = text; if (input) input.classList.toggle('physical-invalid', Boolean(text)); };
    const clearFieldErrors = () => Object.keys(errorIds).forEach((id) => setFieldError(id));
    const setMessage = (text, type = 'info') => { message.textContent = text || ''; message.className = `physical-book-message ${type}`; message.hidden = !text; if (text && fixedBook && ['success', 'error'].includes(type)) window.bookCopiesFeedback?.showToast(type, type === 'error' ? 'Action failed' : 'Ready to save', text); };
    const hidePreview = () => { previewPanel.hidden = true; confirm.hidden = true; generate.hidden = false; };
    const close = () => { modal.classList.remove('active'); modal.setAttribute('aria-hidden', 'true'); };
    const reset = () => { form.reset(); field('physicalCondition').value = 'good'; bookId.value = fixedBook?.book_id || ''; search.value = fixedBook?.title || ''; results.innerHTML = ''; results.hidden = true; selectedBook = fixedBook; selected.textContent = fixedBook ? `Selected book ID: ${fixedBook.book_id} · ${fixedBook.title}` : ''; selected.hidden = !fixedBook; clearFieldErrors(); hidePreview(); setMessage(''); generate.disabled = false; confirm.disabled = false; };
    const showResults = (books, hasBooks, term) => {
        results.innerHTML = '';
        if (!hasBooks) { results.hidden = false; results.innerHTML = '<div class="physical-book-message info" style="margin:0;">No books available. Please add a new book first.<br><button type="button" class="btn btn-primary" id="physicalAddNewBookBtn" style="margin-top:8px;">Add New Book</button></div>'; document.getElementById('physicalAddNewBookBtn')?.addEventListener('click', () => { close(); document.getElementById('addBookBtn')?.click(); }); return; }
        if (!term) { results.hidden = false; results.innerHTML = '<div class="physical-book-message info" style="margin:0;">Type a book name, ISBN, or author to search existing books.</div>'; return; }
        if (!books.length) { results.hidden = false; results.innerHTML = '<div class="physical-book-message info" style="margin:0;">No books found</div>'; return; }
        books.forEach((book) => { const option = document.createElement('button'); option.type = 'button'; option.className = 'physical-book-result'; option.innerHTML = '<span class="physical-book-result-title"></span><span class="physical-book-result-meta"></span>'; option.querySelector('.physical-book-result-title').textContent = book.title; option.querySelector('.physical-book-result-meta').textContent = `ISBN: ${book.isbn || 'N/A'} · Author: ${book.author || 'N/A'}`; option.addEventListener('click', () => selectBook(book)); results.appendChild(option); });
        results.hidden = false;
    };
    const searchBooks = async () => { const term = search.value.trim(); try { const response = await fetch(`${searchUrl}?q=${encodeURIComponent(term)}`, { headers: { 'Accept': 'application/json' } }); const data = await response.json(); if (!response.ok) throw new Error(data.message || 'Unable to search books.'); showResults(data.data || [], Boolean(data.has_books), term); } catch (error) { showResults([], true, ''); setMessage(error.message || 'Unable to search books.', 'error'); } };
    const selectBook = (book) => { selectedBook = book; bookId.value = book.book_id; search.value = book.title; selected.textContent = `Selected book ID: ${book.book_id} · ${book.title}`; selected.hidden = false; results.hidden = true; setFieldError('physicalBookSearch'); hidePreview(); setMessage(''); };
    const validateCounts = () => {
        const totalRaw = field('physicalTotalCopies').value, borrowingRaw = field('physicalBorrowingCopies').value, referenceRaw = field('physicalReferenceCopies').value;
        const total = Number(totalRaw), borrowing = Number(borrowingRaw), reference = Number(referenceRaw);
        let valid = true;
        setFieldError('physicalTotalCopies', !totalRaw ? 'Enter the total number of copies.' : (!Number.isInteger(total) || total < 1 || total > 500 ? 'Total copies must be a whole number from 1 to 500.' : ''));
        setFieldError('physicalBorrowingCopies', !borrowingRaw ? 'Enter the number of borrowing copies.' : (!Number.isInteger(borrowing) || borrowing < 0 || borrowing > 500 ? 'Borrowing copies must be a whole number from 0 to 500.' : ''));
        setFieldError('physicalReferenceCopies', !referenceRaw ? 'Enter the number of reference copies.' : (!Number.isInteger(reference) || reference < 0 || reference > 500 ? 'Reference copies must be a whole number from 0 to 500.' : ''));
        valid = !field('physicalTotalCopiesError').textContent && !field('physicalBorrowingCopiesError').textContent && !field('physicalReferenceCopiesError').textContent;
        if (valid && total !== borrowing + reference) { setFieldError('physicalTotalCopies', 'Borrowing and Reference copies must equal the total number of copies.'); valid = false; }
        return valid;
    };
    const validateField = (id) => {
        if (id === 'physicalBookSearch') { if (!bookId.value || !selectedBook) { setFieldError(id, search.value.trim() ? 'Select a book from the search results.' : 'Select an existing book.'); return false; } setFieldError(id); return true; }
        if (['physicalTotalCopies', 'physicalBorrowingCopies', 'physicalReferenceCopies'].includes(id)) return validateCounts();
        const value = field(id).value;
        let error = '';
        if (id === 'physicalPrice' && value !== '' && (!Number.isFinite(Number(value)) || Number(value) < 0 || Number(value) > 99999999.99)) error = 'Price must be a valid amount from 0 to 99,999,999.99.';
        if (id === 'physicalEntryDate' && (!value || value > field(id).max)) error = !value ? 'Select an entry date.' : `Entry date cannot be later than ${field(id).dataset.todayLabel}.`;
        if (id === 'physicalShelfLocation' && value.length > 100) error = 'Shelf location must be 100 characters or fewer.';
        if (id === 'physicalCondition' && !['new', 'good', 'fair', 'damaged'].includes(value)) error = 'Select a condition.';
        if (id === 'physicalRemarks' && value.length > 1000) error = 'Remarks must be 1,000 characters or fewer.';
        setFieldError(id, error);
        return !error;
    };
    const validateAllFields = () => ['physicalBookSearch', 'physicalTotalCopies', 'physicalBorrowingCopies', 'physicalReferenceCopies', 'physicalPrice', 'physicalEntryDate', 'physicalShelfLocation', 'physicalCondition', 'physicalRemarks'].map(validateField).every(Boolean);
    const applyServerErrors = (errors) => { const map = { book_id: 'physicalBookSearch', total_copies: 'physicalTotalCopies', borrowing_copies: 'physicalBorrowingCopies', reference_copies: 'physicalReferenceCopies', price: 'physicalPrice', entry_date: 'physicalEntryDate', shelf_location: 'physicalShelfLocation', condition: 'physicalCondition', remarks: 'physicalRemarks' }; const first = Object.entries(errors || {})[0]; if (!first) return false; const details = Array.isArray(first[1]) ? first[1][0] : first[1]; setFieldError(map[first[0]] || first[0], details || 'Please check this field.'); setMessage('Please correct the highlighted field.', 'error'); return true; };
    const open = async (event) => {
        reset();
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        if (!fixedBook) {
            if (event?.detail?.book?.book_id) {
                selectBook(event.detail.book);
                field('physicalTotalCopies').focus();
            } else {
                await searchBooks();
                search.focus();
            }
        }
    };
    modal.addEventListener('physical-book:open', open);
    const generatePreview = async () => { setMessage(''); if (!validateAllFields()) { setMessage('Please correct the highlighted fields.', 'error'); return; } generate.disabled = true; try { const params = new URLSearchParams({ book_id: bookId.value, total_copies: field('physicalTotalCopies').value, borrowing_copies: field('physicalBorrowingCopies').value, reference_copies: field('physicalReferenceCopies').value }); const response = await fetch(`${nextAccessionUrl}?${params.toString()}`, { headers: { 'Accept': 'application/json' } }); const data = await response.json().catch(() => ({})); if (!response.ok) { applyServerErrors(data.errors); throw new Error(data.message || 'Unable to generate accession numbers.'); } const preview = data.data; field('previewBookName').textContent = preview.book.title; field('previewTotalCopies').textContent = preview.total_copies; field('previewBorrowingCopies').textContent = preview.borrowing_copies; field('previewReferenceCopies').textContent = preview.reference_copies; field('previewAccessionFrom').textContent = preview.accession_from; field('previewAccessionTo').textContent = preview.accession_to; previewPanel.hidden = false; generate.hidden = true; confirm.hidden = false; setMessage('Accession numbers generated. Confirm to save the complete batch.', 'success'); } catch (error) { generate.disabled = false; if (!message.textContent) setMessage(error.message || 'Unable to generate accession numbers.', 'error'); } };
    const saveBatch = async () => { if (!validateAllFields()) { setMessage('Please correct the highlighted fields.', 'error'); return; } confirm.disabled = true; setMessage('Saving physical copies...', 'info'); try { const response = await fetch(storeUrl, { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: new FormData(form) }); const data = await response.json().catch(() => ({})); if (!response.ok) { const hasFieldErrors = applyServerErrors(data.errors); throw new Error(data.message || (!hasFieldErrors ? Object.values(data.errors || {}).flat()[0] : '') || 'Unable to save physical copies.'); } const range = data.data?.accession_range || {}; const successText = `${data.message} Accession From: ${range.from}. Accession To: ${range.to}. All copies are Available.`; if (fixedBook && window.bookCopiesFeedback) { sessionStorage.setItem(`book-copies-toast:${window.location.pathname}`, JSON.stringify({ title: 'Copies added', message: successText })); window.location.reload(); } else { setMessage(successText, 'success'); window.setTimeout(() => window.location.reload(), 900); } } catch (error) { confirm.disabled = false; if (!message.classList.contains('error')) setMessage(error.message || 'Unable to save physical copies.', 'error'); } };
    const bindOpenButton = () => document.getElementById('addPhysicalBookBtn')?.addEventListener('click', open);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bindOpenButton, { once: true }); else bindOpenButton();
    document.getElementById('closeAddPhysicalBookModal')?.addEventListener('click', close); document.getElementById('cancelAddPhysicalBook')?.addEventListener('click', close); modal.addEventListener('click', (event) => { if (event.target === modal) close(); });
    search.addEventListener('input', () => { bookId.value = ''; selectedBook = null; selected.hidden = true; hidePreview(); setFieldError('physicalBookSearch'); setMessage(''); clearTimeout(searchTimer); searchTimer = setTimeout(searchBooks, 250); }); search.addEventListener('focus', () => { setFieldError('physicalBookSearch'); setMessage(''); if (!results.innerHTML) searchBooks(); }); form.querySelectorAll('input, select, textarea').forEach((input) => { if (input.id === 'physicalBookId') return; input.addEventListener('focus', () => { setFieldError(input.id); setMessage(''); }); input.addEventListener('input', () => { if (input.id !== 'physicalBookSearch') hidePreview(); setFieldError(input.id); setMessage(''); }); input.addEventListener('change', () => { if (input.id !== 'physicalBookSearch') hidePreview(); setFieldError(input.id); setMessage(''); }); input.addEventListener('blur', () => validateField(input.id)); }); form.addEventListener('submit', (event) => { event.preventDefault(); if (!confirm.hidden) saveBatch(); }); generate.addEventListener('click', generatePreview);
})();
</script>
