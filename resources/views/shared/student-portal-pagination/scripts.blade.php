<script>
    (() => {
        if (window.StudentPortalPagination) {
            return;
        }

        class StudentPortalPagination {
            constructor(config = {}) {
                this.config = {
                    containerId: config.containerId || 'paginationContainer',
                    fromId: config.fromId || 'paginationStart',
                    toId: config.toId || 'paginationEnd',
                    totalId: config.totalId || 'paginationTotal',
                    pageInfoId: config.pageInfoId || 'paginationPageInfo',
                    buttonsId: config.buttonsId || 'paginationButtons',
                };

                this.onPageChange = null;
                this.elements = {
                    container: document.getElementById(this.config.containerId),
                    from: document.getElementById(this.config.fromId),
                    to: document.getElementById(this.config.toId),
                    total: document.getElementById(this.config.totalId),
                    pageInfo: document.getElementById(this.config.pageInfoId),
                    buttons: document.getElementById(this.config.buttonsId),
                };

                this.handleClick = this.handleClick.bind(this);
                this.elements.buttons?.addEventListener('click', this.handleClick);
            }

            render({
                total = 0,
                from = 0,
                to = 0,
                currentPage = 1,
                lastPage = 1,
                onPageChange = null,
            } = {}) {
                if (!this.elements.container || !this.elements.buttons) {
                    return;
                }

                if (typeof onPageChange === 'function') {
                    this.onPageChange = onPageChange;
                }

                const safeTotal = Math.max(0, Number(total) || 0);
                const safeLastPage = safeTotal === 0 ? 0 : Math.max(1, Number(lastPage) || 1);
                const safeCurrentPage = safeTotal === 0 ? 0 : Math.min(safeLastPage, Math.max(1, Number(currentPage) || 1));
                const safeFrom = safeTotal === 0 ? 0 : Math.max(1, Number(from) || 1);
                const safeTo = safeTotal === 0 ? 0 : Math.min(safeTotal, Math.max(safeFrom, Number(to) || safeFrom));

                if (this.elements.from) this.elements.from.textContent = String(safeFrom);
                if (this.elements.to) this.elements.to.textContent = String(safeTo);
                if (this.elements.total) this.elements.total.textContent = String(safeTotal);
                if (this.elements.pageInfo) {
                    this.elements.pageInfo.textContent = safeTotal === 0
                        ? 'Page 0 of 0'
                        : `Page ${safeCurrentPage} of ${safeLastPage}`;
                }

                this.elements.container.hidden = safeTotal === 0;
                this.elements.buttons.innerHTML = safeTotal === 0
                    ? ''
                    : this.buildButtons(safeCurrentPage, safeLastPage);
            }

            handleClick(event) {
                const button = event.target.closest('[data-page]');
                if (!button || button.disabled) {
                    return;
                }

                const nextPage = Number(button.getAttribute('data-page'));
                if (!Number.isNaN(nextPage) && nextPage > 0 && typeof this.onPageChange === 'function') {
                    this.onPageChange(nextPage);
                }
            }

            buildButtons(currentPage, lastPage) {
                const buttons = [
                    this.buttonMarkup('&larr; Previous', currentPage - 1, currentPage === 1, false, 'Previous page'),
                ];

                this.buildSequence(currentPage, lastPage).forEach((page) => {
                    if (page === null) {
                        buttons.push('<span class="admin-table-pagination-ellipsis" aria-hidden="true">&hellip;</span>');
                        return;
                    }

                    buttons.push(
                        this.buttonMarkup(String(page), page, false, page === currentPage, `Page ${page}`)
                    );
                });

                buttons.push(
                    this.buttonMarkup('Next &rarr;', currentPage + 1, currentPage === lastPage, false, 'Next page')
                );

                return buttons.join('');
            }

            buttonMarkup(label, page, disabled = false, active = false, ariaLabel = '') {
                const accessibleLabel = active
                    ? `Current page, ${ariaLabel || label}`
                    : (ariaLabel || `Page ${label}`);

                return `
                    <button
                        type="button"
                        class="admin-table-pagination-link${active ? ' is-active' : ''}${disabled ? ' is-disabled' : ''}"
                        data-page="${page}"
                        aria-label="${this.escapeHtml(accessibleLabel)}"
                        ${disabled ? 'disabled aria-disabled="true"' : ''}
                        ${active ? 'aria-current="page"' : ''}
                    >
                        ${label}
                    </button>
                `;
            }

            buildSequence(currentPage, lastPage) {
                if (lastPage <= 7) {
                    return Array.from({ length: lastPage }, (_, index) => index + 1);
                }

                const pages = [1];
                let startPage = Math.max(2, currentPage - 1);
                let endPage = Math.min(lastPage - 1, currentPage + 1);

                if (currentPage <= 3) {
                    endPage = 4;
                }

                if (currentPage >= lastPage - 2) {
                    startPage = lastPage - 3;
                }

                if (startPage > 2) {
                    pages.push(null);
                }

                for (let page = startPage; page <= endPage; page += 1) {
                    pages.push(page);
                }

                if (endPage < lastPage - 1) {
                    pages.push(null);
                }

                pages.push(lastPage);

                return pages;
            }

            escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }
        }

        window.StudentPortalPagination = StudentPortalPagination;
    })();
</script>
