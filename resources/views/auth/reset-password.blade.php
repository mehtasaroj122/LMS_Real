<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reset Password - Library Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-container {
            display: flex;
            max-width: 1000px;
            width: 100%;
            background: linear-gradient(135deg, #5c6bc0 0%, #7e57c2 100%);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Left Side - Form */
        .reset-form-container {
            flex: 1;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: linear-gradient(135deg, #512da8 0%, #6a1b9a 100%);
        }

        .reset-form-header {
            margin-bottom: 30px;
        }

        .reset-form-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #e1bee7;
            margin-bottom: 8px;
        }

        .reset-form-header p {
            font-size: 13px;
            color: #ce93d8;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #e1bee7;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid rgba(225, 190, 231, 0.3);
            border-radius: 8px;
            font-size: 13px;
            transition: all 0.3s ease;
            font-family: inherit;
            background: rgba(206, 147, 216, 0.15);
            color: #f3e5f5;
        }

        .form-group input:focus {
            outline: none;
            border-color: #e1bee7;
            background: rgba(206, 147, 216, 0.25);
            box-shadow: 0 0 0 3px rgba(225, 190, 231, 0.2);
        }

        .form-group input::placeholder {
            color: #ce93d8;
        }

        /* Password Input Wrapper */
        .password-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-input-wrapper input {
            padding-right: 40px;
        }

        /* Password Toggle Button */
        .password-toggle {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ce93d8;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .password-toggle:hover {
            color: #e1bee7;
            transform: scale(1.1);
        }

        .password-toggle:active {
            transform: scale(0.95);
        }

        .password-toggle svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .password-toggle .hidden {
            display: none;
        }

        .reset-btn {
            width: 100%;
            padding: 11px 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .reset-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .reset-btn:active {
            transform: translateY(0);
        }

        .reset-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .reset-btn svg {
            width: 16px;
            height: 16px;
        }

        /* Error & Success Messages */
        .error-message {
            background: rgba(220, 38, 38, 0.1);
            color: #fca5a5;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(220, 38, 38, 0.3);
        }

        .error-message svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        .success-message {
            background: rgba(16, 185, 129, 0.1);
            color: #a7f3d0;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        /* Right Side - Branding */
        .reset-branding {
            display: none;
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 50px 40px;
            position: relative;
            overflow: hidden;
        }

        .reset-branding.active {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .branding-content {
            z-index: 2;
            position: relative;
        }

        .library-icon {
            width: 90px;
            height: 90px;
            margin: 0 auto 25px;
            background: linear-gradient(135deg, #5b7eff 0%, #4a63d1 100%);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: float 3s ease-in-out infinite;
            box-shadow: 0 10px 30px rgba(91, 126, 255, 0.4);
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .library-icon svg {
            width: 55px;
            height: 55px;
            color: white;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.15));
        }

        .branding-content h1 {
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: -1px;
        }

        .branding-content p {
            font-size: 15px;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 35px;
        }

        .branding-features {
            display: flex;
            flex-direction: column;
            gap: 18px;
            text-align: left;
            margin-top: 40px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
        }

        .feature-item svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        /* Decorative Elements */
        .reset-branding::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -100px;
            right: -100px;
            animation: pulse 4s ease-in-out infinite;
        }

        .reset-branding::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            bottom: -50px;
            left: -50px;
            animation: pulse 5s ease-in-out infinite 0.5s;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        /* Back to Login Link */
        .back-to-login {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
        }

        .back-to-login a {
            color: #ce93d8;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-to-login a:hover {
            color: #e1bee7;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .reset-container {
                max-width: 100%;
                border-radius: 15px;
            }

            .reset-form-container {
                padding: 40px 25px;
            }

            .reset-branding.active {
                display: none;
            }

            .reset-form-header h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <!-- Left Side - Form -->
        <div class="reset-form-container">
            <div class="reset-form-header">
                <h2>Reset Password</h2>
                <p>Enter your email and new password to reset your account</p>
            </div>

            <!-- Display Errors (One at a Time) -->
            @if ($errors->any())
                <div class="error-message">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Success Message -->
            @if (session('status'))
                <div class="success-message">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    {{ session('status') }}
                </div>
            @endif

            <!-- Reset Password Form -->
            <form method="POST" action="{{ route('password.store') }}" id="resetForm">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email', $request->email) }}"
                        placeholder="Enter your email address"
                        required
                        autofocus
                    >
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password">New Password</label>
                    <div class="password-input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Enter a strong password"
                            required
                        >
                        <button type="button" class="password-toggle" data-target="password">
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="hidden eye-off-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password Field -->
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <div class="password-input-wrapper">
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            placeholder="Confirm your password"
                            required
                        >
                        <button type="button" class="password-toggle" data-target="password_confirmation">
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="hidden eye-off-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Reset Button -->
                <button type="submit" class="reset-btn" id="resetBtn">
                    <span>Reset Password</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12l5 5l10-10"></path>
                    </svg>
                </button>
            </form>

            <!-- Back to Login -->
            <div class="back-to-login">
                Remember your password? <a href="{{ route('login') }}">Back to Login</a>
            </div>
        </div>

        <!-- Right Side - Branding -->
        <div class="reset-branding active">
            <div class="branding-content">
                <div class="library-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="55" height="55" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-key">
                        <path d="M7 10a6 6 0 1 0 12 0A6 6 0 0 0 7 10z"></path>
                        <path d="M21 21l-4.35-4.35"></path>
                    </svg>
                </div>
                
                <h1>Secure Access</h1>
                <p>Reset your password and regain access to your account</p>

                <div class="branding-features">
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Quick & Easy Reset</span>
                    </div>
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Secure Password</span>
                    </div>
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Instant Access</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lucide Icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Password Toggle Functionality
            document.querySelectorAll('.password-toggle').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const targetId = button.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const eyeIcon = button.querySelector('.eye-icon');
                    const eyeOffIcon = button.querySelector('.eye-off-icon');

                    if (input && eyeIcon && eyeOffIcon) {
                        if (input.type === 'password') {
                            input.type = 'text';
                            eyeIcon.classList.add('hidden');
                            eyeOffIcon.classList.remove('hidden');
                        } else {
                            input.type = 'password';
                            eyeIcon.classList.remove('hidden');
                            eyeOffIcon.classList.add('hidden');
                        }
                    }
                });
            });

            // Form submission feedback
            const resetForm = document.getElementById('resetForm');
            const resetBtn = document.getElementById('resetBtn');

            if (resetForm && resetBtn) {
                resetForm.addEventListener('submit', () => {
                    resetBtn.disabled = true;
                    resetBtn.innerHTML = '<span>Resetting...</span>';
                });
            }
        });
    </script>
</body>
</html>
