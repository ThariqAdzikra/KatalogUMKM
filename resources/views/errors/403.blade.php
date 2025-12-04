@extends('layouts.app')

@section('title', '403 - Akses Ditolak')

@push('styles')
<style>
    .error-container {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .error-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        padding: 3rem;
        max-width: 600px;
        text-align: center;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }

    .error-icon {
        font-size: 5rem;
        color: #ef4444;
        margin-bottom: 1.5rem;
        animation: shake 0.5s;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }

    .error-code {
        font-size: 6rem;
        font-weight: 800;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 1rem;
        line-height: 1;
    }

    .error-title {
        font-size: 2rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 1rem;
    }

    .error-description {
        color: rgba(255, 255, 255, 0.7);
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 2rem;
    }

    .error-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: white;
        padding: 0.875rem 2rem;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
        color: white;
    }

    .btn-secondary {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: rgba(255, 255, 255, 0.9);
        padding: 0.875rem 2rem;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.3);
        color: #fff;
        transform: translateY(-2px);
    }

    .role-badge {
        display: inline-block;
        background: rgba(59, 130, 246, 0.2);
        border: 1px solid rgba(59, 130, 246, 0.3);
        color: #3b82f6;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        margin: 1rem 0;
    }
</style>
@endpush

@section('content')
<div class="error-container">
    <div class="error-card">
        <div class="error-icon">
            <i class="bi bi-shield-exclamation"></i>
        </div>
        
        <div class="error-code">403</div>
        
        <h1 class="error-title">Akses Ditolak</h1>
        
        <p class="error-description">
            Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.
            @auth
                <br>Anda login sebagai: 
                <span class="role-badge">
                    <i class="bi bi-person-badge"></i>
                    {{ ucfirst(Auth::user()->role) }}
                </span>
            @endauth
        </p>

        <div class="error-actions">
            @auth
                @if(Auth::user()->role === 'superadmin')
                    <a href="{{ route('superadmin.dashboard') }}" class="btn-primary">
                        <i class="bi bi-house-door"></i>
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('katalog.index') }}" class="btn-primary">
                        <i class="bi bi-laptop"></i>
                        Katalog
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn-primary">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Login
                </a>
            @endauth
            
            <a href="javascript:history.back()" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>
</div>
@endsection
