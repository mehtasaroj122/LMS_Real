@php
    $branding = \App\Support\LibraryBranding::resolve();
    $appName = $branding['name'] ?? config('app.name', 'Library Management System');
    $accent = trim($__env->yieldContent('accent', '#2563eb'));
    $hero = trim($__env->yieldContent('hero', $accent));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>@yield('title', $appName)</title>
    <style>
        body { margin: 0; padding: 0; background: #edf2f7; font-family: Arial, Helvetica, sans-serif; color: #0f172a; }
        table { border-collapse: collapse; }
        .shell { width: 100%; background: #edf2f7; padding: 24px 12px; }
        .card { width: 100%; max-width: 640px; background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 14px 38px rgba(15, 23, 42, 0.12); }
        .hero { padding: 28px 28px 24px; color: #ffffff; }
        .eyebrow { margin: 0 0 8px; font-size: 12px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; opacity: 0.88; }
        .headline { margin: 0; font-size: 28px; line-height: 1.2; font-weight: 700; }
        .subhead { margin: 10px 0 0; font-size: 15px; line-height: 1.6; opacity: 0.92; }
        .content { padding: 28px; }
        .content p { margin: 0 0 16px; font-size: 15px; line-height: 1.7; color: #334155; }
        .greeting { margin-bottom: 18px; font-size: 16px; color: #0f172a; }
        .panel { margin: 20px 0; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; background: #f8fafc; }
        .panel td { padding: 14px 18px; font-size: 14px; }
        .label { color: #475569; font-weight: 700; width: 42%; }
        .value { color: #0f172a; text-align: right; }
        .notice { margin: 20px 0; padding: 16px 18px; border-radius: 14px; background: #eff6ff; color: #1e3a8a; font-size: 14px; line-height: 1.6; }
        .notice strong { color: #0f172a; }
        .cta-wrap { padding-top: 8px; }
        .cta { display: inline-block; padding: 13px 24px; border-radius: 999px; color: #ffffff !important; text-decoration: none; font-size: 14px; font-weight: 700; }
        .footer { padding: 0 28px 24px; color: #64748b; }
        .footer p { margin: 0; font-size: 12px; line-height: 1.7; color: #64748b; }
        .muted { color: #64748b; font-size: 13px; }
        @media only screen and (max-width: 620px) {
            .hero { padding: 24px 20px 20px; }
            .content { padding: 22px 20px; }
            .footer { padding: 0 20px 20px; }
            .headline { font-size: 24px; }
            .panel td { display: block; width: auto; text-align: left; padding: 12px 16px; }
            .value { padding-top: 0; }
        }
    </style>
</head>
<body>
    <span style="display:none!important;visibility:hidden;opacity:0;color:transparent;height:0;width:0;overflow:hidden;">
        @yield('preheader', $appName)
    </span>

    <table role="presentation" class="shell" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table role="presentation" class="card" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="hero" style="background: linear-gradient(135deg, {{ $hero }} 0%, {{ $accent }} 100%);">
                            <p class="eyebrow">@yield('eyebrow', $appName)</p>
                            <h1 class="headline">@yield('headline', $appName)</h1>
                            <p class="subhead">@yield('subhead')</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="content">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td class="footer">
                            <p>{{ $appName }}</p>
                            <p class="muted">This is an automated email from your library system. Please do not share security links or one-time codes.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
