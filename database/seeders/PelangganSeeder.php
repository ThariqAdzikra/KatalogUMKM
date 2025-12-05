<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PelangganSeeder extends Seeder
{
    public function run(): void
    {
        $pelanggan = [
            [
                'nama' => 'Andi Pratama',
                'alamat' => 'Jl. Dago Asri No. 12, Bandung, Jawa Barat',
                'no_hp' => '081234567890',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Siti Nurhaliza',
                'alamat' => 'Komplek Permata Hijau Blok C5, Jakarta Selatan',
                'no_hp' => '081398765432',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Budi Santoso',
                'alamat' => 'Jl. Pemuda No. 45, Semarang, Jawa Tengah',
                'no_hp' => '081567890123',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Dewi Lestari',
                'alamat' => 'Perumahan CitraLand Surabaya, Cluster Raffles No. 8',
                'no_hp' => '081123456789',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Eko Kurniawan',
                'alamat' => 'Jl. Malioboro No. 10, Yogyakarta',
                'no_hp' => '085712345678',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Rina Wati',
                'alamat' => 'Jl. Sudirman Kav. 50, Jakarta Pusat',
                'no_hp' => '081298765432',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Hendra Gunawan',
                'alamat' => 'Jl. Gajah Mada No. 100, Medan, Sumatera Utara',
                'no_hp' => '081345678901',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Maya Sari',
                'alamat' => 'Jl. Udayana No. 88, Denpasar, Bali',
                'no_hp' => '081987654321',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('pelanggan')->insert($pelanggan);
    }
}
