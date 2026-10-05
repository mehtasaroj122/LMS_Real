<script>
(() => {
    const root = document.getElementById('bookCopiesPage');
    if (!root) return;

    const feedback = new window.ActionFeedbackUI();
    window.bookCopiesFeedback = feedback;
    const flashKey = `book-copies-toast:${window.location.pathname}`;
    const notify = (type, title, message) => feedback.showToast(type, title, message);
    const flash = sessionStorage.getItem(flashKey);
    if (flash) {
        sessionStorage.removeItem(flashKey);
        try {
            const toast = JSON.parse(flash);
            notify('success', toast.title, toast.message);
        } catch (_) { /* Ignore stale flash data. */ }
    }

    const filters = document.getElementById('copyFilters');
    const search = document.getElementById('copySearch');
    const status = document.getElementById('copyStatusFilter');
    const type = document.getElementById('copyTypeFilter');
    const entries = document.getElementById('copyEntriesSelect');
    const tableBody = document.getElementById('copyTableBody');
    const pagination = document.getElementById('copyPagination');
    const skeletonRows = document.getElementById('copyTableSkeleton').innerHTML;
    let lastRowsHtml = tableBody.innerHTML;
    let lastPaginationHtml = pagination.innerHTML;
    let currentPage = Number(new URLSearchParams(window.location.search).get('page')) || 1;
    let activeRequest;
    let requestNumber = 0;

    const pageUrl = (page) => {
        const url = new URL(filters.action);
        const params = new URLSearchParams();
        if (search.value.trim()) params.set('search', search.value.trim());
        if (status.value !== 'all') params.set('status', status.value);
        if (type.value !== 'all') params.set('book_type', type.value);
        if (page > 1) params.set('page', String(page));
        if (entries.value !== '10') params.set('per_page', entries.value);
        url.search = params.toString();
        return url;
    };

    const loadCopies = async (page = 1, updateUrl = true) => {
        activeRequest?.abort();
        const controller = new AbortController();
        activeRequest = controller;
        const requestId = ++requestNumber;
        const url = pageUrl(page);
        url.searchParams.set('ajax_copies', '1');
        tableBody.setAttribute('aria-busy', 'true');
        tableBody.innerHTML = skeletonRows;
        pagination.innerHTML = '';
        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            });
            if (!response.ok) throw new Error('Unable to load copies.');
            const data = await response.json();
            if (!data.success) throw new Error(data.message || 'Unable to load copies.');
            if (requestId !== requestNumber) return;
            currentPage = Number(data.current_page) || 1;
            entries.value = String(data.per_page || entries.value);
            tableBody.innerHTML = data.tableRows || '';
            pagination.innerHTML = data.pagination || '';
            lastRowsHtml = tableBody.innerHTML;
            lastPaginationHtml = pagination.innerHTML;
            for (const [key, value] of Object.entries(data.summary || {})) {
                const node = root.querySelector(`[data-copy-summary="${key}"]`);
                if (node) node.textContent = value;
            }
            if (updateUrl) window.history.replaceState(null, '', pageUrl(currentPage));
        } catch (error) {
            if (error.name !== 'AbortError' && requestId === requestNumber) {
                tableBody.innerHTML = lastRowsHtml;
                pagination.innerHTML = lastPaginationHtml;
                notify('error', 'Load failed', error.message || 'Unable to load copies.');
            }
        } finally {
            if (requestId === requestNumber) tableBody.removeAttribute('aria-busy');
        }
    };
    window.bookCopiesRefresh = () => loadCopies(currentPage);
    let searchTimer;
    search.addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => loadCopies(1), 300);
    });
    [status, type, entries].forEach(control => control.addEventListener('change', () => loadCopies(1)));
    filters.addEventListener('submit', event => { event.preventDefault(); window.clearTimeout(searchTimer); loadCopies(1); });
    document.getElementById('resetCopyFilters').addEventListener('click', () => {
        window.clearTimeout(searchTimer);
        search.value = '';
        status.value = 'all';
        type.value = 'all';
        loadCopies(1);
    });
    pagination.addEventListener('click', event => {
        const link = event.target.closest('a[href]');
        if (!link || !pagination.contains(link)) return;
        event.preventDefault();
        loadCopies(Number(new URL(link.href).searchParams.get('page')) || 1);
    });
    window.addEventListener('popstate', () => {
        const params = new URLSearchParams(window.location.search);
        search.value = params.get('search') || '';
        status.value = params.get('status') || 'all';
        type.value = params.get('book_type') || 'all';
        entries.value = params.get('per_page') || '10';
        loadCopies(Number(params.get('page')) || 1, false);
    });

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const readResponse = async (response) => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'The action could not be completed.');
        return data;
    };

    tableBody.addEventListener('click', async event => {
        const editButton = event.target.closest('.edit-copy-btn');
        if (editButton) {
            const row = document.getElementById(`edit-row-${editButton.dataset.copyId}`);
            const willOpen = row.style.display !== 'table-row';
            root.querySelectorAll('.book-copies-edit-row').forEach(other => { other.style.display = 'none'; });
            row.style.display = willOpen ? 'table-row' : 'none';
            return;
        }
        const button = event.target.closest('.delete-copy-btn');
        if (!button) return;
        const confirmed = await feedback.confirm({
            title: 'Delete physical copy?',
            message: 'Copies with borrowing history cannot be deleted.',
            confirmText: 'Delete Copy',
            variant: 'danger',
            buttonVariant: 'danger',
        });
        if (!confirmed) return;
        button.disabled = true;
        try {
            const response = await fetch(`${root.dataset.baseUrl}/${button.dataset.copyId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            });
            const data = await readResponse(response);
            await loadCopies(currentPage);
            notify('success', 'Copy deleted', data.message || 'Physical copy deleted successfully.');
        } catch (error) {
            notify('error', 'Delete failed', error.message || 'Unable to delete this copy.');
            button.disabled = false;
        }
    });

    tableBody.addEventListener('submit', async event => {
        const form = event.target.closest('.copy-edit-form');
        if (!form) return;
        event.preventDefault();
        if (!form.reportValidity()) return;
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        try {
            const response = await fetch(`${root.dataset.baseUrl}/${form.dataset.copyId}`, {
                method: 'PUT',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: new FormData(form),
            });
            await readResponse(response);
            await loadCopies(currentPage);
            notify('success', 'Copy updated', 'Changes saved successfully.');
        } catch (error) {
            notify('error', 'Save failed', error.message || 'Unable to update this copy.');
            button.disabled = false;
        }
    });
})();
</script>
