<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'nama_supplier' => 'PT. Synnex Metrodata Indonesia',
                'alamat' => 'APL Tower Lantai 42, Jl. Letjen S. Parman Kav. 28, Jakarta Barat 11470',
                'kontak' => '021-29345800',
                'email' => 'contact@synnexmetrodata.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_supplier' => 'PT. Dragon Computer & Communication',
                'alamat' => 'Ruko Mangga Dua Square Blok F No. 25-27, Jl. Gunung Sahari Raya No. 1, Jakarta Utara 14420',
                'kontak' => '021-62312888',
                'email' => 'sales@dragon.co.id',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_supplier' => 'PT. Astrindo Senayasa',
                'alamat' => 'Wisma Kosgoro Lt. 3, Jl. M.H. Thamrin No. 53, Jakarta Pusat 10350',
                'kontak' => '021-31906666',
                'email' => 'info@astrindo.co.id',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_supplier' => 'PT. Datascrip',
                'alamat' => 'Jl. Selaparang Blok B-15 Kav. 9, Kompleks Kemayoran, Jakarta Pusat 10610',
                'kontak' => '021-6544515',
                'email' => 'info@datascrip.co.id',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('supplier')->insert($suppliers);
    }
}
