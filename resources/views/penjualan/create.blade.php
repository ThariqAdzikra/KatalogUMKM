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
            {{-- Cart Header --}}
            <header class="cart-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-cart4"></i>
                    <span>Keranjang</span>
                </h4>
                <button type="button" id="btn-reset-cart" class="btn btn-sm btn-outline-danger" title="Reset Keranjang">
                    <i class="bi bi-trash3"></i>
                </button>
            </header>

            {{-- Cart Body --}}
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
                                    data-nama="{{ $p->name }}" 
                                    data-hp="{{ $p->no_hp }}" 
                                    data-email="{{ $p->email }}" 
                                    data-alamat="{{ $p->alamat }}">
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Cart Items Container --}}
                <div id="cart-items-container">
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted py-5">
                        <i class="bi bi-basket display-4 opacity-25 mb-3"></i>
                        <p class="mb-1 fw-medium">Keranjang Kosong</p>
                        <small class="opacity-75">Pilih produk untuk memulai transaksi</small>
                    </div>
                </div>
            </div>

            {{-- Cart Footer --}}
            <footer class="cart-footer">
                {{-- Subtotal --}}
                <div class="total-row">
                    <span>Subtotal</span>
                    <span id="subtotal-display">Rp 0</span>
                </div>

                {{-- Grand Total --}}
                <div class="total-row grand-total">
                    <span>Total</span>
                    <span id="total-display">Rp 0</span>
                </div>

                {{-- Payment Method --}}
                <div class="payment-method-wrapper">
                    <label class="form-label mb-2">Metode Pembayaran</label>
                    <select id="metode_pembayaran" class="form-select">
                        <option value="cash">💵 Cash</option>
                        <option value="transfer">🏦 Transfer Bank</option>
                        <option value="qris">📱 QRIS</option>
                    </select>
                </div>

                {{-- Checkout Button --}}
                <button type="button" id="btn-bayar" class="btn-primary-custom">
                    <i class="bi bi-cash-coin"></i>
                    <span>Proses Pembayaran</span>
                </button>
            </footer>
        </aside>

        {{-- RIGHT PANEL: PRODUCT CATALOG --}}
        <main class="pos-catalog-section">
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
                    <option value="all">📦 Semua Kategori</option>
                    <option value="laptop">💻 Laptop</option>
                    <option value="aksesoris">🎧 Aksesoris</option>
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