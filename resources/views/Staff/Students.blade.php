@extends('Staff.layouts.app')

@section('title', 'Students')

@php
    $studentManagementConfig = [
        'role' => 'staff',
        'labels' => [
            'pageTitle' => 'Students',
            'pageDescription' => 'Review and manage student records from the staff portal.',
            'sectionTitle' => 'All Students',
            'searchPlaceholder' => 'Search by name, email, phone, or student ID...',
            'createButton' => 'Add Student',
        ],
        'routes' => [
            'data' => route('staff.students.data'),
            'stats' => route('staff.students.stats'),
            'store' => route('staff.students.store'),
            'show' => url('staff/students/__STUDENT_ID__'),
            'activate' => url('staff/students/__STUDENT_ID__/activate'),
            'deactivate' => url('staff/students/__STUDENT_ID__/deactivate'),
        ],
        'features' => [
            'create' => true,
            'statusFilter' => true,
            'toggleStatus' => true,
        ],
        'departments' => $departments->map(fn ($department) => [
            'id' => $department->id,
            'name' => $department->name,
        ])->values()->all(),
    ];
@endphp

@push('styles')
    @include('shared.student-management.styles')
@endpush

@section('content')
    @include('shared.student-management.page', [
        'studentManagementConfig' => $studentManagementConfig,
        'departments' => $departments,
    ])
@endsection

@push('scripts')
    @include('shared.student-management.scripts', [
        'studentManagementConfig' => $studentManagementConfig,
    ])
@endpush
