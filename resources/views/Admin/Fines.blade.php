@extends('Admin.layouts.app')

@section('title', 'Fines')

@php
    $fineManagementConfig = [
        'role' => 'admin',
        'routes' => [
            'data' => route('admin.fines.data'),
            'markPaid' => route('admin.fines.mark-as-paid', ['fine' => '__FINE_ID__']),
            'waive' => route('admin.fines.waive', ['fine' => '__FINE_ID__']),
            'sendEmail' => route('admin.fines.send-email', ['fine' => '__FINE_ID__']),
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
            'liveLabel' => 'Live updates on',
            'exportButton' => 'Export CSV',
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
    @include('shared.fine-management.styles')
@endpush

@section('content')
    @include('shared.fine-management.page', ['fineManagementConfig' => $fineManagementConfig])
@endsection

@push('scripts')
    @include('shared.fine-management.scripts', ['fineManagementConfig' => $fineManagementConfig])
@endpush
