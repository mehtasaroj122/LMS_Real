<!DOCTYPE html>

<html>
<head>
    <meta charset="UTF-8">
    <title>Account Verification</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6fb;font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6fb;padding:40px 0;">
    <tr>
        <td align="center">

```
        <!-- Card -->
        <table width="100%" cellpadding="0" cellspacing="0"
               style="max-width:600px;background:#ffffff;border-radius:10px;
               box-shadow:0 6px 20px rgba(0,0,0,0.08);overflow:hidden;">

            <!-- Header -->
            <tr>
                <td style="background:#512da8;padding:24px 30px;color:#ffffff;">
                    <h1 style="margin:0;font-size:22px;font-weight:600;">
                        Library Management System
                    </h1>
                </td>
            </tr>

            <!-- Body -->
            <tr>
                <td style="padding:30px;color:#333333;font-size:15px;line-height:1.6;">

                    <p style="margin-top:0;">Hi <strong>{{ $name }}</strong>,</p>

                    <p>
                        Thank you for registering with the <strong>Library Management System</strong>.
                        To complete your account verification, please use the verification code below.
                    </p>

                    <!-- OTP Box -->
                    <div style="margin:30px 0;text-align:center;">
                        <div style="
                            display:inline-block;
                            background:#f4f6fb;
                            border:1px dashed #512da8;
                            padding:18px 36px;
                            border-radius:8px;
                            font-size:28px;
                            font-weight:700;
                            letter-spacing:4px;
                            color:#512da8;
                        ">
                            {{ $otp }}
                        </div>
                    </div>

                    <p style="text-align:center;font-size:14px;color:#555;">
                        Enter this code on the verification page to activate your account.
                    </p>

                    <p style="margin-top:25px;color:#b00020;font-size:14px;">
                        ⏱️ <strong>This verification code will expire in 10 minutes.</strong>
                    </p>

                    <p style="font-size:14px;color:#555;">
                        If you did not initiate this request, please ignore this email.
                        No further action is required.
                    </p>

                    <p style="margin-bottom:0;">
                        Kind regards,<br>
                        <strong>Library Management System Team</strong>
                    </p>

                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td style="background:#f4f6fb;padding:16px 30px;text-align:center;
                           font-size:12px;color:#777;">
                    This is an automated message. Please do not reply.
                </td>
            </tr>

        </table>
        <!-- End Card -->

    </td>
</tr>
```

</table>

</body>
</html>
