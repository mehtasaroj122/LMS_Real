<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Forgot Password - Library Management System</title>
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

        .password-container {
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
        .password-form-container {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: linear-gradient(135deg, #512da8 0%, #6a1b9a 100%);
        }

        .password-form-header {
            margin-bottom: 30px;
        }

        .password-form-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #e1bee7;
            margin-bottom: 10px;
        }

        .password-form-header p {
            font-size: 13px;
            color: #ce93d8;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #e1bee7;
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

        .reset-btn {
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
            margin-top: 10px;
        }

        .reset-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .reset-btn:active {
            transform: translateY(0);
        }

        .reset-btn svg {
            width: 20px;
            height: 20px;
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
        }

        .back-link a {
            color: #e1bee7;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
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

        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            color: #86efac;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .error-message {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            background: rgba(220, 38, 38, 0.15);
            color: #fca5a5;
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
        .password-branding {
            display: none;
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            position: relative;
            overflow: hidden;
        }

        .password-branding.active {
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
        .password-branding::before {
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

        .password-branding::after {
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
            .password-container {
                flex-direction: column-reverse;
            }

            .password-branding {
                padding: 40px 30px;
            }

            .password-branding.active {
                display: flex;
            }

            .password-form-container {
                padding: 40px 30px;
            }

            .branding-content h1 {
                font-size: 24px;
            }

            .password-form-header h2 {
                font-size: 24px;
            }

            body {
                padding: 15px;
            }

            .password-container {
                border-radius: 15px;
            }
        }

        @media (max-width: 480px) {
            .password-container {
                border-radius: 12px;
            }

            .password-branding {
                padding: 30px 20px;
                min-height: 250px;
            }

            .password-form-container {
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

            .password-form-header h2 {
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
        }

        /* Loading State */
        .reset-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .reset-btn.loading {
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
    <div class="password-container">
        <!-- Left Side - Form -->
        <div class="password-form-container">
            <div class="password-form-header">
                <h2>Forgot Password?</h2>
                <p>No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.</p>
            </div>

            <!-- Display Status -->
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

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

            <!-- Password Reset Form -->
            <form method="POST" action="{{ route('password.email') }}" class="password-form">
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

                <!-- Reset Button -->
                <button type="submit" class="reset-btn" id="resetBtn">
                    <span>Email Password Reset Link</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>

                <!-- Back to Login Link -->
                <div class="back-link">
                    Remember your password? 
                    <a href="{{ route('login') }}">Back to login</a>
                </div>
            </form>
        </div>

        <!-- Right Side - Branding -->
        <div class="password-branding active">
            <div class="branding-content">
                <div class="library-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="55" height="55" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg>
                </div>
                
                <h1>Library Management System</h1>
                <p>Securely reset your password and regain access to your account</p>

                <div class="branding-features">
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Secure Password Reset</span>
                    </div>
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Instant Email Delivery</span>
                    </div>
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Quick Account Recovery</span>
                    </div>
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>24/7 Support Available</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add loading state to form
        document.querySelector('.password-form').addEventListener('submit', function() {
            const btn = document.getElementById('resetBtn');
            btn.disabled = true;
            btn.classList.add('loading');
            btn.innerHTML = '<div class="spinner"></div><span>Sending Reset Link...</span>';
        });

        // Add animation to error messages
        const errorMessages = document.querySelectorAll('.error-message');
        errorMessages.forEach((msg, index) => {
            msg.style.animation = `slideIn 0.3s ease-out ${index * 0.1}s both`;
        });
    </script>
</body>
</html>

