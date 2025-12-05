<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        // 1. PEMBELIAN (Stok Masuk) - Jan & Feb 2025
        // Kita asumsikan stok awal di ProdukSeeder adalah sisa stok.
        // Jadi kita buat transaksi pembelian yang "pernah terjadi" untuk mengisi stok tersebut (logikanya).
        // Namun, karena tabel produk sudah di-seed dengan stok "saat ini", 
        // transaksi pembelian ini lebih sebagai history bahwa toko pernah beli barang.
        
        $pembelianIds = [];
        
        // Transaksi Pembelian 1: Dari Synnex (ASUS & Lenovo)
        $id_pembelian_1 = DB::table('pembelian')->insertGetId([
            'id_supplier' => 1, // Synnex
            'id_user' => 1, // Budi (Admin)
            'tanggal_pembelian' => Carbon::create(2025, 1, 15),
            'total_harga' => 0, // Nanti diupdate
            'created_at' => Carbon::create(2025, 1, 15),
            'updated_at' => Carbon::create(2025, 1, 15),
        ]);

        $total_1 = 0;
        // Beli ASUS ROG (5 unit)
        $subtotal = 5 * 21000000;
        DB::table('pembelian_detail')->insert([
            'id_pembelian' => $id_pembelian_1,
            'id_produk' => 1, // ASUS ROG
            'jumlah' => 5,
            'harga_satuan' => 21000000,
            'subtotal' => $subtotal,
            'created_at' => Carbon::create(2025, 1, 15),
            'updated_at' => Carbon::create(2025, 1, 15),
        ]);
        $total_1 += $subtotal;

        // Beli Lenovo Legion (3 unit)
        $subtotal = 3 * 26500000;
        DB::table('pembelian_detail')->insert([
            'id_pembelian' => $id_pembelian_1,
            'id_produk' => 2, // Lenovo Legion
            'jumlah' => 3,
            'harga_satuan' => 26500000,
            'subtotal' => $subtotal,
            'created_at' => Carbon::create(2025, 1, 15),
            'updated_at' => Carbon::create(2025, 1, 15),
        ]);
        $total_1 += $subtotal;

        // Update Total Pembelian 1
        DB::table('pembelian')->where('id_pembelian', $id_pembelian_1)->update(['total_harga' => $total_1]);


        // Transaksi Pembelian 2: Dari Dragon (Acer & MSI)
        $id_pembelian_2 = DB::table('pembelian')->insertGetId([
            'id_supplier' => 2, // Dragon
            'id_user' => 3, // Rudi (Staf)
            'tanggal_pembelian' => Carbon::create(2025, 2, 10),
            'total_harga' => 0,
            'created_at' => Carbon::create(2025, 2, 10),
            'updated_at' => Carbon::create(2025, 2, 10),
        ]);

        $total_2 = 0;
        // Beli Acer Swift 3 (10 unit)
        $subtotal = 10 * 8500000;
        DB::table('pembelian_detail')->insert([
            'id_pembelian' => $id_pembelian_2,
            'id_produk' => 9, // Acer Swift 3
            'jumlah' => 10,
            'harga_satuan' => 8500000,
            'subtotal' => $subtotal,
            'created_at' => Carbon::create(2025, 2, 10),
            'updated_at' => Carbon::create(2025, 2, 10),
        ]);
        $total_2 += $subtotal;

        DB::table('pembelian')->where('id_pembelian', $id_pembelian_2)->update(['total_harga' => $total_2]);


        // 2. PENJUALAN (Barang Keluar) - Mar - Dec 2025
        
        // Transaksi Penjualan 1: Andi beli ASUS ROG
        $id_penjualan_1 = DB::table('penjualan')->insertGetId([
            'id_user' => 2, // Siti (Kasir)
            'id_pelanggan' => 1, // Andi
            'tanggal_penjualan' => Carbon::create(2025, 3, 5, 14, 30, 0),
            'total_harga' => 24500000,
            'metode_pembayaran' => 'transfer',
            'created_at' => Carbon::create(2025, 3, 5, 14, 30, 0),
            'updated_at' => Carbon::create(2025, 3, 5, 14, 30, 0),
        ]);

        DB::table('penjualan_detail')->insert([
            'id_penjualan' => $id_penjualan_1,
            'id_produk' => 1, // ASUS ROG
            'jumlah' => 1,
            'harga_satuan' => 24500000,
            'subtotal' => 24500000,
            'created_at' => Carbon::create(2025, 3, 5, 14, 30, 0),
            'updated_at' => Carbon::create(2025, 3, 5, 14, 30, 0),
        ]);

        // Transaksi Penjualan 2: Siti beli Acer Swift
        $id_penjualan_2 = DB::table('penjualan')->insertGetId([
            'id_user' => 2, // Siti (Kasir)
            'id_pelanggan' => 2, // Siti Nurhaliza
            'tanggal_penjualan' => Carbon::create(2025, 4, 12, 10, 15, 0),
            'total_harga' => 9999000,
            'metode_pembayaran' => 'qris',
            'created_at' => Carbon::create(2025, 4, 12, 10, 15, 0),
            'updated_at' => Carbon::create(2025, 4, 12, 10, 15, 0),
        ]);

        DB::table('penjualan_detail')->insert([
            'id_penjualan' => $id_penjualan_2,
            'id_produk' => 9, // Acer Swift 3
            'jumlah' => 1,
            'harga_satuan' => 9999000,
            'subtotal' => 9999000,
            'created_at' => Carbon::create(2025, 4, 12, 10, 15, 0),
            'updated_at' => Carbon::create(2025, 4, 12, 10, 15, 0),
        ]);

        // Transaksi Penjualan 3: Budi beli MacBook Air
        $id_penjualan_3 = DB::table('penjualan')->insertGetId([
            'id_user' => 2, // Siti (Kasir)
            'id_pelanggan' => 3, // Budi Santoso
            'tanggal_penjualan' => Carbon::create(2025, 5, 20, 16, 45, 0),
            'total_harga' => 18999000,
            'metode_pembayaran' => 'cash',
            'created_at' => Carbon::create(2025, 5, 20, 16, 45, 0),
            'updated_at' => Carbon::create(2025, 5, 20, 16, 45, 0),
        ]);

        DB::table('penjualan_detail')->insert([
            'id_penjualan' => $id_penjualan_3,
            'id_produk' => 4, // MacBook Air M2
            'jumlah' => 1,
            'harga_satuan' => 18999000,
            'subtotal' => 18999000,
            'created_at' => Carbon::create(2025, 5, 20, 16, 45, 0),
            'updated_at' => Carbon::create(2025, 5, 20, 16, 45, 0),
        ]);
    }
}
