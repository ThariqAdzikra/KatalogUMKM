@extends('layouts.app')

@section('title', 'Profil Pengguna - Laptop Store')

@push('styles')
    {{-- PATH DIPERBARUI --}}
    <link rel="stylesheet" href="/css/profile/profile.css">
@endpush

@section('content')
<div class="profile-container">
    <div class="profile-header">
        <h1><i class="bi bi-person-circle"></i> Profil Pengguna</h1>
        <p>Kelola informasi profil dan keamanan akun Anda</p>
    </div>

    {{-- Alert Messages --}}
    @if (session('status') === 'profile-updated')
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i>
            Profil berhasil diperbarui!
        </div>
    @endif

    @if (session('status') === 'photo-updated')
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i>
            Foto profil berhasil diperbarui!
        </div>
    @endif

    @if (session('status') === 'photo-deleted')
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i>
            Foto profil berhasil dihapus!
        </div>
    @endif

    @if (session('status') === 'password-updated')
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i>
            Password berhasil diperbarui!
        </div>
    @endif

    
    {{-- Profile Information & Photo --}}
    <div class="profile-card">
        <div class="card-header">
            <h2><i class="bi bi-person-badge"></i> Informasi & Foto Profil</h2>
        </div>
        <div class="card-body">
            
            <div class="row align-items-center">
                
                {{-- Kolom Foto Profil (Kiri) --}}
                <div class="col-lg-4">
                    <div class="photo-section">
                        <div class="photo-wrapper">
                            @if($user->photo)
                                <img src="{{ asset('storage/' . $user->photo) }}" alt="Profile Photo" class="profile-photo">
                            @else
                                <div class="photo-placeholder">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                            @endif
                        </div>
        
                        <div class="photo-actions">
                            <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data" id="photoForm">
                                @csrf
                                @method('PATCH')
                                <input type="file" name="photo" id="photoInput" accept="image/*" style="display: none;" onchange="document.getElementById('photoForm').submit()">
                                <label for="photoInput" class="btn-upload">
                                    <i class="bi bi-cloud-upload"></i> Unggah
                                </label>
                            </form>
        
                            @if($user->photo)
                                <form action="{{ route('profile.photo.delete') }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus foto profil?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete-photo">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
        
                        @error('photo')
                            <div class="text-danger mt-3">{{ $message }}</div>
                        @enderror
        
                        <p class="text-muted mt-3" style="font-size: 0.85rem;">
                            Format: JPG, PNG. Maks 2MB
                        </p>
                    </div>
                </div>

                {{-- Kolom Form Informasi (Kanan) --}}
                <div class="col-lg-8">
                    <form action="{{ route('profile.update') }}" method="POST" class="profile-form">
                        @csrf
                        @method('PATCH')
        
                        <div class="form-group">
                            <label class="form-label" for="name">
                                <i class="bi bi-person"></i> Nama Lengkap
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   class="form-control" 
                                   value="{{ old('name', $user->name) }}" 
                                   required>
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
        
                        <div class="form-group">
                            <label class="form-label" for="email">
                                <i class="bi bi-envelope"></i> Email
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-control" 
                                   value="{{ old('email', $user->email) }}" 
                                   required>
                            @error('email')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
        
                            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                <div class="alert alert-danger mt-3">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    Email Anda belum diverifikasi.
                                    <form method="POST" action="{{ route('verification.send') }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-link p-0" style="vertical-align: baseline;">
                                            Kirim ulang email verifikasi
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
        
                        <div class="form-group">
                            <label class="form-label" for="role">
                                <i class="bi bi-shield-check"></i> Role
                            </label>
                            <input type="text" 
                                   id="role" 
                                   class="form-control" 
                                   value="{{ ucfirst($user->role) }}" 
                                   disabled>
                        </div>
        
                        <div class="text-end">
                            <button type="submit" class="btn-save">
                                <i class="bi bi-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- Password Section --}}
    <div class="profile-card">
        <div class="card-header">
            <h2><i class="bi bi-lock"></i> Ubah Password</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('profile.password.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label" for="current_password">
                        <i class="bi bi-key"></i> Password Saat Ini
                    </label>
                    <input type="password" 
                           id="current_password" 
                           name="current_password" 
                           class="form-control" 
                           required>
                    @error('current_password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class="bi bi-lock-fill"></i> Password Baru
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           required>
                    @error('password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">
                        <i class="bi bi-lock-fill"></i> Konfirmasi Password Baru
                    </label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           class="form-control" 
                           required>
                </div>

                <button type="submit" class="btn-save">
                    <i class="bi bi-shield-check"></i> Ubah Password
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    {{-- PATH DIPERBARUI --}}
    <script src="/js/profile/profile.js"></script>
@endpush
@endsection