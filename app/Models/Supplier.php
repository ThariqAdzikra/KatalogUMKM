<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'supplier';
    protected $primaryKey = 'id_supplier';
    
    /**
     * 'no_hp' dikembalikan ke 'kontak' agar sesuai dengan database.
     */
    protected $fillable = [
        'nama_supplier',
        'kontak', 
        'alamat',
        'email', 
    ];

    // Relasi ke Pembelian
    public function pembelian()
    {
        return $this->hasMany(Pembelian::class, 'id_supplier');
    }
}