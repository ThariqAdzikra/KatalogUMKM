@extends('layouts.app')

@section('title', 'Edit Pelanggan - ' . App\Models\SiteSetting::get('brand_name'))


@push('styles')
<link rel="stylesheet" href="/css/manajemen/style.css">
<link rel="stylesheet" href="/css/manajemen/pelanggan.css">
@endpush

@section('content')
<div class="container py-4">
@if($errors->any())
    <div class="alert alert-danger alert-custom">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div>
            <strong>Terdapat kesalahan!</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="form-container">
    <div class="form-header">
        <h2><i class="bi bi-clipboard-check"></i> Form Edit Data Pelanggan</h2>
        <p>Perbarui informasi pelanggan di bawah ini dengan benar</p>
    </div>

    <div class="form-body">
        <form method="POST" action="{{ route('pelanggan.update', $pelanggan->id_pelanggan) }}">
            @csrf
            @method('PUT')

            <div class="section-card">
                <h3 class="section-title">
                    <i class="bi bi-person-badge-fill"></i>
                    Informasi Pribadi
                </h3>
                <p class="text-muted mb-4" style="font-size: 0.9rem;">
                    Anda hanya dapat mengedit informasi pribadi pelanggan. Riwayat transaksi dan pembelian tidak dapat diubah dari menu ini.
                </p>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama" class="form-label">
                                <i class="bi bi-person"></i>
                                Nama Lengkap <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control @error('nama') is-invalid @enderror" 
                                id="nama"
                                name="nama"
                                value="{{ old('nama', $pelanggan->nama) }}"
                                placeholder="Masukkan nama lengkap pelanggan"
                                required
                            >
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="no_hp" class="form-label">
                                <i class="bi bi-telephone"></i>
                                Nomor HP <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control @error('no_hp') is-invalid @enderror" 
                                id="no_hp"
                                name="no_hp"
                                value="{{ old('no_hp', $pelanggan->no_hp) }}"
                                placeholder="Contoh: 081234567890"
                                required
                                pattern="^08[0-9]{8,11}$"
                                maxlength="13"
                                minlength="10"
                                title="Nomor HP harus diawali 08 dan terdiri dari 10–13 digit angka"
                            >
                            <small class="input-info">
                                <i class="bi bi-info-circle"></i>
                                Format: 08xxxxxxxxxx (maksimal 13 digit)
                            </small>
                            @error('no_hp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope"></i>
                                Email <span class="required">*</span>
                            </label>
                            <input 
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                value="{{ old('email', $pelanggan->email) }}"
                                placeholder="contoh@email.com"
                                required
                                pattern="^[^\s@]+@[^\s@]+\.[^\s@]+$"
                                title="Masukkan email yang valid, harus mengandung @ dan domain (contoh: nama@email.com)"
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="alamat" class="form-label">
                                <i class="bi bi-geo-alt"></i>
                                Alamat <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control @error('alamat') is-invalid @enderror" 
                                id="alamat"
                                name="alamat"
                                value="{{ old('alamat', $pelanggan->alamat) }}"
                                placeholder="Masukkan alamat lengkap"
                                required
                            >
                            @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="button-group">
                <a href="{{ route('pelanggan.show', $pelanggan->id_pelanggan) }}" class="btn-cancel">
                    <i class="bi bi-arrow-left"></i>
                    Batal
                </a>
                <button type="submit" class="btn-submit">
                    <i class="bi bi-save"></i>
                    Update Data
                </button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection

@push('scripts')
    @endpush