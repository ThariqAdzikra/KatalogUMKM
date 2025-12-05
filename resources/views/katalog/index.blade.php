@extends('layouts.app')

@section('title', 'Katalog Laptop - Laptop Store')

@push('styles')
    {{-- Memuat CSS spesifik untuk halaman katalog --}}
    <link rel="stylesheet" href="/css/katalog/style.css">
@endpush

@section('content')
@if(!auth()->check() || auth()->user()->role !== 'pegawai')
{{-- Hero Section with Warm Ambiance --}}
<div class="hero-section">
    <div class="hero-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="hero-title">
                        {!! nl2br(e(App\Models\SiteSetting::get('hero_title', "Temukan Laptop\nImpian Anda"))) !!}
                    </h1>
                    <p class="hero-subtitle">
                        {{ App\Models\SiteSetting::get('hero_subtitle', 'Koleksi laptop terlengkap dengan spesifikasi terbaik untuk kebutuhan kerja, gaming, dan entertainment. Dapatkan harga terbaik dengan garansi resmi dan layanan purna jual terpercaya.') }}
                    </p>
                    <a href="#katalog" class="btn btn-hero">
                        <i class="bi bi-arrow-down-circle me-2"></i>Jelajahi Katalog
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Carousel Section --}}
<div class="container" style="margin-top: -3rem; margin-bottom: 6rem; position: relative; z-index: 5;">
    <div id="promoCarousel" class="carousel slide rounded-4 overflow-hidden shadow-lg border border-primary border-opacity-25" data-bs-ride="carousel">
        <div class="carousel-indicators">
            @php
                $carouselImages = App\Models\SiteSetting::get('carousel_images', []);
                if (empty($carouselImages)) {
                    $carouselImages = ['images/background.jpeg'];
                }
            @endphp
            @foreach($carouselImages as $index => $imagePath)
            <button type="button" data-bs-target="#promoCarousel" data-bs-slide-to="{{ $index }}" 
                    class="{{ $index === 0 ? 'active' : '' }}" 
                    aria-current="{{ $index === 0 ? 'true' : 'false' }}" 
                    aria-label="Slide {{ $index + 1 }}"></button>
            @endforeach
        </div>
        <div class="carousel-inner">
            @foreach($carouselImages as $index => $imagePath)
            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                <img src="{{ asset($imagePath) }}" class="d-block w-100" 
                     alt="Carousel {{ $index + 1 }}" 
                     style="height: 400px; object-fit: cover;">
            </div>
            @endforeach
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#promoCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#promoCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>
@endif

@if(!auth()->check() || auth()->user()->role !== 'pegawai')
<div class="section-header d-flex flex-column align-items-start container">
        <h2 class="section-title pt-2 mb-2">Katalog Produk</h2>
        <div class="section-divider mt-2 mx-0" style="width: 100%"></div>
        {{-- Pastikan $produk memiliki data total() dari pagination --}}
        <p class="section-subtitle">Menampilkan {{ $produk->total() }} produk berkualitas tinggi</p>
    </div>
@endif

{{-- Section Header --}}
    @if(auth()->check() && auth()->user()->role === 'pegawai')
    <div class="container mb-4 mt-3">
        <div class="card welcome-card shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <h2 class="welcome-title mb-2">Selamat Datang, {{ Auth::user()->name }}!</h2>
                        <p class="welcome-subtitle mb-3">Semoga harimu menyenangkan. Silakan cek katalog produk terbaru.</p>
                        
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
    </div>
    @endif

{{-- Filter Section with Cozy Design --}}
<div class="container" style="margin-top: 5rem;">
    <div class="filter-section" id="katalog">
        <div class="filter-card">
            <form action="{{ route('katalog.index') }}" method="GET">
                <div class="row g-4 align-items-end">
                    <div class="col-12 col-lg-6">
                        <label class="form-label">
                            <i class="bi bi-search me-2"></i>Cari Produk
                        </label>
                        <div class="position-relative">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   class="form-control" 
                                   placeholder="Ketik nama laptop atau spesifikasi...">
                            <i class="bi bi-search search-icon"></i>
                        </div>
                    </div>
                    <div class="col-12 col-lg-3">
                        <label class="form-label">
                            <i class="bi bi-filter me-2"></i>Kategori
                        </label>
                        <select name="kategori" class="form-select">
                            <option value="">Semua Kategori</option>
                            @foreach($kategori as $kat)
                                <option value="{{ $kat->slug }}" {{ request('kategori') == $kat->slug ? 'selected' : '' }}>
                                    {{ $kat->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-lg-3">
                        <div class="d-flex gap-2">
                            <a href="{{ route('katalog.index') }}" class="btn btn-reset flex-fill">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                            <button type="submit" class="btn btn-search flex-fill">
                                <i class="bi bi-search me-2"></i>Cari
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    

    {{-- Product Grid --}}
    <div class="row g-4 pb-5">
        @forelse($produk as $item)
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="product-card">
                <div class="product-img-wrapper">
                    
                @if($item->gambar)
                    <img src="{{ asset($item->gambar) . '?v=' . $item->updated_at->timestamp }}" 
                        alt="{{ $item->nama_produk }}" 
                        class="product-img">
                @else
                <i class="bi bi-laptop" style="font-size: 4.5rem; color: var(--border-soft);"></i>
                @endif

                    
                    {{-- ============================================= --}}
                    {{-- ✅ DIPERBARUI: Logika if-else dikembalikan     --}}
                    {{--    untuk menangani tampilan "Habis"           --}}
                    {{--    bagi user yang login.                      --}}
                    {{-- ============================================= --}}
                    @if($item->stok > 0)
                        @php
                            // Color-coded stock levels
                            if ($item->stok > 15) {
                                $badgeClass = 'badge-success'; // Green - High stock
                                $icon = 'bi-check-circle';
                            } elseif ($item->stok >= 6) {
                                $badgeClass = 'badge-warning'; // Yellow - Medium stock  
                                $icon = 'bi-exclamation-circle';
                            } else {
                                $badgeClass = 'badge-danger'; // Red - Low stock
                                $icon = 'bi-exclamation-triangle';
                            }
                        @endphp
                        <span class="product-badge badge-premium {{ $badgeClass }}">
                            <i class="bi {{ $icon }} me-1"></i>Stok: {{ $item->stok }}
                        </span>
                    @else
                        <span class="product-badge badge-premium badge-danger">
                            <i class="bi bi-x-circle me-1"></i>Habis
                        </span>
                    @endif
                </div>

                <div class="product-body">
                    <div class="d-flex gap-2 mb-2">
                        <span class="badge-brand">{{ $item->merk }}</span>
                        <span class="badge-brand" style="background-color: var(--primary-wood); color: white;">
                            {{ $item->kategori->nama_kategori ?? '-' }}
                        </span>
                    </div>
                    
                    <h5 class="product-title">{{ $item->nama_produk }}</h5>
                    
                    <p class="product-desc">{{ $item->spesifikasi }}</p>
                    
                    <div class="product-price">
                        Rp {{ number_format($item->harga_jual, 0, ',', '.') }}
                    </div>
                    
                    <div class="d-flex">
                        <a href="{{ route('stok.show', $item->id_produk) }}" class="btn btn-detail flex-fill">
                            <i class="bi bi-eye me-2"></i>Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h3>Produk Tidak Ditemukan</h3>
                <p>Coba ubah kata kunci pencarian atau filter kategori Anda untuk menemukan produk yang sesuai</p>
                {{-- Tombol "Lihat Semua Produk" telah dihapus dari sini --}}
            </div>
        </div>
        @endforelse
    </div>

    {{-- PAGINATION --}}
    @if($produk->hasPages())
    <div class="d-flex justify-content-center pb-5">
        {{ $produk->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Fungsi untuk mengambil data cuaca
    async function fetchWeather() {
        const apiKey = '93b7587a55f39ff4f0dc94e189ea5bd3';
        const city = 'Pekanbaru';
        const url = `https://api.openweathermap.org/data/2.5/weather?q=${city}&units=metric&lang=id&appid=${apiKey}`;

        const weatherWidget = document.getElementById('weather-widget');
        if (!weatherWidget) return;

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error('Gagal mengambil data cuaca');
            }

            const data = await response.json();

            const temp = Math.round(data.main.temp);
            const description = data.weather[0].description;
            const iconCode = data.weather[0].icon.slice(0, -1);

            const weatherIcons = {
                '01': '<i class="bi bi-sun-fill"></i>',
                '02': '<i class="bi bi-cloud-sun-fill"></i>',
                '03': '<i class="bi bi-cloud-fill"></i>',
                '04': '<i class="bi bi-clouds-fill"></i>',
                '09': '<i class="bi bi-cloud-drizzle-fill"></i>',
                '10': '<i class="bi bi-cloud-rain-fill"></i>',
                '11': '<i class="bi bi-cloud-lightning-rain-fill"></i>',
                '13': '<i class="bi bi-snow-fill"></i>',
                '50': '<i class="bi bi-cloud-fog-fill"></i>'
            };

            const icon = weatherIcons[iconCode] || '<i class="bi bi-cloud-sun"></i>';

            weatherWidget.innerHTML = `
                <span class="bi-icon">${icon}</span>
                <strong>${temp}°C</strong>
                <span style="opacity: 0.9;">${description} di ${city}</span>
            `;

            const iconElement = weatherWidget.querySelector('.bi-icon i');
            if (iconElement) {
                iconElement.style.fontSize = '1.5rem';
                iconElement.style.verticalAlign = 'bottom';
            }

        } catch (error) {
            console.error('Error fetching weather:', error);
            weatherWidget.innerHTML = '<span>Gagal memuat cuaca</span>';
        }
    }

    // 2. Fungsi untuk mengatur gambar Siang/Malam
    function updateWeatherImage() {
        const imgElement = document.getElementById('weather-image');
        if (!imgElement) return;

        const hour = new Date().getHours();
        const isDayTime = hour >= 6 && hour < 18;

        if (isDayTime) {
            imgElement.src = '/images/matahari.png';
            imgElement.alt = 'Ilustrasi Siang Hari';
        } else {
            imgElement.src = '/images/bulan.png';
            imgElement.alt = 'Ilustrasi Malam Hari';
        }

        imgElement.onerror = function () {
            console.warn('Gambar cuaca tidak ditemukan di path:', this.src);
            this.style.display = 'none';
        };
    }

    function updateClock() {
        const clockWidget = document.getElementById('live-clock');
        if (!clockWidget) return;

        const now = new Date();

        const dateOptions = { weekday: 'long', day: 'numeric', month: 'long' };
        const formattedDate = now.toLocaleDateString('id-ID', dateOptions);

        const timeOptions = { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Asia/Jakarta' };
        const formattedTime = now.toLocaleTimeString('id-ID', timeOptions).replace(/\./g, ':');

        clockWidget.innerHTML = `
            <span class="bi-icon"><i class="bi bi-clock"></i></span>
            <strong>${formattedTime}</strong>
            <span style="opacity: 0.9;">${formattedDate}</span>
        `;

        const iconElement = clockWidget.querySelector('.bi-icon i');
        if (iconElement) {
            iconElement.style.fontSize = '1.5rem';
            iconElement.style.verticalAlign = 'bottom';
        }
    }

    // Panggil fungsi
    fetchWeather();
    updateWeatherImage();
    updateClock();
    setInterval(updateClock, 1000);
});
</script>
@endpush