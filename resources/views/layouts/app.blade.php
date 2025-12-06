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
    <link rel="stylesheet" href="/css/notifications.css">
    
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
            {{-- Notification Nav Item (TOP) --}}
            <div class="notification-nav-wrapper" id="notificationNavWrapper">
                <a href="javascript:void(0)" 
                   class="nav-item notification-nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}"
                   id="notificationNavItem"
                   onclick="toggleNotificationPopup(event)">
                    <i class="bi bi-bell"></i>
                    <span>Notifikasi</span>
                    @if(isset($unreadCount) && $unreadCount > 0)
                        <span class="nav-notification-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                    @endif
                    <div class="nav-item-glow"></div>
                </a>
            </div>

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
                    <i class="bi bi-cart-check"></i>
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

    {{-- Notification Popup (OUTSIDE SIDEBAR - at body level) --}}
    <div class="notification-popup" id="notificationPopup">
        <div class="popup-header">
            <h6><i class="bi bi-bell-fill me-2"></i>Notifikasi</h6>
            <form action="{{ route('notifications.read-all') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="mark-all-read" title="Tandai Semua Dibaca">
                    <i class="bi bi-check2-all"></i>
                </button>
            </form>
        </div>
        <div class="popup-body" id="notificationList">
            @if(isset($unreadNotifications) && $unreadNotifications->count() > 0)
            @foreach($unreadNotifications as $notif)
                    <a href="{{ route('notifications.index') }}" class="notification-item {{ $notif->is_read ? 'read' : 'unread' }}">
                        <div class="notification-icon" style="color: {{ $notif->color }};">
                            <i class="bi {{ $notif->icon }}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">{{ $notif->title }}</div>
                            <div class="notification-message">{{ Str::limit($notif->message, 50) }}</div>
                            <div class="notification-time">{{ $notif->time_ago }}</div>
                        </div>
                    </a>
                @endforeach
            @else
                <div class="notification-empty">
                    <i class="bi bi-bell-slash"></i>
                    <p>Tidak ada notifikasi baru</p>
                </div>
            @endif
        </div>
        <div class="popup-footer">
            <a href="{{ route('notifications.index') }}">
                <i class="bi bi-arrow-right-circle me-1"></i>Lihat Selengkapnya
            </a>
        </div>
    </div>

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
                
                {{-- Kolom 1: Brand & Deskripsi --}}
                <div class="col-12 col-sm-6 col-md-4 col-lg-5">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ asset(App\Models\SiteSetting::get('logo_path', 'images/logo.png')) }}" alt="Logo" style="height: 40px; margin-right: 10px; filter: drop-shadow(0 0 5px rgba(59, 130, 246, 0.5));">
                        <h5 class="mb-0 fw-bold">{{ App\Models\SiteSetting::get('brand_name', 'LaptopPremium') }}</h5>
                    </div>
                    <p class="text-muted">
                        {{ App\Models\SiteSetting::get('brand_description', 'Toko laptop terpercaya dengan koleksi lengkap dan harga terbaik untuk semua kebutuhan Anda. Memberikan solusi teknologi berkualitas sejak 2020.') }}
                    </p>
                    <div class="social-links mt-3">
                        @if(isset($social_links) && count($social_links) > 0)
                            @foreach($social_links as $link)
                                <a href="{{ $link['url'] }}" class="me-3" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-{{ $link['platform'] }} fs-4"></i>
                                </a>
                            @endforeach
                        @else
                            {{-- Default Social Links if none set --}}
                            <a href="#" class="me-3"><i class="bi bi-facebook fs-4"></i></a>
                            <a href="#" class="me-3"><i class="bi bi-instagram fs-4"></i></a>
                            <a href="#"><i class="bi bi-whatsapp fs-4"></i></a>
                        @endif
                    </div>
                </div>

                {{-- Kolom 2: Kategori --}}
                <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                    <h6>Kategori</h6>
                    <ul class="list-unstyled">
                        @if(isset($categories) && $categories->count() > 0)
                            @foreach($categories as $category)
                                <li><a href="{{ route('katalog.index', ['kategori' => $category->slug]) }}">{{ $category->nama_kategori }}</a></li>
                            @endforeach
                        @else
                            <li><a href="{{ route('katalog.index', ['kategori' => 'gaming']) }}">Gaming</a></li>
                            <li><a href="{{ route('katalog.index', ['kategori' => 'office']) }}">Office</a></li>
                            <li><a href="{{ route('katalog.index', ['kategori' => 'ultrabook']) }}">Ultrabook</a></li>
                            <li><a href="{{ route('katalog.index', ['kategori' => 'workstation']) }}">Workstation</a></li>
                        @endif
                    </ul>
                </div>

                {{-- Kolom 3: Kontak --}}
                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                    <h6>Kontak Kami</h6>
                    <ul class="list-unstyled">
                        <li class="d-flex">
                            <i class="bi bi-geo-alt fs-5 me-3"></i>
                            <span class="text-muted">{{ $footer_address ?? 'Pekanbaru, Riau, Indonesia' }}</span>
                        </li>
                        <li class="d-flex">
                            <i class="bi bi-telephone fs-5 me-3"></i>
                            <span class="text-muted">{{ $footer_phone ?? '+62 823-1659-2733' }}</span>
                        </li>
                        <li class="d-flex">
                            <i class="bi bi-envelope fs-5 me-3"></i>
                            <span class="text-muted">{{ $footer_email ?? 'laptopPremium@gmail.com' }}</span>
                        </li>
                    </ul>
                </div>
            </div>



            {{-- BARIS 3: COPYRIGHT --}}
            <hr class="mt-5 mb-4">
            <div class="row copyright-section align-items-center">
                <div class="col-12 text-center">
                    <p class="mb-0">
                        {{ $footer_copyright_text ?? '© 2025 LaptopPremium. All rights reserved.' }}
                    </p>
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
    <script src="/js/notifications.js"></script>
    
    {{-- Stack untuk JS spesifik per halaman --}}
    @stack('scripts')
</body>
</html>