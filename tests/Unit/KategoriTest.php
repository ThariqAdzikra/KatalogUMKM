<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Kategori;

/**
 * Unit Test untuk Model Kategori
 * 
 * Contoh test sederhana untuk model
 */
class KategoriTest extends TestCase
{
    /**
     * Test: Kategori dapat dibuat dengan data yang benar
     */
    public function test_kategori_dapat_dibuat_dengan_data_benar(): void
    {
        // Arrange: Buat instance kategori
        $kategori = new Kategori([
            'nama_kategori' => 'Laptop Gaming',
            'slug' => 'laptop-gaming'
        ]);

        // Assert: Pastikan data tersimpan dengan benar
        $this->assertEquals('Laptop Gaming', $kategori->nama_kategori);
        $this->assertEquals('laptop-gaming', $kategori->slug);
    }

    /**
     * Test: Fillable attributes mengandung field yang benar
     */
    public function test_fillable_attributes(): void
    {
        // Arrange
        $kategori = new Kategori();

        // Act
        $fillable = $kategori->getFillable();

        // Assert: Pastikan field penting ada di fillable
        $this->assertContains('nama_kategori', $fillable);
        $this->assertContains('slug', $fillable);
    }

    /**
     * Test: Primary key adalah id_kategori
     */
    public function test_primary_key_adalah_id_kategori(): void
    {
        $kategori = new Kategori();
        $this->assertEquals('id_kategori', $kategori->getKeyName());
    }

    /**
     * Test: Model menggunakan timestamps
     */
    public function test_model_menggunakan_timestamps(): void
    {
        $kategori = new Kategori();
        $this->assertTrue($kategori->usesTimestamps());
    }
}
