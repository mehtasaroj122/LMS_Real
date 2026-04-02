@php
    $formatCurrency = fn ($value) => '₹' . number_format((float) $value, 2);
@endphp

@switch($reportType)
    @case('inventory')
        @php($inventoryReport = $reportData)
        <div class="section-library">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon books">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20" /></svg>
                        </div>
                        <div class="stat-value">{{ number_format($inventoryReport['stats']['total_books']) }}</div>
                    </div>
                    <div class="stat-label">Total Books</div>
                    <div class="stat-subtitle">{{ number_format($inventoryReport['stats']['total_copies']) }} total copies</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon available">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div class="stat-value">{{ number_format($inventoryReport['stats']['available_copies']) }}</div>
                    </div>
                    <div class="stat-label">Available</div>
                    <div class="stat-subtitle">Copies ready for issue</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon issued">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                        </div>
                        <div class="stat-value">{{ number_format($inventoryReport['stats']['current_issued']) }}</div>
                    </div>
                    <div class="stat-label">Currently Issued</div>
                    <div class="stat-subtitle">Books currently with students</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon additions">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                        </div>
                        <div class="stat-value">{{ number_format($inventoryReport['stats']['recent_additions']) }}</div>
                    </div>
                    <div class="stat-label">Recent Additions</div>
                    <div class="stat-subtitle">{{ $filters['periodLabel'] }}</div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <div class="chart-heading">
                            <h3 class="chart-title">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                                Books by Category
                            </h3>
                            <p class="chart-description">Top categories are ranked for faster scanning, with remaining titles grouped into <strong>Others</strong>.</p>
                            <div id="categoryChartInsight" class="chart-note">
                                <span class="chart-note-pill">Top 8 + Others</span>
                                <span>Readable inventory ranking across the current catalog.</span>
                            </div>
                        </div>

                        <div class="chart-actions">
                            <label class="chart-action-group" for="categoryChartLimit">
                                <span class="chart-action-label">Visible Items</span>
                                <select id="categoryChartLimit" class="chart-action-select">
                                    <option value="6">Top 6</option>
                                    <option value="8" selected>Top 8</option>
                                    <option value="10">Top 10</option>
                                </select>
                            </label>

                            <div class="chart-action-group">
                                <span class="chart-action-label">View</span>
                                <div class="chart-view-switch" role="group" aria-label="Category chart view">
                                    <button type="button" class="chart-view-btn is-active" data-category-view="bar" aria-pressed="true">Bar</button>
                                    <button type="button" class="chart-view-btn" data-category-view="doughnut" aria-pressed="false">Donut</button>
                                </div>
                            </div>

                            <div class="chart-action-group">
                                <span class="chart-action-label">Export</span>
                                <button type="button" class="chart-action-btn" data-export-chart="categoryChart" data-export-filename="books-by-category">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                    PNG
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="chart-container category-chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-card-header">
                        <div class="chart-heading">
                            <h3 class="chart-title">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="2" x2="12" y2="22"></line><polyline points="4 7 12 2 20 7"></polyline><polyline points="4 17 12 22 20 17"></polyline></svg>
                                Books by Condition
                            </h3>
                            <p class="chart-description">Condition breakdown for books currently in the catalog, with clearer status colors and value labels.</p>
                            <div class="chart-note">
                                <span class="chart-note-pill">Catalog Health</span>
                                <span>New, Good, and Damaged statuses stay aligned across the chart and table.</span>
                            </div>
                        </div>

                        <div class="chart-actions">
                            <div class="chart-action-group">
                                <span class="chart-action-label">Status Guide</span>
                                <div class="condition-legend" aria-label="Condition color legend">
                                    <span class="legend-chip"><span class="legend-swatch" style="background:#16a34a;"></span>New</span>
                                    <span class="legend-chip"><span class="legend-swatch" style="background:#2563eb;"></span>Good</span>
                                    <span class="legend-chip"><span class="legend-swatch" style="background:#f97316;"></span>Damaged</span>
                                </div>
                            </div>

                            <div class="chart-action-group">
                                <span class="chart-action-label">Export</span>
                                <button type="button" class="chart-action-btn" data-export-chart="conditionChart" data-export-filename="books-by-condition">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                    PNG
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="conditionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-library tables-grid">
            <div class="table-card">
                <h3 class="table-title">Books by Category</h3>
                <div class="table-scroll-area">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th class="table-count">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inventoryReport['category_rows'] as $category)
                                <tr>
                                    <td>{{ $category['name'] }}</td>
                                    <td class="table-count">{{ number_format($category['count']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2">No category data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-card">
                <h3 class="table-title">Books by Condition</h3>
                <div class="table-scroll-area">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Condition</th>
                                <th class="table-count">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inventoryReport['condition_rows'] as $condition)
                                <tr>
                                    <td><span class="badge {{ $condition['badge_class'] }}">{{ $condition['label'] }}</span></td>
                                    <td class="table-count">{{ number_format($condition['count']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2">No condition data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @break

    @case('transactions')
        @php($transactionsReport = $reportData)
        <div class="section-transaction active">
            <div class="transaction-header">
                <h2>Transaction Analytics</h2>
                <p>Circulation activity for {{ $filters['periodLabel'] }}.</p>
            </div>

            <div class="transaction-stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon issued">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        </div>
                        <div class="stat-value">{{ number_format($transactionsReport['stats']['books_issued']) }}</div>
                    </div>
                    <div class="stat-label">Books Issued</div>
                    <div class="stat-subtitle">{{ $filters['periodLabel'] }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon available">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div class="stat-value">{{ number_format($transactionsReport['stats']['books_returned']) }}</div>
                    </div>
                    <div class="stat-label">Books Returned</div>
                    <div class="stat-subtitle">Completed within the range</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon issued">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20" /></svg>
                        </div>
                        <div class="stat-value">{{ number_format($transactionsReport['stats']['currently_issued']) }}</div>
                    </div>
                    <div class="stat-label">Currently Issued</div>
                    <div class="stat-subtitle">Still out on loan</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon additions">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <div class="stat-value">{{ number_format($transactionsReport['stats']['average_issue_duration'], 1) }}</div>
                    </div>
                    <div class="stat-label">Avg. Issue Duration</div>
                    <div class="stat-subtitle">Days before return</div>
                </div>
            </div>

            <div class="trends-section">
                <div class="charts-grid">
                    <div class="chart-card">
                        <h3 class="chart-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="12 3 20 7.5 20 16.5 12 21 4 16.5 4 7.5 12 3"></polyline><polyline points="12 12 20 7.5"></polyline><polyline points="12 12 12 21"></polyline><polyline points="12 12 4 7.5"></polyline></svg>
                            Monthly Book Circulation
                        </h3>
                        <p class="chart-description">Issues and returns across the last 12 months.</p>
                        <div class="chart-container">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <h3 class="chart-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path><path d="M3 21v-5h5"></path></svg>
                            {{ $transactionsReport['activity_chart_title'] }}
                        </h3>
                        <p class="chart-description">{{ $transactionsReport['activity_chart_description'] }}</p>
                        <div class="chart-container">
                            <canvas id="dailyChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="chart-card">
                        <h3 class="chart-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                            Most Borrowed Books
                        </h3>
                        <p class="chart-description">Top titles issued within {{ $filters['periodLabel'] }}.</p>
                        <div class="chart-container">
                            <canvas id="borrowedChart"></canvas>
                        </div>
                    </div>

                    <div class="table-card">
                        <h3 class="table-title">Most Issued Books</h3>
                        <p class="table-subtitle">Top titles issued within {{ $filters['periodLabel'] }}</p>
                        <div class="table-scroll-area">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Book Title</th>
                                        <th>Author</th>
                                        <th class="table-count">Times Issued</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($transactionsReport['most_issued_books'] as $book)
                                        <tr>
                                            <td>{{ $book['title'] }}</td>
                                            <td>{{ $book['author'] }}</td>
                                            <td class="table-count">{{ number_format($book['count']) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3">No transaction data available for the selected period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @break

    @case('fines')
        @php($finesReport = $reportData)
        <div class="section-fines active">
            <div class="transaction-header">
                <h2>Fines & Revenue Overview</h2>
                <p>Collection performance for {{ $filters['periodLabel'] }}.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon issued">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="M7 15h0M2 9.5h20"></path></svg>
                        </div>
                        <div class="stat-value">{{ $formatCurrency($finesReport['stats']['generated']) }}</div>
                    </div>
                    <div class="stat-label">Total Generated</div>
                    <div class="stat-subtitle">{{ $filters['periodLabel'] }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon available">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        </div>
                        <div class="stat-value">{{ $formatCurrency($finesReport['stats']['collected']) }}</div>
                    </div>
                    <div class="stat-label">Total Collected</div>
                    <div class="stat-subtitle">Paid in the selected range</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon overdue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <div class="stat-value">{{ $formatCurrency($finesReport['stats']['pending']) }}</div>
                    </div>
                    <div class="stat-label">Total Pending</div>
                    <div class="stat-subtitle">Still unpaid from the range</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon additions">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>
                        </div>
                        <div class="stat-value">{{ $formatCurrency($finesReport['stats']['waived']) }}</div>
                    </div>
                    <div class="stat-label">Total Waived</div>
                    <div class="stat-subtitle">Waived during the range</div>
                </div>
            </div>

            <div class="trends-section">
                <div class="charts-grid">
                    <div class="chart-card">
                        <h3 class="chart-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22m4.5-18.5H3.5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h17a2 2 0 0 0 2-2v-12a2 2 0 0 0-2-2z"></path></svg>
                            Fine Collection Overview
                        </h3>
                        <p class="chart-description">Generated, collected, and pending fines over the last 12 months.</p>
                        <div class="chart-container">
                            <canvas id="fineCollectionChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <h3 class="chart-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 10"></polyline><path d="M7 4a1 1 0 0 0 0 2h10a1 1 0 1 0 0-2H7z"></path></svg>
                            Collection Efficiency Trend
                        </h3>
                        <p class="chart-description">Generated versus collected fines over the last 12 months.</p>
                        <div class="chart-container">
                            <canvas id="efficiencyChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="tables-grid">
                    <div class="table-card">
                        <h3 class="table-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h18v18H3z"></path><path d="M3 9h18"></path><path d="M9 21V9"></path></svg>
                            Collection Health
                        </h3>
                        <p class="table-subtitle">Quick operational indicators for {{ $filters['periodLabel'] }}.</p>
                        <div class="table-scroll-area">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Metric</th>
                                        <th class="table-count">Value</th>
                                        <th>Insight</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($finesReport['collection_health'] as $row)
                                        <tr>
                                            <td>{{ $row['metric'] }}</td>
                                            <td class="table-count"><span class="badge {{ $row['badge_class'] }}">{{ $row['value_display'] }}</span></td>
                                            <td>{{ $row['insight'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3">No collection health data available.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="table-card">
                        <h3 class="table-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                            Top Defaulters
                        </h3>
                        <p class="table-subtitle">Current students with the highest pending fine balances.</p>
                        <div class="table-scroll-area">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Student ID</th>
                                        <th class="table-count">Open Cases</th>
                                        <th class="table-count">Pending Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($finesReport['top_defaulters'] as $student)
                                        <tr>
                                            <td>{{ $student['student_name'] }}</td>
                                            <td>{{ $student['student_id'] }}</td>
                                            <td class="table-count">{{ number_format($student['pending_cases']) }}</td>
                                            <td class="table-count"><span style="color: #ef4444; font-weight: 600;">{{ $formatCurrency($student['pending_amount']) }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4">No pending defaulters found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @break

    @case('users')
        @php($usersReport = $reportData)
        <div class="section-users active">
            <div class="transaction-header">
                <h2>Users & Activity Overview</h2>
                <p>User counts and activity logs for {{ $filters['periodLabel'] }}.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon books">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <div class="stat-value">{{ number_format($usersReport['stats']['total_users']) }}</div>
                    </div>
                    <div class="stat-label">Total Users</div>
                    <div class="stat-subtitle">All registered accounts</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon available">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg>
                        </div>
                        <div class="stat-value">{{ number_format($usersReport['stats']['active_users']) }}</div>
                    </div>
                    <div class="stat-label">Active Users</div>
                    <div class="stat-subtitle">Active accounts</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon issued">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 18a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2"></path><rect x="3" y="4" width="18" height="18" rx="2"></rect><circle cx="12" cy="10" r="2"></circle><line x1="8" y1="2" x2="8" y2="4"></line><line x1="16" y1="2" x2="16" y2="4"></line></svg>
                        </div>
                        <div class="stat-value">{{ number_format($usersReport['stats']['inactive_users']) }}</div>
                    </div>
                    <div class="stat-label">Inactive Users</div>
                    <div class="stat-subtitle">Inactive accounts</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon additions">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="8" x2="19" y2="14"></line><line x1="22" y1="11" x2="16" y2="11"></line></svg>
                        </div>
                        <div class="stat-value">{{ number_format($usersReport['stats']['new_users']) }}</div>
                    </div>
                    <div class="stat-label">New Users</div>
                    <div class="stat-subtitle">{{ $filters['periodLabel'] }}</div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        Users by Role
                    </h3>
                    <p class="chart-description">Distribution of users by role.</p>
                    <div class="chart-container">
                        <canvas id="usersByRoleChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        User Activity by Role
                    </h3>
                    <p class="chart-description">Activity log volume by role within {{ $filters['periodLabel'] }}.</p>
                    <div class="chart-container">
                        <canvas id="userActivityChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="tables-grid section-spacing-top">
                <div class="table-card">
                    <h3 class="table-title">Users by Role</h3>
                    <div class="table-scroll-area">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Role</th>
                                    <th class="table-count">Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($usersReport['users_by_role'] as $role)
                                    <tr>
                                        <td>{{ $role['role'] }}</td>
                                        <td class="table-count">{{ number_format($role['count']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2">No user data available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="table-card">
                    <h3 class="table-title">Most Active Readers</h3>
                    <p class="table-subtitle">Students with the most issues in {{ $filters['periodLabel'] }}.</p>
                    <div class="table-scroll-area">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th class="table-count">Books Issued</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($usersReport['most_active_readers'] as $reader)
                                    <tr>
                                        <td>{{ $reader['student_name'] }}</td>
                                        <td class="table-count">{{ number_format($reader['issued_count']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2">No reader activity found for the selected period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @break

    @case('overdue')
        @php($overdueReport = $reportData)
        <div class="section-overdue active">
            <div class="transaction-header">
                <h2>Overdue Books Management</h2>
                <p>Current overdue inventory and overdue trend monitoring.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon overdue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <div class="stat-value">{{ number_format($overdueReport['stats']['total_overdue']) }}</div>
                    </div>
                    <div class="stat-label">Total Overdue Books</div>
                    <div class="stat-subtitle">Currently overdue</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon additions">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        </div>
                        <div class="stat-value">{{ number_format($overdueReport['stats']['critical_overdue']) }}</div>
                    </div>
                    <div class="stat-label">30+ Days Overdue</div>
                    <div class="stat-subtitle">Critical overdue</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon books">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        </div>
                        <div class="stat-value">{{ $formatCurrency($overdueReport['stats']['total_fine_amount']) }}</div>
                    </div>
                    <div class="stat-label">Total Fine Amount</div>
                    <div class="stat-subtitle">Across current overdue books</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon available">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                        </div>
                        <div class="stat-value">{{ number_format($overdueReport['stats']['average_days_overdue'], 1) }}</div>
                    </div>
                    <div class="stat-label">Avg Days Overdue</div>
                    <div class="stat-subtitle">Current overdue portfolio</div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                        Overdue Duration
                    </h3>
                    <p class="chart-description">Current overdue books by days late.</p>
                    <div class="chart-container">
                        <canvas id="overdueDistributionChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 17"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                        Overdue Trend
                    </h3>
                    <p class="chart-description">Overdue incidents and current critical cases over the last 12 months.</p>
                    <div class="chart-container">
                        <canvas id="overdueeTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="tables-grid section-spacing-top">
                <div class="table-card">
                    <h3 class="table-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 3h5v5"></path><path d="M8 3H3v5"></path><path d="M3 16v5h5"></path><path d="M21 16v5h-5"></path><path d="M8 8h8v8H8z"></path></svg>
                        Overdue Student Summary
                    </h3>
                    <p class="table-subtitle">Students with the highest current overdue exposure and delay severity.</p>
                    <div class="table-scroll-area">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th class="table-count">Books Overdue</th>
                                    <th class="table-count">Longest Delay</th>
                                    <th class="table-count">Fine Exposure</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($overdueReport['overdue_students'] as $student)
                                    <tr>
                                        <td>{{ $student['student_name'] }}</td>
                                        <td class="table-count">{{ number_format($student['books_overdue']) }}</td>
                                        <td class="table-count"><span style="color: {{ $student['highest_days_overdue'] >= 30 ? '#dc2626' : '#f97316' }}; font-weight: 600;">{{ number_format($student['highest_days_overdue']) }} days</span></td>
                                        <td class="table-count"><span style="color: #ef4444; font-weight: 600;">{{ $formatCurrency($student['fine_exposure']) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">No overdue student summary available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="table-card">
                    <h3 class="table-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                        Overdue Books List
                    </h3>
                    <p class="table-subtitle">All books currently overdue by student.</p>
                    <div class="table-scroll-area">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Book Title</th>
                                    <th class="table-count">Days Overdue</th>
                                    <th class="table-count">Fine Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($overdueReport['overdue_books'] as $book)
                                    <tr>
                                        <td>{{ $book['student_name'] }}</td>
                                        <td>{{ $book['book_title'] }}</td>
                                        <td class="table-count"><span style="color: {{ $book['severity_color'] }}; font-weight: 600;">{{ number_format($book['days_overdue']) }}</span></td>
                                        <td class="table-count"><span style="color: {{ $book['severity_color'] }}; font-weight: 600;">{{ $formatCurrency($book['fine_amount']) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">No overdue books found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @break
@endswitch
