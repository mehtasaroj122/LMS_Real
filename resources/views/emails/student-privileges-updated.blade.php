@php
    $resolvedStudentName = $studentName ?? 'Student';
    $resolvedChangedByName = $changedByName ?? 'Library team';
    $resolvedChangedByRole = $changedByRole ?? 'Library team';
    $resolvedUpdatedAt = $updatedAt ?? now()->format('Y-m-d H:i');
    $resolvedSettings = $settings ?? [];
    $resolvedChangeSummary = trim((string) ($changeSummary ?? ''));
@endphp
@extends('emails.layouts.base')

@section('title', $resetToDefaults ? 'Library Privileges Reset' : 'Library Privileges Updated')
@section('preheader', $resetToDefaults
    ? 'Your library privileges were reset to the default policy.'
    : 'Your library privileges were updated.')
@section('eyebrow', 'Privilege Update')
@section('headline', $resetToDefaults ? 'Your library privileges were reset' : 'Your library privileges were updated')
@section('subhead', $resetToDefaults
    ? 'Your account now follows the standard library borrowing policy.'
    : 'Please review the updated borrowing settings on your account.')
@section('accent', '#0f766e')
@section('hero', '#115e59')

@section('content')
    <p class="greeting">Hello <strong>{{ $resolvedStudentName }}</strong>,</p>

    <p>Your library privilege settings were {{ $resetToDefaults ? 'reset' : 'updated' }} by {{ $resolvedChangedByRole }} <strong>{{ $resolvedChangedByName }}</strong>.</p>

    @if ($resolvedChangeSummary !== '')
        <div class="notice" style="background:#ecfeff;color:#155e75;">
            <strong>Summary:</strong> {{ $resolvedChangeSummary }}
        </div>
    @endif

    <table role="presentation" class="panel" width="100%" cellpadding="0" cellspacing="0">
        @foreach ($resolvedSettings as $setting)
            <tr>
                <td class="label">{{ $setting['label'] }}</td>
                <td class="value">{{ $setting['value'] }}</td>
            </tr>
        @endforeach
        <tr>
            <td class="label">Updated on</td>
            <td class="value">{{ $resolvedUpdatedAt }}</td>
        </tr>
    </table>

    <div class="notice" style="background:#f0fdfa;color:#115e59;">
        <strong>Important:</strong> These settings apply the next time you issue, renew, or return library items. Contact the library if you need clarification.
    </div>
@endsection
