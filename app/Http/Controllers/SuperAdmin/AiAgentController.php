<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AiAgentController extends Controller
{
    public function chat(Request $request)
    {
        $userMessage = $request->input('message');
        $apiKey = env('KOLOSAL_API_KEY') ?: env('OPENAI_API_KEY');

        if (!$apiKey) {
            return response()->json(['reply' => 'Maaf, API Key belum dikonfigurasi. Silakan cek file .env Anda.']);
        }

        // Cek intent user (deteksi manual tanpa function calling)
        $detectedAction = $this->detectUserIntent($userMessage);

        if ($detectedAction['type'] === 'query_database') {
            // Langsung execute query dan berikan data ke AI
            $data = $this->fetchRelevantData($userMessage);
            
            // DEBUG: Log data yang akan dikirim ke AI
            \Log::info('Data sent to AI:', ['data' => $data, 'user_message' => $userMessage]);
            
            $systemPrompt = "Anda adalah Asisten Manajer Toko LAPTOP. PENTING: Anda HANYA boleh menjawab berdasarkan data yang diberikan di bawah ini. JANGAN mengarang atau menambahkan informasi yang tidak ada dalam data.\n\nData dari database:\n\n" . $data . "\n\nJika data kosong atau tidak relevan dengan pertanyaan, katakan 'Maaf, data tidak tersedia.' JANGAN mengarang data produk.";
            
            try {
                $response = Http::withToken($apiKey)->withoutVerifying()->timeout(30)->post('https://api.kolosal.ai/v1/chat/completions', [
                    'model' => 'Claude Sonnet 4.5',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage]
                    ],
                ]);

                return response()->json(['reply' => $response->json('choices.0.message.content')]);
            } catch (\Exception $e) {
                return response()->json(['reply' => 'Maaf, terjadi kesalahan: ' . $e->getMessage()]);
            }
        }

        // Untuk chat biasa tanpa query database
        try {
            $response = Http::withToken($apiKey)->withoutVerifying()->timeout(30)->post('https://api.kolosal.ai/v1/chat/completions', [
                'model' => 'Claude Sonnet 4.5',
                'messages' => [
                    ['role' => 'system', 'content' => 'Anda adalah Asisten Manajer Toko yang ramah. Jawab singkat dalam Bahasa Indonesia.'],
                    ['role' => 'user', 'content' => $userMessage]
                ],
            ]);

            return response()->json(['reply' => $response->json('choices.0.message.content')]);
        } catch (\Exception $e) {
            return response()->json(['reply' => 'Maaf, terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    private function detectUserIntent($message)
    {
        $keywords = ['omset', 'penjualan', 'transaksi', 'produk terlaris', 'best seller', 'bulan', 'hari', 'minggu', 'stok', 'stock', 'barang', 'sisa', 'tersedia', 'hampir habis', 'menipis'];
        
        foreach ($keywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                return ['type' => 'query_database'];
            }
        }

        return ['type' => 'general_chat'];
    }

    private function fetchRelevantData($message)
    {
        $now = Carbon::now();
        $data = [];

        // --- 1. DATA PENJUALAN ---
        // Omset hari ini
        $todayRevenue = DB::table('penjualan')->whereDate('tanggal_penjualan', $now->today())->sum('total_harga');
        $data[] = "📊 Omset Hari Ini: Rp " . number_format($todayRevenue, 0, ',', '.');

        // Omset bulan ini
        $monthRevenue = DB::table('penjualan')
            ->whereMonth('tanggal_penjualan', $now->month)
            ->whereYear('tanggal_penjualan', $now->year)
            ->sum('total_harga');
        $data[] = "📊 Omset Bulan Ini: Rp " . number_format($monthRevenue, 0, ',', '.');

        // Omset bulan lalu
        $lastMonth = $now->copy()->subMonth();
        $lastMonthRevenue = DB::table('penjualan')
            ->whereMonth('tanggal_penjualan', $lastMonth->month)
            ->whereYear('tanggal_penjualan', $lastMonth->year)
            ->sum('total_harga');
        $data[] = "📊 Omset Bulan Lalu: Rp " . number_format($lastMonthRevenue, 0, ',', '.');

        // Produk terlaris bulan ini
        $bestSeller = DB::table('penjualan_detail')
            ->join('penjualan', 'penjualan_detail.id_penjualan', '=', 'penjualan.id_penjualan')
            ->join('produk', 'penjualan_detail.id_produk', '=', 'produk.id_produk')
            ->select('produk.nama_produk', DB::raw('SUM(penjualan_detail.jumlah) as total'))
            ->whereMonth('penjualan.tanggal_penjualan', $now->month)
            ->groupBy('produk.nama_produk')
            ->orderByDesc('total')
            ->first();

        if ($bestSeller) {
            $data[] = "🏆 Produk Terlaris Bulan Ini: {$bestSeller->nama_produk} ({$bestSeller->total} unit terjual)";
        }

        // --- 2. DATA STOK ---
        $stockKeywords = ['stok', 'stock', 'barang', 'sisa', 'tersedia', 'hampir habis', 'menipis'];
        $isStockQuery = false;
        foreach ($stockKeywords as $kw) {
            if (stripos($message, $kw) !== false) {
                $isStockQuery = true;
                break;
            }
        }

        if ($isStockQuery) {
            // 1. Bersihkan kata-kata umum (stop words) untuk mendapatkan keyword produk
            $stopWords = ['cek', 'stok', 'stock', 'berapa', 'sisa', 'tersedia', 'barang', 'apakah', 'ada', 'di', 'gudang', 'toko', 'ini', 'itu', 'yang', 'mau', 'habis', 'ketersediaan', 'jumlah', 'hampir'];
            $cleanMessage = str_ireplace($stopWords, '', $message);
            $keyword = trim(preg_replace('/\s+/', ' ', $cleanMessage)); // Hapus spasi berlebih

            // 2. Jika keyword terlalu pendek (misal cuma tanda tanya), anggap user tidak menyebut produk spesifik
            if (strlen($keyword) < 3) {
                // Tampilkan stok menipis (General Info)
                $lowStock = DB::table('produk')
                    ->select('nama_produk', 'stok')
                    ->where('stok', '<', 10)
                    ->orderBy('stok', 'asc')
                    ->limit(5)
                    ->get();
                
                if ($lowStock->count() > 0) {
                    $list = $lowStock->map(fn($item) => "- {$item->nama_produk} (Sisa: {$item->stok} unit)")->implode("\n");
                    $data[] = "⚠️ Peringatan Stok Menipis (<10):\n" . $list;
                } else {
                    $data[] = "✅ Semua stok produk aman (di atas 10 unit).";
                }
            } else {
                // 3. Cari produk yang MENGANDUNG keyword tersebut (LIKE search)
                $products = DB::table('produk')
                    ->select('nama_produk', 'stok')
                    ->where('nama_produk', 'like', "%{$keyword}%")
                    ->limit(5) // Batasi 5 hasil agar tidak membanjiri prompt
                    ->get();

                if ($products->count() > 0) {
                    $list = $products->map(fn($item) => "- {$item->nama_produk}: {$item->stok} unit")->implode("\n");
                    $data[] = "🔍 Hasil pencarian untuk '{$keyword}':\n" . $list;
                } else {
                    $data[] = "❌ Tidak ditemukan produk dengan kata kunci '{$keyword}'. Coba kata kunci lain.";
                }
            }
        }

        return implode("\n\n", $data);
    }

    // --- IMPLEMENTASI LOGIKA BISNIS untuk restock (optional, jika diperlukan) ---

    private function getSalesAnalytics($period, $metric)
    {
        $query = DB::table('penjualan');
        $now = Carbon::now();

        switch ($period) {
            case 'today':
                $query->whereDate('tanggal_penjualan', $now->today());
                break;
            case 'yesterday':
                $query->whereDate('tanggal_penjualan', $now->yesterday());
                break;
            case 'this_week':
                $query->whereBetween('tanggal_penjualan', [$now->startOfWeek(), $now->endOfWeek()]);
                break;
            case 'last_week':
                $query->whereBetween('tanggal_penjualan', [$now->subWeek()->startOfWeek(), $now->subWeek()->endOfWeek()]);
                break;
            case 'this_month':
                $query->whereMonth('tanggal_penjualan', $now->month)->whereYear('tanggal_penjualan', $now->year);
                break;
        }

        if ($metric === 'revenue') {
            $total = $query->sum('total_harga');
            return "Total omset untuk periode $period adalah Rp " . number_format($total, 0, ',', '.');
        } elseif ($metric === 'count') {
            $count = $query->count();
            return "Ada $count transaksi penjualan pada periode $period.";
        } elseif ($metric === 'best_seller') {
            // Logika best seller agak beda, butuh join
            $best = DB::table('penjualan_detail')
                ->join('penjualan', 'penjualan_detail.id_penjualan', '=', 'penjualan.id_penjualan')
                ->join('produk', 'penjualan_detail.id_produk', '=', 'produk.id_produk')
                ->select('produk.nama_produk', DB::raw('SUM(penjualan_detail.jumlah) as total'))
                ->whereBetween('penjualan.tanggal_penjualan', [
                    ($period == 'today' ? $now->today() : $now->startOfMonth()), 
                    $now->endOfDay()
                ]) // Simplifikasi range waktu untuk best seller
                ->groupBy('produk.nama_produk')
                ->orderByDesc('total')
                ->first();
            
            if ($best) {
                return "Produk terlaris adalah {$best->nama_produk} dengan total terjual {$best->total} unit.";
            }
            return "Belum ada data penjualan untuk menentukan produk terlaris.";
        }

        return "Data tidak ditemukan.";
    }

    private function createPurchaseDraft($productName, $quantity)
    {
        // 1. Cari Produk
        $product = DB::table('produk')->where('nama_produk', 'like', "%$productName%")->first();
        if (!$product) {
            return "Gagal: Produk dengan nama '$productName' tidak ditemukan.";
        }

        // 2. Cari Supplier (Ambil sembarang supplier dulu untuk demo, idealnya dari relasi)
        $supplier = DB::table('supplier')->first();
        if (!$supplier) {
            return "Gagal: Belum ada data supplier di sistem.";
        }

        // 3. Buat Header Pembelian (Status: Pending/Draft)
        $pembelianId = DB::table('pembelian')->insertGetId([
            'id_supplier' => $supplier->id_supplier,
            'tanggal_pembelian' => now(),
            'total_harga' => 0, // Akan diupdate nanti
            'status' => 'Pending', // Asumsi ada kolom status, atau kita anggap pending
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Buat Detail Pembelian
        // Asumsi harga beli belum ada di tabel produk, kita set 0 atau ambil dari history
        // Untuk simplifikasi, kita set harga 0 dulu, user harus edit nanti.
        DB::table('pembelian_detail')->insert([
            'id_pembelian' => $pembelianId,
            'id_produk' => $product->id_produk,
            'jumlah' => $quantity,
            'harga_satuan' => 0, 
            'total_harga' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return "Berhasil! Draft pembelian untuk {$quantity} unit {$product->nama_produk} telah dibuat. Silakan cek menu Pembelian untuk konfirmasi harga dan proses lebih lanjut.";
    }
}
