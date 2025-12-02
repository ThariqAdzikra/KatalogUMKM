@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Riwayat Pembelian</h2>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID Order</th>
                            <th>Tanggal</th>
                            <th>Produk</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                            <tr>
                                <td>#{{ $purchase->id_penjualan }}</td>
                                <td>{{ $purchase->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($purchase->detailPenjualans as $detail)
                                            <li>
                                                {{ $detail->produk->nama_produk }} 
                                                <small class="text-muted">x{{ $detail->jumlah }}</small>
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>Rp {{ number_format($purchase->total_harga, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge bg-success">Selesai</span>
                                </td>
                                <td>
                                    <a href="{{ route('penjualan.struk', $purchase->id_penjualan) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                        <i class="bi bi-printer"></i> Struk
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <p class="text-muted mb-0">Belum ada riwayat pembelian</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $purchases->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
