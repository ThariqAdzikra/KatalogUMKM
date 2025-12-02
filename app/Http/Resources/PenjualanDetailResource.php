<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenjualanDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_penjualan_detail' => $this->id_penjualan_detail,
            'id_produk' => $this->id_produk,
            'nama_produk' => $this->whenLoaded('produk', $this->produk->nama_produk),
            'garansi_tahun' => $this->whenLoaded('produk', $this->produk->garansi),
            'jumlah' => (int) $this->jumlah,
            'harga_satuan' => (float) $this->harga_satuan,
            'subtotal' => (float) $this->subtotal,
        ];
    }
}