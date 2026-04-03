@php
    $expiresIn = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);
@endphp
@extends('emails.layouts.base')

@section('title', 'Reset Your Password')
@section('preheader', 'Use the secure link in this email to reset your library account password.')
@section('eyebrow', 'Password Reset')
@section('headline', 'Reset your library password')
@section('subhead', 'We received a request to reset the password for your library account.')
@section('accent', '#2563eb')
@section('hero', '#1d4ed8')

@section('content')
    <p class="greeting">Hello <strong>{{ $name }}</strong>,</p>

    <p>Select the button below to choose a new password for your account.</p>

    <div class="cta-wrap" style="margin: 26px 0 18px; text-align: center;">
        <a class="cta" href="{{ $url }}" style="display:inline-block;padding:13px 24px;border-radius:999px;background:#2563eb;color:#ffffff !important;text-decoration:none;font-size:14px;font-weight:700;">
            <span style="color:#ffffff !important;">Reset Password</span>
        </a>
    </div>

    <div class="notice">
        <strong>Security note:</strong> This reset link expires in {{ $expiresIn }} minutes. If you did not request a password reset, no changes have been made to your account.
    </div>

    <p class="muted">If the button does not open, copy and paste this link into your browser:</p>
    <p class="muted" style="word-break: break-all; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; background:#f8fafc;">{{ $url }}</p>
@endsection
