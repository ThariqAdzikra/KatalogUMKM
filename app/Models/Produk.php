<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'produk';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_produk';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok' => 'integer',
        'garansi' => 'integer', 
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id_produk';
    }

    /**
     * Accessor untuk mendapatkan margin keuntungan
     *
     * @return float
     */
    public function getMarginAttribute()
    {
        return $this->harga_jual - $this->harga_beli;
    }

    /**
     * Accessor untuk mendapatkan persentase margin
     *
     * @return float
     */
    public function getMarginPersentaseAttribute()
    {
        if ($this->harga_beli == 0) {
            return 0;
        }
        return (($this->harga_jual - $this->harga_beli) / $this->harga_beli) * 100;
    }

    /**
     * Accessor untuk status stok
     *
     * @return string
     */
    public function getStatusStokAttribute()
    {
        if ($this->stok == 0) {
            return 'habis';
        } elseif ($this->stok <= 5) {
            return 'menipis';
        } else {
            return 'tersedia';
        }
    }

    /**
     * Accessor untuk total nilai stok
     *
     * @return float
     */
    public function getTotalNilaiStokAttribute()
    {
        return $this->harga_jual * $this->stok;
    }

    /**
     * Relasi many-to-one ke Kategori.
     * Satu produk hanya memiliki satu kategori.
     */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    /**
     * Scope untuk filter produk dengan stok habis
     */
    public function scopeStokHabis($query)
    {
        return $query->where('stok', 0);
    }

    /**
     * Scope untuk filter produk dengan stok menipis
     */
    public function scopeStokMenipis($query)
    {
        return $query->where('stok', '>', 0)->where('stok', '<=', 5);
    }

    /**
     * Scope untuk filter produk dengan stok tersedia
     */
    public function scopeStokTersedia($query)
    {
        return $query->where('stok', '>', 5);
    }

    /**
     * Scope untuk pencarian produk
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nama_produk', 'like', "%{$search}%")
              ->orWhere('merk', 'like', "%{$search}%")
              ->orWhere('spesifikasi', 'like', "%{$search}%");
        });
    }

    /**
     * Scope untuk filter berdasarkan slug kategori
     */
    public function scopeFilterByKategori($query, $slug)
    {
        if ($slug) {
            return $query->whereHas('kategori', function ($q) use ($slug) {
                $q->where('slug', $slug);
            });
        }
        return $query;
    }
}