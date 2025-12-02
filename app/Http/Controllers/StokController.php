<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; 
use App\Models\Pembelian;
use App\Models\PembelianDetail;

class StokController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Query untuk statistik
        $baseQuery = Produk::query();

        // Filter berdasarkan pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                  ->orWhere('merk', 'like', "%{$search}%")
                  ->orWhere('spesifikasi', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan status stok
        if ($request->filled('status_stok')) {
            switch ($request->status_stok) {
                case 'habis':
                    $baseQuery->where('stok', 0);
                    break;
                case 'menipis':
                    $baseQuery->where('stok', '>', 0)->where('stok', '<=', 5);
                    break;
                case 'tersedia':
                    $baseQuery->where('stok', '>', 5);
                    break;
            }
        }
        
        // Ambil statistik *sebelum* paginasi
        $stats = [
            'total' => $baseQuery->count(),
            'tersedia' => (clone $baseQuery)->where('stok', '>', 5)->count(),
            'menipis' => (clone $baseQuery)->where('stok', '>', 0)->where('stok', '<=', 5)->count(),
            'habis' => (clone $baseQuery)->where('stok', 0)->count(),
        ];

        // Lanjutkan query untuk data paginasi
        $query = $baseQuery;
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $produk = $query->with('kategori')->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('stok.partials.table_wrapper', compact('produk'))->render(),
            ]);
        }

        return view('stok.index', compact('produk', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($pembelian = null)
    {
        $kategori = Kategori::orderBy('nama_kategori', 'asc')->get();
        $semua_produk = Produk::orderBy('nama_produk', 'asc')->get();
        $semua_produk_data = $semua_produk->keyBy('id_produk');
        $id_pembelian = null;
        $pembelianData = null;
        
        if ($pembelian) {
            $pembelianModel = Pembelian::with('supplier')->find($pembelian);
            if ($pembelianModel) {
                $id_pembelian = $pembelianModel->id_pembelian;
                $pembelianData = $pembelianModel;
            }
        }

        return view('stok.create', compact(
            'kategori', 
            'id_pembelian', 
            'pembelianData', 
            'semua_produk', 
            'semua_produk_data' 
        ));
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. LOGIKA MENANGANI KATEGORI BARU
        $inputKategori = $request->id_kategori;

        if ($inputKategori && !is_numeric($inputKategori)) {
            $kategoriBaru = Kategori::firstOrCreate(
                ['nama_kategori' => $inputKategori], 
                ['slug' => Str::slug($inputKategori)]
            );
            $request->merge(['id_kategori' => $kategoriBaru->id_kategori]);
        }

        // 2. Validasi dasar
        $validated = $request->validate([
            'id_produk_existing' => 'nullable', 
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'nama_produk' => 'required', 
            'merk' => 'required|string|max:100',
            'spesifikasi' => 'required|string',
            'garansi' => 'required|integer|min:0', 
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0', 
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'id_pembelian' => 'nullable|exists:pembelian,id_pembelian',
        ], [
            'id_kategori.required' => 'Kategori produk harus dipilih',
            'nama_produk.required' => 'Nama produk harus diisi',
            'garansi.required' => 'Garansi harus diisi (masukkan 0 jika tidak ada).', 
        ]);

        $actionMessage = '';
        $produk = null;
        $jumlah_ditambah = $validated['stok'];
        
        // DETEKSI PRODUK EXISTING
        $existingProduct = null;
        if (is_numeric($request->nama_produk)) {
            $existingProduct = Produk::find($request->nama_produk);
        }
        if (!$existingProduct && $request->filled('id_produk_existing')) {
            $existingProduct = Produk::find($request->id_produk_existing);
        }

        if ($existingProduct) {
            // === UPDATE PRODUK LAMA ===
            $produk = $existingProduct;
            $produkData = $validated;
            
            unset($produkData['nama_produk']); 
            unset($produkData['id_pembelian'], $produkData['id_produk_existing'], $produkData['stok']);

            if ($request->hasFile('gambar')) {
                if ($produk->gambar) {
                    // Bersihkan path lama dari prefix storage/ agar bisa dihapus
                    $oldPath = str_replace(['storage/', 'public/'], '', $produk->gambar);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
                $path = $request->file('gambar')->store('produk', 'public');
                
                // ✅ FIX UTAMA: Tambahkan 'storage/' agar konsisten dengan seeder
                $produkData['gambar'] = 'storage/' . $path; 
            } else {
                unset($produkData['gambar']);
            }
            
            $produk->update($produkData);
            $produk->increment('stok', $jumlah_ditambah);
            $actionMessage = "Stok untuk '{$produk->nama_produk}' berhasil ditambah.";

        } else {
            // === BUAT PRODUK BARU ===
            $produkData = $validated;
            $produkData['nama_produk'] = $request->nama_produk;

            unset($produkData['id_pembelian'], $produkData['id_produk_existing']);

            if ($request->hasFile('gambar')) {
                $path = $request->file('gambar')->store('produk', 'public');
                
                // ✅ FIX UTAMA: Tambahkan 'storage/' agar konsisten dengan seeder
                $produkData['gambar'] = 'storage/' . $path;
            }

            $produk = Produk::create($produkData);
            $actionMessage = "Stok untuk '{$produk->nama_produk}' berhasil ditambah.";
        }

        // LOGIKA PENCATATAN PEMBELIAN
        if (isset($validated['id_pembelian']) && $validated['id_pembelian']) {
            $pembelian = Pembelian::find($validated['id_pembelian']);
            $subtotal = $validated['harga_beli'] * $jumlah_ditambah; 
            
            PembelianDetail::create([
                'id_pembelian' => $pembelian->id_pembelian,
                'id_produk' => $produk->id_produk,
                'jumlah' => $jumlah_ditambah,
                'harga_satuan' => $validated['harga_beli'],
                'subtotal' => $subtotal,
            ]);

            $pembelian->increment('total_harga', $subtotal);
            
            return redirect()->route('stok.create', ['pembelian' => $pembelian->id_pembelian])
                             ->with('success', $actionMessage . ' Data dicatat dalam pembelian.');
        }

        return redirect()->route('stok.index')
            ->with('success', $actionMessage);
    }
  
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $stok = Produk::with('kategori')->findOrFail($id);
        return view('stok.show', compact('stok'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $stok = Produk::findOrFail($id);
        $kategori = Kategori::orderBy('nama_kategori', 'asc')->get();
        return view('stok.edit', compact('stok', 'kategori'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $stok = Produk::findOrFail($id);

        $inputKategori = $request->id_kategori;
        if ($inputKategori && !is_numeric($inputKategori)) {
            $kategoriBaru = Kategori::firstOrCreate(
                ['nama_kategori' => $inputKategori], 
                ['slug' => Str::slug($inputKategori)]
            );
            $request->merge(['id_kategori' => $kategoriBaru->id_kategori]);
        }

        $validated = $request->validate([
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'nama_produk' => 'required|string|max:150',
            'merk' => 'required|string|max:100',
            'spesifikasi' => 'required|string',
            'garansi' => 'nullable|integer|min:0', 
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('gambar')) {
            if ($stok->gambar) {
                $oldPath = str_replace(['storage/', 'public/'], '', $stok->gambar);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $newPath = $request->file('gambar')->store('produk', 'public');
            
            // ✅ FIX UTAMA: Tambahkan 'storage/' saat update juga
            $validated['gambar'] = 'storage/' . $newPath;
        }

        $stok->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'message' => "Data produk '{$stok->nama_produk}' berhasil diperbarui!"
            ]);
        }

        return redirect()
            ->route('stok.index')
            ->with('success', "Data produk '{$stok->nama_produk}' berhasil diperbarui!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $stok = Produk::findOrFail($id);

        if ($stok->gambar) {
            $path = str_replace(['storage/', 'public/'], '', $stok->gambar);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $stok->delete();

        if (request()->ajax()) {
            return response()->json([
                'message' => 'Produk berhasil dihapus dari stok!'
            ]);
        }

        return redirect()->route('stok.index')
            ->with('success', 'Produk berhasil dihapus dari stok!');
    }

    /**
     * Update stock quantity (simple method)
     */
    public function updateStok(Request $request, $id)
    {
        $stok = Produk::findOrFail($id);

        $validated = $request->validate([
            'stok' => 'required|integer|min:0',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $stok->update(['stok' => $validated['stok']]);

        return redirect()->back()
            ->with('success', 'Stok berhasil diperbarui!');
    }
}