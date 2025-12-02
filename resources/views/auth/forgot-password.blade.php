@extends('layouts.app')

@section('title', config('app.name', 'Laptop Store') . ' - Lupa Password')

@push('styles')
    {{-- CSS bawaan login dipakai ulang --}}
    <link rel="stylesheet" href="/css/auth/login.css">
@endpush

@section('content')
    <div id="login-wrapper">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <h1>Lupa Password</h1>
                </div>

                {{-- Session Status --}}
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" autocomplete="off" novalidate>
                    @csrf

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
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus
                                   placeholder="Masukkan Alamat Email Anda">
                        </div>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex flex-column gap-3 mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn-login">
                                <i class="bi bi-send-fill me-1"></i> Kirim Link Reset
                            </button>
                        </div>
                        <div class="col-12">
                            <button class="btn-login">
                                <a href="{{ route('login') }}" class="text-decoration-none text-white">
                                    Kembali
                                </a>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="/js/auth/login.js"></script>
@endpush