@extends('layouts.app')

@section('title', config('app.name', 'Laptop Store') . ' - Login')
@section('hide_navbar', true)
@section('hide_footer', true)

@push('styles')
    {{-- Memuat CSS spesifik untuk halaman login --}}
    <link rel="stylesheet" href="/css/auth/login.css">
@endpush

@section('content')
    <div id="login-wrapper">

        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <h1>Login</h1>
                </div>

                <form method="POST" action="{{ route('login') }}" autocomplete="off" novalidate>
                    @csrf

                    <div class="form-group">
                        <div class="input-wrapper">
                            <div class="input-icon">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <input id="email" 
                                   type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus
                                   autocomplete="off" 
                                   placeholder="Email Address">
                        </div>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
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
                                   placeholder="Password">

                            <button type="button" class="password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                                <i class="bi bi-eye-slash-fill" id="toggleIcon1"></i>
                            </button>
                        </div>
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-options">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Remember Me
                            </label>
                        </div>
                        
                        @if (Route::has('password.request'))
                            <a class="forgot-password-link" href="{{ route('password.request') }}">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <button type"submit" class="btn-login">
                        Login
                    </button>

                </form>
            </div>
        </div>
        
    </div>
@endsection

@push('scripts')
    {{-- Memuat JS spesifik untuk halaman login --}}
    <script src="/js/auth/login.js"></script>
@endpush