@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        {{-- Product Image --}}
        <div class="col-md-6">
            @if($produk->gambar)
                <img src="{{ asset('storage/'.$produk->gambar) }}" class="img-fluid rounded" alt="{{ $produk->nama_produk }}">
            @else
                <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 400px;">
                    <span>No Image</span>
                </div>
            @endif
        </div>

        {{-- Product Info --}}
        <div class="col-md-6">
            <h1>{{ $produk->nama_produk }}</h1>
            <p class="text-muted">{{ $produk->kategori->nama_kategori }}</p>
            <p class="text-muted">{{ $produk->merk }}</p>
            
            {{-- Rating Display --}}
            <div class="mb-3">
                @if($produk->rating_count > 0)
                    <div class="d-flex align-items-center">
                        <div class="text-warning">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= round($produk->average_rating) ? '-fill' : '' }}"></i>
                            @endfor
                        </div>
                        <span class="ms-2">({{ number_format($produk->average_rating, 1) }} / 5) - {{ $produk->rating_count }} reviews</span>
                    </div>
                @else
                    <p class="text-muted">Belum ada rating</p>
                @endif
            </div>

            <h2 class="text-primary">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</h2>
            
            <p class="mb-3">
                <strong>Stok:</strong> 
                @if($produk->stok > 5)
                    <span class="badge bg-success">Tersedia ({{ $produk->stok }})</span>
                @elseif($produk->stok > 0)
                    <span class="badge bg-warning">Terbatas ({{ $produk->stok }})</span>
                @else
                    <span class="badge bg-danger">Habis</span>
                @endif
            </p>

            {{-- Bookmark Button --}}
            @auth
                <form action="{{ route('bookmarks.toggle', $produk->id_produk) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary mb-3">
                        <i class="bi bi-bookmark{{ $isBookmarked ? '-fill' : '' }}"></i>
                        {{ $isBookmarked ? 'Remove Bookmark' : 'Add Bookmark' }}
                    </button>
                </form>
            @endauth

            <h5 class="mt-4">Spesifikasi:</h5>
            <p>{!! nl2br(e($produk->spesifikasi)) !!}</p>
            
            @if($produk->garansi)
                <p><strong>Garansi:</strong> {{ $produk->garansi }} bulan</p>
            @endif
        </div>
    </div>

    {{-- Rating Section --}}
    <div class="row mt-5">
        <div class="col-12">
            <h3>Customer Reviews</h3>

            @auth
                @if($hasPurchased)
                    {{-- Rating Form --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>{{ $userRating ? 'Edit Your Rating' : 'Write a Review' }}</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ $userRating ? route('ratings.update', $userRating->id) : route('ratings.store', $produk->id_produk) }}" method="POST">
                                @csrf
                                @if($userRating)
                                    @method('PUT')
                                @endif

                                <div class="mb-3">
                                    <label class="form-label">Rating</label>
                                    <div class="rating-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <input type="radio" name="rating" id="star{{$i}}" value="{{$i}}" {{ ($userRating && $userRating->rating == $i) ? 'checked' : '' }} required>
                                            <label for="star{{$i}}"><i class="bi bi-star-fill"></i></label>
                                        @endfor
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="review" class="form-label">Review (Optional)</label>
                                    <textarea name="review" id="review" class="form-control" rows="4">{{ $userRating->review ?? '' }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">Submit Review</button>
                                
                                @if($userRating)
                                    <button type="button" class="btn btn-danger" onclick="if(confirm('Delete this review?')) document.getElementById('delete-rating-form').submit()">Delete Review</button>
                                @endif
                            </form>

                            @if($userRating)
                                <form id="delete-rating-form" action="{{ route('ratings.destroy', $userRating->id) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> You can only review products you've purchased.
                    </div>
                @endif
            @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Please <a href="{{ route('login') }}">login</a> to write a review.
                </div>
            @endauth

            {{-- Display Reviews --}}
            <div class="reviews-list">
                @forelse($produk->ratings as $rating)
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $rating->user->name }}</strong>
                                    <div class="text-warning">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star{{ $i <= $rating->rating ? '-fill' : '' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                <small class="text-muted">{{ $rating->created_at->diffForHumans() }}</small>
                            </div>
                            @if($rating->review)
                                <p class="mt-2 mb-0">{{ $rating->review }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted">No reviews yet. Be the first to review!</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
.rating-stars {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 5px;
}

.rating-stars input {
    display: none;
}

.rating-stars label {
    font-size: 2rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}

.rating-stars input:checked ~ label,
.rating-stars label:hover,
.rating-stars label:hover ~ label {
    color: #ffc107;
}
</style>
@endsection
