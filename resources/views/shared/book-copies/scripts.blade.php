<script>
(() => {
    const root = document.getElementById('bookCopiesPage');
    if (!root) return;

    const feedback = new window.ActionFeedbackUI();
    window.bookCopiesFeedback = feedback;
    const flashKey = `book-copies-toast:${window.location.pathname}`;
    const notify = (type, title, message, timeout = 4200, detail = '') => feedback.showToast(type, title, message, timeout, detail);
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
    const selectAll = document.getElementById('selectAllCopies');
    const bulkAction = document.getElementById('copyBulkAction');
    const applyBulkAction = document.getElementById('applyCopyBulkAction');
    const clearSelectionButton = document.getElementById('clearCopySelection');
    const selectionCount = document.getElementById('copySelectionCount');
    const skeletonRows = document.getElementById('copyTableSkeleton').innerHTML;
    const selectedCopyIds = new Set();
    let lastRowsHtml = tableBody.innerHTML;
    let lastPaginationHtml = pagination.innerHTML;
    let currentPage = Number(new URLSearchParams(window.location.search).get('page')) || 1;
    let activeRequest;
    let requestNumber = 0;
    let bulkBusy = false;

    const selectableCheckboxes = () => Array.from(tableBody.querySelectorAll('.copy-select-checkbox:not(:disabled)'));
    const syncSelection = () => {
        const checkboxes = selectableCheckboxes();
        checkboxes.forEach(checkbox => { checkbox.checked = selectedCopyIds.has(Number(checkbox.value)); });
        const checkedCount = checkboxes.filter(checkbox => checkbox.checked).length;
        selectAll.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        selectAll.disabled = checkboxes.length === 0 || bulkBusy;
        selectionCount.textContent = `${selectedCopyIds.size} ${selectedCopyIds.size === 1 ? 'copy' : 'copies'} selected`;
        clearSelectionButton.disabled = selectedCopyIds.size === 0 || bulkBusy;
        applyBulkAction.disabled = bulkBusy || !bulkAction.value || (bulkAction.value === 'delete_selected' && selectedCopyIds.size === 0);
    };

    const clearSelection = () => {
        selectedCopyIds.clear();
        syncSelection();
    };

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
            selectedCopyIds.clear();
            syncSelection();
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

    selectAll.addEventListener('change', () => {
        selectableCheckboxes().forEach(checkbox => {
            const id = Number(checkbox.value);
            checkbox.checked = selectAll.checked;
            if (selectAll.checked) selectedCopyIds.add(id);
            else selectedCopyIds.delete(id);
        });
        syncSelection();
    });

    tableBody.addEventListener('change', event => {
        const checkbox = event.target.closest('.copy-select-checkbox');
        if (!checkbox || checkbox.disabled) return;
        const id = Number(checkbox.value);
        if (checkbox.checked) selectedCopyIds.add(id);
        else selectedCopyIds.delete(id);
        syncSelection();
    });

    clearSelectionButton.addEventListener('click', clearSelection);
    bulkAction.addEventListener('change', syncSelection);
    syncSelection();

    const actionLabel = action => ({
        delete_selected: 'Delete Selected Copies',
        delete_available: 'Delete Available Copies',
        delete_damaged: 'Delete Damaged Copies',
        delete_lost: 'Delete Lost Copies',
        delete_all_eligible: 'Delete All Eligible Copies',
    }[action] || 'Delete Physical Copies');

    const previewDetail = preview => {
        const lines = [
            `Book: ${preview.book.title}`,
            `ISBN: ${preview.book.isbn || 'N/A'}`,
            '',
            `Total copies: ${preview.inventory.total}`,
            `Requested/targeted: ${preview.requested_count}`,
            `Matched to this book: ${preview.matched_count}`,
            `Eligible: ${preview.eligible_count}`,
            `Borrowed in target: ${preview.borrowed_count}`,
            `History/other protected in target: ${preview.other_protected_count}`,
            `Copies remaining after deletion: ${preview.inventory.total - preview.eligible_count}`,
        ];
        const accessions = preview.eligible_accession_numbers || [];
        if (accessions.length) {
            const visible = accessions.slice(0, 6);
            lines.push('', `Eligible accession numbers: ${visible.join(', ')}`);
            if (accessions.length > visible.length) lines.push(`+ ${accessions.length - visible.length} more`);
        }
        return lines.join('\n');
    };

    applyBulkAction.addEventListener('click', async () => {
        const action = bulkAction.value;
        if (!action || bulkBusy) return;
        if (action === 'delete_selected' && selectedCopyIds.size === 0) {
            notify('warning', 'Nothing selected', 'Select at least one eligible physical copy.');
            return;
        }

        const payload = { action, copy_ids: Array.from(selectedCopyIds) };
        const originalLabel = applyBulkAction.textContent;
        let deletionModalOpen = false;
        bulkBusy = true;
        applyBulkAction.textContent = 'Checking...';
        syncSelection();

        try {
            const previewResponse = await fetch(root.dataset.bulkPreviewUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const previewPayload = await readResponse(previewResponse);
            const preview = previewPayload.data;

            if (!preview.eligible_count) {
                notify('warning', 'No eligible copies', 'No copies can be deleted. Borrowed and historical copies remain protected.');
                return;
            }

            const confirmed = await feedback.confirm({
                title: `${actionLabel(action)}?`,
                message: `${preview.eligible_count} eligible ${preview.eligible_count === 1 ? 'copy' : 'copies'} will be deleted. Borrowed and historical copies will remain protected. This action cannot be undone.`,
                detail: previewDetail(preview),
                confirmText: `Delete ${preview.eligible_count} ${preview.eligible_count === 1 ? 'Copy' : 'Copies'}`,
                cancelText: 'Cancel',
                variant: 'danger',
                buttonVariant: 'danger',
            });
            if (!confirmed) return;

            await new Promise(resolve => window.setTimeout(resolve, 0));
            feedback.openConfirm({
                title: 'Deleting physical copies',
                message: `Deleting ${preview.eligible_count} eligible ${preview.eligible_count === 1 ? 'copy' : 'copies'} of ${preview.book.title}.`,
                detail: 'The server is rechecking every copy. Borrowed and historical copies will remain protected.',
                confirmText: 'Deleting...',
                variant: 'danger',
                buttonVariant: 'danger',
            });
            feedback.setConfirmBusy(true, 'Deleting...');
            feedback.getConfirmCancelButtons().forEach(button => { button.disabled = true; });
            deletionModalOpen = true;
            applyBulkAction.textContent = 'Deleting...';
            const deleteResponse = await fetch(root.dataset.bulkDeleteUrl, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const resultPayload = await readResponse(deleteResponse);
            const result = resultPayload.data;
            const skippedDetail = (result.skipped || []).slice(0, 3)
                .map(item => `${item.accession_number}: ${item.reason}`).join(' · ');
            clearSelection();
            await loadCopies(currentPage);
            notify(
                result.deleted_count > 0 ? 'success' : 'warning',
                'Bulk deletion completed',
                `Deleted: ${result.deleted_count}. Skipped: ${result.skipped_count}.`,
                5200,
                skippedDetail
            );
        } catch (error) {
            notify('error', 'Bulk deletion failed', error.message || 'Unable to delete the selected physical copies.');
        } finally {
            if (deletionModalOpen) {
                feedback.getConfirmCancelButtons().forEach(button => { button.disabled = false; });
                feedback.resetConfirm();
                feedback.closeConfirm();
            }
            bulkBusy = false;
            applyBulkAction.textContent = originalLabel;
            syncSelection();
        }
    });

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
