@php
    $resolvedStudentName = $studentName ?? $student?->user?->name ?? $student?->name ?? 'Student';
    $resolvedFineAmount = isset($fineAmount) ? (float) $fineAmount : (float) ($fine?->amount ?? 0);
    $resolvedReason = $reason ?? $fine?->remarks ?? 'Waived by library staff.';
@endphp
@extends('emails.layouts.base')

@section('title', 'Fine Waived')
@section('preheader', 'A fine on your library account has been waived.')
@section('eyebrow', 'Fine Update')
@section('headline', 'Your fine was waived')
@section('subhead', 'The library has updated your account and removed the fine below.')
@section('accent', '#b45309')
@section('hero', '#92400e')

@section('content')
    <p class="greeting">Hello <strong>{{ $resolvedStudentName }}</strong>,</p>

    <table role="presentation" class="panel" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="label">Waived amount</td>
            <td class="value">Rs. {{ number_format($resolvedFineAmount, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Status</td>
            <td class="value">Waived</td>
        </tr>
        <tr>
            <td class="label">Reason</td>
            <td class="value">{{ $resolvedReason }}</td>
        </tr>
    </table>

    <div class="notice" style="background:#fffbeb;color:#92400e;">
        <strong>Updated:</strong> This fine is no longer outstanding on your account.
    </div>
@endsection
