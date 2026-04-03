@extends('emails.layouts.base')

@section('title', 'Verify Your Email')
@section('preheader', 'Use this one-time verification code to finish creating your library account.')
@section('eyebrow', 'Account Verification')
@section('headline', 'Your verification code is ready')
@section('subhead', 'Enter this one-time password on the verification screen to finish activating your library account.')
@section('accent', '#7c3aed')
@section('hero', '#5b21b6')

@section('content')
    <p class="greeting">Hello <strong>{{ $name }}</strong>,</p>

    <p>Thanks for signing up. Use the one-time password below to verify your email address and complete your registration.</p>

    <div style="margin: 24px 0; text-align: center;">
        <div style="display: inline-block; min-width: 220px; padding: 18px 24px; border-radius: 18px; background: #f5f3ff; border: 1px solid #ddd6fe; color: #5b21b6; font-size: 32px; font-weight: 700; letter-spacing: 10px;">
            {{ $otp }}
        </div>
    </div>

    <div class="notice" style="background:#faf5ff;color:#6b21a8;">
        <strong>Important:</strong> This code expires in 10 minutes. If you did not start this registration, you can safely ignore this email.
    </div>

    <p class="muted">For your security, never share this code with anyone.</p>
@endsection
