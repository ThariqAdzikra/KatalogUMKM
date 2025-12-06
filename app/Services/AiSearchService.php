<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AiSearchService
{
    public function detectUserIntent($message)
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

    public function fetchRelevantData($message, $context = 'general')
    {
        $now = Carbon::now();
        $data = [];

        // Filter data based on context OR keywords
        // UNIFIED CONTROLLER: Allow full access to all data regardless of module
        // This makes the AI behave like SuperAdmin everywhere
        $msg = strtolower($message);
        
        $showSales = true;
        $showStock = true;
        $showPurchase = true;
        $showCustomer = true;
        $showEmployee = true;

        // --- UNIVERSAL ENTITY SEARCH (The "Deep Think" Logic) ---
        // Extract meaningful words to find entities (Product, Customer, Supplier) mentioned in the chat
        // regardless of the context or specific keywords used.
        $words = preg_split('/[\s\W]+/', strtolower($message));
        $universalStopWords = ['cek', 'berapa', 'apakah', 'ada', 'di', 'ini', 'itu', 'yang', 'mau', 'ketersediaan', 'jumlah', 'nya', 'siapa', 'cari', 'lihat', 'tampilkan', 'data', 'list', 'daftar', 'dari', 'untuk', 'oleh', 'pada', 'dengan', 'dan', 'atau'];
        
        $searchTerms = array_filter($words, function($word) use ($universalStopWords) {
            $isNumeric = is_numeric($word);
            return !in_array($word, $universalStopWords) && ($isNumeric || strlen($word) >= 3);
        });

        if (!empty($searchTerms)) {
            // 1. Search Products (Universal)
            $uniProducts = DB::table('produk')
                ->select('id_produk', 'nama_produk', 'merk', 'stok', 'harga_jual')
                ->where(function($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $query->orWhere('nama_produk', 'like', "%{$term}%")
                              ->orWhere('merk', 'like', "%{$term}%");
                    }
                })
                ->limit(3)
                ->get();

            if ($uniProducts->count() > 0) {
                $list = $uniProducts->map(function($item) {
                    $merk = $item->merk ? " ({$item->merk})" : "";
                    $harga = number_format($item->harga_jual, 0, ',', '.');
                    $status = $item->stok < 5 ? "⚠️" : ($item->stok < 10 ? "📦" : "✅");
                    
                    // Deep Context: Buyers
                    $recentBuyers = DB::table('penjualan_detail')
                        ->join('penjualan', 'penjualan_detail.id_penjualan', '=', 'penjualan.id_penjualan')
                        ->join('pelanggan', 'penjualan.id_pelanggan', '=', 'pelanggan.id_pelanggan')
                        ->where('penjualan_detail.id_produk', $item->id_produk)
                        ->select('pelanggan.nama', 'penjualan.tanggal_penjualan')
                        ->orderBy('penjualan.tanggal_penjualan', 'desc')
                        ->limit(3)
                        ->get();
                        
                    $buyersInfo = "";
                    if ($recentBuyers->count() > 0) {
                        $buyersList = $recentBuyers->map(function($b) {
                            return $b->nama . " (" . date('d/m', strtotime($b->tanggal_penjualan)) . ")";
                        })->implode(", ");
                        $buyersInfo = "\n  👤 Pembeli Terakhir: " . $buyersList;
                    }

                    // Deep Context: Suppliers
                    $suppliers = DB::table('pembelian_detail')
                        ->join('pembelian', 'pembelian_detail.id_pembelian', '=', 'pembelian.id_pembelian')
                        ->join('supplier', 'pembelian.id_supplier', '=', 'supplier.id_supplier')
                        ->where('pembelian_detail.id_produk', $item->id_produk)
                        ->select('supplier.nama_supplier')
                        ->distinct()
                        ->limit(3)
                        ->get();
                        
                    $supplierInfo = "";
                    if ($suppliers->count() > 0) {
                        $suppList = $suppliers->pluck('nama_supplier')->implode(", ");
                        $supplierInfo = "\n  🏢 Supplier: " . $suppList;
                    }

                    return "• {$item->nama_produk}{$merk}\n  {$status} Stok: {$item->stok} unit | 💰 Rp {$harga}{$buyersInfo}{$supplierInfo}";
                })->implode("\n\n");
                $data[] = "📦 Data Produk Relevan (Universal Search):\n" . $list;
            }

            // 2. Search Customers (Universal)
            $uniCustomers = DB::table('pelanggan')
                ->select('id_pelanggan', 'nama', 'no_hp', 'email', 'alamat')
                ->where(function($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $query->orWhere('nama', 'like', "%{$term}%");
                    }
                })
                ->limit(3)
                ->get();

            if ($uniCustomers->count() > 0) {
                $list = $uniCustomers->map(function($item) {
                    // Deep Context: Purchase History
                    $history = DB::table('penjualan')
                        ->join('penjualan_detail', 'penjualan.id_penjualan', '=', 'penjualan_detail.id_penjualan')
                        ->join('produk', 'penjualan_detail.id_produk', '=', 'produk.id_produk')
                        ->where('penjualan.id_pelanggan', $item->id_pelanggan)
                        ->select('produk.nama_produk', 'penjualan.tanggal_penjualan')
                        ->orderBy('penjualan.tanggal_penjualan', 'desc')
                        ->limit(5)
                        ->get();
                        
                    $historyInfo = "";
                    if ($history->count() > 0) {
                        $histList = $history->map(function($h) {
                            return $h->nama_produk;
                        })->implode(", ");
                        $historyInfo = "\n  🛒 Riwayat Belanja: " . $histList;
                    }
                    return "• {$item->nama} | 📱 {$item->no_hp}{$historyInfo}";
                })->implode("\n\n");
                $data[] = "👥 Data Pelanggan Relevan (Universal Search):\n" . $list;
            }

            // 3. Search Suppliers (Universal)
            $uniSuppliers = DB::table('supplier')
                ->select('id_supplier', 'nama_supplier')
                ->where(function($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $query->orWhere('nama_supplier', 'like', "%{$term}%");
                    }
                })
                ->limit(3)
                ->get();

            if ($uniSuppliers->count() > 0) {
                $list = $uniSuppliers->map(function($item) {
                    // Deep Context: Supplied Products
                    $products = DB::table('pembelian')
                        ->join('pembelian_detail', 'pembelian.id_pembelian', '=', 'pembelian_detail.id_pembelian')
                        ->join('produk', 'pembelian_detail.id_produk', '=', 'produk.id_produk')
                        ->where('pembelian.id_supplier', $item->id_supplier)
                        ->select('produk.nama_produk')
                        ->distinct()
                        ->limit(5)
                        ->get();
                        
                    $productInfo = "";
                    if ($products->count() > 0) {
                        $prodList = $products->pluck('nama_produk')->implode(", ");
                        $productInfo = "\n  📦 Supply: " . $prodList;
                    }
                    return "• {$item->nama_supplier}{$productInfo}";
                })->implode("\n\n");
                $data[] = "🏢 Data Supplier Relevan (Universal Search):\n" . $list;
            }
        }

        // --- 1. DATA PENJUALAN ---
        if ($showSales) {
        // Omset hari ini
        $todayRevenue = DB::table('penjualan')->whereDate('tanggal_penjualan', $now->today())->sum('total_harga');
        $data[] = "📊 Omset Hari Ini: Rp " . number_format($todayRevenue, 0, ',', '.') . " [Source: penjualan.total_harga]";

        // Omset bulan ini
        $monthRevenue = DB::table('penjualan')
            ->whereMonth('tanggal_penjualan', $now->month)
            ->whereYear('tanggal_penjualan', $now->year)
            ->sum('total_harga');
        $data[] = "📊 Omset Bulan Ini: Rp " . number_format($monthRevenue, 0, ',', '.') . " [Source: penjualan.total_harga]";

        // Omset bulan lalu
        $lastMonth = $now->copy()->subMonth();
        $lastMonthRevenue = DB::table('penjualan')
            ->whereMonth('tanggal_penjualan', $lastMonth->month)
            ->whereYear('tanggal_penjualan', $lastMonth->year)
            ->sum('total_harga');
        $data[] = "📊 Omset Bulan Lalu: Rp " . number_format($lastMonthRevenue, 0, ',', '.') . " [Source: penjualan.total_harga]";

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
            $data[] = "🏆 Produk Terlaris Bulan Ini: {$bestSeller->nama_produk} ({$bestSeller->total} unit terjual) [Source: produk.nama_produk, penjualan_detail.jumlah]";
        }

        }

        // --- 2. DATA STOK ---
        if ($showStock) {
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
            // Split by non-word characters to handle punctuation
            $words = preg_split('/[\s\W]+/', strtolower($message));
            $stopWords = ['cek', 'berapa', 'apakah', 'ada', 'di', 'ini', 'itu', 'yang', 'mau', 'ketersediaan', 'jumlah', 'nya', 'stok', 'stock', 'barang', 'sisa', 'tersedia', 'habis', 'menipis'];
            
            // Filter out stop words
            $productKeywords = array_filter($words, function($word) use ($stopWords) {
                // Allow short words if they are numeric (e.g., "5" in "Nitro 5")
                // Otherwise require at least 3 chars
                $isNumeric = is_numeric($word);
                return !in_array($word, $stopWords) && ($isNumeric || strlen($word) >= 3);
            });

            // Check for sorting intent (paling sedikit/banyak)
            $isSortAsc = false;
            $isSortDesc = false;
            
            if (preg_match('/(sedikit|rendah|kecil|min|habis)/i', $message)) {
                $isSortAsc = true;
            } elseif (preg_match('/(banyak|tinggi|besar|max|penuh)/i', $message)) {
                $isSortDesc = true;
            }

            if ($isSortAsc || $isSortDesc) {
                // Fetch products sorted by stock
                $direction = $isSortAsc ? 'asc' : 'desc';
                $label = $isSortAsc ? 'Sedikit' : 'Banyak';
                
                $sortedProducts = DB::table('produk')
                    ->select('nama_produk', 'merk', 'stok', 'harga_jual')
                    ->orderBy('stok', $direction)
                    ->limit(5) // Top 5
                    ->get();
                    
                if ($sortedProducts->count() > 0) {
                    $list = $sortedProducts->map(function($item) {
                        $merk = $item->merk ? " ({$item->merk})" : "";
                        $status = $item->stok < 5 ? "⚠️" : ($item->stok < 10 ? "📦" : "✅");
                        return "• {$item->nama_produk}{$merk}: {$status} {$item->stok} unit";
                    })->implode("\n");
                    $data[] = "📊 5 Produk dengan Stok Paling {$label}:\n" . $list;
                } else {
                    $data[] = "❌ Data produk tidak ditemukan.";
                }
            } elseif (count($productKeywords) > 0) {
                // Search for products that match ANY of the keywords
                $products = DB::table('produk')
                    ->select('id_produk', 'nama_produk', 'merk', 'stok', 'harga_jual') // Added id_produk
                    ->where(function($query) use ($productKeywords) {
                        foreach ($productKeywords as $keyword) {
                            $query->orWhere('nama_produk', 'like', "%{$keyword}%")
                                  ->orWhere('merk', 'like', "%{$keyword}%");
                        }
                    })
                    ->limit(5) // Limit to 5 to avoid overload
                    ->get();

                if ($products->count() > 0) {
                    $list = $products->map(function($item) {
                        $merk = $item->merk ? " ({$item->merk})" : "";
                        $harga = number_format($item->harga_jual, 0, ',', '.');
                        $status = $item->stok < 5 ? "⚠️" : ($item->stok < 10 ? "📦" : "✅");
                        
                        // DEEP CONTEXT: Get recent buyers for this product
                        $recentBuyers = DB::table('penjualan_detail')
                            ->join('penjualan', 'penjualan_detail.id_penjualan', '=', 'penjualan.id_penjualan')
                            ->join('pelanggan', 'penjualan.id_pelanggan', '=', 'pelanggan.id_pelanggan')
                            ->where('penjualan_detail.id_produk', $item->id_produk)
                            ->select('pelanggan.nama', 'penjualan.tanggal_penjualan')
                            ->orderBy('penjualan.tanggal_penjualan', 'desc')
                            ->limit(3)
                            ->get();
                            
                        $buyersInfo = "";
                        if ($recentBuyers->count() > 0) {
                            $buyersList = $recentBuyers->map(function($b) {
                                return $b->nama . " (" . date('d/m', strtotime($b->tanggal_penjualan)) . ")";
                            })->implode(", ");
                            $buyersInfo = "\n  👤 Pembeli Terakhir: " . $buyersList;
                        }

                        // DEEP CONTEXT: Get suppliers for this product
                        $suppliers = DB::table('pembelian_detail')
                            ->join('pembelian', 'pembelian_detail.id_pembelian', '=', 'pembelian.id_pembelian')
                            ->join('supplier', 'pembelian.id_supplier', '=', 'supplier.id_supplier')
                            ->where('pembelian_detail.id_produk', $item->id_produk)
                            ->select('supplier.nama_supplier')
                            ->distinct()
                            ->limit(3)
                            ->get();
                            
                        $supplierInfo = "";
                        if ($suppliers->count() > 0) {
                            $suppList = $suppliers->pluck('nama_supplier')->implode(", ");
                            $supplierInfo = "\n  🏢 Supplier: " . $suppList;
                        }

                        return "• {$item->nama_produk}{$merk}\n  {$status} Stok: {$item->stok} unit | 💰 Rp {$harga}{$buyersInfo}{$supplierInfo}";
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
                    $data[] = "⚠️ Produk dengan Stok Menipis (<10):\n" . $list . "\n[Source: produk.stok]";
                    
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
                    $data[] = "💰 Total Nilai Stok: Rp " . number_format($totalStockValue->total_value, 0, ',', '.') . " [Source: produk.stok * produk.harga_beli]";
                }
            }
        }

        }

        // --- 3. DATA SUPPLIER ---
        // Supplier relevan untuk Pembelian dan Stok
        if ($showPurchase || $showStock) {
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
                $query = DB::table('supplier')->select('id_supplier', 'nama_supplier');
                
                // Filter by name if specific terms are provided
                $words = preg_split('/[\s\W]+/', strtolower($message));
                $searchTerms = array_filter($words, function($word) use ($supplierKeywords) {
                    return strlen($word) >= 3 && !in_array($word, $supplierKeywords) && !in_array($word, ['data', 'list', 'daftar', 'siapa', 'cari', 'lihat', 'tampilkan']);
                });

                if (!empty($searchTerms)) {
                    $query->where(function($q) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $q->orWhere('nama_supplier', 'like', "%{$term}%");
                        }
                    });
                }

                $suppliers = $query->limit(10)->get();
                
                if ($suppliers->count() > 0) {
                    $list = $suppliers->map(function($item, $index) {
                        // DEEP CONTEXT: Get products supplied by this supplier
                        $products = DB::table('pembelian')
                            ->join('pembelian_detail', 'pembelian.id_pembelian', '=', 'pembelian_detail.id_pembelian')
                            ->join('produk', 'pembelian_detail.id_produk', '=', 'produk.id_produk')
                            ->where('pembelian.id_supplier', $item->id_supplier)
                            ->select('produk.nama_produk')
                            ->distinct()
                            ->limit(5)
                            ->get();
                            
                        $productInfo = "";
                        if ($products->count() > 0) {
                            $prodList = $products->pluck('nama_produk')->implode(", ");
                            $productInfo = "\n  📦 Supply: " . $prodList;
                        }

                        return ($index + 1) . ". {$item->nama_supplier}{$productInfo}";
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

        }

        if ($showCustomer) {
        $customerKeywords = ['pelanggan', 'customer', 'konsumen'];
        $isCustomerQuery = false;
        
        // If context is explicitly 'pelanggan', we assume it's a customer query
        if ($context === 'pelanggan') {
            $isCustomerQuery = true;
        } else {
            foreach ($customerKeywords as $kw) {
                if (stripos($message, $kw) !== false) {
                    $isCustomerQuery = true;
                    break;
                }
            }
        }

        if ($isCustomerQuery) {
            try {
                $query = DB::table('pelanggan')
                    ->select('id_pelanggan', 'nama', 'no_hp', 'email', 'alamat', 'garansi', 'tanggal_pembelian', 'catatan');

                // Use the same robust splitting and filtering as Universal Search
                $words = preg_split('/[\s\W]+/', strtolower($message));
                $stopWords = ['cek', 'berapa', 'apakah', 'ada', 'di', 'ini', 'itu', 'yang', 'mau', 'ketersediaan', 'jumlah', 'nya', 'siapa', 'cari', 'lihat', 'tampilkan', 'data', 'list', 'daftar', 'dari', 'untuk', 'oleh', 'pada', 'dengan', 'dan', 'atau', 'bernama', 'pelanggan', 'customer', 'konsumen'];
                
                $searchTerms = array_filter($words, function($word) use ($stopWords) {
                    $isNumeric = is_numeric($word);
                    return !in_array($word, $stopWords) && ($isNumeric || strlen($word) >= 3);
                });

                if (!empty($searchTerms)) {
                    $query->where(function($q) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $q->orWhere('nama', 'like', "%{$term}%");
                        }
                    });
                }

                $customers = $query->orderBy('created_at', 'desc')
                    ->limit(5) // Limit to 5
                    ->get();
                
                if ($customers->count() > 0) {
                    $list = $customers->map(function($item, $index) {
                        $number = $index + 1;
                        $hp = $item->no_hp ? " | 📱 {$item->no_hp}" : "";
                        $email = $item->email ? " | ✉️ {$item->email}" : "";
                        $alamat = $item->alamat ? " | 🏠 {$item->alamat}" : "";
                        $garansi = $item->garansi ? " | 🛡️ Garansi: {$item->garansi}" : "";
                        $catatan = $item->catatan ? " | 📝 {$item->catatan}" : "";
                        
                        // DEEP CONTEXT: Get purchase history
                        $history = DB::table('penjualan')
                            ->join('penjualan_detail', 'penjualan.id_penjualan', '=', 'penjualan_detail.id_penjualan')
                            ->join('produk', 'penjualan_detail.id_produk', '=', 'produk.id_produk')
                            ->where('penjualan.id_pelanggan', $item->id_pelanggan)
                            ->select('produk.nama_produk', 'penjualan.tanggal_penjualan')
                            ->orderBy('penjualan.tanggal_penjualan', 'desc')
                            ->limit(3)
                            ->get();
                            
                        $historyInfo = "";
                        if ($history->count() > 0) {
                            $histList = $history->map(function($h) {
                                return $h->nama_produk;
                            })->implode(", ");
                            $historyInfo = "\n  🛒 Riwayat: " . $histList;
                        }

                        return "{$number}. {$item->nama}{$hp}{$email}{$alamat}{$garansi}{$catatan}{$historyInfo}";
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

        }

        // --- 5. DATA PEMBELIAN ---
        if ($showPurchase) {
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
                
                $data[] = "🛒 Total Pembelian Bulan Ini: Rp " . number_format($monthPurchases, 0, ',', '.') . " [Source: pembelian.total_harga]";

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
                    $data[] = "📦 Pembelian Terbaru:\n" . $list . "\n[Source: pembelian.tanggal_pembelian, pembelian.total_harga, supplier.nama_supplier]";
                }
            } catch (\Exception $e) {
                $data[] = "⚠️ Error mengambil data pembelian: " . $e->getMessage();
                \Log::error('Purchase query error:', ['error' => $e->getMessage()]);
            }
        }

        }

        // --- 6. DATA PEGAWAI ---
        if ($showEmployee) {
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
                $query = DB::table('users')
                    ->select('name', 'email', 'role', 'created_at');

                // Filter by name if specific terms are provided
                $words = preg_split('/[\s\W]+/', strtolower($message));
                $stopWords = array_merge($employeeKeywords, ['data', 'list', 'daftar', 'siapa', 'cari', 'lihat', 'tampilkan', 'info', 'informasi', 'bernama', 'yang']);
                
                $searchTerms = array_filter($words, function($word) use ($stopWords) {
                    $isNumeric = is_numeric($word);
                    return !in_array($word, $stopWords) && ($isNumeric || strlen($word) >= 3);
                });

                if (!empty($searchTerms)) {
                    $query->where(function($q) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $q->orWhere('name', 'like', "%{$term}%")
                              ->orWhere('email', 'like', "%{$term}%");
                        }
                    });
                }

                $employees = $query->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
                
                if ($employees->count() > 0) {
                    // Group by role
                    $admins = $employees->where('role', 'superadmin');
                    $staff = $employees->where('role', 'pegawai');
                    
                    $output = [];
                    
                    if ($admins->count() > 0) {
                        $adminList = $admins->map(function($item, $index) {
                            return "• {$item->name} (Admin) | ✉️ {$item->email}";
                        })->implode("\n");
                        $output[] = "👑 Admin:\n" . $adminList;
                    }
                    
                    if ($staff->count() > 0) {
                        $staffList = $staff->map(function($item, $index) {
                            return "• {$item->name} (Staff) | ✉️ {$item->email}";
                        })->implode("\n");
                        $output[] = "👨‍💼 Pegawai:\n" . $staffList;
                    }
                    
                    $data[] = "👥 Data Pegawai/Karyawan:\n\n" . implode("\n\n", $output);
                    
                    // Only show total count if not searching
                    if (empty($searchTerms)) {
                        $data[] = "📊 Total Pengguna Sistem: " . DB::table('users')->count() . " orang";
                    }
                } else {
                    $data[] = "❌ Data pegawai tidak ditemukan.";
                }
            } catch (\Exception $e) {
                $data[] = "⚠️ Error mengambil data pegawai: " . $e->getMessage();
                \Log::error('Employee query error:', ['error' => $e->getMessage()]);
            }
        }

        return implode("\n\n", $data);
    }
    }
}
