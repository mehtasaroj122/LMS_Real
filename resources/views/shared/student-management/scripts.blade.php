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
                    reset: document.getElementById('studentResetFiltersBtn'),
                    create: document.getElementById('studentCreateBtn'),
                    tableShell: document.getElementById('studentTableShell'),
                    tableWrapper: document.getElementById('studentTableWrapper'),
                    tableBody: document.getElementById('studentTableBody'),
                    empty: document.getElementById('studentEmptyState'),
                    emptyMessage: document.getElementById('studentEmptyMessage'),
                    paginationButtons: document.getElementById('studentPaginationButtons'),
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
                    this.state.stats = data.stats || null;
                    this.state.lastUpdatedAt = new Date();
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

                this.elements.tableBody.querySelectorAll('[data-action="toggle-status"]').forEach((button) => {
                    button.addEventListener('click', () => this.handleStatusToggle(button));
                });
            }

            renderPagination() {
                const pagination = this.state.pagination;

                if (!pagination) {
                    this.elements.paginationButtons.innerHTML = '';
                    return;
                }

                this.elements.paginationButtons.innerHTML = '';

                if (pagination.last_page <= 1) {
                    return;
                }

                const buttons = [
                    this.paginationButton('Prev', pagination.current_page - 1, pagination.current_page === 1),
                ];

                for (let page = 1; page <= pagination.last_page; page += 1) {
                    buttons.push(this.paginationButton(page, page, false, page === pagination.current_page));
                }

                buttons.push(this.paginationButton('Next', pagination.current_page + 1, pagination.current_page === pagination.last_page));
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
                const avatar = student.avatar
                    ? `<span class="student-avatar"><img src="${this.escapeHtml(student.avatar)}" alt="${this.escapeHtml(student.name)}"></span>`
                    : `<span class="student-avatar">${this.escapeHtml(student.initials || 'ST')}</span>`;
                const statusIcon = student.status === 'active' ? 'fas fa-toggle-on' : 'fas fa-toggle-off';
                const statusTitle = student.status === 'active' ? 'Deactivate account' : 'Activate account';
                const statusClass = student.status === 'active' ? 'status-active' : 'status-inactive';
                const statusIconMarkup = student.status === 'active'
                    ? '<i class="fas fa-check-circle" style="font-size: 10px;"></i>'
                    : '<i class="fas fa-times-circle" style="font-size: 10px;"></i>';

                return `
                    <tr>
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

            paginationButton(label, page, disabled = false, active = false) {
                return `
                    <button
                        type="button"
                        class="student-page-btn${active ? ' active' : ''}"
                        data-page="${page}"
                        ${disabled ? 'disabled' : ''}
                    >
                        ${label}
                    </button>
                `;
            }

            async handleStatusToggle(button) {
                const studentId = button.getAttribute('data-student-id');
                const currentStatus = button.getAttribute('data-status');
                const routeTemplate = currentStatus === 'active' ? this.config.routes.deactivate : this.config.routes.activate;
                const pendingIcon = '<i class="fas fa-spinner fa-spin"></i>';

                button.disabled = true;
                button.innerHTML = pendingIcon;

                try {
                    const response = await fetch(this.buildUrl(routeTemplate, studentId), {
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

                    this.showToast(data.message || 'Student status updated successfully.', 'success');
                    this.announce(`Student status updated for record ${studentId}.`);
                    await this.fetchStudents();
                } catch (error) {
                    console.error(error);
                    this.showToast(error.message || 'Unable to update student status.', 'error');
                    await this.fetchStudents();
                }
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
                    const response = await fetch(this.config.routes.store, {
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
                    this.showToast(data.message || 'Student created successfully.', 'success');
                    this.announce('Student created successfully.');
                    await this.fetchStudents();
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
            }

            showToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = `student-toast ${type}`;
                toast.textContent = message;
                this.elements.toast?.appendChild(toast);

                window.setTimeout(() => toast.remove(), 3200);
            }

            announce(message) {
                if (this.elements.liveRegion) {
                    this.elements.liveRegion.textContent = message;
                }
            }

            buildUrl(template, value) {
                return String(template || '').replace('__STUDENT_ID__', value);
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
