<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\User;

/**
 * Test untuk StokController
 * 
 * Menguji fungsionalitas manajemen stok:
 * - CRUD produk
 * - Filter dan statistik stok
 * - Validasi input
 * - Upload gambar
 */
class StokControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Fake storage untuk testing upload file
        Storage::fake('public');
    }

    /**
     * Test: Halaman index stok dapat diakses oleh user yang login
     */
    public function test_halaman_stok_dapat_diakses_oleh_authenticated_user(): void
    {
        // Arrange: Buat user
        $user = User::factory()->create();

        // Act: Login dan akses halaman stok
        $response = $this->actingAs($user)->get('/stok');

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('stok.index');
        $response->assertViewHas(['produk', 'stats']);
    }

    /**
     * Test: Statistik stok dihitung dengan benar
     */
    public function test_statistik_stok_dihitung_dengan_benar(): void
    {
        // Arrange
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create();

        // Buat produk dengan berbagai status stok
        Produk::factory()->create(['id_kategori' => $kategori->id_kategori, 'stok' => 10]); // Tersedia
        Produk::factory()->create(['id_kategori' => $kategori->id_kategori, 'stok' => 8]);  // Tersedia
        Produk::factory()->create(['id_kategori' => $kategori->id_kategori, 'stok' => 3]);  // Menipis
        Produk::factory()->create(['id_kategori' => $kategori->id_kategori, 'stok' => 2]);  // Menipis
        Produk::factory()->create(['id_kategori' => $kategori->id_kategori, 'stok' => 0]);  // Habis

        // Act
        $response = $this->actingAs($user)->get('/stok');

        // Assert: Periksa statistik
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total'] === 5 &&
                   $stats['tersedia'] === 2 &&
                   $stats['menipis'] === 2 &&
                   $stats['habis'] === 1;
        });
    }

    /**
     * Test: Filter stok berdasarkan status (habis/menipis/tersedia)
     */
    public function test_filter_stok_berdasarkan_status(): void
    {
        // Arrange
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create();

        $produkHabis = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Produk Habis',
            'stok' => 0
        ]);

        $produkTersedia = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Produk Tersedia',
            'stok' => 10
        ]);

        // Act: Filter stok habis
        $response = $this->actingAs($user)->get('/stok?status_stok=habis');

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Produk Habis');
        $response->assertDontSee('Produk Tersedia');
    }

    /**
     * Test: User dapat menambah produk baru
     */
    public function test_user_dapat_menambah_produk_baru(): void
    {
        // Arrange
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create();
        $gambar = UploadedFile::fake()->image('laptop.jpg');

        $produkData = [
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop ASUS TUF Gaming',
            'merk' => 'ASUS',
            'spesifikasi' => 'Intel i5, 16GB RAM, RTX 3050',
            'garansi' => 24,
            'harga_beli' => 10000000,
            'harga_jual' => 12000000,
            'stok' => 5,
            'gambar' => $gambar,
        ];

        // Act: Submit form create
        $response = $this->actingAs($user)
            ->post('/stok', $produkData);

        // Assert: Redirect dan data tersimpan
        $response->assertRedirect('/stok');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('produk', [
            'nama_produk' => 'Laptop ASUS TUF Gaming',
            'merk' => 'ASUS',
            'stok' => 5,
            'harga_beli' => 10000000,
            'harga_jual' => 12000000,
        ]);

        // Assert: Gambar tersimpan
        Storage::disk('public')->assertExists('produk/' . $gambar->hashName());
    }

    /**
     * Test: User dapat mengupdate produk existing (menambah stok)
     */
    public function test_user_dapat_menambah_stok_produk_existing(): void
    {
        // Arrange
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create();
        
        $produkExisting = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop Dell XPS',
            'stok' => 5,
            'harga_beli' => 15000000,
        ]);

        $updateData = [
            'id_produk_existing' => $produkExisting->id_produk,
            'nama_produk' => $produkExisting->id_produk, // Trick: kirim ID sebagai nama
            'id_kategori' => $kategori->id_kategori,
            'merk' => 'Dell',
            'spesifikasi' => 'Updated specs',
            'garansi' => 12,
            'harga_beli' => 15000000,
            'harga_jual' => 17000000,
            'stok' => 3, // Tambah 3 unit
        ];

        // Act
        $response = $this->actingAs($user)->post('/stok', $updateData);

        // Assert: Stok bertambah
        $response->assertRedirect('/stok');
        
        $produkExisting->refresh();
        $this->assertEquals(8, $produkExisting->stok); // 5 + 3 = 8
    }

    /**
     * Test: Validasi input saat menambah produk
     */
    public function test_validasi_input_produk_baru(): void
    {
        // Arrange
        $user = User::factory()->create();

        $invalidData = [
            'nama_produk' => '', // Nama kosong (invalid)
            'merk' => '',
            'stok' => -5, // Stok negatif (invalid)
        ];

        // Act
        $response = $this->actingAs($user)->post('/stok', $invalidData);

        // Assert: Validation error dan data tidak tersimpan
        $response->assertSessionHasErrors(['nama_produk', 'id_kategori', 'merk']);
        $this->assertDatabaseCount('produk', 0);
    }

    /**
     * Test: User dapat mengedit produk
     */
    public function test_user_dapat_mengedit_produk(): void
    {
        // Arrange
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create();
        
        $produk = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop Lama',
            'stok' => 5,
        ]);

        $updateData = [
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop Baru (Updated)',
            'merk' => 'ASUS',
            'spesifikasi' => 'New specs',
            'garansi' => 12,
            'harga_beli' => 10000000,
            'harga_jual' => 12000000,
            'stok' => 10,
        ];

        // Act
        $response = $this->actingAs($user)
            ->put("/stok/{$produk->id_produk}", $updateData);

        // Assert
        $response->assertRedirect('/stok');
        
        $produk->refresh();
        $this->assertEquals('Laptop Baru (Updated)', $produk->nama_produk);
        $this->assertEquals(10, $produk->stok);
    }

    /**
     * Test: User dapat menghapus produk
     */
    public function test_user_dapat_menghapus_produk(): void
    {
        // Arrange
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create();
        
        $produk = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop Akan Dihapus',
        ]);

        // Act
        $response = $this->actingAs($user)
            ->delete("/stok/{$produk->id_produk}");

        // Assert: Redirect dan data terhapus
        $response->assertRedirect('/stok');
        $this->assertDatabaseMissing('produk', [
            'id_produk' => $produk->id_produk,
        ]);
    }

    /**
     * Test: Gambar lama dihapus saat upload gambar baru
     */
    public function test_gambar_lama_dihapus_saat_update_gambar_baru(): void
    {
        // Arrange
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create();
        
        $gambarLama = UploadedFile::fake()->image('old.jpg');
        $gambarBaru = UploadedFile::fake()->image('new.jpg');

        // Buat produk dengan gambar
        $produk = Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'gambar' => 'storage/produk/' . $gambarLama->hashName(),
        ]);

        // Simulasi file lama ada di storage
        Storage::disk('public')->put('produk/' . $gambarLama->hashName(), 'old content');

        // Act: Update dengan gambar baru
        $response = $this->actingAs($user)->put("/stok/{$produk->id_produk}", [
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Test Produk',
            'merk' => 'Test',
            'spesifikasi' => 'Test',
            'garansi' => 12,
            'harga_beli' => 1000000,
            'harga_jual' => 1200000,
            'stok' => 5,
            'gambar' => $gambarBaru,
        ]);

        // Assert: Gambar lama dihapus, gambar baru tersimpan
        Storage::disk('public')->assertMissing('produk/' . $gambarLama->hashName());
        Storage::disk('public')->assertExists('produk/' . $gambarBaru->hashName());
    }

    /**
     * Test: Search produk di halaman stok
     */
    public function test_search_produk_di_halaman_stok(): void
    {
        // Arrange
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create();

        Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop ASUS ROG',
            'merk' => 'ASUS',
        ]);

        Produk::factory()->create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop Dell Inspiron',
            'merk' => 'Dell',
        ]);

        // Act: Search "ASUS"
        $response = $this->actingAs($user)->get('/stok?search=ASUS');

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Laptop ASUS ROG');
        $response->assertDontSee('Laptop Dell Inspiron');
    }

    /**
     * Test: Kategori baru dapat dibuat on-the-fly saat menambah produk
     */
    public function test_kategori_baru_dapat_dibuat_otomatis(): void
    {
        // Arrange
        $user = User::factory()->create();

        $produkData = [
            'id_kategori' => 'Laptop Gaming Baru', // String bukan ID = kategori baru
            'nama_produk' => 'Test Laptop',
            'merk' => 'Test',
            'spesifikasi' => 'Test specs',
            'garansi' => 12,
            'harga_beli' => 5000000,
            'harga_jual' => 6000000,
            'stok' => 5,
        ];

        // Act
        $response = $this->actingAs($user)->post('/stok', $produkData);

        // Assert: Kategori baru dibuat
        $this->assertDatabaseHas('kategori', [
            'nama_kategori' => 'Laptop Gaming Baru',
            'slug' => 'laptop-gaming-baru',
        ]);

        // Assert: Produk tersimpan dengan kategori baru
        $kategori = Kategori::where('nama_kategori', 'Laptop Gaming Baru')->first();
        $this->assertDatabaseHas('produk', [
            'nama_produk' => 'Test Laptop',
            'id_kategori' => $kategori->id_kategori,
        ]);
    }
}
