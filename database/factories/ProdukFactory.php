<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Produk>
 */
class ProdukFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $brands = ['ASUS', 'Acer', 'HP', 'Dell', 'Lenovo', 'MSI'];
        $processors = ['Intel Core i3', 'Intel Core i5', 'Intel Core i7', 'AMD Ryzen 5', 'AMD Ryzen 7'];
        $rams = ['8GB', '16GB', '32GB'];
        $gpus = ['Intel UHD', 'NVIDIA GTX 1650', 'NVIDIA RTX 3050', 'NVIDIA RTX 3060'];

        $merk = fake()->randomElement($brands);
        $hargaBeli = fake()->numberBetween(3000000, 15000000);
        $margin = fake()->numberBetween(10, 30); // 10-30% margin
        $hargaJual = $hargaBeli + ($hargaBeli * $margin / 100);

        return [
            'id_kategori' => \App\Models\Kategori::factory(),
            'nama_produk' => 'Laptop ' . $merk . ' ' . fake()->randomElement(['Gaming', 'Office', 'Creator', 'Ultrabook']),
            'merk' => $merk,
            'spesifikasi' => fake()->randomElement($processors) . ', ' . fake()->randomElement($rams) . ' RAM, ' . fake()->randomElement($gpus),
            'harga_beli' => $hargaBeli,
            'harga_jual' => round($hargaJual, -3), // Round to nearest thousand
            'stok' => fake()->numberBetween(0, 20),
            'garansi' => fake()->randomElement([0, 12, 24, 36]),
            'gambar' => null, // Nullable untuk test
        ];
    }
}
