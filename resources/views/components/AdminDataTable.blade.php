<!--
    Reusable Admin Data Table Component
    
    Props:
    - $title: string - Table title
    - $subtitle: string - Table subtitle
    - $columns: array - Column definitions: [['key' => 'id', 'label' => 'ID', 'sortable' => true, 'width' => '80px'], ...]
    - $data: array - Row data
    - $actions: array - Action buttons: [['icon' => 'fa-edit', 'label' => 'Edit', 'class' => 'edit', 'onclick' => 'editRow()'], ...]
    - $searchable: boolean - Show search box
    - $paginated: boolean - Show pagination
    - $striped: boolean - Alternate row colors
    - $hover: boolean - Show hover effects
    - $emptyMessage: string - Message when no data
    - $rowClass: string - Custom class for rows
    - $responsive: boolean - Make responsive
-->

@props([
    'title' => 'Data Table',
    'subtitle' => 'Manage your data',
    'columns' => [],
    'data' => [],
    'actions' => [],
    'searchable' => true,
    'paginated' => false,
    'striped' => true,
    'hover' => true,
    'emptyMessage' => 'No data available',
    'rowClass' => '',
    'responsive' => true,
    'filters' => [],
    'showHeader' => true,
])

<style>
    /* Admin Data Table Component Styles */
    .admin-table-wrapper {
        padding: 5px;
    }

    .admin-table-header {
        margin-bottom: 16px;
    }

    .admin-table-title {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .admin-table-subtitle {
        font-size: 12px;
        color: #64748b;
    }

    body.dark-theme .admin-table-subtitle {
        color: #94a3b8;
    }

    .admin-table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .admin-table-search-box {
        position: relative;
        min-width: 250px;
    }

    .admin-table-search-input {
        width: 100%;
        padding: 8px 12px 8px 32px;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        font-size: 13px;
        transition: all 0.3s ease;
    }

    body.light-theme .admin-table-search-input {
        background-color: #ffffff;
        color: #0f172a;
    }

    body.dark-theme .admin-table-search-input {
        background-color: #1e293b;
        border-color: #475569;
        color: #e2e8f0;
    }

    .admin-table-search-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .admin-table-search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-size: 13px;
    }

    body.dark-theme .admin-table-search-icon {
        color: #9ca3af;
    }

    .admin-table-filters {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .admin-table-filter-btn {
        padding: 6px 12px;
        border-radius: 18px;
        border: 1px solid #d1d5db;
        background: transparent;
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
        transition: all 0.3s ease;
        color: #374151;
    }

    body.dark-theme .admin-table-filter-btn {
        border-color: #475569;
        color: #cbd5e1;
    }

    .admin-table-filter-btn.active {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border-color: transparent;
        color: white;
    }

    .admin-table-filter-btn:hover:not(.active) {
        background-color: #f3f4f6;
    }

    body.dark-theme .admin-table-filter-btn:hover:not(.active) {
        background-color: #334155;
    }

    .admin-table-container {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid;
        margin-top: 12px;
    }

    body.light-theme .admin-table-container {
        background-color: #ffffff;
        border-color: #e5e7eb;
    }

    body.dark-theme .admin-table-container {
        background-color: #1e293b;
        border-color: #334155;
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 100%;
    }

    .admin-table thead {
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .admin-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid;
        white-space: nowrap;
        user-select: none;
    }

    body.light-theme .admin-table th {
        background-color: #f8fafc;
        border-color: #e2e8f0;
        color: #475569;
    }

    body.dark-theme .admin-table th {
        background-color: #1e293b;
        border-color: #334155;
        color: #cbd5e1;
    }

    .admin-table th.sortable {
        cursor: pointer;
        user-select: none;
    }

    .admin-table th.sortable:hover {
        background-color: #e2e8f0;
    }

    body.dark-theme .admin-table th.sortable:hover {
        background-color: #334155;
    }

    .admin-table td {
        padding: 12px;
        border-bottom: 1px solid;
        vertical-align: middle;
        font-size: 13px;
        line-height: 1.5;
    }

    body.light-theme .admin-table td {
        color: #1f2937;
        border-color: #e2e8f0;
    }

    body.dark-theme .admin-table td {
        color: #e2e8f0;
        border-color: #334155;
    }

    .admin-table tbody tr {
        transition: all 0.2s ease;
    }

    .admin-table tbody tr:last-child td {
        border-bottom: none;
    }

    .admin-table tbody tr:hover {
        background-color: #f8fafc;
    }

    body.dark-theme .admin-table tbody tr:hover {
        background-color: #2d3748;
    }

    .admin-table tbody tr.striped:nth-child(odd) {
        background-color: #f9fafb;
    }

    body.dark-theme .admin-table tbody tr.striped:nth-child(odd) {
        background-color: #1f2937;
    }

    /* Status Badge - Multiple variants */
    .admin-status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 16px;
        font-size: 11px;
        font-weight: 600;
        gap: 6px;
        white-space: nowrap;
    }

    .admin-status-badge.active {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #166534;
    }

    body.dark-theme .admin-status-badge.active {
        background: linear-gradient(135deg, #14532d 0%, #052e16 100%);
        color: #4ade80;
    }

    .admin-status-badge.inactive {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }

    body.dark-theme .admin-status-badge.inactive {
        background: linear-gradient(135deg, #7f1d1d 0%, #450a0a 100%);
        color: #f87171;
    }

    .admin-status-badge.pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
    }

    body.dark-theme .admin-status-badge.pending {
        background: linear-gradient(135deg, #78350f 0%, #451a03 100%);
        color: #fbbf24;
    }

    .admin-status-badge.warning {
        background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);
        color: #9a3412;
    }

    body.dark-theme .admin-status-badge.warning {
        background: linear-gradient(135deg, #7c2d12 0%, #431407 100%);
        color: #fb923c;
    }

    /* Action Buttons */
    .admin-table-actions {
        display: flex;
        gap: 6px;
        align-items: center;
        justify-content: flex-start;
        flex-wrap: wrap;
    }

    .admin-action-btn {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: none;
        background: transparent;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        font-size: 13px;
        position: relative;
    }

    body.light-theme .admin-action-btn {
        color: #64748b;
        background-color: #f1f5f9;
    }

    body.dark-theme .admin-action-btn {
        color: #94a3b8;
        background-color: #334155;
    }

    .admin-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .admin-action-btn.edit:hover {
        color: #3b82f6;
        background: #dbeafe;
    }

    body.dark-theme .admin-action-btn.edit:hover {
        background: #1e3a8a;
        color: #60a5fa;
    }

    .admin-action-btn.view:hover {
        color: #06b6d4;
        background: #cffafe;
    }

    body.dark-theme .admin-action-btn.view:hover {
        background: #164e63;
        color: #22d3ee;
    }

    .admin-action-btn.password:hover {
        color: #10b981;
        background: #dcfce7;
    }

    body.dark-theme .admin-action-btn.password:hover {
        background: #14532d;
        color: #4ade80;
    }

    .admin-action-btn.toggle:hover {
        color: #f59e0b;
        background: #fef3c7;
    }

    body.dark-theme .admin-action-btn.toggle:hover {
        background: #78350f;
        color: #fbbf24;
    }

    .admin-action-btn.download:hover {
        color: #8b5cf6;
        background: #ede9fe;
    }

    body.dark-theme .admin-action-btn.download:hover {
        background: #6d28d9;
        color: #c4b5fd;
    }

    .admin-action-btn.delete:hover {
        color: #ef4444;
        background: #fee2e2;
    }

    body.dark-theme .admin-action-btn.delete:hover {
        background: #7f1d1d;
        color: #f87171;
    }

    .admin-action-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .admin-action-btn-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background-color: #1f2937;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
        margin-bottom: 4px;
        z-index: 1000;
    }

    .admin-action-btn:hover .admin-action-btn-tooltip {
        opacity: 1;
    }

    /* Role Badge */
    .admin-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .admin-role-badge.admin {
        background-color: #dbeafe;
        color: #1e40af;
    }

    body.dark-theme .admin-role-badge.admin {
        background-color: #1e3a8a;
        color: #60a5fa;
    }

    .admin-role-badge.staff {
        background-color: #f3e8ff;
        color: #7c3aed;
    }

    body.dark-theme .admin-role-badge.staff {
        background-color: #6d28d9;
        color: #c4b5fd;
    }

    .admin-role-badge.student {
        background-color: #dcfce7;
        color: #166534;
    }

    body.dark-theme .admin-role-badge.student {
        background-color: #14532d;
        color: #4ade80;
    }

    .admin-role-badge.user {
        background-color: #f0f9ff;
        color: #0c4a6e;
    }

    body.dark-theme .admin-role-badge.user {
        background-color: #082f49;
        color: #38bdf8;
    }

    /* User Cell with Avatar */
    .admin-user-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .admin-user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #3b82f6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 14px;
        flex-shrink: 0;
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .admin-user-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .admin-user-avatar:hover {
        border-color: #3b82f6;
        box-shadow: 0 0 8px rgba(59, 130, 246, 0.3);
    }

    .admin-user-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    .admin-user-name {
        font-weight: 600;
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .admin-user-email {
        font-size: 12px;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    body.dark-theme .admin-user-email {
        color: #94a3b8;
    }

    /* Empty State */
    .admin-table-empty {
        text-align: center;
        padding: 40px 20px;
    }

    .admin-table-empty-icon {
        font-size: 48px;
        opacity: 0.3;
        margin-bottom: 12px;
    }

    .admin-table-empty-message {
        font-size: 14px;
        font-weight: 500;
        opacity: 0.7;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .admin-table-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .admin-table-search-box {
            min-width: 100%;
        }

        .admin-table {
            font-size: 12px;
        }

        .admin-table th,
        .admin-table td {
            padding: 8px;
        }

        .admin-action-btn {
            width: 28px;
            height: 28px;
            font-size: 12px;
        }

        .admin-user-avatar {
            width: 32px;
            height: 32px;
            font-size: 12px;
        }
    }

    @media (max-width: 640px) {
        .admin-table-title {
            font-size: 16px;
        }

        .admin-table th {
            font-size: 10px;
            padding: 8px;
        }

        .admin-table td {
            padding: 8px;
            font-size: 12px;
        }

        .admin-user-cell {
            gap: 8px;
        }

        .admin-user-avatar {
            width: 28px;
            height: 28px;
            font-size: 10px;
        }

        .admin-action-btn {
            width: 24px;
            height: 24px;
            font-size: 11px;
        }
    }
</style>

<div class="admin-table-wrapper">
    @if ($showHeader)
        <div class="admin-table-header">
            <div class="admin-table-title">{{ $title }}</div>
            <div class="admin-table-subtitle">{{ $subtitle }}</div>
        </div>
    @endif

    <div class="admin-table-toolbar">
        <div style="flex: 1;"></div>

        @if ($searchable)
            <div class="admin-table-search-box">
                <i class="fas fa-search admin-table-search-icon"></i>
                <input 
                    type="text" 
                    class="admin-table-search-input"
                    placeholder="Search..." 
                    id="adminTableSearch{{ uniqid() }}"
                    data-table-search
                >
            </div>
        @endif

        @if (count($filters) > 0)
            <div class="admin-table-filters">
                @foreach ($filters as $filter)
                    <button 
                        class="admin-table-filter-btn {{ $filter['active'] ?? false ? 'active' : '' }}"
                        data-filter="{{ $filter['value'] ?? '' }}"
                    >
                        <i class="{{ $filter['icon'] ?? 'fas fa-filter' }}"></i>
                        {{ $filter['label'] ?? 'Filter' }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <div class="admin-table-container">
        @if (count($data) > 0)
            <table class="admin-table" data-admin-table>
                <thead>
                    <tr>
                        @foreach ($columns as $column)
                            <th 
                                @if ($column['sortable'] ?? false) class="sortable" @endif
                                @if (isset($column['width'])) style="width: {{ $column['width'] }}" @endif
                            >
                                @if ($column['sortable'] ?? false)
                                    <span style="display: flex; align-items: center; gap: 4px;">
                                        {{ $column['label'] ?? ucwords(str_replace('_', ' ', $column['key'])) }}
                                        <i class="fas fa-arrow-down-up" style="font-size: 10px; opacity: 0.5;"></i>
                                    </span>
                                @else
                                    {{ $column['label'] ?? ucwords(str_replace('_', ' ', $column['key'])) }}
                                @endif
                            </th>
                        @endforeach

                        @if (count($actions) > 0)
                            <th style="width: {{ count($actions) * 40 + 20 }}px">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $row)
                        <tr class="{{ $striped ? 'striped' : '' }} {{ $rowClass }}" data-row-id="{{ $row['id'] ?? '' }}">
                            @foreach ($columns as $column)
                                <td>
                                    {!! $row[$column['key']] ?? '-' !!}
                                </td>
                            @endforeach

                            @if (count($actions) > 0)
                                <td>
                                    <div class="admin-table-actions">
                                        @foreach ($actions as $action)
                                            <button 
                                                class="admin-action-btn {{ $action['class'] ?? '' }}"
                                                @if (isset($action['onclick'])) onclick="{!! $action['onclick'] !!}({{ json_encode($row) }})" @endif
                                                @if (isset($action['disabled']) && $action['disabled']) disabled @endif
                                                title="{{ $action['tooltip'] ?? $action['label'] ?? '' }}"
                                            >
                                                <i class="fas {{ $action['icon'] ?? 'fa-ellipsis-h' }}"></i>
                                                <span class="admin-action-btn-tooltip">{{ $action['label'] ?? '' }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($paginated)
                <div id="paginationContainer" style="margin-top: 20px; padding: 0 12px;">
                    {{-- Pagination links will go here --}}
                </div>
            @endif
        @else
            <div class="admin-table-empty">
                <div class="admin-table-empty-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <div class="admin-table-empty-message">{{ $emptyMessage }}</div>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchInput = document.querySelector('[data-table-search]');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const table = document.querySelector('[data-admin-table]');
                if (!table) return;

                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }

        // Filter functionality
        const filterBtns = document.querySelectorAll('[data-filter]');
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                // Filter logic here
            });
        });

        // Sortable headers
        const sortHeaders = document.querySelectorAll('th.sortable');
        sortHeaders.forEach(header => {
            header.addEventListener('click', function() {
                console.log('Sort by:', this.textContent);
                // Implement sorting logic
            });
        });
    });
</script>
