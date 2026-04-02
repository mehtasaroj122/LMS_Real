@extends('Staff.layouts.app')

@section('title', 'Fines')

@php
    $fineManagementConfig = [
        'role' => 'staff',
        'routes' => [
            'data' => route('staff.fines.data'),
            'exportData' => route('staff.fines.export-data'),
            'bulkUpdate' => route('staff.fines.bulk-status'),
            'bulkEmail' => route('staff.fines.bulk-email'),
            'markPaid' => route('staff.fines.mark-as-paid', ['fine' => '__FINE_ID__']),
            'waive' => route('staff.fines.waive', ['fine' => '__FINE_ID__']),
            'sendEmail' => route('staff.fines.send-email', ['fine' => '__FINE_ID__']),
        ],
        'features' => [
            'export' => true,
            'markPaid' => true,
            'waive' => true,
            'sendEmail' => true,
        ],
        'labels' => [
            'pageTitle' => 'Fines Records',
            'pageDescription' => 'Manage and update fines for overdue books',
            'exportButton' => 'Export Excel',
            'searchPlaceholder' => 'Search by user, book title, amount, or reason...',
            'stats' => [
                'totalTitle' => 'Total Fines',
                'collectedTitle' => 'Collected',
                'pendingTitle' => 'Pending',
                'waivedTitle' => 'Waived',
            ],
        ],
    ];
@endphp

@push('styles')
    @include('shared.action-feedback.styles')
    @include('shared.report-export.styles')
    @include('shared.fine-management.styles')
@endpush

@section('content')
    @include('shared.fine-management.page', ['fineManagementConfig' => $fineManagementConfig])
@endsection

@push('scripts')
    @include('shared.action-feedback.scripts')
    @include('shared.report-export.scripts')
    @include('shared.fine-management.scripts', ['fineManagementConfig' => $fineManagementConfig])
@endpush
