/* Read-only presentation for the existing admin fine-history endpoint. */
(() => {
    let request = null;
    let selectedFine = null;
    let trigger = null;
    const element = id => document.getElementById(id);
    const hasValue = value => value !== null && value !== undefined && value !== '';

    function resetContent() {
        element('fineHistoryContent').hidden = true;
        element('fineHistoryError').hidden = true;
        element('fineHistoryLoading').hidden = false;
        ['historyCalculationSection', 'historyPaymentSection', 'historyWaiverSection',
            'historyAccession', 'historyTransaction', 'historyCalcAdjustmentsRow'].forEach(id => element(id).hidden = true);
        element('fineHistoryContent').querySelectorAll('[id]').forEach(node => {
            if (/^history/.test(node.id) && !node.matches('section, dl, div.fine-calculation-row')) node.textContent = '';
        });
        element('historyStatus').replaceChildren();
        element('historyPaymentDetails').replaceChildren();
        element('historyWaiverDetails').replaceChildren();
        element('fineHistoryList').replaceChildren();
        element('historyOverlay').querySelector('.fine-history-modal-body').scrollTop = 0;
    }

    function setOptionalText(id, label, value) {
        element(id).hidden = !hasValue(value);
        element(id).textContent = hasValue(value) ? `${label}${value}` : '';
    }

    function detailRows(rows) {
        return rows.filter(([, value]) => hasValue(value)).map(([label, value]) =>
            `<div><dt>${escapeHtml(label)}</dt><dd>${escapeHtml(value)}</dd></div>`).join('');
    }

    function populate(data) {
        const fine = data.fineDetails || {};
        const calculation = data.calculation;
        const history = Array.isArray(data.history) ? data.history : [];
        // All amounts and differences come from the endpoint, never from a new UI formula.
        element('historyAmount').textContent = hasValue(fine.currentAmount) ? formatCurrency(fine.currentAmount) : 'Not recorded';
        element('historyOriginalAmount').textContent = hasValue(fine.originalAmount) ? formatCurrency(fine.originalAmount) : 'Not recorded';
        element('historyStatus').innerHTML = renderHistoryStatusBadge(fine.status);
        element('historyDaysLate').textContent = hasValue(fine.daysLate)
            ? `${fine.daysLate} day${Number(fine.daysLate) === 1 ? '' : 's'}` : 'Not recorded';
        element('historyBookTitle').textContent = fine.bookTitle || 'Unknown Book';
        element('historyBookIsbn').textContent = `ISBN: ${fine.isbn || 'N/A'}`;
        element('historyFineId').textContent = `Fine #${fine.id ?? selectedFine}`;
        setOptionalText('historyAccession', 'Accession: ', fine.accessionNumber);
        setOptionalText('historyTransaction', 'Transaction: ', fine.transactionId);

        if (calculation) {
            element('historyCalculationSection').hidden = false;
            const fields = { historyCalcBaseRate: 'baseRate', historyCalcSubtotal: 'subtotal', historyCalcFinalAmount: 'finalAmount' };
            Object.entries(fields).forEach(([id, key]) => element(id).textContent =
                hasValue(calculation[key]) ? formatCurrency(calculation[key]) : 'Not recorded');
            element('historyCalcDaysLate').textContent = calculation.daysLate ?? 'Not recorded';
            element('historyCalcAdjustmentsRow').hidden = !hasValue(calculation.adjustments) || Number(calculation.adjustments) === 0;
            element('historyCalcAdjustments').textContent = formatSignedCurrency(calculation.adjustments);
        }

        if (fine.status === 'paid' && data.paymentDetails) {
            const payment = data.paymentDetails;
            element('historyPaymentSection').hidden = false;
            element('historyPaymentDetails').innerHTML = detailRows([
                ['Paid Amount', hasValue(payment.amount) ? formatCurrency(payment.amount) : null],
                ['Outstanding', hasValue(payment.outstanding) ? formatCurrency(payment.outstanding) : null],
                ['Paid On', payment.date], ['Payment Method', hasValue(payment.method) ? formatDisplayLabel(payment.method) : null],
                ['Recorded By', payment.recordedBy],
            ]);
        }
        if (fine.status === 'waived' && data.waiverDetails) {
            const waiver = data.waiverDetails;
            element('historyWaiverSection').hidden = false;
            element('historyWaiverDetails').innerHTML = detailRows([
                ['Waived Amount', hasValue(waiver.amount) ? formatCurrency(waiver.amount) : null],
                ['Date', waiver.date], ['Waived By', waiver.waivedBy],
            ]);
            element('historyWaiverReason').textContent = waiver.reason || 'No reason recorded.';
        }
        // Preserve the endpoint's existing newest-first audit ordering.
        element('fineHistoryList').innerHTML = history.length ? history.map(renderFineHistoryItem).join('') :
            '<li class="fine-history-empty"><i class="fas fa-history" aria-hidden="true"></i><div><strong>No fine history yet.</strong>This fine has not been adjusted, paid, or waived.</div></li>';
    }

    async function loadHistory(fineId) {
        request?.abort();
        const activeRequest = new AbortController();
        request = activeRequest;
        resetContent();
        try {
            const response = await fetch(`/admin/fines/${encodeURIComponent(fineId)}/history`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                credentials: 'same-origin', signal: activeRequest.signal,
            });
            if (!response.ok) throw new Error('Unable to load fine history');
            const data = await response.json();
            if (request !== activeRequest || activeRequest.signal.aborted) return;
            if (!data.success) throw new Error('Unable to load fine history');
            populate(data);
            element('fineHistoryLoading').hidden = true;
            element('fineHistoryContent').hidden = false;
        } catch (error) {
            if (request !== activeRequest || activeRequest.signal.aborted) return;
            element('fineHistoryLoading').hidden = true;
            element('fineHistoryError').hidden = false;
        }
    }

    window.viewFineHistory = function (fineId, fine, source = document.activeElement) {
        const overlay = element('historyOverlay');
        if (!overlay.classList.contains('show')) trigger = source;
        selectedFine = fineId;
        overlay.classList.add('show');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('fine-history-open');
        element('fineHistoryClose').focus({ preventScroll: true });
        loadHistory(fineId);
    };
    window.retryFineHistory = function () {
        if (selectedFine === null) return;
        // The retry control will be hidden during loading; keep focus on a visible control.
        element('fineHistoryClose').focus({ preventScroll: true });
        loadHistory(selectedFine);
    };
    window.closeFineHistoryPanel = function () {
        request?.abort();
        request = null;
        selectedFine = null;
        element('historyOverlay').classList.remove('show');
        element('historyOverlay').setAttribute('aria-hidden', 'true');
        document.body.classList.remove('fine-history-open');
        if (trigger?.isConnected) trigger.focus({ preventScroll: true });
        trigger = null;
    };

    window.renderFineHistoryItem = function (item) {
        const actions = {
            created: { title: 'Fine Created', icon: 'fa-plus' },
            adjusted: { title: 'Fine Adjusted', icon: 'fa-sliders-h' },
            paid: { title: 'Fine Marked Paid', icon: 'fa-check' },
            waived: { title: 'Fine Waived', icon: 'fa-ban' },
        };
        const type = Object.hasOwn(actions, item.actionType) ? item.actionType : 'other';
        const action = actions[type] || { title: item.action || 'Fine Updated', icon: 'fa-history' };
        const role = hasValue(item.userRole) ? formatDisplayLabel(item.userRole) : 'Role not recorded';
        let amounts = '';
        if (type === 'adjusted' && hasValue(item.oldAmount) && hasValue(item.newAmount)) {
            const delta = hasValue(item.amountChange) ? `<span class="fine-history-delta ${Number(item.amountChange) > 0 ? 'increase' : Number(item.amountChange) < 0 ? 'decrease' : ''}">${escapeHtml(formatSignedCurrency(item.amountChange))}</span>` : '';
            amounts = `<span>${formatCurrency(item.oldAmount)} → ${formatCurrency(item.newAmount)}</span>${delta}`;
        } else if (hasValue(item.newAmount)) {
            const label = { created: 'Initial fine', paid: 'Paid', waived: 'Waived' }[type] || 'Amount';
            amounts = `<span>${label} ${formatCurrency(item.newAmount)}</span>`;
        }
        const reason = item.remarks || 'No reason recorded.';
        const note = item.description && item.description !== item.action
            ? `<details class="fine-history-audit"><summary>Audit note</summary><p>${escapeHtml(item.description)}</p></details>` : '';
        return `<li class="fine-history-item">
            <span class="fine-history-dot ${type}" aria-hidden="true"><i class="fas ${action.icon}"></i></span>
            <div class="fine-history-entry">
                <div class="fine-history-top"><h4 class="fine-history-action">${escapeHtml(action.title)}</h4><span class="fine-history-date">${escapeHtml(item.date || 'Date not recorded')}</span></div>
                ${amounts ? `<div class="fine-history-amount-change">${amounts}</div>` : ''}
                <p class="fine-history-meta">${escapeHtml(item.user || 'System')} · ${escapeHtml(role)}${hasValue(item.paymentMethod) ? ` · ${escapeHtml(formatDisplayLabel(item.paymentMethod))}` : ''}</p>
                ${type === 'adjusted' || type === 'waived' || hasValue(item.remarks) ? `<p class="fine-history-remarks"><strong>Reason</strong>${escapeHtml(reason)}</p>` : ''}
                ${note}
            </div>
        </li>`;
    };
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && element('historyOverlay')?.classList.contains('show')) {
            event.preventDefault();
            closeFineModal('history');
        }
    });
})();
