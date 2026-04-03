@php
    $resolvedStudentName = $studentName ?? $student?->user?->name ?? $student?->name ?? 'Reader';
    $resolvedBookTitle = $bookTitle ?? $book?->title ?? $issuedBook?->book?->title ?? 'Book';
    $resolvedAuthor = $author ?? $book?->author ?? $issuedBook?->book?->author ?? 'Unknown';
    $resolvedIssueDate = $issueDate ?? optional($issuedBook?->issue_date)->format('Y-m-d') ?? 'N/A';
    $resolvedDueDate = $dueDate ?? optional($issuedBook?->due_date)->format('Y-m-d') ?? 'N/A';
@endphp
@extends('emails.layouts.base')

@section('title', 'Book Issued')
@section('preheader', 'A library book has been issued to your account.')
@section('eyebrow', 'Book Issued')
@section('headline', 'Your book has been issued')
@section('subhead', 'Keep this message for your records and return the book by the listed due date.')
@section('accent', '#4f46e5')
@section('hero', '#3730a3')

@section('content')
    <p class="greeting">Hello <strong>{{ $resolvedStudentName }}</strong>,</p>

    <table role="presentation" class="panel" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="label">Book title</td>
            <td class="value">{{ $resolvedBookTitle }}</td>
        </tr>
        <tr>
            <td class="label">Author</td>
            <td class="value">{{ $resolvedAuthor }}</td>
        </tr>
        <tr>
            <td class="label">Issued on</td>
            <td class="value">{{ $resolvedIssueDate }}</td>
        </tr>
        <tr>
            <td class="label">Due date</td>
            <td class="value">{{ $resolvedDueDate }}</td>
        </tr>
    </table>

    <div class="notice">
        <strong>Reminder:</strong> Return this book on or before the due date to avoid overdue fines and reminder notices.
    </div>
@endsection
