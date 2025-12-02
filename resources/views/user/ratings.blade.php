@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Ulasan Saya</h2>

    <div class="row">
        @forelse($ratings as $rating)
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                        <div class="d-flex align-items-center">
                            @if($rating->produk->gambar)
                                <img src="{{ asset('storage/'.$rating->produk->gambar) }}" alt="" style="width: 40px; height: 40px; object-fit: cover;" class="rounded me-2">
                            @endif
                            <a href="{{ route('katalog.show', $rating->produk->id_produk) }}" class="text-decoration-none text-dark fw-bold">
                                {{ $rating->produk->nama_produk }}
                            </a>
                        </div>
                        <small class="text-muted">{{ $rating->updated_at->format('d M Y') }}</small>
                    </div>
                    <div class="card-body">
                        <div class="mb-2 text-warning">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $rating->rating ? '-fill' : '' }}"></i>
                            @endfor
                        </div>
                        <p class="card-text">{{ $rating->review ?? 'Tidak ada review tertulis.' }}</p>
                    </div>
                    <div class="card-footer bg-transparent border-top-0 text-end">
                        <a href="{{ route('katalog.show', $rating->produk->id_produk) }}" class="btn btn-sm btn-outline-primary">
                            Edit Review
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    Belum ada ulasan yang diberikan.
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $ratings->links() }}
    </div>
</div>
@endsection
