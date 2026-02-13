<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 420px;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: white;
            font-size: 28px;
        }

        h1 {
            margin: 0 0 8px 0;
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
        }

        .subtitle {
            margin: 0;
            font-size: 14px;
            color: #64748b;
        }

        .notice {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 24px;
            font-size: 12px;
            color: #78350f;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #0f172a;
        }

        .required {
            color: #ef4444;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        input[disabled] {
            background-color: #f8fafc;
            color: #64748b;
            cursor: not-allowed;
        }

        .password-requirements {
            background: #f0f9ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 24px;
            font-size: 12px;
        }

        .password-requirements p {
            margin: 0 0 8px 0;
            font-weight: 600;
            color: #0369a1;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 16px;
            color: #0369a1;
            line-height: 1.6;
        }

        .password-requirements li {
            margin-bottom: 4px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .loading-state {
            display: none;
            text-align: center;
            padding: 30px;
        }

        .spinner {
            display: inline-block;
            margin-bottom: 16px;
        }

        .spinner i {
            font-size: 32px;
            color: #667eea;
        }

        .loading-text {
            margin: 0;
            font-size: 14px;
            color: #64748b;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 8px;
            font-weight: 600;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.3s ease;
        }

        .notification.success {
            background-color: #10b981;
            color: white;
        }

        .notification.error {
            background-color: #ef4444;
            color: white;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media (max-width: 480px) {
            .card {
                padding: 30px 20px;
            }

            h1 {
                font-size: 20px;
            }

            button {
                padding: 10px;
                font-size: 13px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="header">
                <div class="icon">🔒</div>
                <h1>Change Password</h1>
                <p class="subtitle">Set a new secure password for your account</p>
            </div>

            <!-- Important Notice -->
            <div class="notice">
                <strong>⚠️ Important:</strong> You are required to set a new password before accessing your account.
                Your temporary password is no longer valid after this change.
            </div>

            <!-- Form -->
            <form id="changePasswordForm" style="display: none;">
                @csrf

                <!-- Current Email (Read-only) -->
                <div class="form-group">
                    <label>Your Email</label>
                    <input type="email" readonly value="{{ Auth::user()->email }}" disabled>
                </div>

                <!-- New Password -->
                <div class="form-group">
                    <label>New Password <span class="required">*</span></label>
                    <input type="password" name="password" id="password" placeholder="Enter new password" required>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label>Confirm Password <span class="required">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        placeholder="Confirm new password" required>
                </div>

                <!-- Password Requirements -->
                <div class="password-requirements">
                    <p>Password Requirements:</p>
                    <ul>
                        <li>At least 8 characters long</li>
                        <li>Contains at least one uppercase letter (A-Z)</li>
                        <li>Contains at least one lowercase letter (a-z)</li>
                        <li>Contains at least one number (0-9)</li>
                    </ul>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submitBtn">
                    Update Password
                </button>
            </form>

            <!-- Loading State -->
            <div id="loadingState" class="loading-state">
                <div class="spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <p class="loading-text">Loading form...</p>
            </div>
        </div>

        <!-- Footer -->
        <p class="footer">
            Need help? Contact your administrator
        </p>
    </div>

    <script>
        // Show form once DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('changePasswordForm').style.display = 'block';
            document.getElementById('loadingState').style.display = 'none';

            const form = document.getElementById('changePasswordForm');
            const submitBtn = document.getElementById('submitBtn');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const password = document.getElementById('password').value;
                const passwordConfirmation = document.getElementById('password_confirmation').value;

                // Client-side validation
                if (password.length < 8) {
                    showError('Password must be at least 8 characters long');
                    return;
                }

                if (password !== passwordConfirmation) {
                    showError('Passwords do not match');
                    return;
                }

                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

                try {
                    const response = await fetch('{{ route('password.update') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')
                                .value,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            password: password,
                            password_confirmation: passwordConfirmation
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Show success message
                        showSuccess('Password changed successfully! Redirecting...');

                        // Redirect after 2 seconds
                        setTimeout(() => {
                            window.location.href = data.redirect || '/';
                        }, 2000);
                    } else {
                        showError(data.message || 'Error updating password');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Update Password';
                    }
                } catch (error) {
                    showError('An error occurred. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Update Password';
                }
            });

            function showError(message) {
                const notification = document.createElement('div');
                notification.className = 'notification error';
                notification.innerHTML = `
                    <i class="fas fa-exclamation-circle"></i>
                    ${message}
                `;
                document.body.appendChild(notification);

                setTimeout(() => notification.remove(), 5000);
            }

            function showSuccess(message) {
                const notification = document.createElement('div');
                notification.className = 'notification success';
                notification.innerHTML = `
                    <i class="fas fa-check-circle"></i>
                    ${message}
                `;
                document.body.appendChild(notification);
            }
        });
    </script>
</body>

</html>
