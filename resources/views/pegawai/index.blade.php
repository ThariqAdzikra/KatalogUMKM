@extends('layouts.app')


@section('title', 'Manajemen Pegawai - ' . App\Models\SiteSetting::get('brand_name', 'KatalogUMKM'))
@push('styles')
<link rel="stylesheet" href="/css/manajemen/style.css">
<link rel="stylesheet" href="/css/manajemen/pegawai.css">
@endpush

@section('content')
<div class="container py-4">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-people-fill me-2"></i>Manajemen Pegawai
                </h1>
                <p class="page-subtitle">Kelola data pegawai yang terdaftar dalam sistem</p>
            </div>
            <a href="{{ route('pegawai.create') }}" class="btn btn-primary-custom">
                <i class="bi bi-plus-circle me-2"></i>Tambah Pegawai
            </a>
        </div>
    </div>
    
    {{-- 
        ✅ PENANDA SUKSES UNTUK JS
        Jika ada session success, div ini akan dirender dan dideteksi oleh popup.js
    --}}
    @if(session('success'))
        <div id="flash-success-flag" data-message="{{ session('success') }}" style="display: none;"></div>
    @endif

    {{-- Error/Warning Alert tetap ditampilkan biasa --}}
    @if(session('warning'))
        <div class="alert alert-warning alert-custom mt-3">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
        </div>
    @endif
     @if(session('error'))
        <div class="alert alert-danger alert-custom mt-3">
            <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
        </div>
    @endif

    {{-- Card untuk Filter --}}
    <div class="filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-md-10">
                <label class="form-label">
                    <i class="bi bi-search me-2"></i>Cari Pegawai
                </label>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    class="form-control"
                    placeholder="Cari berdasarkan nama atau email..."
                >
            </div>
            <div class="col-md-2">
                <button class="btn btn-search w-100 btn-search-js" type="button">
                    <i class="bi bi-search me-2"></i>Cari
                </button>
            </div>
        </div>
    </div>

    {{-- Card untuk Tabel --}}
    <div class="table-card mt-4">
        <div class="table-header">
            <h3 class="table-title">
                <i class="bi bi-table me-2"></i>Daftar Pegawai
            </h3>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Nama Pegawai</th>
                        <th>Email</th>
                        <th>Jabatan (Role)</th>
                        <th style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pegawais as $p)
                        <tr>
                            <td class="text-center">
                                {{ $loop->iteration + ($pegawais->currentPage() - 1) * $pegawais->perPage() }}
                            </td>
                            <td><strong>{{ $p->name }}</strong></td>
                            <td>{{ $p->email }}</td>
                            <td>
                                @if($p->role == 'admin')
                                    <span class="badge bg-primary">Admin</span>
                                @elseif($p->role == 'pegawai')
                                    <span class="badge badge-role-pegawai">Pegawai</span>
                                @else
                                    <span class="badge bg-light text-dark">{{ $p->role ?? '-' }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button 
                                        class="btn-action btn-detail" 
                                        data-bs-toggle="modal"
                                        data-bs-target="#detailModal"
                                        data-nama="{{ $p->name }}"
                                        data-email="{{ $p->email }}"
                                        data-role="{{ $p->role ?? '-' }}"
                                        data-tanggal="{{ $p->created_at->format('d/m/Y') }}"
                                        title="Lihat Detail"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <a 
                                        href="{{ route('pegawai.edit', $p->id) }}" 
                                        class="btn-action btn-edit"
                                        title="Edit Pegawai"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    {{-- 
                                        ✅ FORM HAPUS (DIPERBARUI)
                                        - Menghapus onsubmit inline
                                        - Menambah class 'delete-form'
                                        - Menambah data-confirm-message
                                    --}}
                                    <form 
                                        action="{{ route('pegawai.destroy', $p->id) }}" 
                                        method="POST"
                                        class="delete-form mb-0"
                                        data-confirm-message="Apakah Anda yakin ingin menghapus pegawai {{ $p->name }}?"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            class="btn-action btn-delete" 
                                            type="submit" 
                                            title="Hapus Pegawai"
                                            {{ $p->id === auth()->id() ? 'disabled' : '' }}
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <h4>Belum Ada Data Pegawai</h4>
                                    <p>Tambahkan pegawai pertama untuk memulai pengelolaan data pegawai.</p>
                                    <a href="{{ route('pegawai.create') }}" class="btn btn-primary-custom mt-2">
                                        <i class="bi bi-plus-circle me-2"></i>Tambah Pegawai
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if ($pegawais->hasPages())
            <div class="pagination-container p-3 d-flex justify-content-center">
                {{ $pegawais->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal Detail (Ada perubahan sedikit pada class agar konsisten jika diperlukan) --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 bg-gradient-primary text-white" style="background: var(--primary-wood);">
                <h5 class="modal-title fw-bold" id="detailModalLabel">
                    <i class="bi bi-person-circle me-2"></i>Detail Pegawai
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <div class="customer-info-card">
                    <div class="info-header">
                        <i class="bi bi-person-badge-fill"></i>
                        <span>Informasi Pegawai</span>
                    </div>
                    <div class="info-content">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="bi bi-person"></i> Nama Lengkap
                            </div>
                            <div class="info-value" id="modalNama"></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="bi bi-envelope"></i> Email
                            </div>
                            <div class="info-value" id="modalEmail"></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="bi bi-briefcase"></i> Jabatan (Role)
                            </div>
                            <div class="info-value text-capitalize" id="modalRole"></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="bi bi-calendar-check"></i> Tanggal Bergabung
                            </div>
                            <div class="info-value" id="modalTanggal"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary-modern" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 
==================================================
✅ MODAL KONFIRMASI HAPUS (DESIGN BARU)
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
            Apakah Anda yakin ingin menghapus data pegawai ini secara permanen?
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
✅ MODAL SUKSES (ANIMASI TRASH & CHECK)
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
        <div id="successIconTrash" class="anim-trash mx-auto mb-2" aria-hidden="true" style="display: none;">
          <svg viewBox="0 0 120 120" width="100" height="100">
            <rect x="30" y="40" width="60" height="70" rx="6" ry="6" fill="#dc3545" opacity="0.12" stroke="#dc3545" stroke-width="3" />
            <rect class="trash-item" x="56" y="-12" width="8" height="18" rx="1" ry="1" fill="#dc3545" />
            <rect class="trash-lid" x="24" y="28" width="72" height="10" rx="4" ry="4" fill="#dc3545" />
            <circle class="dust puff-left" cx="46" cy="108" r="2" fill="#dc3545" opacity="0.4" />
            <circle class="dust puff-right" cx="74" cy="108" r="2" fill="#dc3545" opacity="0.4" />
          </svg>
        </div>

        {{-- Animasi Centang (Untuk Create/Edit) --}}
        <div id="successIconCheck" class="modal-icon-wrapper success mx-auto mb-2" style="display: none;">
            <i class="bi bi-check-lg"></i>
        </div>

        <h5 class="modal-title-text mt-3">Berhasil</h5>
        <p class="modal-desc-text mt-2" id="successModalMessage">Data berhasil diproses.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-modal-action btn-cancel-soft" data-bs-dismiss="modal">
          <i class="bi bi-check2 me-2"></i>OK
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
{{-- Memuat jQuery --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

{{-- Pass data ke main.js (Sudah benar) --}}
<script id="pegawai-script-data"
    data-search-url="{{ route('pegawai.search.ajax') }}"
    data-csrf-token="{{ csrf_token() }}"
    data-edit-url-template="{{ route('pegawai.edit', ['pegawai' => 'ID']) }}"
    data-delete-url-template="{{ route('pegawai.destroy', ['pegawai' => 'ID']) }}"
></script>

{{-- Memuat file JS eksternal Anda (main.js tidak perlu diubah) --}}
<script src="/js/pegawai/main.js"></script>

{{-- ✅ Memuat script pop-up baru --}}
<script src="/js/pegawai/popup.js"></script>
@endpush