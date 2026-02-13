@extends('Staff.layouts.app')

@section('title', 'Fines')

@push('styles')
    <style>
        /* Fines Table Styles */
        .fines-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .fines-table thead tr {
            background-color: #f8fafc;
        }

        body.dark-theme .fines-table thead tr {
            background-color: #1e293b;
        }

        .fines-table th {
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.65rem;
            padding: 0.6rem 0.8rem;
            border-bottom: 2px solid #e2e8f0;
            color: #64748b;
        }

        body.dark-theme .fines-table th {
            border-bottom-color: #475569;
            color: #94a3b8;
        }

        .fines-table tbody tr {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-bottom: 1px solid #f1f5f9;
        }

        body.dark-theme .fines-table tbody tr {
            border-bottom-color: #334155;
        }

        /* Striped rows */
        .fines-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        body.dark-theme .fines-table tbody tr:nth-child(even) {
            background-color: rgba(30, 41, 59, 0.4);
        }

        /* Hover effects */
        .fines-table tbody tr:hover {
            background-color: #e2e8f0 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        body.dark-theme .fines-table tbody tr:hover {
            background-color: #334155 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }

        .fines-table td {
            padding: 0.6rem 0.8rem;
            font-size: 0.8rem;
            color: #475569;
        }

        body.dark-theme .fines-table td {
            color: #cbd5e1;
        }

        .fines-table td:first-child {
            border-top-left-radius: 0.5rem;
            border-bottom-left-radius: 0.5rem;
        }

        .fines-table td:last-child {
            border-top-right-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }

        /* Status Badges */
        .status-badge {
            padding: 0.25rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.65rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.2s ease;
        }

        .status-badge:hover {
            transform: scale(1.05);
        }

        .status-paid {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        body.dark-theme .status-paid {
            background-color: #14532d;
            color: #4ade80;
            border-color: #166534;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        body.dark-theme .status-pending {
            background-color: #78350f;
            color: #fbbf24;
            border-color: #92400e;
        }

        .status-waived {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        body.dark-theme .status-waived {
            background-color: #1e3a8a;
            color: #60a5fa;
            border-color: #1e40af;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.4rem;
        }

        .action-btn {
            padding: 0.4rem 0.8rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-paid {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-paid:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);
        }

        .btn-waive {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-waive:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
        }

        .btn-email {
            padding: 0.35rem;
            border-radius: 0.5rem;
            background: transparent;
            border: 1px solid #e2e8f0;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.dark-theme .btn-email {
            border-color: #475569;
            color: #94a3b8;
        }

        .btn-email:hover {
            background-color: #f1f5f9;
            color: #475569;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        body.dark-theme .btn-email:hover {
            background-color: #334155;
            color: #e2e8f0;
        }

        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            gap: 0.25rem;
            border-bottom: 2px solid #e5e7eb;
            padding: 0.25rem;
            background: #f8fafc;
            border-radius: 0.75rem;
        }

        body.dark-theme .filter-tabs {
            border-bottom-color: #374151;
            background: #1e293b;
        }

        .filter-tab {
            padding: 0.5rem 1rem;
            border: none;
            background: transparent;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.8rem;
            color: #64748b;
            border-radius: 0.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.dark-theme .filter-tab {
            color: #94a3b8;
        }

        .filter-tab:hover {
            color: #475569;
            background-color: #e2e8f0;
        }

        body.dark-theme .filter-tab:hover {
            color: #e2e8f0;
            background-color: #334155;
        }

        .filter-tab.active {
            color: #2563eb;
            background-color: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        body.dark-theme .filter-tab.active {
            color: #60a5fa;
            background-color: #334155;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        /* Pagination - Modern Style */
        .pagination-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem 0;
        }

        .pagination-info {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 500;
        }

        body.dark-theme .pagination-info {
            color: #94a3b8;
        }

        .pagination-buttons {
            display: flex;
            gap: 0.3rem;
            align-items: center;
        }

        .pagination-btn {
            padding: 0.4rem 0.7rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            background: white;
            color: #475569;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.75rem;
            font-weight: 500;
            min-width: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
        }

        body.dark-theme .pagination-btn {
            border-color: #475569;
            background: #1e293b;
            color: #cbd5e1;
        }

        .pagination-btn:hover:not(:disabled) {
            background-color: #f1f5f9;
            border-color: #cbd5e1;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        body.dark-theme .pagination-btn:hover:not(:disabled) {
            background-color: #334155;
            border-color: #64748b;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .pagination-btn.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border-color: #2563eb;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }

        .pagination-btn.active:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }

        .pagination-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .pagination-ellipsis {
            padding: 0.5rem 0.25rem;
            color: #64748b;
        }

        body.dark-theme .pagination-ellipsis {
            color: #94a3b8;
        }

        /* Stats Cards Enhancement */
        .stats-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e2e8f0;
        }

        body.dark-theme .stats-card {
            border-color: #334155;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border-color: #cbd5e1;
        }

        body.dark-theme .stats-card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            border-color: #475569;
        }

        .stats-card .stats-icon {
            transition: transform 0.3s ease;
        }

        .stats-card:hover .stats-icon {
            transform: scale(1.1) rotate(5deg);
        }

        /* Loading Spinner */
        .loading-spinner {
            display: inline-block;
            width: 1.25rem;
            height: 1.25rem;
            border: 3px solid #f3f4f6;
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        body.dark-theme .loading-spinner {
            border-color: #374151;
            border-top-color: #60a5fa;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Animations */
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fade-out {
            from {
                opacity: 1;
                transform: translateY(0);
            }

            to {
                opacity: 0;
                transform: translateY(-10px);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }

        .animate-fade-out {
            animation: fade-out 0.3s ease-in;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            opacity: 0.4;
        }

        /* Search Bar Enhancement */
        #searchInput {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid #e2e8f0;
        }

        #searchInput:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        body.dark-theme #searchInput:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
        }

        /* Card Enhancement */
        .card {
            border-radius: 1rem;
            background: white;
            border: 1px solid #e2e8f0;
        }

        body.dark-theme .card {
            background: #1e293b;
            border-color: #334155;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-primary">Fines Records</h1>
            <p class="mt-1 text-secondary">Manage and update fines for overdue books</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-2 mb-2 md:grid-cols-2 lg:grid-cols-4">

            <!-- Total Fines Card -->
            <div class="p-2 shadow-sm card stats-card">
                <div class="flex justify-between mb-1">
                    <div>
                        <p class="text-xs font-medium text-muted">Total Fines</p>
                        <h3 class="text-2xl font-bold" id="totalFines">₹0</h3>
                    </div>
                    <div
                        class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg dark:bg-blue-900 stats-icon">
                        <i data-lucide="receipt" class="w-6 h-6 text-blue-600 dark:text-blue-400"></i>
                    </div>
                </div>
                <p class="text-xs text-muted">All issued fines</p>
            </div>

            <!-- Collected fines card -->
            <div class="p-2 shadow-sm card stats-card">
                <div class="flex justify-between mb-1">
                    <div>
                        <p class="text-xs font-medium text-muted">Collected</p>
                        <h3 class="text-2xl font-bold" id="collectedFines">₹0</h3>
                    </div>
                    <div
                        class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-lg dark:bg-green-900 stats-icon">
                        <i data-lucide="banknote" class="w-6 h-6 text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
                <p class="text-xs text-muted">Total collected</p>
            </div>

            <!-- Pending Fine card -->
            <div class="p-2 shadow-sm card stats-card">
                <div class="flex justify-between mb-1">
                    <div>
                        <p class="text-xs font-medium text-muted">Pending</p>
                        <h3 class="text-2xl font-bold" id="pendingFines">₹0</h3>
                    </div>
                    <div
                        class="flex items-center justify-center w-10 h-10 bg-yellow-100 rounded-lg dark:bg-yellow-900 stats-icon">
                        <i data-lucide="clock-alert" class="w-6 h-6 text-yellow-600 dark:text-yellow-400"></i>
                    </div>
                </div>
                <p class="text-xs text-muted">To be collected</p>
            </div>

            <!-- Waived Fine card -->
            <div class="p-2 shadow-sm card stats-card">
                <div class="flex justify-between mb-1">
                    <div>
                        <p class="text-xs font-medium text-muted">Waived</p>
                        <h3 class="text-2xl font-bold" id="waivedFines">₹0</h3>
                    </div>
                    <div
                        class="flex items-center justify-center w-10 h-10 bg-purple-100 rounded-lg dark:bg-purple-900 stats-icon">
                        <i data-lucide="shield-check" class="w-6 h-6 text-purple-600 dark:text-purple-400"></i>
                    </div>
                </div>
                <p class="text-xs text-muted">Waived amount</p>
            </div>
        </div>


        <div class="p-6">
            <div class="p-0 overflow-hidden card">
                <!-- Table Header -->
                <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-base font-semibold text-primary">Fines List</h2>
                            <p class="text-xs text-secondary">Fines records for overdue books</p>
                        </div>
                        <button onclick="finesManager.exportToCSV()"
                            class="flex items-center gap-2 px-3 py-1 font-medium text-white transition-all bg-blue-600 rounded-lg hover:bg-blue-700 hover:shadow-lg">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                            Export CSV
                        </button>
                    </div>

                    <!-- Search Bar -->
                    <div class="relative mb-2">
                        <input type="text" id="searchInput"
                            placeholder="Search by student name, book title, or reason..."
                            class="w-full py-2 pl-10 pr-4 transition-all bg-transparent border border-gray-300 rounded-lg dark:border-gray-600 text-primary focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <i data-lucide="search" class="absolute w-5 h-5 text-gray-400 left-3 top-3"></i>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="filter-tabs">
                        <button class="filter-tab active" data-filter="all" onclick="finesManager.setFilter('all')">
                            All Fines
                        </button>
                        <button class="filter-tab" data-filter="Pending" onclick="finesManager.setFilter('Pending')">
                            Pending
                        </button>
                        <button class="filter-tab" data-filter="paid" onclick="finesManager.setFilter('paid')">
                            Paid
                        </button>
                        <button class="filter-tab" data-filter="waived" onclick="finesManager.setFilter('waived')">
                            Waived
                        </button>
                        <button class="filter-tab" data-filter="overdue" onclick="finesManager.setFilter('overdue')">
                            Overdue
                        </button>
                    </div>
                </div>

                <!-- Fines Table -->
                <div class="overflow-x-auto">
                    <table class="w-full fines-table">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-6 py-3 text-sm font-medium text-left text-secondary">Student ID</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-secondary">Student Name</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-secondary">Book Title</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-secondary">Due Date</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-secondary">Days Overdue</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-secondary">Fine Amount</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-secondary">Status</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-secondary">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="finesTableBody">
                            <!-- Data will be populated by JavaScript -->
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-secondary">
                                    <div class="loading-spinner" style="margin: 0 auto;"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer -->
                <div id="paginationContainer"
                    class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="pagination-info">
                        Showing <span id="recordCount">0</span> records out of <span id="totalCount">0</span>
                    </div>
                    <div id="paginationButtons" class="pagination-buttons">
                        <!-- Pagination will be generated here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Waive Fine Modal -->
        <div id="waiveModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
            <div class="w-full max-w-md mx-4 card">
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-primary">Waive Fine</h3>
                    <button onclick="finesManager.closeWaiveModal()" class="transition text-secondary hover:text-primary">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <div class="p-6">
                    <p class="mb-4 text-secondary">Please provide a reason for waiving this fine:</p>
                    <textarea id="waiveReason" rows="4"
                        class="w-full px-3 py-2 transition bg-transparent border border-gray-300 rounded-lg dark:border-gray-600 text-primary focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter reason..."></textarea>
                    <div class="flex justify-end mt-6 space-x-3">
                        <button onclick="finesManager.closeWaiveModal()"
                            class="px-4 py-2 transition border border-gray-300 rounded-lg dark:border-gray-600 text-primary hover:bg-gray-50 dark:hover:bg-gray-800">
                            Cancel
                        </button>
                        <button onclick="finesManager.confirmWaiveFine()"
                            class="px-4 py-2 text-white transition bg-green-600 rounded-lg hover:bg-green-700">
                            Waive Fine
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <div id="successModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
            <div class="w-full max-w-sm mx-4 card">
                <div class="p-6 text-center">
                    <div
                        class="flex items-center justify-center w-12 h-12 mx-auto mb-4 text-green-600 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-400">
                        <i data-lucide="check-circle" class="w-8 h-8"></i>
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-primary" id="successTitle">Action Successful!</h3>
                    <p class="mb-6 text-secondary" id="successMessage">The action has been completed.</p>
                    <button onclick="finesManager.closeSuccessModal()"
                        class="px-4 py-2 text-white transition bg-blue-600 rounded-lg hover:bg-blue-700">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const finesManager = {
            state: {
                currentPage: 1,
                perPage: 10,
                filter: 'all',
                search: '',
                sortBy: 'created_at',
                order: 'desc',
                fines: [],
                stats: {},
                pagination: {}
            },

            // BroadcastChannel for cross-tab communication
            fineUpdateChannel: null,

            init() {
                this.setupEventListeners();
                this.setupBroadcastChannel();
                this.loadFines();
            },

            setupBroadcastChannel() {
                try {
                    this.fineUpdateChannel = new BroadcastChannel('fine_updates');
                    console.log('[Fines] BroadcastChannel object created:', typeof this.fineUpdateChannel);
                    console.log('[Fines] Channel name: fine_updates');

                    // Optional: Listen for messages from ViewStudent page
                    this.fineUpdateChannel.addEventListener('message', (event) => {
                        console.log('[Fines] Received message (should not happen):', event.data);
                    });

                    console.log('[Fines] BroadcastChannel initialized and listener attached');
                } catch (e) {
                    console.error('[Fines] BroadcastChannel ERROR:', e);
                    console.error('[Fines] Error message:', e.message);
                    console.error('[Fines] Error stack:', e.stack);
                }
            },

            notifyFineUpdate(fineId, status) {
                console.log('[Fines] notifyFineUpdate called with:', {
                    fineId,
                    status
                });

                // Notify via localStorage (for other tabs)
                try {
                    localStorage.setItem('fineUpdated', JSON.stringify({
                        fineId,
                        status,
                        timestamp: Date.now()
                    }));
                    console.log('[Fines] localStorage notification sent');
                } catch (e) {
                    console.error('[Fines] Error updating localStorage:', e);
                }

                // Notify via BroadcastChannel (for same browser window)
                if (this.fineUpdateChannel) {
                    try {
                        const messageData = {
                            type: 'fineUpdated',
                            fineId: fineId,
                            status: status,
                            timestamp: Date.now()
                        };
                        console.log('[Fines] About to send BroadcastChannel message:', messageData);
                        this.fineUpdateChannel.postMessage(messageData);
                        console.log('[Fines] ✓ BroadcastChannel message sent successfully');
                        console.log(
                            '[Fines] Message details - type: postMessage, channel: fine_updates, contains fineId:',
                            fineId);
                    } catch (e) {
                        console.error('[Fines] ✗ Error broadcasting fine update:', e);
                        console.error('[Fines] Error message:', e.message);
                    }
                } else {
                    console.warn('[Fines] ✗ BroadcastChannel not initialized (null)');
                }
            },

            setupEventListeners() {
                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    let debounceTimer;
                    searchInput.addEventListener('input', (e) => {
                        clearTimeout(debounceTimer);
                        this.state.search = e.target.value;
                        this.state.currentPage = 1;
                        debounceTimer = setTimeout(() => this.loadFines(), 300);
                    });
                }
            },

            setFilter(status) {
                this.state.filter = status;
                this.state.currentPage = 1;

                // Update active tab
                document.querySelectorAll('.filter-tab').forEach(tab => {
                    tab.classList.remove('active');
                });
                document.querySelector(`[data-filter="${status}"]`).classList.add('active');

                this.loadFines();
            },

            loadFines() {
                const params = new URLSearchParams({
                    search: this.state.search,
                    status: this.state.filter,
                    page: this.state.currentPage,
                    per_page: this.state.perPage,
                    sort: this.state.sortBy,
                    order: this.state.order
                });

                const tbody = document.getElementById('finesTableBody');
                if (tbody) {
                    tbody.innerHTML =
                        '<tr><td colspan="8" class="px-6 py-8 text-center text-secondary"><div class="loading-spinner" style="margin: 0 auto;"></div></td></tr>';
                }

                fetch(`{{ route('staff.fines.data') }}?${params}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            this.state.fines = data.fines || [];
                            this.state.stats = data.stats || {};
                            this.state.pagination = data.pagination || {};
                            this.renderTable();
                            this.updateStats();
                            this.renderPagination();
                        } else {
                            this.showError(data.message || 'Error loading fines');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading fines:', error);
                        this.showError('Error loading fines: ' + error.message);
                    });
            },

            renderTable() {
                const tbody = document.getElementById('finesTableBody');
                if (!tbody) return;

                if (this.state.fines.length === 0) {
                    tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="px-6 py-8">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i data-lucide="inbox" class="inline w-16 h-16"></i>
                                </div>
                                <p class="text-secondary">No fines found</p>
                            </div>
                        </td>
                    </tr>
                `;
                    lucide.createIcons();
                    return;
                }

                tbody.innerHTML = this.state.fines.map(fine => `
                <tr class="border-b border-gray-200 dark:border-gray-700" data-fine-id="${fine.id}">
                    <td class="px-6 py-3 text-primary">${fine.studentId}</td>
                    <td class="px-6 py-3 text-primary">${fine.studentName}</td>
                    <td class="px-6 py-3 text-primary">${fine.bookTitle}</td>
                    <td class="px-6 py-3 text-secondary">${fine.dueDate}</td>
                    <td class="px-6 py-3 text-secondary">${fine.daysOverdue}</td>
                    <td class="py-3 px-6 font-medium ${fine.status.toLowerCase() === 'pending' ? 'text-red-600 dark:text-red-400' : 'text-primary'}">₹${parseFloat(fine.fineAmount).toFixed(2)}</td>
                    <td class="px-6 py-3">
                        <div class="status-badge ${this.getStatusClass(fine.status)}">
                            <i data-lucide="${this.getStatusIcon(fine.status)}" class="w-3 h-3"></i>
                            ${fine.status}
                        </div>
                    </td>
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-2">
                            <div class="action-buttons">
                                ${fine.status.toLowerCase() === 'pending' ? `
                                            <button class="action-btn btn-paid" onclick="finesManager.markAsPaid(${fine.id})">
                                                <i data-lucide="credit-card" class="w-4 h-4"></i>
                                                Paid
                                            </button>
                                            <button class="action-btn btn-waive" onclick="finesManager.openWaiveModal(${fine.id})">
                                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                                                Waive
                                            </button>
                                        ` : ''}
                            </div>
                            <button class="btn-email" onclick="finesManager.sendEmailNotification(${fine.id})" title="Send Email">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

                lucide.createIcons();
            },

            getStatusClass(status) {
                const statusLower = status.toLowerCase();
                if (statusLower === 'paid') return 'status-paid';
                if (statusLower === 'waived') return 'status-waived';
                return 'status-pending';
            },

            getStatusIcon(status) {
                const statusLower = status.toLowerCase();
                if (statusLower === 'paid') return 'check-circle';
                if (statusLower === 'waived') return 'shield-check';
                return 'alert-circle';
            },

            updateStats() {
                const stats = this.state.stats;
                document.getElementById('totalFines').textContent = `₹${(stats.total || 0).toFixed(2)}`;
                document.getElementById('collectedFines').textContent = `₹${(stats.collected || 0).toFixed(2)}`;
                document.getElementById('pendingFines').textContent = `₹${(stats.pending || 0).toFixed(2)}`;
                document.getElementById('waivedFines').textContent = `₹${(stats.waived || 0).toFixed(2)}`;
            },

            renderPagination() {
                const pagination = this.state.pagination;
                const paginationButtons = document.getElementById('paginationButtons');
                if (!paginationButtons) return;

                paginationButtons.innerHTML = '';

                // Previous button
                const prevBtn = document.createElement('button');
                prevBtn.className = 'pagination-btn';
                prevBtn.disabled = pagination.current_page === 1;
                prevBtn.innerHTML = '<i data-lucide="chevron-left" class="w-4 h-4"></i>';
                prevBtn.onclick = () => this.goToPage(pagination.current_page - 1);
                paginationButtons.appendChild(prevBtn);

                // Page numbers
                const startPage = Math.max(1, pagination.current_page - 2);
                const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

                if (startPage > 1) {
                    const btn = document.createElement('button');
                    btn.className = 'pagination-btn';
                    btn.textContent = '1';
                    btn.onclick = () => this.goToPage(1);
                    paginationButtons.appendChild(btn);

                    if (startPage > 2) {
                        const dots = document.createElement('span');
                        dots.className = 'pagination-ellipsis';
                        dots.textContent = '...';
                        paginationButtons.appendChild(dots);
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    const btn = document.createElement('button');
                    btn.className = i === pagination.current_page ? 'pagination-btn active' : 'pagination-btn';
                    btn.textContent = i;
                    btn.onclick = () => this.goToPage(i);
                    paginationButtons.appendChild(btn);
                }

                if (endPage < pagination.last_page) {
                    if (endPage < pagination.last_page - 1) {
                        const dots = document.createElement('span');
                        dots.className = 'pagination-ellipsis';
                        dots.textContent = '...';
                        paginationButtons.appendChild(dots);
                    }

                    const btn = document.createElement('button');
                    btn.className = 'pagination-btn';
                    btn.textContent = pagination.last_page;
                    btn.onclick = () => this.goToPage(pagination.last_page);
                    paginationButtons.appendChild(btn);
                }

                // Next button
                const nextBtn = document.createElement('button');
                nextBtn.className = 'pagination-btn';
                nextBtn.disabled = pagination.current_page === pagination.last_page;
                nextBtn.innerHTML = '<i data-lucide="chevron-right" class="w-4 h-4"></i>';
                nextBtn.onclick = () => this.goToPage(pagination.current_page + 1);
                paginationButtons.appendChild(nextBtn);

                // Update record count
                const startRecord = (pagination.current_page - 1) * this.state.perPage + 1;
                const endRecord = Math.min(pagination.current_page * this.state.perPage, pagination.total);

                document.getElementById('recordCount').textContent = `${startRecord}-${endRecord}`;
                document.getElementById('totalCount').textContent = pagination.total || 0;

                lucide.createIcons();
            },

            goToPage(page) {
                this.state.currentPage = page;
                this.loadFines();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            },

            markAsPaid(fineId) {
                if (!confirm('Mark this fine as paid?')) return;

                fetch(`/staff/fines/${fineId}/mark-as-paid`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Show amount in success popup
                            const fine = this.state.fines.find(f => f.id === fineId) || null;
                            const amount = fine ? parseFloat(fine.fineAmount).toFixed(2) : null;
                            this.showSuccess(amount ? `Fine marked as paid (₹${amount})` : 'Fine marked as paid');
                            // Notify other pages/tabs that fine was updated
                            this.notifyFineUpdate(fineId, 'paid');
                            this.loadFines();
                        } else {
                            this.showError(data.message || 'Error updating fine');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showError('Error updating fine: ' + error.message);
                    });
            },

            openWaiveModal(fineId) {
                this.state.currentFineId = fineId;
                document.getElementById('waiveReason').value = '';
                document.getElementById('waiveModal').classList.remove('hidden');
            },

            closeWaiveModal() {
                document.getElementById('waiveModal').classList.add('hidden');
                this.state.currentFineId = null;
            },

            confirmWaiveFine() {
                const reason = document.getElementById('waiveReason').value.trim();
                if (!reason) {
                    this.showError('Please enter a reason for waiving the fine');
                    return;
                }

                const fineId = this.state.currentFineId;
                fetch(`/staff/fines/${fineId}/waive`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            remarks: reason
                        })
                    })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            this.closeWaiveModal();
                            this.showSuccess(`Fine waived: ${reason}`);
                            // Notify other pages/tabs that fine was updated
                            this.notifyFineUpdate(this.state.currentFineId, 'waived');
                            this.loadFines();
                        } else {
                            this.showError(data.message || 'Error waiving fine');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showError('Error waiving fine: ' + error.message);
                    });
            },

            sendEmailNotification(fineId) {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                fetch(`/staff/fines/${fineId}/send-email`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            this.showSuccess(data.message ||
                                'Email queued — the student will receive a notification shortly');
                        } else {
                            this.showError(data.message || 'Failed to send email notification');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showError('Error sending email: ' + error.message);
                    });
            },

            exportToCSV() {
                if (this.state.fines.length === 0) {
                    this.showError('No fines to export');
                    return;
                }

                const headers = ['Student ID', 'Student Name', 'Book Title', 'Due Date', 'Days Overdue', 'Fine Amount',
                    'Status'
                ];
                const rows = this.state.fines.map(fine => [
                    fine.studentId,
                    fine.studentName,
                    fine.bookTitle,
                    fine.dueDate,
                    fine.daysOverdue,
                    `₹${parseFloat(fine.fineAmount).toFixed(2)}`,
                    fine.status
                ]);

                const csvContent = [
                    headers.join(','),
                    ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
                ].join('\n');

                const blob = new Blob([csvContent], {
                    type: 'text/csv;charset=utf-8;'
                });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', `fines_export_${new Date().toISOString().split('T')[0]}.csv`);
                link.style.display = 'none';

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                setTimeout(() => URL.revokeObjectURL(url), 100);

                this.showSuccess('CSV exported successfully');
            },

            showSuccess(message) {
                const modal = document.getElementById('successModal');
                document.getElementById('successTitle').textContent = 'Success!';
                document.getElementById('successMessage').textContent = message;
                modal.classList.remove('hidden');
                setTimeout(() => this.closeSuccessModal(), 3000);
            },

            closeSuccessModal() {
                document.getElementById('successModal').classList.add('hidden');
            },

            showError(message) {
                alert('Error: ' + message);
            }
        };

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            finesManager.init();
        });
    </script>
@endpush
