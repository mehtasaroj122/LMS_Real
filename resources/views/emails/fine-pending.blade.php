@php
    $resolvedStudentName = $studentName ?? $student?->user?->name ?? $student?->name ?? 'Student';
    $resolvedFineAmount = isset($fineAmount) ? (float) $fineAmount : (float) ($fine?->amount ?? 0);
    $resolvedBookTitle = $bookTitle ?? $fine?->issuedBook?->book?->title ?? 'Library item';
@endphp
@extends('emails.layouts.base')

@section('title', 'Outstanding Fine Reminder')
@section('preheader', 'You have a pending fine recorded against your library account.')
@section('eyebrow', 'Fine Reminder')
@section('headline', 'You still have an outstanding fine')
@section('subhead', 'Please review the pending balance below and settle it as soon as possible.')
@section('accent', '#c2410c')
@section('hero', '#9a3412')

@section('content')
    <p class="greeting">Hello <strong>{{ $resolvedStudentName }}</strong>,</p>

    <table role="presentation" class="panel" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="label">Amount due</td>
            <td class="value">Rs. {{ number_format($resolvedFineAmount, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Related item</td>
            <td class="value">{{ $resolvedBookTitle }}</td>
        </tr>
        <tr>
            <td class="label">Status</td>
            <td class="value">Pending</td>
        </tr>
    </table>

    <div class="notice" style="background:#fff7ed;color:#9a3412;">
        <strong>Reminder:</strong> Unpaid fines may affect borrowing privileges until they are resolved.
    </div>
@endsection
