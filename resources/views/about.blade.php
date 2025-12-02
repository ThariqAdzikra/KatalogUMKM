@extends('layouts.app')

@section('title', 'Tentang Kami - Katalog Laptop Premium')

@push('styles')
    {{-- Bootstrap 5 CSS (pastikan sudah ter-load di layout.app) --}}
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="css/about/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
@endpush

@section('content')

<div class="about-page-wrapper">

    {{-- ============================================
        1. BAGIAN DESKRIPSI USAHA
        ============================================ --}}
    <section class="business-description-section py-5">
        <div class="container">
            {{-- Section Header --}}
            <header class="section-header text-center mb-5">
                <h1 data-aos="fade-down">
                    Tentang <span class="highlight">Laptop Premium</span>
                </h1>
                <p class="subtitle mx-auto" data-aos="fade-up" data-aos-delay="100">
                    Platform terpercaya untuk menemukan laptop terbaik — mulai dari kebutuhan profesional, gaming,
                    hingga pelajar. Dibangun dengan semangat efisiensi dan inovasi teknologi.
                </p>
            </header>

            {{-- Business Content Card --}}
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-9">
                    <div class="business-content-card card shadow-sm scroll-reveal p-4 p-lg-5">
                        <div class="row g-4 g-lg-5 align-items-center">
                            {{-- Text Content --}}
                            <div class="col-lg-6 order-2 order-lg-1">
                                <div class="content-text">
                                    <h2>Cerita & Misi Kami</h2>
                                    <p>
                                        Katalog Laptop hadir sebagai solusi digital untuk toko laptop modern. Kami menggabungkan 
                                        pengelolaan stok, transaksi, dan data pegawai dalam satu sistem terpadu berbasis Laravel. 
                                        Dengan teknologi terkini dan desain yang user-friendly, kami memberikan pengalaman pencarian 
                                        laptop yang mudah, cepat, dan akurat untuk memajukan bisnis retail di era digital.
                                    </p>
                                </div>
                            </div>

                            {{-- Image Content --}}
                            <div class="col-lg-6 order-1 order-lg-2">
                                <div class="content-image">
                                    <img src="{{ asset('images/tempatUsaha.jpg') }}" 
                                         alt="Suasana Toko Laptop Premium"
                                         class="rounded-image img-fluid"
                                         loading="lazy">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Smooth Section Divider --}}
    <div class="section-divider">
        <div class="container">
            <div class="divider-line"></div>
        </div>
    </div>

    {{-- ============================================
        2. BAGIAN PEMILIK USAHA
        ============================================ --}}
    <section class="owner-section py-5">
        <div class="container">
            {{-- Section Header --}}
            <header class="section-header text-center mb-4">
                <h2>Pemilik Usaha</h2>
                <div class="divider mx-auto"></div>
                <p class="subtitle mx-auto mt-3">
                    Visioner di balik inovasi dan komitmen kualitas
                </p>
            </header>
            
            {{-- Owner Profile Card --}}
            <div class="row justify-content-center">
                <div class="col-lg-7 col-xl-6">
                    <div class="profile-card-owner card shadow text-center scroll-reveal">
                        <div class="card-body p-4 p-lg-5">
                            <img src="{{ asset('images/owner.jpeg') }}" 
                                 alt="Foto Pemilik Usaha" 
                                 class="profile-image rounded-circle"
                                 loading="lazy">
                            <h3 class="name">Gibran Rakabuming Raka</h3>
                            <p class="role">Founder & Chief Executive Officer</p>
                            <p class="bio">
                                "Kami berkomitmen untuk memberikan solusi teknologi terbaik yang fungsional,
                                inovatif, dan mudah digunakan untuk memajukan bisnis lokal menuju transformasi digital."
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Smooth Section Divider --}}
    <div class="section-divider">
        <div class="container">
            <div class="divider-line"></div>
        </div>
    </div>

    {{-- ============================================
        3. BAGIAN FITUR UTAMA
        ============================================ --}}
    <section class="features-section py-5">
        <div class="container">
            {{-- Section Header --}}
            <header class="section-header text-center mb-5">
                <h2>Fitur Utama Website</h2>
                <div class="divider mx-auto"></div>
                <p class="subtitle mx-auto mt-3">
                    Solusi terintegrasi yang kami tawarkan untuk manajemen bisnis Anda.
                </p>
            </header>

            {{-- MODIFIED: Wrapper dikembalikan dan dipersempit untuk margin & ukuran card yang lebih baik --}}
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-9">
                    <div class="row g-4 justify-content-center">

                        {{-- Fitur 1: Manajemen Pengguna --}}
                        <div class="col-12 col-md-6 col-lg-4 d-flex align-items-stretch">
                            <div class="feature-card card h-100 shadow-sm text-center scroll-reveal p-3 p-md-4">
                                <i class="bi bi-people-fill fs-1 text-gradient mb-3 mb-md-4"></i>
                                <h3 class="name">Manajemen Pengguna</h3>
                                <ul class="feature-list">
                                    <li>Role: Super Admin & Pegawai</li>
                                    <li>Autentikasi & otorisasi dengan Laravel Breeze</li>
                                </ul>
                            </div>
                        </div>

                        {{-- Fitur 2: Modul Katalog --}}
                        <div class="col-12 col-md-6 col-lg-4 d-flex align-items-stretch">
                            <div class="feature-card card h-100 shadow-sm text-center scroll-reveal p-3 p-md-4">
                                <i class="bi bi-laptop fs-1 text-gradient mb-3 mb-md-4"></i>
                                <h3 class="name">Modul Katalog</h3>
                                <ul class="feature-list">
                                    <li>Menampilkan daftar laptop untuk calon pembeli</li>
                                    <li>Pencarian & filter berdasarkan merek atau spesifikasi</li>
                                </ul>
                            </div>
                        </div>

                        {{-- Fitur 3: Modul Stok --}}
                        <div class="col-12 col-md-6 col-lg-4 d-flex align-items-stretch">
                            <div class="feature-card card h-100 shadow-sm text-center scroll-reveal p-3 p-md-4">
                                <i class="bi bi-box-seam fs-1 text-gradient mb-3 mb-md-4"></i>
                                <h3 class="name">Modul Stok</h3>
                                <ul class="feature-list">
                                    <li>Menampilkan daftar produk lengkap dengan jumlah stok</li>
                                    <li>Indikator stok menipis & habis</li>
                                    <li>Fitur CRUD produk dan upload gambar</li>
                                </ul>
                            </div>
                        </div>

                        {{-- Fitur 4: Modul Pembelian --}}
                        <div class="col-12 col-md-6 col-lg-4 d-flex align-items-stretch">
                            <div class="feature-card card h-100 shadow-sm text-center scroll-reveal p-3 p-md-4">
                                <i class="bi bi-cart-check-fill fs-1 text-gradient mb-3 mb-md-4"></i>
                                <h3 class="name">Modul Pembelian</h3>
                                <ul class="feature-list">
                                    <li>Mengelola transaksi pembelian dari supplier</li>
                                    <li>Perhitungan total harga otomatis</li>
                                    <li>Riwayat pembelian disimpan sebagai log</li>
                                </ul>
                            </div>
                        </div>

                        {{-- Fitur 5: Modul Penjualan (Kasir) --}}
                        <div class="col-12 col-md-6 col-lg-4 d-flex align-items-stretch">
                            <div class="feature-card card h-100 shadow-sm text-center scroll-reveal p-3 p-md-4">
                                <i class="bi bi-cash-coin fs-1 text-gradient mb-3 mb-md-4"></i>
                                <h3 class="name">Modul Penjualan (Kasir)</h3>
                                <ul class="feature-list">
                                    <li>Tampilan kasir 2 kolom interaktif</li>
                                    <li>Perhitungan total otomatis (real-time)</li>
                                    <li>Pilihan metode pembayaran: Cash, Transfer, QRIS</li>
                                    <li>Pengurangan stok otomatis setelah transaksi</li>
                                </ul>
                            </div>
                        </div>

                        {{-- Fitur 6: Laporan Penjualan --}}
                        <div class="col-12 col-md-6 col-lg-4 d-flex align-items-stretch">
                            <div class="feature-card card h-100 shadow-sm text-center scroll-reveal p-3 p-md-4">
                                <i class="bi bi-bar-chart-line-fill fs-1 text-gradient mb-3 mb-md-4"></i>
                                <h3 class="name">Laporan Penjualan</h3>
                                <ul class="feature-list">
                                    <li>Rekap data penjualan berdasarkan tanggal</li>
                                    <li>Ringkasan total penjualan harian/bulanan</li>
                                </ul>
                            </div>
                        </div>
                    
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Smooth Section Divider --}}
    <div class="section-divider">
        <div class="container">
            <div class="divider-line"></div>
        </div>
    </div>

    {{-- ============================================
        4. BAGIAN TIM PENGEMBANG
        ============================================ --}}
    <section class="developer-team-section py-5">
        <div class="container">
            {{-- Section Header --}}
            <header class="section-header text-center mb-5">
                <h2>Tim Pengembang</h2>
                <div class="divider mx-auto"></div>
                <p class="subtitle mx-auto mt-3">
                    Dibangun dengan dedikasi dan semangat eksplorasi teknologi oleh tim profesional kami
                </p>
            </header>

            {{-- MODIFIED: Wrapper dikembalikan dan dipersempit untuk margin & ukuran card yang lebih baik --}}
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-9">
                    <div class="row g-4 justify-content-center">
                
                        {{-- Developer 1: Ryanda Valents Anakri --}}
                        <div class="col-12 col-md-6 col-lg-4 d-flex align-items-stretch">
                            <div class="profile-card card h-100 shadow-sm text-center scroll-reveal">
                                <div class="card-body p-3 p-md-4 d-flex flex-column">
                                    <img src="{{ asset('images/ryanda.jpeg') }}" 
                                         alt="Ryanda Valents Anakri - Backend Developer" 
                                         class="profile-image rounded-circle mx-auto"
                                         loading="lazy">
                                    <h3 class="name mt-2 mt-md-3">Ryanda Valents Anakri</h3>
                                    <p class="role">Backend Developer</p>
                                    <p class="bio">
                                        Asisten Praktikum PBO FMIPA & Mahasiswa Sistem Informasi, Universitas Riau. 
                                        Spesialis dalam arsitektur backend, database optimization, dan API development.
                                    </p>
                                    <span class="role-badge mt-auto">Backend Specialist</span>
                                </div>
                            </div>
                        </div>

                        {{-- Developer 2: M. Thariq Adzikra --}}
                        <div class="col-12 col-md-6 col-lg-4 d-flex align-items-stretch">
                            <div class="profile-card card h-100 shadow-sm text-center scroll-reveal">
                                <div class="card-body p-3 p-md-4 d-flex flex-column">
                                    <img src="{{ asset('images/thariq.png') }}" 
                                         alt="M. Thariq Adzikra - Frontend Developer" 
                                         class="profile-image rounded-circle mx-auto"
                                         loading="lazy">
                                    <h3 class="name mt-2 mt-md-3">M. Thariq Adzikra</h3>
                                    <p class="role">Frontend Developer</p>
                                    <p class="bio">
                                        Calon Engineer NASA, Pencinta Naspad & Mie Ayam, Universitas Riau. 
                                        Ahli dalam UI/UX implementation dan modern JavaScript frameworks.
                                    </p>
                                    <span class="role-badge mt-auto">UI/UX Expert</span>
                                </div>
                            </div>
                        </div>

                        {{-- Developer 3: Frenky Estiawan --}}
                        <div class="col-12 col-md-6 col-lg-4 d-flex align-items-stretch">
                            <div class="profile-card card h-100 shadow-sm text-center scroll-reveal">
                                <div class="card-body p-3 p-md-4 d-flex flex-column">
                                    <img src="{{ asset('images/frenky.jpeg') }}" 
                                         alt="Frenky Estiawan - System Analyst" 
                                         class="profile-image rounded-circle mx-auto"
                                         loading="lazy">
                                    <h3 class="name mt-2 mt-md-3">Frenky Estiawan</h3>
                                    <p class="role">System Analyst</p>
                                    <p class="bio">
                                        Fokus pada pengalaman pengguna yang intuitif, analisis sistem yang mendalam,
                                        dan desain yang memanjakan mata dengan pendekatan user-centric.
                                    </p>
                                    <span class="role-badge mt-auto">Analyst Pro</span>
                                </div>
                            </div>
                        </div>

                        {{-- Developer 4: Dimas Adji Isnanda --}}
                        <div class="col-12 col-md-6 col-lg-4 d-flex align-items-stretch">
                            <div class="profile-card card h-100 shadow-sm text-center scroll-reveal">
                                <div class="card-body p-3 p-md-4 d-flex flex-column">
                                    <img src="{{ asset('images/dimas.png') }}" 
                                         alt="Dimas Adji Isnanda - Quality Assurance Tester" 
                                         class="profile-image rounded-circle mx-auto"
                                         loading="lazy">
                                    <h3 class="name mt-2 mt-md-3">Dimas Adji Isnanda</h3>
                                    <p class="role">QA Tester</p>
                                    <p class="bio">
                                        Memastikan kualitas aplikasi dengan testing menyeluruh, debugging efektif,
                                        dan quality assurance yang ketat untuk pengalaman pengguna terbaik.
                                    </p>
                                    <span class="role-badge mt-auto">QA Guardian</span>
                                </div>
                            </div>
                        </div>

                        {{-- Developer 5: Muhammad Farhan --}}
                        <div class="col-12 col-md-6 col-lg-4 d-flex align-items-stretch">
                            <div class="profile-card card h-100 shadow-sm text-center scroll-reveal">
                                <div class="card-body p-3 p-md-4 d-flex flex-column">
                                    <img src="{{ asset('images/farhan.jpeg') }}" 
                                         alt="Muhammad Farhan - Software Tester" 
                                         class="profile-image rounded-circle mx-auto"
                                         loading="lazy">
                                    <h3 class="name mt-2 mt-md-3">Muhammad Farhan</h3>
                                    <p class="role">Software Tester</p>
                                    <p class="bio">
                                        Menjaga kualitas aplikasi dengan pengujian sistematis, memastikan semua fitur 
                                        berjalan tanpa bug dan memberikan performa optimal di semua kondisi.
                                    </p>
                                    <span class="role-badge mt-auto">Test Engineer</span>
                                </div>
                            </div>
                        </div>

                        {{-- Developer 6: Anggota Baru (Tambahan) --}}
                        <div class="col-12 col-md-6 col-lg-4 d-flex align-items-stretch">
                            <div class="profile-card card h-100 shadow-sm text-center scroll-reveal">
                                <div class="card-body p-3 p-md-4 d-flex flex-column">
                                    {{-- Ganti 'member.jpg' dengan nama file foto anggota baru --}}
                                    <img src="{{ asset('images/faiq.png') }}" 
                                         alt="Nama Anggota Baru - DevOps" 
                                         class="profile-image rounded-circle mx-auto"
                                         loading="lazy">
                                    <h3 class="name mt-2 mt-md-3">Dzaki Faiq</h3>
                                    <p class="role">DevOps Engineer</p>
                                    <p class="bio">
                                        Mengelola infrastruktur server, deployment otomatis, dan memastikan 
                                        keandalan sistem agar aplikasi dapat diakses dengan cepat dan aman.
                                    </p>
                                    <span class="role-badge mt-auto">Infrastructure</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection

@push('scripts')
    {{-- Bootstrap 5 JS (pastikan sudah ter-load di layout.app) --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> --}}
    <script src="js/about/main.js"></script>
@endpush