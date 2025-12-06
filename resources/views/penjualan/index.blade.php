@extends('layouts.app')

@section('title', 'Manajemen Penjualan - ' . App\Models\SiteSetting::get('brand_name'))


@push('styles')
{{-- Memuat style dasar dan style khusus penjualan (pop-up) --}}
<link rel="stylesheet" href="/css/manajemen/style.css">
<link rel="stylesheet" href="/css/manajemen/penjualan.css">
@endpush

@section('content')
<div class="container py-4">

    {{-- Page Header --}}
    <div class="page-header mb-4">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="page-title">
            <i class="bi bi-cash-coin me-2"></i> Manajemen Penjualan
          </h1>
          <p class="page-subtitle">Kelola transaksi penjualan dan histori pelanggan</p>
        </div>

        {{-- Modal Error (Tetap) --}}
        <div class="modal fade" id="penjualanErrorModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-octagon me-2"></i>Terjadi Kesalahan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <p id="penjualanErrorMessage" class="mb-0">Terjadi kesalahan. Coba lagi.</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Statistik Ringkas --}}
    <div class="row">
      <div class="col">
        <div class="stats-card">
          <div class="stats-icon" style="background: rgba(13, 110, 253, 0.15); color: #0d6efd;">
            <i class="bi bi-receipt"></i>
          </div>
          <div class="stats-value">{{ $penjualan->total() ?? $penjualan->count() }}</div>
          <div class="stats-label">Transaksi Selesai</div>
        </div>
      </div>
      <div class="col">
        <div class="stats-card">
          <div class="stats-icon" style="background: rgba(40, 167, 69, 0.15); color: #28a745;">
            <i class="bi bi-people"></i>
          </div>
          <div class="stats-value">{{ $penjualan->groupBy('id_pelanggan')->count() }}</div>
          <div class="stats-label">Pelanggan Hari Ini</div>
        </div>
      </div>
      <div class="col-lg-3">
        <div class="stats-card">
          <div class="stats-icon" style="background: rgba(253, 126, 20, 0.15); color: #fd7e14;">
            <i class="bi bi-wallet2"></i>
          </div>
          <div class="stats-value">
            Rp {{ number_format($penjualan->sum('total_harga'), 0, ',', '.') }}
          </div>
          <div class="stats-label">Pendapatan Hari Ini</div>
        </div>
      </div>
      <div class="col">
        <div class="stats-card">
          <div class="stats-icon" style="background: rgba(23, 162, 184, 0.15); color: #17a2b8;">
            <i class="bi bi-calendar3"></i>
          </div>
          <div class="stats-value">
            {{ $penjualan->where('tanggal_penjualan', '>=', now()->startOfMonth())->count() }}
          </div>
          <div class="stats-label">Transaksi Bulan Ini</div>
        </div>
      </div>
    </div>

    {{-- Filter Section --}}
    <div class="filter-card mb-4">
      <form method="GET" action="{{ route('penjualan.index') }}">
        <div class="row g-3 align-items-end">
          <div class="col-md-4">
            <label class="form-label"><i class="bi bi-search me-2"></i>Cari Pelanggan</label>
            <div class="position-relative">
              <input type="text" name="search" id="penjualan-search-input" class="form-control pe-5" placeholder="Nama pelanggan..." value="{{ request('search') }}">
              <button type="button" id="penjualan-clear-search" class="btn btn-sm position-absolute top-50 translate-middle-y end-0 me-2 d-none" title="Bersihkan filter" aria-label="Bersihkan filter">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label"><i class="bi bi-funnel me-2"></i>Metode Pembayaran</label>
            <select name="metode" class="form-select">
              <option value="">Semua</option>
              <option value="cash" {{ request('metode') == 'cash' ? 'selected' : '' }}>Cash</option>
              <option value="transfer" {{ request('metode') == 'transfer' ? 'selected' : '' }}>Transfer</option>
              <option value="qris" {{ request('metode') == 'qris' ? 'selected' : '' }}>QRIS</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label"><i class="bi bi-calendar me-2"></i>Urutkan Berdasarkan</label>
            <select name="sort" class="form-select">
              <option value="tanggal" {{ request('sort') == 'tanggal' ? 'selected' : '' }}>Tanggal</option>
              <option value="total" {{ request('sort') == 'total' ? 'selected' : '' }}>Total Harga</option>
              <option value="nama" {{ request('sort') == 'nama' ? 'selected' : '' }}>Nama Pelanggan</option>
            </select>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-search w-100">
              <i class="bi bi-search"></i>
            </button>
          </div>
        </div>
      </form>
    </div>

    {{-- Table Section (AJAX updatable) --}}
    <div id="penjualan-table">
      @include('penjualan.partials.table_wrapper', ['penjualan' => $penjualan])
    </div>
    
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
                Apakah Anda yakin ingin menghapus data penjualan ini?
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
            <div id="successIconTrash" class="anim-trash mx-auto mb-2" aria-hidden="true">
              <svg viewBox="0 0 120 120" width="100" height="100">
                <rect x="30" y="40" width="60" height="70" rx="6" ry="6" fill="#dc3545" opacity="0.12" stroke="#dc3545" stroke-width="3" />
                <rect class="trash-item" x="56" y="-12" width="8" height="18" rx="1" ry="1" fill="#dc3545" />
                <rect class="trash-lid" x="24" y="28" width="72" height="10" rx="4" ry="4" fill="#dc3545" />
                <circle class="dust puff-left" cx="46" cy="108" r="2" fill="#dc3545" opacity="0.4" />
                <circle class="dust puff-right" cx="74" cy="108" r="2" fill="#dc3545" opacity="0.4" />
              </svg>
            </div>

            <h5 class="modal-title-text mt-3">Berhasil</h5>
            <p class="modal-desc-text mt-2" id="successMessage">Data penjualan berhasil dihapus.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-modal-action btn-cancel-soft" data-bs-dismiss="modal">
              <i class="bi bi-check2 me-2"></i>OK
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
    <x-ai-chat-widget context="penjualan" />
@endsection

@push('scripts')
{{-- Memuat script popup untuk delete --}}
<script src="/js/penjualan/popup.js"></script>

<script>
  (function(){
    // --- Logic Filter & Tabel AJAX (Disimpan di sini agar tidak memecah fitur filter) ---
    
    const form = document.querySelector('.filter-card form');
    const tableWrap = document.getElementById('penjualan-table');

    // ✅ EKSPOS FUNGSI INI KE WINDOW AGAR BISA DIPANGGIL POPUP.JS
    window.loadPenjualanTable = function(url){
      if (!url) return;
      const fullUrl = new URL(url, window.location.origin);
      
      fetch(fullUrl.toString(), {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(r => r.ok ? r.json() : r.text().then(t => Promise.reject(t)))
      .then(data => {
        if (data && data.html && tableWrap) {
          tableWrap.innerHTML = data.html;
          window.history.pushState({}, '', fullUrl.toString());
        }
      })
      .catch(() => {
        // Fallback: reload page jika AJAX gagal
        window.location.href = fullUrl.toString();
      });
    };

    if (form) {
      form.addEventListener('submit', function(e){
        e.preventDefault();
        const params = new URLSearchParams(new FormData(form));
        const url = form.getAttribute('action') + '?' + params.toString();
        window.loadPenjualanTable(url);
      });

      // Auto submit when filter selects change
      const metode = form.querySelector('select[name="metode"]');
      const sort = form.querySelector('select[name="sort"]');
      [metode, sort].forEach(el => {
        if (el) el.addEventListener('change', () => {
          form.requestSubmit();
        });
      });

      // Clear (X) button behavior
      const clearBtn = document.getElementById('penjualan-clear-search');
      const searchInput = document.getElementById('penjualan-search-input');

      function anyFilterActive(){
        const s = (searchInput?.value || '').trim();
        const m = metode ? (metode.value || '') : '';
        const so = sort ? (sort.value || 'tanggal') : 'tanggal';
        return s.length > 0 || m !== '' || so !== 'tanggal';
      }

      function toggleClear(){
        if (!clearBtn) return;
        clearBtn.classList.toggle('d-none', !anyFilterActive());
      }

      if (searchInput) {
        searchInput.addEventListener('input', toggleClear);
      }
      if (metode) metode.addEventListener('change', toggleClear);
      if (sort) sort.addEventListener('change', toggleClear);

      if (clearBtn) {
        clearBtn.addEventListener('click', function(){
          if (searchInput) searchInput.value = '';
          if (metode) metode.value = '';
          if (sort) sort.value = 'tanggal';
          toggleClear();
          form.requestSubmit();
        });
      }

      // initialize state on load
      toggleClear();
    }

    // Delegate pagination clicks inside table container
    if (tableWrap) {
      tableWrap.addEventListener('click', function(e){
        const a = e.target.closest('a.page-link');
        if (a && a.getAttribute('href')) {
          e.preventDefault();
          window.loadPenjualanTable(a.getAttribute('href'));
        }
      });
    }
    
    // Logic Delete telah dipindahkan ke popup.js
    
  })();
</script>
@endpush