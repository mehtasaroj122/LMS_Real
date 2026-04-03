<div class="shadow-sm card fine-graph-card">
    <div class="fine-transaction-header">
        <div class="fine-transaction-heading">
            <i data-lucide="trending-up" class="fine-transaction-icon"></i>
            <div>
                <h4 class="fine-transaction-title text-primary">Fine Transactions</h4>
                <p class="fine-transaction-subtitle">{{ $fineTrendSubtitle }}</p>
            </div>
        </div>

        <form
            method="GET"
            action="{{ route('admin.dashboard') }}"
            class="fine-period-form"
            autocomplete="off"
            data-fine-period-form
            data-fine-trend-url="{{ route('admin.dashboard.fine-trend') }}"
        >
            <label for="fine-period" class="fine-period-label">Period</label>
            <select
                id="fine-period"
                name="fine_period"
                class="fine-period-select"
                data-fine-period-select
                data-server-period="{{ $selectedFineTrendPeriod }}"
            >
                @foreach($fineTrendPeriodOptions as $value => $label)
                    <option value="{{ $value }}" @selected($selectedFineTrendPeriod === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <noscript>
                <button type="submit" class="fine-period-label">Apply</button>
            </noscript>
        </form>
    </div>

    <div class="fine-trend-shell">
        <div class="fine-trend-wrap">
            <div class="fine-trend-canvas-shell">
                <canvas
                    class="fine-trend-chart"
                    aria-label="Fine transaction trend chart"
                    data-fine-trend-chart
                    data-fine-trend-labels='@json($fineTrendLabels)'
                    data-fine-trend-axis-labels='@json($fineTrendAxisLabels)'
                    data-fine-trend-pending='@json($fineTrendPending)'
                    data-fine-trend-collected='@json($fineTrendCollected)'
                    data-fine-trend-waived='@json($fineTrendWaived)'
                ></canvas>
            </div>
        </div>
    </div>
</div>
