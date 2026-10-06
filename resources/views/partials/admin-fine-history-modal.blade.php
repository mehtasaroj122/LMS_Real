{{-- Read-only panel; existing fine actions stay in Fine & Payment Management. --}}
<div class="fine-modal-overlay" id="historyOverlay" aria-hidden="true">
    <div class="fine-modal wide" role="dialog" aria-modal="true" aria-labelledby="fineHistoryTitle"
        aria-describedby="fineHistorySubtitle" tabindex="-1">
        <header class="fine-modal-header">
            <div>
                <h2 id="fineHistoryTitle">Fine &amp; Payment History</h2>
                <p id="fineHistorySubtitle">Fine calculation, adjustments, payments and audit history</p>
            </div>
            <button type="button" class="fine-modal-close" id="fineHistoryClose" aria-label="Close"
                onclick="closeFineModal('history')">&times;</button>
        </header>
        <div class="fine-modal-body fine-history-modal-body" tabindex="0" aria-label="Fine history content">
            <div id="fineHistoryLoading" class="fine-history-state" role="status">
                <i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Loading fine history…
            </div>
            <div id="fineHistoryError" class="fine-history-state" hidden>
                <p role="alert">Unable to load fine history.<br>Please try again.</p>
                <button type="button" class="fine-modal-btn fine-modal-btn-cancel" onclick="retryFineHistory()">Try again</button>
            </div>
            <div id="fineHistoryContent" hidden>
                <section class="fine-history-section" aria-labelledby="fineSummaryTitle">
                    <h3 class="fine-history-section-title" id="fineSummaryTitle">Fine Summary</h3>
                    <p class="fine-history-book-title" id="historyBookTitle"></p>
                    <div class="fine-history-book-meta">
                        <span id="historyBookIsbn"></span>
                        <span id="historyFineId"></span>
                        <span id="historyAccession" hidden></span>
                        <span id="historyTransaction" hidden></span>
                    </div>
                    <dl class="fine-summary-grid">
                        <div><dt class="fine-summary-label">CURRENT FINE</dt><dd class="fine-history-amount" id="historyAmount"></dd></div>
                        <div><dt class="fine-summary-label">Original Fine</dt><dd class="fine-summary-value" id="historyOriginalAmount"></dd></div>
                        <div><dt class="fine-summary-label">Status</dt><dd class="fine-summary-value" id="historyStatus"></dd></div>
                        <div><dt class="fine-summary-label">Days Late</dt><dd class="fine-summary-value" id="historyDaysLate"></dd></div>
                    </dl>
                </section>
                <section class="fine-history-section" id="historyCalculationSection" aria-labelledby="fineCalculationTitle" hidden>
                    <h3 class="fine-history-section-title" id="fineCalculationTitle">Fine Calculation</h3>
                    <dl class="fine-calculation-table">
                        <div class="fine-calculation-row"><dt>Per-day rate</dt><dd id="historyCalcBaseRate"></dd></div>
                        <div class="fine-calculation-row"><dt>Days overdue</dt><dd id="historyCalcDaysLate"></dd></div>
                        <div class="fine-calculation-row"><dt>Calculated subtotal</dt><dd id="historyCalcSubtotal"></dd></div>
                        <div class="fine-calculation-row" id="historyCalcAdjustmentsRow" hidden><dt>Manual adjustment</dt><dd id="historyCalcAdjustments"></dd></div>
                        <div class="fine-calculation-row total"><dt>Current fine</dt><dd id="historyCalcFinalAmount"></dd></div>
                    </dl>
                </section>
                <section class="fine-history-section" id="historyPaymentSection" aria-labelledby="finePaymentTitle" hidden>
                    <h3 class="fine-history-section-title" id="finePaymentTitle">Payment Details</h3>
                    <dl class="fine-history-details-grid" id="historyPaymentDetails"></dl>
                </section>
                <section class="fine-history-section" id="historyWaiverSection" aria-labelledby="fineWaiverTitle" hidden>
                    <h3 class="fine-history-section-title" id="fineWaiverTitle">Waiver Details</h3>
                    <dl class="fine-history-details-grid" id="historyWaiverDetails"></dl>
                    <p class="fine-history-remarks"><strong>Reason</strong><span id="historyWaiverReason"></span></p>
                </section>
                <section class="fine-history-section" aria-labelledby="fineHistoryHeading">
                    <div class="fine-history-heading">
                        <h3 class="fine-history-section-title" id="fineHistoryHeading">Fine History</h3>
                        <span>Newest first</span>
                    </div>
                    <ol class="fine-history-list" id="fineHistoryList"></ol>
                </section>
            </div>
        </div>
        <footer class="fine-modal-actions fine-history-footer">
            <button type="button" class="fine-modal-btn fine-modal-btn-cancel" onclick="closeFineModal('history')">Close</button>
        </footer>
    </div>
</div>
