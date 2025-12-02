<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request; 
use Carbon\Carbon;            

class DashboardController extends Controller
{
    public function index()
    {
        $totalProduk = DB::table('produk')->count();
        $totalPelanggan = DB::table('pelanggan')->count();
        $totalPenjualan = DB::table('penjualan')->count();
        $totalPembelian = DB::table('pembelian')->count();

        $pendapatanMingguan = DB::table('penjualan')
            ->whereBetween('tanggal_penjualan', [now()->subDays(7), now()])
            ->sum('total_harga');


        // Transaksi Hari Ini
        $transaksiHariIni = DB::table('penjualan')
            ->whereDate('tanggal_penjualan', now())
            ->count();

        // Produk Terlaris
        $terlaris = DB::table('penjualan_detail')
            ->join('produk', 'penjualan_detail.id_produk', '=', 'produk.id_produk')
            ->select('produk.nama_produk', DB::raw('SUM(penjualan_detail.jumlah) as total_terjual'))
            ->groupBy('produk.nama_produk')
            ->orderByDesc('total_terjual')
            ->first();
        
        $produkTerlaris = $terlaris ? $terlaris->nama_produk : '-';

        // Stok Menipis
        $stokMenipis = DB::table('produk')
            ->where('stok', '<=', 5)
            ->count();
            
        // User Aktif
        $userAktif = DB::table('users')->count();
        
        // Mengirim Semua Data ke View 
        return view('superadmin.dashboard', compact(
            'totalProduk',
            'totalPelanggan',
            'totalPenjualan',
            'totalPembelian',
            'pendapatanMingguan',
            'transaksiHariIni', 
            'produkTerlaris',  
            'stokMenipis',      
            'userAktif'         
        ));
    }

    /**
     * [BARU] Mengambil data chart untuk permintaan AJAX.
     */
    public function getChartData(Request $request)
    {
        // Validasi input
        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after_or_equal:start',
        ]);

        // Konversi tanggal menggunakan Carbon
        $startDate = Carbon::parse($request->input('start'))->startOfDay();
        $endDate = Carbon::parse($request->input('end'))->endOfDay();

        // Query data berdasarkan rentang tanggal (menggunakan DB::table)
        $grafik = DB::table('penjualan')
            ->select(
                DB::raw('DATE(tanggal_penjualan) as tanggal'),
                DB::raw('SUM(total_harga) as total') 
            )
            ->whereBetween('tanggal_penjualan', [$startDate, $endDate])
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        // Siapkan data untuk Chart.js (mengisi hari kosong)
        $labels = [];
        $values = [];
        $currentDate = $startDate->clone();
        $dataMap = $grafik->pluck('total', 'tanggal');

        while ($currentDate <= $endDate) {
            $dateString = $currentDate->format('Y-m-d');
            $labels[] = $dateString;
            $values[] = $dataMap[$dateString] ?? 0;
            $currentDate->addDay();
        }

        // Kembalikan sebagai JSON
        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }
}