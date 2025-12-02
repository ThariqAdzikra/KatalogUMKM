@extends('layouts.app')

@section('title', 'Tambah Pembelian (Langkah 1) - Laptop Store')

@push('styles')
<link rel="stylesheet" href="/css/manajemen/style.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
<link rel="stylesheet" href="/css/manajemen/pembelian.css">
<link rel="stylesheet" href="/css/manajemen/pembelian-responsive.css">
@endpush

@section('content')
<div class="container py-4">
    <div class="form-container">
        <div class="form-card">
            <div class="form-header">
                <h1 class="form-title">
                    <i class="bi bi-person-plus me-2"></i>Tambah Pembelian: Langkah 1
                </h1>
                <p class="form-subtitle">Mulai dengan mengisi data supplier dan tanggal pembelian</p>
            </div>

            <form action="{{ route('pembelian.store') }}" method="POST" id="form-step-1">
                @csrf

                {{-- Informasi Supplier & Pembelian --}}
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="bi bi-info-circle"></i>Informasi Supplier & Tanggal
                    </h3>
                    
                    <div class="row g-3">
                        {{-- NAMA SUPPLIER (Input Utama) --}}
                        <div class="col-md-6">
                            <label for="nama-supplier" class="form-label">
                                Nama Supplier <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('nama_supplier') is-invalid @enderror" 
                                   id="nama-supplier" 
                                   name="nama_supplier"
                                   value="{{ old('nama_supplier') }}"
                                   placeholder="Masukan nama supplier"
                                   required>
                            <div class="form-text text-muted">
                                Masukan nama supplier yang baru atau lama
                            </div>
                            @error('nama_supplier')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- TANGGAL PEMBELIAN --}}
                        <div class="col-md-6">
                            <label for="tanggal-pembelian" class="form-label">
                                Tanggal Pembelian <span class="required">*</span>
                            </label>
                            <input type="date" 
                                   name="tanggal_pembelian" 
                                   id="tanggal-pembelian"
                                   class="form-control @error('tanggal_pembelian') is-invalid @enderror" 
                                   value="{{ old('tanggal_pembelian', date('Y-m-d')) }}"
                                   required>
                            @error('tanggal_pembelian')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- KONTAK (NOMOR HP) SUPPLIER --}}
                        <div class="col-md-6">
                            <label for="kontak-supplier" class="form-label">
                                Nomor HP Supplier
                            </label>
                            <input type="text" 
                                   class="form-control @error('kontak') is-invalid @enderror" 
                                   id="kontak-supplier" 
                                   name="kontak"
                                   value="{{ old('kontak') }}"
                                   placeholder="Akan terisi otomatis atau isi manual">
                            <div id="kontak-helper" class="form-text text-muted">
                                Wajib diisi untuk supplier baru.
                            </div>
                            @error('kontak')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- ALAMAT SUPPLIER --}}
                        <div class="col-md-6">
                            <label for="alamat-supplier" class="form-label">Alamat Supplier</label>
                            <input type="text" 
                                   class="form-control @error('alamat') is-invalid @enderror" 
                                   id="alamat-supplier" 
                                   name="alamat"
                                   value="{{ old('alamat') }}"
                                   placeholder="Akan terisi otomatis atau isi manual">
                            <div id="alamat-helper" class="form-text text-muted">
                                Wajib diisi untuk supplier baru.
                            </div>
                            @error('alamat')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-between flex-wrap gap-3 mt-4">
                    <div class="d-flex gap-3">
                        {{-- Tombol Kembali ke Index --}}
                        <a href="{{ route('pembelian.index') }}"
                           class="btn btn-secondary-custom"
                           title="Kembali ke daftar pembelian"
                           aria-label="Kembali">
                            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>
                            Kembali
                        </a>

                        {{-- Tombol Batal (Kosongkan Form) --}}
                        {{-- PERUBAHAN: Class diganti ke btn-danger dan diberi inline style agar padding sama dengan tombol custom --}}
                        <button type="button"
                                id="btn-batal"
                                class="btn btn-danger"
                                style="padding: 0.875rem 2.5rem; border-radius: 8px; font-weight: 600;"
                                title="Kosongkan form"
                                aria-label="Batal dan kosongkan form">
                            <i class="bi bi-x-circle me-2" aria-hidden="true"></i>
                            Batal 
                        </button>
                    </div>

                    {{-- Tombol Selanjutnya --}}
                    <button type="submit"
                            class="btn btn-primary-custom"
                            title="Lanjut ke langkah berikutnya"
                            aria-label="Selanjutnya">
                        Selanjutnya
                        <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection


{{-- ================================================ --}}
{{-- @push('scripts') --}}
{{-- ================================================ --}}
@push('scripts')
{{-- Load jQuery dan jQuery UI JS --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>

{{-- 
  Tag script ini berfungsi untuk "melewatkan" data dari Blade ke file JS eksternal.
  File create.js akan membaca 'data-search-url' dari tag ini.
--}}
<script id="pembelian-create-data"
    data-search-url="{{ route('supplier.search') }}">
</script>

{{-- Memuat file JS eksternal Anda --}}
<script src="/js/pembelian/create.js"></script>
@endpush