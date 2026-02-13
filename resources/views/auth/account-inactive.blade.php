<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Account Inactive - Library Management System</title>
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

        .inactive-container {
            display: flex;
            flex-direction: column;
            max-width: 900px;
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

        /* Header Section with Gradient */
        .inactive-header {
            background: linear-gradient(135deg, #d32f2f 0%, #c62828 100%);
            padding: 40px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .inactive-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .icon-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .icon-wrapper svg {
            width: 60px;
            height: 60px;
            color: white;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.8;
                transform: scale(0.95);
            }
        }

        .inactive-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: white;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .inactive-header p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
            position: relative;
            z-index: 1;
        }

        /* Content Section */
        .inactive-content {
            padding: 50px 40px;
            background: linear-gradient(135deg, #f8f7ff 0%, #ede9f6 100%);
        }

        .status-badge {
            display: inline-block;
            background: linear-gradient(135deg, #fce4ec 0%, #f8bbd0 100%);
            color: #c2185b;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-message {
            margin-bottom: 30px;
        }

        .status-message h2 {
            font-size: 22px;
            font-weight: 700;
            color: #2d1b4e;
            margin-bottom: 12px;
        }

        .status-message p {
            font-size: 15px;
            color: #5d4e7f;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        /* Reasons List */
        .reasons-section {
            background: linear-gradient(135deg, #ede9f6 0%, #e6d9f0 100%);
            padding: 24px;
            border-radius: 12px;
            margin: 30px 0;
            border-left: 4px solid #9671ed;
        }

        .reasons-section h3 {
            font-size: 14px;
            font-weight: 700;
            color: #2d1b4e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .reasons-section h3 svg {
            width: 16px;
            height: 16px;
            color: #9671ed;
        }

        .reasons-list {
            list-style: none;
        }

        .reasons-list li {
            font-size: 13px;
            color: #5d4e7f;
            padding: 6px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .reasons-list li::before {
            content: '';
            width: 4px;
            height: 4px;
            background: #9671ed;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 16px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 28px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            letter-spacing: 0.3px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #9671ed 0%, #7e57c2 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(150, 113, 237, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(150, 113, 237, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: white;
            color: #9671ed;
            border: 2px solid #9671ed;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #ede9f6 0%, #e6d9f0 100%);
            transform: translateY(-2px);
        }

        .btn-secondary:active {
            transform: translateY(0);
        }

        .btn svg {
            width: 16px;
            height: 16px;
        }

        /* Contact Section */
        .contact-section {
            background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
            padding: 30px;
            border-radius: 12px;
            margin-top: 30px;
            border: 1px solid #e0e0e0;
        }

        .contact-section h3 {
            font-size: 15px;
            font-weight: 700;
            color: #2d1b4e;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .contact-section h3 svg {
            width: 18px;
            height: 18px;
            color: #9671ed;
        }

        .contact-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .contact-method {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .contact-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #ede9f6 0%, #e6d9f0 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9671ed;
            flex-shrink: 0;
        }

        .contact-icon svg {
            width: 18px;
            height: 18px;
        }

        .contact-details {
            flex: 1;
        }

        .contact-details h4 {
            font-size: 13px;
            font-weight: 700;
            color: #2d1b4e;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .contact-details p {
            font-size: 13px;
            color: #5d4e7f;
            line-height: 1.4;
        }

        .contact-details a {
            color: #9671ed;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .contact-details a:hover {
            color: #7e57c2;
            text-decoration: underline;
        }

        /* Brand Logo Area */
        .brand-logo {
            text-align: center;
            padding: 20px;
            border-top: 1px solid rgba(150, 113, 237, 0.1);
            margin-top: 30px;
        }

        .brand-logo p {
            font-size: 12px;
            color: #9671ed;
            font-weight: 500;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .inactive-header {
                padding: 30px 20px;
            }

            .inactive-header h1 {
                font-size: 24px;
            }

            .inactive-content {
                padding: 30px 20px;
            }

            .icon-wrapper svg {
                width: 50px;
                height: 50px;
            }

            .status-message h2 {
                font-size: 18px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .contact-methods {
                grid-template-columns: 1fr;
            }
        }

        /* Loading Animation */
        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
            }
        }
    </style>
</head>
<body>
    <div class="inactive-container">
        <!-- Header Section -->
        <div class="inactive-header">
            <div class="icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <h1>Account Inactive</h1>
            <p>Your account is currently not active</p>
        </div>

        <!-- Content Section -->
        <div class="inactive-content">
            <span class="status-badge">⚠️ Status: Inactive</span>

            <div class="status-message">
                <h2>Account Access Restricted</h2>
                <p>
                    We're sorry, but your account is currently inactive and cannot be used to access the Library Management System at this time.
                </p>
                <p>
                    This typically happens when an administrator disables your account temporarily or there's a pending verification required.
                </p>
            </div>

            <!-- Reasons Section -->
            <div class="reasons-section">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"></path>
                        <path d="M12 4v8l6 4"></path>
                    </svg>
                    Possible Reasons
                </h3>
                <ul class="reasons-list">
                    <li>Administrator has temporarily deactivated your account</li>
                    <li>Your account status is under review</li>
                    <li>Membership or subscription has expired</li>
                    <li>Account security verification required</li>
                    <li>Policy violation or terms of service review</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('login') }}" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Login
                </a>
                <a href="{{ route('register') }}" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                    Create New Account
                </a>
            </div>

            <!-- Contact Section -->
            <div class="contact-section">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Contact Administrator
                </h3>
                <div class="contact-methods">
                    <div class="contact-method">
                        <div class="contact-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                            </svg>
                        </div>
                        <div class="contact-details">
                            <h4>Email Support</h4>
                            <p>
                                <a href="mailto:admin@librarysystem.com">admin@librarysystem.com</a>
                            </p>
                        </div>
                    </div>
                    <div class="contact-method">
                        <div class="contact-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                        </div>
                        <div class="contact-details">
                            <h4>Phone Support</h4>
                            <p>+1 (555) 123-4567</p>
                        </div>
                    </div>
                    <div class="contact-method">
                        <div class="contact-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"></path>
                                <path d="M12 7v5l4 2"></path>
                            </svg>
                        </div>
                        <div class="contact-details">
                            <h4>Office Hours</h4>
                            <p>Mon - Fri: 9 AM - 5 PM</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Brand Footer -->
            <div class="brand-logo">
                <p>📚 Library Management System - Professional Edition</p>
            </div>
        </div>
    </div>
</body>
</html>
