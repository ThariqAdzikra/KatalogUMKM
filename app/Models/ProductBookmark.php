<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBookmark extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'id_produk'
    ];
    
    /**
     * Relationship to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Relationship to Produk
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}
