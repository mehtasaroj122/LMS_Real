@php
    $chartWidth = 700;
    $chartHeight = 280;
    $paddingLeft = 64;
    $paddingRight = 18;
    $paddingTop = 18;
    $paddingBottom = 36;
    $innerWidth = $chartWidth - $paddingLeft - $paddingRight;
    $innerHeight = $chartHeight - $paddingTop - $paddingBottom;
    $pointCount = max(count($fineTrendLabels), 2);
    $stepX = $pointCount > 1 ? $innerWidth / ($pointCount - 1) : $innerWidth;

    $buildFineSeries = function (array $series) use ($paddingLeft, $paddingTop, $paddingBottom, $innerHeight, $stepX, $chartHeight, $fineTrendMax) {
        $points = [];
        foreach ($series as $index => $value) {
            $x = $paddingLeft + ($stepX * $index);
            $ratio = $fineTrendMax > 0 ? ($value / $fineTrendMax) : 0;
            $y = $paddingTop + ($innerHeight - ($ratio * $innerHeight));
            $points[] = [
                'x' => round($x, 2),
                'y' => round($y, 2),
                'value' => $value,
            ];
        }

        $linePath = '';
        foreach ($points as $index => $point) {
            $linePath .= ($index === 0 ? 'M' : ' L') . $point['x'] . ' ' . $point['y'];
        }

        $areaPath = $linePath;
        if (! empty($points)) {
            $areaPath .= ' L' . end($points)['x'] . ' ' . ($chartHeight - $paddingBottom);
            $areaPath .= ' L' . $points[0]['x'] . ' ' . ($chartHeight - $paddingBottom) . ' Z';
        }

        return [$points, $linePath, $areaPath];
    };

    [$generatedPoints, $generatedLinePath, $generatedAreaPath] = $buildFineSeries($fineTrendPending);
    [$collectedPoints, $collectedLinePath, $collectedAreaPath] = $buildFineSeries($fineTrendCollected);
    [$waivedPoints, $waivedLinePath, $waivedAreaPath] = $buildFineSeries($fineTrendWaived);
    $yStep = max(50, (int) ceil($fineTrendMax / 5));
@endphp

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

    <div class="fine-trend-legend">
        <div class="fine-trend-legend-item text-primary">
            <span class="fine-trend-legend-dot" style="background-color: #f87171;"></span>
            <span>Pending Fines</span>
        </div>
        <div class="fine-trend-legend-item text-primary">
            <span class="fine-trend-legend-dot" style="background-color: #10b981;"></span>
            <span>Collected Fines</span>
        </div>
        <div class="fine-trend-legend-item text-primary">
            <span class="fine-trend-legend-dot" style="background-color: #f59e0b;"></span>
            <span>Waived Fines</span>
        </div>
    </div>

    <div class="fine-trend-shell" data-fine-trend>
        <div class="fine-trend-wrap">
            <svg class="fine-trend-chart" viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" aria-label="Fine transaction trend chart">
            @for($i = 0; $i <= 5; $i++)
                @php
                    $y = $paddingTop + (($innerHeight / 5) * $i);
                    $labelValue = $fineTrendMax - ($yStep * $i);
                    if ($i === 5) {
                        $labelValue = 0;
                    }
                @endphp
                <line x1="{{ $paddingLeft }}" y1="{{ $y }}" x2="{{ $chartWidth - $paddingRight }}" y2="{{ $y }}" class="fine-grid-line" />
                <text x="{{ $paddingLeft - 10 }}" y="{{ $y + 4 }}" text-anchor="end" class="fine-axis-label">
                    ₹{{ max($labelValue, 0) }}
                </text>
            @endfor

            @foreach($fineTrendAxisLabels as $index => $label)
                @php
                    $x = $paddingLeft + ($stepX * $index);
                @endphp
                <line x1="{{ $x }}" y1="{{ $paddingTop }}" x2="{{ $x }}" y2="{{ $chartHeight - $paddingBottom }}" class="fine-grid-line" />
                <text x="{{ $x }}" y="{{ $chartHeight - 12 }}" text-anchor="middle" class="fine-axis-label">
                    {{ $label }}
                </text>
            @endforeach

            @if($generatedAreaPath)
                <path d="{{ $generatedAreaPath }}" class="fine-generated-area"></path>
            @endif

            @if($collectedAreaPath)
                <path d="{{ $collectedAreaPath }}" class="fine-collected-area"></path>
            @endif

            @if($waivedAreaPath)
                <path d="{{ $waivedAreaPath }}" class="fine-waived-area"></path>
            @endif

            @if($generatedLinePath)
                <path d="{{ $generatedLinePath }}" class="fine-generated-line"></path>
            @endif

            @if($collectedLinePath)
                <path d="{{ $collectedLinePath }}" class="fine-collected-line"></path>
            @endif

            @if($waivedLinePath)
                <path d="{{ $waivedLinePath }}" class="fine-waived-line"></path>
            @endif

            @foreach($generatedPoints as $index => $point)
                <circle
                    cx="{{ $point['x'] }}"
                    cy="{{ $point['y'] }}"
                    r="5.5"
                    class="fine-generated-point"
                    data-trend-series="Pending"
                    data-trend-month="{{ $fineTrendLabels[$index] ?? '' }}"
                    data-trend-amount="{{ number_format((float) $point['value'], 2) }}"
                    data-trend-color="#f87171"
                ></circle>
            @endforeach

            @foreach($collectedPoints as $index => $point)
                <circle
                    cx="{{ $point['x'] }}"
                    cy="{{ $point['y'] }}"
                    r="5.5"
                    class="fine-collected-point"
                    data-trend-series="Collected"
                    data-trend-month="{{ $fineTrendLabels[$index] ?? '' }}"
                    data-trend-amount="{{ number_format((float) $point['value'], 2) }}"
                    data-trend-color="#10b981"
                ></circle>
            @endforeach

            @foreach($waivedPoints as $index => $point)
                <circle
                    cx="{{ $point['x'] }}"
                    cy="{{ $point['y'] }}"
                    r="5.5"
                    class="fine-waived-point"
                    data-trend-series="Waived"
                    data-trend-month="{{ $fineTrendLabels[$index] ?? '' }}"
                    data-trend-amount="{{ number_format((float) $point['value'], 2) }}"
                    data-trend-color="#f59e0b"
                ></circle>
            @endforeach
            </svg>
        </div>

        <div class="fine-trend-tooltip" data-fine-trend-tooltip>
            <div class="fine-trend-tooltip-label">
                <span class="fine-trend-tooltip-dot" data-trend-tooltip-dot></span>
                <span data-trend-tooltip-series></span>
            </div>
            <div class="fine-trend-tooltip-meta">
                <span data-trend-tooltip-month></span>
                <span> • </span>
                <span data-trend-tooltip-amount></span>
            </div>
        </div>
    </div>
</div>
