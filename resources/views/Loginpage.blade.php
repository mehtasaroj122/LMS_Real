<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LibraryPro</title>
    @vite(['resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
      :root {
    --primary-color: #1e3a8a;
    --primary-hover: #172e6d;
    --text-main: #1f2937;
    --text-muted: #6b7280;
    --border-color: #d1d5db;
    --bg-white: #ffffff;
    --font-family: 'Inter', sans-serif;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: var(--font-family);
    background-color: var(--bg-white);
    color: var(--text-main);
    overflow-x: hidden;
}

.login-container {
    display: flex;
    min-height: 100vh;
}

/* --- New Branding Section --- */
.brand-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 40px; /* Spacing above LOGIN heading */
}

.brand-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
}

.brand-name {
    font-size: 1.4rem;
    font-weight: 700;
    letter-spacing: -0.5px;
    color: var(--text-main);
}

.brand-accent {
    color: var(--primary-color);
}

/* --- Left Section --- */
.login-section {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
}

.form-wrapper {
    width: 100%;
    max-width: 400px;
}

/* Fade-in Animation */
.fade-in {
    animation: fadeInSlide 0.8s ease-out forwards;
}

@keyframes fadeInSlide {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-header {
    text-align: left; /* Aligned with input fields */
}

.form-header h1 {
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 4px;
    color: #0f172a;
}

.form-header p {
    color: var(--text-muted);
    font-size: 0.95rem;
    margin-bottom: 28px;
}

/* Input Styles */
.input-group {
    position: relative;
    margin-bottom: 16px;
}

.input-group i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
}

.input-group input {
    width: 100%;
    padding: 14px 14px 14px 45px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.input-group input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
}

.forgot-password {
    text-align: right;
    margin-bottom: 24px;
}

.forgot-password a {
    color: #2563eb;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
}

/* Button Styles */
.btn-primary {
    width: 100%;
    padding: 14px;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    background-color: var(--primary-hover);
    transform: translateY(-1px);
}

/* Divider */
.divider {
    display: flex;
    align-items: center;
    text-align: center;
    margin: 24px 0;
    color: var(--text-muted);
    font-size: 0.75rem;
    font-weight: 600;
}

.divider::before, .divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #e5e7eb;
}

.divider span {
    padding: 0 12px;
}

/* Social Buttons */
.social-logins {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.btn-social {
    width: 100%;
    padding: 12px;
    background-color: white;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s ease;
}

.btn-social:hover {
    background-color: #f8fafc;
}

.btn-social img {
    width: 18px;
}

.form-footer {
    text-align: center;
    margin-top: 32px;
    font-size: 0.9rem;
    color: var(--text-muted);
}

.form-footer a {
    color: #2563eb;
    text-decoration: none;
    font-weight: 700;
}

/* --- Right Section --- */
.image-section {
    flex: 1;
    position: relative;
    display: block;
}

.image-section img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-section .overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.15); /* Slightly darker overlay for premium feel */
}

/* --- Responsiveness --- */
@media (max-width: 900px) {
    .image-section {
        display: none;
    }
    
    .login-section {
        padding: 40px 20px;
    }

    .brand-name {
        font-size: 1.2rem;
    }
}
    </style>
</head>
<body>
    @include('shared.library-branding.bootstrap')
    <main class="login-container">
        <section class="login-section">
            <div class="form-wrapper fade-in">
                
                <div class="brand-container">
                    <div class="brand-logo">
                        <x-logo size="lg" :lazy="false" />
                    </div>
                    <span class="brand-name">Library<span class="brand-accent">Pro</span></span>
                </div>

                <header class="form-header">
                    <h1>LOGIN</h1>
                    <p>Welcome to Library Management System</p>
                </header>

                <form id="loginForm">
                    <div class="input-group">
                        <i class="fa-regular fa-user"></i>
                        <input type="text" placeholder="Username / Email" required>
                    </div>

                    <div class="input-group">
                        <i class="fa-regular fa-lock"></i>
                        <input type="password" placeholder="Password" required>
                    </div>

                    <div class="forgot-password">
                        <a href="#">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn-primary">Login</button>
                </form>

                <div class="divider">
                    <span>OR</span>
                </div>

                <div class="social-logins">
                    <button class="btn-social">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_Logo.svg" alt="Google">
                        Login with Google
                    </button>
                    <button class="btn-social">
                        <i class="fa-brands fa-facebook" style="color: #1877F2;"></i>
                        Login with Facebook
                    </button>
                </div>

                <footer class="form-footer">
                    <p>Don't have an account? <a href="#">Sign up</a></p>
                </footer>
            </div>
        </section>

        <section class="image-section">
            <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?q=80&w=2000" alt="Library Bookshelf">
            <div class="overlay"></div>
        </section>
    </main>
</body>
</html>
