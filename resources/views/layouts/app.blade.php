<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laptop Store'))</title>

    {{-- Favicon & Theme Color --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <meta name="theme-color" content="#0a0e27">
    <meta name="msapplication-TileColor" content="#0a0e27">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- CSS Global Dimuat dari file eksternal (public/css/app.css) --}}
    <link rel="stylesheet" href="/css/layouts/app.css">
    
    {{-- Stack untuk CSS spesifik per halaman --}}
    @stack('styles')

    {{-- Blok <style> inline sudah dihapus --}}
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-glass">
        <div class="container">
            
            {{-- 
                LOGIKA ROLE LOGO NAVBAR:
                - Jika role 'superadmin', link ke 'dashboard'.
                - Jika role 'pegawai' (atau guest), link ke 'home'.
            --}}
            <a class="navbar-brand d-flex align-items-center" 
               href="{{ Auth::check() && Auth::user()->role == 'superadmin' ? route('dashboard') : route('home') }}">
                <img src="{{ asset('images/logo.png') }}" alt="LaptopPremium Logo" class="logo-filtered">
                LaptopPremium
            </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    
                    @auth
                    
                    {{-- 
                        LOGIKA ROLE MENU:
                        - Jika role 'superadmin', tampilkan 'Dashboard'.
                        - Jika role 'pegawai' (else), tampilkan 'Katalog'.
                    --}}
                    @if(Auth::user()->role == 'superadmin')
                        <li class="nav-item">
                            {{-- PERBAIKAN LOGIKA ACTIVE STATE DASHBOARD --}}
                            <a class="nav-link {{ request()->is('superadmin/dashboard') ? 'active' : '' }}" 
                               href="{{ route('dashboard') }}">
                                Dashboard
                            </a>
                        </li>
                    @else
                        <li class="nav-item">
                            {{-- Logika 'Katalog' Aktif --}}
                            <a class="nav-link {{ (request()->routeIs('katalog.*') || request()->routeIs('stok.show')) ? 'active' : '' }}" 
                               href="{{ route('katalog.index') }}">
                                Katalog
                            </a>
                        </li>
                    @endif
                    {{-- AKHIR DARI PERUBAHAN MENU --}}


                    {{-- LOGIKA KASIR & MANAJEMEN PEGAWAI --}}
                    @if(Auth::user()->role == 'superadmin')
                        {{-- TAMPILKAN MANAJEMEN PEGAWAI UNTUK SUPERADMIN --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('pegawai.*') ? 'active' : '' }}" 
                               href="{{ route('pegawai.index') }}">
                                Pegawai
                            </a>
                        </li>
                    @else
                        {{-- TAMPILKAN KASIR UNTUK ROLE LAIN (PEGAWAI) --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('penjualan.create') ? 'active' : '' }}" 
                               href="{{ route('penjualan.create') }}">
                                Kasir
                            </a>
                        </li>
                    @endif
                    {{-- AKHIR DARI PERUBAHAN LOGIKA --}}

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pembelian.*') ? 'active' : '' }}" 
                           href="{{ route('pembelian.index')}}">
                            Pembelian
                        </a>
                    </li>

                    @if(Auth::user()->role == 'superadmin')
                    <li class="nav-item">
                        <a class="nav-link {{ (request()->routeIs('penjualan.*') && !request()->routeIs('penjualan.create')) ? 'active' : '' }}" 
                           href="{{ route('penjualan.index') }}">
                            Penjualan
                        </a>
                    </li>
                    @endif
                    
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pelanggan.*') ? 'active' : '' }}" 
                           href="{{ route('pelanggan.index') }}">
                            Pelanggan
                        </a>
                    </li>
                    <li class="nav-item">
                         {{-- Logika 'Stok' Aktif (Tidak termasuk 'show') --}}
                        <a class="nav-link {{ (request()->routeIs('stok.*') && !request()->routeIs('stok.show')) ? 'active' : '' }}" 
                           href="{{ route('stok.index') }}">
                            Stok
                        </a>
                    </li>
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto align-items-center">
                    @guest
                        <li class="nav-item">
                            <a class="btn btn-nav btn-sm" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown"
                               role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                
                                @if(Auth::user()->photo)
                                    <img src="{{ asset('storage/'. Auth::user()->photo) }}" 
                                         alt="{{ Auth::user()->name }}" 
                                         class="me-2" 
                                         style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">
                                @else
                                    <i class="bi bi-person-circle me-2" style="font-size: 1.7rem;"></i>
                                @endif
                                {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                {{-- 
                                    ✅ ITEM DASHBOARD DI SINI SUDAH DIHAPUS.
                                    Item pertama sekarang adalah Profile.
                                --}}
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="bi bi-person me-2"></i>Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>

                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    {{-- SECTION FOOTER PROFESIONAL (Tata letak 4 kolom) --}}
    <footer class="py-5">
        <div class="container">
            {{-- BARIS 1: LINK UTAMA (4 KOLOM) --}}
            <div class="row g-5">
                
                {{-- Kolom 1: Brand & Socials --}}
                <div class="col-12 col-md-6 col-lg-4">
                    {{-- LOGIKA ROLE FOOTER BRAND --}}
                    <a class="footer-brand" 
                       href="{{ Auth::check() && Auth::user()->role == 'superadmin' ? route('dashboard') : route('home') }}">
                        <img src="{{ asset('images/logo.png') }}" alt="LaptopPremium Logo" class="logo-filtered">
                        <h5 class="text-white mb-0" style="font-size: 1.5rem; font-weight: 700;">LaptopPremium</h5>
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
                        <li><a href="{{ Auth::check() && Auth::user()->role == 'superadmin' ? route('dashboard') : route('home') }}">Home</a></li>
                        @auth
                        
                        {{-- Sembunyikan Katalog di footer jika superadmin --}}
                        @if(Auth::user()->role != 'superadmin')
                        <li><a href="{{ route('katalog.index') }}">Katalog</a></li>
                        @endif
                        
                        <li><a href="{{ route('stok.index') }}">Stok</a></li>
                        @endauth
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
                        © {{ date('Y') }} LaptopPremium. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="me-3">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

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
                        !e.ctrlKey && !e.metaKey) {
                        
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Stack untuk JS spesifik per halaman --}}
    @stack('scripts')
</body>
</html>