<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pelanggan>
 */
class PelangganFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'no_hp' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'alamat' => fake()->address(),
            'id_produk' => null, // Akan di-set manual di test jika perlu
            'tanggal_pembelian' => fake()->dateTimeBetween('-1 year', 'now'),
            'garansi' => fake()->randomElement([0, 12, 24, 36]),
            'catatan' => fake()->optional()->sentence(),
        ];
    }
}
