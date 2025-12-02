<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kategori>
 */
class KategoriFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $namaKategori = fake()->randomElement([
            'Laptop Gaming',
            'Laptop Office',
            'Laptop Bisnis',
            'Laptop Ultrabook',
            'Laptop Workstation',
        ]);

        return [
            'nama_kategori' => $namaKategori,
            'slug' => \Illuminate\Support\Str::slug($namaKategori) . '-' . fake()->unique()->numberBetween(1, 1000),
        ];
    }
}
