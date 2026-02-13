{{--
    Admin Pagination Component
    
    Props:
    - $paginator: LengthAwarePaginator - Laravel's paginator instance
    - $showInfo: boolean - Show "Showing X to Y of Z" text
    - $compact: boolean - Compact mode (smaller buttons)
    - $customClass: string - Custom CSS class
    - $onPageChange: string - JavaScript callback (optional)
    
    Usage:
    <x-admin-pagination :paginator="$users" :showInfo="true" />
--}}

@props([
    'paginator' => null,
    'showInfo' => true,
    'compact' => false,
    'customClass' => '',
    'onPageChange' => null,
])

@if ($paginator && $paginator->hasPages())
    <style>
        .admin-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 12px;
            border-top: 1px solid;
            margin-top: 16px;
            flex-wrap: wrap;
            gap: 12px;
        }

        body.light-theme .admin-pagination {
            border-color: #e2e8f0;
        }

        body.dark-theme .admin-pagination {
            border-color: #334155;
        }

        .admin-pagination-info {
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
        }

        body.dark-theme .admin-pagination-info {
            color: #94a3b8;
        }

        .admin-pagination-controls {
            display: flex;
            gap: 4px;
            align-items: center;
            flex-wrap: wrap;
        }

        .admin-pagination-btn {
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            background: transparent;
            color: #374151;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            min-width: 32px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        body.light-theme .admin-pagination-btn {
            background-color: #f9fafb;
            color: #374151;
        }

        body.dark-theme .admin-pagination-btn {
            background-color: #1e293b;
            border-color: #475569;
            color: #cbd5e1;
        }

        .admin-pagination-btn:hover:not(:disabled) {
            border-color: #3b82f6;
            color: #3b82f6;
            background-color: #dbeafe;
        }

        body.dark-theme .admin-pagination-btn:hover:not(:disabled) {
            background-color: #1e3a8a;
        }

        .admin-pagination-btn.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-color: #3b82f6;
            color: white;
        }

        .admin-pagination-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            background-color: #e5e7eb;
        }

        body.dark-theme .admin-pagination-btn:disabled {
            background-color: #334155;
        }

        .admin-pagination-separator {
            color: #d1d5db;
            padding: 0 4px;
            font-size: 12px;
        }

        body.dark-theme .admin-pagination-separator {
            color: #475569;
        }

        /* Compact mode */
        .admin-pagination.compact .admin-pagination-btn {
            padding: 4px 8px;
            font-size: 11px;
            min-width: 28px;
        }

        .admin-pagination.compact .admin-pagination-info {
            font-size: 11px;
        }

        @media (max-width: 640px) {
            .admin-pagination {
                flex-direction: column;
                align-items: stretch;
            }

            .admin-pagination-controls {
                justify-content: center;
                width: 100%;
            }

            .admin-pagination-info {
                text-align: center;
                width: 100%;
            }
        }
    </style>

    <div class="admin-pagination {{ $compact ? 'compact' : '' }} {{ $customClass }}">
        @if ($showInfo)
            <div class="admin-pagination-info">
                Showing <strong>{{ $paginator->firstItem() }}</strong> to 
                <strong>{{ $paginator->lastItem() }}</strong> of 
                <strong>{{ $paginator->total() }}</strong> results
            </div>
        @endif

        <div class="admin-pagination-controls">
            {{-- Previous Button --}}
            <button 
                class="admin-pagination-btn"
                @if ($paginator->onFirstPage()) disabled @endif
                @if (!$paginator->onFirstPage() && $onPageChange)
                    onclick="{{ $onPageChange }}(1)"
                @elseif (!$paginator->onFirstPage())
                    onclick="window.location.href='{{ $paginator->path() }}?page=1'"
                @endif
                title="First Page"
            >
                <i class="fas fa-chevron-double-left"></i>
            </button>

            <button 
                class="admin-pagination-btn"
                @if ($paginator->onFirstPage()) disabled @endif
                @if (!$paginator->onFirstPage() && $onPageChange)
                    onclick="{{ $onPageChange }}({{ $paginator->currentPage() - 1 }})"
                @elseif (!$paginator->onFirstPage())
                    onclick="window.location.href='{{ $paginator->previousPageUrl() }}'"
                @endif
                title="Previous"
            >
                <i class="fas fa-chevron-left"></i>
            </button>

            {{-- Page Numbers (Show only 5 at a time) --}}
            @php
                $current = $paginator->currentPage();
                $last = $paginator->lastPage();
                $pages = [];
                
                // Calculate range to show (5 pages at a time)
                $start = max(1, $current - 2);
                $end = min($last, $current + 2);
                
                // Adjust if we're near the start or end
                if ($end - $start < 4) {
                    if ($start === 1) {
                        $end = min($last, 5);
                    } else {
                        $start = max(1, $end - 4);
                    }
                }
                
                for ($i = $start; $i <= $end; $i++) {
                    $pages[] = $i;
                }
            @endphp

            {{-- Show dots before first page if needed --}}
            @if ($start > 1)
                <span class="admin-pagination-separator">...</span>
                <button 
                    class="admin-pagination-btn"
                    @if ($onPageChange)
                        onclick="{{ $onPageChange }}(1)"
                    @else
                        onclick="window.location.href='{{ $paginator->path() }}?page=1'"
                    @endif
                    title="Go to page 1"
                >
                    1
                </button>
                <span class="admin-pagination-separator">...</span>
            @endif

            {{-- Page number buttons --}}
            @foreach ($pages as $page)
                <button 
                    class="admin-pagination-btn {{ $page === $current ? 'active' : '' }}"
                    @if ($page !== $current && $onPageChange)
                        onclick="{{ $onPageChange }}({{ $page }})"
                    @elseif ($page !== $current)
                        onclick="window.location.href='{{ $paginator->path() }}?page={{ $page }}'"
                    @else
                        disabled
                    @endif
                    title="Go to page {{ $page }}"
                >
                    {{ $page }}
                </button>
            @endforeach

            {{-- Show dots after last page if needed --}}
            @if ($end < $last)
                <span class="admin-pagination-separator">...</span>
                <button 
                    class="admin-pagination-btn"
                    @if ($onPageChange)
                        onclick="{{ $onPageChange }}({{ $last }})"
                    @else
                        onclick="window.location.href='{{ $paginator->path() }}?page={{ $last }}'"
                    @endif
                    title="Go to page {{ $last }}"
                >
                    {{ $last }}
                </button>
                <span class="admin-pagination-separator">...</span>
            @endif

            {{-- Next Button --}}
            <button 
                class="admin-pagination-btn"
                @if ($paginator->hasMorePages() === false) disabled @endif
                @if ($paginator->hasMorePages() && $onPageChange)
                    onclick="{{ $onPageChange }}({{ $paginator->currentPage() + 1 }})"
                @elseif ($paginator->hasMorePages())
                    onclick="window.location.href='{{ $paginator->nextPageUrl() }}'"
                @endif
                title="Next"
            >
                <i class="fas fa-chevron-right"></i>
            </button>

            {{-- Last Button --}}
            <button 
                class="admin-pagination-btn"
                @if ($paginator->hasMorePages() === false) disabled @endif
                @if ($paginator->hasMorePages() && $onPageChange)
                    onclick="{{ $onPageChange }}({{ $last }})"
                @elseif ($paginator->hasMorePages())
                    onclick="window.location.href='{{ $paginator->path() }}?page={{ $last }}'"
                @endif
                title="Last Page"
            >
                <i class="fas fa-chevron-double-right"></i>
            </button>
        </div>
    </div>
@endif
