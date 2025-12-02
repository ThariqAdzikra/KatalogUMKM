<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'POS - Laptop Store')</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    {{-- Fonts & Icons --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Global CSS --}}
    <link rel="stylesheet" href="/css/layouts/app.css">
    
    {{-- POS Specific CSS --}}
    @stack('styles')

    <style>
        body {
            overflow: hidden; /* Prevent global scroll */
        }
        .pos-header {
            height: 60px;
            background: var(--surface-1);
            border-bottom: 1px solid var(--border-medium);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        .pos-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--text-primary);
            text-decoration: none;
        }
        .pos-brand img {
            height: 32px;
        }
        .pos-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .pos-clock {
            font-family: 'Monaco', monospace;
            font-weight: 600;
            color: var(--primary-cyan);
            background: rgba(6, 182, 212, 0.1);
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
        }
        .btn-exit {
            color: var(--danger);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .btn-exit:hover {
            background: rgba(239, 68, 68, 0.1);
        }
    </style>
</head>
<body>

    {{-- POS Header --}}
    <header class="pos-header">
        <a href="{{ route('dashboard') }}" class="pos-brand">
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
            <span>POS System</span>
        </a>

        <div class="pos-actions">
            <div id="digital-clock" class="pos-clock">00:00:00</div>
            
            <div class="dropdown">
                <button class="btn btn-link text-white text-decoration-none dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                    <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    @yield('content')

    {{-- Toast Container --}}
    <div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple Digital Clock
        function updateClock() {
            const now = new Date();
            document.getElementById('digital-clock').innerText = now.toLocaleTimeString('id-ID', { hour12: false });
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    @stack('scripts')
</body>
</html>
