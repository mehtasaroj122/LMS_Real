<script>
    (() => {
        const config = @json($studentManagementConfig);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        class StudentManagementPage {
            constructor(configuration) {
                this.config = configuration;
                this.state = {
                    search: '',
                    status: 'all',
                    department: 'all',
                    sort: 'created-desc',
                    page: 1,
                    perPage: 10,
                    students: [],
                    pagination: null,
                    stats: null,
                    lastUpdatedAt: null,
                };

                this.elements = {
                    search: document.getElementById('studentSearchInput'),
                    status: document.getElementById('studentStatusFilter'),
                    department: document.getElementById('studentDepartmentFilter'),
                    sort: document.getElementById('studentSortFilter'),
                    entriesSelect: document.getElementById('studentEntriesSelect'),
                    reset: document.getElementById('studentResetFiltersBtn'),
                    create: document.getElementById('studentCreateBtn'),
                    tableShell: document.getElementById('studentTableShell'),
                    tableWrapper: document.getElementById('studentTableWrapper'),
                    tableBody: document.getElementById('studentTableBody'),
                    empty: document.getElementById('studentEmptyState'),
                    emptyMessage: document.getElementById('studentEmptyMessage'),
                    paginationContainer: document.getElementById('studentPaginationContainer'),
                    paginationButtons: document.getElementById('studentPaginationButtons'),
                    recordCount: document.getElementById('studentRecordCount'),
                    paginationTotal: document.getElementById('studentPaginationTotal'),
                    pageInfo: document.getElementById('studentPageInfo'),
                    filterSummary: document.getElementById('studentFilterSummary'),
                    lastUpdated: document.getElementById('studentLastUpdated'),
                    liveRegion: document.getElementById('studentLiveRegion'),
                    totalCount: document.getElementById('studentTotalCount'),
                    activeCount: document.getElementById('studentActiveCount'),
                    inactiveCount: document.getElementById('studentInactiveCount'),
                    totalMeta: document.getElementById('studentTotalMeta'),
                    activeMeta: document.getElementById('studentActiveMeta'),
                    inactiveMeta: document.getElementById('studentInactiveMeta'),
                    statCards: document.querySelectorAll('[data-stat-card]'),
                    modal: document.getElementById('addStudentModal'),
                    modalClose: document.getElementById('studentCreateCloseBtn'),
                    modalCancel: document.getElementById('studentCreateCancelBtn'),
                    form: document.getElementById('studentCreateForm'),
                    submit: document.getElementById('studentCreateSubmitBtn'),
                    toast: document.getElementById('studentToastContainer'),
                };

                this.searchDebounce = this.debounce(() => {
                    this.state.page = 1;
                    this.fetchStudents();
                }, 320);

                this.hydrateStateFromUrl();
                this.syncControlsFromState();
                this.bindEvents();
                this.fetchStudents();
            }

            bindEvents() {
                this.elements.search?.addEventListener('input', (event) => {
                    this.state.search = event.target.value.trim();
                    this.searchDebounce();
                });

                this.elements.status?.addEventListener('change', (event) => {
                    this.state.status = event.target.value;
                    this.state.page = 1;
                    this.fetchStudents();
                });

                this.elements.department?.addEventListener('change', (event) => {
                    this.state.department = event.target.value;
                    this.state.page = 1;
                    this.fetchStudents();
                });

                this.elements.sort?.addEventListener('change', (event) => {
                    this.state.sort = event.target.value;
                    this.state.page = 1;
                    this.fetchStudents();
                });

                this.elements.entriesSelect?.addEventListener('change', (event) => {
                    this.state.perPage = this.normalizePerPage(event.target.value);
                    this.state.page = 1;
                    this.fetchStudents();
                });

                this.elements.reset?.addEventListener('click', () => this.resetFilters());
                this.elements.create?.addEventListener('click', () => this.openModal());
                this.elements.modalClose?.addEventListener('click', () => this.closeModal());
                this.elements.modalCancel?.addEventListener('click', () => this.closeModal());
                this.elements.modal?.addEventListener('click', (event) => {
                    if (event.target === this.elements.modal) {
                        this.closeModal();
                    }
                });

                this.elements.form?.addEventListener('submit', (event) => this.handleCreate(event));

                document.addEventListener('keydown', (event) => {
                    if (event.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) {
                        event.preventDefault();
                        this.elements.search?.focus();
                    }

                    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'n' && this.config.features.create) {
                        event.preventDefault();
                        this.openModal();
                    }

                    if (event.key === 'Escape' && this.elements.modal?.style.display === 'flex') {
                        this.closeModal();
                    }
                });
            }

            async fetchStudents() {
                this.setLoading(true);

                try {
                    const params = new URLSearchParams({
                        search: this.state.search,
                        status: this.state.status,
                        department: this.state.department,
                        sort: this.state.sort,
                        page: String(this.state.page),
                        per_page: String(this.state.perPage),
                    });

                    const response = await fetch(`${this.config.routes.data}?${params.toString()}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Unable to load students.');
                    }

                    this.state.students = Array.isArray(data.students) ? data.students : [];
                    this.state.pagination = data.pagination || null;
                    this.state.page = Math.max(1, Number(this.state.pagination?.current_page || this.state.page || 1));
                    this.state.perPage = this.normalizePerPage(this.state.pagination?.per_page || this.state.perPage);
                    this.state.stats = data.stats || null;
                    this.state.lastUpdatedAt = new Date();
                    this.syncControlsFromState();
                    this.updateBrowserUrl();
                    this.render();
                } catch (error) {
                    console.error(error);
                    this.showToast(error.message || 'Unable to load students right now.', 'error');
                    this.renderTableError();
                } finally {
                    this.setLoading(false);
                }
            }

            render() {
                this.renderStats();
                this.renderToolbarMeta();
                this.renderTable();
                this.renderPagination();
            }

            renderToolbarMeta() {
                const segments = [];
                const total = Number(this.state.pagination?.total || this.state.stats?.totalStudents || 0);

                if (this.state.search) {
                    segments.push(`Search: "${this.state.search}"`);
                }

                if (this.state.status !== 'all') {
                    segments.push(`Status: ${this.capitalize(this.state.status)}`);
                }

                if (this.state.department !== 'all') {
                    segments.push(`Department: ${this.getDepartmentLabel()}`);
                }

                segments.push(`Sort: ${this.getSortLabel(this.state.sort)}`);

                if (this.elements.filterSummary) {
                    this.elements.filterSummary.innerHTML = `${this.escapeHtml(segments.join(' • '))} • <strong>${this.formatNumber(total)}</strong> matching ${total === 1 ? 'student' : 'students'}`;
                }

                if (this.elements.lastUpdated) {
                    this.elements.lastUpdated.textContent = this.state.lastUpdatedAt
                        ? `Updated ${this.formatTimeRelative(this.state.lastUpdatedAt)}`
                        : 'Waiting for data...';
                }
            }

            renderStats() {
                const stats = this.state.stats || {};
                const hasFilters = this.hasActiveFilters();
                const totalStudents = Number(stats.totalStudents || 0);
                const activeStudents = Number(stats.activeStudents || 0);
                const inactiveStudents = Number(stats.inactiveStudents || 0);

                this.elements.totalCount.textContent = this.formatNumber(stats.totalStudents || 0);
                this.elements.activeCount.textContent = this.formatNumber(stats.activeStudents || 0);
                this.elements.inactiveCount.textContent = this.formatNumber(stats.inactiveStudents || 0);

                if (this.elements.totalMeta) {
                    this.elements.totalMeta.textContent = hasFilters
                        ? 'Matches the current search and filter selection'
                        : 'Live overview across all departments and batches';
                }

                if (this.elements.activeMeta) {
                    this.elements.activeMeta.textContent = totalStudents > 0
                        ? `${Math.round((activeStudents / totalStudents) * 100)}% of visible students are active`
                        : 'No active student accounts in this view';
                }

                if (this.elements.inactiveMeta) {
                    this.elements.inactiveMeta.textContent = totalStudents > 0
                        ? `${Math.round((inactiveStudents / totalStudents) * 100)}% of visible students are inactive`
                        : 'No inactive student accounts in this view';
                }

                this.elements.statCards.forEach((card) => card.classList.remove('is-loading'));
            }

            renderTable() {
                if (!this.state.students.length) {
                    this.elements.tableBody.classList.remove('student-table-loading');
                    this.elements.tableBody.innerHTML = '';
                    this.elements.empty.hidden = false;
                    this.elements.emptyMessage.textContent = this.state.search || this.state.status !== 'all' || this.state.department !== 'all'
                        ? 'Try adjusting your search or filter selections.'
                        : 'No student records have been added yet.';
                    return;
                }

                this.elements.empty.hidden = true;
                this.elements.tableBody.classList.remove('student-table-loading');
                this.elements.tableBody.innerHTML = this.state.students.map((student) => this.studentRowMarkup(student)).join('');
                this.bindToggleButtons();
            }

            bindToggleButtons(scope = this.elements.tableBody) {
                scope?.querySelectorAll('[data-action="toggle-status"]').forEach((button) => {
                    if (button.dataset.bound === 'true') {
                        return;
                    }

                    button.addEventListener('click', () => this.handleStatusToggle(button));
                    button.dataset.bound = 'true';
                });
            }

            ensurePaginationState() {
                if (this.state.pagination) {
                    return;
                }

                this.state.pagination = {
                    current_page: this.state.page,
                    last_page: 1,
                    per_page: this.state.perPage,
                    total: 0,
                    from: 0,
                    to: 0,
                };
            }

            syncPaginationState() {
                this.ensurePaginationState();

                const total = Math.max(0, Number(this.state.pagination?.total || 0));
                const lastPage = Math.max(1, Math.ceil(total / this.state.perPage));
                this.state.page = Math.min(this.state.page, lastPage);

                this.state.pagination = {
                    ...this.state.pagination,
                    current_page: this.state.page,
                    last_page: lastPage,
                    per_page: this.state.perPage,
                    total,
                    from: total === 0 ? 0 : ((this.state.page - 1) * this.state.perPage) + 1,
                    to: total === 0
                        ? 0
                        : Math.min((((this.state.page - 1) * this.state.perPage) + Math.max(this.state.students.length, 1)), total),
                };
            }

            matchesCurrentFilters(student) {
                if (!student) {
                    return false;
                }

                const searchValue = this.state.search.trim().toLowerCase();
                const searchHaystack = [
                    student.name,
                    student.email,
                    student.phone,
                    student.rollNo,
                    student.department,
                ]
                    .map((value) => String(value || '').toLowerCase())
                    .join(' ');

                if (searchValue && !searchHaystack.includes(searchValue)) {
                    return false;
                }

                if (this.state.status !== 'all' && String(student.status || '').toLowerCase() !== this.state.status) {
                    return false;
                }

                if (this.state.department !== 'all' && String(student.departmentId || '') !== String(this.state.department)) {
                    return false;
                }

                return true;
            }

            applyLocalStudentUpdate(updatedStudent) {
                const studentId = Number(updatedStudent?.id || 0);
                const existingIndex = this.state.students.findIndex((student) => Number(student.id) === studentId);

                if (existingIndex === -1) {
                    return { requiresFetch: false };
                }

                if (!this.matchesCurrentFilters(updatedStudent)) {
                    this.state.students.splice(existingIndex, 1);
                    this.ensurePaginationState();
                    this.state.pagination.total = Math.max(0, Number(this.state.pagination.total || 0) - 1);
                    this.syncPaginationState();

                    if (!this.state.students.length && Number(this.state.pagination.total || 0) > 0 && this.state.page > 1) {
                        this.state.page -= 1;
                        return { requiresFetch: true };
                    }

                    return { requiresFetch: false };
                }

                this.state.students.splice(existingIndex, 1, updatedStudent);
                return { requiresFetch: false };
            }

            applyLocalStudentCreate(student) {
                if (!student) {
                    return;
                }

                this.ensurePaginationState();
                this.state.pagination.total = Math.max(0, Number(this.state.pagination.total || 0) + 1);

                if (this.matchesCurrentFilters(student) && this.state.page === 1) {
                    this.state.students.unshift(student);

                    if (this.state.sort === 'name-asc' || this.state.sort === 'name-desc') {
                        this.state.students.sort((left, right) => {
                            const leftName = String(left.name || '').toLowerCase();
                            const rightName = String(right.name || '').toLowerCase();

                            return this.state.sort === 'name-asc'
                                ? leftName.localeCompare(rightName)
                                : rightName.localeCompare(leftName);
                        });
                    }

                    this.state.students = this.state.students.slice(0, this.state.perPage);
                }

                this.syncPaginationState();
            }

            renderPagination() {
                const pagination = this.state.pagination;
                const total = Number(pagination?.total || 0);
                const currentPage = Math.max(1, Number(pagination?.current_page || this.state.page || 1));
                const lastPage = Math.max(1, Number(pagination?.last_page || 1));
                const from = Number(pagination?.from || 0);
                const to = Number(pagination?.to || 0);

                if (!pagination) {
                    this.elements.paginationButtons.innerHTML = '';
                    if (this.elements.paginationContainer) {
                        this.elements.paginationContainer.hidden = true;
                    }
                    return;
                }

                if (this.elements.recordCount) {
                    this.elements.recordCount.textContent = total > 0
                        ? `${this.formatNumber(from)}-${this.formatNumber(to)}`
                        : '0-0';
                }

                if (this.elements.paginationTotal) {
                    this.elements.paginationTotal.textContent = this.formatNumber(total);
                }

                if (this.elements.pageInfo) {
                    this.elements.pageInfo.textContent = `Page ${currentPage} of ${lastPage}`;
                }

                if (this.elements.paginationContainer) {
                    this.elements.paginationContainer.hidden = total === 0;
                }

                this.elements.paginationButtons.innerHTML = '';

                if (total === 0) {
                    return;
                }

                const buttons = [
                    this.paginationButton('&larr; Previous', currentPage - 1, currentPage === 1, false, 'Previous page'),
                ];

                this.buildPaginationSequence(currentPage, lastPage).forEach((page) => {
                    if (page === null) {
                        buttons.push('<span class="admin-table-pagination-ellipsis" aria-hidden="true">&hellip;</span>');
                        return;
                    }

                    buttons.push(this.paginationButton(String(page), page, false, page === currentPage, `Page ${page}`));
                });

                buttons.push(this.paginationButton('Next &rarr;', currentPage + 1, currentPage === lastPage, false, 'Next page'));
                this.elements.paginationButtons.innerHTML = buttons.join('');

                this.elements.paginationButtons.querySelectorAll('[data-page]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const nextPage = Number(button.getAttribute('data-page'));

                        if (!Number.isNaN(nextPage) && nextPage > 0 && nextPage !== this.state.page) {
                            this.state.page = nextPage;
                            this.fetchStudents();
                        }
                    });
                });
            }

            studentRowMarkup(student) {
                const fallbackInitial = String(student.name || 'S').trim().charAt(0).toUpperCase() || 'S';
                const avatar = student.avatar
                    ? `<span class="student-avatar"><img src="${this.escapeHtml(student.avatar)}" alt="${this.escapeHtml(student.name)}"></span>`
                    : `<span class="student-avatar">${this.escapeHtml(fallbackInitial)}</span>`;
                const statusIcon = student.status === 'active' ? 'fas fa-toggle-on' : 'fas fa-toggle-off';
                const statusTitle = student.status === 'active' ? 'Deactivate account' : 'Activate account';
                const statusClass = student.status === 'active' ? 'status-active' : 'status-inactive';
                const statusIconMarkup = student.status === 'active'
                    ? '<i class="fas fa-check-circle" style="font-size: 10px;"></i>'
                    : '<i class="fas fa-times-circle" style="font-size: 10px;"></i>';

                return `
                    <tr data-student-id="${student.id}">
                        <td>
                            <div class="student-cell">
                                ${avatar}
                                <div class="student-info">
                                    <span class="student-name">${this.escapeHtml(student.name)}</span>
                                    <div class="text-muted">${this.escapeHtml(student.rollNo)}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted">${this.escapeHtml(student.email)}</td>
                        <td class="text-muted">${this.escapeHtml(student.phone)}</td>
                        <td class="text-muted">${this.escapeHtml(student.department)}</td>
                        <td class="text-muted">${this.escapeHtml(student.batch)}</td>
                        <td><span class="status-badge ${statusClass}">${statusIconMarkup} ${this.escapeHtml(student.statusLabel)}</span></td>
                        <td>
                            <div class="action-buttons">
                                <a class="action-btn" href="${this.buildUrl(this.config.routes.show, student.id)}" title="View details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                ${student.canToggleStatus ? `
                                    <button
                                        type="button"
                                        class="action-btn"
                                        data-action="toggle-status"
                                        data-student-id="${student.id}"
                                        data-status="${this.escapeHtml(student.status)}"
                                        title="${statusTitle}"
                                    >
                                        <i class="${statusIcon}"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            }

            paginationButton(label, page, disabled = false, active = false, ariaLabel = '') {
                return `
                    <button
                        type="button"
                        class="admin-table-pagination-link${active ? ' is-active' : ''}${disabled ? ' is-disabled' : ''}"
                        data-page="${page}"
                        aria-label="${this.escapeHtml(active ? `Current page, ${ariaLabel || label}` : (ariaLabel || `Page ${label}`))}"
                        ${disabled ? 'disabled aria-disabled="true"' : ''}
                        ${active ? 'aria-current="page"' : ''}
                    >
                        ${label}
                    </button>
                `;
            }

            buildPaginationSequence(currentPage, lastPage) {
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

            async handleStatusToggle(button) {
                const studentId = button.getAttribute('data-student-id');
                const currentStatus = button.getAttribute('data-status');
                const routeTemplate = currentStatus === 'active' ? this.config.routes.deactivate : this.config.routes.activate;
                const pendingIcon = '<i class="fas fa-spinner fa-spin"></i>';

                button.disabled = true;
                button.innerHTML = pendingIcon;

                try {
                    const response = await fetch(this.buildMutationUrl(this.buildUrl(routeTemplate, studentId)), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Unable to update account status.');
                    }

                    if (data.stats) {
                        this.state.stats = data.stats;
                    }

                    const updatedStudent = data.student || this.state.students.find((student) => Number(student.id) === Number(studentId));
                    const mutation = updatedStudent ? this.applyLocalStudentUpdate(updatedStudent) : { requiresFetch: true };
                    this.state.lastUpdatedAt = new Date();

                    if (mutation.requiresFetch) {
                        await this.fetchStudents();
                    } else {
                        this.renderStats();
                        this.renderToolbarMeta();
                        this.renderPagination();
                        this.patchStudentRow(updatedStudent, studentId);
                    }

                    const isActive = String(updatedStudent?.status || '').toLowerCase() === 'active';
                    this.showToast({
                        title: isActive ? 'Student Activated' : 'Student Deactivated',
                        message: `${isActive ? 'Activated' : 'Deactivated'} ${updatedStudent?.name || 'student account'}.`,
                        detail: updatedStudent?.rollNo ? `Student ID ${updatedStudent.rollNo}` : '',
                        icon: isActive ? 'fas fa-user-check' : 'fas fa-user-slash',
                    }, isActive ? 'success' : 'warning');
                    this.announce(`${updatedStudent?.name || 'Student'} has been ${isActive ? 'activated' : 'deactivated'}.`);
                } catch (error) {
                    console.error(error);
                    this.showToast(error.message || 'Unable to update student status.', 'error');
                    this.renderTable();
                    this.renderPagination();
                }
            }

            patchStudentRow(student, fallbackStudentId) {
                const studentId = Number(student?.id || fallbackStudentId || 0);
                const existingRow = this.elements.tableBody?.querySelector(`[data-student-id="${studentId}"]`);

                if (!this.state.students.length) {
                    this.renderTable();
                    return;
                }

                if (!student || !this.matchesCurrentFilters(student)) {
                    existingRow?.remove();

                    if (!this.elements.tableBody?.querySelector('tr')) {
                        this.renderTable();
                    }

                    return;
                }

                if (!existingRow) {
                    this.renderTable();
                    return;
                }

                const fragment = document.createElement('tbody');
                fragment.innerHTML = this.studentRowMarkup(student).trim();
                const nextRow = fragment.firstElementChild;

                if (!nextRow) {
                    this.renderTable();
                    return;
                }

                existingRow.replaceWith(nextRow);
                this.bindToggleButtons(this.elements.tableBody);
            }

            openModal() {
                this.clearFormErrors();
                if (this.elements.modal) {
                    this.elements.modal.style.display = 'flex';
                    this.elements.modal.setAttribute('aria-hidden', 'false');
                }
                document.body.style.overflow = 'hidden';
                document.getElementById('student_name')?.focus();
            }

            closeModal() {
                if (this.elements.modal) {
                    this.elements.modal.style.display = 'none';
                    this.elements.modal.setAttribute('aria-hidden', 'true');
                }
                document.body.style.overflow = '';
                this.elements.form?.reset();
                this.clearFormErrors();
            }

            async handleCreate(event) {
                event.preventDefault();
                this.clearFormErrors();

                const values = Object.fromEntries(new FormData(this.elements.form).entries());
                const errors = this.validateStudent(values);

                if (Object.keys(errors).length) {
                    this.showFormErrors(errors);
                    this.showToast('Please fix the highlighted fields before saving.', 'warning');
                    return;
                }

                this.elements.submit.disabled = true;
                this.elements.submit.innerHTML = '<span class="student-spinner" aria-hidden="true"></span><span>Creating...</span>';

                try {
                    const response = await fetch(this.buildMutationUrl(this.config.routes.store), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(this.elements.form),
                    });

                    const data = await response.json();

                    if (response.status === 422) {
                        this.showFormErrors(this.normalizeErrors(data.errors || {}));
                        throw new Error('Validation failed.');
                    }

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Unable to create student.');
                    }

                    this.closeModal();
                    if (data.stats) {
                        this.state.stats = data.stats;
                    }

                    if (data.student) {
                        this.applyLocalStudentCreate(data.student);
                    }

                    this.state.lastUpdatedAt = new Date();
                    this.render();
                    this.showToast({
                        title: 'Student Added',
                        message: `Created a new record for ${data.student?.name || 'the student'}.`,
                        detail: data.student?.rollNo ? `Student ID ${data.student.rollNo}` : '',
                        icon: 'fas fa-user-plus',
                    }, 'success');
                    this.announce(`${data.student?.name || 'Student'} created successfully.`);
                } catch (error) {
                    if (error.message !== 'Validation failed.') {
                        console.error(error);
                        this.showToast(error.message || 'Unable to create student.', 'error');
                    }
                } finally {
                    this.elements.submit.disabled = false;
                    this.elements.submit.textContent = 'Add Student';
                }
            }

            validateStudent(values) {
                const errors = {};
                const phonePattern = /^\+[1-9]\d{7,14}$/;
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                const rollPattern = /^[A-Za-z0-9-]+$/;
                const namePattern = /^[A-Za-z ]+$/;

                const name = String(values.name || '').trim();
                const email = String(values.email || '').trim();
                const phone = String(values.phone || '').trim();
                const dob = String(values.date_of_birth || '').trim();
                const rollNo = String(values.roll_no || '').trim();
                const departmentId = String(values.department_id || '').trim();
                const batch = String(values.batch || '').trim();
                const semester = String(values.semester || '').trim();
                const address = String(values.address || '').trim();

                if (!name) {
                    errors.name = 'Enter the student\'s full name.';
                } else if (name.length < 2) {
                    errors.name = 'Full name must be at least 2 characters long.';
                } else if (name.length > 100) {
                    errors.name = 'Full name must be 100 characters or fewer.';
                } else if (!namePattern.test(name)) {
                    errors.name = 'Full name can use letters and spaces only.';
                }

                if (!email) {
                    errors.email = 'Enter the student\'s email address.';
                } else if (!emailPattern.test(email)) {
                    errors.email = 'Enter a valid email address, like student@example.com.';
                }

                if (!phone) {
                    errors.phone = 'Enter the student\'s phone number with country code.';
                } else if (!phonePattern.test(phone)) {
                    errors.phone = 'Enter a valid phone number with country code, like +9779812345678.';
                }

                if (!dob) {
                    errors.date_of_birth = 'Select the student\'s date of birth.';
                } else {
                    const birthDate = new Date(dob);
                    const today = new Date();

                    if (Number.isNaN(birthDate.getTime()) || birthDate >= today) {
                        errors.date_of_birth = 'Date of birth must be earlier than today.';
                    } else {
                        let age = today.getFullYear() - birthDate.getFullYear();
                        const monthDiff = today.getMonth() - birthDate.getMonth();

                        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                            age -= 1;
                        }

                        if (age < 14 || age > 100) {
                            errors.date_of_birth = 'Student age must be between 14 and 100 years.';
                        }
                    }
                }

                if (!rollNo) {
                    errors.roll_no = 'Enter the student ID.';
                } else if (rollNo.length < 3) {
                    errors.roll_no = 'Student ID must be at least 3 characters long.';
                } else if (rollNo.length > 30) {
                    errors.roll_no = 'Student ID must be 30 characters or fewer.';
                } else if (!rollPattern.test(rollNo)) {
                    errors.roll_no = 'Student ID can use letters, numbers, and hyphens only.';
                }

                if (!departmentId) {
                    errors.department_id = 'Select a department.';
                }

                if (!batch) {
                    errors.batch = 'Enter the batch year.';
                } else if (!/^(19|20)\d{2}$/.test(batch)) {
                    errors.batch = 'Batch year must be a 4-digit year.';
                }

                if (!semester) {
                    errors.semester = 'Enter the semester number.';
                } else {
                    const semesterNumber = Number(semester);
                    if (!Number.isInteger(semesterNumber) || semesterNumber < 1 || semesterNumber > 12) {
                        errors.semester = 'Semester must be a number between 1 and 12.';
                    }
                }

                if (!address) {
                    errors.address = 'Enter the student\'s address.';
                } else if (address.length < 10) {
                    errors.address = 'Address must be at least 10 characters long.';
                } else if (address.length > 255) {
                    errors.address = 'Address must be 255 characters or fewer.';
                } else if (/<[^>]*>/.test(address)) {
                    errors.address = 'Address contains unsupported characters.';
                }

                return errors;
            }

            normalizeErrors(errors) {
                return Object.keys(errors).reduce((carry, key) => {
                    carry[key] = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
                    return carry;
                }, {});
            }

            showFormErrors(errors) {
                Object.entries(errors).forEach(([field, message]) => {
                    const target = this.elements.form?.querySelector(`[data-error-for="${field}"]`);

                    if (target) {
                        target.textContent = message;
                        target.classList.add('show');
                    }
                });
            }

            clearFormErrors() {
                this.elements.form?.querySelectorAll('.student-field-error').forEach((element) => {
                    element.textContent = '';
                    element.classList.remove('show');
                });
            }

            resetFilters() {
                this.state.search = '';
                this.state.status = 'all';
                this.state.department = 'all';
                this.state.sort = 'created-desc';
                this.state.page = 1;

                if (this.elements.search) {
                    this.elements.search.value = '';
                }

                if (this.elements.status) {
                    this.elements.status.value = 'all';
                }

                if (this.elements.department) {
                    this.elements.department.value = 'all';
                }

                if (this.elements.sort) {
                    this.elements.sort.value = 'created-desc';
                }

                this.fetchStudents();
                this.announce('Student filters reset.');
            }

            hydrateStateFromUrl() {
                const params = new URLSearchParams(window.location.search);
                this.state.search = String(params.get('search') || '').trim();
                this.state.status = String(params.get('status') || 'all').toLowerCase();
                this.state.department = String(params.get('department') || 'all');
                this.state.sort = String(params.get('sort') || 'created-desc').toLowerCase();
                this.state.page = Math.max(1, Number(params.get('page')) || 1);
                this.state.perPage = this.normalizePerPage(params.get('per_page'));
            }

            syncControlsFromState() {
                if (this.elements.search) {
                    this.elements.search.value = this.state.search;
                }

                if (this.elements.status) {
                    this.elements.status.value = this.state.status;
                }

                if (this.elements.department) {
                    this.elements.department.value = this.state.department;
                }

                if (this.elements.sort) {
                    this.elements.sort.value = this.state.sort;
                }

                if (this.elements.entriesSelect) {
                    this.elements.entriesSelect.value = String(this.state.perPage);
                }
            }

            normalizePerPage(value) {
                const allowedValues = [10, 20, 50, 100];
                const perPage = Number(value);
                return allowedValues.includes(perPage) ? perPage : 10;
            }

            updateBrowserUrl() {
                const params = new URLSearchParams();

                if (this.state.search) params.set('search', this.state.search);
                if (this.state.status !== 'all') params.set('status', this.state.status);
                if (this.state.department !== 'all') params.set('department', this.state.department);
                if (this.state.sort !== 'created-desc') params.set('sort', this.state.sort);
                if (this.state.page > 1) params.set('page', String(this.state.page));
                if (this.state.perPage !== 10) params.set('per_page', String(this.state.perPage));

                const nextUrl = params.toString()
                    ? `${window.location.pathname}?${params.toString()}`
                    : window.location.pathname;

                window.history.replaceState({ url: nextUrl }, '', nextUrl);
            }

            hasActiveFilters() {
                return this.state.search !== '' || this.state.status !== 'all' || this.state.department !== 'all';
            }

            setLoading(isLoading) {
                this.elements.tableShell?.classList.toggle('is-loading', isLoading);
                this.elements.tableWrapper?.setAttribute('aria-busy', String(isLoading));
                this.elements.statCards.forEach((card) => card.classList.toggle('is-loading', isLoading));

                if (isLoading) {
                    if (this.elements.empty) {
                        this.elements.empty.hidden = true;
                    }

                    if (this.elements.paginationButtons) {
                        this.elements.paginationButtons.innerHTML = '';
                    }

                    if (this.elements.paginationContainer) {
                        this.elements.paginationContainer.hidden = true;
                    }

                    this.elements.tableBody?.classList.add('student-table-loading');
                    this.elements.tableBody.innerHTML = this.tableSkeletonMarkup();
                    return;
                }

                this.elements.tableBody?.classList.remove('student-table-loading');
            }

            renderTableError() {
                this.elements.tableBody.classList.remove('student-table-loading');
                this.elements.tableBody.innerHTML = '';
                this.elements.empty.hidden = false;
                this.elements.emptyMessage.textContent = 'We could not load students right now. Please try again.';
                this.elements.paginationButtons.innerHTML = '';
                if (this.elements.paginationContainer) {
                    this.elements.paginationContainer.hidden = true;
                }
            }

            dismissToast(toast) {
                if (!toast) {
                    return;
                }

                toast.classList.add('is-leaving');
                window.setTimeout(() => toast.remove(), 180);
            }

            showToast(message, type = 'info') {
                const payload = typeof message === 'object' && message !== null
                    ? message
                    : {
                        title: type === 'error' ? 'Action Failed' : 'Update Complete',
                        message: String(message || ''),
                    };
                const toast = document.createElement('div');
                toast.className = `student-toast ${type}`;
                toast.innerHTML = `
                    <div class="student-toast-icon" aria-hidden="true">
                        <i class="${this.escapeHtml(payload.icon || this.toastIcon(type))}"></i>
                    </div>
                    <div class="student-toast-copy">
                        <div class="student-toast-title">${this.escapeHtml(payload.title || 'Notice')}</div>
                        <div class="student-toast-message">${this.escapeHtml(payload.message || '')}</div>
                        ${payload.detail ? `<div class="student-toast-detail">${this.escapeHtml(payload.detail)}</div>` : ''}
                    </div>
                    <button type="button" class="student-toast-close" aria-label="Dismiss notification">
                        <i class="fas fa-times"></i>
                    </button>
                    <span class="student-toast-progress" aria-hidden="true"></span>
                `;
                this.elements.toast?.appendChild(toast);
                toast.querySelector('.student-toast-close')?.addEventListener('click', () => this.dismissToast(toast));

                window.setTimeout(() => this.dismissToast(toast), 4200);
            }

            toastIcon(type) {
                if (type === 'success') return 'fas fa-check-circle';
                if (type === 'warning') return 'fas fa-triangle-exclamation';
                if (type === 'error') return 'fas fa-circle-xmark';
                return 'fas fa-circle-info';
            }

            announce(message) {
                if (this.elements.liveRegion) {
                    this.elements.liveRegion.textContent = message;
                }
            }

            buildUrl(template, value) {
                return String(template || '').replace('__STUDENT_ID__', value);
            }

            buildMutationUrl(url) {
                const params = new URLSearchParams({
                    search: this.state.search,
                    status: this.state.status,
                    department: this.state.department,
                    sort: this.state.sort,
                });

                return `${url}${url.includes('?') ? '&' : '?'}${params.toString()}`;
            }

            formatNumber(value) {
                return new Intl.NumberFormat().format(Number(value || 0));
            }

            formatTimeRelative(date) {
                const seconds = Math.max(0, Math.floor((Date.now() - date.getTime()) / 1000));

                if (seconds < 5) return 'just now';
                if (seconds < 60) return `${seconds}s ago`;

                const minutes = Math.floor(seconds / 60);
                if (minutes < 60) return `${minutes}m ago`;

                const hours = Math.floor(minutes / 60);
                if (hours < 24) return `${hours}h ago`;

                return `${Math.floor(hours / 24)}d ago`;
            }

            getDepartmentLabel() {
                return this.elements.department?.selectedOptions?.[0]?.textContent?.trim() || 'Selected department';
            }

            getSortLabel(sort) {
                const labels = {
                    'created-desc': 'Newest first',
                    'created-asc': 'Oldest first',
                    'name-asc': 'Alphabetical A-Z',
                    'name-desc': 'Alphabetical Z-A',
                };

                return labels[sort] || 'Newest first';
            }

            capitalize(value) {
                const input = String(value || '');
                return input ? input.charAt(0).toUpperCase() + input.slice(1) : '';
            }

            escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            debounce(callback, delay = 300) {
                let timeoutId;

                return (...args) => {
                    window.clearTimeout(timeoutId);
                    timeoutId = window.setTimeout(() => callback(...args), delay);
                };
            }

            tableSkeletonMarkup(rows = 5) {
                return Array.from({ length: rows }, () => `
                    <tr>
                        <td><span class="student-skeleton-line long"></span></td>
                        <td><span class="student-skeleton-line medium"></span></td>
                        <td><span class="student-skeleton-line short"></span></td>
                        <td><span class="student-skeleton-line medium"></span></td>
                        <td><span class="student-skeleton-line short"></span></td>
                        <td><span class="student-skeleton-line short"></span></td>
                        <td><span class="student-skeleton-line long"></span></td>
                    </tr>
                `).join('');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            new StudentManagementPage(config);
        });
    })();
</script>
