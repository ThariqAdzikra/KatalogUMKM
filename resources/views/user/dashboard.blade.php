@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Dashboard User</h2>
    
    {{-- Stats Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Bookmarks</h5>
                    <p class="display-6">{{ $bookmarksCount }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Ratings</h5>
                    <p class="display-6">{{ $ratingsCount }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Purchases</h5>
                    <p class="display-6">{{ $purchasesCount }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Purchases --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5>Recent Purchases</h5>
        </div>
        <div class="card-body">
            @forelse($recentPurchases as $purchase)
                <div class="mb-3">
                    <strong>Order #{{ $purchase->id_penjualan }}</strong> - {{ $purchase->created_at->format('d M Y') }}
                    <br>
                    <small>Total: Rp {{ number_format($purchase->total_harga, 0, ',', '.') }}</small>
                </div>
            @empty
                <p class="text-muted">Belum ada pembelian</p>
            @endforelse
        </div>
    </div>

    {{-- Bookmarked Products --}}
    <div class="card">
        <div class="card-header">
            <h5>Bookmarked Products</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @forelse($bookmarkedProducts as $product)
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            @if($product->gambar)
                                <img src="{{ asset('storage/'. $product->gambar) }}" class="card-img-top" alt="{{ $product->nama_produk }}">
                            @endif
                            <div class="card-body">
                                <h6 class="card-title">{{ $product->nama_produk }}</h6>
                                <p class="card-text">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">Belum ada bookmark</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
