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

        // Removed strict business guard - let AI be more natural

        // Cek intent user (deteksi manual tanpa function calling)
        $detectedAction = $this->detectUserIntent($userMessage);

        if ($detectedAction['type'] === 'query_database') {
            // Langsung execute query dan berikan data ke AI
            $data = $this->fetchRelevantData($userMessage);
            
            // DEBUG: Log data yang akan dikirim ke AI
            \Log::info('Data sent to AI:', ['data' => $data, 'user_message' => $userMessage]);
            
            $systemPrompt = "Anda adalah Asisten Manajer Toko LAPTOP yang friendly dan helpful.

Data terkini dari database:

" . $data . "

Gunakan data di atas untuk menjawab pertanyaan user. Format jawaban dengan rapi menggunakan:
- Numbering untuk list
- Bullet points untuk item
- Emoji yang sesuai
- Spacing yang baik

Jawab dalam Bahasa Indonesia yang ramah dan profesional.";
            
            try {
                $response = Http::withToken($apiKey)->withoutVerifying()->timeout(30)->post('https://api.kolosal.ai/v1/chat/completions', [
                    'model' => 'Claude Sonnet 4.5',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage]
                    ],
                ]);

                // DEBUG: Log full API response
                \Log::info('Kolosal API Response:', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);

                $aiReply = $response->json('choices.0.message.content');
                
                // Jika null, coba alternatif path
                if (!$aiReply) {
                    // Coba struktur alternatif
                    $aiReply = $response->json('message.content') 
                            ?? $response->json('content')
                            ?? $response->json('response')
                            ?? 'Maaf, AI tidak memberikan respons yang valid.';
                    
                    \Log::warning('AI response was null, used fallback', ['reply' => $aiReply]);
                }

                // Keep markdown untuk formatting yang rapi
                return response()->json(['reply' => $aiReply]);
            } catch (\Exception $e) {
                \Log::error('AI Chat Error:', ['error' => $e->getMessage()]);
                return response()->json(['reply' => 'Maaf, terjadi kesalahan: ' . $e->getMessage()]);
            }
        }

        // Untuk chat biasa tanpa query database
        try {
            $response = Http::withToken($apiKey)->withoutVerifying()->timeout(30)->post('https://api.kolosal.ai/v1/chat/completions', [
                'model' => 'Claude Sonnet 4.5',
                'messages' => [
                    ['role' => 'system', 'content' => 'Anda adalah Asisten Manajer Toko LAPTOP yang ramah dan helpful. Jawab pertanyaan user dengan natural dan informatif. Gunakan format yang rapi dengan numbering dan bullet points. Bahasa Indonesia yang friendly tapi tetap profesional.'],
                    ['role' => 'user', 'content' => $userMessage]
                ],
            ]);

            // DEBUG: Log full API response
            \Log::info('Kolosal API Response (General):', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            $aiReply = $response->json('choices.0.message.content');
            
            // Jika null, coba alternatif path
            if (!$aiReply) {
                $aiReply = $response->json('message.content') 
                        ?? $response->json('content')
                        ?? $response->json('response')
                        ?? 'Maaf, AI tidak memberikan respons yang valid.';
                
                \Log::warning('AI response was null (General), used fallback', ['reply' => $aiReply]);
            }

            // Keep markdown untuk formatting yang rapi
            return response()->json(['reply' => $aiReply]);
        } catch (\Exception $e) {
            \Log::error('AI Chat Error (General):', ['error' => $e->getMessage()]);
            return response()->json(['reply' => 'Maaf, terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    private function detectUserIntent($message)
    {
        $keywords = [
            // Penjualan & Omset
            'omset', 'penjualan', 'transaksi', 'produk terlaris', 'best seller', 'bulan', 'hari', 'minggu',
            // Stok & Produk
            'stok', 'stock', 'barang', 'sisa', 'tersedia', 'hampir habis', 'menipis',
            // Pembelian
            'pembelian', 'beli', 'purchase', 'order',
            // Supplier
            'supplier', 'suplier', 'vendor', 'pemasok',
            // Pelanggan
            'pelanggan', 'customer', 'konsumen',
            // Pegawai
            'pegawai', 'karyawan', 'staff', 'user', 'admin',
            // General data request
            'data', 'info', 'informasi', 'laporan', 'semua', 'apa saja', 'punya'
        ];
        
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
            // Extract meaningful words from the message
            $words = preg_split('/\s+/', strtolower($message));
            $stopWords = ['cek', 'berapa', 'apakah', 'ada', 'di', 'ini', 'itu', 'yang', 'mau', 'ketersediaan', 'jumlah', 'nya'];
            
            // Filter out stop words and stock keywords
            $productKeywords = array_filter($words, function($word) use ($stopWords) {
                return !in_array($word, $stopWords) && 
                       !in_array($word, ['stok', 'stock', 'barang', 'sisa', 'tersedia', 'habis', 'menipis']) &&
                       strlen($word) > 2;
            });

            if (count($productKeywords) > 0) {
                // Search for products that match ANY of the keywords
                $products = DB::table('produk')
                    ->select('nama_produk', 'merk', 'stok', 'harga_jual')
                    ->where(function($query) use ($productKeywords) {
                        foreach ($productKeywords as $keyword) {
                            $query->orWhere('nama_produk', 'like', "%{$keyword}%")
                                  ->orWhere('merk', 'like', "%{$keyword}%");
                        }
                    })
                    ->limit(10)
                    ->get();

                if ($products->count() > 0) {
                    $list = $products->map(function($item) {
                        $merk = $item->merk ? " ({$item->merk})" : "";
                        $harga = number_format($item->harga_jual, 0, ',', '.');
                        $status = $item->stok < 5 ? "⚠️" : ($item->stok < 10 ? "📦" : "✅");
                        return "• {$item->nama_produk}{$merk}\n  {$status} Stok: {$item->stok} unit | 💰 Rp {$harga}";
                    })->implode("\n\n");
                    $data[] = "📦 Informasi Produk:\n" . $list;
                } else {
                    $data[] = "❌ Produk tidak ditemukan. Coba kata kunci lain.";
                }
            } else {
                // No specific product mentioned, show low stock items
                $lowStock = DB::table('produk')
                    ->select('nama_produk', 'merk', 'stok', 'harga_jual')
                    ->where('stok', '<', 10)
                    ->orderBy('stok', 'asc')
                    ->limit(10)
                    ->get();
                
                if ($lowStock->count() > 0) {
                    $list = $lowStock->map(function($item) {
                        $merk = $item->merk ? " ({$item->merk})" : "";
                        return "• {$item->nama_produk}{$merk}: {$item->stok} unit";
                    })->implode("\n");
                    $data[] = "⚠️ Produk dengan Stok Menipis (<10):\n" . $list;
                    
                    // Total produk dengan stok habis
                    $outOfStock = DB::table('produk')->where('stok', 0)->count();
                    if ($outOfStock > 0) {
                        $data[] = "🚫 Produk Habis Stok: {$outOfStock} item";
                    }
                } else {
                    $data[] = "✅ Semua stok produk aman.";
                }
                
                // Total nilai stok
                $totalStockValue = DB::table('produk')
                    ->selectRaw('SUM(stok * harga_beli) as total_value')
                    ->first();
                if ($totalStockValue && $totalStockValue->total_value > 0) {
                    $data[] = "💰 Total Nilai Stok: Rp " . number_format($totalStockValue->total_value, 0, ',', '.');
                }
            }
        }

        // --- 3. DATA SUPPLIER ---
        $supplierKeywords = ['supplier', 'suplier', 'vendor', 'pemasok'];
        $isSupplierQuery = false;
        foreach ($supplierKeywords as $kw) {
            if (stripos($message, $kw) !== false) {
                $isSupplierQuery = true;
                break;
            }
        }

        if ($isSupplierQuery) {
            try {
                $suppliers = DB::table('supplier')
                    ->select('nama_supplier')
                    ->limit(20)
                    ->get();
                
                if ($suppliers->count() > 0) {
                    $list = $suppliers->map(function($item, $index) {
                        return ($index + 1) . ". {$item->nama_supplier}";
                    })->implode("\n");
                    $data[] = "🏢 Daftar Supplier ({$suppliers->count()}):\n" . $list;
                } else {
                    $data[] = "❌ Belum ada data supplier.";
                }
            } catch (\Exception $e) {
                $data[] = "⚠️ Error mengambil data supplier: " . $e->getMessage();
                \Log::error('Supplier query error:', ['error' => $e->getMessage()]);
            }
        }

        // --- 4. DATA PELANGGAN ---
        $customerKeywords = ['pelanggan', 'customer', 'konsumen'];
        $isCustomerQuery = false;
        foreach ($customerKeywords as $kw) {
            if (stripos($message, $kw) !== false) {
                $isCustomerQuery = true;
                break;
            }
        }

        if ($isCustomerQuery) {
            try {
                $customers = DB::table('pelanggan')
                    ->select('nama', 'no_hp', 'email', 'alamat', 'tanggal_pembelian')
                    ->orderBy('created_at', 'desc')
                    ->limit(15)
                    ->get();
                
                if ($customers->count() > 0) {
                    $list = $customers->map(function($item, $index) {
                        $number = $index + 1;
                        $hp = $item->no_hp ? " | 📱 {$item->no_hp}" : "";
                        $email = $item->email ? " | ✉️ {$item->email}" : "";
                        return "{$number}. {$item->nama}{$hp}{$email}";
                    })->implode("\n");
                    $data[] = "👥 Daftar Pelanggan ({$customers->count()}):\n" . $list;
                    
                    // Statistik pelanggan
                    $totalCustomers = DB::table('pelanggan')->count();
                    $data[] = "📊 Total Pelanggan Terdaftar: {$totalCustomers} orang";
                    
                    // Pelanggan baru bulan ini
                    $newCustomers = DB::table('pelanggan')
                        ->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year)
                        ->count();
                    if ($newCustomers > 0) {
                        $data[] = "🆕 Pelanggan Baru Bulan Ini: {$newCustomers} orang";
                    }
                } else {
                    $data[] = "❌ Belum ada data pelanggan.";
                }
            } catch (\Exception $e) {
                $data[] = "⚠️ Error mengambil data pelanggan: " . $e->getMessage();
                \Log::error('Customer query error:', ['error' => $e->getMessage()]);
            }
        }

        // --- 5. DATA PEMBELIAN ---
        $purchaseKeywords = ['pembelian', 'beli', 'purchase', 'order'];
        $isPurchaseQuery = false;
        foreach ($purchaseKeywords as $kw) {
            if (stripos($message, $kw) !== false) {
                $isPurchaseQuery = true;
                break;
            }
        }

        if ($isPurchaseQuery) {
            try {
                // Total pembelian bulan ini
                $monthPurchases = DB::table('pembelian')
                    ->whereMonth('tanggal_pembelian', $now->month)
                    ->whereYear('tanggal_pembelian', $now->year)
                    ->sum('total_harga');
                
                $data[] = "🛒 Total Pembelian Bulan Ini: Rp " . number_format($monthPurchases, 0, ',', '.');

                // Count pembelian
                $purchaseCount = DB::table('pembelian')
                    ->whereMonth('tanggal_pembelian', $now->month)
                    ->whereYear('tanggal_pembelian', $now->year)
                    ->count();
                
                $data[] = "📋 Jumlah Transaksi Pembelian: {$purchaseCount} kali";
                
                // Pembelian terbaru dengan detail supplier
                $recentPurchases = DB::table('pembelian')
                    ->join('supplier', 'pembelian.id_supplier', '=', 'supplier.id_supplier')
                    ->select('supplier.nama_supplier', 'pembelian.tanggal_pembelian', 'pembelian.total_harga')
                    ->orderBy('pembelian.tanggal_pembelian', 'desc')
                    ->limit(5)
                    ->get();
                
                if ($recentPurchases->count() > 0) {
                    $list = $recentPurchases->map(function($item, $index) {
                        $number = $index + 1;
                        $tanggal = date('d/m/Y', strtotime($item->tanggal_pembelian));
                        $total = number_format($item->total_harga, 0, ',', '.');
                        return "{$number}. {$item->nama_supplier} - {$tanggal} - Rp {$total}";
                    })->implode("\n");
                    $data[] = "📦 Pembelian Terbaru:\n" . $list;
                }
            } catch (\Exception $e) {
                $data[] = "⚠️ Error mengambil data pembelian: " . $e->getMessage();
                \Log::error('Purchase query error:', ['error' => $e->getMessage()]);
            }
        }

        // --- 6. DATA PEGAWAI ---
        $employeeKeywords = ['pegawai', 'karyawan', 'staff', 'user', 'admin'];
        $isEmployeeQuery = false;
        foreach ($employeeKeywords as $kw) {
            if (stripos($message, $kw) !== false) {
                $isEmployeeQuery = true;
                break;
            }
        }

        if ($isEmployeeQuery) {
            try {
                $employees = DB::table('users')
                    ->select('name', 'email', 'role', 'created_at')
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                if ($employees->count() > 0) {
                    // Group by role
                    $admins = $employees->where('role', 'superadmin');
                    $staff = $employees->where('role', 'pegawai');
                    
                    $output = [];
                    
                    if ($admins->count() > 0) {
                        $adminList = $admins->map(function($item, $index) {
                            return ($index + 1) . ". {$item->name} | ✉️ {$item->email}";
                        })->implode("\n");
                        $output[] = "👑 Admin ({$admins->count()}):\n" . $adminList;
                    }
                    
                    if ($staff->count() > 0) {
                        $staffList = $staff->map(function($item, $index) {
                            return ($index + 1) . ". {$item->name} | ✉️ {$item->email}";
                        })->implode("\n");
                        $output[] = "👨‍💼 Pegawai ({$staff->count()}):\n" . $staffList;
                    }
                    
                    $data[] = "👥 Data Pegawai/Karyawan:\n\n" . implode("\n\n", $output);
                    $data[] = "📊 Total Pengguna Sistem: {$employees->count()} orang";
                } else {
                    $data[] = "❌ Belum ada data pegawai.";
                }
            } catch (\Exception $e) {
                $data[] = "⚠️ Error mengambil data pegawai: " . $e->getMessage();
                \Log::error('Employee query error:', ['error' => $e->getMessage()]);
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

    /**
     * Cek apakah pertanyaan relevan dengan bisnis toko
     */
    private function isRelevantToBusinessQuery($message)
    {
        // Daftar keyword bisnis yang diizinkan
        $businessKeywords = [
            // Modul Stok & Produk
            'stok', 'stock', 'barang', 'produk', 'laptop', 'tersedia', 'habis', 'menipis', 'item', 'katalog',
            
            // Modul Penjualan
            'penjualan', 'jual', 'omset', 'revenue', 'transaksi', 'terjual', 'laris', 'pembeli', 'penjualan',
            
            // Modul Pembelian
            'pembelian', 'beli', 'supplier', 'restock', 'order', 'pesan',
            
            // Modul Pelanggan
            'pelanggan', 'customer', 'konsumen', 'pembeli',
            
            // Analytics & Laporan
            'grafik', 'laporan', 'statistik', 'analisis', 'terlaris', 'best seller', 'report', 'data',
            
            // Umum Bisnis
            'toko', 'gudang', 'inventori', 'inventory', 'bisnis', 'usaha'
        ];
        
        // Cek apakah ada keyword bisnis di pesan user
        foreach ($businessKeywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                return true; // Pertanyaan relevan
            }
        }
        
        // Jika tidak ada keyword bisnis, anggap tidak relevan
        return false;
    }

    /**
     * Hilangkan format markdown dari response AI
     */
    private function stripMarkdown($text)
    {
        if (!$text) return $text;

        // Hapus bold (**text** atau __text__)
        $text = preg_replace('/\*\*(.+?)\*\*/', '$1', $text);
        $text = preg_replace('/__(.+?)__/', '$1', $text);
        
        // Hapus italic (*text* atau _text_)
        $text = preg_replace('/\*(.+?)\*/', '$1', $text);
        $text = preg_replace('/_(.+?)_/', '$1', $text);
        
        // Hapus code blocks (```code```)
        $text = preg_replace('/```(.+?)```/s', '$1', $text);
        $text = preg_replace('/`(.+?)`/', '$1', $text);
        
        return $text;
    }
}
