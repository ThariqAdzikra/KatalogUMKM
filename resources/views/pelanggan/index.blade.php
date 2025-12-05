@extends('layouts.app')

@section('title', 'Manajemen Pelanggan - ' . App\Models\SiteSetting::get('brand_name'))


@push('styles')
<link rel="stylesheet" href="/css/manajemen/style.css">
<link rel="stylesheet" href="/css/manajemen/pelanggan.css">
@endpush


@section('content')
<div class="container py-4">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="bi bi-people me-2"></i>Manajemen Pelanggan
            </h1>
            <p class="page-subtitle">Kelola data pelanggan yang terdaftar dalam sistem</p>
        </div>
    </div>

    {{-- 
        PENANDA SUKSES UNTUK JS
        Jika ada session success, div ini akan dirender dan dideteksi oleh popup.js
        untuk menampilkan Modal Sukses yang estetik.
    --}}
    @if(session('success'))
        <div id="flash-success-flag" style="display: none;"></div>
        {{-- Fallback: Tampilkan pesan teks kecil di console/debug jika perlu --}}
    @endif

    {{-- Error Alert tetap ditampilkan biasa --}}
    @if($errors->any())
        <div class="alert alert-danger alert-custom mt-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
                <strong>Terdapat kesalahan!</strong>
                <ul class="mb-0 mt-2 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="filter-card mt-4">
        <form method="GET" action="{{ route('pelanggan.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-10">
                    <label class="form-label" for="searchInput">
                        <i class="bi bi-search me-2"></i>Cari Pelanggan
                    </label>
                    <input
                        type="text"
                        name="query"
                        id="searchInput"
                        class="form-control"
                        placeholder="Cari nama, email, atau nomor HP..."
                        value="{{ request('query', '') }}"
                    >
                </div>

                <div class="col-md-2">
                    <button class="btn btn-search w-100" type="button" id="btnSearch">
                        <i class="bi bi-search me-2"></i>Cari
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="table-card mt-4">
        <div class="table-header">
            <h3 class="table-title">
                <i class="bi bi-table me-2"></i>Daftar Pelanggan
            </h3>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Nama Pelanggan</th>
                        <th>No HP</th>
                        <th>Email</th>
                        <th>Alamat</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pelanggan as $p)
                        <tr>
                            <td class="text-center">
                                {{ $loop->iteration + ($pelanggan->currentPage() - 1) * $pelanggan->perPage() }}
                            </td>
                            <td><strong>{{ $p->nama }}</strong></td>
                            <td>{{ $p->no_hp ?? '-' }}</td>
                            <td>{{ $p->email ?? '-' }}</td>
                            <td>{{ $p->alamat ?? '-' }}</td>
                            <td>
                                <div class="d-flex gap-2 justify-content-center">
                                    
                                    <a href="{{ route('pelanggan.show', $p->id_pelanggan) }}"
                                       class="btn-action btn-info"
                                       title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    {{-- 
                                        FORM HAPUS (MODIFIED)
                                        - Menghapus onsubmit inline
                                        - Menambah class 'delete-form'
                                        - Menambah data-confirm-message
                                    --}}
                                    <form
                                        action="{{ route('pelanggan.destroy', $p->id_pelanggan) }}"
                                        method="POST"
                                        class="mb-0 delete-form"
                                        data-confirm-message="Apakah Anda yakin ingin menghapus pelanggan {{ $p->nama }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-action btn-delete" type="submit" title="Hapus Pelanggan">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state text-center py-4">
                                    <i class="bi bi-inbox"></i>
                                    <h4 class="mt-2">Belum Ada Data Pelanggan</h4>
                                    <p>Data pelanggan baru akan otomatis ditambahkan saat transaksi di kasir.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pelanggan->hasPages())
            <div class="d-flex justify-content-center align-items-center p-4">
                {{ $pelanggan->links() }}
            </div>
        @endif
    </div>
</div>

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
            Apakah Anda yakin ingin menghapus data ini secara permanen?
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
        {{-- Animasi Tempat Sampah SVG --}}
        <div class="anim-trash mx-auto mb-2" aria-hidden="true">
          <svg viewBox="0 0 120 120" width="100" height="100">
            <rect x="30" y="40" width="60" height="70" rx="6" ry="6" fill="#dc3545" opacity="0.12" stroke="#dc3545" stroke-width="3" />
            <rect class="trash-item" x="56" y="-12" width="8" height="18" rx="1" ry="1" fill="#dc3545" />
            <rect class="trash-lid" x="24" y="28" width="72" height="10" rx="4" ry="4" fill="#dc3545" />
            <circle class="dust puff-left" cx="46" cy="108" r="2" fill="#dc3545" opacity="0.4" />
            <circle class="dust puff-right" cx="74" cy="108" r="2" fill="#dc3545" opacity="0.4" />
          </svg>
        </div>
        <p class="mt-2">Data pelanggan berhasil dihapus.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">
          <i class="bi bi-check2 me-2"></i>OK
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script id="pelanggan-script-data"
    data-search-url="{{ route('pelanggan.search.ajax') }}"
    data-base-url="{{ url('pelanggan') }}"
    data-csrf-token="{{ csrf_token() }}"
></script>

<script src="/js/pelanggan/main.js"></script>

{{-- Memuat script pop-up baru --}}
<script src="/js/pelanggan/popup.js"></script>
@endpush