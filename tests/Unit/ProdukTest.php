<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Produk;

/**
 * Unit Test untuk Model Produk
 * 
 * Test ini menguji business logic di model Produk tanpa database.
 * Fokus pada:
 * - Accessors (computed attributes)
 * - Margin calculation
 * - Status stok logic
 */
class ProdukTest extends TestCase
{
    /**
     * Test: Accessor margin menghitung selisih harga jual - harga beli
     */
    public function test_margin_dihitung_dengan_benar(): void
    {
        // Arrange
        $produk = new Produk([
            'harga_beli' => 5000000,
            'harga_jual' => 6000000,
        ]);

        // Act & Assert
        $this->assertEquals(1000000, $produk->margin);
    }

    /**
     * Test: Margin persentase dihitung dengan benar
     */
    public function test_margin_persentase_dihitung_dengan_benar(): void
    {
        // Arrange
        $produk = new Produk([
            'harga_beli' => 5000000,
            'harga_jual' => 6000000,
        ]);

        // Act
        $marginPersentase = $produk->margin_persentase;

        // Assert: (6000000 - 5000000) / 5000000 * 100 = 20%
        $this->assertEquals(20, $marginPersentase);
    }

    /**
     * Test: Margin persentase = 0 jika harga beli = 0 (hindari division by zero)
     */
    public function test_margin_persentase_nol_jika_harga_beli_nol(): void
    {
        // Arrange
        $produk = new Produk([
            'harga_beli' => 0,
            'harga_jual' => 1000000,
        ]);

        // Act & Assert
        $this->assertEquals(0, $produk->margin_persentase);
    }

    /**
     * Test: Status stok "habis" jika stok = 0
     */
    public function test_status_stok_habis(): void
    {
        // Arrange
        $produk = new Produk(['stok' => 0]);

        // Act & Assert
        $this->assertEquals('habis', $produk->status_stok);
    }

    /**
     * Test: Status stok "menipis" jika stok > 0 dan <= 5
     */
    public function test_status_stok_menipis(): void
    {
        // Test berbagai nilai stok menipis
        $testCases = [1, 2, 3, 4, 5];

        foreach ($testCases as $stok) {
            $produk = new Produk(['stok' => $stok]);
            $this->assertEquals('menipis', $produk->status_stok, "Stok $stok seharusnya 'menipis'");
        }
    }

    /**
     * Test: Status stok "tersedia" jika stok > 5
     */
    public function test_status_stok_tersedia(): void
    {
        // Test berbagai nilai stok tersedia
        $testCases = [6, 10, 50, 100];

        foreach ($testCases as $stok) {
            $produk = new Produk(['stok' => $stok]);
            $this->assertEquals('tersedia', $produk->status_stok, "Stok $stok seharusnya 'tersedia'");
        }
    }

    /**
     * Test: Total nilai stok = harga jual × stok
     */
    public function test_total_nilai_stok(): void
    {
        // Arrange
        $produk = new Produk([
            'harga_jual' => 6000000,
            'stok' => 5,
        ]);

        // Act
        $totalNilai = $produk->total_nilai_stok;

        // Assert: 6000000 × 5 = 30000000
        $this->assertEquals(30000000, $totalNilai);
    }

    /**
     * Test: Total nilai stok = 0 jika stok = 0
     */
    public function test_total_nilai_stok_nol_jika_stok_nol(): void
    {
        // Arrange
        $produk = new Produk([
            'harga_jual' => 10000000,
            'stok' => 0,
        ]);

        // Act & Assert
        $this->assertEquals(0, $produk->total_nilai_stok);
    }

    /**
     * Test: Margin negatif jika harga jual < harga beli (rugi)
     */
    public function test_margin_negatif_jika_rugi(): void
    {
        // Arrange: Harga jual lebih rendah dari harga beli (rugi)
        $produk = new Produk([
            'harga_beli' => 6000000,
            'harga_jual' => 5000000,
        ]);

        // Act & Assert
        $this->assertEquals(-1000000, $produk->margin);
        $this->assertLessThan(0, $produk->margin_persentase);
    }

    /**
     * Test: Fillable attributes
     */
    public function test_fillable_attributes(): void
    {
        // Arrange
        $produk = new Produk();

        // Act
        $fillable = $produk->getFillable();

        // Assert: Pastikan semua field penting ada di fillable
        $expectedFillable = [
            'id_kategori',
            'nama_produk',
            'merk',
            'spesifikasi',
            'harga_beli',
            'harga_jual',
            'stok',
            'gambar',
            'garansi'
        ];

        foreach ($expectedFillable as $field) {
            $this->assertContains($field, $fillable, "Field '$field' seharusnya fillable");
        }
    }

    /**
     * Test: Casts attributes (type casting)
     */
    public function test_casts_attributes(): void
    {
        // Arrange
        $produk = new Produk();

        // Act
        $casts = $produk->getCasts();

        // Assert: Pastikan casts yang penting ada
        $this->assertEquals('decimal:2', $casts['harga_beli']);
        $this->assertEquals('decimal:2', $casts['harga_jual']);
        $this->assertEquals('integer', $casts['stok']);
        $this->assertEquals('integer', $casts['garansi']);
    }

    /**
     * Test: Primary key adalah id_produk
     */
    public function test_primary_key_adalah_id_produk(): void
    {
        // Arrange
        $produk = new Produk();

        // Assert
        $this->assertEquals('id_produk', $produk->getKeyName());
    }

    /**
     * Test: Route key name adalah id_produk
     */
    public function test_route_key_name(): void
    {
        // Arrange
        $produk = new Produk();

        // Assert
        $this->assertEquals('id_produk', $produk->getRouteKeyName());
    }
}
