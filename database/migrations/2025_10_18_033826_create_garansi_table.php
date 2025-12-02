<?php
// ============================================
// 9. Migration: Garansi Table
// ============================================
// File: database/migrations/xxxx_xx_xx_create_garansi_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('garansi', function (Blueprint $table) {
            $table->id('id_garansi');
            $table->foreignId('id_penjualan_detail')->constrained('penjualan_detail', 'id_penjualan_detail')->onDelete('cascade');
            $table->date('tanggal_mulai');
            $table->date('tanggal_akhir');
            $table->enum('status', ['aktif', 'kadaluarsa'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garansi');
    }
};