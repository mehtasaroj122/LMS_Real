@php
    $resolvedStudentName = $studentName ?? $student?->user?->name ?? $student?->name ?? 'Student';
    $resolvedFineAmount = isset($fineAmount) ? (float) $fineAmount : (float) ($fine?->amount ?? 0);
@endphp
@extends('emails.layouts.base')

@section('title', 'Fine Payment Received')
@section('preheader', 'Your library fine payment has been recorded successfully.')
@section('eyebrow', 'Payment Confirmation')
@section('headline', 'Your fine payment is confirmed')
@section('subhead', 'We recorded your payment and updated your account balance.')
@section('accent', '#15803d')
@section('hero', '#166534')

@section('content')
    <p class="greeting">Hello <strong>{{ $resolvedStudentName }}</strong>,</p>

    <table role="presentation" class="panel" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="label">Amount received</td>
            <td class="value">Rs. {{ number_format($resolvedFineAmount, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Status</td>
            <td class="value">Paid</td>
        </tr>
        <tr>
            <td class="label">Processed on</td>
            <td class="value">{{ optional($fine?->paid_on ?? now())->format('Y-m-d') }}</td>
        </tr>
    </table>

    <div class="notice" style="background:#f0fdf4;color:#166534;">
        <strong>Completed:</strong> Your payment has been applied to your account. No further action is needed for this fine.
    </div>
@endsection
