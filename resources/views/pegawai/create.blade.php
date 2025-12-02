@extends('layouts.app')

@section('title', 'Tambah Pegawai - Laptop Store')

@push('styles')
<link rel="stylesheet" href="/css/manajemen/pegawai.css">
@endpush

@section('content')
<div class="container py-4">
    <div class="form-container">
        <div class="form-card">
            <div class="form-header">
                <h1 class="form-title"><i class="bi bi-person-plus-fill me-2"></i>Tambah Pegawai Baru</h1>
                <p class="form-subtitle">Lengkapi form di bawah ini untuk menambahkan pegawai</p>
            </div>

            <form action="{{ route('pegawai.store') }}" method="POST">
                @csrf
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama <span class="required">*</span></label>
                        <input 
                            type="text" 
                            name="name" 
                            class="form-control @error('name') is-invalid @enderror" 
                            value="{{ old('name') }}" 
                            required
                            placeholder="Masukkan nama lengkap"
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <input 
                            type="email" 
                            name="email" 
                            class="form-control @error('email') is-invalid @enderror" 
                            value="{{ old('email') }}" 
                            required
                            placeholder="email@example.com"
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ================================== --}}
                    {{-- === PERUBAHAN DROPDOWN DI SINI === --}}
                    {{-- ================================== --}}
                    <div class="col-md-6">
                        <label class="form-label">Jabatan (Role) <span class="required">*</span></label>
                        <select 
                            name="role" {{-- Ganti name menjadi 'role' --}}
                            class="form-select @error('role') is-invalid @enderror" 
                            required
                        >
                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>Pilih jabatan</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="pegawai" {{ old('role') == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- ================================== --}}
                    {{-- === AKHIR PERUBAHAN DROPDOWN === --}}
                    {{-- ================================== --}}


                    <div class="col-md-6">
                        <label class="form-label">Password <span class="required">*</span></label>
                        <input 
                            type="password" 
                            name="password" 
                            class="form-control @error('password') is-invalid @enderror" 
                            required
                            placeholder="Minimal 6 karakter"
                        >
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-between gap-3 mt-4">
                    <a href="{{ route('pegawai.index') }}" class="btn btn-secondary-custom">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                    <button class="btn btn-primary-custom" type="submit">
                        <i class="bi bi-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection