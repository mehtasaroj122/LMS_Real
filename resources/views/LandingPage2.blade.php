<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Modern Library Management System for schools and institutions.">
    <title>LMS | Smart Library Solutions</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    <style>
        /* --- 1. CSS VARIABLES & RESET --- */
        :root {
            /* Palette */
            --primary: #4F46E5; /* Indigo */
            --primary-dark: #4338ca;
            --accent: #9333EA; /* Purple */
            --text-main: #111827;
            --text-muted: #6B7280;
            --bg-light: #ffffff;
            --bg-off: #F9FAFB;
            --bg-dark: #1F2937;
            
            /* UI Tokens */
            --nav-height: 80px;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 20px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-glow: 0 0 20px rgba(79, 70, 229, 0.3);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; scroll-behavior: smooth; }
        
        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            background-color: var(--bg-light);
            overflow-x: hidden;
            line-height: 1.6;
        }

        h1, h2, h3, h4 { font-family: 'Poppins', sans-serif; color: var(--text-main); line-height: 1.2; }
        a { text-decoration: none; color: inherit; transition: var(--transition); }
        ul { list-style: none; }
        img { max-width: 100%; display: block; }

        /* Utility Classes */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
        .section-padding { padding: 100px 0; }
        .text-center { text-align: center; }
        .gradient-text {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* --- 2. BUTTONS --- */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 28px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            border: none;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .btn-ghost {
            background: transparent;
            color: var(--text-main);
        }

        .btn-ghost:hover { color: var(--primary); }

        /* --- 3. NAVIGATION --- */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: var(--nav-height);
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            z-index: 1000;
            transition: box-shadow 0.3s ease;
            display: flex;
            align-items: center;
        }

        .navbar.scrolled { box-shadow: var(--shadow-sm); }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1.25rem;
            font-family: 'Poppins', sans-serif;
        }

        .logo i { color: var(--primary); font-size: 1.5rem; }

        .nav-links {
            display: flex;
            gap: 32px;
        }

        .nav-link {
            font-weight: 500;
            position: relative;
            color: var(--text-muted);
        }

        .nav-link:hover, .nav-link.active { color: var(--primary); }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0%;
            height: 2px;
            bottom: -4px;
            left: 0;
            background-color: var(--primary);
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after { width: 100%; }

        .auth-buttons { display: flex; gap: 16px; align-items: center; }
        
        .mobile-toggle { display: none; font-size: 1.5rem; cursor: pointer; }

        /* --- 4. HERO SECTION --- */
        .hero {
            padding-top: calc(var(--nav-height) + 60px);
            padding-bottom: 80px;
            background: radial-gradient(circle at top right, rgba(79, 70, 229, 0.1), transparent 40%),
                        radial-gradient(circle at bottom left, rgba(147, 51, 234, 0.05), transparent 40%);
            overflow: hidden;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            align-items: center;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 24px;
            letter-spacing: -0.02em;
        }

        .hero-content p {
            font-size: 1.25rem;
            color: var(--text-muted);
            margin-bottom: 32px;
            max-width: 90%;
        }

        .hero-btns { display: flex; gap: 16px; }

        /* Dashboard Preview Mockup */
        .hero-visual {
            position: relative;
            perspective: 1000px;
        }

        .dashboard-mockup {
            background: white;
            border-radius: var(--radius-md);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
            border: 1px solid rgba(0,0,0,0.05);
            transform: rotateY(-10deg) rotateX(5deg);
            transition: transform 0.5s ease;
            overflow: hidden;
        }

        .dashboard-mockup:hover {
            transform: rotateY(0) rotateX(0) scale(1.02);
            box-shadow: 0 30px 60px rgba(0,0,0,0.2);
        }
        
        .mockup-header {
            height: 30px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            padding: 0 12px;
            gap: 6px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .dot { width: 10px; height: 10px; border-radius: 50%; }
        .dot.red { background: #EF4444; }
        .dot.yellow { background: #F59E0B; }
        .dot.green { background: #10B981; }

        /* --- 5. FEATURES --- */
        .features { background: var(--bg-off); }
        
        .section-header { text-align: center; margin-bottom: 60px; }
        .section-header h2 { font-size: 2.5rem; margin-bottom: 16px; }
        .section-header p { color: var(--text-muted); font-size: 1.1rem; }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
        }

        .feature-card {
            background: white;
            padding: 32px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border: 1px solid transparent;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(79, 70, 229, 0.1);
        }

        .icon-box {
            width: 56px;
            height: 56px;
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 24px;
            transition: var(--transition);
        }

        .feature-card:hover .icon-box {
            background: var(--primary);
            color: white;
            transform: rotate(10deg);
        }

        .feature-card h3 { font-size: 1.25rem; margin-bottom: 12px; }
        .feature-card p { color: var(--text-muted); font-size: 0.95rem; }

        /* --- 6. HOW IT WORKS --- */
        .steps-container {
            display: flex;
            justify-content: space-between;
            position: relative;
            flex-wrap: wrap;
            gap: 20px;
        }

        .steps-container::before {
            content: '';
            position: absolute;
            top: 40px;
            left: 10%;
            right: 10%;
            height: 2px;
            background: #e5e7eb;
            z-index: 0;
        }

        .step {
            position: relative;
            z-index: 1;
            flex: 1;
            text-align: center;
            min-width: 250px;
        }

        .step-number {
            width: 80px;
            height: 80px;
            background: white;
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 auto 24px;
            transition: var(--transition);
        }

        .step:hover .step-number {
            background: var(--primary);
            color: white;
            transform: scale(1.1);
        }

        /* --- 7. CTA SECTION --- */
        .cta-section {
            background: var(--bg-dark);
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: var(--primary);
            filter: blur(100px);
            opacity: 0.2;
            top: -100px;
            left: -100px;
            border-radius: 50%;
        }

        .cta-content { position: relative; z-index: 1; }
        .cta-content h2 { color: white; margin-bottom: 20px; font-size: 2.5rem; }
        .cta-content p { color: #9CA3AF; margin-bottom: 32px; font-size: 1.1rem; }

        /* --- 8. FOOTER --- */
        footer {
            background: #111827;
            color: #D1D5DB;
            padding: 60px 0 20px;
            border-top: 1px solid #374151;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-col h4 { color: white; margin-bottom: 20px; font-size: 1.1rem; }
        .footer-col ul li { margin-bottom: 12px; }
        .footer-col ul li a:hover { color: var(--primary); padding-left: 5px; }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #374151;
            font-size: 0.9rem;
            color: #6B7280;
        }

        /* --- ANIMATIONS --- */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 992px) {
            .hero-grid { grid-template-columns: 1fr; text-align: center; }
            .hero-content p { margin: 0 auto 32px; }
            .hero-btns { justify-content: center; }
            .steps-container::before { display: none; }
        }

        @media (max-width: 768px) {
            :root { --nav-height: 70px; }
            .nav-links, .auth-buttons {
                display: none;
                flex-direction: column;
                position: absolute;
                top: var(--nav-height);
                left: 0;
                width: 100%;
                background: white;
                padding: 24px;
                box-shadow: var(--shadow-md);
                border-top: 1px solid #eee;
            }
            
            .nav-links.active, .auth-buttons.active { display: flex; }
            
            .mobile-toggle { display: block; }
            
            .hero-content h1 { font-size: 2.5rem; }
            .footer-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <nav class="navbar" id="navbar">
        <div class="container nav-container">
            <a href="#" class="logo">
                <i class="ri-book-mark-fill"></i>
                <span>LibManage</span>
            </a>

            <ul class="nav-links" id="navLinks">
                <li><a href="#hero" class="nav-link active">Home</a></li>
                <li><a href="#features" class="nav-link">Features</a></li>
                <li><a href="#how-it-works" class="nav-link">How It Works</a></li>
                <li><a href="#contact" class="nav-link">Contact</a></li>
                
                <li class="mobile-only" style="margin-top: 20px;">
                     <a href="/login" class="btn btn-outline" style="width:100%">Login</a>
                </li>
                 <li class="mobile-only" style="margin-top: 10px;">
                     <a href="/register" class="btn btn-primary" style="width:100%">Sign Up</a>
                </li>
            </ul>

            <div class="auth-buttons desktop-only">
                <a href="/login" class="btn btn-outline">Login</a>
                <a href="/register" class="btn btn-primary">Sign Up</a>
            </div>

            <div class="mobile-toggle" id="mobileToggle">
                <i class="ri-menu-line"></i>
            </div>
        </div>
    </nav>

    <header id="hero" class="hero">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content reveal">
                    <h1>Smart Library Management, <span class="gradient-text">Made Simple.</span></h1>
                    <p>Manage books, students, staff, borrowing, fines, and analytics — all in one secure platform designed for modern institutions.</p>
                    <div class="hero-btns">
                        <a href="/register" class="btn btn-primary">Sign Up Free <i class="ri-arrow-right-line" style="margin-left:8px"></i></a>
                        <a href="/login" class="btn btn-outline">Login</a>
                    </div>
                    <div style="margin-top: 30px; font-size: 0.9rem; color: var(--text-muted);">
                        <i class="ri-checkbox-circle-fill" style="color: #10B981; margin-right: 5px;"></i> No credit card required &nbsp;
                        <i class="ri-checkbox-circle-fill" style="color: #10B981; margin-right: 5px;"></i> 14-day free trial
                    </div>
                </div>

                <div class="hero-visual reveal">
                    <div class="dashboard-mockup">
                        <div class="mockup-header">
                            <div class="dot red"></div>
                            <div class="dot yellow"></div>
                            <div class="dot green"></div>
                        </div>
                        
                        <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="LMS Dashboard Preview" style="width: 100%; height: auto; display: block;">
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section id="features" class="features section-padding">
        <div class="container">
            <div class="section-header reveal">
                <h2>Everything you need to run a library</h2>
                <p>Powerful features to automate your workflow and improve efficiency.</p>
            </div>

            <div class="features-grid">
                <div class="feature-card reveal">
                    <div class="icon-box">
                        <i class="ri-book-open-line"></i>
                    </div>
                    <h3>Book Management</h3>
                    <p>Catalog books with ISBN scanning, manage categories, authors, and digital assets effortlessly.</p>
                </div>

                <div class="feature-card reveal">
                    <div class="icon-box">
                        <i class="ri-user-follow-line"></i>
                    </div>
                    <h3>Member Records</h3>
                    <p>Track students and staff securely. Manage digital ID cards and borrowing history in one place.</p>
                </div>

                <div class="feature-card reveal">
                    <div class="icon-box">
                        <i class="ri-arrow-left-right-line"></i>
                    </div>
                    <h3>Issue & Return</h3>
                    <p>One-click issue and return system. Automatic calculation of due dates and penalty alerts.</p>
                </div>

                <div class="feature-card reveal">
                    <div class="icon-box">
                        <i class="ri-pie-chart-2-line"></i>
                    </div>
                    <h3>Smart Analytics</h3>
                    <p>Visual reports on most borrowed books, overdue fines, and monthly library usage statistics.</p>
                </div>
                
                 <div class="feature-card reveal">
                    <div class="icon-box">
                        <i class="ri-notification-3-line"></i>
                    </div>
                    <h3>Auto Notifications</h3>
                    <p>Send automated email and SMS reminders for due dates, fines, and new arrivals.</p>
                </div>
                
                <div class="feature-card reveal">
                    <div class="icon-box">
                        <i class="ri-shield-check-line"></i>
                    </div>
                    <h3>Role Based Access</h3>
                    <p>Secure login for Admin, Librarian, and Students with specific permission levels.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="section-padding">
        <div class="container">
            <div class="section-header reveal">
                <h2>How It Works</h2>
                <p>Get your library digital in three simple steps.</p>
            </div>

            <div class="steps-container">
                <div class="step reveal">
                    <div class="step-number">1</div>
                    <h3>Create Account</h3>
                    <p>Sign up as an institution administrator and set up your library profile.</p>
                </div>
                <div class="step reveal">
                    <div class="step-number">2</div>
                    <h3>Add Data</h3>
                    <p>Import books via CSV or ISBN and register your staff and student members.</p>
                </div>
                <div class="step reveal">
                    <div class="step-number">3</div>
                    <h3>Start Managing</h3>
                    <p>Issue books, track returns, and generate reports from your dashboard.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding" style="background: #F3F4F6;">
        <div class="container">
            <div class="section-header reveal">
                <h2>Trusted by Institutions</h2>
            </div>
            <div class="features-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
                <div class="feature-card reveal" style="text-align: left;">
                    <div style="color: #F59E0B; margin-bottom: 10px;">
                        <i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i>
                    </div>
                    <p style="font-style: italic; margin-bottom: 20px;">"This system transformed how we handle our university library. The analytics are a game changer for budgeting."</p>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 40px; height: 40px; background: #ddd; border-radius: 50%; overflow: hidden;">
                             <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="User">
                        </div>
                        <div>
                            <h4 style="font-size: 0.9rem;">Sarah Jenkins</h4>
                            <small style="color: var(--text-muted);">Head Librarian, City College</small>
                        </div>
                    </div>
                </div>

                <div class="feature-card reveal" style="text-align: left;">
                    <div style="color: #F59E0B; margin-bottom: 10px;">
                        <i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i>
                    </div>
                    <p style="font-style: italic; margin-bottom: 20px;">"The barcode scanning feature saves us hours every week. Highly recommended for high schools."</p>
                    <div style="display: flex; align-items: center; gap: 10px;">
                         <div style="width: 40px; height: 40px; background: #ddd; border-radius: 50%; overflow: hidden;">
                             <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User">
                        </div>
                        <div>
                            <h4 style="font-size: 0.9rem;">Mark Davies</h4>
                            <small style="color: var(--text-muted);">Principal, St. Mary High</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="cta" class="cta-section section-padding">
        <div class="container cta-content reveal">
            <h2>Digitize Your Library Today</h2>
            <p>Join hundreds of institutions modernizing their library experience.</p>
            <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <a href="/register" class="btn btn-primary" style="background: white; color: var(--primary);">Get Started Now</a>
                <a href="/login" class="btn btn-outline" style="border-color: white; color: white;">Admin Login</a>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <a href="#" class="logo" style="color: white; margin-bottom: 20px; display: inline-block;">
                        <i class="ri-book-mark-fill" style="color: var(--primary);"></i> LibManage
                    </a>
                    <p style="font-size: 0.9rem; color: #9CA3AF; line-height: 1.6;">
                        Secure, efficient, and user-friendly library management software for educational institutions worldwide.
                    </p>
                </div>
                <div class="footer-col">
                    <h4>Product</h4>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                        <li><a href="#">Updates</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#contact">Contact Us</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Account</h4>
                    <ul>
                        <li><a href="/login">Admin Login</a></li>
                        <li><a href="/login">Student Login</a></li>
                        <li><a href="/register">Create Account</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; 2023 Library Management System. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        // 1. Mobile Menu Toggle
        const mobileToggle = document.getElementById('mobileToggle');
        const navLinks = document.getElementById('navLinks');
        const navbar = document.getElementById('navbar');

        mobileToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            const icon = mobileToggle.querySelector('i');
            if(navLinks.classList.contains('active')) {
                icon.classList.replace('ri-menu-line', 'ri-close-line');
            } else {
                icon.classList.replace('ri-close-line', 'ri-menu-line');
            }
        });

        // 2. Sticky Navbar Effect
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // 3. Scroll Reveal Animation using Intersection Observer
        const revealElements = document.querySelectorAll('.reveal');

        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    observer.unobserve(entry.target); // Only animate once
                }
            });
        }, {
            root: null,
            threshold: 0.15, // Trigger when 15% of element is visible
        });

        revealElements.forEach(el => revealObserver.observe(el));

        // 4. Smooth Scroll for Anchor Links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                navLinks.classList.remove('active'); // Close mobile menu on click
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>