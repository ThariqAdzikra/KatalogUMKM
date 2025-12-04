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
    /**
     * [BARU] Mengambil data forecasting menggunakan AI (OpenAI).
     */
    public function getForecastingData()
    {
        // 1. Siapkan Data Penjualan untuk AI
        $threeMonthsAgo = now()->subMonths(3)->startOfMonth();
        
        $salesData = DB::table('penjualan_detail')
            ->join('penjualan', 'penjualan_detail.id_penjualan', '=', 'penjualan.id_penjualan')
            ->join('produk', 'penjualan_detail.id_produk', '=', 'produk.id_produk')
            ->select(
                'produk.nama_produk',
                'produk.stok',
                DB::raw('DATE_FORMAT(penjualan.tanggal_penjualan, "%Y-%m") as bulan'),
                DB::raw('SUM(penjualan_detail.jumlah) as total_qty')
            )
            ->where('penjualan.tanggal_penjualan', '>=', $threeMonthsAgo)
            ->groupBy('produk.nama_produk', 'produk.stok', 'bulan')
            ->orderBy('produk.nama_produk')
            ->orderBy('bulan')
            ->get();

        // Format data agar mudah dibaca AI
        $dataSummary = [];
        foreach ($salesData as $data) {
            $dataSummary[$data->nama_produk]['stok'] = $data->stok;
            $dataSummary[$data->nama_produk]['history'][] = "$data->bulan: $data->total_qty terjual";
        }

        // Batasi hanya 20 produk teratas yang ada penjualannya untuk keseimbangan performance & data
        $dataSummary = array_slice($dataSummary, 0, 20);

        if (empty($dataSummary)) {
            return response()->json(['error' => 'Belum ada data penjualan yang cukup untuk analisis AI.']);
        }

        $prompt = "Analisis data penjualan berikut dan berikan prediksi penjualan bulan depan serta rekomendasi restock.\n\n";
        foreach ($dataSummary as $produk => $info) {
            $history = implode(", ", $info['history']);
            $prompt .= "- Produk: $produk | Stok Saat Ini: {$info['stok']} | Riwayat: $history\n";
        }
        $prompt .= "\nOutputkan HANYA JSON valid dengan format: [{'nama_produk': '...', 'prediksi': 10, 'saran': '...', 'status': 'aman/warning/danger'}]";

        // 2. Panggil API Kolosal (Claude)
        $apiKey = env('KOLOSAL_API_KEY') ?: env('OPENAI_API_KEY');
        
        if (!$apiKey) {
            // Fallback jika API Key tidak ada (Simulasi AI)
            return $this->getSimulationData($dataSummary);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withToken($apiKey)->withoutVerifying()->timeout(30)->post('https://api.kolosal.ai/v1/chat/completions', [
                'model' => 'Claude Sonnet 4.5',
                'messages' => [
                    ['role' => 'system', 'content' => 'Anda adalah asisten manajemen stok ahli.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
            ]);

            $content = $response->json('choices.0.message.content');
            
            // Bersihkan markdown code block jika ada
            $content = str_replace(['```json', '```'], '', $content);
            $forecasts = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Format JSON dari AI tidak valid.');
            }

            return response()->json($forecasts);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menghubungi AI: ' . $e->getMessage()], 500);
        }
    }

    private function getSimulationData($dataSummary)
    {
        // Simulasi logika sederhana jika API Key belum diset
        $results = [];
        foreach ($dataSummary as $name => $info) {
            // Ambil rata-rata kasar dari string history
            $total = 0;
            $count = 0;
            foreach ($info['history'] as $h) {
                if (preg_match('/(\d+) terjual/', $h, $matches)) {
                    $total += (int)$matches[1];
                    $count++;
                }
            }
            $avg = $count > 0 ? ceil($total / $count) : 0;
            
            $status = 'aman';
            if ($info['stok'] < $avg) $status = 'danger';
            elseif ($info['stok'] < $avg * 1.5) $status = 'warning';

            $results[] = [
                'nama_produk' => $name,
                'prediksi' => $avg,
                'saran' => $status == 'danger' ? 'Stok kritis! Segera restock.' : 'Stok aman.',
                'status' => $status
            ];
        }
        return response()->json($results);
    }
}