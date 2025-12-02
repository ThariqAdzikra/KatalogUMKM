# 🚀 Quick Start: Cara Menulis dan Menjalankan Test

## ⚡ Workflow Cepat (3 Langkah)

### 1️⃣ **Buat Test File**
```bash
# Untuk Unit test (test model/class)
php artisan make:test NamaModelTest --unit

# Untuk Feature test (test controller/HTTP)
php artisan make:test NamaControllerTest
```

### 2️⃣ **Tulis Test**
Buka file test yang dibuat, lalu tulis test dengan pattern **Arrange-Act-Assert**:

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\NamaModel;

class NamaModelTest extends TestCase
{
    public function test_contoh_sederhana(): void
    {
        // Arrange - Setup data
        $model = new NamaModel(['field' => 'value']);
        
        // Act - Lakukan sesuatu (opsional untuk test sederhana)
        $result = $model->field;
        
        // Assert - Verifikasi hasilnya
        $this->assertEquals('value', $result);
    }
}
```

### 3️⃣ **Jalankan Test**
```bash
# Jalankan test spesifik
php artisan test tests/Unit/NamaModelTest.php

# Jalankan semua Unit tests
php artisan test --testsuite=Unit

# Jalankan semua tests
php artisan test
```

---

## 📝 Template & Contoh

### Template Unit Test (Tanpa Database)

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;  // ← Penting: PHPUnit\Framework\TestCase
use App\Models\YourModel;

class YourModelTest extends TestCase
{
    /**
     * Test deskripsi apa yang dites
     */
    public function test_sesuatu_yang_dites(): void
    {
        // Arrange
        $model = new YourModel([
            'field1' => 'value1',
            'field2' => 100
        ]);
        
        // Act (jika perlu)
        $result = $model->someMethod();
        
        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

### Template Unit Test (Dengan Database)

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;  // ← Penting: Tests\TestCase (bukan PHPUnit)
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\YourModel;

class YourModelDatabaseTest extends TestCase
{
    use RefreshDatabase;  // Reset database setiap test
    
    public function test_sesuatu_dengan_database(): void
    {
        // Arrange: Buat data dengan factory
        $model = YourModel::factory()->create([
            'field' => 'value'
        ]);
        
        // Act
        $found = YourModel::find($model->id);
        
        // Assert
        $this->assertEquals('value', $found->field);
    }
}
```

### Template Feature Test

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class YourControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_halaman_dapat_diakses(): void
    {
        // Arrange: Login jika perlu
        $user = User::factory()->create();
        
        // Act: Hit endpoint
        $response = $this->actingAs($user)->get('/your-route');
        
        // Assert
        $response->assertStatus(200);
        $response->assertSee('Expected Text');
    }
}
```

---

## 🎯 Contoh Konkret: Test Model Pelanggan

**1. Buat test:**
```bash
php artisan make:test PelangganTest --unit
```

**2. Edit file `tests/Unit/PelangganTest.php`:**

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Pelanggan;

class PelangganTest extends TestCase
{
    public function test_pelanggan_punya_nama(): void
    {
        $pelanggan = new Pelanggan(['nama_pelanggan' => 'Budi']);
        $this->assertEquals('Budi', $pelanggan->nama_pelanggan);
    }
    
    public function test_fillable_attributes(): void
    {
        $pelanggan = new Pelanggan();
        $fillable = $pelanggan->getFillable();
        
        $this->assertContains('nama_pelanggan', $fillable);
        $this->assertContains('alamat', $fillable);
        $this->assertContains('no_telepon', $fillable);
    }
}
```

**3. Jalankan:**
```bash
php artisan test tests/Unit/PelangganTest.php
```

---

## 🔥 Assertions yang Sering Dipakai

### Basic Assertions
```php
// Equality
$this->assertEquals($expected, $actual);
$this->assertSame($expected, $actual);  // Strict (sama tipe)

// Boolean
$this->assertTrue($value);
$this->assertFalse($value);

// Null
$this->assertNull($value);
$this->assertNotNull($value);

// Arrays
$this->assertCount(3, $array);
$this->assertContains('item', $array);
```

### HTTP Assertions (Feature Tests)
```php
$response->assertStatus(200);        // HTTP 200 OK
$response->assertOk();               // Sama dengan 200
$response->assertRedirect('/home');  // Redirect
$response->assertSee('Text');        // HTML contains text
$response->assertDontSee('Text');    // HTML tidak contains text
```

### Database Assertions (Feature Tests)
```php
$this->assertDatabaseHas('table_name', [
    'column' => 'value'
]);

$this->assertDatabaseMissing('table_name', [
    'column' => 'value'
]);

$this->assertDatabaseCount('table_name', 5);
```

---

## 💡 Tips Praktis

### 1. **Mulai dari yang Sederhana**
Test model dulu sebelum controller:
```bash
✅ Unit Test (Model) → Mudah
⬇️
Feature Test (Controller) → Lebih kompleks
```

### 2. **Naming Convention yang Jelas**
```php
// ✅ GOOD
public function test_user_dapat_login_dengan_kredensial_benar(): void

// ❌ BAD
public function test1(): void
```

### 3. **Satu Test = Satu Hal**
```php
// ✅ GOOD - Focus pada satu hal
public function test_produk_dapat_dihapus(): void
{
    $produk = Produk::factory()->create();
    $produk->delete();
    $this->assertDatabaseMissing('produk', ['id_produk' => $produk->id_produk]);
}
```

### 4. **Gunakan Factory**
```php
// ✅ GOOD - Pakai factory
$produk = Produk::factory()->create(['stok' => 10]);

// ❌ BAD - Manual semua field
$produk = Produk::create([
    'id_kategori' => 1,
    'nama_produk' => 'Test',
    'merk' => 'Test',
    // ... 10 field lagi
]);
```

---

## 🎓 Latihan Mandiri

### Latihan 1: Test Model Supplier
```bash
# 1. Buat test
php artisan make:test SupplierTest --unit

# 2. Tulis test untuk:
# - Test fillable attributes
# - Test primary key
# - Test timestamps
```

### Latihan 2: Test Accessor/Mutator
Jika model punya custom accessor:
```php
public function test_accessor_bekerja(): void
{
    $model = new Model(['field' => 'value']);
    $this->assertEquals('EXPECTED', $model->custom_accessor);
}
```

---

## 📊 Kesimpulan

**Workflow Testing:**
```
1. Buat test file
   ↓
2. Tulis test (Arrange → Act → Assert)
   ↓
3. Jalankan test
   ↓
4. Liat hasil (hijau ✅ / merah ❌)
   ↓
5. Fix kalau merah, repeat
```

**Files yang Sudah Berhasil di Project Ini:**
- ✅ `tests/Unit/ProdukTest.php` - 13 tests PASSED
- ✅ `tests/Unit/KategoriTest.php` - 4 tests PASSED

**Langkah Selanjutnya:**
1. Lihat contoh di file-file yang sudah ada
2. Copy pattern-nya untuk model lain
3. Jalankan dan lihat hasilnya
4. Expand ke Feature tests kalau sudah nyaman

---

**🚀 Selamat Mencoba!**

Kalau ada error, baca pesan errornya dengan teliti. Laravel error messages biasanya sangat informatif!
