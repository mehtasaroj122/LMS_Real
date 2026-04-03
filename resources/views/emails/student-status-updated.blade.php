@php
    $resolvedStudentName = $studentName ?? 'Student';
    $resolvedStatus = strtolower((string) ($status ?? 'inactive'));
    $isActive = $resolvedStatus === 'active';
    $resolvedAccountRole = strtolower((string) ($accountRole ?? 'student'));
    $isStaff = $resolvedAccountRole === 'staff';
    $resolvedChangedByName = $changedByName ?? 'Library team';
    $resolvedChangedByRole = $changedByRole ?? 'Library team';
    $resolvedUpdatedAt = $updatedAt ?? now()->format('Y-m-d H:i');
@endphp
@extends('emails.layouts.base')

@section('title', $isActive ? 'Account Activated' : 'Account Deactivated')
@section('preheader', $isActive ? 'Your library account has been activated.' : 'Your library account has been deactivated.')
@section('eyebrow', 'Account Status Update')
@section('headline', $isActive ? 'Your account is active again' : 'Your account has been deactivated')
@section('subhead', $isActive
    ? ($isStaff
        ? 'You can continue using the staff portal and library operations according to your assigned permissions.'
        : 'You can continue using library services according to your current borrowing privileges.')
    : ($isStaff
        ? 'Access to the staff portal and library operations may be restricted until the account is reactivated.'
        : 'Some library services may be restricted until the account is reactivated.'))
@section('accent', $isActive ? '#16a34a' : '#dc2626')
@section('hero', $isActive ? '#166534' : '#991b1b')

@section('content')
    <p class="greeting">Hello <strong>{{ $resolvedStudentName }}</strong>,</p>

    <p>Your library account status was updated by {{ $resolvedChangedByRole }} <strong>{{ $resolvedChangedByName }}</strong>.</p>

    <table role="presentation" class="panel" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="label">Current status</td>
            <td class="value">{{ $isActive ? 'Active' : 'Inactive' }}</td>
        </tr>
        <tr>
            <td class="label">Updated by</td>
            <td class="value">{{ $resolvedChangedByName }} ({{ $resolvedChangedByRole }})</td>
        </tr>
        <tr>
            <td class="label">Updated on</td>
            <td class="value">{{ $resolvedUpdatedAt }}</td>
        </tr>
    </table>

    @if ($isActive)
        <div class="notice" style="background:#f0fdf4;color:#166534;">
            <strong>Good news:</strong>
            {{ $isStaff
                ? 'Your account is active again. You can sign in to the staff panel and continue library operations according to your assigned permissions.'
                : 'Your account is active again. You can sign in, borrow books, and submit requests according to your library privileges.' }}
        </div>
    @else
        <div class="notice" style="background:#fef2f2;color:#991b1b;">
            <strong>Warning:</strong>
            {{ $isStaff
                ? 'Access to the staff dashboard, circulation tools, and other library operations may be restricted while your account remains inactive. If this change seems incorrect, contact the library administrator immediately.'
                : 'Borrowing, renewals, and book requests may be restricted while your account remains inactive. If this change seems incorrect, contact the library immediately.' }}
        </div>
    @endif
@endsection
