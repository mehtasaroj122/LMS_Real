@extends('emails.layouts.base')

@section('title', 'Account Temporarily Locked')
@section('preheader', 'Your account was temporarily locked after repeated failed sign-in attempts.')
@section('eyebrow', 'Security Alert')
@section('headline', 'We temporarily locked your account')
@section('subhead', 'This is a protective action triggered by repeated failed sign-in attempts.')
@section('accent', '#b91c1c')
@section('hero', '#7f1d1d')

@section('content')
    <p class="greeting">Hello <strong>{{ $userName }}</strong>,</p>

    <table role="presentation" class="panel" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="label">Source IP</td>
            <td class="value">{{ $sourceIp }}</td>
        </tr>
        <tr>
            <td class="label">Estimated unlock window</td>
            <td class="value">{{ $minutes }} {{ $minuteLabel }}</td>
        </tr>
        <tr>
            <td class="label">Unlock link expires</td>
            <td class="value">{{ $unlockExpiresAt }}</td>
        </tr>
    </table>

    <div class="notice" style="background:#fef2f2;color:#991b1b;">
        <strong>Important:</strong> If this activity was not yours, reset your password as soon as possible and review recent sign-in attempts.
    </div>

    <div class="cta-wrap" style="margin: 24px 0 8px; text-align: center;">
        <a class="cta" href="{{ $unlockUrl }}" style="background:#b91c1c;">Unlock Account</a>
    </div>
@endsection
