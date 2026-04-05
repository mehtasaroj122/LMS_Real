@extends('emails.layouts.base')

@section('title', 'Complete your library registration')
@section('preheader', 'Complete registration to activate your library account.')
@section('eyebrow', 'Registration invitation')
@section('headline', 'Complete registration to activate your account')
@section('subhead', 'Your library account has been prepared. Finish registration using the details below and choose your own password.')
@section('accent', '#1d4ed8')
@section('hero', '#0f172a')

@section('content')
    <p class="greeting">Hello <strong>{{ $userName }}</strong>,</p>

    <p>Your {{ strtolower($roleLabel) }} account has been created in the library management system. Complete your registration to activate the account and set your own password.</p>

    <div class="panel">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="label">Role</td>
                <td class="value">{{ $roleLabel }}</td>
            </tr>
            <tr>
                <td class="label">Email</td>
                <td class="value">{{ $userEmail }}</td>
            </tr>
            <tr>
                <td class="label">{{ $identifierLabel }}</td>
                <td class="value">{{ $identifierValue }}</td>
            </tr>
            <tr>
                <td class="label">Phone</td>
                <td class="value">{{ $phone ?: 'Add your phone number during registration' }}</td>
            </tr>
        </table>
    </div>

    <div class="notice">
        <strong>Next step:</strong> open the registration page, confirm your invited details, and create your password. Your account will remain inactive until registration is completed.
    </div>

    <div class="cta-wrap" style="margin: 24px 0 12px; text-align: center;">
        <a class="cta" href="{{ $registerUrl }}" style="background:#1d4ed8;">Complete Registration</a>
    </div>

    <p class="muted" style="text-align: center;">
        If the button does not open, use this link:
        <br>
        <a href="{{ $registerUrl }}" style="color:#1d4ed8; word-break: break-all;">{{ $registerUrl }}</a>
    </p>
@endsection
