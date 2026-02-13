<!DOCTYPE html>

<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6fb;font-family:Arial, Helvetica, sans-serif;">

```
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6fb;padding:40px 0;">
    <tr>
        <td align="center">

            <!-- Card -->
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,0.08);overflow:hidden;">
                
                <!-- Header -->
                <tr>
                    <td style="background:#512da8;padding:24px 30px;color:#ffffff;">
                        <h1 style="margin:0;font-size:22px;font-weight:600;">Library Management System</h1>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:30px;color:#333333;font-size:15px;line-height:1.6;">
                        <p style="margin-top:0;">Hi <strong>{{ $name }}</strong>,</p>

                        <p>
                            We received a request to reset the password for your Library Management System account.
                        </p>

                        <p style="margin-bottom:30px;">
                            Click the button below to securely reset your password:
                        </p>

                        <!-- Button -->
                        <p style="text-align:center;">
                            <a href="{{ $url }}" 
                               style="display:inline-block;background:#512da8;color:#ffffff;text-decoration:none;
                               padding:14px 36px;border-radius:6px;font-size:15px;font-weight:600;">
                                Reset Password
                            </a>
                        </p>

                        <p style="margin-top:30px;font-size:14px;color:#555;">
                            If the button doesn’t work, copy and paste this link into your browser:
                        </p>

                        <p style="word-break:break-all;background:#f1f3f9;padding:12px;border-radius:6px;font-size:13px;color:#333;">
                            {{ $url }}
                        </p>

                        <p style="margin-top:25px;color:#b00020;font-size:14px;">
                            ⏱️ <strong>This link will expire in 10 minutes.</strong>
                        </p>

                        <p style="font-size:14px;color:#555;">
                            If you did not request a password reset, please ignore this email. Your account remains secure.
                        </p>

                        <p style="margin-bottom:0;">
                            Regards,<br>
                            <strong>Library Management System Team</strong>
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#f4f6fb;padding:16px 30px;text-align:center;font-size:12px;color:#777;">
                        This is an automated message. Please do not reply.
                    </td>
                </tr>

            </table>
            <!-- End Card -->

        </td>
    </tr>
</table>
```

</body>
</html>
