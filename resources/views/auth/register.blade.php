<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register - Library Management System</title>
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

        .register-container {
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
        .register-form-container {
            flex: 1;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: linear-gradient(135deg, #512da8 0%, #6a1b9a 100%);
            max-height: 95vh;
            overflow-y: auto;
        }

        .register-form-container::-webkit-scrollbar {
            width: 6px;
        }

        .register-form-container::-webkit-scrollbar-track {
            background: rgba(225, 190, 231, 0.1);
            border-radius: 10px;
        }

        .register-form-container::-webkit-scrollbar-thumb {
            background: rgba(225, 190, 231, 0.3);
            border-radius: 10px;
        }

        .register-form-header {
            margin-bottom: 25px;
        }

        .register-form-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #e1bee7;
            margin-bottom: 8px;
        }

        .register-form-header p {
            font-size: 13px;
            color: #ce93d8;
        }

        .form-group {
            margin-bottom: 18px;
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

        .register-btn {
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .register-btn:active {
            transform: translateY(0);
        }

        .register-btn svg {
            width: 18px;
            height: 18px;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #ce93d8;
        }

        .login-link a {
            color: #e1bee7;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: #f3e5f5;
            text-decoration: underline;
        }

        .alert {
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
            animation: slideIn 0.3s ease-out;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            color: #86efac;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .error-message {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 10px;
            background: rgba(220, 38, 38, 0.15);
            color: #fca5a5;
            border-radius: 6px;
            font-size: 12px;
            margin-bottom: 12px;
            border: 1px solid rgba(220, 38, 38, 0.3);
        }

        .error-message svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        /* Right Side - Branding */
        .register-branding {
            display: none;
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 50px 40px;
            position: relative;
            overflow: hidden;
        }

        .register-branding.active {
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
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
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
        .register-branding::before {
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

        .register-branding::after {
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
            .register-container {
                flex-direction: column-reverse;
            }

            .register-branding {
                padding: 35px 25px;
            }

            .register-branding.active {
                display: flex;
            }

            .register-form-container {
                padding: 35px 25px;
            }

            .branding-content h1 {
                font-size: 24px;
            }

            .register-form-header h2 {
                font-size: 24px;
            }

            body {
                padding: 15px;
            }

            .register-container {
                border-radius: 15px;
            }
        }

        @media (max-width: 480px) {
            .register-container {
                border-radius: 12px;
            }

            .register-branding {
                padding: 25px 18px;
                min-height: 220px;
            }

            .register-form-container {
                padding: 25px 18px;
            }

            .branding-content h1 {
                font-size: 20px;
            }

            .branding-content p {
                font-size: 13px;
                margin-bottom: 25px;
            }

            .branding-features {
                gap: 12px;
            }

            .register-form-header h2 {
                font-size: 20px;
            }

        }

        /* Loading State */
        .register-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .register-btn.loading {
            pointer-events: none;
        }

        .spinner {
            width: 16px;
            height: 16px;
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
    @include('shared.library-branding.bootstrap')
    <div class="register-container">
        <!-- Left Side - Form -->
        <div class="register-form-container">
            <div class="register-form-header">
                <h2>Create Account</h2>
                <p>Join our library community and start exploring</p>
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

            <!-- Registration Form -->
            <form method="POST" action="{{ route('register') }}" class="register-form">
                @csrf

                <!-- Name Field -->
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        placeholder="Enter your full name"
                        value="{{ old('name') }}"
                        required 
                        autofocus
                    >
                </div>

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
                            placeholder="Enter a strong password"
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
                            <svg class="eye-off-icon hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Register Button -->
                <button type="submit" class="register-btn" id="registerBtn">
                    <span>Create Account</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>

                <!-- Login Link -->
                <div class="login-link">
                    Already have an account? 
                    <a href="{{ route('login') }}">Sign in here</a>
                </div>
            </form>
        </div>

        <!-- Right Side - Branding -->
        <div class="register-branding active">
            <div class="branding-content">
                <div class="library-icon">
                    <x-logo size="hero" :lazy="false" />
                </div>
                
                <h1>Join Us Today</h1>
                <p>Create an account and get instant access to our library resources</p>

                <div class="branding-features">
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Browse thousands of books</span>
                    </div>
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Request books anytime</span>
                    </div>
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Manage your borrowings</span>
                    </div>
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Instant notifications</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add loading state to form
        document.querySelector('.register-form').addEventListener('submit', function() {
            const btn = document.getElementById('registerBtn');
            btn.disabled = true;
            btn.classList.add('loading');
            btn.innerHTML = '<div class="spinner"></div><span>Creating Account...</span>';
        });

        // Add animation to error messages
        const errorMessages = document.querySelectorAll('.error-message');
        errorMessages.forEach((msg, index) => {
            msg.style.animation = `slideIn 0.3s ease-out ${index * 0.1}s both`;
        });

        // Password Toggle Functionality
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = button.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const eyeIcon = button.querySelector('.eye-icon');
                const eyeOffIcon = button.querySelector('.eye-off-icon');

                if (input.type === 'password') {
                    input.type = 'text';
                    eyeIcon.classList.add('hidden');
                    eyeOffIcon.classList.remove('hidden');
                } else {
                    input.type = 'password';
                    eyeIcon.classList.remove('hidden');
                    eyeOffIcon.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>
