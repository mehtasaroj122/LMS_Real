@extends('emails.layouts.base')

@section('title', 'Account Unlocked')
@section('preheader', 'Your account lock has been cleared and you can sign in again.')
@section('eyebrow', 'Security Update')
@section('headline', 'Your account is unlocked')
@section('subhead', 'You can now sign in to your library account again.')
@section('accent', '#0f766e')
@section('hero', '#0f766e')

@section('content')
    <p class="greeting">Hello <strong>{{ $userName }}</strong>,</p>

    <p>Your account lock has been cleared successfully.</p>

    <table role="presentation" class="panel" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="label">Related IP</td>
            <td class="value">{{ $sourceIp }}</td>
        </tr>
    </table>

    <div class="notice">
        <strong>Security reminder:</strong> If you did not request this unlock or still see suspicious activity, change your password immediately and contact support.
    </div>

    <div class="cta-wrap" style="margin: 24px 0 8px; text-align: center;">
        <a class="cta" href="{{ $loginUrl }}" style="background:#0f766e;">Sign In</a>
    </div>
@endsection
