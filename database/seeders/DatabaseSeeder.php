<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Jalankan Seeder Utama (User & Kategori)
        $this->call([
            UserSeeder::class,
            KategoriSeeder::class,
        ]);

        // ============================================================
        // 2. SUPPLIER (Dibutuhkan untuk Pembelian)
        // ============================================================
        $supplierData = [
            ['nama_supplier' => 'ASUS Distributor', 'kontak' => '081234567890', 'alamat' => 'Jakarta', 'email' => 'asus@supplier.com'],
            ['nama_supplier' => 'Lenovo Partner', 'kontak' => '082112345678', 'alamat' => 'Bandung', 'email' => 'lenovo@supplier.com'],
            ['nama_supplier' => 'MSI Indonesia', 'kontak' => '085321998877', 'alamat' => 'Surabaya', 'email' => 'msi@supplier.com'],
            ['nama_supplier' => 'Acer Center', 'kontak' => '083355667788', 'alamat' => 'Medan', 'email' => 'acer@supplier.com'],
            ['nama_supplier' => 'Apple Authorized', 'kontak' => '081555666777', 'alamat' => 'Jakarta', 'email' => 'apple@official.com'],
            ['nama_supplier' => 'HP Partner Riau', 'kontak' => '082277889900', 'alamat' => 'Pekanbaru', 'email' => 'hp@supplier.com'],
        ];
        
        // Gunakan updateOrInsert agar tidak duplikat saat seed ulang
        foreach ($supplierData as $s) {
            DB::table('supplier')->updateOrInsert(
                ['email' => $s['email']],
                array_merge($s, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // 3. Jalankan ProdukAutoSeeder (Sekarang mengambil dari storage/app/public/produk)
        $this->call(ProdukAutoSeeder::class);

        // ============================================================
        // 4. PERSIAPAN DATA UNTUK TRANSAKSI
        // ============================================================
        // Ambil produk yang BARU SAJA diinput oleh ProdukAutoSeeder
        $products = DB::table('produk')->get();
        $supplierIds = DB::table('supplier')->pluck('id_supplier')->toArray();

        // Cek apakah produk berhasil di-seed
        if ($products->isEmpty()) {
            $this->command->error('❌ Gagal membuat transaksi: Tidak ada produk ditemukan.');
            $this->command->warn('👉 Pastikan folder storage/app/public/produk/ berisi gambar .jpg');
            $this->command->warn('👉 Pastikan Anda sudah menjalankan: php artisan storage:link');
            return;
        }

        // ============================================================
        // 5. PELANGGAN (30 ORANG DUMMY)
        // ============================================================
        $pelanggan = [];
        for ($i = 1; $i <= 30; $i++) {
            $pelanggan[] = [
                'nama' => "Pelanggan {$i}",
                'no_hp' => '08' . rand(1000000000, 9999999999),
                'email' => "pelanggan{$i}@example.com",
                'alamat' => 'Jl. Melati No. ' . rand(1, 200) . ', Pekanbaru',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        // Insert batch untuk performa
        DB::table('pelanggan')->insertOrIgnore($pelanggan); // insertOrIgnore agar aman jika di-run 2x
        
        // Ambil ID pelanggan yang baru dibuat
        $pelangganIds = DB::table('pelanggan')->pluck('id_pelanggan')->toArray();

        // ============================================================
        // 6. GENERATE PEMBELIAN (Restock barang dari Supplier)
        // ============================================================
        $this->command->info('🔄 Generating Pembelian History...');
        for ($i = 0; $i < 20; $i++) {
            $tanggal = Carbon::now()->subDays(rand(1, 30)); // Random tanggal 30 hari terakhir
            
            $idPembelian = DB::table('pembelian')->insertGetId([
                'id_supplier' => $supplierIds[array_rand($supplierIds)],
                'id_user' => 1, // Asumsi ID 1 adalah Super Admin
                'tanggal_pembelian' => $tanggal,
                'total_harga' => 0, // Nanti diupdate setelah detail
                'created_at' => $tanggal,
                'updated_at' => $tanggal,
            ]);

            $totalHarga = 0;
            // Ambil 1-3 produk random untuk satu transaksi pembelian
            $randomProducts = $products->random(rand(1, 3));

            foreach ($randomProducts as $produk) {
                $jumlah = rand(5, 15); // Stok masuk biasanya lumayan banyak
                $subtotal = $produk->harga_beli * $jumlah;

                DB::table('pembelian_detail')->insert([
                    'id_pembelian' => $idPembelian,
                    'id_produk' => $produk->id_produk,
                    'jumlah' => $jumlah,
                    'harga_satuan' => $produk->harga_beli,
                    'subtotal' => $subtotal,
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ]);

                $totalHarga += $subtotal;
            }

            // Update total harga di tabel induk
            DB::table('pembelian')->where('id_pembelian', $idPembelian)->update(['total_harga' => $totalHarga]);
        }

        // ============================================================
        // 7. GENERATE PENJUALAN (100 Transaksi Realistis)
        // ============================================================
        $this->command->info('🔄 Generating Penjualan History...');
        
        for ($i = 0; $i < 100; $i++) {
            $tanggal = Carbon::now()->subDays(rand(0, 30));
            
            $idPenjualan = DB::table('penjualan')->insertGetId([
                'id_user' => 2, // Asumsi ID 2 adalah Pegawai
                'id_pelanggan' => $pelangganIds[array_rand($pelangganIds)],
                'tanggal_penjualan' => $tanggal,
                'total_harga' => 0,
                'metode_pembayaran' => collect(['cash', 'transfer', 'qris'])->random(),
                'created_at' => $tanggal,
                'updated_at' => $tanggal,
            ]);

            $total = 0;
            // Ambil 1-2 produk random untuk dijual
            $randomProducts = $products->random(rand(1, 2));

            foreach ($randomProducts as $produk) {
                $jumlah = rand(1, 2); // Orang beli laptop jarang > 2
                $subtotal = $produk->harga_jual * $jumlah;

                $idDetail = DB::table('penjualan_detail')->insertGetId([
                    'id_penjualan' => $idPenjualan,
                    'id_produk' => $produk->id_produk,
                    'jumlah' => $jumlah,
                    'harga_satuan' => $produk->harga_jual,
                    'subtotal' => $subtotal,
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ]);

                // Tambahkan Data Garansi Otomatis
                DB::table('garansi')->insert([
                    'id_penjualan_detail' => $idDetail,
                    'tanggal_mulai' => $tanggal,
                    'tanggal_akhir' => Carbon::parse($tanggal)->addYears((int)$produk->garansi),
                    'status' => 'aktif',
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ]);

                $total += $subtotal;
            }

            // Update total harga penjualan
            DB::table('penjualan')->where('id_penjualan', $idPenjualan)->update(['total_harga' => $total]);
        }

        $this->command->info('✅ SEEDER SELESAI! Semua data dummy (User, Produk, Supplier, Transaksi) berhasil dibuat.');
    }
}