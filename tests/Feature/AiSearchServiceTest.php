<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AiSearchService;
use App\Models\Produk;
use App\Models\Penjualan;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Kategori;

class AiSearchServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $aiService;

    public function setUp(): void
    {
        parent::setUp();
        $this->aiService = new AiSearchService();
    }

    // ... (rest of tests)

    /** @test */
    public function it_can_fetch_relevant_product_data()
    {
        // Arrange: Create Category dependency
        $kategori = Kategori::create([
            'nama_kategori' => 'Elektronik',
            'slug' => 'elektronik'
        ]);

        // Arrange: Create dummy product
        Produk::create([
            'id_kategori' => $kategori->id_kategori ?? $kategori->id, // Handle potential primary key diff
            'nama_produk' => 'Laptop Gaming Test',
            'merk' => 'Asus',
            'spesifikasi' => 'RAM 16GB',
            'harga_beli' => 10000000,
            'harga_jual' => 15000000,
            'stok' => 50,
            'garansi' => 12,
            'gambar' => 'default.jpg'
        ]);

        // Act: Fetch data
        $result = $this->aiService->fetchRelevantData("tampilkan stok laptop", "general");

        // Assert: Result string should contain the product name
        $this->assertStringContainsString('Laptop Gaming Test', $result);
        $this->assertStringContainsString('50', $result);
    }
}
