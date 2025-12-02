<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenjualanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_penjualan' => $this->id_penjualan,
            'tanggal_penjualan' => $this->tanggal_penjualan,
            'metode_pembayaran' => $this->metode_pembayaran,
            'total_harga' => (float) $this->total_harga,
            'kasir' => [
                'id_user' => $this->id_user,
                // PENYESUAIAN DI SINI: dari $this->user->nama menjadi $this->user->name
                'nama_user' => $this->whenLoaded('user', $this->user->name), 
            ],
            'pelanggan' => [
                'id_pelanggan' => $this->id_pelanggan,
                'nama_pelanggan' => $this->whenLoaded('pelanggan', $this->pelanggan->nama),
                'no_hp' => $this->whenLoaded('pelanggan', $this->pelanggan->no_hp),
            ],
            // Muat detail hanya jika diminta (misal: di method 'show')
            'detail_penjualan' => PenjualanDetailResource::collection($this->whenLoaded('detail')),
            'created_at' => $this->created_at,
        ];
    }
}