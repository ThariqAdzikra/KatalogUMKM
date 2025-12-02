<?php

namespace App\Http\Controllers\Pembelian;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Produk;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class PembelianController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembelian::with('supplier')
            // Filter: Hanya tampilkan yang total harga > 0 dan memiliki detail
            ->where('total_harga', '>', 0)
            ->has('detail') 
            
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('supplier', function ($s) use ($request) {
                    $s->where('nama_supplier', 'like', "%{$request->search}%");
                });
            })
            ->when($request->filled('sort'), function ($q) use ($request) {
                if ($request->sort === 'tanggal') {
                    $q->orderBy('tanggal_pembelian', 'desc');
                } elseif ($request->sort === 'total') {
                    $q->orderBy('total_harga', 'desc');
                }
            });

        if ($request->filled('dari_tanggal') && $request->filled('sampai_tanggal')) {
            $query->whereBetween('tanggal_pembelian', [
                $request->dari_tanggal,
                $request->sampai_tanggal,
            ]);
        } elseif ($request->filled('dari_tanggal')) {
            $query->whereDate('tanggal_pembelian', '>=', $request->dari_tanggal);
        } elseif ($request->filled('sampai_tanggal')) {
            $query->whereDate('tanggal_pembelian', '<=', $request->sampai_tanggal);
        }

        if (!$request->filled('sort')) {
            $query->orderBy('tanggal_pembelian', 'desc')
                  ->orderBy('id_pembelian', 'desc');
        }

        $pembelian = $query->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pembelian.partials.table_wrapper', compact('pembelian'))->render(),
            ]);
        }

        return view('pembelian.index', compact('pembelian'));
    }

    public function create()
    {
        return view('pembelian.create');
    }

    public function storeHeader(Request $request)
    {
        $existingSupplier = Supplier::where('nama_supplier', $request->nama_supplier)->first();
        $rules = [
            'nama_supplier' => 'required|string|max:255',
            'tanggal_pembelian' => 'required|date',
        ];

        if ($existingSupplier) {
            $rules['kontak'] = 'nullable|string|max:20';
            $rules['alamat'] = 'nullable|string';
        } else {
            $rules['kontak'] = 'required|string|max:20';
            $rules['alamat'] = 'required|string';
        }

        $request->validate($rules);
        if ($existingSupplier) {
            $dataToUpdate = [];
            if ($request->filled('kontak')) {
                $dataToUpdate['kontak'] = $request->kontak;
            }
            if ($request->filled('alamat')) {
                $dataToUpdate['alamat'] = $request->alamat;
            }
            
            if (!empty($dataToUpdate)) {
                $existingSupplier->update($dataToUpdate);
            }
            $supplier = $existingSupplier;

        } else {
            $supplier = Supplier::create([
                'nama_supplier' => $request->nama_supplier,
                'kontak' => $request->kontak,
                'alamat' => $request->alamat,
            ]);
        }

        $pembelian = Pembelian::create([
            'id_supplier' => $supplier->id_supplier,
            'id_user' => Auth::id(),
            'tanggal_pembelian' => $request->tanggal_pembelian,
            'total_harga' => 0 
        ]);

        // Redirect ke Langkah 2 (stok.create)
        return redirect()->route('stok.create', ['pembelian' => $pembelian->id_pembelian])
                         ->with('info', 'Data supplier & pembelian berhasil disimpan. Silakan tambahkan produk.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_supplier' => 'required',
            'tanggal_pembelian' => 'required|date',
            'produk.*' => 'required',
            'jumlah.*' => 'required|integer|min:1',
            'harga_satuan.*' => 'required|numeric|min:0',
        ]);

        // Simpan transaksi utama
        $pembelian = Pembelian::create([
            'id_supplier' => $request->id_supplier,
            'id_user' => Auth::id(),
            'tanggal_pembelian' => $request->tanggal_pembelian,
            'total_harga' => 0
        ]);

        $total = 0;
        foreach ($request->produk as $index => $id_produk) {
            $jumlah = $request->jumlah[$index];
            $harga = $request->harga_satuan[$index];
            $subtotal = $jumlah * $harga;

            PembelianDetail::create([
                'id_pembelian' => $pembelian->id_pembelian,
                'id_produk' => $id_produk,
                'jumlah' => $jumlah,
                'harga_satuan' => $harga,
                'subtotal' => $subtotal,
            ]);

            // update stok produk
            $produk = Produk::find($id_produk);
            $produk->stok += $jumlah;
            $produk->save();

            $total += $subtotal;
        }

        $pembelian->update(['total_harga' => $total]);

        return redirect()->route('pembelian.index')->with('success', 'Data pembelian berhasil disimpan');
    }

    public function destroy(Request $request, $id)
    {
        $pembelian = Pembelian::findOrFail($id);

        foreach ($pembelian->detail as $detail) {
            $produk = $detail->produk;
            if($produk) {
                $produk->stok -= $detail->jumlah;
                $produk->save();
            }
        }

        $pembelian->detail()->delete();
        $pembelian->delete();

        // Jika request dari AJAX (Hapus di halaman Index)
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pembelian berhasil dihapus',
            ]);
        }

        // ✅ PERBAIKAN: Jika request dari Form Batal (Create Stok), Redirect ke Index
        return redirect()->route('pembelian.index')->with('success', 'Transaksi pembelian dibatalkan.');
    }

    public function edit($id)
    {
        $pembelian = Pembelian::with('detail')->findOrFail($id);
        $supplier = Supplier::all();
        $produk = Produk::all();
        return view('pembelian.edit', compact('pembelian', 'supplier', 'produk'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_supplier' => 'required',
            'tanggal_pembelian' => 'required|date',
            'produk' => 'required|array|min:1',
            'produk.*.id_produk' => 'required|integer|exists:produk,id_produk',
            'produk.*.jumlah' => 'required|integer|min:1',
            'produk.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        $pembelian = Pembelian::findOrFail($id);
        $pembelian->update([
            'id_supplier' => $request->id_supplier,
            'tanggal_pembelian' => $request->tanggal_pembelian,
        ]);

        // Hapus detail lama
        $pembelian->detail()->delete();

        $total = 0;
        foreach ($request->produk as $row) {
            $id_produk = (int) ($row['id_produk'] ?? 0);
            $jumlah = (int) ($row['jumlah'] ?? 0);
            $harga = (float) ($row['harga_satuan'] ?? 0);

            $subtotal = $jumlah * $harga;

            $pembelian->detail()->create([
                'id_produk' => $id_produk,
                'jumlah' => $jumlah,
                'harga_satuan' => $harga,
                'subtotal' => $subtotal,
            ]);

            $total += $subtotal;
        }

        $pembelian->update(['total_harga' => $total]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pembelian berhasil diperbarui',
                'id_pembelian' => $pembelian->id_pembelian,
            ]);
        }

        return redirect()->route('pembelian.index')->with('success', 'Data pembelian berhasil diperbarui');
    }

    public function show($id)
    {
        $pembelian = Pembelian::with('supplier', 'detail.produk', 'user')->findOrFail($id);
        return view('pembelian.show', compact('pembelian'));
    }

}