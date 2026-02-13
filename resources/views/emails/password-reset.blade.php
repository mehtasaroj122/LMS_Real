<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - Library Management System</title>
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
                            style="background:linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);padding:40px 30px;text-align:center;color:#ffffff;">
                            <div style="font-size:48px;margin-bottom:15px;">🔐</div>
                            <h1 style="margin:0 0 5px 0;font-size:28px;font-weight:700;letter-spacing:0.5px;">Library
                                Management System</h1>
                            <p style="margin:0;font-size:14px;opacity:0.9;letter-spacing:0.3px;">Password Reset</p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding:40px 30px;color:#0f172a;line-height:1.6;">

                            <!-- Greeting -->
                            <p style="margin:0 0 20px 0;font-size:16px;font-weight:500;">
                                Hello <strong>{{ $userName }}</strong>,
                            </p>

                            <p style="margin:0 0 20px 0;font-size:14px;color:#475569;line-height:1.8;">
                                Your password has been reset by an administrator. Please find your login credentials
                                below. Use these to log in, then set a new password immediately.
                            </p>

                            <!-- Credentials Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                <tr>
                                    <td
                                        style="background:#f8fafc;border-left:4px solid #3b82f6;padding:20px;border-radius:6px;">

                                        <!-- Email/Username -->
                                        <p
                                            style="margin:0 0 16px 0;font-size:12px;color:#64748b;text-transform:uppercase;font-weight:600;letter-spacing:0.5px;">
                                            📧 Email / Username
                                        </p>
                                        <p
                                            style="margin:0 0 20px 0;font-size:15px;font-weight:700;color:#0f172a;letter-spacing:0.3px;">
                                            {{ $userEmail }}
                                        </p>

                                        <!-- Temporary Password -->
                                        <p
                                            style="margin:0 0 8px 0;font-size:12px;color:#64748b;text-transform:uppercase;font-weight:600;letter-spacing:0.5px;">
                                            🔑 Temporary Password
                                        </p>
                                        <p
                                            style="margin:0;font-size:18px;font-weight:700;color:#3b82f6;letter-spacing:2px;font-family:monospace;">
                                            {{ $tempPassword }}
                                        </p>

                                    </td>
                                </tr>
                            </table>

                            <!-- Important Notice - Yellow -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                <tr>
                                    <td
                                        style="background:#fef3c7;border:1px solid #fcd34d;padding:16px;border-radius:6px;">
                                        <p style="margin:0 0 12px 0;font-size:13px;color:#92400e;font-weight:700;">
                                            ⚠️ IMPORTANT - Read This First
                                        </p>
                                        <ul
                                            style="margin:0;padding-left:20px;font-size:13px;color:#78350f;line-height:1.8;">
                                            <li style="margin-bottom:8px;">
                                                Use the <strong>temporary password</strong> above on your first login
                                            </li>
                                            <li style="margin-bottom:8px;">
                                                You will be <strong>required to set a new password</strong> immediately
                                                after logging in
                                            </li>
                                            <li style="margin-bottom:8px;">
                                                Your new password must be at least 8 characters long and contain:
                                                <ul style="margin:4px 0 0 20px;padding:0;">
                                                    <li>At least one uppercase letter (A-Z)</li>
                                                    <li>At least one lowercase letter (a-z)</li>
                                                    <li>At least one number (0-9)</li>
                                                </ul>
                                            </li>
                                            <li>This temporary password will expire after your first login</li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>

                            <!-- Login Instructions - Light Blue -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                <tr>
                                    <td
                                        style="background:#f0f9ff;border:1px solid #bfdbfe;padding:16px;border-radius:6px;">
                                        <p style="margin:0 0 12px 0;font-size:13px;color:#0369a1;font-weight:700;">
                                            📋 Login Steps:
                                        </p>
                                        <ol
                                            style="margin:0;padding-left:20px;font-size:13px;color:#0369a1;line-height:1.8;">
                                            <li style="margin-bottom:6px;">
                                                Visit the login page: <a href="{{ $loginUrl }}"
                                                    style="color:#3b82f6;text-decoration:underline;">Click here</a>
                                            </li>
                                            <li style="margin-bottom:6px;">
                                                Enter your email: <strong>{{ $userEmail }}</strong>
                                            </li>
                                            <li style="margin-bottom:6px;">
                                                Enter the temporary password provided above
                                            </li>
                                            <li style="margin-bottom:6px;">
                                                Click "Login" to proceed
                                            </li>
                                            <li style="margin-bottom:6px;">
                                                You will be immediately redirected to set a new password
                                            </li>
                                            <li>Your account will be ready to use after creating your new password</li>
                                        </ol>
                                    </td>
                                </tr>
                            </table>

                            <!-- Login Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $loginUrl }}"
                                            style="display:inline-block;background:linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);color:#ffffff;padding:14px 40px;text-decoration:none;border-radius:6px;font-weight:600;font-size:14px;letter-spacing:0.3px;">
                                            Login to Your Account
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Security Note - Light Blue -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                <tr>
                                    <td
                                        style="background:#f0f9ff;border:1px solid #bfdbfe;padding:12px;border-radius:6px;">
                                        <p style="margin:0;font-size:12px;color:#0369a1;line-height:1.6;">
                                            <strong>🔒 Security:</strong> If you did not request this password reset,
                                            please contact your administrator immediately.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Footer -->
                            <p
                                style="margin:0;font-size:13px;color:#475569;border-top:1px solid #e5e7eb;padding-top:20px;">
                                Best regards,<br>
                                <strong>{{ $appName }} Team</strong>
                            </p>

                        </td>
                    </tr>

                    <!-- Footer Bar -->
                    <tr>
                        <td
                            style="background:#f8fafc;padding:20px 30px;text-align:center;border-top:1px solid #e5e7eb;">
                            <p style="margin:0;font-size:12px;color:#64748b;">
                                © {{ date('Y') }} {{ $appName }}. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>
