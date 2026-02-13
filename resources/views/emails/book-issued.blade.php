<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Issued - Library Management System</title>
</head>

<body
    style="margin:0;padding:0;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);font-family:'Segoe UI', Arial, sans-serif;min-height:100vh;">

    <table width="100%" cellpadding="0" cellspacing="0"
        style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:600px;background:#ffffff;border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,0.15);overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td
                            style="background:linear-gradient(135deg, #512da8 0%, #6a1b9a 100%);padding:40px 30px;text-align:center;color:#ffffff;">
                            <div style="font-size:48px;margin-bottom:15px;">📚</div>
                            <h1 style="margin:0 0 5px 0;font-size:28px;font-weight:700;letter-spacing:0.5px;">Library
                                Management System</h1>
                            <p style="margin:0;font-size:14px;opacity:0.9;letter-spacing:0.3px;">Book Issued
                                Successfully</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:40px 30px;color:#333333;font-size:15px;line-height:1.8;">
                            <p style="margin:0 0 20px 0;font-size:18px;color:#512da8;font-weight:600;">Hi
                                {{ $studentName }},</p>

                            <p style="margin:0 0 25px 0;color:#555555;line-height:1.8;">
                                Great news! Your requested book has been successfully issued to you. Please see the
                                details below:
                            </p>

                            <!-- Book Details Card -->
                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="background:#f8f9ff;border-left:4px solid #512da8;border-radius:8px;margin:0 0 25px 0;">
                                <tr>
                                    <td style="padding:20px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="font-weight:600;color:#512da8;padding:8px 0;">📖 Book Title
                                                </td>
                                                <td style="text-align:right;color:#333;padding:8px 0;">
                                                    {{ $bookTitle }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="border-top:1px solid #e0e0e0;font-weight:600;color:#512da8;padding:8px 0;">
                                                </td>
                                                <td
                                                    style="border-top:1px solid #e0e0e0;text-align:right;color:#333;padding:8px 0;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight:600;color:#512da8;padding:8px 0;">✍️ Author</td>
                                                <td style="text-align:right;color:#333;padding:8px 0;">
                                                    {{ $author ?? 'Unknown' }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="border-top:1px solid #e0e0e0;font-weight:600;color:#512da8;padding:8px 0;">
                                                </td>
                                                <td
                                                    style="border-top:1px solid #e0e0e0;text-align:right;color:#333;padding:8px 0;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight:600;color:#512da8;padding:8px 0;">📅 Issue Date
                                                </td>
                                                <td style="text-align:right;color:#333;padding:8px 0;">
                                                    {{ $issueDate }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="border-top:1px solid #e0e0e0;font-weight:600;color:#512da8;padding:8px 0;">
                                                </td>
                                                <td
                                                    style="border-top:1px solid #e0e0e0;text-align:right;color:#333;padding:8px 0;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight:600;color:#e53935;padding:8px 0;">📍 Due Date
                                                </td>
                                                <td
                                                    style="text-align:right;color:#e53935;padding:8px 0;font-weight:600;">
                                                    {{ $dueDate }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Important Notice -->
                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="background:#fff3cd;border-left:4px solid #ffc107;border-radius:6px;margin:20px 0;">
                                <tr>
                                    <td style="padding:12px 15px;font-size:13px;color:#856404;">
                                        ⏰ <strong>Important:</strong> Please ensure to return this book on or before the
                                        due date to avoid fines.
                                    </td>
                                </tr>
                            </table>

                            <!-- Action Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:35px 0;">
                                <tr>
                                    <td align="center">
                                        <table cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td
                                                    style="background:linear-gradient(135deg, #512da8 0%, #6a1b9a 100%);padding:14px 40px;border-radius:8px;box-shadow:0 4px 15px rgba(81, 45, 168, 0.3);">
                                                    <a href="{{ config('app.url') }}/student/dashboard"
                                                        style="display:inline-block;color:#ffffff;text-decoration:none;font-size:16px;font-weight:700;letter-spacing:0.3px;">
                                                        View My Books
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:13px;color:#666666;line-height:1.8;">
                                Best regards,<br>
                                <strong>Library Management System Team</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="height:1px;background:#e0e0e0;"></td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f8f9fa;padding:20px 30px;text-align:center;">
                            <p style="margin:0 0 8px 0;font-size:12px;color:#888888;">
                                <strong>Library Management System</strong>
                            </p>
                            <p style="margin:0 0 8px 0;font-size:11px;color:#999999;">
                                Smart Library Management
                            </p>
                            <p
                                style="margin:0;padding-top:8px;border-top:1px solid #e0e0e0;font-size:11px;color:#aaaaaa;">
                                © {{ date('Y') }} Library Management System. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
