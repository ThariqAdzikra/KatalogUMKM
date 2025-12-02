# 📚 Panduan Laravel Testing - katalogLaptop

## 📖 Daftar Isi
1. [Pengenalan Testing](#pengenalan-testing)
2. [Jenis-jenis Test](#jenis-jenis-test)
3. [Setup & Konfigurasi](#setup--konfigurasi)
4. [Menjalankan Test](#menjalankan-test)
5. [Feature Testing](#feature-testing)
6. [Unit Testing](#unit-testing)
7. [Best Practices](#best-practices)
8. [Assertions yang Sering Digunakan](#assertions-yang-sering-digunakan)

---

## 🎯 Pengenalan Testing

Testing adalah proses untuk memverifikasi bahwa aplikasi Anda bekerja sesuai dengan yang diharapkan. Laravel menyediakan dukungan testing yang sangat baik menggunakan **PHPUnit**.

### Mengapa Testing Penting?

✅ **Deteksi Bug Lebih Awal** - Menemukan masalah sebelum ke production  
✅ **Keamanan Refactoring** - Yakin bahwa perubahan kode tidak merusak fitur yang sudah ada  
✅ **Dokumentasi Hidup** - Test menjelaskan bagaimana aplikasi seharusnya bekerja  
✅ **Kualitas Kode** - Mendorong penulisan kode yang lebih modular dan maintainable  

---

## 🔍 Jenis-jenis Test

### 1. **Feature Tests** (tests/Feature/)
Test yang menguji **flow lengkap aplikasi** dari request hingga response.

**Karakteristik:**
- Menguji HTTP requests dan responses
- Berinteraksi dengan database
- Simulasi user interactions
- Lebih lambat, tapi lebih comprehensive

**Contoh Use Case:**
- Apakah halaman katalog menampilkan produk dengan benar?
- Apakah user bisa menambah stok produk?
- Apakah filter dan search berfungsi?

### 2. **Unit Tests** (tests/Unit/)
Test yang menguji **satu unit kecil kode** secara terisolasi (biasanya satu method).

**Karakteristik:**
- Menguji logic bisnis di model
- Tidak berinteraksi dengan database (atau menggunakan mock)
- Sangat cepat
- Fokus pada satu fungsi/method

**Contoh Use Case:**
- Apakah method `getMarginAttribute()` menghitung dengan benar?
- Apakah method `getStatusStokAttribute()` mengembalikan status yang tepat?
- Apakah relasi model sudah benar?

---

## ⚙️ Setup & Konfigurasi

### File Konfigurasi: `phpunit.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <!-- ... konfigurasi lainnya -->
    </php>
</phpunit>
```

### Database Testing

Laravel secara otomatis menggunakan **SQLite in-memory** untuk testing (lebih cepat). Data di database testing akan **di-reset** setiap kali test selesai.

### Traits Penting

```php
use Illuminate\Foundation\Testing\RefreshDatabase;  // Reset database setiap test
use Illuminate\Foundation\Testing\WithFaker;         // Generate data palsu
use Illuminate\Foundation\Testing\WithoutMiddleware; // Nonaktifkan middleware
```

---

## 🚀 Menjalankan Test

### Command Dasar

```bash
# Jalankan semua test
php artisan test

# Atau menggunakan PHPUnit langsung
./vendor/bin/phpunit

# Jalankan test tertentu
php artisan test --filter=KatalogControllerTest

# Jalankan hanya Feature tests
php artisan test --testsuite=Feature

# Jalankan hanya Unit tests
php artisan test --testsuite=Unit

# Jalankan dengan detail (verbose)
php artisan test --verbose

# Jalankan dengan code coverage (memerlukan xdebug)
php artisan test --coverage
```

---

## 🧪 Feature Testing

### Struktur Dasar

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\User;

class KatalogControllerTest extends TestCase
{
    use RefreshDatabase; // Reset database setelah setiap test
    
    public function test_halaman_katalog_dapat_diakses(): void
    {
        // Arrange (Persiapan)
        // ...
        
        // Act (Aksi)
        $response = $this->get('/katalog');
        
        // Assert (Verifikasi)
        $response->assertStatus(200);
    }
}
```

### Contoh Feature Test untuk Katalog

```php
public function test_katalog_menampilkan_produk_yang_ada_stok(): void
{
    // Buat produk dengan stok
    $produkTersedia = Produk::factory()->create([
        'nama_produk' => 'Laptop ASUS ROG',
        'stok' => 10
    ]);
    
    // Buat produk habis stok
    $produkHabis = Produk::factory()->create([
        'nama_produk' => 'Laptop Dell',
        'stok' => 0
    ]);
    
    // Guest user seharusnya hanya melihat produk yang ada stok
    $response = $this->get('/katalog');
    
    $response->assertSee('Laptop ASUS ROG');
    $response->assertDontSee('Laptop Dell');
}

public function test_search_katalog_berfungsi(): void
{
    Produk::factory()->create(['nama_produk' => 'Laptop ASUS', 'stok' => 5]);
    Produk::factory()->create(['nama_produk' => 'Laptop Acer', 'stok' => 3]);
    
    $response = $this->get('/katalog?search=ASUS');
    
    $response->assertStatus(200);
    $response->assertSee('Laptop ASUS');
    $response->assertDontSee('Laptop Acer');
}
```

### Test dengan Autentikasi

```php
public function test_admin_dapat_menambah_stok(): void
{
    $user = User::factory()->create(); // Buat user test
    $kategori = Kategori::factory()->create();
    
    $response = $this->actingAs($user) // Login sebagai user
        ->post('/stok', [
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Laptop HP',
            'merk' => 'HP',
            'spesifikasi' => 'i5, 8GB RAM',
            'garansi' => 12,
            'harga_beli' => 5000000,
            'harga_jual' => 6000000,
            'stok' => 10,
        ]);
    
    $response->assertRedirect('/stok');
    $this->assertDatabaseHas('produk', [
        'nama_produk' => 'Laptop HP',
        'stok' => 10
    ]);
}
```

---

## 🔬 Unit Testing

### Struktur Dasar

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase; // Perhatikan: bukan Tests\TestCase
use App\Models\Produk;

class ProdukTest extends TestCase
{
    public function test_dapat_menghitung_margin(): void
    {
        $produk = new Produk([
            'harga_beli' => 5000000,
            'harga_jual' => 6000000
        ]);
        
        $this->assertEquals(1000000, $produk->margin);
    }
}
```

### Contoh Unit Test untuk Model

```php
public function test_margin_persentase_dihitung_dengan_benar(): void
{
    $produk = new Produk([
        'harga_beli' => 5000000,
        'harga_jual' => 6000000
    ]);
    
    // Margin = (6000000 - 5000000) / 5000000 * 100 = 20%
    $this->assertEquals(20, $produk->margin_persentase);
}

public function test_status_stok_habis(): void
{
    $produk = new Produk(['stok' => 0]);
    $this->assertEquals('habis', $produk->status_stok);
}

public function test_status_stok_menipis(): void
{
    $produk = new Produk(['stok' => 3]);
    $this->assertEquals('menipis', $produk->status_stok);
}

public function test_status_stok_tersedia(): void
{
    $produk = new Produk(['stok' => 10]);
    $this->assertEquals('tersedia', $produk->status_stok);
}

public function test_total_nilai_stok(): void
{
    $produk = new Produk([
        'harga_jual' => 6000000,
        'stok' => 5
    ]);
    
    // 6000000 * 5 = 30000000
    $this->assertEquals(30000000, $produk->total_nilai_stok);
}
```

### Test dengan Database (Unit Test yang kompleks)

Jika perlu database, extend `Tests\TestCase` dan gunakan `RefreshDatabase`:

```php
namespace Tests\Unit;

use Tests\TestCase;  // Bukan PHPUnit\Framework\TestCase
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Produk;
use App\Models\Kategori;

class ProdukRelationshipTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_produk_memiliki_relasi_ke_kategori(): void
    {
        $kategori = Kategori::factory()->create(['nama_kategori' => 'Laptop Gaming']);
        $produk = Produk::factory()->create(['id_kategori' => $kategori->id_kategori]);
        
        $this->assertInstanceOf(Kategori::class, $produk->kategori);
        $this->assertEquals('Laptop Gaming', $produk->kategori->nama_kategori);
    }
}
```

---

## 📋 Assertions yang Sering Digunakan

### HTTP Response Assertions

```php
$response->assertStatus(200);           // Status code 200 OK
$response->assertOk();                  // Sama dengan assertStatus(200)
$response->assertRedirect('/stok');     // Redirect ke URL
$response->assertViewIs('katalog.index'); // View yang digunakan
$response->assertViewHas('produk');     // View memiliki variable $produk
$response->assertSee('Laptop ASUS');    // HTML mengandung teks
$response->assertDontSee('Produk Habis'); // HTML tidak mengandung teks
$response->assertJson(['success' => true]); // Response JSON
```

### Database Assertions

```php
$this->assertDatabaseHas('produk', [
    'nama_produk' => 'Laptop ASUS',
    'stok' => 10
]);

$this->assertDatabaseMissing('produk', [
    'nama_produk' => 'Produk Tidak Ada'
]);

$this->assertDatabaseCount('produk', 5); // Ada 5 produk di database
```

### General Assertions (PHPUnit)

```php
$this->assertTrue($value);
$this->assertFalse($value);
$this->assertEquals($expected, $actual);
$this->assertSame($expected, $actual);  // Lebih strict (termasuk tipe data)
$this->assertNull($value);
$this->assertNotNull($value);
$this->assertEmpty($array);
$this->assertCount(3, $array);
$this->assertInstanceOf(Produk::class, $object);
$this->assertStringContainsString('ASUS', $text);
```

---

## ✨ Best Practices

### 1. **Naming Convention**
```php
// ✅ GOOD - Deskriptif dan jelas
public function test_user_dapat_melihat_halaman_katalog(): void

// ❌ BAD - Tidak jelas
public function test1(): void
```

### 2. **Arrange-Act-Assert (AAA) Pattern**
```php
public function test_example(): void
{
    // Arrange - Setup data dan kondisi
    $produk = Produk::factory()->create();
    
    // Act - Lakukan aksi yang ingin ditest
    $response = $this->get("/produk/{$produk->id_produk}");
    
    // Assert - Verifikasi hasilnya
    $response->assertStatus(200);
}
```

### 3. **Satu Test, Satu Assertion (idealnya)**
Fokus pada satu hal yang ingin diverifikasi per test.

```php
// ✅ GOOD
public function test_produk_ditampilkan_di_halaman(): void
{
    $response = $this->get('/katalog');
    $response->assertStatus(200);
}

public function test_produk_berisi_nama(): void
{
    $response = $this->get('/katalog');
    $response->assertSee('Laptop ASUS');
}
```

### 4. **Gunakan Factory untuk Test Data**
```php
// ✅ GOOD - Menggunakan factory
$produk = Produk::factory()->create(['stok' => 10]);

// ❌ BAD - Hardcode semua field
$produk = Produk::create([
    'id_kategori' => 1,
    'nama_produk' => 'Test',
    'merk' => 'Test',
    // ... 10 field lainnya
]);
```

### 5. **Isolasi Test**
Setiap test harus independen dan tidak bergantung pada test lain.

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase; // Reset database setiap test
}
```

### 6. **Test Edge Cases**
```php
public function test_margin_persentase_ketika_harga_beli_nol(): void
{
    $produk = new Produk(['harga_beli' => 0, 'harga_jual' => 1000]);
    $this->assertEquals(0, $produk->margin_persentase);
}
```

---

## 🎓 Workflow Testing yang Disarankan

1. **TDD (Test-Driven Development)** - Opsional tapi recommended
   - Tulis test dulu → Test gagal → Tulis kode → Test berhasil → Refactor

2. **Testing Workflow Standar**
   ```bash
   # 1. Tulis kode
   # 2. Tulis test
   # 3. Jalankan test
   php artisan test
   
   # 4. Jika gagal, perbaiki
   # 5. Ulangi sampai semua hijau ✅
   ```

3. **Kapan Menulis Test?**
   - ✅ Fitur baru → Tulis feature test
   - ✅ Bug ditemukan → Tulis test yang reproduce bug, lalu fix
   - ✅ Logic bisnis kompleks → Tulis unit test
   - ✅ Refactoring → Pastikan test existing tetap pass

---

## 📚 Resources

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel Factories](https://laravel.com/docs/database-testing#defining-model-factories)

---

## 🔥 Quick Reference

### Membuat Test Baru

```bash
# Feature test
php artisan make:test KatalogControllerTest

# Unit test
php artisan make:test ProdukTest --unit
```

### Template Quick Start

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_example(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
```

---

**Happy Testing! 🎉**

Testing bukan hanya tentang menemukan bug, tapi juga tentang **confidence** dalam kode yang kita tulis.
