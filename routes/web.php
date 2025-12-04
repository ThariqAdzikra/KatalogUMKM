<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\{
    ProfileController,
    KatalogController,
    StokController,
    Pembelian\PembelianController,
    Penjualan\PenjualanController,
    SuperAdmin\DashboardController,
    SuperAdmin\SiteSettingController,
    PegawaiController,
    SupplierController,
    PelangganController
};

// ========================
// 🔹 Public Routes
// ========================
Route::get('/', [KatalogController::class, 'index'])->name('home');
Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog.index');
Route::get('stok/{stok}', [StokController::class, 'show'])->name('stok.show');

// ========================
// 🔹 Authenticated Routes
// ========================
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard Redirect
    Route::get('/dashboard', function () {
        $user = Auth::user();
        return $user->role === 'superadmin'
            ? redirect()->route('superadmin.dashboard')
            : redirect()->route('katalog.index');
    })->name('dashboard');

    // ========================
    // 🔹 Profile
    // ========================
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::patch('/profile/photo', 'updatePhoto')->name('profile.photo.update');
        Route::delete('/profile/photo', 'deletePhoto')->name('profile.photo.delete');
        Route::put('/profile/password', 'updatePassword')->name('profile.password.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    // ROUTE STOK
    // Mendefinisikan 'create' secara manual agar bisa menerima parameter opsional {pembelian?}
    Route::get('stok/create/{pembelian?}', [StokController::class, 'create'])->name('stok.create');
    // Menggunakan Resource Route untuk Index, Store, Edit, Update, Destroy
    // show dan create dikecualikan karena sudah didefinisikan di atas/bawah
    Route::resource('stok', StokController::class)->except(['show', 'create']);

    // ROUTE PEMBELIAN
    // Resource Route untuk Index, Create, Show, Edit, Update, Destroy
    Route::resource('pembelian', PembelianController::class)->except(['store']);
    // Route POST untuk Store Header didefinisikan terpisah
    Route::post('pembelian', [PembelianController::class, 'storeHeader'])->name('pembelian.store');

    // ROUTE SUPPLIER & PELANGGAN
    Route::get('/supplier/search', [SupplierController::class, 'search'])->name('supplier.search');
    Route::post('/supplier/store-ajax', [SupplierController::class, 'storeAjax'])->name('supplier.store-ajax');

    Route::resource('pelanggan', PelangganController::class)->except(['create', 'store']);
    Route::get('/pelanggan/search/ajax', [PelangganController::class, 'searchAjax'])->name('pelanggan.search.ajax');

});

Route::middleware(['auth', 'role:pegawai,superadmin'])
    ->prefix('penjualan')
    ->name('penjualan.')
    ->group(function () {

        // Route untuk Pegawai & Superadmin
        Route::get('/create', [PenjualanController::class, 'create'])->name('create');
        Route::post('/', [PenjualanController::class, 'store'])->name('store');
        Route::get('/print', [PenjualanController::class, 'print'])->name('print');

        Route::get('/struk/{penjualan}', [PenjualanController::class, 'generateStrukPdf'])->name('struk');

        // Route khusus Superadmin (Middleware 'role' di sini sudah mengizinkan superadmin)
        Route::get('/', [PenjualanController::class, 'index'])->name('index');
        Route::get('/export', [PenjualanController::class, 'export'])->name('export');
        Route::get('/laporan', [PenjualanController::class, 'laporan'])->name('laporan');
        Route::get('/{penjualan}/edit', [PenjualanController::class, 'edit'])->name('edit');
        Route::put('/{penjualan}', [PenjualanController::class, 'update'])->name('update');
        Route::delete('/{penjualan}', [PenjualanController::class, 'destroy'])->name('destroy');
        Route::get('/pembelian/{id_pembelian}', [PembelianController::class, 'show'])->name('pembelian.show');
        Route::get('/{id}', [PenjualanController::class, 'show'])->name('show');

    });



Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/superadmin/dashboard', [DashboardController::class, 'index'])->name('superadmin.dashboard');
    Route::get('/superadmin/dashboard/chart-data', [DashboardController::class, 'getChartData'])
        ->name('superadmin.dashboard.chartData');

    Route::resource('/pegawai', PegawaiController::class);
    Route::get('/pegawai/search/ajax', [PegawaiController::class, 'searchAjax'])->name('pegawai.search.ajax');

    // Site Settings
    Route::get('/superadmin/settings', [SiteSettingController::class, 'index'])->name('superadmin.settings');
    Route::post('/superadmin/settings', [SiteSettingController::class, 'update'])->name('superadmin.settings.update');
    Route::delete('/superadmin/settings/carousel/{index}', [SiteSettingController::class, 'deleteCarouselImage'])->name('superadmin.settings.carousel.delete');

});

use App\Http\Controllers\SuperAdmin\PegawaiController as PegawaiSuperAdminController;
Route::prefix('superadmin')->middleware(['auth', 'role:superadmin'])->group(function () {
    Route::resource('pegawai', PegawaiSuperAdminController::class);
});


Route::view('/about', 'about')->name('about');

require __DIR__ . '/auth.php';