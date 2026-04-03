@extends('emails.layouts.base')

@section('title', 'Temporary Password Issued')
@section('preheader', 'An administrator reset your library password and issued a temporary sign-in password.')
@section('eyebrow', 'Administrator Reset')
@section('headline', 'Your temporary password is ready')
@section('subhead', 'An administrator reset your account credentials. Sign in with the temporary password below and change it immediately.')
@section('accent', '#0f766e')
@section('hero', '#0f766e')

@section('content')
    <p class="greeting">Hello <strong>{{ $userName }}</strong>,</p>

    <table role="presentation" class="panel" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="label">Email address</td>
            <td class="value">{{ $userEmail }}</td>
        </tr>
        <tr>
            <td class="label">Temporary password</td>
            <td class="value" style="font-family: Consolas, Monaco, monospace; letter-spacing: 1px;">{{ $tempPassword }}</td>
        </tr>
    </table>

    <div class="notice" style="background:#f0fdf4;color:#166534;">
        <strong>Next step:</strong> Sign in with this temporary password, then create a new password right away. Your account is configured to require a password change after login.
    </div>

    <div class="cta-wrap" style="margin: 24px 0 8px; text-align: center;">
        <a class="cta" href="{{ $loginUrl }}" style="background:#0f766e;">Go to Login</a>
    </div>

    <p class="muted">If you were not expecting this reset, contact your administrator immediately.</p>
@endsection
