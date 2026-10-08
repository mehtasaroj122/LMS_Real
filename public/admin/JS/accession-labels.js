document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-accession-page]');
    const config = window.accessionLabelConfig;
    if (!root || !config) return;

    const $ = (selector, scope = root) => scope.querySelector(selector);
    const $$ = (selector, scope = root) => [...scope.querySelectorAll(selector)];
    const feedback = typeof window.ActionFeedbackUI === 'function' ? new window.ActionFeedbackUI() : null;
    const state = {
        method: 'quantity',
        generationController: null,
        preview: null,
        previewSignature: null,
        previewPage: 1,
        reprint: {
            phase: 'initial', books: [], booksMeta: null, copies: [], copiesMeta: null, selectedBook: null,
            matchedAccession: null, selected: new Set(), searchController: null, copyController: null, requestId: 0,
        },
        modal: { ids: [], returnFocus: null },
    };

    const elements = {
        generateForm: $('[data-generate-form]'),
        generateButton: $('[data-generate-button]'),
        generateError: $('[data-generate-error]'),
        preview: $('[data-generated-preview]'),
        previewLoading: $('[data-generated-loading]'),
        previewStale: $('[data-preview-stale]'),
        searchForm: $('[data-search-form]'),
        searchError: $('[data-search-error]'),
        bookResults: $('[data-book-results]'),
        copyResults: $('[data-copy-results]'),
        selectedBookHeader: $('[data-selected-book-header]'),
        resultsBody: $('[data-results-body]'),
        resultsTable: $('[data-results-table]'),
        resultsLoading: $('[data-results-loading]'),
        resultsLoadingText: $('[data-results-loading-text]'),
        resultsEmpty: $('[data-results-empty]'),
        resultsAnnouncement: $('[data-results-announcement]'),
        resultToolbar: $('[data-result-toolbar]'),
        pagination: $('[data-pagination]'),
        settings: $('[data-print-settings]'),
        settingsCard: $('[data-shared-print-settings]'),
        modal: $('[data-preview-modal]'),
        modalLabels: $('[data-modal-labels]'),
        modalSummary: $('[data-modal-summary]'),
    };

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
    })[character]);

    const toast = (type, title, message) => {
        if (feedback) feedback.showToast(type, title, message);
        else if (type === 'error') window.alert(message);
    };

    const refreshIcons = () => window.lucide?.createIcons?.();

    const scrollWithinWorkspace = (target) => {
        const scroller = target?.closest('.pwa-shell-content');
        if (!target || !scroller) return;

        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const scrollerRect = scroller.getBoundingClientRect();
        const targetRect = target.getBoundingClientRect();
        const top = scroller.scrollTop + targetRect.top - scrollerRect.top;

        scroller.scrollTo({ top: Math.max(0, top), behavior: reduceMotion ? 'auto' : 'smooth' });
    };

    const fetchJson = async (url, options = {}) => {
        const response = await fetch(url, {
            ...options,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.body ? { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrf } : {}),
                ...(options.headers || {}),
            },
        });
        const data = await response.json().catch(() => ({ success: false, message: 'The server returned an invalid response.' }));
        if (!response.ok || data.success === false) {
            const error = new Error(data.message || 'The request could not be completed.');
            error.errors = data.errors || {};
            throw error;
        }
        return data;
    };

    const parseAccession = (value) => {
        const match = /^ACC-(\d{6})$/i.exec(String(value || '').trim());
        if (!match) return null;
        const number = Number(match[1]);
        return number >= 1 && number <= 999999 ? number : null;
    };
    const formatAccession = (number) => Number.isInteger(number) && number >= 1 && number <= 999999
        ? `ACC-${String(number).padStart(6, '0')}`
        : null;

    const generationPayload = () => {
        if (state.method === 'quantity') {
            return {
                method: 'quantity',
                start: $('#start').value.trim().toUpperCase(),
                quantity: Number($('#quantity').value),
                skip_existing: $('[name="skip_existing"]').checked,
            };
        }
        return {
            method: 'range',
            from: $('#from').value.trim().toUpperCase(),
            to: $('#to').value.trim().toUpperCase(),
        };
    };

    const generationSignature = () => JSON.stringify(generationPayload());

    const updateCalculations = () => {
        const start = parseAccession($('#start').value);
        const quantity = Number($('#quantity').value);
        const calculated = start && Number.isInteger(quantity) && quantity > 0 ? formatAccession(start + quantity - 1) : null;
        $('[data-calculated-end]').textContent = calculated || '—';
        $$('[data-quick-quantity]').forEach((button) => {
            const active = Number(button.dataset.quickQuantity) === quantity;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', String(active));
        });

        const from = parseAccession($('#from').value);
        const to = parseAccession($('#to').value);
        $('[data-range-total]').textContent = from && to && to >= from ? String(to - from + 1) : '—';
    };

    const markPreviewStale = () => {
        updateCalculations();
        if (!state.preview || generationSignature() === state.previewSignature) return;
        elements.previewStale.hidden = false;
        elements.preview.classList.add('is-stale');
        $$('[data-print-generated]', elements.preview).forEach((button) => { button.disabled = true; });
    };

    const setMethod = (method) => {
        state.method = method;
        $$('[data-method-panel]').forEach((panel) => { panel.hidden = panel.dataset.methodPanel !== method; });
        $$('.accession-method-option').forEach((option) => option.classList.toggle('is-selected', option.querySelector('input').value === method));
        markPreviewStale();
    };

    $$('[name="method"]').forEach((radio) => radio.addEventListener('change', () => setMethod(radio.value)));
    $$('#start, #quantity, #from, #to, [name="skip_existing"]').forEach((input) => input.addEventListener('input', markPreviewStale));
    $$('[data-quick-quantity]').forEach((button) => button.addEventListener('click', () => {
        $('#quantity').value = button.dataset.quickQuantity;
        markPreviewStale();
        $('#quantity').focus();
    }));

    $('[data-use-next]').addEventListener('click', async (event) => {
        const button = event.currentTarget;
        button.disabled = true;
        const original = button.textContent;
        button.textContent = 'Refreshing...';
        try {
            const data = await fetchJson(config.next);
            $('[data-next-accession]').textContent = data.next_accession;
            if (state.method === 'quantity') $('#start').value = data.next_accession;
            else $('#from').value = data.next_accession;
            markPreviewStale();
        } catch (error) {
            toast('error', 'Unable to refresh', error.message);
        } finally {
            button.disabled = false;
            button.textContent = original;
        }
    });

    const clearFieldErrors = () => {
        elements.generateError.hidden = true;
        elements.generateError.textContent = '';
        $$('.accession-field input').forEach((input) => input.classList.remove('is-invalid'));
        $$('[data-field-error]').forEach((help) => help.classList.remove('is-error'));
    };

    const displayGenerateError = (error) => {
        elements.generateError.textContent = error.message;
        elements.generateError.hidden = false;
        Object.keys(error.errors || {}).forEach((field) => {
            const input = $(`#${CSS.escape(field)}`);
            input?.classList.add('is-invalid');
            const help = $(`[data-field-error="${CSS.escape(field)}"]`);
            if (help) { help.textContent = error.errors[field][0]; help.classList.add('is-error'); }
        });
    };

    elements.generateForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFieldErrors();
        state.generationController?.abort();
        state.generationController = new AbortController();
        elements.generateButton.disabled = true;
        elements.generateButton.querySelector('span').textContent = 'Generating...';
        elements.preview.hidden = true;
        elements.previewLoading.hidden = false;
        elements.generateForm.setAttribute('aria-busy', 'true');
        elements.previewStale.hidden = true;
        try {
            const payload = generationPayload();
            const data = await fetchJson(config.preview, { method: 'POST', body: JSON.stringify(payload), signal: state.generationController.signal });
            state.preview = { ...data, selected: new Set(data.labels.map((label) => label.accession)) };
            state.previewSignature = JSON.stringify(payload);
            state.previewPage = 1;
            renderGeneratedPreview();
            elements.preview.hidden = false;
            elements.preview.classList.remove('is-stale');
            toast('success', 'Preview generated', `${data.generated_count} printable labels are ready for review.`);
            scrollWithinWorkspace(elements.preview);
        } catch (error) {
            if (error.name !== 'AbortError') displayGenerateError(error);
        } finally {
            elements.previewLoading.hidden = true;
            elements.generateForm.setAttribute('aria-busy', 'false');
            elements.generateButton.disabled = false;
            elements.generateButton.querySelector('span').textContent = 'Generate Preview';
        }
    });

    const renderGeneratedPreview = () => {
        const preview = state.preview;
        if (!preview) return;
        const totalPages = Math.max(1, Math.ceil(preview.labels.length / config.previewPageSize));
        state.previewPage = Math.min(state.previewPage, totalPages);
        const startIndex = (state.previewPage - 1) * config.previewPageSize;
        const pageLabels = preview.labels.slice(startIndex, startIndex + config.previewPageSize);
        const selectedCount = preview.selected.size;
        const stale = generationSignature() !== state.previewSignature;
        const skippedMarkup = preview.skipped.length ? `
            <details class="accession-skipped">
                <summary>Skipped Accession Numbers (${preview.skipped.length})</summary>
                <div>${preview.skipped.map((accession) => `<span><strong>${escapeHtml(accession)}</strong> — Already exists</span>`).join('')}</div>
            </details>` : '';
        const emptyMarkup = preview.generated_count === 0 ? `
            <div class="accession-empty"><i data-lucide="badge-x" aria-hidden="true"></i><strong>No printable labels available</strong><p>All ${preview.requested_quantity} requested accession numbers already exist.</p></div>` : '';

        elements.preview.innerHTML = `
            <div class="accession-preview-header">
                <div><span class="accession-kicker">Generation summary</span><h2>${preview.generated_count} printable ${preview.generated_count === 1 ? 'label' : 'labels'}</h2><p>${escapeHtml(preview.start)} → ${escapeHtml(preview.last_scanned)} · ${preview.scanned_count} sequence numbers scanned</p></div>
                <span class="accession-limit">${preview.skipped_count} unavailable</span>
            </div>
            <div class="accession-summary">
                <div><span>Requested</span><strong>${preview.requested_quantity}</strong></div>
                <div><span>Printable</span><strong>${preview.generated_count}</strong></div>
                <div><span>Already Existing</span><strong>${preview.skipped_count}</strong></div>
                <div><span>Selected</span><strong data-selected-count>${selectedCount}</strong></div>
            </div>
            ${preview.generated_count ? `
                <div class="accession-preview-toolbar">
                    <div class="accession-preview-selection"><button type="button" data-select-generated-all>Select All</button><button type="button" data-clear-generated>Clear Selection</button><strong><span data-selected-toolbar>${selectedCount}</span> selected</strong></div>
                    <div class="accession-print-controls"><button type="button" class="accession-button accession-button-secondary" data-print-generated="selected" ${selectedCount && !stale ? '' : 'disabled'}><i data-lucide="list-checks"></i> Print Selected</button><button type="button" class="accession-button accession-button-primary" data-print-generated="all" ${stale ? 'disabled' : ''}><i data-lucide="printer"></i> Print All</button></div>
                </div>
                <div class="accession-preview-grid">
                    ${pageLabels.map((label) => `
                        <label class="accession-label-preview ${preview.selected.has(label.accession) ? 'is-selected' : ''}">
                            <input type="checkbox" data-generated-accession="${escapeHtml(label.accession)}" ${preview.selected.has(label.accession) ? 'checked' : ''}>
                            <span class="accession-checkmark" aria-hidden="true"><i data-lucide="check"></i></span>
                            <span class="accession-barcode">${label.svg}</span><strong>${escapeHtml(label.accession)}</strong>
                            <span class="sr-only">Select ${escapeHtml(label.accession)} for printing</span>
                        </label>`).join('')}
                </div>
                <div class="accession-preview-pager"><button type="button" data-preview-page="${state.previewPage - 1}" ${state.previewPage === 1 ? 'disabled' : ''}>Previous</button><span>Showing ${startIndex + 1}–${Math.min(startIndex + config.previewPageSize, preview.labels.length)} of ${preview.labels.length}</span><button type="button" data-preview-page="${state.previewPage + 1}" ${state.previewPage === totalPages ? 'disabled' : ''}>Next</button></div>` : emptyMarkup}
            ${skippedMarkup}`;
        refreshIcons();
    };

    elements.preview.addEventListener('change', (event) => {
        const checkbox = event.target.closest('[data-generated-accession]');
        if (!checkbox || !state.preview) return;
        if (checkbox.checked) state.preview.selected.add(checkbox.dataset.generatedAccession);
        else state.preview.selected.delete(checkbox.dataset.generatedAccession);
        renderGeneratedPreview();
    });
    elements.preview.addEventListener('click', (event) => {
        const pageButton = event.target.closest('[data-preview-page]');
        if (pageButton && !pageButton.disabled) { state.previewPage = Number(pageButton.dataset.previewPage); renderGeneratedPreview(); return; }
        if (event.target.closest('[data-select-generated-all]')) { state.preview.labels.forEach((label) => state.preview.selected.add(label.accession)); renderGeneratedPreview(); return; }
        if (event.target.closest('[data-clear-generated]')) { state.preview.selected.clear(); renderGeneratedPreview(); return; }
        const printButton = event.target.closest('[data-print-generated]');
        if (printButton) prepareGeneratedPrint(printButton.dataset.printGenerated, printButton);
    });

    $('[data-reset-generation]').addEventListener('click', () => {
        state.generationController?.abort();
        state.preview = null;
        state.previewSignature = null;
        state.previewPage = 1;
        elements.generateForm.reset();
        $('[name="method"][value="quantity"]').checked = true;
        $('#start').value = $('[data-next-accession]').textContent.trim();
        $('#from').value = $('#start').value;
        $('#quantity').value = '100';
        setMethod('quantity');
        updateCalculations();
        clearFieldErrors();
        elements.preview.hidden = true;
        elements.preview.innerHTML = '';
        elements.preview.classList.remove('is-stale');
        elements.previewStale.hidden = true;
    });

    const placePrintSettings = (workspace) => {
        const anchor = $(`[data-settings-anchor="${workspace}"]`);
        anchor?.insertAdjacentElement('afterend', elements.settingsCard);
        elements.settingsCard.hidden = workspace === 'reprint' && state.reprint.selected.size === 0;
    };

    const switchWorkspace = (workspace, focusTab = false) => {
        $$('[data-workspace-tab]').forEach((tab) => {
            const active = tab.dataset.workspaceTab === workspace;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-selected', String(active));
            tab.tabIndex = active ? 0 : -1;
            if (active && focusTab) tab.focus();
        });
        $$('[data-workspace-panel]').forEach((panel) => {
            const active = panel.dataset.workspacePanel === workspace;
            panel.hidden = !active;
            panel.classList.toggle('is-entering', active);
        });
        placePrintSettings(workspace);
    };
    $$('[data-workspace-tab]').forEach((tab) => {
        tab.addEventListener('click', () => switchWorkspace(tab.dataset.workspaceTab));
        tab.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
            event.preventDefault();
            switchWorkspace(tab.dataset.workspaceTab === 'generate' ? 'reprint' : 'generate', true);
        });
    });

    const resetReprintView = () => {
        state.reprint.searchController?.abort();
        state.reprint.copyController?.abort();
        state.reprint.requestId += 1;
        Object.assign(state.reprint, {
            phase: 'initial', books: [], booksMeta: null, copies: [], copiesMeta: null,
            selectedBook: null, matchedAccession: null, selected: new Set(),
        });
        elements.searchForm.reset();
        elements.searchError.hidden = true;
        elements.bookResults.hidden = true;
        elements.copyResults.hidden = true;
        elements.resultsTable.hidden = true;
        elements.resultToolbar.hidden = true;
        elements.pagination.hidden = true;
        elements.resultsLoading.hidden = true;
        $('[data-reprint-results]').setAttribute('aria-busy', 'false');
        elements.resultsEmpty.hidden = false;
        elements.resultsEmpty.innerHTML = '<i data-lucide="book-search"></i><strong>Search for a book or physical copy</strong><p>Enter a book title, ISBN, or accession number to find labels available for reprinting.</p>';
        elements.resultsAnnouncement.textContent = '';
        placePrintSettings('reprint');
        refreshIcons();
    };

    let searchTimer;
    $('#copySearch').addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        const term = $('#copySearch').value.trim();
        if (!term) { resetReprintView(); return; }
        searchTimer = window.setTimeout(() => searchBooks(1), 350);
    });
    elements.searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        if ($('#copySearch').value.trim()) searchBooks(1);
    });
    $('[data-reset-search]').addEventListener('click', resetReprintView);
    $$('#statusFilter, #typeFilter, #conditionFilter, #sortFilter, #entriesFilter').forEach((select) => select.addEventListener('change', () => {
        if (state.reprint.phase === 'copies' && state.reprint.selectedBook) loadBookCopies(1);
        else if ($('#copySearch').value.trim()) searchBooks(1);
    }));

    const beginResultsLoading = (message) => {
        elements.searchError.hidden = true;
        elements.resultsLoadingText.textContent = message;
        elements.resultsLoading.hidden = false;
        elements.resultsEmpty.hidden = true;
        elements.resultsTable.hidden = true;
        elements.resultToolbar.hidden = true;
        elements.pagination.hidden = true;
        $('[data-reprint-results]').setAttribute('aria-busy', 'true');
    };

    const searchBooks = async (page = 1) => {
        const term = $('#copySearch').value.trim();
        if (!term) { resetReprintView(); return; }
        if (state.reprint.phase === 'copies') {
            state.reprint.selected.clear();
            placePrintSettings('reprint');
        }
        state.reprint.searchController?.abort();
        state.reprint.searchController = new AbortController();
        const requestId = ++state.reprint.requestId;
        beginResultsLoading('Searching books...');
        elements.bookResults.hidden = true;
        elements.copyResults.hidden = true;
        const params = new URLSearchParams(new FormData(elements.searchForm));
        params.set('page', String(page));
        try {
            const data = await fetchJson(`${config.search}?${params.toString()}`, { signal: state.reprint.searchController.signal });
            if (requestId !== state.reprint.requestId) return;
            state.reprint.phase = 'books';
            state.reprint.books = data.data;
            state.reprint.booksMeta = data.meta;
            renderBookResults();
        } catch (error) {
            if (error.name === 'AbortError') return;
            elements.searchError.textContent = `${error.message} Try again.`;
            elements.searchError.hidden = false;
            elements.resultsEmpty.hidden = false;
            elements.resultsEmpty.innerHTML = '<i data-lucide="wifi-off"></i><strong>Unable to search books</strong><p>Please try again.</p>';
        } finally {
            if (requestId === state.reprint.requestId) {
                elements.resultsLoading.hidden = true;
                $('[data-reprint-results]').setAttribute('aria-busy', 'false');
            }
            refreshIcons();
        }
    };

    const renderBookResults = () => {
        const { books, booksMeta: meta } = state.reprint;
        elements.resultsLoading.hidden = true;
        elements.copyResults.hidden = true;
        elements.resultsTable.hidden = true;
        elements.resultToolbar.hidden = true;
        if (!books.length) {
            elements.bookResults.hidden = true;
            elements.resultsEmpty.hidden = false;
            elements.resultsEmpty.innerHTML = '<i data-lucide="search-x"></i><strong>No matching books or accession numbers found</strong><p>Try another book title, ISBN, or accession number.</p><button type="button" class="accession-button accession-button-reset" data-empty-reset>Reset Search</button>';
            elements.resultsAnnouncement.textContent = 'No matching books or accession numbers found.';
            elements.pagination.hidden = true;
            $('[data-empty-reset]')?.addEventListener('click', resetReprintView);
            refreshIcons();
            return;
        }
        elements.resultsEmpty.hidden = true;
        elements.bookResults.hidden = false;
        elements.bookResults.innerHTML = `<div class="accession-books-heading"><span class="accession-kicker">Matching books</span><strong>${meta.total} ${meta.total === 1 ? 'book' : 'books'} found</strong></div><div class="accession-book-results">${books.map((book) => `
            <button type="button" class="accession-book-result" data-select-book="${book.id}" data-matched-accession="${escapeHtml(book.matched_accession || '')}" aria-label="${book.matched_accession ? 'View copies of' : 'Select book'} ${escapeHtml(book.title)}">
                <span class="accession-book-result-icon"><i data-lucide="book-open" aria-hidden="true"></i></span>
                <span class="accession-book-result-copy"><strong>${escapeHtml(book.title)}</strong><span>${escapeHtml(book.author || 'Unknown author')}</span><small>ISBN: ${escapeHtml(book.isbn || '—')}${book.matched_accession ? ` · Matched copy: <b>${escapeHtml(book.matched_accession)}</b>` : ''}</small></span>
                <span class="accession-book-counts"><strong>${book.copies_count}</strong><span>copies</span><strong>${book.available_copies_count}</strong><span>available</span></span>
                <span class="accession-button accession-button-secondary" aria-hidden="true">${book.matched_accession ? 'View Copies' : 'Select Book'}</span>
            </button>`).join('')}</div>`;
        elements.resultsAnnouncement.textContent = `Showing ${meta.from}–${meta.to} of ${meta.total} matching books.`;
        renderPagination(meta, 'books');
        refreshIcons();
    };

    const loadBookCopies = async (page = 1, book = state.reprint.selectedBook, matchedAccession = state.reprint.matchedAccession) => {
        if (!book) return;
        state.reprint.copyController?.abort();
        state.reprint.copyController = new AbortController();
        const requestId = ++state.reprint.requestId;
        state.reprint.selectedBook = book;
        state.reprint.matchedAccession = matchedAccession || null;
        beginResultsLoading('Loading physical copies...');
        elements.bookResults.hidden = true;
        elements.copyResults.hidden = true;
        const params = new URLSearchParams(new FormData(elements.searchForm));
        params.delete('q');
        params.set('page', String(page));
        if (state.reprint.matchedAccession) params.set('highlight', state.reprint.matchedAccession);
        const url = config.bookCopies.replace('__BOOK__', String(book.id));
        try {
            const data = await fetchJson(`${url}?${params.toString()}`, { signal: state.reprint.copyController.signal });
            if (requestId !== state.reprint.requestId) return;
            state.reprint.phase = 'copies';
            state.reprint.selectedBook = data.book;
            state.reprint.copies = data.data;
            state.reprint.copiesMeta = data.meta;
            renderCopyResults();
            scrollWithinWorkspace($('[data-reprint-results]'));
        } catch (error) {
            if (error.name === 'AbortError') return;
            elements.searchError.textContent = `${error.message} Try again.`;
            elements.searchError.hidden = false;
        } finally {
            if (requestId === state.reprint.requestId) {
                elements.resultsLoading.hidden = true;
                $('[data-reprint-results]').setAttribute('aria-busy', 'false');
            }
        }
    };

    const renderCopyResults = () => {
        const { copies, copiesMeta: meta, selected, selectedBook, matchedAccession } = state.reprint;
        elements.resultsLoading.hidden = true;
        elements.bookResults.hidden = true;
        elements.copyResults.hidden = false;
        elements.selectedBookHeader.innerHTML = `<div><strong>${escapeHtml(selectedBook.title)}</strong><span>${escapeHtml(selectedBook.author || 'Unknown author')} · ISBN ${escapeHtml(selectedBook.isbn || '—')}</span></div><p><strong>${selectedBook.copies_count}</strong> physical copies</p>`;
        if (!copies.length) {
            elements.resultsTable.hidden = true;
            elements.resultToolbar.hidden = true;
            elements.resultsEmpty.hidden = false;
            elements.resultsEmpty.innerHTML = '<i data-lucide="filter-x"></i><strong>No copies match these filters</strong><p>Adjust the status, condition, or book type filters.</p>';
            elements.pagination.hidden = true;
            return;
        }
        elements.resultsEmpty.hidden = true;
        elements.resultsTable.hidden = false;
        elements.resultToolbar.hidden = false;
        elements.resultsBody.innerHTML = copies.map((copy) => `
            <tr class="${selected.has(copy.id) ? 'is-selected' : ''} ${copy.accession === matchedAccession ? 'is-matched' : ''}">
                <td><label><input type="checkbox" data-copy-selection="${copy.id}" ${selected.has(copy.id) ? 'checked' : ''}><span class="sr-only">Select accession ${escapeHtml(copy.accession)}</span></label></td>
                <td><strong>${escapeHtml(copy.accession)}</strong>${copy.accession === matchedAccession ? '<span class="accession-match-badge">Matched search</span>' : ''}</td>
                <td><span class="accession-table-status" data-status="${escapeHtml(copy.status)}">${escapeHtml(copy.status)}</span></td><td>${escapeHtml(copy.condition || '—')}</td><td>${escapeHtml(copy.book_type || '—')}</td><td>${escapeHtml(copy.shelf_location || '—')}</td><td>${escapeHtml(copy.entry_date || '—')}</td>
                <td><button type="button" class="accession-button accession-button-secondary" data-preview-copy="${copy.id}"><i data-lucide="eye"></i> Preview</button></td>
            </tr>`).join('');
        elements.resultsAnnouncement.textContent = `Showing ${meta.from}–${meta.to} of ${meta.total} copies for ${selectedBook.title}.`;
        renderReprintToolbar();
        renderPagination(meta, 'copies');
        refreshIcons();
    };

    const renderReprintToolbar = () => {
        const selectedCount = state.reprint.selected.size;
        $('[data-reprint-selected-count]').textContent = String(selectedCount);
        const pageIds = state.reprint.copies.map((copy) => copy.id);
        const allPageSelected = pageIds.length > 0 && pageIds.every((id) => state.reprint.selected.has(id));
        const selectPage = $('[data-select-result-page]');
        selectPage.checked = allPageSelected;
        selectPage.indeterminate = !allPageSelected && pageIds.some((id) => state.reprint.selected.has(id));
        $('[data-preview-selected]').disabled = selectedCount === 0;
        $('[data-print-reprints]').disabled = selectedCount === 0;
        $('[data-clear-reprints]').disabled = selectedCount === 0;
        placePrintSettings('reprint');
    };

    const renderPagination = (meta, kind) => {
        const start = Math.max(1, meta.current_page - 2);
        const end = Math.min(meta.last_page, meta.current_page + 2);
        const pages = [];
        for (let page = start; page <= end; page += 1) pages.push(page);
        elements.pagination.hidden = false;
        elements.pagination.dataset.paginationKind = kind;
        elements.pagination.innerHTML = `<span>Showing ${meta.from}–${meta.to} of ${meta.total} ${kind}</span><div><button type="button" data-search-page="${meta.current_page - 1}" ${meta.current_page === 1 ? 'disabled' : ''}>Previous</button>${pages.map((page) => `<button type="button" data-search-page="${page}" class="${page === meta.current_page ? 'is-active' : ''}" ${page === meta.current_page ? 'aria-current="page"' : ''}>${page}</button>`).join('')}<button type="button" data-search-page="${meta.current_page + 1}" ${meta.current_page === meta.last_page ? 'disabled' : ''}>Next</button></div>`;
    };

    elements.bookResults.addEventListener('click', (event) => {
        const button = event.target.closest('[data-select-book]');
        if (!button) return;
        const book = state.reprint.books.find((candidate) => candidate.id === Number(button.dataset.selectBook));
        state.reprint.selected.clear();
        loadBookCopies(1, book, button.dataset.matchedAccession || null);
    });
    $('[data-back-to-books]').addEventListener('click', () => {
        state.reprint.phase = 'books';
        elements.copyResults.hidden = true;
        elements.resultsTable.hidden = true;
        elements.resultToolbar.hidden = true;
        state.reprint.selected.clear();
        renderBookResults();
        placePrintSettings('reprint');
    });
    elements.resultsBody.addEventListener('change', (event) => {
        const checkbox = event.target.closest('[data-copy-selection]');
        if (!checkbox) return;
        const id = Number(checkbox.dataset.copySelection);
        if (checkbox.checked && state.reprint.selected.size >= config.maximum && !state.reprint.selected.has(id)) {
            checkbox.checked = false;
            toast('warning', 'Selection limit reached', `Select up to ${config.maximum} accession numbers per print job.`);
        } else if (checkbox.checked) state.reprint.selected.add(id);
        else state.reprint.selected.delete(id);
        renderCopyResults();
    });
    elements.resultsBody.addEventListener('click', (event) => {
        const button = event.target.closest('[data-preview-copy]');
        if (button) previewExisting([Number(button.dataset.previewCopy)], button);
    });
    $('[data-select-result-page]').addEventListener('change', (event) => {
        if (event.target.checked) {
            state.reprint.copies.forEach((copy) => { if (state.reprint.selected.size < config.maximum) state.reprint.selected.add(copy.id); });
        } else state.reprint.copies.forEach((copy) => state.reprint.selected.delete(copy.id));
        renderCopyResults();
    });
    $('[data-clear-reprints]').addEventListener('click', () => { state.reprint.selected.clear(); renderCopyResults(); });
    $('[data-preview-selected]').addEventListener('click', (event) => previewExisting([...state.reprint.selected], event.currentTarget));
    $('[data-print-reprints]').addEventListener('click', (event) => prepareReprintPrint([...state.reprint.selected], event.currentTarget));
    elements.pagination.addEventListener('click', (event) => {
        const button = event.target.closest('[data-search-page]');
        if (!button || button.disabled) return;
        if (elements.pagination.dataset.paginationKind === 'books') searchBooks(Number(button.dataset.searchPage));
        else loadBookCopies(Number(button.dataset.searchPage));
    });

    const previewExisting = async (ids, trigger) => {
        if (!ids.length) return;
        trigger.disabled = true;
        try {
            const data = await fetchJson(config.existingPreview, { method: 'POST', body: JSON.stringify({ copy_ids: ids }) });
            state.modal.ids = data.labels.map((label) => label.id);
            state.modal.returnFocus = trigger;
            elements.modalSummary.textContent = `${data.labels.length} selected accession ${data.labels.length === 1 ? 'number' : 'numbers'}${data.missing_ids.length ? ` · ${data.missing_ids.length} no longer exists` : ''}`;
            elements.modalLabels.innerHTML = data.labels.map((label) => `<article class="accession-modal-label"><div class="accession-barcode">${label.svg}</div><strong>${escapeHtml(label.accession)}</strong><small>${escapeHtml(label.book_title)}</small></article>`).join('');
            openModal();
            if (data.missing_ids.length) toast('warning', 'Selection updated', `${data.missing_ids.length} selected copies no longer exist.`);
        } catch (error) {
            toast('error', 'Unable to prepare preview', error.message);
        } finally { trigger.disabled = false; }
    };

    const openModal = () => {
        elements.modal.classList.add('is-open');
        elements.modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('accession-modal-open');
        $('[data-close-preview]', elements.modal)?.focus();
        refreshIcons();
    };
    const closeModal = () => {
        elements.modal.classList.remove('is-open');
        elements.modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('accession-modal-open');
        state.modal.returnFocus?.focus();
    };
    $$('[data-close-preview]').forEach((button) => button.addEventListener('click', closeModal));
    elements.modal.addEventListener('click', (event) => { if (event.target === elements.modal) closeModal(); });
    document.addEventListener('keydown', (event) => {
        if (!elements.modal.classList.contains('is-open')) return;
        if (event.key === 'Escape') { closeModal(); return; }
        if (event.key !== 'Tab') return;
        const focusable = $$('button:not([disabled]), [href], input:not([disabled]), select:not([disabled])', elements.modal);
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });
    $('[data-modal-print]').addEventListener('click', (event) => prepareReprintPrint(state.modal.ids, event.currentTarget));

    const readPrintSettings = () => {
        const form = new FormData(elements.settings);
        return {
            label_size: form.get('label_size'), columns: Number(form.get('columns')), page_size: form.get('page_size'),
            orientation: form.get('orientation'), barcode_height: Number(form.get('barcode_height')), copies_per_label: Number(form.get('copies_per_label')),
            show_accession: form.has('show_accession'), show_library_name: form.has('show_library_name'),
            show_logo: form.has('show_logo'), show_border: form.has('show_border'),
        };
    };

    const prepareGeneratedPrint = async (scope, trigger) => {
        if (!state.preview || generationSignature() !== state.previewSignature) {
            elements.previewStale.hidden = false;
            toast('warning', 'Preview is out of date', 'Generate the preview again before printing.');
            return;
        }
        const selected = [...state.preview.selected];
        if (scope === 'selected' && !selected.length) return;
        const accessions = scope === 'all' ? state.preview.labels.map((label) => label.accession) : selected;
        await preparePrint({ source: 'generated', ...generationPayload(), print_scope: scope, accessions, ...readPrintSettings() }, trigger);
    };

    const prepareReprintPrint = async (ids, trigger) => {
        if (!ids.length) return;
        await preparePrint({ source: 'reprint', copy_ids: ids, ...readPrintSettings() }, trigger);
    };

    const preparePrint = async (payload, trigger) => {
        const estimated = (payload.source === 'reprint' ? payload.copy_ids.length : (payload.print_scope === 'all' ? state.preview.generated_count : payload.accessions.length)) * payload.copies_per_label;
        if (estimated >= 300 && !window.confirm(`You are about to prepare approximately ${estimated} labels. Continue?`)) return;
        const popup = window.open('', '_blank');
        if (!popup) { toast('error', 'Popup blocked', 'Allow popups for this site to open the print preview.'); return; }
        popup.document.write('<!doctype html><title>Preparing labels</title><style>body{font:14px Arial;display:grid;min-height:90vh;place-items:center;color:#475569}</style><p>Checking availability and preparing print preview...</p>');
        trigger.disabled = true;
        const original = trigger.innerHTML;
        trigger.textContent = 'Checking availability...';
        try {
            const data = await fetchJson(config.preparePrint, { method: 'POST', body: JSON.stringify(payload) });
            if (data.skipped_now.length) toast('warning', 'Availability updated', `${data.summary.unique_count} ready to print; ${data.skipped_now.length} unavailable or removed.`);
            else toast('success', 'Print preview prepared', `${data.summary.total_labels} labels are ready.`);
            popup.document.open(); popup.document.write(data.html); popup.document.close();
            if (payload.source === 'generated' && data.skipped_now.length) {
                data.skipped_now.forEach((accession) => state.preview.selected.delete(accession));
                renderGeneratedPreview();
            }
        } catch (error) {
            popup.close();
            toast('error', 'Unable to prepare print', error.message);
        } finally {
            trigger.disabled = false;
            trigger.innerHTML = original;
            refreshIcons();
        }
    };

    const updateColumnOptions = () => {
        const labelSize = $('[data-label-size]');
        const columns = $('[data-columns]');
        const max = Number(labelSize.selectedOptions[0]?.dataset.maxColumns || 5);
        [...columns.options].forEach((option) => { option.disabled = Number(option.value) > max; });
        if (Number(columns.value) > max) columns.value = String(max);
    };
    const settingsKey = 'lms.accession-label-settings.v2';
    const saveSettings = () => { updateColumnOptions(); localStorage.setItem(settingsKey, JSON.stringify(readPrintSettings())); };
    const restoreSettings = () => {
        try {
            const saved = JSON.parse(localStorage.getItem(settingsKey) || 'null');
            if (!saved) return;
            Object.entries(saved).forEach(([name, value]) => {
                const input = elements.settings.elements.namedItem(name);
                if (!input || input.disabled) return;
                if (input.type === 'checkbox') input.checked = Boolean(value); else input.value = String(value);
            });
        } catch (_) { /* Ignore invalid browser storage. */ }
    };
    elements.settings.addEventListener('change', saveSettings);
    restoreSettings(); updateColumnOptions(); updateCalculations(); switchWorkspace('generate'); refreshIcons();
});
