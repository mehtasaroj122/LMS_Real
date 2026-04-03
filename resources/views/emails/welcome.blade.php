@extends('emails.layouts.base')

@section('title', 'Welcome to the Library')
@section('preheader', 'Your library account is active and ready to use.')
@section('eyebrow', 'Welcome')
@section('headline', 'Your library account is now active')
@section('subhead', 'Your email has been verified successfully and your account is ready.')
@section('accent', '#0f766e')
@section('hero', '#1d4ed8')

@section('content')
    <p class="greeting">Hello <strong>{{ $userName }}</strong>,</p>

    <p>Welcome to the library management system. You can now sign in, search the catalog, request books, and keep track of your issued books and fines in one place.</p>

    <div class="notice">
        <strong>Tip:</strong> Keep your profile information up to date so you never miss approval updates, due reminders, or fine notifications.
    </div>

    <div class="cta-wrap" style="margin: 24px 0 8px; text-align: center;">
        <a class="cta" href="{{ $loginUrl }}" style="background:#0f766e;">Open My Account</a>
    </div>
@endsection
