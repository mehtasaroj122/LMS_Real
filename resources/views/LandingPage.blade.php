<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibraFlow | Smart Library Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* Custom Properties & Transitions */
:root {
    --glass-bg: rgba(255, 255, 255, 0.7);
    --glass-border: rgba(255, 255, 255, 0.3);
}

.dark {
    --glass-bg: rgba(15, 23, 42, 0.7);
    --glass-border: rgba(255, 255, 255, 0.05);
}

* {
    font-family: 'Inter', sans-serif;
}

/* Glassmorphism utility */
.glass-card {
    background: var(--glass-bg);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid var(--glass-border);
}

/* Navbar Scroll Effect */
#navbar.scrolled {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    height: 70px;
    border-color: var(--glass-border);
    box-shadow: 0 10px 30px -10px rgba(0,0,0,0.1);
}

/* Nav Link Animation */
.nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(to right, #2563eb, #9333ea);
    transition: width 0.3s ease;
}

.nav-link:hover::after {
    width: 100%;
}

/* Reveal Animations */
.reveal {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
}

.reveal.active {
    opacity: 1;
    transform: translateY(0);
}

/* Floating Animations */
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

.animate-float { animation: float 6s ease-in-out infinite; }
.animate-float-delayed { animation: float 6s ease-in-out infinite 2s; }

/* Feature Card Hover */
.feature-card {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.feature-card:hover {
    transform: translateY(-10px) rotateX(5deg) rotateY(2deg);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
}

/* Add to previous CSS */

/* Grid Pattern Background for sections */
#features {
    background-image: radial-gradient(#cbd5e1 0.5px, transparent 0.5px);
    background-size: 24px 24px;
}
.dark #features {
    background-image: radial-gradient(#1e293b 1px, transparent 1px);
}

/* Enhanced Reveal Delays */
.delay-100 { transition-delay: 100ms; }
.delay-200 { transition-delay: 200ms; }
.delay-300 { transition-delay: 300ms; }

/* Timeline Hover Effect */
.how-it-works-step:hover .step-number {
    transform: scale(1.1) rotate(5deg);
    filter: brightness(1.2);
}

/* Smooth Icon Hover */
.feature-card i {
    transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.feature-card:hover i {
    transform: scale(1.2) translateY(-2px);
}

/* Accessibility: Focus visible */
:focus-visible {
    outline: 2px solid #2563eb;
    outline-offset: 4px;
}
    </style>
</head>
<body class="transition-colors duration-300 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100">

    <div id="progress-bar" class="fixed top-0 left-0 h-1 bg-gradient-to-r from-blue-600 to-purple-600 z-[100] transition-all duration-150"></div>

    <nav id="navbar" class="fixed z-50 w-full transition-all duration-300 border-b border-transparent">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center gap-2 cursor-pointer group">
                    <div class="p-2 transition-transform bg-blue-600 rounded-lg group-hover:rotate-12">
                        <i data-lucide="book-open" class="text-white"></i>
                    </div>
                    <span class="text-xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">LibraFlow</span>
                </div>

                <div class="items-center hidden space-x-8 md:flex">
                    <a href="#home" class="relative py-1 nav-link">Home</a>
                    <a href="#features" class="relative py-1 nav-link">Features</a>
                    <a href="#modules" class="relative py-1 nav-link">How It Works</a>
                    <a href="#pricing" class="relative py-1 nav-link">Pricing</a>
                    <button id="theme-toggle" class="p-2 transition-colors rounded-full hover:bg-slate-200 dark:hover:bg-slate-800">
                        <i data-lucide="sun" class="hidden dark:block"></i>
                        <i data-lucide="moon" class="block dark:hidden"></i>
                    </button>
                </div>

                <div class="flex items-center gap-4">
                    <a href="{{ route('login') }}" class="px-5 py-2 transition-all border rounded-lg border-blue-600/50 hover:bg-blue-600/10 hover:scale-105 active:scale-95">Login</a>
                    <a href="{{ route('register') }}" class="px-6 py-2 font-semibold text-white transition-all rounded-full shadow-lg bg-gradient-to-r from-blue-600 to-purple-600 shadow-blue-500/30 hover:shadow-blue-500/50 hover:scale-105 active:scale-95">Sign Up</a>
                </div>
            </div>
        </div>
    </nav>

    <section id="home" class="relative pt-32 pb-20 overflow-hidden">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-500/10 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-[10%] right-[-5%] w-[30%] h-[30%] bg-purple-500/10 blur-[120px] rounded-full"></div>
        
        <div class="flex flex-col items-center gap-12 px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 lg:flex-row">
            <div class="flex-1 reveal">
                <h1 class="mb-6 text-5xl font-extrabold leading-tight lg:text-7xl">
                    Smart Library <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-teal-400">Management</span> Made Simple
                </h1>
                <p class="max-w-xl mb-8 text-lg text-slate-600 dark:text-slate-400">
                    Manage books, students, borrowing, and reports with a fast, secure, cloud-based system. Designed for the modern academic ecosystem.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('register') }}" class="px-8 py-4 font-bold text-white transition-all shadow-xl bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl hover:shadow-blue-500/40 hover:-translate-y-1">Get Started Now</a>
                    <button class="flex items-center gap-2 px-8 py-4 font-bold transition-all border border-slate-300 dark:border-slate-700 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">
                        <i data-lucide="play-circle"></i> View Demo
                    </button>
                </div>
            </div>

            <div class="relative flex-1 delay-200 reveal">
                <div class="relative z-10 p-4 border shadow-2xl glass-card rounded-2xl border-white/20">
                    <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&q=80&w=1000" alt="Dashboard Preview" class="shadow-inner rounded-xl">
                    
                    <div class="absolute p-4 shadow-xl -top-6 -right-6 glass-card rounded-xl animate-float">
                        <p class="text-xs text-slate-500">Total Books</p>
                        <p class="text-2xl font-bold counter" data-target="12840">0</p>
                    </div>
                    <div class="absolute p-4 shadow-xl -bottom-8 -left-8 glass-card rounded-xl animate-float-delayed">
                        <p class="text-xs text-slate-500">Active Members</p>
                        <p class="text-2xl font-bold counter" data-target="3250">0</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

   <section id="features" class="relative py-24 overflow-hidden bg-white dark:bg-slate-950">
    <div class="relative z-10 px-4 mx-auto max-w-7xl">
        <div class="max-w-3xl mx-auto mb-20 text-center reveal">
            <h2 class="mb-3 text-sm font-bold tracking-widest text-indigo-600 uppercase">Powerful Tools</h2>
            <h3 class="mb-6 text-4xl font-extrabold lg:text-5xl dark:text-white">Everything you need to run a modern library</h3>
            <p class="text-lg text-slate-600 dark:text-slate-400">Powerful tools designed to automate daily operations, reduce manual work, and give you complete control over your library ecosystem.</p>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            <div class="p-8 border feature-card glass-card rounded-3xl border-slate-200 dark:border-white/10 reveal">
                <div class="flex items-center justify-center w-12 h-12 mb-6 text-blue-600 bg-blue-500/10 rounded-xl">
                    <i data-lucide="book-copy"></i>
                </div>
                <h4 class="mb-4 text-xl font-bold dark:text-white">Book Management</h4>
                <ul class="space-y-3 text-sm text-slate-500 dark:text-slate-400">
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Bulk import via ISBN/CSV</li>
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Category & Author tracking</li>
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Digital asset & PDF storage</li>
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Real-time availability status</li>
                </ul>
            </div>

            <div class="p-8 delay-100 border feature-card glass-card rounded-3xl border-slate-200 dark:border-white/10 reveal">
                <div class="flex items-center justify-center w-12 h-12 mb-6 text-purple-600 bg-purple-500/10 rounded-xl">
                    <i data-lucide="users"></i>
                </div>
                <h4 class="mb-4 text-xl font-bold dark:text-white">Member Records</h4>
                <ul class="space-y-3 text-sm text-slate-500 dark:text-slate-400">
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Digital ID cards & history</li>
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Student & Staff profiles</li>
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Fine history tracking</li>
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Role-based data security</li>
                </ul>
            </div>

            <div class="p-8 delay-200 border feature-card glass-card rounded-3xl border-slate-200 dark:border-white/10 reveal">
                <div class="flex items-center justify-center w-12 h-12 mb-6 text-teal-600 bg-teal-500/10 rounded-xl">
                    <i data-lucide="refresh-cw"></i>
                </div>
                <h4 class="mb-4 text-xl font-bold dark:text-white">Issue & Return System</h4>
                <ul class="space-y-3 text-sm text-slate-500 dark:text-slate-400">
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> One-click workflow</li>
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Auto due-date calculation</li>
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Real-time overdue tracking</li>
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Detailed transaction logs</li>
                </ul>
            </div>

            <div class="p-8 border feature-card glass-card rounded-3xl border-slate-200 dark:border-white/10 reveal">
                <div class="flex items-center justify-center w-12 h-12 mb-6 text-orange-600 bg-orange-500/10 rounded-xl">
                    <i data-lucide="bar-chart-3"></i>
                </div>
                <h4 class="mb-4 text-xl font-bold dark:text-white">Smart Analytics</h4>
                <ul class="space-y-3 text-sm text-slate-500 dark:text-slate-400">
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Popular category insights</li>
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> PDF/Excel report export</li>
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Monthly usage stats</li>
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Fine summary dashboards</li>
                </ul>
            </div>

            <div class="p-8 delay-100 border feature-card glass-card rounded-3xl border-slate-200 dark:border-white/10 reveal">
                <div class="flex items-center justify-center w-12 h-12 mb-6 text-pink-600 bg-pink-500/10 rounded-xl">
                    <i data-lucide="bell"></i>
                </div>
                <h4 class="mb-4 text-xl font-bold dark:text-white">Automated Notifications</h4>
                <ul class="space-y-3 text-sm text-slate-500 dark:text-slate-400">
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> SMS & Email due reminders</li>
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> New book arrival alerts</li>
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Penalty/Fine notifications</li>
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Customizable templates</li>
                </ul>
            </div>

            <div class="p-8 delay-200 border feature-card glass-card rounded-3xl border-slate-200 dark:border-white/10 reveal">
                <div class="flex items-center justify-center w-12 h-12 mb-6 text-indigo-600 bg-indigo-500/10 rounded-xl">
                    <i data-lucide="shield-check"></i>
                </div>
                <h4 class="mb-4 text-xl font-bold dark:text-white">Secure Access</h4>
                <ul class="space-y-3 text-sm text-slate-500 dark:text-slate-400">
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Admin & Student portals</li>
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Permission-based actions</li>
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Session & Auth handling</li>
                    <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 mt-1 text-teal-500"></i> Full system activity logs</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section id="modules" class="py-24 overflow-hidden bg-slate-50 dark:bg-slate-900">
    <div class="px-4 mx-auto max-w-7xl">
        <div class="mb-20 text-center reveal">
            <h3 class="mb-4 text-4xl font-bold dark:text-white">How It Works</h3>
            <p class="text-slate-500">Get your library digital in three simple steps</p>
        </div>

        <div class="relative">
            <div class="hidden lg:block absolute left-1/2 top-0 bottom-0 w-0.5 bg-slate-200 dark:bg-slate-800 -translate-x-1/2"></div>

            <div class="flex flex-col items-center justify-between mb-20 lg:flex-row reveal">
                <div class="mb-8 text-center lg:w-5/12 lg:text-right lg:mb-0">
                    <h4 class="mb-3 text-2xl font-bold dark:text-white">1. Create Account</h4>
                    <p class="text-slate-500">Sign up as an institution administrator and set up your digital library policies instantly.</p>
                </div>
                <div class="relative z-10 flex items-center justify-center w-16 h-16 text-xl font-bold text-white bg-blue-600 rounded-full shadow-xl shadow-blue-500/30">1</div>
                <div class="p-6 mt-8 border lg:w-5/12 lg:mt-0 glass-card rounded-2xl border-white/10">
                    <ul class="space-y-2 text-sm text-slate-500 dark:text-slate-400">
                        <li>• Register your institution securely</li>
                        <li>• Configure library policies</li>
                        <li>• Access personalized dashboard</li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col items-center justify-between mb-20 lg:flex-row-reverse reveal">
                <div class="mb-8 text-center lg:w-5/12 lg:text-left lg:mb-0">
                    <h4 class="mb-3 text-2xl font-bold dark:text-white">2. Add Data</h4>
                    <p class="text-slate-500">Bring your library data into the system effortlessly using automated scanners and CSV tools.</p>
                </div>
                <div class="relative z-10 flex items-center justify-center w-16 h-16 text-xl font-bold text-white bg-purple-600 rounded-full shadow-xl shadow-purple-500/30">2</div>
                <div class="p-6 mt-8 border lg:w-5/12 lg:mt-0 glass-card rounded-2xl border-white/10">
                    <ul class="space-y-2 text-sm text-slate-500 dark:text-slate-400">
                        <li>• Import books using ISBN scanning</li>
                        <li>• Add students and staff members</li>
                        <li>• Assign roles and permissions</li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col items-center justify-between lg:flex-row reveal">
                <div class="mb-8 text-center lg:w-5/12 lg:text-right lg:mb-0">
                    <h4 class="mb-3 text-2xl font-bold dark:text-white">3. Start Managing</h4>
                    <p class="text-slate-500">Begin day-to-day operations with full control and automated tracking of every transaction.</p>
                </div>
                <div class="relative z-10 flex items-center justify-center w-16 h-16 text-xl font-bold text-white bg-teal-500 rounded-full shadow-xl shadow-teal-500/30">3</div>
                <div class="p-6 mt-8 border lg:w-5/12 lg:mt-0 glass-card rounded-2xl border-white/10">
                    <ul class="space-y-2 text-sm text-slate-500 dark:text-slate-400">
                        <li>• Issue & return with one click</li>
                        <li>• Real-time activity monitoring</li>
                        <li>• Automated fine alerts & reports</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

    <section class="relative py-24 overflow-hidden text-center border-t bg-slate-950 border-white/5">
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-blue-600/10 blur-[120px] rounded-full"></div>
    
    <div class="relative z-10 max-w-4xl px-4 mx-auto reveal">
        <h2 class="mb-6 text-4xl font-extrabold text-white md:text-6xl">Digitize Your Library Today</h2>
        <p class="max-w-2xl mx-auto mb-10 text-lg text-slate-400 md:text-xl">
            Join hundreds of institutions modernizing their library experience.
        </p>
        
        <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
            <a href="{{ route('register') }}" class="w-full px-10 py-4 font-bold transition-all bg-white shadow-xl sm:w-auto text-slate-900 rounded-xl hover:bg-slate-100 hover:scale-105 active:scale-95 shadow-white/10">
                Get Started Now
            </a>
            <a href="{{ route('login') }}" class="w-full px-10 py-4 font-bold text-white transition-all bg-transparent border-2 sm:w-auto border-slate-700 rounded-xl hover:border-white hover:bg-white/5">
                Admin Login
            </a>
        </div>
    </div>
</section>

<footer class="pt-20 pb-10 border-t bg-slate-950 border-white/5">
    <div class="px-4 mx-auto max-w-7xl lg:px-8">
        <div class="grid grid-cols-1 gap-12 mb-16 md:grid-cols-2 lg:grid-cols-4">
            <div class="col-span-1">
                <div class="flex items-center gap-2 mb-6">
                    <div class="p-2 bg-blue-600 rounded-lg">
                        <i data-lucide="book-open" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="text-2xl font-extrabold tracking-tight text-white">LibManage</span>
                </div>
                <p class="max-w-xs leading-relaxed text-slate-400">
                    Secure, efficient, and user-friendly library management software for educational institutions worldwide.
                </p>
            </div>

            <div>
                <h4 class="mb-6 text-sm font-bold tracking-wider text-white uppercase">Product</h4>
                <ul class="space-y-4 text-slate-400">
                    <li><a href="#features" class="transition-colors hover:text-blue-500">Features</a></li>
                    <li><a href="#pricing" class="transition-colors hover:text-blue-500">Pricing</a></li>
                    <li><a href="#" class="transition-colors hover:text-blue-500">Updates</a></li>
                </ul>
            </div>

            <div>
                <h4 class="mb-6 text-sm font-bold tracking-wider text-white uppercase">Support</h4>
                <ul class="space-y-4 text-slate-400">
                    <li><a href="#" class="transition-colors hover:text-blue-500">Help Center</a></li>
                    <li><a href="#" class="transition-colors hover:text-blue-500">Documentation</a></li>
                    <li><a href="#" class="transition-colors hover:text-blue-500">Contact Us</a></li>
                </ul>
            </div>

            <div>
                <h4 class="mb-6 text-sm font-bold tracking-wider text-white uppercase">Account</h4>
                <ul class="space-y-4 text-slate-400">
                    <li><a href="{{ route('login') }}" class="transition-colors hover:text-blue-500">Admin Login</a></li>
                    <li><a href="{{ route('login') }}" class="transition-colors hover:text-blue-500">Student Login</a></li>
                    <li><a href="{{ route('register') }}" class="transition-colors hover:text-blue-500">Create Account</a></li>
                </ul>
            </div>
        </div>

        <div class="flex flex-col items-center justify-between gap-4 pt-8 text-sm border-t border-white/5 md:flex-row text-slate-500">
            <p>© 2026 Library Management System. All rights reserved.</p>
            <div class="flex gap-6">
                <a href="#" class="transition-colors hover:text-white"><i data-lucide="twitter" class="w-5 h-5"></i></a>
                <a href="#" class="transition-colors hover:text-white"><i data-lucide="linkedin" class="w-5 h-5"></i></a>
                <a href="#" class="transition-colors hover:text-white"><i data-lucide="github" class="w-5 h-5"></i></a>
            </div>
        </div>
    </div>
</footer>

    <script>
        // Initialize Icons
lucide.createIcons();

// Navbar Scroll Effect
window.addEventListener('scroll', () => {
    const navbar = document.getElementById('navbar');
    const progressBar = document.getElementById('progress-bar');
    
    // Header shadow and height
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }

    // Scroll Progress
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const scrolled = (winScroll / height) * 100;
    progressBar.style.width = scrolled + "%";
});

// Reveal on Scroll Logic
const reveal = () => {
    const reveals = document.querySelectorAll('.reveal');
    reveals.forEach(element => {
        const windowHeight = window.innerHeight;
        const elementTop = element.getBoundingClientRect().top;
        const elementVisible = 150;
        if (elementTop < windowHeight - elementVisible) {
            element.classList.add('active');
        }
    });
};

window.addEventListener('scroll', reveal);
reveal(); // Initial check

// Animated Counter Logic
const counters = document.querySelectorAll('.counter');
const speed = 200;

counters.forEach(counter => {
    const animate = () => {
        const value = +counter.getAttribute('data-target');
        const data = +counter.innerText;
        const time = value / speed;
        if (data < value) {
            counter.innerText = Math.ceil(data + time);
            setTimeout(animate, 10);
        } else {
            counter.innerText = value.toLocaleString();
        }
    };
    
    // Start counter when visible
    const observer = new IntersectionObserver((entries) => {
        if(entries[0].isIntersecting) animate();
    });
    observer.observe(counter);
});

// Theme Toggle
const themeToggle = document.getElementById('theme-toggle');
const html = document.documentElement;

themeToggle.addEventListener('click', () => {
    html.classList.toggle('dark');
    const isDark = html.classList.contains('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
});

// Check for saved theme
if (localStorage.getItem('theme') === 'dark' || 
    (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    html.classList.add('dark');
}
    </script>
</body>
</html>