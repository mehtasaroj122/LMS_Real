@extends('Staff.layouts.app')

@section('title', 'Book Request')

@php
    $bookRequestManagementConfig = [
        'role' => 'staff',
        'routes' => [
            'data' => route('staff.book-requests.data'),
            'stats' => route('staff.book-requests.stats'),
            'store' => route('staff.book-requests.store'),
            'bulkUpdate' => route('staff.book-requests.bulk-status'),
            'update' => url('staff/book-requests/__REQUEST_ID__'),
        ],
        'features' => [
            'create' => true,
        ],
        'labels' => [
            'pageTitle' => 'Book Requests',
            'pageDescription' => 'Manage student book requests',
            'sectionTitle' => 'All Requests',
            'searchPlaceholder' => 'Search by student, book, or date...',
            'resetButton' => 'Reset',
            'createButton' => 'Create Request',
            'stats' => [
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
            ],
        ],
    ];
@endphp

@push('styles')
    @include('shared.action-feedback.styles')
    @include('shared.book-request-management.styles')
@endpush

@section('content')
    @include('shared.book-request-management.page', [
        'bookRequestManagementConfig' => $bookRequestManagementConfig,
        'students' => $students,
        'books' => $books,
    ])
@endsection

@push('scripts')
    @include('shared.action-feedback.scripts')
    @include('shared.book-request-management.scripts', ['bookRequestManagementConfig' => $bookRequestManagementConfig])
@endpush
