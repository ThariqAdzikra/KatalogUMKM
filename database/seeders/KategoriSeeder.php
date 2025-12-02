<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = ['Gaming', 'Office', 'Ultrabook', 'Workstation'];

        foreach ($kategori as $k) {
            DB::table('kategori')->updateOrInsert(
                ['nama_kategori' => $k],
                [
                    'slug' => Str::slug($k),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}