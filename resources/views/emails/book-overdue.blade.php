@extends('emails.layouts.base')

@section('title', 'Overdue Book Reminder')
@section('preheader', 'One of your library books is overdue and needs attention.')
@section('eyebrow', 'Due Reminder')
@section('headline', 'A borrowed book is overdue')
@section('subhead', 'Please return or renew the book as soon as possible to avoid additional fines.')
@section('accent', '#b45309')
@section('hero', '#92400e')

@section('content')
    <p class="greeting">Hello <strong>{{ $studentName }}</strong>,</p>

    <table role="presentation" class="panel" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="label">Book title</td>
            <td class="value">{{ $bookTitle }}</td>
        </tr>
        <tr>
            <td class="label">Due date</td>
            <td class="value">{{ $dueDate }}</td>
        </tr>
        <tr>
            <td class="label">Days overdue</td>
            <td class="value">{{ $daysOverdue }}</td>
        </tr>
        @if ($fineAmount > 0)
            <tr>
                <td class="label">Current fine</td>
                <td class="value">Rs. {{ number_format($fineAmount, 2) }}</td>
            </tr>
        @endif
    </table>

    <div class="notice" style="background:#fff7ed;color:#9a3412;">
        <strong>Action needed:</strong> Return this book promptly to stop the overdue balance from increasing.
    </div>
@endsection
