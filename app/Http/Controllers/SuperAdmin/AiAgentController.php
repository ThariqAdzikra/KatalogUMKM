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

        // Definisi Tools/Functions untuk AI
        $tools = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_sales_analytics',
                    'description' => 'Mendapatkan data penjualan atau omset berdasarkan periode tertentu.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'period' => [
                                'type' => 'string',
                                'enum' => ['today', 'yesterday', 'this_week', 'last_week', 'this_month'],
                                'description' => 'Periode waktu yang ditanyakan.'
                            ],
                            'metric' => [
                                'type' => 'string',
                                'enum' => ['revenue', 'count', 'best_seller'],
                                'description' => 'Metrik yang ingin diketahui: revenue (omset), count (jumlah transaksi), atau best_seller (produk terlaris).'
                            ]
                        ],
                        'required' => ['period', 'metric']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_purchase_draft',
                    'description' => 'Membuat draft pembelian (restock) untuk produk tertentu.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'product_name' => [
                                'type' => 'string',
                                'description' => 'Nama produk yang ingin direstock.'
                            ],
                            'quantity' => [
                                'type' => 'integer',
                                'description' => 'Jumlah barang yang ingin dibeli (default: 10).',
                                'default' => 10
                            ]
                        ],
                        'required' => ['product_name']
                    ]
                ]
            ]
        ];

        try {
            // 1. Kirim pesan user ke Kolosal AI (Claude)
            $response = Http::withToken($apiKey)->withoutVerifying()->timeout(30)->post('https://api.kolosal.ai/v1/chat/completions', [
                'model' => 'Claude Sonnet 4.5',
                'messages' => [
                    ['role' => 'system', 'content' => 'Anda adalah Asisten Manajer Toko yang cerdas. Jawablah dengan singkat, padat, dan profesional dalam Bahasa Indonesia. Jika user meminta tindakan, gunakan function yang tersedia.'],
                    ['role' => 'user', 'content' => $userMessage]
                ],
                'tools' => $tools,
                'tool_choice' => 'auto',
            ]);

            $responseData = $response->json();
            $message = $responseData['choices'][0]['message'];

            // 2. Cek apakah AI ingin memanggil function
            if (isset($message['tool_calls'])) {
                $toolCall = $message['tool_calls'][0];
                $functionName = $toolCall['function']['name'];
                $arguments = json_decode($toolCall['function']['arguments'], true);

                $functionResult = null;

                // Eksekusi Function Lokal
                if ($functionName === 'get_sales_analytics') {
                    $functionResult = $this->getSalesAnalytics($arguments['period'], $arguments['metric']);
                } elseif ($functionName === 'create_purchase_draft') {
                    $qty = $arguments['quantity'] ?? 10;
                    $functionResult = $this->createPurchaseDraft($arguments['product_name'], $qty);
                }

                // 3. Kirim hasil function kembali ke Kolosal AI untuk diformat jadi kalimat
                $secondResponse = Http::withToken($apiKey)->withoutVerifying()->timeout(30)->post('https://api.kolosal.ai/v1/chat/completions', [
                    'model' => 'Claude Sonnet 4.5',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Anda adalah Asisten Manajer Toko.'],
                        ['role' => 'user', 'content' => $userMessage],
                        $message, // Pesan assistant sebelumnya (tool_call)
                        [
                            'role' => 'tool',
                            'tool_call_id' => $toolCall['id'],
                            'content' => json_encode($functionResult)
                        ]
                    ]
                ]);

                return response()->json(['reply' => $secondResponse->json('choices.0.message.content')]);
            }

            // Jika tidak ada function call, kembalikan balasan biasa
            return response()->json(['reply' => $message['content']]);

        } catch (\Exception $e) {
            return response()->json(['reply' => 'Maaf, terjadi kesalahan pada sistem AI: ' . $e->getMessage()]);
        }
    }

    // --- IMPLEMENTASI LOGIKA BISNIS ---

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
