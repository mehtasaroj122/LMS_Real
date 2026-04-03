@php
    $resolvedStudentName = $studentName ?? $student?->user?->name ?? $student?->name ?? 'Reader';
    $resolvedBookTitle = $bookTitle ?? $book?->title ?? $bookRequest?->book?->title ?? 'Requested Book';
    $resolvedStatusDate = optional($bookRequest?->processed_date ?? $bookRequest?->updated_at ?? now())->format('Y-m-d');
@endphp
@extends('emails.layouts.base')

@section('title', 'Request Approved')
@section('preheader', 'Your library book request has been approved.')
@section('eyebrow', 'Request Update')
@section('headline', 'Your request was approved')
@section('subhead', 'The library has approved your request and the book is ready for the next step.')
@section('accent', '#15803d')
@section('hero', '#166534')

@section('content')
    <p class="greeting">Hello <strong>{{ $resolvedStudentName }}</strong>,</p>

    <table role="presentation" class="panel" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="label">Requested book</td>
            <td class="value">{{ $resolvedBookTitle }}</td>
        </tr>
        <tr>
            <td class="label">Decision</td>
            <td class="value">Approved</td>
        </tr>
        <tr>
            <td class="label">Processed on</td>
            <td class="value">{{ $resolvedStatusDate }}</td>
        </tr>
    </table>

    <div class="notice" style="background:#f0fdf4;color:#166534;">
        <strong>Next step:</strong> Visit the library desk to collect the approved book when convenient.
    </div>
@endsection
