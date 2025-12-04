```
@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    {{-- Flatpickr CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    {{-- Custom Dashboard CSS (Loaded last to override) --}}
    <link rel="stylesheet" href="/css/admin/dashboard.css">
@endpush

@section('content')
<div class="admin-dashboard-container container py-4">

    {{-- ========================================================== --}}
    {{-- Welcome Card dengan Cuaca & Jam --}}
    {{-- ========================================================== --}}
    <div class="card welcome-card mb-4 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h2 class="welcome-title mb-2">Selamat Datang, {{ Auth::user()->name ?? 'Admin' }}!</h2>
                    <p class="welcome-subtitle mb-3">Semoga harimu menyenangkan. Berikut adalah ringkasan sistem hari ini.</p>
                    
                    {{-- Grup Widget Cuaca & Jam --}}
                    <div class="d-flex flex-column flex-lg-row gap-2 w-100">
                        {{-- Widget Cuaca (diisi oleh JS) --}}
                        <div id="weather-widget" class="weather-widget">
                            <div class="spinner-border spinner-border-sm text-highlight" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span class="text-highlight ms-2">Memuat cuaca...</span>
                        </div>

                        {{-- Widget Jam (diisi oleh JS) --}}
                        <div id="live-clock" class="weather-widget">
                            <span class="bi-icon"><i class="bi bi-clock"></i></span>
                            <span>Memuat jam...</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 text-center text-md-end mt-4 mt-md-0">
                    {{-- Gambar dinamis (diisi oleh JS) --}}
                    <img src="" alt="Ilustrasi Cuaca" id="weather-image" class="weather-image">
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- 4 Kartu Statistik Utama --}}
    {{-- ========================================================== --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stat-box stat-box-produk h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-3 text-white">Total Produk</h6>
                            <h2 class="fw-bold mb-0 text-white">{{ number_format($totalProduk) }}</h2>
                        </div>
                        <div class="icon-box">
                            <i class="bi bi-box-seam-fill"></i>
                        </div>
                    </div>
                    <small class="text-white-75">Total item dalam inventori</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-box stat-box-pelanggan h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-3 text-white">Total Pelanggan</h6>
                            <h2 class="fw-bold mb-0 text-white">{{ number_format($totalPelanggan) }}</h2>
                        </div>
                        <div class="icon-box">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                    <small class="text-white-75">Pelanggan terdaftar</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-box stat-box-penjualan h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-3 text-white">Total Penjualan</h6>
                            <h2 class="fw-bold mb-0 text-white">{{ number_format($totalPenjualan) }}</h2>
                        </div>
                        <div class="icon-box">
                            <i class="bi bi-cart-check-fill"></i>
                        </div>
                    </div>
                    <small class="text-white-75">Transaksi penjualan</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-box stat-box-pembelian h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-3 text-white">Total Pembelian</h6>
                            <h2 class="fw-bold mb-0 text-white">{{ number_format($totalPembelian) }}</h2>
                        </div>
                        <div class="icon-box">
                            <i class="bi bi-bag-check-fill"></i>
                        </div>
                    </div>
                    <small class="text-white-75">Transaksi pembelian</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- Pendapatan & Statistik Cepat --}}
    {{-- ========================================================== --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card theme-card h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-graph-up-arrow me-2 text-accent"></i>
                        Pendapatan Minggu Ini
                    </h5>
                    <h2 class="fw-bold mb-2 text-accent">Rp {{ number_format($pendapatanMingguan, 0, ',', '.') }}</h2>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" style="width: 75%"></div>
                    </div>
                    <small class="text-gray mt-2 d-block">Target mingguan tercapai 75%</small>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card theme-card h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Statistik Cepat</h5>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="quick-stat-item">
                                <small class="text-gray d-block mb-1">Transaksi Hari Ini</small>
                                <h4 class="fw-bold mb-0 text-main">{{ $transaksiHariIni ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-stat-item">
                                <small class="text-gray d-block mb-1">Produk Terlaris</small>
                                <h4 class="fw-bold mb-0 text-accent">{{ $produkTerlaris ?? '-' }}</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-stat-item">
                                <small class="text-gray d-block mb-1">Stok Menipis</small>
                                <h4 class="fw-bold mb-0 text-highlight">{{ $stokMenipis ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-stat-item">
                                <small class="text-gray d-block mb-1">User Aktif</small>
                                <h4 class="fw-bold mb-0 text-main">{{ $userAktif ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- [BARU] AI Forecasting Widget --}}
    {{-- ========================================================== --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="table-card">
                <div class="table-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="table-title mb-1">
                                <i class="bi bi-stars text-warning me-2"></i>AI Forecasting & Restock
                            </h3>
                            <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Prediksi cerdas berbasis data historis</small>
                        </div>
                        <button id="btn-generate-forecast" class="btn btn-outline-info">
                            <i class="bi bi-cpu me-1"></i> Analisa Sekarang
                        </button>
                    </div>
                                    
                    {{-- Filter & Info Row --}}
                    <div id="forecast-filter-row" class="d-none mt-3">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label text-muted mb-1" style="font-size: 0.85rem;">
                                    <i class="bi bi-funnel me-1"></i>Filter Rekomendasi
                                </label>
                                <select id="forecast-filter" class="form-select">
                                    <option value="all">Semua</option>
                                    <option value="aman">Aman</option>
                                    <option value="warning">Waspada</option>
                                    <option value="danger">Perlu Restock</option>
                                </select>
                            </div>
                            <div class="col-md-8 text-end">
                                <small class="text-muted" id="forecast-info">
                                    <i class="bi bi-info-circle me-1"></i>Menampilkan <span id="showing-count">0</span> dari <span id="total-count">0</span> produk
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4">

                    <div id="forecast-loading" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-gray">Sedang meminta analisis AI...</p>
                    </div>

                    <div id="forecast-error" class="alert alert-danger d-none"></div>

                    <div id="forecast-results" class="table-responsive d-none">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">NO</th>
                                    <th>PRODUK</th>
                                    <th class="text-center">PREDIKSI BULAN DEPAN</th>
                                    <th>REKOMENDASI AI</th>
                                    
                                                                    <th class="text-center" style="width: 120px;">STATUS</th>
                                </tr>
                            </thead>
                            <tbody id="forecast-table-body">
                                {{-- Data akan diisi oleh JS --}}
                            </tbody>
                        </table>
                    </div>                    
                    {{-- Pagination Controls --}}
                    <div id="forecast-pagination" class="d-none mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Halaman <span id="current-page">1</span> dari <span id="total-pages">1</span></small>
                            </div>
                            <div class="pagination-container">
                                <ul class="pagination mb-0" id="pagination-controls">
                                    {{-- Generated by JS --}}
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div id="forecast-empty" class="text-center py-5">
                        <i class="bi bi-bar-chart-line display-6 d-block mb-3 opacity-50" style="color: rgba(59, 130, 246, 0.5);"></i>
                        <p class="mb-0" style="color: #9ca3af;">Klik tombol "Analisa Sekarang" untuk melihat prediksi.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- Grafik Penjualan (DIUBAH UNTUK AJAX) --}}
    {{-- ========================================================== --}}
    <div class="row g-3">
        <div class="col-12">
            <div class="card theme-card">
                <div class="card-body p-4">

                    {{-- Judul dan Form Filter --}}
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                        <div>
                            <h5 class="fw-bold mb-1">Grafik Penjualan</h5>
                            <small class="text-gray">Filter berdasarkan rentang tanggal</small>
                        </div>
                        
                        {{-- Form Filter Tanggal --}}
                        <form id="chart-filter-form" class="d-flex flex-wrap align-items-end gap-2">
                            <div>
                                <label for="chart-date-start" class="form-label form-label-sm text-gray mb-1">Mulai</label>
                                <input type="text" class="form-control form-control-sm date-picker" id="chart-date-start" placeholder="Pilih tanggal" required>
                            </div>
                            <div>
                                <label for="chart-date-end" class="form-label form-label-sm text-gray mb-1">Selesai</label>
                                <input type="text" class="form-control form-control-sm date-picker" id="chart-date-end" placeholder="Pilih tanggal" required>
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-secondary" id="btn-filter-chart">
                                <i class="bi bi-filter me-1"></i> Filter
                            </button>
                        </form>
                    </div>
                    
                    {{-- Kontainer Chart & Loader --}}
                    <div style="height: 300px; position: relative;">
                        {{-- Loader akan muncul di sini (di-toggle oleh JS) --}}
                        <div id="chart-loader" class="chart-loader-overlay">
                            <div class="spinner-border text-main" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        
                        {{-- Canvas (tanpa data-attributes) --}}
                        <canvas id="chartPenjualan"></canvas>
                    </div>

                </div>
            </div>
        </div>
    </div>
    
    {{-- ========================================================== --}}
    {{-- [BARU] Floating Chat Widget --}}
    {{-- ========================================================== --}}
    <div id="ai-chat-widget" class="position-fixed bottom-0 end-0 m-4" style="z-index: 1050;">
        {{-- Chat Toggle Button - Cyan to match Analisa Sekarang --}}
        <button id="ai-chat-toggle" class="btn btn-outline-info rounded-circle shadow-lg p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
            <i class="bi bi-robot fs-3"></i>
        </button>

        {{-- Backdrop Overlay for Maximized View --}}
        <div id="ai-chat-backdrop" class="ai-chat-backdrop"></div>

        {{-- Chat Box - WhatsApp Style --}}
        <div id="ai-chat-box" class="d-none">
            {{-- Header --}}
            <div class="ai-chat-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-robot fs-5"></i>
                    <h6><i class="bi bi-stars me-2"></i>AI Assistant</h6>
                </div>
                <div class="ai-chat-header-actions">
                    <button id="ai-chat-maximize" title="Maximize/Minimize">
                        <i class="bi bi-arrows-fullscreen" id="maximize-icon"></i>
                    </button>
                    <button id="ai-chat-close" title="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Messages Area --}}
            <div id="ai-chat-messages">
                {{-- Welcome Message --}}
                <div class="chat-bubble chat-bubble-ai">
                    <div class="chat-bubble-meta">
                        <i class="bi bi-robot"></i>
                        <span>AI Assistant</span>
                    </div>
                    <div class="chat-bubble-text">
                        👋 Halo! Saya bisa bantu cek omset, produk terlaris, atau buatkan draft restock. Mau tanya apa?
                    </div>
                </div>
            </div>

            {{-- Input Area --}}
            <div class="ai-chat-input-area">
                <form id="ai-chat-form">
                    <input type="text" id="ai-chat-input" placeholder="Ketik pesan..." required autocomplete="off">
                    <button type="submit" title="Send">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{-- Flatpickr JS --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr(".date-picker", {
                dateFormat: "Y-m-d",
                locale: "id",
                allowInput: true,
                altInput: true,
                altFormat: "j F Y",
            });
        });
    </script>
    <script src="/js/admin/dashboard.js"></script>
@endpush
