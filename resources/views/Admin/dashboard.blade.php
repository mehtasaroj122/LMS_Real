@extends('Admin.layouts.app')

@section('page-title', 'Dashboard')

@section('content')
    <div class="mx-auto max-w-7xl">

        <!-- Page Header -->
        <div class="mb-2">
            <h2 class="text-lg font-bold text-primary">Admin Dashboard</h2>
            <p class="text-xs text-muted">Overview of library statistics and activities</p>
        </div>

        <!-- ==================== QUICK STATS ==================== -->
        <div class="grid grid-cols-1 gap-2 mb-4 md:grid-cols-2 lg:grid-cols-4">

            <!-- Total Books -->
            <div class="p-4 shadow-sm card">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-xs font-medium text-muted">Total Books</p>
                        <h3 class="text-2xl font-bold">{{ $totalBooks }}</h3>
                    </div>
                    <i data-lucide="book" class="w-5 h-5 text-muted"></i>
                </div>
                <p class="text-xs text-muted">{{ $availableBooks }} available</p>
            </div>

            <!-- Books Issued -->
            <div class="p-4 shadow-sm card">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-xs font-medium text-muted">Books Issued</p>
                        <h3 class="text-2xl font-bold">{{ $issuedBooks }}</h3>
                    </div>
                    <i data-lucide="trending-up" class="w-5 h-5 text-muted"></i>
                </div>
                <p class="text-xs text-muted">Currently issued</p>
            </div>

            <!-- Overdue Books -->
            <div class="p-4 shadow-sm card">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-xs font-medium text-muted">Overdue Books</p>
                        <h3 class="text-2xl font-bold text-danger">{{ $overdueBooks }}</h3>
                    </div>
                    <i data-lucide="alert-circle" class="w-5 h-5 text-danger"></i>
                </div>
                <p class="text-xs text-muted">Need attention</p>
            </div>

            {{-- <!-- Pending Requests -->
            <div class="p-4 shadow-sm card">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-xs font-medium text-muted">Pending Requests</p>
                        <h3 class="text-2xl font-bold">{{ $pendingRequestsCount ?? 0 }}</h3>
                    </div>
                    <i data-lucide="clock" class="w-5 h-5 text-muted"></i>
                </div>
                <p class="text-xs text-muted">Book requests awaiting approval</p>
            </div> --}}

            <!-- Total Users -->
            <div class="p-4 shadow-sm card">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-xs font-medium text-muted">Total Users</p>
                        <h3 class="text-2xl font-bold">6</h3>
                    </div>
                    <i data-lucide="users" class="w-5 h-5 text-muted"></i>
                </div>
                <p class="text-xs text-muted">{{ $totalStudents }} students</p>
            </div>

            <!-- Pending Fines -->
            <div class="p-4 shadow-sm card">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-xs font-medium text-muted">Pending Fines</p>
                        <h3 class="text-2xl font-bold">₹{{ number_format($pendingFines, 2) }}</h3>
                    </div>
                    <i data-lucide="dollar-sign" class="w-5 h-5 text-muted"></i>
                </div>
                <p class="text-xs text-muted">Unpaid fines</p>
            </div>

            <!-- Fines Collected -->
            <div class="p-4 shadow-sm card">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-xs font-medium text-muted">Fines Collected</p>
                        <h3 class="text-2xl font-bold text-green-500">₹{{ number_format($collectedFines, 2) }}</h3>
                    </div>
                    <i data-lucide="dollar-sign" class="w-5 h-5 text-green-500"></i>
                </div>
                <p class="text-xs text-muted">Total collected</p>
            </div>

            <!-- Today's Activity -->
            <div class="p-4 shadow-sm card">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-xs font-medium text-muted">Today's Activity</p>
                        <h3 class="text-2xl font-bold">{{ $todayActivities }}</h3>
                    </div>
                    <i data-lucide="calendar" class="w-5 h-5 text-muted"></i>
                </div>
                <p class="text-xs text-muted">Actions logged</p>
            </div>

            <!-- Book Categories -->
            <div class="p-4 shadow-sm card">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-xs font-medium text-muted">Book Categories</p>
                        <h3 class="text-2xl font-bold">{{ $totalCategories }}</h3>
                    </div>
                    <i data-lucide="book" class="w-5 h-5 text-muted"></i>
                </div>
                <p class="text-xs text-muted">Different categories</p>
            </div>

        </div>

        <!-- ==================== MAIN SECTIONS ==================== -->
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">

            <!-- Overdue Books -->
            <div class="p-3 shadow-sm card">
                <h4 class="mb-2 text-sm font-bold">Overdue Books</h4>

                @forelse($overdueList as $issue)
                    <div class="flex items-start justify-between gap-2 pb-2 mb-2 border-b last:border-b-0 last:mb-0 last:pb-0">
                        <div class="flex-1">
                            <h5 class="text-xs font-semibold">{{ $issue->book->title }}</h5>
                            <p class="mt-0.5 text-xs text-muted">
                                {{ $issue->student->user->name }}
                            </p>
                            <p class="mt-0.5 text-xs text-muted">
                                Due: {{ \Carbon\Carbon::parse($issue->due_date)->format('m/d/Y') }}
                            </p>
                        </div>

                        <span class="inline-flex items-center justify-center flex-shrink-0 px-1.5 py-0.5 text-xs font-bold text-white bg-red-600 rounded-full whitespace-nowrap">
                            {{ (int) \Carbon\Carbon::parse($issue->due_date)->diffInDays(now()) }} days
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-muted">No overdue books 🎉</p>
                @endforelse
            </div>

            <!-- Recent Activity -->
            <div class="p-3 shadow-sm card">
                <h4 class="mb-2 text-sm font-bold">Recent Activity</h4>

                <div class="space-y-1">
                    @forelse($recentActivities as $activity)
                        <div class="pb-1 border-b last:border-b-0 last:pb-0">
                            <!-- Activity Header: Description + Role Badge -->
                            <div class="flex items-start justify-between gap-1 mb-0.5">
                                <!-- Activity Description -->
                                <p class="flex-1 text-xs font-semibold line-clamp-2">
                                    {{ $activity->description ?? $activity->action }}
                                </p>

                                <!-- Role Badge -->
                                @if($activity->user)
                                <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded-full flex-shrink-0 {{ 
                                    $activity->user->role === 'admin' 
                                        ? 'bg-white text-red-600 dark:bg-red-600 dark:text-white' :
                                    ($activity->user->role === 'staff' 
                                        ? 'bg-white text-blue-600 dark:bg-blue-600 dark:text-white' :
                                        'bg-white text-green-600 dark:bg-green-600 dark:text-white')
                                }}">
                                    {{ ucfirst($activity->user->role) }}
                                </span>
                                @elseif($activity->user_role)
                                <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded-full flex-shrink-0 {{ 
                                    $activity->user_role === 'admin' 
                                        ? 'bg-white text-red-600 dark:bg-red-600 dark:text-white' :
                                    ($activity->user_role === 'staff' 
                                        ? 'bg-white text-blue-600 dark:bg-blue-600 dark:text-white' :
                                        'bg-white text-green-600 dark:bg-green-600 dark:text-white')
                                }}">
                                    {{ ucfirst($activity->user_role) }}
                                </span>
                                @else
                                <span class="inline-flex items-center flex-shrink-0 px-1.5 py-0.5 text-xs font-semibold text-gray-600 bg-white rounded-full dark:bg-gray-600 dark:text-white">
                                    Unknown
                                </span>
                                @endif
                            </div>

                            <!-- User and Timestamp -->
                            <div class="flex items-center gap-1 text-xs text-muted">
                                <span>
                                    @if($activity->user)
                                        {{ $activity->user->name }}
                                    @elseif($activity->user_name)
                                        {{ $activity->user_name }}
                                    @else
                                        Unknown User
                                    @endif
                                </span>
                                <span>•</span>
                                <span>{{ $activity->created_at->format('n/d/Y, g:i A') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-2 text-center">
                            <p class="text-xs text-muted">No activities yet</p>
                        </div>
                    @endforelse
                </div>

                <!-- View All Link -->
                <div class="pt-1 mt-2 border-t">
                    <a href="{{ route('admin.activity-logs.index') }}" class="text-xs font-medium text-primary hover:underline">
                        View All Activities →
                    </a>
                </div>
            </div>

    </div>
@endsection
