@php
    $studentProfileScriptStudent = [
        'id' => $student->id,
        'name' => $student->user?->name,
        'status' => strtolower((string) ($student->user?->status ?? 'inactive')),
    ];
@endphp

<script>
    (() => {
        const config = @json($studentProfileConfig);
        const student = @json($studentProfileScriptStudent);
        const books = @json($studentBooks);
        const fines = @json($studentFines);
        const requests = @json($studentRequests ?? []);
        const activities = @json($studentActivities);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const icons = {
            paid: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><path d="M2 10h20"></path><path d="M7 15h.01"></path><path d="M11 15h2"></path></svg>',
            waive: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
            email: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-10 7L2 7"></path></svg>',
            spinner: '<span class="student-spinner" aria-hidden="true"></span>',
        };

        class StudentProfilePage {
            constructor() {
                this.student = { ...student };
                this.books = Array.isArray(books) ? books : [];
                this.fines = Array.isArray(fines) ? fines : [];
                this.requests = Array.isArray(requests) ? requests : [];
                this.activities = Array.isArray(activities) ? activities : [];
                this.bookSearch = '';
                this.bookStatus = 'all';
                this.bookSort = 'issue-desc';
                this.bookPage = 1;
                this.bookPerPage = 10;
                this.finePage = 1;
                this.finePerPage = 10;
                this.activityVisibleCount = 10;
                this.requestPage = 1;
                this.requestPerPage = 10;
                this.currentFineId = null;
                this.pendingFineAction = null;
                this.activeModalId = null;
                this.lastFocusedElement = null;
                this.actionInFlight = false;
                this.elements = {
                    root: document.getElementById('studentProfileRoot'),
                    statusBadge: document.getElementById('studentStatusBadge'),
                    statusAction: document.getElementById('studentStatusActionBtn'),
                    pendingFine: document.getElementById('studentPendingFineValue'),
                    bookSearch: document.getElementById('studentBookSearch'),
                    bookStatus: document.getElementById('studentBookStatusFilter'),
                    bookSort: document.getElementById('studentBookSortFilter'),
                    bookReset: document.getElementById('studentBookResetFiltersBtn'),
                    bookEntries: document.getElementById('studentBookEntries'),
                    bookSummary: document.getElementById('studentBookSummary'),
                    bookPageInfo: document.getElementById('studentBookPageInfo'),
                    bookPagination: document.getElementById('studentBookPagination'),
                    booksPanel: document.querySelector('.student-books-table-panel'),
                    bookTableScroller: document.querySelector('.student-books-table-scroller'),
                    booksBody: document.getElementById('studentBooksTableBody'),
                    booksEmpty: document.getElementById('studentBooksEmptyState'),
                    fineEntries: document.getElementById('studentFineEntries'),
                    fineSummary: document.getElementById('studentFineSummary'),
                    finePageInfo: document.getElementById('studentFinePageInfo'),
                    finePagination: document.getElementById('studentFinePagination'),
                    finesPanel: document.querySelector('.student-fines-table-panel'),
                    fineTableScroller: document.querySelector('.student-fine-table-scroller'),
                    finesBody: document.getElementById('studentFinesTableBody'),
                    finesEmpty: document.getElementById('studentFinesEmptyState'),
                    activityPanel: document.querySelector('.student-pane-scroll-activity'),
                    activityList: document.getElementById('studentActivityTimeline'),
                    activityEmpty: document.getElementById('studentActivityEmptyState'),
                    activitySummary: document.getElementById('studentActivitySummary'),
                    activityShowMore: document.getElementById('studentActivityShowMoreBtn'),
                    requestEntries: document.getElementById('studentRequestEntries'),
                    requestPanel: document.querySelector('.student-request-table-panel'),
                    requestTableScroller: document.querySelector('.student-request-table-scroller'),
                    requestBody: document.getElementById('studentRequestTableBody'),
                    requestEmpty: document.getElementById('studentRequestEmptyState'),
                    requestSummary: document.getElementById('studentRequestSummary'),
                    requestPageInfo: document.getElementById('studentRequestPageInfo'),
                    requestPagination: document.getElementById('studentRequestPagination'),
                    toast: document.getElementById('studentProfileToastContainer'),
                    liveRegion: document.getElementById('studentProfileLiveRegion'),
                    fineConfirmIcon: document.getElementById('studentFineConfirmIcon'),
                    fineConfirmTitle: document.getElementById('studentFineConfirmTitle'),
                    fineConfirmMessage: document.getElementById('studentFineConfirmMessage'),
                    fineConfirmDetail: document.getElementById('studentFineConfirmDetail'),
                    fineConfirmSubmit: document.getElementById('studentFineConfirmSubmit'),
                    fineWaiveDetail: document.getElementById('studentFineWaiveDetail'),
                    fineWaiveReason: document.getElementById('studentFineWaiveReason'),
                    fineWaiveError: document.getElementById('studentFineWaiveError'),
                    fineWaiveSubmit: document.getElementById('studentFineWaiveSubmit'),
                };
                if (!this.elements.root) return;
                this.bindEvents();
                this.renderBooks();
                this.renderFines();
                this.renderRequests();
                this.renderActivities();
                this.refreshPendingFineSummary();
            }

            bindEvents() {
                this.elements.bookSearch?.addEventListener('input', (event) => {
                    this.bookSearch = event.target.value.trim().toLowerCase();
                    this.bookPage = 1;
                    this.renderBooks();
                });
                this.elements.bookStatus?.addEventListener('change', (event) => {
                    this.bookStatus = event.target.value;
                    this.bookPage = 1;
                    this.renderBooks();
                });
                this.elements.bookSort?.addEventListener('change', (event) => {
                    this.bookSort = event.target.value;
                    this.bookPage = 1;
                    this.renderBooks();
                });
                this.elements.bookReset?.addEventListener('click', () => {
                    if (this.elements.bookSearch) this.elements.bookSearch.value = '';
                    if (this.elements.bookStatus) this.elements.bookStatus.value = 'all';
                    if (this.elements.bookSort) this.elements.bookSort.value = 'issue-desc';
                    this.bookSearch = '';
                    this.bookStatus = 'all';
                    this.bookSort = 'issue-desc';
                    this.bookPage = 1;
                    this.renderBooks();
                });
                this.elements.bookEntries?.addEventListener('change', (event) => {
                    this.bookPerPage = Number(event.target.value || 10);
                    this.bookPage = 1;
                    this.renderBooks();
                });
                this.elements.fineEntries?.addEventListener('change', (event) => {
                    this.finePerPage = Number(event.target.value || 10);
                    this.finePage = 1;
                    this.renderFines();
                });
                this.elements.activityShowMore?.addEventListener('click', () => {
                    this.activityVisibleCount += 10;
                    this.renderActivities();
                });
                this.elements.requestEntries?.addEventListener('change', (event) => {
                    this.requestPerPage = Number(event.target.value || 10);
                    this.requestPage = 1;
                    this.renderRequests();
                });
                this.elements.statusAction?.addEventListener('click', () => this.toggleStatus());
                this.elements.root.addEventListener('click', (event) => {
                    const fineActionButton = event.target.closest('[data-fine-action]');
                    if (fineActionButton) {
                        this.handleFineAction(fineActionButton);
                        return;
                    }
                    const finePageButton = event.target.closest('[data-fine-page]');
                    if (finePageButton) {
                        const nextPage = Number(finePageButton.getAttribute('data-fine-page'));
                        if (nextPage) {
                            this.finePage = nextPage;
                            this.renderFines();
                        }
                        return;
                    }
                    const bookPageButton = event.target.closest('[data-book-page]');
                    if (bookPageButton) {
                        const nextPage = Number(bookPageButton.getAttribute('data-book-page'));
                        if (nextPage) {
                            this.bookPage = nextPage;
                            this.renderBooks();
                        }
                        return;
                    }
                    const requestPageButton = event.target.closest('[data-request-page]');
                    if (requestPageButton) {
                        const nextPage = Number(requestPageButton.getAttribute('data-request-page'));
                        if (nextPage) {
                            this.requestPage = nextPage;
                            this.renderRequests();
                        }
                        return;
                    }
                    const modalCloseButton = event.target.closest('[data-student-modal-close]');
                    if (modalCloseButton) this.closeModal(modalCloseButton.getAttribute('data-student-modal-close'));
                });
                this.elements.fineConfirmSubmit?.addEventListener('click', () => this.confirmFineAction());
                this.elements.fineWaiveSubmit?.addEventListener('click', () => this.submitFineWaiver());
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && this.activeModalId) {
                        event.preventDefault();
                        this.closeModal(this.activeModalId);
                        return;
                    }
                    if (event.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) {
                        event.preventDefault();
                        this.elements.bookSearch?.focus();
                    }
                    if (event.key === 'Escape' && document.activeElement === this.elements.bookSearch) {
                        this.elements.bookSearch.value = '';
                        this.bookSearch = '';
                        this.renderBooks();
                    }
                });
            }

            renderBooks() {
                const filteredBooks = this.books.filter((book) => {
                    const matchesSearch = !this.bookSearch || [book.title, book.author, book.isbn].filter(Boolean).some((value) => String(value).toLowerCase().includes(this.bookSearch));
                    const matchesStatus = this.bookStatus === 'all' || book.status === this.bookStatus;
                    return matchesSearch && matchesStatus;
                });
                const sortedBooks = [...filteredBooks].sort((left, right) => this.compareBooks(left, right));
                const totalBooks = sortedBooks.length;
                const totalPages = Math.max(1, Math.ceil(totalBooks / this.bookPerPage));
                this.bookPage = Math.min(this.bookPage, totalPages);

                if (!totalBooks) {
                    this.elements.booksBody.innerHTML = '';
                    if (this.elements.booksPanel) this.elements.booksPanel.hidden = false;
                    if (this.elements.bookTableScroller) this.elements.bookTableScroller.hidden = true;
                    this.elements.booksEmpty.hidden = false;
                    if (this.elements.bookSummary) this.elements.bookSummary.textContent = 'Showing 0 books';
                    if (this.elements.bookPageInfo) this.elements.bookPageInfo.textContent = 'Page 0 of 0';
                    if (this.elements.bookPagination) this.elements.bookPagination.innerHTML = '';
                    return;
                }

                const startIndex = (this.bookPage - 1) * this.bookPerPage;
                const endIndex = Math.min(startIndex + this.bookPerPage, totalBooks);
                const pageItems = sortedBooks.slice(startIndex, endIndex);

                if (this.elements.booksPanel) this.elements.booksPanel.hidden = false;
                if (this.elements.bookTableScroller) this.elements.bookTableScroller.hidden = false;
                this.elements.booksEmpty.hidden = true;
                this.elements.booksBody.innerHTML = pageItems.map((book) => `
                    <tr>
                        <td><div class="student-table-book"><strong>${this.escapeHtml(book.title)}</strong><span>${this.escapeHtml(book.author || book.category || 'Unknown')}</span></div></td>
                        <td>${this.escapeHtml(book.isbn || 'N/A')}</td>
                        <td>${this.escapeHtml(book.issueDate || 'N/A')}</td>
                        <td>${this.escapeHtml(book.dueDate || 'N/A')}</td>
                        <td>${this.escapeHtml(book.returnDate || '-')}</td>
                        <td><span class="student-status-pill status-${this.escapeHtml(book.status)}">${this.escapeHtml(book.statusLabel || book.status)}</span></td>
                        <td class="${book.status === 'overdue' ? 'student-amount overdue' : ''}">${this.escapeHtml(book.fineLabel || 'Rs. 0.00')}</td>
                    </tr>
                `).join('');

                if (this.elements.bookSummary) {
                    this.elements.bookSummary.textContent = `Showing ${startIndex + 1}-${endIndex} of ${totalBooks} books`;
                }

                if (this.elements.bookPageInfo) {
                    this.elements.bookPageInfo.textContent = `Page ${this.bookPage} of ${totalPages}`;
                }

                this.renderBookPagination(totalPages);
            }

            compareBooks(left, right) {
                if (this.bookSort === 'issue-asc') return this.bookDateValue(left.issueDate) - this.bookDateValue(right.issueDate);
                if (this.bookSort === 'title-asc') return String(left.title || '').localeCompare(String(right.title || ''));
                if (this.bookSort === 'title-desc') return String(right.title || '').localeCompare(String(left.title || ''));
                if (this.bookSort === 'fine-desc') return Number(right.fineAmount || 0) - Number(left.fineAmount || 0);
                return this.bookDateValue(right.issueDate) - this.bookDateValue(left.issueDate);
            }

            bookDateValue(value) {
                const parsed = Date.parse(String(value || ''));
                return Number.isNaN(parsed) ? 0 : parsed;
            }

            renderFines() {
                const totalFines = this.fines.length;
                const totalPages = Math.max(1, Math.ceil(totalFines / this.finePerPage));
                this.finePage = Math.min(this.finePage, totalPages);

                if (!totalFines) {
                    this.elements.finesBody.innerHTML = '';
                    if (this.elements.finesPanel) this.elements.finesPanel.hidden = false;
                    if (this.elements.fineTableScroller) this.elements.fineTableScroller.hidden = true;
                    this.elements.finesEmpty.hidden = false;
                    if (this.elements.fineSummary) this.elements.fineSummary.textContent = 'Showing 0 fines';
                    if (this.elements.finePageInfo) this.elements.finePageInfo.textContent = 'Page 0 of 0';
                    if (this.elements.finePagination) this.elements.finePagination.innerHTML = '';
                    this.refreshPendingFineSummary();
                    return;
                }

                const startIndex = (this.finePage - 1) * this.finePerPage;
                const endIndex = Math.min(startIndex + this.finePerPage, totalFines);
                const pageItems = this.fines.slice(startIndex, endIndex);

                if (this.elements.finesPanel) this.elements.finesPanel.hidden = false;
                if (this.elements.fineTableScroller) this.elements.fineTableScroller.hidden = false;
                this.elements.finesEmpty.hidden = true;
                this.elements.finesBody.innerHTML = pageItems.map((fine) => this.buildFineRow(fine)).join('');
                if (this.elements.fineSummary) {
                    this.elements.fineSummary.textContent = `Showing ${startIndex + 1}-${endIndex} of ${totalFines} fines`;
                }
                if (this.elements.finePageInfo) {
                    this.elements.finePageInfo.textContent = `Page ${this.finePage} of ${totalPages}`;
                }
                this.renderFinePagination(totalPages);
                this.refreshPendingFineSummary();
            }

            buildFineRow(fine) {
                const status = this.normalizeFineStatus(fine.status);
                const isOverdue = status === 'pending' && Number(fine.daysOverdue || 0) > 0;
                const actionsEnabled = this.hasFineActions();
                return `
                    <tr class="${isOverdue ? 'is-overdue' : ''}">
                        <td>${this.escapeHtml(fine.bookName || 'Unknown')}</td>
                        <td>${this.escapeHtml(fine.dueDate || 'N/A')}</td>
                        <td>${this.escapeHtml(String(fine.daysOverdue ?? 0))}</td>
                        <td class="${status === 'pending' ? 'student-amount overdue' : ''}">${this.escapeHtml(fine.amountLabel || this.formatCurrency(fine.amount || 0))}</td>
                        <td><span class="student-status-pill status-${this.escapeHtml(status)}">${this.escapeHtml(fine.statusLabel || this.formatStatusLabel(status))}</span></td>
                        ${actionsEnabled ? `<td><div class="student-fine-actions">${this.buildFineActionButtons(fine, status)}</div></td>` : ''}
                    </tr>
                `;
            }

            buildFineActionButtons(fine, status = this.normalizeFineStatus(fine.status)) {
                const buttons = [];
                const studentName = this.student.name || 'student';
                const fineId = Number(fine.id);
                if ((config.features?.markPaid ?? false) && status === 'pending' && config.routes?.markPaid) {
                    buttons.push(`<button type="button" class="student-fine-action-btn btn-paid" data-fine-action="mark-paid" data-fine-id="${fineId}" aria-label="Mark ${this.escapeHtml(studentName)}'s fine as paid">${icons.paid}<span>Paid</span></button>`);
                }
                if ((config.features?.waive ?? false) && status === 'pending' && config.routes?.waive) {
                    buttons.push(`<button type="button" class="student-fine-action-btn btn-waive" data-fine-action="waive" data-fine-id="${fineId}" aria-label="Waive ${this.escapeHtml(studentName)}'s fine">${icons.waive}<span>Waive</span></button>`);
                }
                if ((config.features?.sendEmail ?? false) && config.routes?.sendEmail) {
                    buttons.push(`<button type="button" class="student-fine-action-btn btn-email" data-fine-action="send-email" data-fine-id="${fineId}" aria-label="Send a ${this.escapeHtml(this.describeFineEmail(status))} email to ${this.escapeHtml(studentName)}">${icons.email}<span>Mail</span></button>`);
                }
                return buttons.join('');
            }

            hasFineActions() {
                return Boolean(config.features?.fineActions);
            }

            handleFineAction(button) {
                const fineId = Number(button.getAttribute('data-fine-id'));
                const action = button.getAttribute('data-fine-action');
                if (!fineId || !action) return;
                if (action === 'mark-paid') return this.openFineConfirmModal('paid', fineId);
                if (action === 'waive') return this.openFineWaiveModal(fineId);
                if (action === 'send-email') return this.openFineConfirmModal('email', fineId);
            }

            openFineConfirmModal(type, fineId) {
                const fine = this.getFineRecord(fineId);
                if (!fine || !this.elements.fineConfirmTitle) return;
                const status = this.normalizeFineStatus(fine.status);
                const amount = fine.amountLabel || this.formatCurrency(fine.amount || 0);
                const bookName = fine.bookName || 'this book';
                const studentName = this.student.name || 'this student';
                const modalCopy = {
                    paid: { title: 'Mark Fine as Paid?', message: `Record ${studentName}'s payment now?`, detail: `${amount} for "${bookName}" will be marked as paid and removed from the pending total.`, submit: 'Mark as Paid', iconClass: 'paid', iconMarkup: icons.paid },
                    email: { title: 'Send Fine Email?', message: `Send a ${this.describeFineEmail(status)} to ${studentName}?`, detail: `The email will use the current ${status} fine status for "${bookName}".`, submit: 'Send Email', iconClass: 'email', iconMarkup: icons.email },
                };
                const selectedCopy = modalCopy[type];
                if (!selectedCopy) return;
                this.pendingFineAction = { type, fineId };
                this.elements.fineConfirmIcon.className = `student-modal-icon ${selectedCopy.iconClass}`;
                this.elements.fineConfirmIcon.innerHTML = selectedCopy.iconMarkup;
                this.elements.fineConfirmTitle.textContent = selectedCopy.title;
                this.elements.fineConfirmMessage.textContent = selectedCopy.message;
                this.elements.fineConfirmDetail.textContent = selectedCopy.detail;
                this.elements.fineConfirmSubmit.textContent = selectedCopy.submit;
                this.elements.fineConfirmSubmit.dataset.defaultLabel = selectedCopy.submit;
                this.openModal('studentFineConfirmModal', this.elements.fineConfirmSubmit);
            }

            openFineWaiveModal(fineId) {
                const fine = this.getFineRecord(fineId);
                if (!fine || !this.elements.fineWaiveDetail) return;
                this.currentFineId = fineId;
                this.clearWaiveForm();
                this.elements.fineWaiveDetail.textContent = `${fine.amountLabel || this.formatCurrency(fine.amount || 0)} for "${fine.bookName || 'this book'}" will be waived after you provide a reason.`;
                this.openModal('studentFineWaiveModal', this.elements.fineWaiveReason);
            }

            async confirmFineAction() {
                if (!this.pendingFineAction || this.actionInFlight) return;
                const submitButton = this.elements.fineConfirmSubmit;
                const { type, fineId } = this.pendingFineAction;
                const fine = this.getFineRecord(fineId);
                if (!fine) return;
                this.actionInFlight = true;
                this.setButtonBusy(submitButton, true, submitButton?.dataset.defaultLabel || 'Processing...');
                try {
                    if (type === 'paid') {
                        await this.requestJson(this.buildFineRoute(config.routes.markPaid, fineId), { method: 'POST' });
                        this.applyFineStatusChange(fineId, 'paid');
                        this.showToast(`${fine.bookName || 'Fine'} marked as paid successfully.`, 'success');
                        this.announce('Fine marked as paid.');
                    }
                    if (type === 'email') {
                        const data = await this.requestJson(this.buildFineRoute(config.routes.sendEmail, fineId), { method: 'POST', body: JSON.stringify({}) });
                        this.showToast(data.message || `The ${this.describeFineEmail(this.normalizeFineStatus(fine.status))} was queued successfully.`, 'success');
                        this.announce('Fine email queued successfully.');
                    }
                    this.closeModal('studentFineConfirmModal');
                } catch (error) {
                    console.error(error);
                    this.showToast(error.message || 'Unable to complete the fine action.', 'error');
                } finally {
                    this.actionInFlight = false;
                    this.setButtonBusy(submitButton, false, submitButton?.dataset.defaultLabel || 'Continue');
                }
            }

            async submitFineWaiver() {
                if (this.actionInFlight || !this.currentFineId) return;
                const fine = this.getFineRecord(this.currentFineId);
                const reason = (this.elements.fineWaiveReason?.value || '').trim();
                if (!fine) return;
                if (!reason) {
                    this.showFineWaiveError('Please enter a reason for waiving this fine.');
                    this.elements.fineWaiveReason?.focus();
                    return;
                }
                this.showFineWaiveError('');
                this.actionInFlight = true;
                this.setButtonBusy(this.elements.fineWaiveSubmit, true, 'Saving...');
                try {
                    await this.requestJson(this.buildFineRoute(config.routes.waive, this.currentFineId), { method: 'POST', body: JSON.stringify({ remarks: reason }) });
                    this.applyFineStatusChange(this.currentFineId, 'waived', { remarks: reason });
                    this.closeModal('studentFineWaiveModal');
                    this.showToast(`${fine.bookName || 'Fine'} was waived successfully.`, 'success');
                    this.announce('Fine waived successfully.');
                } catch (error) {
                    console.error(error);
                    this.showFineWaiveError(error.message || 'Unable to waive this fine.');
                    this.showToast(error.message || 'Unable to waive this fine.', 'error');
                } finally {
                    this.actionInFlight = false;
                    this.setButtonBusy(this.elements.fineWaiveSubmit, false, 'Waive Fine');
                }
            }

            applyFineStatusChange(fineId, nextStatus, extra = {}) {
                const fine = this.getFineRecord(fineId);
                if (!fine) return;
                fine.status = this.normalizeFineStatus(nextStatus);
                fine.statusLabel = this.formatStatusLabel(fine.status);
                if (extra.remarks) fine.remarks = extra.remarks;
                this.renderFines();
            }

            refreshPendingFineSummary() {
                if (!this.elements.pendingFine) return;
                const pendingTotal = this.fines.reduce((total, fine) => this.normalizeFineStatus(fine.status) === 'pending' ? total + Number(fine.amount || 0) : total, 0);
                this.elements.pendingFine.textContent = this.formatCurrency(pendingTotal);
            }

            renderRequests() {
                const totalRequests = this.requests.length;
                const totalPages = Math.max(1, Math.ceil(totalRequests / this.requestPerPage));
                this.requestPage = Math.min(this.requestPage, totalPages);

                if (!totalRequests) {
                    if (this.elements.requestBody) this.elements.requestBody.innerHTML = '';
                    if (this.elements.requestPanel) this.elements.requestPanel.hidden = false;
                    if (this.elements.requestTableScroller) this.elements.requestTableScroller.hidden = true;
                    if (this.elements.requestEmpty) this.elements.requestEmpty.hidden = false;
                    if (this.elements.requestSummary) this.elements.requestSummary.textContent = 'Showing 0 requests';
                    if (this.elements.requestPageInfo) this.elements.requestPageInfo.textContent = 'Page 0 of 0';
                    if (this.elements.requestPagination) this.elements.requestPagination.innerHTML = '';
                    return;
                }

                const startIndex = (this.requestPage - 1) * this.requestPerPage;
                const endIndex = Math.min(startIndex + this.requestPerPage, totalRequests);
                const pageItems = this.requests.slice(startIndex, endIndex);

                if (this.elements.requestPanel) this.elements.requestPanel.hidden = false;
                if (this.elements.requestTableScroller) this.elements.requestTableScroller.hidden = false;
                if (this.elements.requestEmpty) this.elements.requestEmpty.hidden = true;
                if (this.elements.requestBody) {
                    this.elements.requestBody.innerHTML = pageItems.map((request) => `
                        <tr>
                            <td title="${this.escapeHtml(request.bookTitle || 'Unknown')}">${this.escapeHtml(request.bookTitle || 'Unknown')}</td>
                            <td title="${this.escapeHtml(request.requestTime || request.requestDate || 'N/A')}">${this.escapeHtml(request.requestDate || 'N/A')}</td>
                            <td>
                                <span class="student-request-status-pill status-${this.escapeHtml(request.status || 'pending')}">
                                    ${this.escapeHtml(request.statusLabel || this.formatStatusLabel(request.status || 'pending'))}
                                </span>
                            </td>
                        </tr>
                    `).join('');
                }

                if (this.elements.requestSummary) {
                    this.elements.requestSummary.textContent = `Showing ${startIndex + 1}-${endIndex} of ${totalRequests} requests`;
                }

                if (this.elements.requestPageInfo) {
                    this.elements.requestPageInfo.textContent = `Page ${this.requestPage} of ${totalPages}`;
                }

                this.renderRequestPagination(totalPages);
            }

            renderActivities() {
                if (!this.activities.length) {
                    this.elements.activityList.innerHTML = '';
                    if (this.elements.activityPanel) this.elements.activityPanel.hidden = false;
                    if (this.elements.activityList) this.elements.activityList.hidden = true;
                    this.elements.activityEmpty.hidden = false;
                    if (this.elements.activitySummary) this.elements.activitySummary.textContent = 'Showing 0 activities';
                    if (this.elements.activityShowMore) this.elements.activityShowMore.hidden = true;
                    return;
                }

                if (this.elements.activityPanel) this.elements.activityPanel.hidden = false;
                if (this.elements.activityList) this.elements.activityList.hidden = false;
                this.elements.activityEmpty.hidden = true;
                const visibleActivities = this.activities.slice(0, this.activityVisibleCount);
                this.elements.activityList.innerHTML = visibleActivities.map((activity, index) => {
                    const typeMeta = this.getActivityTypeMeta(activity.type);
                    const actorName = activity.actorName || 'System';
                    const actorRole = activity.actorRole || 'System';
                    const roleClass = activity.roleClass || 'system';
                    const relativeTime = activity.displayTime || activity.timestamp || 'Unknown time';
                    const exactTime = activity.timestamp || relativeTime;

                    return `
                        <article class="student-activity-entry type-${this.escapeHtml(typeMeta.key)} ${index === visibleActivities.length - 1 ? 'is-last' : ''}">
                            <div class="student-activity-rail" aria-hidden="true">
                                <span class="student-activity-dot"></span>
                                <span class="student-activity-line"></span>
                            </div>
                            <div class="student-activity-surface">
                                <div class="student-activity-head">
                                    <div class="student-activity-head-copy">
                                        <span class="student-activity-type-pill">${this.escapeHtml(typeMeta.label)}</span>
                                        <h4 class="student-activity-title">${this.escapeHtml(activity.title || 'Activity')}</h4>
                                    </div>
                                    <div class="student-activity-time">
                                        <span class="student-activity-time-chip" title="${this.escapeHtml(exactTime)}">${this.escapeHtml(relativeTime)}</span>
                                        <span class="student-activity-time-detail">${this.escapeHtml(exactTime)}</span>
                                    </div>
                                </div>
                                <p class="student-activity-description">${this.escapeHtml(activity.description || 'Activity recorded.')}</p>
                                <div class="student-activity-footer">
                                    <div class="student-activity-actor">
                                        <span class="student-activity-avatar">${this.escapeHtml(this.getInitials(actorName))}</span>
                                        <div class="student-activity-actor-copy">
                                            <div class="student-activity-actor-row">
                                                <span class="student-activity-actor-name">${this.escapeHtml(actorName)}</span>
                                                <span class="student-activity-role role-${this.escapeHtml(roleClass)}">${this.escapeHtml(actorRole)}</span>
                                            </div>
                                            <span class="student-activity-actor-note">${this.escapeHtml(typeMeta.note)}</span>
                                        </div>
                                    </div>
                                    <span class="student-activity-status">${this.escapeHtml(typeMeta.shortLabel)}</span>
                                </div>
                            </div>
                        </article>
                    `;
                }).join('');

                if (this.elements.activitySummary) {
                    this.elements.activitySummary.textContent = `Showing ${visibleActivities.length} of ${this.activities.length} activities`;
                }

                if (this.elements.activityShowMore) {
                    this.elements.activityShowMore.hidden = visibleActivities.length >= this.activities.length;
                }
            }

            renderRequestPagination(totalPages) {
                if (!this.elements.requestPagination) return;

                if (totalPages <= 1) {
                    this.elements.requestPagination.innerHTML = '';
                    return;
                }

                const buttons = [];
                buttons.push(`
                    <button type="button" class="student-pagination-btn" data-request-page="${this.requestPage - 1}" ${this.requestPage === 1 ? 'disabled' : ''}>
                        Prev
                    </button>
                `);

                this.getVisiblePaginationPages(this.requestPage, totalPages).forEach((page) => {
                    if (page === 'ellipsis') {
                        buttons.push('<span class="student-pagination-ellipsis" aria-hidden="true">&hellip;</span>');
                        return;
                    }

                    buttons.push(`
                        <button type="button" class="student-pagination-btn ${page === this.requestPage ? 'is-active' : ''}" data-request-page="${page}" ${page === this.requestPage ? 'aria-current="page"' : ''}>
                            ${page}
                        </button>
                    `);
                });

                buttons.push(`
                    <button type="button" class="student-pagination-btn" data-request-page="${this.requestPage + 1}" ${this.requestPage === totalPages ? 'disabled' : ''}>
                        Next
                    </button>
                `);

                this.elements.requestPagination.innerHTML = buttons.join('');
            }

            renderBookPagination(totalPages) {
                if (!this.elements.bookPagination) return;

                if (totalPages <= 1) {
                    this.elements.bookPagination.innerHTML = '';
                    return;
                }

                const buttons = [];
                buttons.push(`
                    <button type="button" class="student-pagination-btn" data-book-page="${this.bookPage - 1}" ${this.bookPage === 1 ? 'disabled' : ''}>
                        Prev
                    </button>
                `);

                this.getVisiblePaginationPages(this.bookPage, totalPages).forEach((page) => {
                    if (page === 'ellipsis') {
                        buttons.push('<span class="student-pagination-ellipsis" aria-hidden="true">&hellip;</span>');
                        return;
                    }

                    buttons.push(`
                        <button type="button" class="student-pagination-btn ${page === this.bookPage ? 'is-active' : ''}" data-book-page="${page}" ${page === this.bookPage ? 'aria-current="page"' : ''}>
                            ${page}
                        </button>
                    `);
                });

                buttons.push(`
                    <button type="button" class="student-pagination-btn" data-book-page="${this.bookPage + 1}" ${this.bookPage === totalPages ? 'disabled' : ''}>
                        Next
                    </button>
                `);

                this.elements.bookPagination.innerHTML = buttons.join('');
            }

            renderFinePagination(totalPages) {
                if (!this.elements.finePagination) return;

                if (totalPages <= 1) {
                    this.elements.finePagination.innerHTML = '';
                    return;
                }

                const buttons = [];
                buttons.push(`
                    <button type="button" class="student-pagination-btn" data-fine-page="${this.finePage - 1}" ${this.finePage === 1 ? 'disabled' : ''}>
                        Prev
                    </button>
                `);

                this.getVisiblePaginationPages(this.finePage, totalPages).forEach((page) => {
                    if (page === 'ellipsis') {
                        buttons.push('<span class="student-pagination-ellipsis" aria-hidden="true">&hellip;</span>');
                        return;
                    }

                    buttons.push(`
                        <button type="button" class="student-pagination-btn ${page === this.finePage ? 'is-active' : ''}" data-fine-page="${page}" ${page === this.finePage ? 'aria-current="page"' : ''}>
                            ${page}
                        </button>
                    `);
                });

                buttons.push(`
                    <button type="button" class="student-pagination-btn" data-fine-page="${this.finePage + 1}" ${this.finePage === totalPages ? 'disabled' : ''}>
                        Next
                    </button>
                `);

                this.elements.finePagination.innerHTML = buttons.join('');
            }

            getVisiblePaginationPages(currentPage, totalPages) {
                if (totalPages <= 7) {
                    return Array.from({ length: totalPages }, (_, index) => index + 1);
                }

                const pages = [1];
                const startPage = Math.max(2, currentPage - 1);
                const endPage = Math.min(totalPages - 1, currentPage + 1);

                if (startPage > 2) {
                    pages.push('ellipsis');
                }

                for (let page = startPage; page <= endPage; page += 1) {
                    pages.push(page);
                }

                if (endPage < totalPages - 1) {
                    pages.push('ellipsis');
                }

                pages.push(totalPages);

                return pages;
            }

            getActivityTypeMeta(type) {
                const normalized = String(type || 'profile').toLowerCase();
                const map = {
                    book: { key: 'book', label: 'Book Activity', shortLabel: 'Book', note: 'Circulation event recorded in the library workflow.' },
                    fine: { key: 'fine', label: 'Fine Update', shortLabel: 'Fine', note: 'Fine or payment activity recorded for this student.' },
                    account: { key: 'account', label: 'Account Update', shortLabel: 'Account', note: 'Account access or status change recorded for this student.' },
                    profile: { key: 'profile', label: 'Profile Update', shortLabel: 'Profile', note: 'Profile or privilege information was updated for this student.' },
                };

                return map[normalized] || map.profile;
            }

            getInitials(name) {
                const parts = String(name || 'System').trim().split(/\s+/).filter(Boolean).slice(0, 2);
                const initials = parts.map((part) => part.charAt(0).toUpperCase()).join('');
                return initials || 'SY';
            }

            async toggleStatus() {
                const nextStatus = this.student.status === 'active' ? 'inactive' : 'active';
                const routeTemplate = nextStatus === 'active' ? config.routes.activate : config.routes.deactivate;
                const actionButton = this.elements.statusAction;
                if (!actionButton) return;
                actionButton.disabled = true;
                actionButton.innerHTML = `${icons.spinner}<span>${nextStatus === 'active' ? 'Activating...' : 'Deactivating...'}</span>`;
                try {
                    const data = await this.requestJson(this.buildUrl(routeTemplate, this.student.id), { method: 'POST' });
                    this.student.status = nextStatus;
                    this.elements.root.dataset.studentStatus = nextStatus;
                    this.updateStatusPresentation();
                    this.showToast(data.message || 'Student account status updated.', 'success');
                    this.announce(`Student account ${nextStatus === 'active' ? 'activated' : 'deactivated'}.`);
                } catch (error) {
                    console.error(error);
                    this.showToast(error.message || 'Unable to update the account status.', 'error');
                    this.updateStatusPresentation();
                } finally {
                    actionButton.disabled = false;
                }
            }

            updateStatusPresentation() {
                const status = this.student.status;
                const label = status === 'active' ? 'Deactivate Account' : 'Activate Account';
                if (this.elements.statusBadge) {
                    this.elements.statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    this.elements.statusBadge.className = `student-chip status-${status}`;
                }
                if (this.elements.statusAction) {
                    this.elements.statusAction.className = `student-account-action status-${status}`;
                    this.elements.statusAction.textContent = label;
                }
            }

            openModal(modalId, focusTarget = null) {
                const modal = document.getElementById(modalId);
                if (!modal) return;
                this.lastFocusedElement = document.activeElement;
                this.activeModalId = modalId;
                modal.hidden = false;
                modal.setAttribute('aria-hidden', 'false');
                window.requestAnimationFrame(() => modal.classList.add('is-open'));
                window.setTimeout(() => focusTarget?.focus?.(), 30);
            }

            closeModal(modalId) {
                const modal = document.getElementById(modalId);
                if (!modal) return;
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                window.setTimeout(() => { modal.hidden = true; }, 180);
                if (modalId === 'studentFineConfirmModal') this.pendingFineAction = null;
                if (modalId === 'studentFineWaiveModal') {
                    this.currentFineId = null;
                    this.clearWaiveForm();
                }
                this.activeModalId = null;
                this.lastFocusedElement?.focus?.();
            }

            clearWaiveForm() {
                if (this.elements.fineWaiveReason) this.elements.fineWaiveReason.value = '';
                this.showFineWaiveError('');
            }

            showFineWaiveError(message) {
                if (!this.elements.fineWaiveError || !this.elements.fineWaiveReason) return;
                const hasError = Boolean(message);
                this.elements.fineWaiveError.hidden = !hasError;
                this.elements.fineWaiveError.textContent = message || '';
                this.elements.fineWaiveReason.setAttribute('aria-invalid', hasError ? 'true' : 'false');
            }

            setButtonBusy(button, isBusy, label) {
                if (!button) return;
                button.disabled = isBusy;
                button.innerHTML = isBusy ? `${icons.spinner}<span>${this.escapeHtml(label)}</span>` : `<span>${this.escapeHtml(label)}</span>`;
            }

            getFineRecord(fineId) {
                return this.fines.find((fine) => Number(fine.id) === Number(fineId)) || null;
            }

            normalizeFineStatus(status) {
                const normalized = String(status || 'pending').toLowerCase();
                return normalized === 'paid' || normalized === 'waived' ? normalized : 'pending';
            }

            formatStatusLabel(status) {
                const normalized = this.normalizeFineStatus(status);
                return normalized.charAt(0).toUpperCase() + normalized.slice(1);
            }

            describeFineEmail(status) {
                const normalized = this.normalizeFineStatus(status);
                if (normalized === 'paid') return 'payment confirmation';
                if (normalized === 'waived') return 'waiver update';
                return 'fine reminder';
            }

            formatCurrency(value) {
                return `Rs. ${Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            }

            showToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = `student-toast ${type}`;
                toast.textContent = message;
                this.elements.toast?.appendChild(toast);
                window.setTimeout(() => toast.remove(), 3200);
            }

            announce(message) {
                if (!this.elements.liveRegion) return;
                this.elements.liveRegion.textContent = '';
                window.setTimeout(() => { this.elements.liveRegion.textContent = message; }, 20);
            }

            buildUrl(template, value) {
                return String(template || '').replace('__STUDENT_ID__', value);
            }

            buildFineRoute(template, value) {
                return String(template || '').replace('__FINE_ID__', value);
            }

            async requestJson(url, options = {}) {
                const headers = {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.headers || {}),
                };
                if (options.method && options.method.toUpperCase() !== 'GET' && !(options.body instanceof FormData)) {
                    headers['Content-Type'] = headers['Content-Type'] || 'application/json';
                }
                const response = await fetch(url, { ...options, headers });
                const data = await response.json().catch(() => ({}));
                if (!response.ok || data.success === false) {
                    const error = new Error(data.message || data.errors?.reason?.[0] || data.errors?.remarks?.[0] || 'Something went wrong while processing the request.');
                    error.data = data;
                    throw error;
                }
                return data;
            }

            escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }
        }

        document.addEventListener('DOMContentLoaded', () => new StudentProfilePage());
    })();
</script>
