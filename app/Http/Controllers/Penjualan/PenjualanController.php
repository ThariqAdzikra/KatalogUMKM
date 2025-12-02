<?php

namespace App\Http\Controllers\Penjualan;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Pelanggan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDF; 

class PenjualanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $metode = $request->input('metode');
        $sort   = $request->input('sort', 'tanggal');

        $query = Penjualan::with('pelanggan');

        if ($search) {
            $query->whereHas('pelanggan', function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%');
            });
        }

        if ($metode) {
            $query->where('metode_pembayaran', $metode);
        }

        switch ($sort) {
            case 'total':
                $query->orderBy('total_harga', 'desc');
                break;
            case 'nama':
                $query->join('pelanggan', 'pelanggan.id_pelanggan', '=', 'penjualan.id_pelanggan')
                      ->orderBy('pelanggan.nama', 'asc')
                      ->select('penjualan.*');
                break;
            default:
                $query->orderBy('tanggal_penjualan', 'desc');
        }

        $penjualan = $query->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('penjualan.partials.table_wrapper', compact('penjualan'))->render(),
            ]);
        }

        return view('penjualan.index', compact('penjualan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pelanggan = Pelanggan::all();
        $produk    = Produk::where('stok', '>', 0)->orderBy('nama_produk')->get();
        return view('penjualan.create', compact('pelanggan', 'produk'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi
        $validated = $request->validate([
            'id_pelanggan'        => 'nullable|string',
            'nama_pelanggan_baru' => 'nullable|string|max:100',
            'no_hp_baru'          => 'nullable|string|max:20',
            'email_baru'          => 'nullable|email|max:100',
            'alamat_baru'         => 'nullable|string|max:255',
            'tanggal_penjualan'   => 'required|date',
            'metode_pembayaran'   => 'required|in:cash,transfer,qris',
            'produk'              => 'required|array|min:1',
            'produk.*'            => 'required|exists:produk,id_produk',
            'jumlah'              => 'required|array|min:1',
            'jumlah.*'            => 'required|integer|min:1',
            'harga_satuan'        => 'required|array|min:1',
            'harga_satuan.*'      => 'required|numeric|min:0',
        ]);

        // Transaksi atomic
        try {
            $penjualan = DB::transaction(function () use ($request) {

                $idPelanggan = null;
                $isNew = str_starts_with($request->id_pelanggan ?? '', 'NEW_');

                if (!$isNew && $request->filled('id_pelanggan') && is_numeric($request->id_pelanggan)) {
                    $idPelanggan = (int) $request->id_pelanggan;
                } else {
                    $pelangganBaru = Pelanggan::create([
                        'nama'   => $request->nama_pelanggan_baru,
                        'no_hp'  => $request->no_hp_baru,
                        'email'  => $request->email_baru,
                        'alamat' => $request->alamat_baru,
                    ]);
                    $idPelanggan = $pelangganBaru->id_pelanggan;
                }

                $penjualanHeader = Penjualan::create([
                    'id_user'           => Auth::id(),
                    'id_pelanggan'      => $idPelanggan,
                    'tanggal_penjualan' => $request->tanggal_penjualan,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'total_harga'       => 0, 
                ]);

                $total = 0;
                foreach ($request->produk as $i => $id_produk) {
                    $produk = Produk::lockForUpdate()->findOrFail($id_produk);
                    $jumlah = (int) $request->jumlah[$i];
                    $harga = (float) $request->harga_satuan[$i];

                    if ($jumlah > $produk->stok) {
                        throw new \Exception("Stok {$produk->nama_produk} tidak mencukupi (tersisa {$produk->stok}).");
                    }

                    PenjualanDetail::create([
                        'id_penjualan' => $penjualanHeader->id_penjualan,
                        'id_produk'    => $produk->id_produk,
                        'jumlah'       => $jumlah,
                        'harga_satuan' => $harga,
                        'subtotal'     => $jumlah * $harga,
                    ]);

                    $produk->decrement('stok', $jumlah);
                    $total += $jumlah * $harga;
                }

                $penjualanHeader->update(['total_harga' => $total]);

                return $penjualanHeader;
            });

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan.',
                'id_penjualan' => $penjualan->id_penjualan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422); 
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $penjualan = Penjualan::with(['pelanggan', 'user', 'detail.produk'])->findOrFail($id);
        return view('penjualan.show', compact('penjualan'));
    }

    public function print()
    {
        $penjualan    = Penjualan::with('pelanggan')->get();
        $total_semua  = $penjualan->sum('total_harga');
        return view('penjualan.print', compact('penjualan', 'total_semua'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $penjualan = Penjualan::with('detail')->findOrFail($id);
        $pelanggan = Pelanggan::all();
        $produk    = Produk::all();
        return view('penjualan.edit', compact('penjualan', 'pelanggan', 'produk'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $penjualan = Penjualan::with('detail')->lockForUpdate()->findOrFail($id);

            foreach ($penjualan->detail as $detail) {
                $produk = Produk::lockForUpdate()->find($detail->id_produk);
                if ($produk) {
                    $produk->increment('stok', $detail->jumlah);
                }
            }

            $penjualan->detail()->delete();

            // Tentukan pelanggan (lama/baru)
            $isNewFromTags = $request->filled('id_pelanggan') && !is_numeric($request->id_pelanggan);
            if ($request->filled('id_pelanggan') && !$isNewFromTags) {
                $idPelanggan = (int) $request->id_pelanggan;
            } else {
                $pelangganBaru = Pelanggan::create([
                    'nama'   => $request->nama_pelanggan_baru,
                    'no_hp'  => $request->no_hp_baru,
                    'email'  => $request->email_baru,
                    'alamat' => $request->alamat_baru,
                ]);
                $idPelanggan = $pelangganBaru->id_pelanggan;
            }

            $penjualan->update([
                'id_pelanggan'      => $idPelanggan,
                'tanggal_penjualan' => $request->tanggal_penjualan,
                'metode_pembayaran' => $request->metode_pembayaran,
            ]);

            // Buat detail baru + cek stok
            $total = 0;
            foreach ($request->produk as $i => $id_produk) {
                $idProduk = (int) $id_produk;
                $jumlah   = (int) ($request->jumlah[$i] ?? 0);
                $harga    = (float) ($request->harga_satuan[$i] ?? 0);

                $produk = Produk::lockForUpdate()->findOrFail($idProduk);

                if ($jumlah > $produk->stok) {
                    abort(422, "Stok untuk {$produk->nama_produk} tidak mencukupi. Tersedia: {$produk->stok}, diminta: {$jumlah}.");
                }

                $subtotal = $jumlah * $harga;

                $penjualan->detail()->create([
                    'id_produk'    => $idProduk,
                    'jumlah'       => $jumlah,
                    'harga_satuan' => $harga,
                    'subtotal'     => $subtotal,
                ]);

                $produk->decrement('stok', $jumlah);

                $total += $subtotal;
            }

            $penjualan->update(['total_harga' => $total]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data penjualan berhasil diperbarui',
                    'id_penjualan' => $penjualan->id_penjualan,
                ]);
            }

            return redirect()
                ->route('penjualan.index')
                ->with('success', 'Data penjualan berhasil diperbarui');
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        return DB::transaction(function () use ($id, $request) {
            $penjualan = Penjualan::with('detail')->lockForUpdate()->findOrFail($id);

            foreach ($penjualan->detail as $detail) {
                $produk = Produk::lockForUpdate()->find($detail->id_produk);
                if ($produk) {
                    $produk->increment('stok', $detail->jumlah);
                }
            }

            $penjualan->detail()->delete();
            $penjualan->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data penjualan berhasil dihapus'
                ]);
            }

            return redirect()->route('penjualan.index')->with('success', 'Data penjualan berhasil dihapus');
        });
    }

    public function laporan(Request $request)
    {
        $query = Penjualan::with('pelanggan');

        if ($request->filled('dari_tanggal') && $request->filled('sampai_tanggal')) {
            $query->whereBetween('tanggal_penjualan', [$request->dari_tanggal, $request->sampai_tanggal]);
        }

        if ($request->filled('metode_pembayaran')) {
            $query->where('metode_pembayaran', $request->metode_pembayaran);
        }

        $penjualan   = $query->orderBy('tanggal_penjualan', 'desc')->get();
        $total_semua = $penjualan->sum('total_harga');

        return view('penjualan.laporan', compact('penjualan', 'total_semua'));
    }

    // Method untuk generate struk PDF
    public function generateStrukPdf($id_penjualan)
    {
        $penjualan = Penjualan::with(['pelanggan', 'detail.produk'])->findOrFail($id_penjualan);
        $company = [
            'name' => 'LaptopPremium',
            'address' => 'Jl. Digital No. 1, Pekanbaru',
            'phone' => '0812-3456-7890',
            'email' => 'kasir@laptoppremium.com',
        ];

        $pdf = PDF::loadView('penjualan.struk_pdf', compact('penjualan', 'company'));
        
        $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait'); 
        
        // Tampilkan PDF di browser
        return $pdf->stream('struk_penjualan_' . $penjualan->id_penjualan . '.pdf');
    }
}