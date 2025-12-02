@extends('layouts.pos')

@section('title', 'Point of Sale - Laptop Store')

@push('styles')
    <link rel="stylesheet" href="/css/manajemen/kasir.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush

@section('content')
<div class="pos-container">
    {{-- Hidden Form for Submission --}}
    <form action="{{ route('penjualan.store') }}" method="POST" id="form-kasir">
        @csrf
        <input type="hidden" name="id_pelanggan" id="hidden_id_pelanggan">
        <input type="hidden" name="nama_pelanggan_baru" id="hidden_nama_pelanggan_baru">
        <input type="hidden" name="no_hp_baru" id="hidden_no_hp_baru">
        <input type="hidden" name="email_baru" id="hidden_email_baru">
        <input type="hidden" name="alamat_baru" id="hidden_alamat_baru">
        <input type="hidden" name="tanggal_penjualan" id="hidden_tanggal_penjualan">
        <input type="hidden" name="metode_pembayaran" id="hidden_metode_pembayaran">
        <input type="hidden" name="total_harga" id="hidden_total_harga">
        
        {{-- Dynamic Product Inputs --}}
        <div id="hidden-product-inputs"></div>
    </form>

    <div class="pos-row">
        {{-- LEFT PANEL: SHOPPING CART --}}
        <aside class="pos-cart-section">
            <div class="cart-body">
                {{-- Customer Selection --}}
                <div class="customer-selection-wrapper">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">Pelanggan</label>
                        <button type="button" 
                                id="btn-view-customer" 
                                class="btn btn-link btn-sm p-0 text-decoration-none" 
                                style="font-size: 0.813rem; color: var(--primary-cyan); display: none;">
                            <i class="bi bi-eye me-1"></i>Lihat Detail
                        </button>
                    </div>
                    <select id="pelanggan-select" class="form-select" style="width: 100%;">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach($pelanggan as $p)
                            <option value="{{ $p->id_pelanggan }}" 
                                    data-nama="{{ $p->nama }}" 
                                    data-hp="{{ $p->no_hp }}" 
                                    data-email="{{ $p->email }}" 
                                    data-alamat="{{ $p->alamat }}">
                                {{ $p->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Cart Items Container --}}
                <div id="cart-items-container">
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-cart3" style="font-size: 3rem;"></i>
                        <p class="mt-3">Keranjang kosong</p>
                    </div>
                </div>

                {{-- Cart Summary (Subtotal, Total, Payment) --}}
                <div class="cart-summary" id="cart-summary">
                    {{-- Toggle Button --}}
                    <div class="cart-summary-toggle" id="cart-summary-toggle">
                        <i class="bi bi-chevron-up"></i>
                    </div>

                    {{-- Summary Content --}}
                    <div class="cart-summary-content" id="cart-summary-content">
                        {{-- Grand Total --}}
                        <div class="summary-row summary-total">
                            <span>Total</span>
                            <span id="total-display">Rp 0</span>
                        </div>

                        {{-- Payment Method --}}
                        <div class="payment-method-wrapper">
                            <label class="form-label mb-2">Metode Pembayaran</label>
                            <select id="metode_pembayaran" class="form-select">
                                <option value="cash">Cash</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>

                        {{-- Checkout Button --}}
                        <button type="button" id="btn-bayar" class="btn-primary-custom">
                            <i class="bi bi-cash-coin"></i>
                            <span>Proses Pembayaran</span>
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        {{-- RIGHT PANEL: PRODUCT CATALOG --}}
        <main class="pos-catalog-section">
            {{-- Info Widget --}}
            <div class="pos-info-widget">
                <div class="info-left">
                    <a href="{{ route('dashboard') }}" class="btn-back" title="Kembali ke Dashboard">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div class="info-divider"></div>
                    <div class="info-time">
                        <i class="bi bi-clock"></i>
                        <span id="widget-clock">00:00:00</span>
                    </div>
                    <div class="info-divider"></div>
                    <div class="info-weather">
                        <i class="bi bi-cloud-sun"></i>
                        <span>28°C</span>
                    </div>
                </div>
                <div class="info-right">
                    <div class="user-info">
                        <span class="user-name">{{ Auth::user()->name }}</span>
                        <span class="user-role">{{ ucfirst(Auth::user()->role ?? 'Staff') }}</span>
                    </div>
                    <div class="user-avatar">
                        @if(Auth::user()->photo)
                            <img src="{{ asset('storage/' . Auth::user()->photo) }}" alt="User">
                        @else
                            <i class="bi bi-person-circle"></i>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Catalog Header --}}
            <header class="catalog-header">
                {{-- Search Bar --}}
                <div class="search-wrapper">
                    <i class="bi bi-search"></i>
                    <input type="text" 
                           id="search-product" 
                           class="form-control" 
                           placeholder="Cari produk (nama, merk, spesifikasi)..."
                           autocomplete="off">
                </div>

                {{-- Category Filter --}}
                <select id="filter-category" class="form-select">
                    <option value="all">Semua Kategori</option>
                    @foreach($kategori as $kat)
                        <option value="{{ $kat->slug ?? strtolower($kat->nama_kategori) }}">{{ $kat->nama_kategori }}</option>
                    @endforeach
                </select>
            </header>

            {{-- Product Grid --}}
            <div class="catalog-grid" id="product-grid">
                {{-- Products will be rendered here by JavaScript --}}
                <div class="col-12 text-center text-muted py-5" style="grid-column: 1 / -1;">
                    <div class="loading-spinner mx-auto mb-3"></div>
                    <p>Memuat produk...</p>
                </div>
            </div>
        </main>
    </div>
</div>

{{-- Pass Data to JavaScript --}}
<script>
    window.productsData = @json($produk);
</script>

{{-- Modals --}}
@include('penjualan.partials.modals')

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="/js/kasir/main.js"></script>
@endpush