<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Verify OTP - Library Management System</title>
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

        .otp-container {
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
        .otp-form-container {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: linear-gradient(135deg, #512da8 0%, #6a1b9a 100%);
        }

        .otp-form-header {
            margin-bottom: 35px;
            text-align: center;
        }

        .otp-form-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #e1bee7;
            margin-bottom: 12px;
        }

        .otp-form-header p {
            font-size: 14px;
            color: #ce93d8;
            line-height: 1.6;
        }

        .email-display {
            font-size: 13px;
            color: #e1bee7;
            background: rgba(206, 147, 216, 0.1);
            padding: 10px 14px;
            border-radius: 8px;
            margin-top: 10px;
            border: 1px solid rgba(225, 190, 231, 0.2);
            word-break: break-all;
        }

        /* OTP Input Container */
        .otp-inputs-container {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin: 35px 0;
            flex-wrap: wrap;
        }

        .otp-input {
            width: 50px;
            height: 50px;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
            border: 2px solid rgba(225, 190, 231, 0.3);
            border-radius: 10px;
            background: rgba(206, 147, 216, 0.15);
            color: #f3e5f5;
            transition: all 0.3s ease;
        }

        .otp-input:focus {
            outline: none;
            border-color: #e1bee7;
            background: rgba(206, 147, 216, 0.25);
            box-shadow: 0 0 0 3px rgba(225, 190, 231, 0.2);
        }

        .otp-input::placeholder {
            color: #ce93d8;
        }

        .otp-input.filled {
            background: rgba(225, 190, 231, 0.2);
            border-color: #e1bee7;
        }

        /* Form Group */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #e1bee7;
            margin-bottom: 8px;
        }

        .form-group textarea {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid rgba(225, 190, 231, 0.3);
            border-radius: 8px;
            font-size: 13px;
            transition: all 0.3s ease;
            font-family: inherit;
            background: rgba(206, 147, 216, 0.15);
            color: #f3e5f5;
            resize: none;
            height: 70px;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #e1bee7;
            background: rgba(206, 147, 216, 0.25);
            box-shadow: 0 0 0 3px rgba(225, 190, 231, 0.2);
        }

        /* Buttons Container */
        .otp-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 25px;
        }

        .verify-btn, .resend-btn {
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            height: auto;
        }

        .verify-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 100%;
        }

        .verify-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }

        .verify-btn:active {
            transform: translateY(0);
        }

        .verify-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .resend-btn {
            background: rgba(225, 190, 231, 0.15);
            color: #e1bee7;
            border: 1px solid rgba(225, 190, 231, 0.3);
            width: 100%;
        }

        .resend-btn:hover:not(:disabled) {
            background: rgba(225, 190, 231, 0.25);
            border-color: rgba(225, 190, 231, 0.5);
        }

        .resend-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .verify-btn svg, .resend-btn svg {
            width: 14px;
            height: 14px;
            stroke-width: 2.5;
        }

        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            animation: slideIn 0.3s ease-out;
        }

        .alert-danger {
            background: rgba(220, 38, 38, 0.15);
            color: #fca5a5;
            border: 1px solid rgba(220, 38, 38, 0.3);
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
        .otp-branding {
            display: none;
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            position: relative;
            overflow: hidden;
        }

        .otp-branding.active {
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
        .otp-branding::before {
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

        .otp-branding::after {
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
            .otp-container {
                flex-direction: column-reverse;
            }

            .otp-branding {
                padding: 40px 30px;
            }

            .otp-branding.active {
                display: flex;
            }

            .otp-form-container {
                padding: 40px 30px;
            }

            .branding-content h1 {
                font-size: 24px;
            }

            .otp-form-header h2 {
                font-size: 24px;
            }

            body {
                padding: 15px;
            }

            .otp-container {
                border-radius: 15px;
            }

            .otp-inputs-container {
                gap: 8px;
                margin: 25px 0;
            }

            .otp-input {
                width: 45px;
                height: 45px;
                font-size: 20px;
            }
        }

        @media (max-width: 480px) {
            .otp-container {
                border-radius: 12px;
            }

            .otp-branding {
                padding: 30px 20px;
                min-height: 250px;
            }

            .otp-form-container {
                padding: 30px 20px;
            }

            .branding-content h1 {
                font-size: 22px;
            }

            .otp-form-header h2 {
                font-size: 22px;
            }

            .otp-inputs-container {
                gap: 6px;
                margin: 20px 0;
            }

            .otp-input {
                width: 40px;
                height: 40px;
                font-size: 18px;
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
        .verify-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .verify-btn.loading {
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
    <div class="otp-container">
        <!-- Left Side - Form -->
        <div class="otp-form-container">
            <div class="otp-form-header">
                <h2>Verify Your Email</h2>
                <p>We've sent a 6-digit verification code to your email address</p>
                <div class="email-display">{{ $email }}</div>
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

            <!-- OTP Verification Form -->
            <form method="POST" action="{{ route('verify.otp') }}" class="otp-form">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Enter 6-Digit OTP Code</label>
                </div>

                <!-- OTP Input Fields -->
                <div class="otp-inputs-container">
                    <input type="text" class="otp-input" name="otp1" maxlength="1" placeholder="0" required>
                    <input type="text" class="otp-input" name="otp2" maxlength="1" placeholder="0" required>
                    <input type="text" class="otp-input" name="otp3" maxlength="1" placeholder="0" required>
                    <input type="text" class="otp-input" name="otp4" maxlength="1" placeholder="0" required>
                    <input type="text" class="otp-input" name="otp5" maxlength="1" placeholder="0" required>
                    <input type="text" class="otp-input" name="otp6" maxlength="1" placeholder="0" required>
                </div>

                <!-- Action Buttons -->
                <div class="otp-actions">
                    <button type="submit" class="verify-btn" id="verifyBtn">
                        <span>Verify OTP</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <button type="button" class="resend-btn" id="resendBtn" onclick="resendOTP()">
                        <span>Resend OTP</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <path d="M20.49 15a9 9 0 1 1-2-8.83"></path>
                        </svg>
                    </button>
                </div>

                <div class="resend-timer" id="timerDisplay"></div>
            </form>
        </div>

        <!-- Right Side - Branding -->
        <div class="otp-branding active">
            <div class="branding-content">
                <div class="library-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="55" height="55" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg>
                </div>
                
                <h1>Account Verification</h1>
                <p>Secure your account by verifying your email address</p>

                <div class="branding-features">
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>One-time OTP verification</span>
                    </div>
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>6-digit security code</span>
                    </div>
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Instant verification process</span>
                    </div>
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Resend code anytime</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const otpInputs = document.querySelectorAll('.otp-input');
        let resendTimer = 60;
        let timerInterval = null;

        // Auto-focus next input and validate numeric input
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                // Only allow digits
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                
                if (e.target.value.length === 1) {
                    e.target.classList.add('filled');
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                } else {
                    e.target.classList.remove('filled');
                }
            });

            // Handle backspace
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && input.value === '') {
                    if (index > 0) {
                        otpInputs[index - 1].focus();
                    }
                }
            });

            // Handle paste
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
                let pasteIndex = index;
                
                for (let i = 0; i < pasteData.length && pasteIndex < otpInputs.length; i++) {
                    otpInputs[pasteIndex].value = pasteData[i];
                    otpInputs[pasteIndex].classList.add('filled');
                    pasteIndex++;
                }
                
                if (pasteIndex > 0) {
                    otpInputs[Math.min(pasteIndex - 1, otpInputs.length - 1)].focus();
                }
            });
        });

        // Verify OTP Form
        document.querySelector('.otp-form').addEventListener('submit', function(e) {
            // Combine OTP values
            const otp = Array.from(otpInputs).map(input => input.value).join('');
            
            if (otp.length !== 6) {
                e.preventDefault();
                alert('Please enter a valid 6-digit OTP');
                return false;
            }

            const btn = document.getElementById('verifyBtn');
            btn.disabled = true;
            btn.classList.add('loading');
            btn.innerHTML = '<div class="spinner"></div><span>Verifying...</span>';
        });

        // Timer for resend button
        function startTimer() {
            resendTimer = 60;
            const resendBtn = document.getElementById('resendBtn');
            resendBtn.disabled = true;
            
            timerInterval = setInterval(() => {
                resendTimer--;
                document.getElementById('timerDisplay').textContent = `Resend OTP in ${resendTimer}s`;
                
                if (resendTimer <= 0) {
                    clearInterval(timerInterval);
                    resendBtn.disabled = false;
                    document.getElementById('timerDisplay').textContent = '';
                }
            }, 1000);
        }

        function resendOTP() {
            const email = document.querySelector('input[name="email"]').value;
            
            fetch('{{ route("resend.otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear OTP inputs
                    otpInputs.forEach(input => {
                        input.value = '';
                        input.classList.remove('filled');
                    });
                    otpInputs[0].focus();
                    
                    // Show success message
                    alert('OTP has been resent to your email');
                    
                    // Start timer again
                    startTimer();
                } else {
                    alert(data.message || 'Failed to resend OTP');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while resending OTP');
            });
        }

        // Start initial timer
        startTimer();
    </script>
</body>
</html>
