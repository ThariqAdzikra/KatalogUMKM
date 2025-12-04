@extends('layouts.app')

@section('title', 'Data Pembelian - Laptop Store')

@push('styles')
{{-- Flatpickr CSS (Load BEFORE custom CSS) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">

{{-- Memuat style dasar dan style pembelian yang sudah digabung dengan animasi/modal --}}
<link rel="stylesheet" href="/css/manajemen/style.css">
<link rel="stylesheet" href="/css/manajemen/pembelian.css">
<link rel="stylesheet" href="/css/manajemen/responsive.css">
<link rel="stylesheet" href="/css/manajemen/pembelian-responsive.css">
@endpush

@section('content')
<div class="container py-4">
    {{-- Page Header --}}
    <div class="page-header">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-cart"></i> Data Pembelian
                </h1>
                <p class="page-subtitle">Kelola data pembelian dari supplier</p>
            </div>

            @if(auth()->check() && auth()->user()->role === 'pegawai')
            <a href="{{ route('pembelian.create') }}" class="btn-add">
                <i class="bi bi-plus-circle me-2"></i>Tambah Pembelian
            </a>
            @endif

        </div>
    </div>

    {{-- Filter Card --}}
    <div class="filter-card">
        <form action="{{ route('pembelian.index') }}" method="GET" id="filterFormPembelian">
            <div class="row g-3 align-items-end">
                
                <!-- Search Supplier -->
                <div class="col-12 col-md-5"> 
                    <label class="form-label">
                        <i class="bi bi-search me-2"></i>Cari Supplier
                    </label>
                    <div class="position-relative">
                      <input type="text" 
                             id="searchInputPembelian"
                             name="search" 
                             value="{{ request('search') }}"
                             class="form-control pe-5" 
                             placeholder="Cari berdasarkan nama supplier...">
                      <button type="button" 
                              id="clearFiltersPembelian" 
                              class="btn btn-sm position-absolute top-50 translate-middle-y end-0 me-2 d-none"
                              title="Bersihkan filter" aria-label="Bersihkan filter">
                        <i class="bi bi-x-lg"></i>
                      </button>
                    </div>
                </div>

                <!-- Dari Tanggal -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label">
                        <i class="bi bi-calendar-start me-2"></i>Dari Tanggal
                    </label>
                    <input type="text" 
                           name="dari_tanggal" 
                           value="{{ request('dari_tanggal') }}"
                           class="form-control date-picker"
                           placeholder="Pilih tanggal">
                </div>

                <!-- Sampai Tanggal -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label">
                        <i class="bi bi-calendar-end me-2"></i>Sampai Tanggal
                    </label>
                    <input type="text" 
                           name="sampai_tanggal" 
                           value="{{ request('sampai_tanggal') }}"
                           class="form-control date-picker"
                           placeholder="Pilih tanggal">
                </div>

                {{-- Kolom Tombol Search --}}
                <div class="col-12 col-md-1"> 
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

    {{-- Table Section (AJAX updatable) --}}
    <div id="pembelian-table">
        @include('pembelian.partials.table_wrapper', ['pembelian' => $pembelian])
    </div>
</div>


@push('scripts')
{{-- Flatpickr JS --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".date-picker", {
            dateFormat: "Y-m-d",
            locale: "id",
            allowInput: true,
            altInput: true,
            altFormat: "j F Y",
            theme: "material_blue" // Use light theme as requested
        });
    });
</script>
@endpush

{{-- 
==================================================
✅ MODAL KONFIRMASI HAPUS (NEW DESIGN)
==================================================
--}}
<div class="modal fade modal-confirmation-style" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm-custom">
    <div class="modal-content">
      {{-- Header Minimalis --}}
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      {{-- Body dengan Icon Besar --}}
      <div class="modal-body">
        <div class="modal-icon-wrapper">
            <i class="bi bi-trash3-fill"></i>
        </div>
        <span class="modal-title-text">Konfirmasi Hapus</span>
        <p class="modal-desc-text" id="confirmDeleteMessage">
            Apakah Anda yakin ingin menghapus data pembelian ini secara permanen?
        </p>
      </div>

      {{-- Footer dengan Flexbox Equal Width --}}
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

{{-- Modal Sukses --}}
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-trash3 me-2 icon-animate-wiggle"></i>
          Berhasil
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="anim-trash mx-auto mb-2" aria-hidden="true">
          <svg viewBox="0 0 120 120" width="100" height="100">
            <rect x="30" y="40" width="60" height="70" rx="6" ry="6" fill="#dc3545" opacity="0.12" stroke="#dc3545" stroke-width="3" />
            <rect class="trash-item" x="56" y="-12" width="8" height="18" rx="1" ry="1" fill="#dc3545" />
            <rect class="trash-lid" x="24" y="28" width="72" height="10" rx="4" ry="4" fill="#dc3545" />
            <circle class="dust puff-left" cx="46" cy="108" r="2" fill="#dc3545" opacity="0.4" />
            <circle class="dust puff-right" cx="74" cy="108" r="2" fill="#dc3545" opacity="0.4" />
          </svg>
        </div>
        <p id="successMessage" class="mt-2">Berhasil diproses.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">
          <i class="bi bi-check2 me-2"></i>OK
        </button>
      </div>
    </div>
  </div>
</div>

{{-- 
    Element Penanda Sukses untuk JS 
    (JS akan membaca ID ini untuk memicu modal saat reload page) 
--}}
@if(session('success'))
    <div id="flash-success-flag" style="display: none;"></div>
@endif

@endsection

@push('scripts')
{{-- Memuat script terpisah untuk logika popup --}}
<script src="/js/pembelian/popup.js"></script>
@endpush