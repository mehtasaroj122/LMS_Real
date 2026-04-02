@if (($summary['active_ip_locks'] ?? 0) > 0)
    <form
        action="{{ route('admin.account-locks.unlock-all') }}"
        method="POST"
        class="lock-bulk-form js-lock-mutation-form"
        data-confirm-title="Unlock all active lockouts"
        data-confirm-message="Clear every active account lock currently visible on this dashboard?"
        data-confirm-detail="Use this only for broad false positives, support incidents, or controlled recovery events."
        data-confirm-variant="danger"
    >
        @csrf
        <input type="hidden" name="confirm" value="1">

        <div class="lock-note-card">
            <i class="fas fa-bolt"></i>
            <div>
                <strong>{{ number_format($summary['active_ip_locks']) }} active lock point(s)</strong>
                <p>This action clears all live lock timers in one step.</p>
            </div>
        </div>

        <button type="submit" class="btn btn-danger lock-submit-btn">
            <i class="fas fa-bolt"></i>
            <span>Unlock All Active Lockouts</span>
        </button>
    </form>
@else
    <div class="lock-note-card">
        <i class="fas fa-check-circle"></i>
        <div>
            <strong>No bulk action needed</strong>
            <p>There are no active lock timers to clear right now.</p>
        </div>
    </div>
@endif
