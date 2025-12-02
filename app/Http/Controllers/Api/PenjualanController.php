<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Http\Resources\PenjualanResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class PenjualanController extends Controller
{
    /**
     * Menampilkan rekap data penjualan (index/laporan).
     *
     * @queryParam dari_tanggal string Tanggal mulai (YYYY-MM-DD).
     * @queryParam sampai_tanggal string Tanggal akhir (YYYY-MM-DD).
     * @queryParam metode string Filter berdasarkan metode pembayaran (cash, transfer, qris).
     * @queryParam search string Cari berdasarkan nama pelanggan.
     * @queryParam sort string Urutkan berdasarkan 'tanggal' (default), 'total', 'nama'.
     * @queryParam page integer Halaman paginasi.
     */
    public function index(Request $request)
    {
        // Validasi input query
        $request->validate([
            'dari_tanggal' => 'nullable|date',
            'sampai_tanggal' => 'nullable|date|after_or_equal:dari_tanggal',
            'metode' => 'nullable|in:cash,transfer,qris',
            'search' => 'nullable|string',
            'sort' => 'nullable|in:tanggal,total,nama',
        ]);

        $query = Penjualan::with(['pelanggan', 'user']);

        // Filter Laporan: Tanggal
        if ($request->filled('dari_tanggal') && $request->filled('sampai_tanggal')) {
            $query->whereBetween('tanggal_penjualan', [
                $request->dari_tanggal,
                $request->sampai_tanggal
            ]);
        }

        // Filter: Metode Pembayaran
        if ($request->filled('metode')) {
            $query->where('metode_pembayaran', $request->metode);
        }

        // Filter: Search Nama Pelanggan
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('pelanggan', function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%');
            });
        }

        // Sorting
        $sort = $request->input('sort', 'tanggal');
        switch ($sort) {
            case 'total':
                $query->orderBy('total_harga', 'desc');
                break;
            case 'nama':
                $query->join('pelanggan', 'pelanggan.id_pelanggan', '=', 'penjualan.id_pelanggan')
                      ->orderBy('pelanggan.nama', 'asc')
                      ->select('penjualan.*'); // Hindari ambiguitas kolom
                break;
            default:
                $query->orderBy('tanggal_penjualan', 'desc');
        }

        // Paginasi
        $penjualan = $query->paginate(15)->withQueryString();

        // Kembalikan sebagai koleksi Resource
        return PenjualanResource::collection($penjualan);
    }

    /**
     * Menyimpan transaksi penjualan baru.
     */
    public function store(Request $request)
    {
        // Validasi (sama seperti controller web Anda)
        $validated = $request->validate([
            'id_pelanggan'        => 'nullable|string',
            'nama_pelanggan_baru' => 'nullable|string|max:100',
            'no_hp_baru'          => 'nullable|string|max:20',
            'email_baru'          => 'nullable|email|max:100',
            'alamat_baru'         => 'nullable|string|max:255',
            'tanggal_penjualan'   => 'required|date',
            'metode_pembayaran'   => 'required|in:cash,transfer,qris',
            'produk'              => 'required|array|min:1',
            'produk.*.id_produk'  => 'required|exists:produk,id_produk', // Sesuaikan jika input array produk beda
            'produk.*.jumlah'     => 'required|integer|min:1',
        ]);

        try {
            $penjualan = DB::transaction(function () use ($request) {

                $idPelanggan = null;
                // Cek pelanggan baru dari Select2 (jika formatnya "NEW_Nama")
                $isNew = str_starts_with($request->id_pelanggan ?? '', 'NEW_');

                if (!$isNew && $request->filled('id_pelanggan') && is_numeric($request->id_pelanggan)) {
                    // Pelanggan lama
                    $idPelanggan = (int) $request->id_pelanggan;
                } else {
                    // Pelanggan baru, validasi data baru wajib diisi
                    $request->validate([
                        'nama_pelanggan_baru' => 'required|string|max:100',
                        'no_hp_baru' => 'required|string|max:20',
                    ]);
                    $pelangganBaru = Pelanggan::create([
                        'nama'   => $request->nama_pelanggan_baru,
                        'no_hp'  => $request->no_hp_baru,
                        'email'  => $request->email_baru,
                        'alamat' => $request->alamat_baru,
                    ]);
                    $idPelanggan = $pelangganBaru->id_pelanggan;
                }

                // Buat Header Penjualan
                $penjualanHeader = Penjualan::create([
                    'id_user'           => Auth::id(), // Diambil dari token
                    'id_pelanggan'      => $idPelanggan,
                    'tanggal_penjualan' => $request->tanggal_penjualan,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'total_harga'       => 0, // Dihitung di bawah
                ]);

                $total = 0;
                // Loop melalui array produk
                foreach ($request->produk as $item) {
                    $produk = Produk::lockForUpdate()->findOrFail($item['id_produk']);
                    $jumlah = (int) $item['jumlah'];
                    
                    // Ambil harga jual dari DB, bukan dari inputan, agar lebih aman
                    $harga = $produk->harga_jual; 

                    if ($jumlah > $produk->stok) {
                        throw new Exception("Stok {$produk->nama_produk} tidak mencukupi (tersisa {$produk->stok}).");
                    }

                    $subtotal = $jumlah * $harga;
                    $penjualanHeader->detail()->create([
                        'id_produk'    => $produk->id_produk,
                        'jumlah'       => $jumlah,
                        'harga_satuan' => $harga,
                        'subtotal'     => $subtotal,
                    ]);

                    // Kurangi stok
                    $produk->decrement('stok', $jumlah);
                    $total += $subtotal;
                }

                // Update total harga di header
                $penjualanHeader->update(['total_harga' => $total]);

                return $penjualanHeader;
            });

            // Sukses: Kembalikan data penjualan baru menggunakan Resource
            // Muat relasi yang diperlukan oleh Resource
            $penjualan->load(['pelanggan', 'user', 'detail.produk']);
            
            return response()->json([
                'message' => 'Transaksi berhasil disimpan.',
                'data' => new PenjualanResource($penjualan)
            ], 201); // 201 Created

        } catch (Exception $e) {
            // Gagal: Kembalikan error
            return response()->json([
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
            ], 422); // 422 Unprocessable Entity
        }
    }

    /**
     * Menampilkan detail satu transaksi penjualan.
     */
    public function show(string $id)
    {
        try {
            $penjualan = Penjualan::with([
                'pelanggan',
                'user',
                'detail.produk' // Eager load semua relasi
            ])->findOrFail($id);

            // Kembalikan sebagai satu Resource
            return new PenjualanResource($penjualan);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Data penjualan tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * Update transaksi penjualan.
     * (Catatan: Logic update penjualan biasanya kompleks, ini adalah adaptasi
     * dari logic 'store' Anda, dengan asumsi logic update mirip 'store'
     * tapi membatalkan stok lama dulu)
     */
    public function update(Request $request, string $id)
    {
        // Validasi (mirip dengan store)
        $request->validate([
            'id_pelanggan'        => 'nullable|string',
            'nama_pelanggan_baru' => 'nullable|string|max:100',
            // ... (validasi pelanggan baru lainnya)
            'tanggal_penjualan'   => 'required|date',
            'metode_pembayaran'   => 'required|in:cash,transfer,qris',
            'produk'              => 'required|array|min:1',
            'produk.*.id_produk'  => 'required|exists:produk,id_produk',
            'produk.*.jumlah'     => 'required|integer|min:1',
        ]);

        try {
            $penjualan = DB::transaction(function () use ($request, $id) {
                // 1. Ambil penjualan yang ada dan kunci
                $penjualan = Penjualan::with('detail')->lockForUpdate()->findOrFail($id);

                // 2. Kembalikan stok lama
                foreach ($penjualan->detail as $detail) {
                    $produk = Produk::lockForUpdate()->find($detail->id_produk);
                    if ($produk) {
                        $produk->increment('stok', $detail->jumlah);
                    }
                }

                // 3. Hapus detail lama
                $penjualan->detail()->delete();

                // 4. Tentukan pelanggan (adaptasi dari logic 'update' web Anda)
                $idPelanggan = null;
                $isNew = str_starts_with($request->id_pelanggan ?? '', 'NEW_');

                if (!$isNew && $request->filled('id_pelanggan') && is_numeric($request->id_pelanggan)) {
                    $idPelanggan = (int) $request->id_pelanggan;
                } else {
                     $request->validate([
                        'nama_pelanggan_baru' => 'required|string|max:100',
                        'no_hp_baru' => 'required|string|max:20',
                    ]);
                    $pelangganBaru = Pelanggan::create([
                        'nama'   => $request->nama_pelanggan_baru,
                        'no_hp'  => $request->no_hp_baru,
                        'email'  => $request->email_baru,
                        'alamat' => $request->alamat_baru,
                    ]);
                    $idPelanggan = $pelangganBaru->id_pelanggan;
                }

                // 5. Update header penjualan
                $penjualan->update([
                    'id_pelanggan'      => $idPelanggan,
                    'tanggal_penjualan' => $request->tanggal_penjualan,
                    'metode_pembayaran' => $request->metode_pembayaran,
                ]);

                // 6. Buat detail baru + kurangi stok baru
                $total = 0;
                foreach ($request->produk as $item) {
                    $produk = Produk::lockForUpdate()->findOrFail($item['id_produk']);
                    $jumlah = (int) $item['jumlah'];
                    $harga = $produk->harga_jual; // Ambil harga aman dari DB

                    if ($jumlah > $produk->stok) {
                        throw new Exception("Stok {$produk->nama_produk} tidak mencukupi (tersisa {$produk->stok}).");
                    }

                    $subtotal = $jumlah * $harga;
                    $penjualan->detail()->create([
                        'id_produk'    => $produk->id_produk,
                        'jumlah'       => $jumlah,
                        'harga_satuan' => $harga,
                        'subtotal'     => $subtotal,
                    ]);

                    $produk->decrement('stok', $jumlah);
                    $total += $subtotal;
                }

                // 7. Update total harga
                $penjualan->update(['total_harga' => $total]);

                return $penjualan;
            });

            // Sukses: Kembalikan data yang diupdate
            $penjualan->load(['pelanggan', 'user', 'detail.produk']);
            return response()->json([
                'message' => 'Data penjualan berhasil diperbarui',
                'data' => new PenjualanResource($penjualan)
            ], 200); // 200 OK

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Data penjualan tidak ditemukan.'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui: ' . $e->getMessage()], 422);
        }
    }

    /**
     * Menghapus transaksi penjualan.
     */
    public function destroy(string $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $penjualan = Penjualan::with('detail')->lockForUpdate()->findOrFail($id);

                // Kembalikan stok
                foreach ($penjualan->detail as $detail) {
                    $produk = Produk::lockForUpdate()->find($detail->id_produk);
                    if ($produk) {
                        $produk->increment('stok', $detail->jumlah);
                    }
                }

                // Hapus detail
                $penjualan->detail()->delete();
                // Hapus header
                $penjualan->delete();
            });

            // Beri respons 'No Content' yang berarti sukses
            return response()->json([
                'message' => 'Data penjualan berhasil dihapus'
            ], 200); // atau 204 No Content

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Data penjualan tidak ditemukan.'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }
}