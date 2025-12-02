@extends('layouts.app')

@section('title', config('app.name', 'Laptop Store') . ' - Reset Password')

@push('styles')
    {{-- CSS bawaan login dipakai ulang --}}
    <link rel="stylesheet" href="/css/auth/login.css">
@endpush

@section('content')
    <div id="login-wrapper">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <h1>Reset Password</h1>
                </div>

                <form method="POST" action="{{ route('password.store') }}" autocomplete="off" novalidate>
                    @csrf

                    <!-- Token Reset Password -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email -->
                    <div class="form-group">
                        <div class="input-wrapper">
                            <div class="input-icon">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <input id="email" 
                                   type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   name="email" 
                                   value="{{ old('email', $request->email) }}" 
                                   required 
                                   autofocus
                                   readonly
                                   placeholder="Alamat Email">
                        </div>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password Baru -->
                    <div class="form-group mt-3">
                        <div class="input-wrapper">
                            <div class="input-icon">
                                <i class="bi bi-lock-fill"></i>
                            </div>
                            <input id="password" 
                                   type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   name="password" 
                                   required 
                                   autocomplete="new-password"
                                   placeholder="Kata Sandi Baru">

                            <button type="button" class="password-toggle" onclick="togglePassword('password', 'toggleIconReset1')">
                                <i class="bi bi-eye-slash-fill" id="toggleIconReset1"></i>
                            </button>
                        </div>
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="form-group mt-3">
                        <div class="input-wrapper">
                            <div class="input-icon">
                                <i class="bi bi-lock-fill"></i>
                            </div>
                            <input id="password_confirmation" 
                                   type="password" 
                                   class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   name="password_confirmation" 
                                   required 
                                   autocomplete="new-password"
                                   placeholder="Konfirmasi Kata Sandi">

                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', 'toggleIconReset2')">
                                <i class="bi bi-eye-slash-fill" id="toggleIconReset2"></i>
                            </button>
                        </div>
                        @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex flex-column gap-3 mt-4">
                        <button type="submit" class="btn-login">
                            <i class="bi bi-check2-circle me-1"></i> Reset Password
                        </button>
                        <button class="btn-login">
                            <a href="{{ route('login') }}" class="text-decoration-none text-white">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Login
                            </a>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="/js/auth/login.js"></script>
@endpush
