<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            // 1. Tambahkan kolomnya
            $table->unsignedBigInteger('id_kategori')->nullable()->after('merk');

            // 2. Tambahkan foreign key constraint
            $table->foreign('id_kategori')
                  ->references('id_kategori') // Mengacu ke kolom 'id_kategori'
                  ->on('kategori')           // Di tabel 'kategori'
                  ->onDelete('set null')     // Jika kategori dihapus, set produk's id_kategori jadi NULL
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            // Hapus foreign key dulu sebelum hapus kolom
            $table->dropForeign(['id_kategori']);
            $table->dropColumn('id_kategori');
        });
    }
};