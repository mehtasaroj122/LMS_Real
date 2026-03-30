@extends('Staff.layouts.app')

@section('title', 'Student Details')

@php
    $studentProfileConfig = [
        'role' => 'staff',
        'routes' => [
            'back' => route('staff.students.index'),
            'activate' => url('staff/students/__STUDENT_ID__/activate'),
            'deactivate' => url('staff/students/__STUDENT_ID__/deactivate'),
            'fineIndex' => route('staff.fines.index'),
            'markPaid' => route('staff.fines.mark-as-paid', ['fine' => '__FINE_ID__']),
            'waive' => route('staff.fines.waive', ['fine' => '__FINE_ID__']),
            'sendEmail' => route('staff.fines.send-email', ['fine' => '__FINE_ID__']),
        ],
        'features' => [
            'toggleStatus' => true,
            'fineManagementLink' => true,
            'fineActions' => true,
            'markPaid' => true,
            'waive' => true,
            'sendEmail' => true,
        ],
    ];
@endphp

@push('styles')
    @include('shared.student-profile.styles')
@endpush

@section('content')
    @include('shared.student-profile.page', [
        'student' => $student,
        'studentSummary' => $studentSummary,
        'studentBooks' => $studentBooks,
        'studentFines' => $studentFines,
        'studentRequests' => $studentRequests,
        'studentActivities' => $studentActivities,
        'studentPrivileges' => $studentPrivileges,
        'studentProfileConfig' => $studentProfileConfig,
    ])
@endsection

@push('scripts')
    @include('shared.student-profile.scripts', [
        'student' => $student,
        'studentSummary' => $studentSummary,
        'studentBooks' => $studentBooks,
        'studentFines' => $studentFines,
        'studentRequests' => $studentRequests,
        'studentActivities' => $studentActivities,
        'studentPrivileges' => $studentPrivileges,
        'studentProfileConfig' => $studentProfileConfig,
    ])
@endpush
