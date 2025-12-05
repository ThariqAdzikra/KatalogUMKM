<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Gaming Laptops
        $gaming = [
            [
                'id_kategori' => 1,
                'nama_produk' => 'ASUS ROG Zephyrus G14',
                'merk' => 'ASUS',
                'spesifikasi' => 'AMD Ryzen 9 6900HS, RTX 3060, 16GB DDR5, 1TB SSD, 14" QHD 120Hz',
                'harga_beli' => 21000000,
                'harga_jual' => 24500000,
                'stok' => 5,
                'gambar' => 'rog-zephyrus-g14.jpg',
                'garansi' => 24,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 1,
                'nama_produk' => 'Lenovo Legion 5 Pro',
                'merk' => 'Lenovo',
                'spesifikasi' => 'Intel Core i7-12700H, RTX 3070 Ti, 32GB DDR5, 1TB SSD, 16" WQHD+ 165Hz',
                'harga_beli' => 26500000,
                'harga_jual' => 29999000,
                'stok' => 3,
                'gambar' => 'legion-5-pro.jpg',
                'garansi' => 36,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 1,
                'nama_produk' => 'MSI Katana GF66',
                'merk' => 'MSI',
                'spesifikasi' => 'Intel Core i5-11400H, RTX 3050, 8GB DDR4, 512GB SSD, 15.6" FHD 144Hz',
                'harga_beli' => 10500000,
                'harga_jual' => 12499000,
                'stok' => 8,
                'gambar' => 'msi-katana.jpg',
                'garansi' => 24,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // 2. Ultrabooks
        $ultrabooks = [
            [
                'id_kategori' => 2,
                'nama_produk' => 'Apple MacBook Air M2',
                'merk' => 'Apple',
                'spesifikasi' => 'Apple M2 Chip, 8GB RAM, 256GB SSD, 13.6" Liquid Retina Display',
                'harga_beli' => 16000000,
                'harga_jual' => 18999000,
                'stok' => 10,
                'gambar' => 'macbook-air-m2.jpg',
                'garansi' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 2,
                'nama_produk' => 'ASUS Zenbook 14 OLED',
                'merk' => 'ASUS',
                'spesifikasi' => 'Intel Core i5-1240P, 16GB LPDDR5, 512GB SSD, 14" 2.8K OLED 90Hz',
                'harga_beli' => 13500000,
                'harga_jual' => 15499000,
                'stok' => 6,
                'gambar' => 'zenbook-14-oled.jpg',
                'garansi' => 24,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 2,
                'nama_produk' => 'Dell XPS 13 Plus',
                'merk' => 'Dell',
                'spesifikasi' => 'Intel Core i7-1260P, 16GB LPDDR5, 1TB SSD, 13.4" FHD+',
                'harga_beli' => 28000000,
                'harga_jual' => 32999000,
                'stok' => 2,
                'gambar' => 'dell-xps-13.jpg',
                'garansi' => 24,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // 3. Business
        $business = [
            [
                'id_kategori' => 3,
                'nama_produk' => 'Lenovo ThinkPad X1 Carbon Gen 10',
                'merk' => 'Lenovo',
                'spesifikasi' => 'Intel Core i7-1260P, 16GB, 1TB SSD, 14" WUXGA, Carbon Fiber',
                'harga_beli' => 31000000,
                'harga_jual' => 36499000,
                'stok' => 4,
                'gambar' => 'thinkpad-x1.jpg',
                'garansi' => 36,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 3,
                'nama_produk' => 'HP EliteBook 840 G9',
                'merk' => 'HP',
                'spesifikasi' => 'Intel Core i5-1235U, 16GB, 512GB SSD, 14" FHD, Wolf Security',
                'harga_beli' => 19500000,
                'harga_jual' => 22999000,
                'stok' => 5,
                'gambar' => 'hp-elitebook.jpg',
                'garansi' => 36,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // 4. Student
        $student = [
            [
                'id_kategori' => 4,
                'nama_produk' => 'Acer Swift 3',
                'merk' => 'Acer',
                'spesifikasi' => 'AMD Ryzen 5 5500U, 8GB, 512GB SSD, 14" FHD IPS',
                'harga_beli' => 8500000,
                'harga_jual' => 9999000,
                'stok' => 15,
                'gambar' => 'acer-swift-3.jpg',
                'garansi' => 24,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 4,
                'nama_produk' => 'HP 14s',
                'merk' => 'HP',
                'spesifikasi' => 'Intel Core i3-1115G4, 8GB, 512GB SSD, 14" FHD',
                'harga_beli' => 6800000,
                'harga_jual' => 7999000,
                'stok' => 20,
                'gambar' => 'hp-14s.jpg',
                'garansi' => 24,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 4,
                'nama_produk' => 'Lenovo IdeaPad Slim 3',
                'merk' => 'Lenovo',
                'spesifikasi' => 'Intel Core i3-1215U, 8GB, 512GB SSD, 14" FHD',
                'harga_beli' => 7200000,
                'harga_jual' => 8499000,
                'stok' => 12,
                'gambar' => 'ideapad-slim-3.jpg',
                'garansi' => 24,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // 5. Content Creation
        $creator = [
            [
                'id_kategori' => 5,
                'nama_produk' => 'ASUS Vivobook Pro 14X OLED',
                'merk' => 'ASUS',
                'spesifikasi' => 'Intel Core i7-11370H, RTX 3050, 16GB, 1TB SSD, 14" 2.8K OLED',
                'harga_beli' => 17500000,
                'harga_jual' => 19999000,
                'stok' => 4,
                'gambar' => 'vivobook-pro-14x.jpg',
                'garansi' => 24,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_kategori' => 5,
                'nama_produk' => 'MSI Creator Z16',
                'merk' => 'MSI',
                'spesifikasi' => 'Intel Core i7-11800H, RTX 3060, 32GB, 1TB SSD, 16" QHD+ 120Hz Touch',
                'harga_beli' => 32000000,
                'harga_jual' => 38999000,
                'stok' => 2,
                'gambar' => 'msi-creator-z16.jpg',
                'garansi' => 24,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $products = array_merge($gaming, $ultrabooks, $business, $student, $creator);
        DB::table('produk')->insert($products);
    }
}
