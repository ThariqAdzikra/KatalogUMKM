<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\User;

/**
 * Test untuk KatalogController
 * 
 * Feature test ini menguji fungsionalitas halaman katalog:
 * - Apakah halaman dapat diakses
 * - Apakah hanya menampilkan produk yang ada stok (untuk guest)
 * - Apakah fitur search berfungsi
 * - Apakah filter kategori berfungsi
 */
class KatalogControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Halaman katalog dapat diakses dan mengembalikan status 200
     */
    public function test_halaman_katalog_dapat_diakses(): void
    {
        // Act: Akses halaman katalog
        $response = $this->get('/katalog');

        // Assert: Pastikan response OK dan view yang benar
        $response->assertStatus(200);
        $response->assertViewIs('katalog.index');
        $response->assertViewHas('produk');
    }

    /**
     * Test: Guest user hanya melihat produk yang ada stok
     * 
     * Requirement: Guest tidak boleh melihat produk yang stoknya habis
     */
    public function test_guest_hanya_melihat_produk_yang_ada_stok(): void
    {
        // Arrange: Buat kategori
        $kategori = Kategori::factory()->create(['nama_kategori' => 'Laptop Gaming']);

        // Buat produk yang ada stok
        $produkTersedia = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop ASUS ROG Strix',
            'stok' => 10
        ]);

        // Buat produk yang stoknya habis
        $produkHabis = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop MSI Katana',
            'stok' => 0
        ]);

        // Act: Akses halaman katalog tanpa login (guest)
        $response = $this->get('/katalog');

        // Assert: Produk tersedia muncul, produk habis tidak muncul
        $response->assertStatus(200);
        $response->assertSee('Laptop ASUS ROG Strix');
        $response->assertDontSee('Laptop MSI Katana');
    }

    /**
     * Test: User yang sudah login melihat semua produk (termasuk stok habis)
     */
    public function test_authenticated_user_melihat_semua_produk(): void
    {
        // Arrange: Buat user dan login
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create();

        $produkTersedia = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop Tersedia',
            'stok' => 5
        ]);

        $produkHabis = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop Habis',
            'stok' => 0
        ]);

        // Act: Login dan akses katalog
        $response = $this->actingAs($user)->get('/katalog');

        // Assert: Kedua produk muncul
        $response->assertStatus(200);
        $response->assertSee('Laptop Tersedia');
        $response->assertSee('Laptop Habis');
    }

    /**
     * Test: Fitur search berdasarkan nama produk berfungsi
     */
    public function test_search_berdasarkan_nama_produk(): void
    {
        // Arrange: Buat beberapa produk
        $kategori = Kategori::factory()->create();
        
        Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop ASUS ROG',
            'stok' => 5
        ]);

        Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop Acer Predator',
            'stok' => 3
        ]);

        Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop HP Pavilion',
            'stok' => 2
        ]);

        // Act: Search dengan keyword "ASUS"
        $response = $this->get('/katalog?search=ASUS');

        // Assert: Hanya produk ASUS yang muncul
        $response->assertStatus(200);
        $response->assertSee('Laptop ASUS ROG');
        $response->assertDontSee('Laptop Acer Predator');
        $response->assertDontSee('Laptop HP Pavilion');
    }

    /**
     * Test: Search berdasarkan merk
     */
    public function test_search_berdasarkan_merk(): void
    {
        // Arrange
        $kategori = Kategori::factory()->create();
        
        Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop Gaming',
            'merk' => 'ASUS',
            'stok' => 5
        ]);

        Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop Office',
            'merk' => 'Dell',
            'stok' => 3
        ]);

        // Act: Search merk "Dell"
        $response = $this->get('/katalog?search=Dell');

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Laptop Office');
        $response->assertSee('Dell');
        $response->assertDontSee('ASUS');
    }

    /**
     * Test: Filter berdasarkan kategori
     */
    public function test_filter_berdasarkan_kategori(): void
    {
        // Arrange: Buat 2 kategori
        $kategoriGaming = Kategori::factory()->create([
            'nama_kategori' => 'Laptop Gaming',
            'slug' => 'laptop-gaming'
        ]);

        $kategoriOffice = Kategori::factory()->create([
            'nama_kategori' => 'Laptop Office',
            'slug' => 'laptop-office'
        ]);

        // Buat produk untuk masing-masing kategori
        Produk::factory()->create([
            'id_kategori' => $kategoriGaming->id_kategori,
            'nama_produk' => 'ASUS ROG',
            'stok' => 5
        ]);

        Produk::factory()->create([
            'id_kategori' => $kategoriOffice->id_kategori,
            'nama_produk' => 'Lenovo ThinkPad',
            'stok' => 3
        ]);

        // Act: Filter dengan kategori gaming
        $response = $this->get('/katalog?kategori=laptop-gaming');

        // Assert: Hanya produk gaming yang muncul
        $response->assertStatus(200);
        $response->assertSee('ASUS ROG');
        $response->assertDontSee('Lenovo ThinkPad');
    }

    /**
     * Test: Kombinasi search dan filter kategori
     */
    public function test_kombinasi_search_dan_filter_kategori(): void
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

        Produk::factory()->create([
            'id_kategori' => $kategoriGaming->id_kategori,
            'nama_produk' => 'Laptop ASUS ROG',
            'stok' => 5
        ]);

        Produk::factory()->create([
            'id_kategori' => $kategoriGaming->id_kategori,
            'nama_produk' => 'Laptop MSI Gaming',
            'stok' => 3
        ]);

        Produk::factory()->create([
            'id_kategori' => $kategoriOffice->id_kategori,
            'nama_produk' => 'Laptop ASUS Vivobook',
            'stok' => 2
        ]);

        // Act: Search "ASUS" + filter kategori "gaming"
        $response = $this->get('/katalog?search=ASUS&kategori=gaming');

        // Assert: Hanya ASUS ROG yang muncul
        $response->assertStatus(200);
        $response->assertSee('Laptop ASUS ROG');
        $response->assertDontSee('Laptop MSI Gaming');
        $response->assertDontSee('Laptop ASUS Vivobook');
    }

    /**
     * Test: Pagination berfungsi (12 item per halaman)
     */
    public function test_pagination_menampilkan_12_item_per_halaman(): void
    {
        // Arrange: Buat 15 produk
        $kategori = Kategori::factory()->create();
        
        Produk::factory()->count(15)->create([
            'id_kategori' => $kategori->id_kategori,
            'stok' => 5 // Semua ada stok
        ]);

        // Act: Akses halaman pertama
        $response = $this->get('/katalog');

        // Assert: Response OK dan view memiliki pagination
        $response->assertStatus(200);
        $response->assertViewHas('produk', function ($produk) {
            return $produk->count() === 12; // Halaman pertama: 12 item
        });
    }

    /**
     * Test: View memiliki data kategori untuk dropdown filter
     */
    public function test_view_memiliki_data_kategori(): void
    {
        // Arrange: Buat beberapa kategori
        Kategori::factory()->count(3)->create();

        // Act
        $response = $this->get('/katalog');

        // Assert: View memiliki variable 'kategori'
        $response->assertStatus(200);
        $response->assertViewHas('kategori');
        $response->assertViewHas('kategori', function ($kategori) {
            return $kategori->count() === 3;
        });
    }
}
