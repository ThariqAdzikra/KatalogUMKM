@extends('layouts.app')

@section('title', 'Manajemen Stok - ' . App\Models\SiteSetting::get('brand_name'))

@push('styles')
    {{-- CSS Global Manajemen --}}
    <link rel="stylesheet" href="/css/manajemen/style.css">
    {{-- CSS Khusus Halaman Stok (Animasi & Hidden Button) --}}
    <link rel="stylesheet" href="/css/manajemen/stok.css">
@endpush

@section('content')
<div class="container py-4">
    {{-- Page Header --}}
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-box-seam me-2"></i>Manajemen Stok
                </h1>
                <p class="page-subtitle">Kelola inventori dan stok produk laptop</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div id="flash-success-flag" style="display: none;"></div>
    @endif

    {{-- Stats Cards --}}
    <div class="row">
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="bi bi-archive"></i>
                </div>
                <div class="stats-value">{{ $stats['total'] }}</div>
                <div class="stats-label">Total Produk</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="stats-icon" style="background: rgba(40, 167, 69, 0.15); color: #28a745;">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stats-value">{{ $stats['tersedia'] }}</div>
                <div class="stats-label">Stok Tersedia</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="stats-icon" style="background: rgba(253, 126, 20, 0.15); color: #fd7e14;">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stats-value">{{ $stats['menipis'] }}</div>
                <div class="stats-label">Stok Menipis</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="stats-icon" style="background: rgba(220, 53, 69, 0.15); color: #dc3545;">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="stats-value">{{ $stats['habis'] }}</div>
                <div class="stats-label">Stok Habis</div>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="filter-card">
        <form action="{{ route('stok.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="bi bi-search me-2"></i>Cari Produk
                    </label>
                    <div class="position-relative">
                        <input type="text" 
                               name="search" 
                               id="stok-search-input"
                               value="{{ request('search') }}"
                               class="form-control pe-5" 
                               placeholder="Cari nama produk, merk, atau spesifikasi...">
                        <button type="button" id="stok-clear-search" class="btn btn-sm position-absolute top-50 translate-middle-y end-0 me-2 d-none" title="Bersihkan filter" aria-label="Bersihkan filter">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="bi bi-funnel me-2"></i>Status Stok
                    </label>
                    <select name="status_stok" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="tersedia" {{ request('status_stok') == 'tersedia' ? 'selected' : '' }}>
                            Tersedia (> 5)
                        </option>
                        <option value="menipis" {{ request('status_stok') == 'menipis' ? 'selected' : '' }}>
                            Menipis (1-5)
                        </option>
                        <option value="habis" {{ request('status_stok') == 'habis' ? 'selected' : '' }}>
                            Habis (0)
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">
                        <i class="bi bi-sort-down me-2"></i>Urutkan
                    </label>
                    <select name="sort_by" class="form-select">
                        <option value="nama_produk">Nama</option>
                        <option value="stok" {{ request('sort_by') == 'stok' ? 'selected' : '' }}>Stok</option>
                        <option value="harga_jual" {{ request('sort_by') == 'harga_jual' ? 'selected' : '' }}>Harga</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-search">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Table Section (AJAX Wrapper) --}}
    <div id="stok-table">
        @include('stok.partials.table_wrapper', ['produk' => $produk])
    </div>
</div>

<div class="modal fade modal-confirmation-style" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm-custom">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="modal-icon-wrapper">
            <i class="bi bi-trash3-fill"></i>
        </div>
        <span class="modal-title-text">Konfirmasi Hapus</span>
        <p class="modal-desc-text" id="confirmDeleteMessage">
            Apakah Anda yakin ingin menghapus data stok ini secara permanen?
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-modal-action btn-cancel-soft" data-bs-dismiss="modal">
            <i class="bi bi-x-lg"></i> Batal
        </button>
        <button type="button" id="confirmDeleteBtn" class="btn btn-modal-action btn-delete-solid">
            <i class="bi bi-trash-fill"></i> Hapus
        </button>
      </div>
    </div>
  </div>
</div>

{{-- 
==================================================
✅ MODAL SUKSES (ANIMASI TRASH)
==================================================
--}}
<div class="modal fade modal-confirmation-style" id="successModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        
        {{-- Animasi Sampah (Untuk Delete) --}}
        <div class="anim-trash mx-auto mb-2" aria-hidden="true">
          <svg viewBox="0 0 120 120" width="100" height="100">
            <rect x="30" y="40" width="60" height="70" rx="6" ry="6" fill="#dc3545" opacity="0.12" stroke="#dc3545" stroke-width="3" />
            <rect class="trash-item" x="56" y="-12" width="8" height="18" rx="1" ry="1" fill="#dc3545" />
            <rect class="trash-lid" x="24" y="28" width="72" height="10" rx="4" ry="4" fill="#dc3545" />
            <circle class="dust puff-left" cx="46" cy="108" r="2" fill="#dc3545" opacity="0.4" />
            <circle class="dust puff-right" cx="74" cy="108" r="2" fill="#dc3545" opacity="0.4" />
          </svg>
        </div>

        <h5 class="modal-title-text mt-3">Berhasil</h5>
        <p class="modal-desc-text mt-2">Data stok berhasil dihapus.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-modal-action btn-cancel-soft" data-bs-dismiss="modal">
          <i class="bi bi-check2 me-2"></i>OK
        </button>
      </div>
    </div>
  </div>
</div>
    <x-ai-chat-widget context="stok" />
@endsection

@push('scripts')
    {{-- Memanggil Logic JS Eksternal --}}
    <script src="/js/stok/index.js"></script>
    {{-- Memanggil Logic Popup Baru --}}
    <script src="/js/stok/popup.js"></script>
@endpush