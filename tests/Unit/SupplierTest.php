<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Supplier;

/**
 * Unit Test untuk Model Supplier
 * 
 * Test basic functionality dan attributes
 */
class SupplierTest extends TestCase
{
    /**
     * Test: Supplier dapat dibuat dengan data yang benar
     */
    public function test_supplier_dapat_dibuat_dengan_data_benar(): void
    {
        // Arrange
        $supplier = new Supplier([
            'nama_supplier' => 'PT. Laptop Indonesia',
            'kontak' => '021-12345678',
            'alamat' => 'Jl. Supplier No. 1, Jakarta',
            'email' => 'supplier@example.com'
        ]);

        // Assert
        $this->assertEquals('PT. Laptop Indonesia', $supplier->nama_supplier);
        $this->assertEquals('021-12345678', $supplier->kontak);
        $this->assertEquals('Jl. Supplier No. 1, Jakarta', $supplier->alamat);
        $this->assertEquals('supplier@example.com', $supplier->email);
    }

    /**
     * Test: Fillable attributes
     */
    public function test_fillable_attributes(): void
    {
        // Arrange
        $supplier = new Supplier();

        // Act
        $fillable = $supplier->getFillable();

        // Assert
        $expectedFillable = [
            'nama_supplier',
            'kontak',
            'alamat',
            'email'
        ];

        foreach ($expectedFillable as $field) {
            $this->assertContains($field, $fillable, "Field '$field' seharusnya fillable");
        }
    }

    /**
     * Test: Primary key adalah id_supplier
     */
    public function test_primary_key_adalah_id_supplier(): void
    {
        $supplier = new Supplier();
        $this->assertEquals('id_supplier', $supplier->getKeyName());
    }

    /**
     * Test: Table name adalah 'supplier'
     */
    public function test_table_name_adalah_supplier(): void
    {
        $supplier = new Supplier();
        $this->assertEquals('supplier', $supplier->getTable());
    }

    /**
     * Test: Model menggunakan timestamps
     */
    public function test_model_menggunakan_timestamps(): void
    {
        $supplier = new Supplier();
        $this->assertTrue($supplier->usesTimestamps());
    }

    /**
     * Test: Nama supplier dapat diisi
     */
    public function test_nama_supplier_dapat_diisi(): void
    {
        $supplier = new Supplier(['nama_supplier' => 'PT. Test']);
        $this->assertEquals('PT. Test', $supplier->nama_supplier);
    }

    /**
     * Test: Kontak dapat diisi dengan berbagai format
     */
    public function test_kontak_dapat_diisi(): void
    {
        // Test dengan nomor telepon
        $supplier1 = new Supplier(['kontak' => '021-12345678']);
        $this->assertEquals('021-12345678', $supplier1->kontak);

        // Test dengan nomor HP
        $supplier2 = new Supplier(['kontak' => '081234567890']);
        $this->assertEquals('081234567890', $supplier2->kontak);
    }
}
