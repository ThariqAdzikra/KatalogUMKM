<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kategori; // Import model Kategori
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // ✅ DITAMBAHKAN: Import Auth facade

class KatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Produk::with('kategori');

        if (!Auth::check()) {
            $query->where('stok', '>', 0);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                    ->orWhere('merk', 'like', "%{$search}%")
                    ->orWhere('spesifikasi', 'like', "%{$search}%");
            });
        }

        // Logika filter kategori berdasarkan relasi
        if ($request->filled('kategori')) {
            $kategoriSlug = $request->kategori;
            $query->whereHas('kategori', function ($q) use ($kategoriSlug) {
                $q->where('slug', $kategoriSlug);
            });
        }

        // Ambil daftar kategori untuk dropdown
        $kategori = Kategori::orderBy('nama_kategori', 'asc')->get();

        // Paginate results
        $produk = $query->latest()->paginate(12)->withQueryString(); 

        // Kirim data 'kategori' ke view
        return view('katalog.index', compact('produk', 'kategori'));
    }
}