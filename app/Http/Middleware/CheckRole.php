<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Jika belum login
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $userRole = Auth::user()->role ?? '';

        // Samakan format huruf (biar "Pegawai" == "pegawai")
        $userRole = strtolower(trim($userRole));
        $roles = array_map(fn($r) => strtolower(trim($r)), $roles);

        // Jika role user tidak sesuai daftar role yang diizinkan
        if (!in_array($userRole, $roles)) {
            return abort(403, 'Akses ditolak: peran tidak memiliki izin ke halaman ini.');
        }

        // Lolos middleware
        return $next($request);
    }
}