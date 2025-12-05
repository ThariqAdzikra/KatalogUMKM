<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laptop Store'))</title>

    {{-- Favicon & Theme Color --}}
    <link rel="icon" type="image/png" href="{{ asset(App\Models\SiteSetting::get('logo_path', 'images/logo.png')) }}">
    <link rel="apple-touch-icon" href="{{ asset(App\Models\SiteSetting::get('logo_path', 'images/logo.png')) }}">
    <meta name="theme-color" content="#0a0e27">
    <meta name="msapplication-TileColor" content="#0a0e27">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- CSS Global Dimuat dari file eksternal (public/css/app.css) --}}
    <link rel="stylesheet" href="/css/layouts/app.css">
    <link rel="stylesheet" href="/css/global-premium.css">
    <link rel="stylesheet" href="/css/manajemen/style.css">
    
    {{-- Stack untuk CSS spesifik per halaman --}}
    @stack('styles')

    {{-- Blok <style> inline sudah dihapus --}}
    <style>
        /* Guest Layout Adjustments */
        body.layout-guest main.main-content {
            margin-left: 0 !important;
            padding-top: 100px; /* Space for fixed navbar */
        }
        
        body.layout-guest footer {
            margin-left: 0 !important;
        }

        /* Navbar Glassmorphism */
        .navbar-glass {
            background: rgba(10, 14, 39, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(59, 130, 246, 0.2);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .navbar-glass .nav-link {
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar-glass .nav-link:hover {
            color: #3b82f6;
        }

        .navbar-glass .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #3b82f6;
            transition: width 0.3s ease;
        }

        .navbar-glass .nav-link:hover::after {
            width: 100%;
        }

        /* Premium Login Button - Matches .btn-detail */
        .btn-login-premium {
            background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
            color: white !important;
            padding: 0.6rem 1.5rem;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: background 0.3s ease;
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
            letter-spacing: 0.3px;
        }

        .btn-login-premium:hover {
            /* Lighter cyan - contrasting color change only */
            background: linear-gradient(135deg, #60a5fa 0%, #22d3ee 100%);
            color: white !important;
        }
    </style>
</head>
<body>
    @unless(View::hasSection('hide_navbar'))
    {{-- Navbar for Guests --}}
    @guest
    <nav class="navbar navbar-expand-lg fixed-top" style="background: rgba(10, 14, 39, 0.95); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(59, 130, 246, 0.2); z-index: 1050;">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                <img src="{{ asset(App\Models\SiteSetting::get('logo_path', 'images/logo.png')) }}" alt="Logo" style="height: 40px; margin-right: 10px; filter: drop-shadow(0 0 5px rgba(59, 130, 246, 0.5));">
                <span class="fw-bold text-white" style="font-size: 1.25rem; letter-spacing: 0.5px;">{{ App\Models\SiteSetting::get('brand_name', 'LaptopPremium') }}</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#guestNavbar" style="border-color: rgba(255,255,255,0.1);">
                <i class="bi bi-list text-white fs-2"></i>
            </button>
            <div class="collapse navbar-collapse" id="guestNavbar">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="btn-login-premium">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    @endguest
    @endunless


    {{-- Premium SaaS Sidebar (Only for Auth Users) --}}
    @auth
    <aside class="premium-sidebar" id="sidebar">
        {{-- Logo Section with Toggle Button --}}
        <div class="sidebar-logo">
            <a href="{{ Auth::user()->role == 'superadmin' ? route('dashboard') : route('home') }}" 
               class="d-flex align-items-center text-decoration-none">
                <span class="sidebar-logo-text">{{ App\Models\SiteSetting::get('brand_name', 'LaptopPremium') }}</span>
            </a>
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
        </div>

        {{-- Navigation Menu --}}
        <nav class="sidebar-nav">
            {{-- Main Menu Section --}}
            <div class="nav-section">
                <span class="nav-section-title">MAIN MENU</span>
            </div>

            {{-- Dashboard/Katalog (Role-based) --}}
            @if(Auth::user()->role == 'superadmin')
                <a href="{{ route('dashboard') }}" 
                   class="nav-item {{ request()->is('superadmin/dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                    <div class="nav-item-glow"></div>
                </a>
            @else
                <a href="{{ route('katalog.index') }}" 
                   class="nav-item {{ (request()->routeIs('katalog.*') || request()->routeIs('stok.show')) ? 'active' : '' }}">
                    <i class="bi bi-laptop"></i>
                    <span>Katalog</span>
                    <div class="nav-item-glow"></div>
                </a>
            @endif

            {{-- Kasir/Pegawai (Role-based) --}}
            @if(Auth::user()->role == 'superadmin')
                <a href="{{ route('pegawai.index') }}" 
                   class="nav-item {{ request()->routeIs('pegawai.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Pegawai</span>
                    <div class="nav-item-glow"></div>
                </a>
            @else
                <a href="{{ route('penjualan.create') }}" 
                   class="nav-item {{ request()->routeIs('penjualan.create') ? 'active' : '' }}">
                    <i class="bi bi-cash-register"></i>
                    <span>Kasir</span>
                    <div class="nav-item-glow"></div>
                </a>
            @endif

            {{-- Management Section --}}
            <div class="nav-section">
                <span class="nav-section-title">MANAGEMENT</span>
            </div>

            <a href="{{ route('pembelian.index') }}" 
               class="nav-item {{ request()->routeIs('pembelian.*') ? 'active' : '' }}">
                <i class="bi bi-cart-plus"></i>
                <span>Pembelian</span>
                <div class="nav-item-glow"></div>
            </a>

            @if(Auth::user()->role == 'superadmin')
            <a href="{{ route('penjualan.index') }}" 
               class="nav-item {{ (request()->routeIs('penjualan.*') && !request()->routeIs('penjualan.create')) ? 'active' : '' }}">
                <i class="bi bi-receipt"></i>
                <span>Penjualan</span>
                <div class="nav-item-glow"></div>
            </a>
            @endif

            <a href="{{ route('pelanggan.index') }}" 
               class="nav-item {{ request()->routeIs('pelanggan.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i>
                <span>Pelanggan</span>
                <div class="nav-item-glow"></div>
            </a>

            <a href="{{ route('stok.index') }}" 
               class="nav-item {{ (request()->routeIs('stok.*') && !request()->routeIs('stok.show')) ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i>
                <span>Stok</span>
                <div class="nav-item-glow"></div>
            </a>

            {{-- Settings (Superadmin Only) --}}
            @if(Auth::user()->role == 'superadmin')
            <div class="nav-section">
                <span class="nav-section-title">PENGATURAN</span>
            </div>

            <a href="{{ route('superadmin.settings') }}" 
               class="nav-item {{ request()->routeIs('superadmin.settings') ? 'active' : '' }}">
                <i class="bi bi-gear-fill"></i>
                <span>Pengaturan Website</span>
                <div class="nav-item-glow"></div>
            </a>
            @endif
        </nav>

        {{-- Profile Section (Bottom) --}}
        <div class="sidebar-profile">
            <div class="profile-info">
                <div class="profile-avatar">
                    @if(Auth::user()->photo)
                        <img src="{{ asset('storage/'. Auth::user()->photo) }}" 
                             alt="{{ Auth::user()->name }}">
                    @else
                        <i class="bi bi-person-circle"></i>
                    @endif
                </div>
                <div class="profile-details">
                    <div class="profile-name">{{ Str::limit(Auth::user()->name, 15) }}</div>
                    <div class="profile-role">{{ ucfirst(Auth::user()->role) }}</div>
                </div>
            </div>
            <div class="profile-actions">
                <a href="{{ route('profile.edit') }}" class="profile-action" title="Profile">
                    <i class="bi bi-person-gear"></i>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="profile-action" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Mobile Hamburger Button (Only for Auth Users) - Placed after sidebar for CSS sibling selector --}}
    <button class="mobile-hamburger" id="mobileHamburger">
        <i class="bi bi-chevron-right"></i>
    </button>
    @endauth

    {{-- Mobile Overlay --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-content {{ Auth::check() ? '' : 'ms-0 ' . (View::hasSection('hide_navbar') ? '' : 'pt-5 mt-4') }}">
        @yield('content')
    </main>

    {{-- SECTION FOOTER PROFESIONAL (Tata letak 4 kolom) --}}
    @unless(View::hasSection('hide_footer'))
    @guest
    <footer class="py-5 ms-0">
        <div class="container">
            {{-- BARIS 1: LINK UTAMA (4 KOLOM) --}}
            <div class="row g-5">
                
                {{-- Kolom 1: Brand & Socials --}}
                <div class="col-12 col-md-6 col-lg-4">
                    {{-- LOGIKA ROLE FOOTER BRAND --}}
                    <a class="footer-brand" 
                       href="{{ route('home') }}">
                        <img src="{{ asset(App\Models\SiteSetting::get('logo_path', 'images/logo.png')) }}" alt="{{ App\Models\SiteSetting::get('brand_name', 'LaptopPremium') }} Logo" class="logo-filtered">
                        <h5 class="text-white mb-0" style="font-size: 1.5rem; font-weight: 700;">{{ App\Models\SiteSetting::get('brand_name', 'LaptopPremium') }}</h5>
                    </a>
                    <p class="text-muted pe-lg-4 my-4">
                        Toko laptop terpercaya dengan koleksi lengkap dan harga terbaik untuk semua kebutuhan Anda. Memberikan solusi teknologi berkualitas sejak 2020.
                    </p>
                    <div class="social-icons d-flex gap-3">
                        <a href="https://www.facebook.com/people/Laptop-Premium-Pekanbaru/61550669070817/" target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook"></i></a>
                        <a href="https://www.instagram.com/laptoppremiumpku/" target="_blank" rel="noopener noreferrer"><i class="bi bi-instagram"></i></a>
                        <a href="https://wa.me/6282316592733" target="_blank" rel="noopener noreferrer"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>

                {{-- Kolom 2: Menu --}}
                <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                    <h6>Menu</h6>
                    <ul class="list-unstyled">
                        {{-- LOGIKA ROLE FOOTER HOME --}}
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li><a href="{{ route('about') }}">Tentang Kami</a></li>
                    </ul>
                </div>

                {{-- Kolom 3: Kategori --}}
                <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                    <h6>Kategori</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('katalog.index', ['kategori' => 'gaming']) }}">Gaming</a></li>
                        <li><a href="{{ route('katalog.index', ['kategori' => 'office']) }}">Office</a></li>
                        <li><a href="{{ route('katalog.index', ['kategori' => 'ultrabook']) }}">Ultrabook</a></li>
                        <li><a href="{{ route('katalog.index', ['kategori' => 'workstation']) }}">Workstation</a></li>
                    </ul>
                </div>

                {{-- Kolom 4: Kontak --}}
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <h6>Kontak Kami</h6>
                    <ul class="list-unstyled">
                        <li class="d-flex">
                            <i class="bi bi-geo-alt fs-5 me-3"></i>
                            <span class="text-muted">Pekanbaru, Riau, Indonesia</span>
                        </li>
                        <li class="d-flex">
                            <i class="bi bi-telephone fs-5 me-3"></i>
                            <span class="text-muted">+62 823-1659-2733</span>
                        </li>
                        <li class="d-flex">
                            <i class="bi bi-envelope fs-5 me-3"></i>
                            <span class="text-muted">laptopPremium@gmail.com</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- BARIS 2: LOKASI PETA --}}
            <div class="row mt-5">
                <div class="col-12">
                    <h6>LokASI KAMI</h6>
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.6854362125373!2d101.37201277504735!3d0.467626199527809!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31d5a9843387c835%3A0x5c89c63ba8cf2145!2sLaptop%20Premium%20Pekanbaru!5e0!3m2!1sid!2sid!4v1762483568987!5m2!1sid!2sid" 
                        class="w-100" 
                        style="border:0; height: 300px; border-radius: 12px;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

            {{-- BARIS 3: COPYRIGHT --}}
            <hr class="mt-5 mb-4">
            <div class="row copyright-section align-items-center">
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <p class="mb-0">
                        © {{ date('Y') }} {{ App\Models\SiteSetting::get('brand_name', 'LaptopPremium') }}. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="me-3">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>
    @endguest
    @endunless

    {{-- Page Transition Loader --}}
    <div id="page-loader" class="page-loader">
        <div class="loader-content">
            {{-- 3D Laptop Animation --}}
            <div class="loader-laptop-container">
                <div class="laptop-wrapper">
                    <div class="laptop-screen">
                        <div class="screen-glow"></div>
                        <div class="code-lines">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                    <div class="laptop-base"></div>
                    <div class="laptop-shadow"></div>
                </div>
                
                {{-- Floating Particles --}}
                <div class="particles-container">
                    <div class="particle"></div>
                    <div class="particle"></div>
                    <div class="particle"></div>
                    <div class="particle"></div>
                    <div class="particle"></div>
                    <div class="particle"></div>
                </div>
            </div>
            
            {{-- Progress Bar --}}
            <div class="progress-bar-wrapper">
                <div class="progress-fill"></div>
            </div>
            
            {{-- Loading Text with Typing Effect --}}
            <div class="loader-text">
                <span class="typing-text">Memuat</span><span class="dots">...</span>
            </div>
        </div>
    </div>

    <script>
        // Page Loader Logic
        document.addEventListener('DOMContentLoaded', function() {
            const loader = document.getElementById('page-loader');
            
            // Hide loader when page is fully loaded
            window.addEventListener('load', function() {
                setTimeout(() => {
                    loader.classList.add('hidden');
                }, 100); // Short delay for smooth transition
            });

            // CRITICAL: Handle browser back/forward button (bfcache)
            window.addEventListener('pageshow', function(event) {
                // If page is restored from bfcache (back/forward navigation)
                if (event.persisted) {
                    loader.classList.add('hidden');
                } else {
                    // Normal page load
                    setTimeout(() => {
                        loader.classList.add('hidden');
                    }, 100);
                }
            });

            // Hide loader before page is hidden (prevents loader on back)
            window.addEventListener('pagehide', function() {
                loader.classList.add('hidden');
            });

            // Show loader on link clicks (internal links only)
            document.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    const target = this.getAttribute('target');
                    
                    // Check if link is valid, internal, and not opening in new tab
                    if (href && 
                        href.startsWith('/') || 
                        href.startsWith(window.location.origin) &&
                        href !== '#' &&
                        target !== '_blank' &&
                        !e.ctrlKey && !e.metaKey &&
                        !this.classList.contains('page-link') && 
                        !this.classList.contains('ajax-link')) {
                        
                        loader.classList.remove('hidden');
                    }
                });
            });

            // Prevent loader for delete-form button clicks
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('button[type="submit"]');
                if (btn) {
                    const form = btn.closest('form');
                    if (form && form.classList.contains('delete-form')) {
                        // Ensure loader stays hidden for delete forms
                        loader.classList.add('hidden');
                    }
                }
            }, true);

            // Show loader on form submit (except delete-form and forms with onsubmit)
            // Using event delegation to check EARLY if it's a delete-form
            document.addEventListener('submit', function(e) {
                const form = e.target;
                
                // Skip loader for search forms (usually use GET method)
                const isSearchForm = form.method.toLowerCase() === 'get' || 
                                    form.querySelector('input[name*="search"]') ||
                                    form.querySelector('input[name*="query"]');
                
                // Skip loader for delete forms or forms with popup confirmations or search forms
                if (form.classList.contains('delete-form') || 
                    form.hasAttribute('onsubmit') ||
                    form.hasAttribute('data-confirm-message') ||
                    form.hasAttribute('data-skip-loader') ||
                    isSearchForm) {
                    // Force hide loader for these forms
                    loader.classList.add('hidden');
                    return;
                }
                
                loader.classList.remove('hidden');
            }, true); // Use capture phase to run this BEFORE other handlers
        });
    </script>

    {{-- Premium Sidebar Toggle JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileHamburger = document.getElementById('mobileHamburger');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const body = document.body;

            // Exit if sidebar elements don't exist (guest pages)
            if (!sidebar || !sidebarOverlay) {
                return;
            }

            // Load sidebar state from localStorage (only for desktop)
            if (window.innerWidth >= 993) {
                const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                if (isCollapsed) {
                    body.classList.add('sidebar-collapsed');
                }
            }

            // Toggle sidebar (from sidebar toggle button)
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    if (window.innerWidth >= 993) {
                        // Desktop: Toggle collapsed state
                        body.classList.toggle('sidebar-collapsed');
                        const isCollapsed = body.classList.contains('sidebar-collapsed');
                        localStorage.setItem('sidebarCollapsed', isCollapsed);
                    } else {
                        // Mobile: Toggle overlay
                        sidebar.classList.toggle('mobile-open');
                        sidebarOverlay.classList.toggle('active');
                    }
                });
            }

            // Toggle sidebar (from mobile hamburger button)
            if (mobileHamburger) {
                mobileHamburger.addEventListener('click', function() {
                    sidebar.classList.toggle('mobile-open');
                    sidebarOverlay.classList.toggle('active');
                });
            }

            // Close mobile sidebar on overlay click
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('active');
            });

            // Close mobile sidebar on navigation click
            const navItems = sidebar.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (window.innerWidth < 993) {
                        setTimeout(() => {
                            sidebar.classList.remove('mobile-open');
                            sidebarOverlay.classList.remove('active');
                        }, 200);
                    }
                });
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 993) {
                    sidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Stack untuk JS spesifik per halaman --}}
    @stack('scripts')
</body>
</html>