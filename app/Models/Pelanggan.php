<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';
    protected $primaryKey = 'id_pelanggan';
    protected $fillable = [
    'nama',
    'no_hp',
    'email',
    'alamat',
    'id_produk',
    'tanggal_pembelian', 
    'garansi',
    'catatan',
    
    ];

    public function produkDibeli()
    {
        return $this->hasManyThrough(
        \App\Models\Produk::class,
        \App\Models\Penjualan::class,
        'id_pelanggan',  // FK di tabel penjualan
        'id_produk',     // FK di tabel produk
        'id_pelanggan',  // PK di pelanggan
        'id_penjualan'   // PK di penjualan
        );
    }

    
    public function penjualan()
    {
        return $this->hasMany(\App\Models\Penjualan::class, 'id_pelanggan');
    }
      public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}
