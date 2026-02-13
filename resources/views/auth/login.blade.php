<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - Library Management System</title>
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

        .login-container {
            display: flex;
            max-width: 1000px;
            width: 100%;
            background: linear-gradient(135deg, #f8f7ff 0%, #ede9f6 100%);
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

        /* Left Side - Login Form */
        .login-form-container {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: linear-gradient(135deg, #9671ed 0%, #9e2fe4 100%);
        }

        .login-form-header {
            margin-bottom: 40px;
        }

        .login-form-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #fcfcfc;
            margin-bottom: 10px;
        }

        .login-form-header p {
            font-size: 14px;
            color: #f6e4f9;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #f5f3f5;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid rgba(225, 190, 231, 0.3);
            border-radius: 10px;
            font-size: 14px;
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

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 15px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .remember-me label {
            font-size: 14px;
            color: #e1bee7;
            cursor: pointer;
            margin-bottom: 0;
        }

        .forgot-password {
            font-size: 14px;
        }

        .forgot-password a {
            color: #e1bee7;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .forgot-password a:hover {
            color: #f3e5f5;
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 12px 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn svg {
            width: 20px;
            height: 20px;
        }

        .signup-link {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
            color: #ce93d8;
        }

        .signup-link a {
            color: #e1bee7;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .signup-link a:hover {
            color: #f3e5f5;
            text-decoration: underline;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: slideIn 0.3s ease-out;
        }

        .alert-danger {
            background: rgba(220, 38, 38, 0.15);
            color: #7f1d1d;
            border: 1px solid rgba(220, 38, 38, 0.3);
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        /* Error Message */
        .error-message {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            background: rgba(220, 38, 38, 0.15);
            color: #7f1d1d;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 15px;
            border: 1px solid rgba(220, 38, 38, 0.3);
        }

        .error-message svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        /* Right Side - Branding */
        .login-branding {
            display: none;
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            position: relative;
            overflow: hidden;
        }

        .login-branding.active {
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
            margin: 0 auto 30px;
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
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
            letter-spacing: -1px;
        }

        .branding-content p {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 40px;
        }

        .branding-features {
            display: flex;
            flex-direction: column;
            gap: 20px;
            text-align: left;
            margin-top: 50px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 14px;
        }

        .feature-item svg {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }

        /* Decorative Elements */
        .login-branding::before {
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

        .login-branding::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            bottom: -50px;
            left: -50px;
            animation: pulse 5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.2;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column-reverse;
            }

            .login-branding {
                padding: 40px 30px;
            }

            .login-branding.active {
                display: flex;
            }

            .login-form-container {
                padding: 40px 30px;
            }

            .branding-content h1 {
                font-size: 24px;
            }

            .login-form-header h2 {
                font-size: 24px;
            }

            body {
                padding: 15px;
            }

            .login-container {
                border-radius: 15px;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                border-radius: 12px;
            }

            .login-branding {
                padding: 30px 20px;
                min-height: 250px;
            }

            .login-form-container {
                padding: 30px 20px;
            }

            .branding-content h1 {
                font-size: 22px;
            }

            .branding-content p {
                font-size: 14px;
                margin-bottom: 30px;
            }

            .branding-features {
                gap: 15px;
            }

            .login-form-header h2 {
                font-size: 22px;
            }

            .library-icon {
                width: 70px;
                height: 70px;
            }

            .library-icon svg {
                width: 45px;
                height: 45px;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
            }

            .forgot-password {
                width: 100%;
                text-align: right;
            }
        }

        /* Loading State */
        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .login-btn.loading {
            pointer-events: none;
        }

        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side - Login Form -->
        <div class="login-form-container">
            <div class="login-form-header">
                <h2>Welcome Back</h2>
                <p>Sign in to your account to continue</p>
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

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="login-form">
                @csrf

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email"
                        value="{{ old('email') }}"
                        required 
                        autofocus
                    >
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Enter your password"
                            required
                        >
                        <button type="button" class="password-toggle" data-target="password">
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-off-icon hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">Remember me</label>
                    </div>
                    <div class="forgot-password">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">Forgot Password?</a>
                        @endif
                    </div>
                </div>

                <!-- Login Button -->
                <button type="submit" class="login-btn" id="loginBtn">
                    <span>Sign In</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>

                <!-- Sign Up Link -->
                @if (Route::has('register'))
                    <div class="signup-link">
                        Don't have an account? 
                        <a href="{{ route('register') }}">Sign up here</a>
                    </div>
                @endif
            </form>
        </div>

        <!-- Right Side - Branding (Visible on Desktop) -->
        <div class="login-branding active">
            <div class="branding-content">
                <div class="library-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="55" height="55" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg>
                </div>
                
                <h1>Library Management System</h1>
                <p>Efficiently manage your library resources and student requests</p>

                <div class="branding-features">
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Easy Book Management</span>
                    </div>
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Track Student Requests</span>
                    </div>
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Real-time Notifications</span>
                    </div>
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Issue & Return Management</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading state to form
            const loginForm = document.querySelector('.login-form');
            if (loginForm) {
                loginForm.addEventListener('submit', function() {
                    const btn = document.getElementById('loginBtn');
                    if (btn) {
                        btn.disabled = true;
                        btn.classList.add('loading');
                        btn.innerHTML = '<div class="spinner"></div><span>Signing in...</span>';
                    }
                });
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

            // Add animation to error messages
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach((msg, index) => {
                msg.style.animation = `slideIn 0.3s ease-out ${index * 0.1}s both`;
            });
        });
    </script>
</body>
</html>

