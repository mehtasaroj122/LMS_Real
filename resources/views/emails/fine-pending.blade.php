<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fine Payment Reminder - Library Management System</title>
</head>

<body
    style="margin:0;padding:0;background:linear-gradient(135deg, #f97316 0%, #ea580c 100%);font-family:'Segoe UI', Arial, sans-serif;min-height:100vh;">

    <table width="100%" cellpadding="0" cellspacing="0"
        style="background:linear-gradient(135deg, #f97316 0%, #ea580c 100%);padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:600px;background:#ffffff;border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,0.15);overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td
                            style="background:linear-gradient(135deg, #b45309 0%, #ea580c 100%);padding:40px 30px;text-align:center;color:#ffffff;">
                            <div style="font-size:48px;margin-bottom:15px;">⏰</div>
                            <h1 style="margin:0 0 5px 0;font-size:28px;font-weight:700;letter-spacing:0.5px;">Library
                                Management System</h1>
                            <p style="margin:0;font-size:14px;opacity:0.9;letter-spacing:0.3px;">Payment Reminder</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:40px 30px;color:#333333;font-size:15px;line-height:1.8;">
                            <p style="margin:0 0 20px 0;font-size:18px;color:#b45309;font-weight:600;">Hi
                                {{ $studentName }},</p>

                            <p style="margin:0 0 25px 0;color:#555555;line-height:1.8;">
                                This is a friendly reminder that you have an outstanding fine pending payment in the
                                Library Management System.
                            </p>

                            <!-- Fine Details Card -->
                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="background:#fed7aa;border-left:4px solid #b45309;border-radius:8px;margin:0 0 25px 0;">
                                <tr>
                                    <td style="padding:20px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="font-weight:600;color:#b45309;padding:8px 0;">⚠️ Outstanding
                                                    Fine
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:18px;color:#b45309;font-weight:700;padding:8px 0;">
                                                    ₹{{ number_format($fineAmount, 2) }}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 15px 0;color:#555555;line-height:1.8;">
                                Please make the payment as soon as possible to clear your fine or contact the library
                                administration if you have any questions.
                            </p>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:20px 0;">
                                        <a href="{{ config('app.url') }}/student/dashboard"
                                            style="display:inline-block;background:linear-gradient(135deg, #b45309 0%, #ea580c 100%);color:white;padding:12px 30px;text-decoration:none;border-radius:6px;font-weight:600;font-size:14px;transition:all 0.3s ease;">
                                            View Fine Details
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Footer Message -->
                            <p
                                style="margin:25px 0 0 0;padding-top:20px;border-top:1px solid #e5e7eb;color:#666666;font-size:13px;line-height:1.6;text-align:center;">
                                If you believe this is an error or have already made the payment, please contact the
                                library staff immediately.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="background:#f9fafb;padding:25px 30px;border-top:1px solid #e5e7eb;text-align:center;color:#666666;font-size:12px;">
                            <p style="margin:0 0 10px 0;">
                                Library Management System
                            </p>
                            <p style="margin:0;opacity:0.8;">
                                © {{ date('Y') }} All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
