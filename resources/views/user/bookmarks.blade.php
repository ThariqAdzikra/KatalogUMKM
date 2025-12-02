@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">My Bookmarks</h2>
    
    <div class="row">
        @forelse($bookmarks as $bookmark)
            <div class="col-md-3 mb-4">
                <div class="card">
                    @if($bookmark->produk->gambar)
                        <img src="{{ asset('storage/'. $bookmark->produk->gambar) }}" class="card-img-top" alt="{{ $bookmark->produk->nama_produk }}">
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $bookmark->produk->nama_produk }}</h5>
                        <p class="text-muted">{{ $bookmark->produk->kategori->nama_kategori }}</p>
                        <p class="h5">Rp {{ number_format($bookmark->produk->harga_jual, 0, ',', '.') }}</p>
                        
                        <form action="{{ route('bookmarks.toggle', $bookmark->produk->id_produk) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-center text-muted">Belum ada bookmark</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
