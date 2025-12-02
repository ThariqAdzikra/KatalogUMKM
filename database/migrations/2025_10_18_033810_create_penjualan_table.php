<?php

// ============================================
// 7. Migration: Penjualan Table
// ============================================
// File: database/migrations/xxxx_xx_xx_create_penjualan_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id('id_penjualan');
            $table->foreignId('id_user')->constrained('users', 'id')->onDelete('cascade');
            $table->foreignId('id_pelanggan')->nullable()->constrained('pelanggan', 'id_pelanggan')->onDelete('set null');
            $table->dateTime('tanggal_penjualan');
            $table->decimal('total_harga', 15, 2);
            $table->enum('metode_pembayaran', ['cash', 'transfer', 'qris']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};