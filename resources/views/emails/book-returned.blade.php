@php
    $resolvedStudentName = $studentName ?? $student?->user?->name ?? $student?->name ?? 'Reader';
    $resolvedBookTitle = $bookTitle ?? $book?->title ?? $issuedBook?->book?->title ?? 'Book';
    $resolvedCondition = ucfirst($condition ?? $issuedBook?->condition ?? 'good');
    $resolvedFineAmount = isset($fineAmount) ? (float) $fineAmount : (float) ($issuedBook?->fine_amount ?? 0);
@endphp
@extends('emails.layouts.base')

@section('title', 'Book Returned')
@section('preheader', 'Your returned book has been processed by the library.')
@section('eyebrow', 'Return Processed')
@section('headline', 'Your book return has been recorded')
@section('subhead', 'Here is the final status of the returned item.')
@section('accent', '#0f766e')
@section('hero', '#115e59')

@section('content')
    <p class="greeting">Hello <strong>{{ $resolvedStudentName }}</strong>,</p>

    <table role="presentation" class="panel" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="label">Book title</td>
            <td class="value">{{ $resolvedBookTitle }}</td>
        </tr>
        <tr>
            <td class="label">Return condition</td>
            <td class="value">{{ $resolvedCondition }}</td>
        </tr>
        <tr>
            <td class="label">Fine amount</td>
            <td class="value">Rs. {{ number_format($resolvedFineAmount, 2) }}</td>
        </tr>
    </table>

    <div class="notice" style="background:{{ $resolvedFineAmount > 0 ? '#eff6ff' : '#f0fdf4' }};color:{{ $resolvedFineAmount > 0 ? '#1d4ed8' : '#166534' }};">
        <strong>{{ $resolvedFineAmount > 0 ? 'Fine settled:' : 'Completed:' }}</strong>
        {{ $resolvedFineAmount > 0 ? 'A fine was recorded during return processing and has already been marked as paid.' : 'The return was completed with no additional balance due.' }}
    </div>
@endsection
