@php
    $resolvedStudentName = $studentName ?? $student?->user?->name ?? $student?->name ?? 'Reader';
    $resolvedBookTitle = $bookTitle ?? $book?->title ?? $bookRequest?->book?->title ?? 'Requested Book';
    $resolvedStatusDate = optional($bookRequest?->processed_date ?? $bookRequest?->updated_at ?? now())->format('Y-m-d');
@endphp
@extends('emails.layouts.base')

@section('title', 'Request Update')
@section('preheader', 'Your library book request could not be approved at this time.')
@section('eyebrow', 'Request Update')
@section('headline', 'Your request was not approved')
@section('subhead', 'The library reviewed your request and could not approve it right now.')
@section('accent', '#b91c1c')
@section('hero', '#7f1d1d')

@section('content')
    <p class="greeting">Hello <strong>{{ $resolvedStudentName }}</strong>,</p>

    <table role="presentation" class="panel" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="label">Requested book</td>
            <td class="value">{{ $resolvedBookTitle }}</td>
        </tr>
        <tr>
            <td class="label">Decision</td>
            <td class="value">Rejected</td>
        </tr>
        <tr>
            <td class="label">Processed on</td>
            <td class="value">{{ $resolvedStatusDate }}</td>
        </tr>
    </table>

    <div class="notice" style="background:#fef2f2;color:#991b1b;">
        <strong>What you can do:</strong> Check availability again later or submit a new request for a different book.
    </div>
@endsection
