<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Pelanggan;

/**
 * Unit Test untuk Model Pelanggan
 * 
 * Test basic functionality dan attributes
 */
class PelangganTest extends TestCase
{
    /**
     * Test: Pelanggan dapat dibuat dengan data yang benar
     */
    public function test_pelanggan_dapat_dibuat_dengan_data_benar(): void
    {
        // Arrange
        $pelanggan = new Pelanggan([
            'nama' => 'Budi Santoso',
            'no_hp' => '081234567890',
            'email' => 'budi@example.com',
            'alamat' => 'Jl. Raya No. 123, Jakarta'
        ]);

        // Assert
        $this->assertEquals('Budi Santoso', $pelanggan->nama);
        $this->assertEquals('081234567890', $pelanggan->no_hp);
        $this->assertEquals('budi@example.com', $pelanggan->email);
        $this->assertEquals('Jl. Raya No. 123, Jakarta', $pelanggan->alamat);
    }

    /**
     * Test: Fillable attributes
     */
    public function test_fillable_attributes(): void
    {
        // Arrange
        $pelanggan = new Pelanggan();

        // Act
        $fillable = $pelanggan->getFillable();

        // Assert
        $expectedFillable = [
            'nama',
            'no_hp',
            'email',
            'alamat',
            'id_produk',
            'tanggal_pembelian',
            'garansi',
            'catatan'
        ];

        foreach ($expectedFillable as $field) {
            $this->assertContains($field, $fillable, "Field '$field' seharusnya fillable");
        }
    }

    /**
     * Test: Primary key adalah id_pelanggan
     */
    public function test_primary_key_adalah_id_pelanggan(): void
    {
        $pelanggan = new Pelanggan();
        $this->assertEquals('id_pelanggan', $pelanggan->getKeyName());
    }

    /**
     * Test: Table name adalah 'pelanggan'
     */
    public function test_table_name_adalah_pelanggan(): void
    {
        $pelanggan = new Pelanggan();
        $this->assertEquals('pelanggan', $pelanggan->getTable());
    }

    /**
     * Test: Model menggunakan timestamps
     */
    public function test_model_menggunakan_timestamps(): void
    {
        $pelanggan = new Pelanggan();
        $this->assertTrue($pelanggan->usesTimestamps());
    }

    /**
     * Test: Email bisa diisi dengan format yang benar
     */
    public function test_email_dapat_diisi(): void
    {
        $pelanggan = new Pelanggan(['email' => 'test@example.com']);
        $this->assertEquals('test@example.com', $pelanggan->email);
    }

    /**
     * Test: Nomor HP dapat diisi
     */
    public function test_no_hp_dapat_diisi(): void
    {
        $pelanggan = new Pelanggan(['no_hp' => '081234567890']);
        $this->assertEquals('081234567890', $pelanggan->no_hp);
    }
}
