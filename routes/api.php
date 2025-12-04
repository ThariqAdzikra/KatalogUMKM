<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Api\PenjualanController;
use App\Models\User;


// =======================
// 🔹 LOGIN API
// =======================
Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['Email atau password salah.'],
        ]);
    }

    // Hapus semua token lama, buat yang baru
    $user->tokens()->delete();
    $token = $user->createToken('postman-token')->plainTextToken;

    return response()->json([
        'message' => 'Login berhasil',
        'user' => $user->only('id', 'name', 'email', 'role'),
        'token' => $token
    ]);
});


// =======================
// 🔹 API Terproteksi
// =======================
Route::middleware('auth:sanctum')->group(function () {

    // Data user login
    Route::get('/user', fn(Request $request) => $request->user());

    // Logout
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil'], 200);
    });

    // ============================
    // 🔥 API PENJUALAN (NO MORE CONFLICT)
    // ============================
    Route::apiResource('penjualan', PenjualanController::class)
        ->names([
            'index'   => 'api.penjualan.index',
            'store'   => 'api.penjualan.store',
            'show'    => 'api.penjualan.show',
            'update'  => 'api.penjualan.update',
            'destroy' => 'api.penjualan.destroy',
        ]);
});

// =======================
// 🔹 Public API Routes (No Authentication Required)
// =======================
// Customer search for Select2 autocomplete
Route::get('/pelanggan/search', [\App\Http\Controllers\PelangganController::class, 'search'])
    ->name('api.pelanggan.search');
