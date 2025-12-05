<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SiteSettingSeeder::class,
            UserSeeder::class,
            KategoriSeeder::class,
            SupplierSeeder::class,
            PelangganSeeder::class,
            ProdukSeeder::class,
            TransaksiSeeder::class,
        ]);
    }
}