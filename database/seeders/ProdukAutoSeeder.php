<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProdukAutoSeeder extends Seeder
{
    public function run(): void
    {
        // UBAH PATH KE STORAGE
        $path = storage_path('app/public/produk');

        // Pastikan folder ada
        if (!is_dir($path)) {
            $this->command->warn('⚠️  Folder storage/app/public/produk/ tidak ditemukan.');
            $this->command->info('💡 Solusi: Buat folder tersebut dan masukkan gambar produk.');
            return;
        }
        
        $files = glob($path . '/*.jpg'); 

        if (empty($files)) {
            $this->command->warn('⚠️  Tidak ada file .jpg di storage/app/public/produk/');
            return;
        }

        // Ambil semua ID kategori dulu agar query tidak berat di dalam loop
        $kategoriDb = DB::table('kategori')->pluck('id_kategori', 'nama_kategori');

        $kategoriMap = [
            'rog' => 'Gaming', 'tuf' => 'Gaming', 'legion' => 'Gaming', 
            'nitro' => 'Gaming', 'predator' => 'Gaming', 'msi' => 'Gaming',
            'vivobook' => 'Office', 'ideapad' => 'Office', 'thinkpad' => 'Office', 
            'pavilion' => 'Office', 'matebook' => 'Office',
            'xps' => 'Ultrabook', 'swift' => 'Ultrabook', 'zenbook' => 'Ultrabook', 'yoga' => 'Ultrabook',
            'macbook' => 'Workstation', 'envy' => 'Workstation', 'surface' => 'Workstation', 'zbook' => 'Workstation'
        ];

        $insertData = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
            $namePretty = Str::title(str_replace(['-', '_'], ' ', $nameWithoutExt));

            // Tentukan kategori otomatis
            $namaKategori = 'Office'; // Default
            foreach ($kategoriMap as $keyword => $value) {
                if (Str::contains(strtolower($nameWithoutExt), $keyword)) {
                    $namaKategori = $value;
                    break;
                }
            }

            // Ambil ID dari map database
            $idKategori = $kategoriDb[$namaKategori] ?? $kategoriDb['Office'] ?? null;

            // Harga acak sesuai kategori
            $harga = match ($namaKategori) {
                'Gaming'      => rand(14000000, 25000000),
                'Ultrabook'   => rand(10000000, 18000000),
                'Workstation' => rand(20000000, 30000000),
                default       => rand(8000000, 14000000),
            };

            // Garansi otomatis
            $garansi = match ($namaKategori) {
                'Gaming'      => 2,
                'Ultrabook'   => rand(1, 2),
                'Workstation' => rand(2, 3),
                default       => 1,
            };

            $insertData[] = [
                'id_kategori' => $idKategori,
                'nama_produk' => $namePretty,
                'merk' => Str::before($namePretty, ' '),
                'spesifikasi' => 'Processor Generasi Terbaru, RAM Upgradable, SSD NVMe Fast Boot.',
                'harga_beli' => $harga - rand(1000000, 4000000),
                'harga_jual' => $harga,
                'stok' => rand(5, 20),
                'garansi' => $garansi,
                // UBAH PATH PENYIMPANAN DI DB AGAR BISA DIAKSES VIA PUBLIC
                'gambar' => 'storage/produk/' . $filename, 
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Chunk insert untuk performa jika file banyak
        foreach (array_chunk($insertData, 50) as $chunk) {
            DB::table('produk')->insert($chunk);
        }

        $this->command->info('✅ Berhasil menambahkan ' . count($insertData) . ' produk dari folder storage/app/public/produk/');
    }
}