<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan';
    protected $primaryKey = 'id_penjualan';
    protected $fillable = [
        'id_user',
        'id_pelanggan',
        'tanggal_penjualan',
        'total_harga',
        'metode_pembayaran',
    ];  

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

    /**
     * Mendefinisikan relasi ke User.
     * Foreign key 'id_user' di tabel ini merujuk ke primary key 'id' di tabel users.
     */
    public function user()
    {
        // Penyesuaian: Menambahkan 'id' sebagai primary key di model User
        return $this->belongsTo(User::class, 'id_user', 'id'); 
    }

    public function detail()
    {
        return $this->hasMany(PenjualanDetail::class, 'id_penjualan');
    }
}