@extends('layouts.app')

@section('title', 'Edit Pegawai - Laptop Store')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/manajemen/pegawai.css') }}">
@endpush

@section('content')
<div class="container py-4">
    <div class="form-container">
        <div class="form-card">
            <div class="form-header">
                <h1 class="form-title"><i class="bi bi-pencil-square me-2"></i>Edit Pegawai</h1>
                <p class="form-subtitle">Perbarui data pegawai: <strong>{{ $pegawai->name }}</strong></p>
            </div>

            <form action="{{ route('pegawai.update', $pegawai->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama <span class="required">*</span></label>
                        <input 
                            type="text" 
                            name="name" 
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $pegawai->name) }}" 
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
                            value="{{ old('email', $pegawai->email) }}" 
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
                            {{-- Nonaktifkan jika user mengedit diri sendiri --}}
                            {{ $pegawai->id === auth()->id() ? 'disabled' : '' }}
                        >
                            <option value="admin" {{ old('role', $pegawai->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="pegawai" {{ old('role', $pegawai->role) == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                        </select>
                        
                        {{-- Jika dinonaktifkan, kirim role lama sebagai hidden input --}}
                        @if ($pegawai->id === auth()->id())
                            <input type="hidden" name="role" value="{{ $pegawai->role }}">
                            <small class="text-muted">Anda tidak dapat mengubah role Anda sendiri.</small>
                        @endif

                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- ================================== --}}
                    {{-- === AKHIR PERUBAHAN DROPDOWN === --}}
                    {{-- ================================== --}}


                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input 
                            type="password" 
                            name="password" 
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Kosongkan jika tidak ingin mengubah"
                        >
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
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
                        <i class="bi bi-save me-2"></i>Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection