<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query('query', '');

        $pelangganQuery = Pelanggan::when($query, function ($q) use ($query) {
                $q->where('nama', 'like', "%$query%")
                  ->orWhere('email', 'like', "%$query%")
                  ->orWhere('no_hp', 'like', "%$query%");
            })
            ->orderBy('nama');

        $pelanggan = $pelangganQuery->paginate(10)->appends(['query' => $query]);

        return view('pelanggan.index', compact('pelanggan'));
    }

    public function show($id)
    {
        $pelanggan = Pelanggan::with(['penjualan.detail.produk'])
            ->findOrFail($id);

        $riwayat = $pelanggan->penjualan->flatMap(function ($pj) {
            return $pj->detail->map(function ($d) use ($pj) {
                $produk = $d->produk;
                $garansi = (int) ($d->garansi ?? $produk->garansi ?? 0);

                $rawTanggal = $pj->tanggal_penjualan ?? null;
                $tanggal = $rawTanggal ? Carbon::parse($rawTanggal) : null;

                $tanggalAkhir = null;
                $sisaHari = null;
                
                if ($tanggal && $garansi > 0) {
                    $tanggalAkhir = $tanggal->copy()->addMonths($garansi);
                    $sisaHari = now()->diffInDays($tanggalAkhir, false); 
                }

                return [
                    'nama_produk'   => $produk->nama_produk ?? '-',
                    'jumlah'        => (int) $d->jumlah,
                    'subtotal'      => (int) ($d->subtotal ?? 0),
                    'tanggal'       => $tanggal?->format('Y-m-d') ?? '-',
                    'garansi'       => $garansi, 
                    'tanggal_akhir' => $tanggalAkhir ? $tanggalAkhir->format('Y-m-d') : null,
                    'sisa_hari'     => $sisaHari,
                ];
            });
        })->sortByDesc('tanggal')->values(); 

        return view('pelanggan.show', compact('pelanggan', 'riwayat'));
    }

    public function edit($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        return view('pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'alamat' => 'required|string|max:150',
            'email' => 'required|email|max:100',
            'no_hp' => [
                'required',
                'regex:/^08[0-9]{8,11}$/',
                'min:10',
                'max:13'
            ],
        ]);

        $pelanggan = Pelanggan::findOrFail($id);

        $pelanggan->update([
            'nama' => $request->nama,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'alamat' => $request->alamat,
        ]);

        return redirect()->route('pelanggan.show', $pelanggan->id_pelanggan)
                         ->with('success', 'Data pelanggan berhasil diperbarui!');
    }


    public function destroy($id)
    {
        try {
            $pelanggan = Pelanggan::findOrFail($id);
            $pelanggan->delete();
            return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('pelanggan.index')->withErrors(['error' => 'Gagal menghapus pelanggan. Mungkin masih memiliki data transaksi.']);
        }
    }

    public function searchAjax(Request $request)
    {
        $query = $request->query('query', '');
        $pelanggan = \App\Models\Pelanggan::query()
            ->when($query, function ($q) use ($query) {
                $q->where('nama', 'like', "%$query%")
                  ->orWhere('email', 'like', "%$query%")
                  ->orWhere('no_hp', 'like', "%$query%");
            })
            ->orderBy('nama')
            ->take(20)
            ->get(['id_pelanggan', 'nama', 'no_hp', 'email', 'alamat']);

        return response()->json($pelanggan);
    }
}