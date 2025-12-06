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
    PelangganController,
    NotificationController
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

    // PEMBELIAN MANAGEMENT
    // Create is EXCLUSIVE to Pegawai (Superadmin cannot Create)
    Route::middleware(['role:pegawai'])->group(function() {
        Route::get('pembelian/create', [PembelianController::class, 'create'])->name('pembelian.create');
    });

    // Resource Route untuk Index, Show, Edit, Update, Destroy (Shared, except create/store)
    Route::resource('pembelian', PembelianController::class)->except(['store', 'create']);
    // Route POST untuk Store Header didefinisikan terpisah
    Route::post('pembelian', [PembelianController::class, 'storeHeader'])->name('pembelian.store');
    // Route untuk finalize pembelian dan kirim notifikasi
    Route::post('pembelian/{pembelian}/finalize', [PembelianController::class, 'finalize'])->name('pembelian.finalize');

    // ROUTE SUPPLIER & PELANGGAN
    Route::get('/supplier/search', [SupplierController::class, 'search'])->name('supplier.search');
    Route::post('/supplier/store-ajax', [SupplierController::class, 'storeAjax'])->name('supplier.store-ajax');

    Route::resource('pelanggan', PelangganController::class)->except(['create', 'store']);
    Route::get('/pelanggan/search/ajax', [PelangganController::class, 'searchAjax'])->name('pelanggan.search.ajax');

    // ========================
    // 🔔 Notification Routes
    // ========================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread', [NotificationController::class, 'getUnread'])->name('unread');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-delete', [NotificationController::class, 'bulkDelete'])->name('bulk-delete');
    });

});

Route::middleware(['auth', 'role:pegawai'])
    ->prefix('penjualan')
    ->name('penjualan.')
    ->group(function () {
        // POS / Transactions (Pegawai Only - Superadmin cannot access POS)
        Route::get('/create', [PenjualanController::class, 'create'])->name('create');
        Route::post('/', [PenjualanController::class, 'store'])->name('store');
        Route::get('/print', [PenjualanController::class, 'print'])->name('print');
        Route::get('/struk/{penjualan}', [PenjualanController::class, 'generateStrukPdf'])->name('struk');
    });

Route::middleware(['auth', 'role:superadmin'])
    ->prefix('penjualan')
    ->name('penjualan.')
    ->group(function () {
        // Management / History (SuperAdmin Only)
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
    
    // [BARU] Route untuk AI Forecasting
    Route::get('/superadmin/dashboard/forecast', [DashboardController::class, 'getForecastingData'])
        ->name('superadmin.dashboard.forecast');

    // [BARU] Route untuk AI Agent Chat
    Route::post('/superadmin/ai/chat', [\App\Http\Controllers\SuperAdmin\AiAgentController::class, 'chat'])
        ->name('superadmin.ai.chat');

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