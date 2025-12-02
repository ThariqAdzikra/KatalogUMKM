<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Produk;
use App\Models\Kategori;

/**
 * Unit Test untuk Relasi dan Scopes di Model Produk
 * 
 * Test ini memerlukan database karena menguji relasi dan query scopes.
 * Extend dari Tests\TestCase (bukan PHPUnit\Framework\TestCase)
 */
class ProdukRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Produk memiliki relasi belongsTo ke Kategori
     */
    public function test_produk_memiliki_relasi_ke_kategori(): void
    {
        // Arrange: Buat kategori dan produk
        $kategori = Kategori::factory()->create([
            'nama_kategori' => 'Laptop Gaming'
        ]);

        $produk = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori
        ]);

        // Act: Akses relasi
        $relatedKategori = $produk->kategori;

        // Assert
        $this->assertInstanceOf(Kategori::class, $relatedKategori);
        $this->assertEquals('Laptop Gaming', $relatedKategori->nama_kategori);
        $this->assertEquals($kategori->id_kategori, $relatedKategori->id_kategori);
    }

    /**
     * Test: Scope stokHabis mengembalikan produk dengan stok = 0
     */
    public function test_scope_stok_habis(): void
    {
        // Arrange: Buat produk dengan berbagai stok
        $kategori = Kategori::factory()->create();
        
        $produkHabis1 = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'stok' => 0
        ]);
        
        $produkHabis2 = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'stok' => 0
        ]);
        
        Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'stok' => 5
        ]);

        // Act: Gunakan scope
        $result = Produk::stokHabis()->get();

        // Assert: Hanya 2 produk habis yang dikembalikan
        $this->assertCount(2, $result);
        $this->assertTrue($result->contains($produkHabis1));
        $this->assertTrue($result->contains($produkHabis2));
    }

    /**
     * Test: Scope stokMenipis mengembalikan produk dengan stok 1-5
     */
    public function test_scope_stok_menipis(): void
    {
        // Arrange
        $kategori = Kategori::factory()->create();
        
        $produkMenipis1 = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'stok' => 1
        ]);
        
        $produkMenipis2 = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'stok' => 5
        ]);
        
        Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'stok' => 0 // Tidak termasuk
        ]);
        
        Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'stok' => 10 // Tidak termasuk
        ]);

        // Act
        $result = Produk::stokMenipis()->get();

        // Assert
        $this->assertCount(2, $result);
        $this->assertTrue($result->contains($produkMenipis1));
        $this->assertTrue($result->contains($produkMenipis2));
    }

    /**
     * Test: Scope stokTersedia mengembalikan produk dengan stok > 5
     */
    public function test_scope_stok_tersedia(): void
    {
        // Arrange
        $kategori = Kategori::factory()->create();
        
        $produkTersedia1 = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'stok' => 6
        ]);
        
        $produkTersedia2 = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'stok' => 100
        ]);
        
        Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'stok' => 5 // Tidak termasuk
        ]);

        // Act
        $result = Produk::stokTersedia()->get();

        // Assert
        $this->assertCount(2, $result);
        $this->assertTrue($result->contains($produkTersedia1));
        $this->assertTrue($result->contains($produkTersedia2));
    }

    /**
     * Test: Scope search mencari berdasarkan nama, merk, atau spesifikasi
     */
    public function test_scope_search(): void
    {
        // Arrange
        $kategori = Kategori::factory()->create();
        
        $produk1 = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop ASUS ROG',
            'merk' => 'ASUS',
            'spesifikasi' => 'Intel i7'
        ]);
        
        $produk2 = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop Acer',
            'merk' => 'Acer',
            'spesifikasi' => 'Intel i5'
        ]);

        // Act: Search "ASUS"
        $result = Produk::search('ASUS')->get();

        // Assert: Hanya produk ASUS yang ditemukan
        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($produk1));
        $this->assertFalse($result->contains($produk2));
    }

    /**
     * Test: Scope search mencari di field spesifikasi
     */
    public function test_scope_search_di_spesifikasi(): void
    {
        // Arrange
        $kategori = Kategori::factory()->create();
        
        $produkI7 = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop Gaming',
            'merk' => 'ASUS',
            'spesifikasi' => 'Intel Core i7, RTX 3060'
        ]);
        
        Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop Office',
            'merk' => 'Dell',
            'spesifikasi' => 'Intel Core i5'
        ]);

        // Act: Search berdasarkan spesifikasi "RTX"
        $result = Produk::search('RTX')->get();

        // Assert
        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($produkI7));
    }

    /**
     * Test: Scope filterByKategori filter produk berdasarkan slug kategori
     */
    public function test_scope_filter_by_kategori(): void
    {
        // Arrange
        $kategoriGaming = Kategori::factory()->create([
            'nama_kategori' => 'Gaming',
            'slug' => 'gaming'
        ]);
        
        $kategoriOffice = Kategori::factory()->create([
            'nama_kategori' => 'Office',
            'slug' => 'office'
        ]);

        $produkGaming = Produk::factory()->create([
            'id_kategori' => $kategoriGaming->id_kategori
        ]);
        
        Produk::factory()->create([
            'id_kategori' => $kategoriOffice->id_kategori
        ]);

        // Act: Filter dengan slug "gaming"
        $result = Produk::filterByKategori('gaming')->get();

        // Assert
        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($produkGaming));
    }

    /**
     * Test: Scope filterByKategori mengembalikan semua jika slug null
     */
    public function test_scope_filter_by_kategori_null_mengembalikan_semua(): void
    {
        // Arrange
        $kategori = Kategori::factory()->create();
        
        Produk::factory()->count(3)->create([
            'id_kategori' => $kategori->id_kategori
        ]);

        // Act: Filter dengan null
        $result = Produk::filterByKategori(null)->get();

        // Assert: Semua produk dikembalikan
        $this->assertCount(3, $result);
    }

    /**
     * Test: Chain multiple scopes
     */
    public function test_chain_multiple_scopes(): void
    {
        // Arrange
        $kategoriGaming = Kategori::factory()->create([
            'nama_kategori' => 'Gaming',
            'slug' => 'gaming'
        ]);
        
        $kategoriOffice = Kategori::factory()->create([
            'nama_kategori' => 'Office',
            'slug' => 'office'
        ]);

        // Produk gaming dengan stok tersedia
        $produkGamingTersedia = Produk::factory()->create([
            'id_kategori' => $kategoriGaming->id_kategori,
            'nama_produk' => 'ASUS ROG',
            'stok' => 10
        ]);

        // Produk gaming stok habis
        Produk::factory()->create([
            'id_kategori' => $kategoriGaming->id_kategori,
            'nama_produk' => 'MSI Gaming',
            'stok' => 0
        ]);

        // Produk office stok tersedia
        Produk::factory()->create([
            'id_kategori' => $kategoriOffice->id_kategori,
            'nama_produk' => 'Dell Office',
            'stok' => 10
        ]);

        // Act: Chain scopes - filter kategori gaming + stok tersedia + search ASUS
        $result = Produk::filterByKategori('gaming')
            ->stokTersedia()
            ->search('ASUS')
            ->get();

        // Assert: Hanya 1 produk yang cocok
        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($produkGamingTersedia));
    }
}
