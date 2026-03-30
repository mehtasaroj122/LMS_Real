<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Next-gen Library Management System. Streamline cataloging, members, and assets.">
    <title>LMS - Intelligent Library Management</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap"
        rel="stylesheet">

    <style>
        /* =========================================
           1. DESIGN TOKENS & RESET
           ========================================= */
        :root {
            /* Colors */
            --primary: #0f62fe;
            --primary-rgb: 15, 98, 254;
            --accent: #7a5cff;
            --bg: #0b1020;
            --surface: #121a2d;
            /* Slightly lighter than bg */
            --surface-hover: #1a243a;
            --text: #e6eef8;
            --text-muted: #94a3b8;
            --border: rgba(255, 255, 255, 0.1);

            /* Typography */
            --font-head: 'Poppins', sans-serif;
            --font-body: 'Inter', sans-serif;

            /* Spacing */
            --container-width: 1200px;
            --header-height: 80px;

            /* Radius */
            --radius-sm: 6px;
            --radius-md: 12px;
            --radius-lg: 18px;

            /* Shadows */
            --shadow-1: 0 2px 6px rgba(0, 0, 0, 0.15);
            --shadow-2: 0 8px 24px rgba(0, 0, 0, 0.25);
            --shadow-3: 0 20px 40px rgba(0, 0, 0, 0.4);
            --glow: 0 0 20px rgba(15, 98, 254, 0.3);

            /* Animation */
            --ease-pop: cubic-bezier(.22, .9, .4, 1);
            --ease-smooth: cubic-bezier(.2, .9, .2, 1);
            --transition-base: 250ms var(--ease-smooth);
        }

        /* Reset & Base */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: var(--font-body);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        h1,
        h2,
        h3,
        h4 {
            font-family: var(--font-head);
            line-height: 1.2;
            color: #fff;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: var(--transition-base);
        }

        ul {
            list-style: none;
        }

        img,
        svg {
            display: block;
            max-width: 100%;
        }

        /* Container */
        .container {
            width: 100%;
            max-width: var(--container-width);
            margin: 0 auto;
            padding: 0 24px;
        }

        /* =========================================
           2. UTILITIES & ANIMATIONS
           ========================================= */
        /* Scroll Reveal Animation Classes */
        .reveal-up {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease-out, transform 0.6s var(--ease-pop);
        }

        .reveal-up.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Stagger Delays */
        .delay-100 {
            transition-delay: 100ms;
        }

        .delay-200 {
            transition-delay: 200ms;
        }

        .delay-300 {
            transition-delay: 300ms;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: var(--radius-md);
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            border: none;
            transition: transform 0.2s var(--ease-pop), box-shadow 0.2s ease, background 0.2s;
        }

        .btn:focus-visible {
            outline: 3px solid rgba(var(--primary-rgb), 0.5);
            outline-offset: 3px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            box-shadow: var(--shadow-1);
        }

        .btn-primary:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: var(--shadow-2), var(--glow);
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--text);
            transform: translateY(-2px);
        }

        /* =========================================
           3. NAVIGATION
           ========================================= */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: var(--header-height);
            background: rgba(11, 16, 32, 0.85);
            backdrop-filter: blur(12px);
            z-index: 1000;
            border-bottom: 1px solid var(--border);
        }

        .nav-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
        }

        .logo {
            font-family: var(--font-head);
            font-weight: 700;
            font-size: 1.5rem;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo span {
            color: var(--primary);
        }

        .nav-links {
            display: flex;
            gap: 32px;
        }

        .nav-link {
            font-size: 0.95rem;
            color: var(--text-muted);
            position: relative;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #fff;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--primary);
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s var(--ease-pop);
        }

        .nav-link:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        /* Mobile Menu Toggle */
        .hamburger {
            display: none;
            flex-direction: column;
            gap: 6px;
            background: none;
            border: none;
            cursor: pointer;
        }

        .bar {
            width: 24px;
            height: 2px;
            background: #fff;
            transition: 0.3s;
        }

        /* =========================================
           4. HERO SECTION
           ========================================= */
        .hero {
            position: relative;
            padding-top: calc(var(--header-height) + 60px);
            padding-bottom: 80px;
            min-height: 90vh;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(15, 98, 254, 0.15) 0%, rgba(11, 16, 32, 0) 70%);
            border-radius: 50%;
            z-index: -1;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            align-items: center;
        }

        .hero-content h1 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            margin-bottom: 1.5rem;
            letter-spacing: -0.02em;
        }

        .hero-content p {
            font-size: 1.125rem;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            max-width: 500px;
        }

        .hero-btns {
            display: flex;
            gap: 16px;
        }

        .hero-visual {
            position: relative;
            perspective: 1000px;
        }

        /* 3D Tilt Wrapper */
        .tilt-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-3);
            transform-style: preserve-3d;
            transition: transform 0.1s ease-out;
            /* Fast for JS parallax */
        }

        .hero-svg {
            width: 100%;
            height: auto;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.3));
        }

        /* Scroll Chevron */
        .scroll-cue {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            animation: bounce 2s infinite;
            opacity: 0.7;
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0) translateX(-50%);
            }

            40% {
                transform: translateY(-10px) translateX(-50%);
            }

            60% {
                transform: translateY(-5px) translateX(-50%);
            }
        }

        /* =========================================
           5. FEATURES SECTION
           ========================================= */
        .section {
            padding: 100px 0;
        }

        .section-header {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 64px;
        }

        .section-header h2 {
            font-size: 2.5rem;
            margin-bottom: 16px;
        }

        .section-header p {
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
        }

        .feature-card {
            background: var(--surface);
            padding: 32px;
            border-radius: var(--radius-md);
            border: 1px solid rgba(255, 255, 255, 0.03);
            transition: all 0.3s var(--ease-pop);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-2);
            border-color: rgba(15, 98, 254, 0.3);
            background: var(--surface-hover);
        }

        .icon-box {
            width: 56px;
            height: 56px;
            background: rgba(15, 98, 254, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            color: var(--primary);
            transition: transform 0.3s ease;
        }

        .feature-card:hover .icon-box {
            transform: scale(1.1) rotate(-6deg);
            background: var(--primary);
            color: white;
        }

        .feature-card h3 {
            margin-bottom: 12px;
            font-size: 1.25rem;
        }

        .feature-card p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* =========================================
           6. HOW IT WORKS
           ========================================= */
        .steps-container {
            display: flex;
            justify-content: space-between;
            position: relative;
            flex-wrap: wrap;
            gap: 40px;
        }

        .steps-container::before {
            content: '';
            position: absolute;
            top: 24px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--border);
            z-index: 0;
        }

        .step {
            position: relative;
            flex: 1;
            z-index: 1;
            min-width: 250px;
        }

        .step-number {
            width: 50px;
            height: 50px;
            background: var(--surface);
            border: 2px solid var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-bottom: 24px;
            box-shadow: 0 0 0 8px var(--bg);
            /* fake mask */
        }

        /* =========================================
           7. PRICING
           ========================================= */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
            align-items: flex-start;
        }

        .price-card {
            background: var(--surface);
            padding: 40px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            text-align: center;
            position: relative;
        }

        .price-card.featured {
            border-color: var(--primary);
            box-shadow: var(--glow);
            transform: scale(1.05);
            z-index: 2;
        }

        .badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--accent);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .price {
            font-size: 3rem;
            font-weight: 700;
            margin: 16px 0;
        }

        .price span {
            font-size: 1rem;
            color: var(--text-muted);
            font-weight: 400;
        }

        .price-features {
            margin: 24px 0 32px;
            text-align: left;
        }

        .price-features li {
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-muted);
        }

        .price-features svg {
            color: var(--accent);
            width: 20px;
        }

        /* =========================================
           8. CTA & FOOTER
           ========================================= */
        .cta-section {
            background: linear-gradient(135deg, var(--surface) 0%, #151e32 100%);
            border-radius: var(--radius-lg);
            padding: 64px;
            text-align: center;
            border: 1px solid var(--border);
            margin-bottom: 80px;
        }

        footer {
            border-top: 1px solid var(--border);
            padding: 64px 0 32px;
            background: var(--surface);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 48px;
        }

        .footer-col h4 {
            margin-bottom: 24px;
            font-size: 1.1rem;
        }

        .footer-col ul li {
            margin-bottom: 12px;
        }

        .footer-col a {
            color: var(--text-muted);
        }

        .footer-col a:hover {
            color: var(--primary);
        }

        .copyright {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
            padding-top: 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* =========================================
           9. RESPONSIVE
           ========================================= */
        @media (max-width: 968px) {
            .hero-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-btns {
                justify-content: center;
            }

            .hero::before {
                display: none;
            }

            .steps-container {
                flex-direction: column;
            }

            .steps-container::before {
                width: 2px;
                height: 100%;
                left: 24px;
                top: 0;
            }

            .step-number {
                margin-left: 0;
            }

            .step {
                padding-left: 60px;
            }

            .price-card.featured {
                transform: scale(1);
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                position: fixed;
                top: var(--header-height);
                left: 0;
                width: 100%;
                background: var(--bg);
                flex-direction: column;
                padding: 24px;
                border-bottom: 1px solid var(--border);
                transform: translateY(-150%);
                transition: transform 0.3s ease;
            }

            .nav-links.active {
                transform: translateY(0);
            }

            .hamburger {
                display: flex;
            }

            .nav-cta {
                display: none;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }

        /* Accessibility: Reduced Motion */
        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>

<body>

    <header class="navbar">
        <div class="container nav-container">
            <a href="#" class="logo">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--primary)">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                </svg>
                LMS<span>.io</span>
            </a>
            <nav>
                <ul class="nav-links" id="navLinks">
                    <li><a href="#features" class="nav-link">Features</a></li>
                    <li><a href="#how-it-works" class="nav-link">How it Works</a></li>
                    <li><a href="#pricing" class="nav-link">Pricing</a></li>
                    <li><a href="#" class="btn btn-primary mobile-only" style="margin-top:10px; width:100%">Get
                            Started</a></li>
                </ul>
            </nav>
            <div style="display: flex; align-items: center; gap: 16px;">
                <a href="#signup" class="btn btn-primary nav-cta">Get Started</a>
                <button class="hamburger" id="menuToggle" aria-label="Toggle navigation" aria-expanded="false">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container hero-grid">
                <div class="hero-content">
                    <h1 class="reveal-up">Manage your library at the speed of thought.</h1>
                    <p class="delay-100 reveal-up">The modern, cloud-based platform for organizing catalogs, tracking
                        assets, and engaging members. Beautifully simple.</p>
                    <div class="delay-200 hero-btns reveal-up">
                        <a href="#signup" class="btn btn-primary">Start Free Trial</a>
                        <a href="#demo" class="btn btn-secondary">Live Demo</a>
                    </div>
                </div>
                <div class="delay-300 hero-visual reveal-up">
                    <div class="tilt-card" id="heroCard">
                        <svg class="hero-svg" viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="20" y="40" width="360" height="220" rx="8" fill="#1A243A"
                                stroke="#2D3A54" stroke-width="2" />
                            <rect x="20" y="40" width="360" height="40" rx="8" fill="#151E32" />
                            <circle cx="50" cy="60" r="6" fill="#FB5D5D" />
                            <circle cx="70" cy="60" r="6" fill="#FBBF5D" />
                            <circle cx="90" cy="60" r="6" fill="#5DFAFB" />
                            <rect x="50" y="110" width="140" height="120" rx="4" fill="#121a2d" />
                            <rect x="210" y="110" width="140" height="50" rx="4" fill="#0F62FE"
                                fill-opacity="0.2" />
                            <rect x="210" y="170" width="140" height="60" rx="4" fill="#121a2d" />
                            <rect x="70" y="190" width="20" height="30" fill="#0F62FE" />
                            <rect x="100" y="170" width="20" height="50" fill="#7A5CFF" />
                            <rect x="130" y="150" width="20" height="70" fill="#5CFFB1" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="scroll-cue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M7 13l5 5 5-5M7 6l5 5 5-5" />
                </svg>
            </div>
        </section>

        <section id="features" class="section">
            <div class="container">
                <div class="section-header reveal-up">
                    <h2>Everything you need</h2>
                    <p>Powerful features packed into a clean interface.</p>
                </div>
                <div class="features-grid">
                    <div class="feature-card reveal-up">
                        <div class="icon-box">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M12 20h9" />
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                            </svg>
                        </div>
                        <h3>Smart Cataloging</h3>
                        <p>Automatically fetch book details by ISBN. Categorize and tag instantly.</p>
                    </div>
                    <div class="delay-100 feature-card reveal-up">
                        <div class="icon-box">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                        </div>
                        <h3>Member Portal</h3>
                        <p>Self-service portal for members to renew books and pay fines online.</p>
                    </div>
                    <div class="delay-200 feature-card reveal-up">
                        <div class="icon-box">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                                <polyline points="17 6 23 6 23 12" />
                            </svg>
                        </div>
                        <h3>Analytics</h3>
                        <p>Track circulation trends, late returns, and popular genres with real-time charts.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="how-it-works" class="section" style="background: rgba(255,255,255,0.02)">
            <div class="container">
                <div class="section-header reveal-up">
                    <h2>Simple Workflow</h2>
                </div>
                <div class="steps-container">
                    <div class="step reveal-up">
                        <div class="step-number">1</div>
                        <h3>Import Data</h3>
                        <p style="color:var(--text-muted)">Bulk upload your existing CSVs or scan ISBNs directly.</p>
                    </div>
                    <div class="delay-100 step reveal-up">
                        <div class="step-number">2</div>
                        <h3>Invite Members</h3>
                        <p style="color:var(--text-muted)">Send email invites to staff and members to join the portal.
                        </p>
                    </div>
                    <div class="delay-200 step reveal-up">
                        <div class="step-number">3</div>
                        <h3>Start Circulation</h3>
                        <p style="color:var(--text-muted)">Check-in and check-out items with a simple barcode scan.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="pricing" class="section">
            <div class="container">
                <div class="section-header reveal-up">
                    <h2>Transparent Pricing</h2>
                </div>
                <div class="pricing-grid">
                    <div class="price-card reveal-up">
                        <h3>Starter</h3>
                        <div class="price">₹0<span>/mo</span></div>
                        <ul class="price-features">
                            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg> Up to 500 books</li>
                            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg> 1 Admin</li>
                        </ul>
                        <a href="#" class="btn btn-secondary" style="width:100%">Sign Up</a>
                    </div>
                    <div class="delay-100 price-card featured reveal-up">
                        <div class="badge">POPULAR</div>
                        <h3>Institution</h3>
                        <div class="price">₹49<span>/mo</span></div>
                        <ul class="price-features">
                            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg> Unlimited books</li>
                            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg> 5 Staff accounts</li>
                            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg> Advanced Analytics</li>
                        </ul>
                        <a href="#" class="btn btn-primary" style="width:100%">Get Started</a>
                    </div>
                    <div class="delay-200 price-card reveal-up">
                        <h3>Enterprise</h3>
                        <div class="price">Custom</div>
                        <ul class="price-features">
                            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg> Multi-branch support</li>
                            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg> API Access</li>
                        </ul>
                        <a href="#" class="btn btn-secondary" style="width:100%">Contact Us</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="container">
            <div class="cta-section reveal-up">
                <h2>Ready to modernize your library?</h2>
                <p style="color:var(--text-muted); margin: 16px 0 32px;">Join 5,000+ libraries using LMS.io today.</p>
                <a href="#" class="btn btn-primary" style="padding: 16px 32px; font-size: 1.1rem;">Get Started
                    for Free</a>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <a href="#" class="logo" style="margin-bottom:16px;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            style="color:var(--primary)">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                        </svg>
                        LMS.io
                    </a>
                    <p style="color:var(--text-muted); font-size: 0.9rem; max-width: 300px;">
                        Building the best tools for librarians and knowledge managers worldwide.
                    </p>
                </div>
                <div class="footer-col">
                    <h4>Product</h4>
                    <ul>
                        <li><a href="#">Features</a></li>
                        <li><a href="#">Pricing</a></li>
                        <li><a href="#">Changelog</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="#">About</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#">Privacy</a></li>
                        <li><a href="#">Terms</a></li>
                        <li><a href="#">Security</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                &copy; 2023 LibraryManagementSystem. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // 1. Mobile Menu Toggle
            const menuToggle = document.getElementById('menuToggle');
            const navLinks = document.getElementById('navLinks');

            menuToggle.addEventListener('click', () => {
                const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
                menuToggle.setAttribute('aria-expanded', !isExpanded);
                navLinks.classList.toggle('active');
            });

            // Close menu when clicking a link
            navLinks.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    navLinks.classList.remove('active');
                    menuToggle.setAttribute('aria-expanded', 'false');
                });
            });

            // 2. Scroll Reveal Animation (Intersection Observer)
            const observerOptions = {
                threshold: 0.1,
                rootMargin: "0px 0px -50px 0px"
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        // Optional: Stop observing once revealed
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.reveal-up').forEach(el => observer.observe(el));

            // 3. Hero Parallax Tilt Effect
            const heroCard = document.getElementById('heroCard');
            const heroSection = document.querySelector('.hero');

            // Only enable on desktop and if reduced motion is not preferred
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (!prefersReducedMotion && window.innerWidth > 968) {
                heroSection.addEventListener('mousemove', (e) => {
                    const {
                        clientX,
                        clientY
                    } = e;
                    const {
                        innerWidth,
                        innerHeight
                    } = window;

                    // Calculate rotation (limit to +/- 6deg as per brief)
                    const xPos = (clientX / innerWidth - 0.5);
                    const yPos = (clientY / innerHeight - 0.5);

                    const rotateY = xPos * 12; // Rotate around Y axis
                    const rotateX = yPos * -12; // Rotate around X axis

                    // Apply transform using requestAnimationFrame for performance
                    requestAnimationFrame(() => {
                        heroCard.style.transform =
                            `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
                    });
                });

                // Reset on mouse leave
                heroSection.addEventListener('mouseleave', () => {
                    heroCard.style.transform = `perspective(1000px) rotateX(0) rotateY(0)`;
                });
            }
        });
    </script>
</body>

</html>
