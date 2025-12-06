<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\AiSearchService;

class AiAgentController extends Controller
{
    protected $aiSearchService;

    public function __construct(AiSearchService $aiSearchService)
    {
        $this->aiSearchService = $aiSearchService;
    }

    public function chat(Request $request)
    {
        $userMessage = $request->input('message');
        $context = $request->input('context', 'general'); // Default to general if not provided

        $apiKey = env('KOLOSAL_API_KEY') ?: env('OPENAI_API_KEY');

        if (!$apiKey) {
            return response()->json(['reply' => 'Maaf, API Key belum dikonfigurasi. Silakan cek file .env Anda.']);
        }

        // Cek intent user via Service
        $detectedAction = $this->aiSearchService->detectUserIntent($userMessage);

        if ($detectedAction['type'] === 'query_database') {
            // Fetch data via Service
            $data = $this->aiSearchService->fetchRelevantData($userMessage, $context);
            
            // DEBUG: Log data yang akan dikirim ke AI
            \Log::info('Data sent to AI:', ['data' => $data, 'user_message' => $userMessage]);
            
            $systemPrompt = "Anda adalah AI Assistant untuk sebuah toko laptop. Anda hanya boleh menjawab menggunakan data yang diberikan oleh aplikasi backend.

ATURAN WAJIB:
1. Jangan pernah mengarang data, angka, nama produk, stok, harga, penjualan, pelanggan, supplier, atau informasi lain.
2. Jika data tidak tersedia, jawab bahwa datanya tidak ditemukan.
3. Selalu jawab secara ringkas, jelas, dan profesional.
4. Gunakan format berikut:
   - Bullet points untuk menjelaskan daftar
   - Numbering untuk langkah-langkah
   - Emoji hanya bila relevan
5. Jika backend mengirimkan data database, gunakan DAN HANYA gunakan data tersebut.
6. Jika user meminta sesuatu yang tidak ada dalam data, jawab:
   \"Data tersebut tidak tersedia dalam database.\"
7. Selalu validasi bahwa jawaban Anda sesuai konteks pertanyaan user.
8. Jangan menambahkan kolom, tabel, atau atribut yang tidak ada pada struktur database.

STRUKTUR DATABASE (Anda hanya boleh menyebut kolom-kolom ini):

produk:
- id_produk, nama_produk, merk, id_kategori, spesifikasi, garansi, harga_beli, harga_jual, stok, gambar, created_at, updated_at

penjualan:
- id_penjualan, id_user, id_pelanggan, tanggal_penjualan, total_harga, metode_pembayaran, created_at, updated_at

penjualan_detail:
- id_penjualan_detail, id_penjualan, id_produk, jumlah, harga_satuan, subtotal, created_at, updated_at

pembelian:
- id_pembelian, id_supplier, id_user, tanggal_pembelian, total_harga, created_at, updated_at

pembelian_detail:
- id_pembelian_detail, id_pembelian, id_produk, jumlah, harga_satuan, subtotal, created_at, updated_at

pelanggan:
- id_pelanggan, id_kategori, nama, no_hp, email, alamat, garansi, tanggal_pembelian, catatan, created_at, updated_at

supplier:
- id_supplier, nama_supplier, kontak, alamat, email, created_at, updated_at

users:
- id, name, email, photo, email_verified_at, password, role, remember_token, created_at, updated_at

CARA ANDA MERESPON:
- Jika backend mengirim JSON berisi data database, gunakan data itu untuk menjawab.
- Jika backend tidak mengirim data apapun, jawab sebagai asisten biasa (friendly dan informatif).
- Selalu jelaskan sumber data dalam format:
  \"Sumber: tabel.kolom\"

FORMAT JAWABAN WAJIB JSON:
{
  \"jawaban\": \"isi jawaban ke user\",
  \"sumber\": [\"nama_tabel.kolom\", \"nama_tabel.kolom\"]
}

Jika tidak ada data yang dipakai, kirim:
{
  \"jawaban\": \"isi jawaban\",
  \"sumber\": []
}

Anda TIDAK BOLEH keluar dari format JSON.

Data terkini dari backend:
" . $data;
            
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
                    $aiReply = $response->json('message.content') 
                            ?? $response->json('content')
                            ?? $response->json('response')
                            ?? 'Maaf, AI tidak memberikan respons yang valid.';
                }

                // Bersihkan markdown formatting jika ada (```json ... ```) atau teks tambahan
                $firstBrace = strpos($aiReply, '{');
                $lastBrace = strrpos($aiReply, '}');
                
                if ($firstBrace !== false && $lastBrace !== false) {
                    $cleanReply = substr($aiReply, $firstBrace, $lastBrace - $firstBrace + 1);
                } else {
                    $cleanReply = $aiReply;
                }

                // Parse JSON response from AI
                $decoded = json_decode($cleanReply, true);
                
                if (json_last_error() === JSON_ERROR_NONE && isset($decoded['jawaban'])) {
                    $finalReply = $decoded['jawaban'];
                    
                    // Bersihkan tag [Source: ...] jika AI menambahkannya sendiri di dalam jawaban
                    $finalReply = preg_replace('/\[Source:.*?\]/i', '', $finalReply);
                    $finalReply = trim($finalReply);
                } else {
                    // Fallback if AI didn't return valid JSON
                    \Log::warning('AI did not return valid JSON', ['reply' => $aiReply, 'clean_reply' => $cleanReply, 'json_error' => json_last_error_msg()]);
                    // Jika gagal decode, tampilkan raw reply tapi coba bersihkan json syntaxnya jika terlihat seperti json
                    $finalReply = $cleanReply;
                }

                return response()->json([
                    'reply' => $finalReply
                ]);
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
}
