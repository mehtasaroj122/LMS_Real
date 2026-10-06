(function (window, document) {
    'use strict';

    let returnFocusTo = null;
    let storageBase = '/storage';
    let bound = false;

    const byId = (id) => document.getElementById(id);
    const displayLabel = (value, fallback = 'N/A') => {
        if (value === null || value === undefined || value === '') return fallback;
        return String(value).replace(/[_-]+/g, ' ').replace(/\b\w/g, (character) => character.toUpperCase());
    };
    const setText = (id, value, fallback = 'N/A') => {
        const element = byId(id);
        if (element) element.textContent = value === null || value === undefined || value === '' ? fallback : String(value);
    };
    const showBadge = (id, label, tone) => {
        const target = byId(id);
        if (!target) return;
        const badge = document.createElement('span');
        badge.className = `admin-ui-badge admin-ui-badge-${tone}`;
        badge.textContent = label;
        target.replaceChildren(badge);
    };
    const fineStatus = (status) => {
        const key = String(status || 'none').toLowerCase() === 'unpaid'
            ? 'pending'
            : String(status || 'none').toLowerCase();
        const labels = { pending: 'Pending', paid: 'Paid', waived: 'Waived', none: 'No fine', 'n/a': 'No fine' };
        return { key, label: labels[key] || displayLabel(key) };
    };
    const moneyLabel = (book) => {
        if (book.fineLabel) return String(book.fineLabel);
        const amount = Number(book.fineAmount ?? book.fine ?? 0);
        const prefix = window.LmsCurrency?.prefix || 'रु ';
        return `${prefix}${Number.isFinite(amount) ? amount.toFixed(2) : '0.00'}`;
    };
    const coverUrl = (cover) => {
        if (!cover) return '';
        if (/^https?:\/\//i.test(cover)) return cover;
        return `${storageBase.replace(/\/$/, '')}/${String(cover).replace(/^\/?storage\//, '').replace(/^\//, '')}`;
    };
    const renderCover = (book) => {
        const container = byId('bookCoverContent');
        if (!container) return;
        const fallback = () => {
            const avatar = document.createElement('div');
            avatar.className = 'book-cover-avatar';
            avatar.textContent = String(book.title || 'B').charAt(0).toUpperCase();
            avatar.setAttribute('aria-hidden', 'true');
            container.replaceChildren(avatar);
        };
        const source = coverUrl(book.coverImage);
        if (!source) return fallback();
        const image = document.createElement('img');
        image.src = source;
        image.alt = `Cover of ${book.title || 'book'}`;
        image.className = 'book-cover-image';
        image.addEventListener('error', () => {
            if (image.parentElement === container) fallback();
        }, { once: true });
        container.replaceChildren(image);
    };
    const focusable = (overlay) => Array.from(overlay.querySelectorAll(
        'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
    )).filter((element) => !element.hidden && element.offsetParent !== null);

    function close() {
        const overlay = byId('bookDetailsOverlay');
        if (!overlay?.classList.contains('show')) return;
        overlay.classList.remove('show');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('issued-book-details-open');
        if (returnFocusTo?.isConnected) returnFocusTo.focus({ preventScroll: true });
        returnFocusTo = null;
    }

    function bind() {
        if (bound) return;
        const overlay = byId('bookDetailsOverlay');
        if (!overlay) return;
        bound = true;
        byId('closeBookDetailsBtn')?.addEventListener('click', close);
        byId('closeBookDetailsFooterBtn')?.addEventListener('click', close);
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) close();
        });
        document.addEventListener('keydown', (event) => {
            if (!overlay.classList.contains('show')) return;
            if (event.key === 'Escape') {
                event.preventDefault();
                close();
                return;
            }
            if (event.key !== 'Tab') return;
            const items = focusable(overlay);
            if (!items.length) return;
            const first = items[0];
            const last = items[items.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    }

    function open(book, trigger, options = {}) {
        const overlay = byId('bookDetailsOverlay');
        if (!overlay || !book) return false;
        bind();
        storageBase = options.storageBase || storageBase;
        returnFocusTo = trigger || document.activeElement;

        const overdueDays = Number(book.daysOverdue || 0);
        const daysLabel = `${overdueDays} ${overdueDays === 1 ? 'day' : 'days'}`;
        const returned = Boolean(book.returnDateRaw) || String(book.status).toLowerCase() === 'returned';
        const returnLabel = returned ? (book.returnDate || 'N/A') : 'Not returned';
        const money = moneyLabel(book);
        const hasFine = Boolean(book.hasFine) || Number(book.fineAmount ?? book.fine ?? 0) > 0;

        renderCover(book);
        setText('bookDetailsTitle', book.title, 'Unknown');
        setText('bookDetailsAuthor', `by ${book.author || 'Unknown Author'}`);
        setText('bookDetailsCategory', book.category, 'Uncategorized');
        setText('bookDetailsISBN', book.isbn);
        setText('bookDetailsAccession', book.accessionNumber);
        setText('bookDetailsPublisher', book.publisher);
        setText('bookDetailsCondition', displayLabel(book.condition));
        setText('bookDetailsCopyCondition', displayLabel(book.copyCondition));
        setText('bookDetailsCopyStatus', displayLabel(book.copyStatus));
        setText('bookDetailsCopyShelf', book.copyShelf);
        setText('bookDetailsTransactionId', book.transactionId || `TXN-${String(book.id || '').padStart(6, '0')}`);
        setText('bookDetailsIssueDate', book.issueDate);
        setText('bookDetailsDueDate', book.dueDate);
        setText('bookDetailsDueSummary', book.dueDate);
        setText('bookDetailsReturnDate', returnLabel);
        setText('bookDetailsIssuedBy', book.issuedBy, 'System');
        setText('bookDetailsRenewalCount', book.renewalCount ?? 0);
        setText('bookDetailsFine', money);
        setText('bookDetailsDaysOverdue', daysLabel);
        setText('bookDetailsDescription', book.description, 'No description available.');
        setText('bookDetailsTimelineIssued', book.issueDate);
        setText('bookDetailsTimelineDue', book.dueDate);
        setText('bookDetailsTimelineReturned', returnLabel);
        byId('bookDetailsTimelineReturnStep')?.classList.toggle('is-pending', !returned);

        const status = String(book.status || '').toLowerCase();
        const statusTone = { overdue: 'danger', returned: 'success', issued: 'warning', lost: 'danger' };
        showBadge('bookDetailsStatus', displayLabel(status), statusTone[status] || 'neutral');
        if (byId('overdueInfo')) byId('overdueInfo').hidden = status !== 'overdue';
        setText('bookDetailsOverdueMessage', `This book is overdue by ${daysLabel}.`);
        setText('bookDetailsOverdueContext', `Due Date: ${book.dueDate || 'N/A'} · Current Fine: ${money}`);

        const meta = fineStatus(book.fineStatus);
        const fineTone = { pending: 'warning', paid: 'success', waived: 'success' };
        showBadge('bookDetailsFineStatus', hasFine ? meta.label : 'No fine', fineTone[meta.key] || 'neutral');
        if (byId('bookDetailsFineSection')) byId('bookDetailsFineSection').hidden = !hasFine;
        if (byId('bookDetailsNoFine')) byId('bookDetailsNoFine').hidden = hasFine;
        setText('bookDetailsFineAmount', money);
        setText('bookDetailsFineDetailStatus', meta.label);
        [
            ['bookDetailsFineDaysRow', 'bookDetailsFineDays', book.fineDaysLate == null ? null : `${book.fineDaysLate} days`],
            ['bookDetailsPaymentMethodRow', 'bookDetailsPaymentMethod', book.finePaymentMethod],
            ['bookDetailsPaidDateRow', 'bookDetailsPaidDate', book.finePaidDate],
            ['bookDetailsWaivedDateRow', 'bookDetailsWaivedDate', book.fineWaivedDate],
        ].forEach(([rowId, valueId, value]) => {
            const row = byId(rowId);
            if (row) row.hidden = value === null || value === undefined || value === '';
            setText(valueId, value);
        });

        overlay.setAttribute('aria-hidden', 'false');
        overlay.classList.add('show');
        document.body.classList.add('issued-book-details-open');
        const body = overlay.querySelector('.book-details-body');
        if (body) body.scrollTop = 0;
        byId('closeBookDetailsBtn')?.focus({ preventScroll: true });
        return true;
    }

    window.LMSIssuedBookDetails = { open, close };
})(window, document);
