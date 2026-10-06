/* Read-only details and presentation hooks for the existing Admin book modals. */
(() => {
    'use strict';

    window.AdminBookDetails = class AdminBookDetails {
        constructor({ onOpen, onClose, onEdit }) {
            this.modal = document.getElementById('viewBookModal');
            this.content = document.getElementById('bookDetailsContent');
            this.status = document.getElementById('bookDetailsStatus');
            this.manage = document.getElementById('bookDetailsManageCopies');
            this.add = document.getElementById('bookDetailsAddCopies');
            this.onOpen = onOpen;
            this.onClose = onClose;
            this.onEdit = onEdit;
            this.sequence = 0;
            this.inertElements = [];
            this.dateFormat = new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeZone: this.modal.dataset.timeZone });

            this.modal.addEventListener('keydown', event => {
                if (event.key === 'Escape' && this.modal.classList.contains('active')) {
                    event.preventDefault();
                    event.stopPropagation();
                    this.onClose(); // Loading is cancellable; this is not a critical form.
                }
            });
            this.modal.addEventListener('click', event => {
                const action = event.target.closest('[data-book-action]')?.dataset.bookAction;
                if (action === 'retry') this.load();
                if (action === 'edit' && this.book && this.row?.isConnected) this.onEdit(this.row);
            });
            this.add?.addEventListener('click', () => {
                if (!this.book || this.add.disabled) return;
                const book = { book_id: this.book.id, title: this.book.title };
                this.onClose();
                document.getElementById('addPhysicalBookModal').dispatchEvent(new CustomEvent('physical-book:open', { detail: { book } }));
            });
        }

        open(row) {
            if (this.modal.classList.contains('active') && this.row === row && this.request) return;
            this.row = row;
            this.trigger = row.querySelector('.action-btn.view');
            this.book = null;
            this.onOpen();
            this.modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('book-details-open');
            this.makeBackgroundInert();
            this.load();
        }

        makeBackgroundInert() {
            if (this.inertElements.length) return;
            // Leave the dialog's ancestor path and foreground feedback accessible.
            for (let branch = this.modal; branch.parentElement; branch = branch.parentElement) {
                for (const sibling of branch.parentElement.children) {
                    if (sibling === branch || !(sibling instanceof HTMLElement)
                        || sibling.matches('script, style, link, template, .notification-container, .action-feedback-toast-container, .admin-ui-tooltip')) continue;
                    this.inertElements.push([sibling, sibling.inert]);
                    sibling.inert = true;
                }
                if (branch.parentElement === document.body) break;
            }
        }

        dismiss() {
            this.sequence++;
            this.request?.abort();
            this.request = null;
            this.book = null;
            this.modal.setAttribute('aria-hidden', 'true');
            this.content.setAttribute('aria-busy', 'false');
            this.inertElements.forEach(([element, wasInert]) => { element.inert = wasInert; });
            this.inertElements = [];
            document.body.classList.remove('book-details-open');
            const trigger = this.trigger;
            requestAnimationFrame(() => {
                if (trigger?.isConnected && !document.querySelector('.modal-overlay.active')) trigger.focus({ preventScroll: true });
            });
        }

        setActionsEnabled(enabled) {
            if (this.add) this.add.disabled = !enabled;
            if (this.manage) {
                this.manage.setAttribute('aria-disabled', String(!enabled));
                this.manage.tabIndex = enabled ? 0 : -1;
                if (!enabled) this.manage.removeAttribute('href');
            }
        }

        showTemplate(id) {
            this.content.replaceChildren(document.getElementById(id).content.cloneNode(true));
            this.content.parentElement.scrollTop = 0;
        }

        async load() {
            if (!this.modal.classList.contains('active') || !this.row) return;
            this.request?.abort();
            const controller = new AbortController();
            this.request = controller;
            const sequence = ++this.sequence;
            this.book = null;
            this.setActionsEnabled(false);
            this.showTemplate('bookDetailsLoadingTemplate');
            this.content.setAttribute('aria-busy', 'true');
            this.status.textContent = 'Loading book details...';
            let timedOut = false;
            const timeout = setTimeout(() => { timedOut = true; controller.abort(); }, 15000);

            try {
                const url = this.modal.dataset.detailsUrl.replace('__BOOK__', encodeURIComponent(this.row.dataset.bookId));
                const response = await fetch(url, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    signal: controller.signal,
                });
                if (!response.ok) throw new Error('Unable to load book details.');
                const payload = await response.json();
                if (!payload.success || !payload.data?.book || !payload.data?.inventory) throw new Error('Invalid book details.');
                if (sequence !== this.sequence || !this.modal.classList.contains('active')) return;
                this.book = payload.data.book;
                this.render(payload.data);
                this.status.textContent = `Book details loaded for ${this.book.title}.`;
            } catch (error) {
                if (sequence !== this.sequence || (error.name === 'AbortError' && !timedOut)) return;
                this.showTemplate('bookDetailsErrorTemplate');
                this.status.textContent = 'Unable to load book details. Please try again.';
            } finally {
                clearTimeout(timeout);
                if (sequence === this.sequence) {
                    this.request = null;
                    this.content.setAttribute('aria-busy', 'false');
                    if (!this.modal.querySelector('.book-details-panel').contains(document.activeElement)) {
                        document.getElementById('closeViewBookModal').focus({ preventScroll: true });
                    }
                }
            }
        }

        render({ book, inventory, manage_copies_url: manageUrl }) {
            this.showTemplate('bookDetailsLoadedTemplate');
            const field = name => this.content.querySelector(`[data-book-detail="${name}"]`);
            const display = value => value === null || value === undefined || String(value).trim() === '' ? 'N/A' : String(value);
            for (const key of ['title', 'author', 'category', 'publisher', 'isbn', 'id', 'shelf_no', 'condition']) {
                field(key).textContent = display(book[key]);
            }
            for (const key of ['created_at', 'updated_at']) {
                const date = book[key] ? new Date(book[key]) : null;
                field(key).textContent = date && !Number.isNaN(date.valueOf()) ? this.dateFormat.format(date) : 'N/A';
            }
            field('description').textContent = book.description?.trim() ? book.description : 'No description is available for this book.';
            const locations = inventory.shelf_locations || [];
            field('shelf_locations').textContent = locations.length
                ? locations.join(', ') + (inventory.has_more_shelf_locations ? ' · More locations in Manage Copies' : '')
                : 'No physical shelf locations recorded';

            this.content.querySelectorAll('[data-book-count]').forEach(node => {
                node.textContent = Number(inventory[node.dataset.bookCount] ?? 0).toLocaleString();
            });
            const types = inventory.types || {};
            field('copy-types').textContent = inventory.total === 0
                ? 'No physical copies have been added yet.'
                : `${Number(types.borrowing || 0).toLocaleString()} borrowing · ${Number(types.reference || 0).toLocaleString()} reference`;
            const variants = { lost: 'danger', damaged: 'danger' };
            for (const [status, count] of Object.entries(inventory.statuses || {})) {
                // Available and issued are already shown in the summary cards.
                if (status === 'available' || status === 'issued' || !count) continue;
                const badge = document.createElement('span');
                badge.className = `admin-ui-badge admin-ui-badge-${variants[status] || 'info'} book-details-capitalize`;
                badge.textContent = `${status.replace(/_/g, ' ')}: ${Number(count).toLocaleString()}`;
                field('copy-statuses').appendChild(badge);
            }

            const initials = String(book.title || 'Book').trim().split(/\s+/).slice(0, 2).map(word => Array.from(word)[0]).join('').toUpperCase();
            field('initials').textContent = initials;
            const image = field('cover');
            const placeholder = this.content.querySelector('.book-details-cover-placeholder');
            const coverUrl = this.safeUrl(book.cover_url);
            if (coverUrl) {
                image.alt = `Cover of ${book.title}`;
                image.addEventListener('load', () => { placeholder.hidden = true; image.hidden = false; }, { once: true });
                image.addEventListener('error', () => { image.hidden = true; placeholder.hidden = false; }, { once: true });
                image.src = coverUrl.href;
            }
            const copiesUrl = this.safeUrl(manageUrl);
            if (this.manage && copiesUrl?.origin === location.origin) this.manage.href = copiesUrl.href;
            this.setActionsEnabled(Boolean(copiesUrl?.origin === location.origin));
        }

        safeUrl(value) {
            if (!value) return null;
            try {
                const url = new URL(value, location.href);
                return ['http:', 'https:'].includes(url.protocol) ? url : null;
            } catch { return null; }
        }
    };
})();
