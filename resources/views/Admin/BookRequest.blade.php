@extends('Admin.layouts.app')

@section('title', 'Book Request')

@php
    $bookRequestManagementConfig = [
        'role' => 'admin',
        'routes' => [
            'data' => route('admin.book-requests.data'),
            'stats' => route('admin.book-requests.stats'),
            'store' => route('admin.book-requests.store'),
            'update' => url('admin/book-requests/__REQUEST_ID__'),
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
    @include('shared.book-request-management.scripts', ['bookRequestManagementConfig' => $bookRequestManagementConfig])
@endpush
