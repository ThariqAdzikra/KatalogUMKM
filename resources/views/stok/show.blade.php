@extends('layouts.app')

@section('title', 'Detail Produk - ' . App\Models\SiteSetting::get('brand_name'))

@push('styles')
{{-- Pastikan path ini benar --}}
<link rel="stylesheet" href="/css/detail_produk/style.css">
@endpush

@section('content')

@php
    $previousUrl = url()->previous();
    $datangDariStok = str_contains($previousUrl, '/stok');
@endphp


<div class="container py-4">
    <div class="detail-container">
        <div class="detail-card">
            <div class="detail-header">
                <h1 class="detail-title">
                    <i class="bi bi-info-circle-fill me-2"></i>Detail Produk
                </h1>
                <p class="detail-subtitle">Informasi lengkap produk</p>
            </div>

            <div class="detail-body">
                {{-- Product Image --}}
                <div class="product-image-section">
                    @if($stok->gambar)
                        <img src="{{ asset($stok->gambar) }}" alt="{{ $stok->nama_produk }}">
                    @else
                        <div class="product-image-placeholder">
                        <i class="bi bi-laptop"></i>
                        <p>Tidak ada gambar</p>
                        </div>
                    @endif
                </div>

                {{-- ====================================================== --}}
                {{-- === TATA LETAK BARU (INFO DASAR & STOK DI ATAS) === --}}
                {{-- ====================================================== --}}

                {{-- Baris Baru untuk Info Dasar dan Info Stok --}}
                <div class="row g-4 mb-4">

                    {{-- KOLOM 1: INFORMASI DASAR --}}
                    <div class="col-lg-7">
                        <div class="detail-info-card h-100">
                            <div class="info-section">
                                <h3 class="section-title">
                                    <i class="bi bi-info-circle"></i>Informasi Dasar
                                </h3>
                                
                                <div class="info-row">
                                    <div class="info-label">Nama Produk</div>
                                    <div class="info-value">{{ $stok->nama_produk }}</div>
                                </div>
            
                                <div class="info-row">
                                    <div class="info-label">Merk</div>
                                    <div class="info-value">
                                        <span class="badge-merk show-page">{{ $stok->merk }}</span>
                                    </div>
                                </div>
            
                                <div class="info-row">
                                    <div class="info-label">Kategori</div>
                                    <div class="info-value">
                                        <span class="badge-merk show-page">
                                            {{ $stok->kategori->nama_kategori ?? 'Tidak Dikategorikan' }}
                                        </span>
                                    </div>
                                </div>
            
                                {{-- Style text-align: justify agar rata kiri kanan --}}
                                <div class="info-row">
                                    <div class="info-label">Spesifikasi</div>
                                    <div class="info-value" style="text-align: justify;">{{ $stok->spesifikasi }}</div>
                                </div>

                                {{-- Penambahan kata "Tahun" pada garansi --}}
                                <div class="info-row">
                                    <div class="info-label">Garansi</div>
                                    <div class="info-value">{{ $stok->garansi ? $stok->garansi . ' Tahun' : '-' }}</div>
                                </div>
            
                                {{-- ✅ HANYA TAMPIL JIKA SUDAH LOGIN --}}
                                @auth
                                <div class="info-row">
                                    <div class="info-label">ID Produk</div>
                                    <div class="info-value">
                                        <code>#{{ $stok->id_produk }}</code>
                                    </div>
                                </div>
                                @endauth
                            </div>
                        </div>
                    </div>

                    {{-- KOLOM 2: INFORMASI STOK --}}
                    <div class="col-lg-5">
                        <div class="detail-info-card h-100">
                            <div class="info-section">
                                <h3 class="section-title">
                                    <i class="bi bi-box-seam"></i>Informasi Stok
                                </h3>
            
                                <div class="info-row">
                                    <div class="info-label">Status Stok</div>
                                    <div class="info-value">
                                        @if($stok->stok == 0)
                                            <span class="badge-stock-detail badge-habis">
                                                <i class="bi bi-x-circle me-2"></i>Stok Habis
                                            </span>
                                        @elseif($stok->stok <= 5)
                                            <span class="badge-stock-detail badge-menipis">
                                                <i class="bi bi-exclamation-triangle me-2"></i>Stok Menipis
                                            </span>
                                        @else
                                            <span class="badge-stock-detail badge-tersedia">
                                                <i class="bi bi-check-circle me-2"></i>Stok Tersedia
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="info-row">
                                    <div class="info-label">Jumlah Stok</div>
                                    <div class="info-value">
                                        <span style="font-size: 2rem; font-weight: 700; color: var(--primary-wood);">
                                            {{ $stok->stok }}
                                        </span>
                                        <span class="text-muted">Unit</span>
                                    </div>
                                </div>
            
                                {{-- ✅ TAMPILAN BERBEDA UNTUK LOGIN DAN GUEST --}}
                                @auth
                                {{-- Tampilan Total Nilai Stok untuk User Login --}}
                                <div class="info-row">
                                    <div class="info-label">Total Nilai Stok</div>
                                    <div class="info-value">
                                        <span class="price-highlight">
                                            Rp {{ number_format($stok->harga_jual * $stok->stok, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                @else
                                {{-- Tampilan Harga Jual untuk Guest (Sesuai permintaan) --}}
                                <div class="info-row">
                                    <div class="info-label">Harga</div>
                                    <div class="info-value">
                                        <span class="price-highlight" style="color: var(--primary-wood); font-size: 1.75rem;">
                                            Rp {{ number_format($stok->harga_jual, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                @endauth
                            </div>
                        </div>
                    </div>

                </div>
                {{-- Akhir Baris Baru --}}


                {{-- ✅ HANYA TAMPIL JIKA SUDAH LOGIN --}}
                @auth
                {{-- Pricing Information (Tetap Full-Width) --}}
                <div class="info-section info-section-fullwidth">
                    <h3 class="section-title">
                        <i class="bi bi-currency-dollar"></i>Informasi Harga
                    </h3>

                    <div class="row g-4">
                        {{-- TAMPILAN LENGKAP UNTUK USER LOGIN --}}
                        <div class="col-md-4">
                            <div class="price-box h-100">
                                <div class="price-label">Harga Beli</div>
                                <div class="price-amount price-buy">
                                    Rp {{ number_format($stok->harga_beli, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="price-box h-100">
                                <div class="price-label">Harga Jual</div>
                                <div class="price-amount price-sell">
                                    Rp {{ number_format($stok->harga_jual, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="profit-card h-100">
                                <div class="profit-label">Margin Keuntungan</div>
                                <div class="profit-value">
                                    Rp {{ number_format($stok->harga_jual - $stok->harga_beli, 0, ',', '.') }}
                                </div>
                                <small style="opacity: 0.9;">
                                    {{ $stok->harga_beli > 0 ? number_format((($stok->harga_jual - $stok->harga_beli) / $stok->harga_beli) * 100, 1) : 0 }}%
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                @endauth
                

                {{-- ✅ HANYA TAMPIL JIKA SUDAH LOGIN --}}
                @auth
                {{-- Riwayat (Tetap Full-Width) --}}
                <div class="info-section info-section-fullwidth">
                    <h3 class="section-title">
                        <i class="bi bi-clock-history"></i>Riwayat
                    </h3>

                    <div class="info-row">
                        <div class="info-label">Ditambahkan Pada</div>
                        <div class="info-value">
                            <i class="bi bi-calendar-check me-2"></i>
                            {{ $stok->created_at ? $stok->created_at->format('d M Y, H:i') : '-' }}
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Terakhir Diubah</div>
                        <div class="info-value">
                            <i class="bi bi-calendar-event me-2"></i>
                            {{ $stok->updated_at ? $stok->updated_at->format('d M Y, H:i') : '-' }}
                        </div>
                    </div>
                </div>
                @endauth

                {{-- TOMBOL AKSI --}}
                {{-- ✅ DIPERBARUI: Class 'border-top' dihapus agar garis menjadi 1 saja (garis dari list riwayat) --}}
                <div class="d-flex justify-content-between gap-3 mt-4 pt-4">
                    
                    {{-- Tombol Kembali selalu tampil --}}
                    <a href="{{ $previousUrl }}" class="btn btn-action-detail btn-back-detail">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                    
                    {{-- ✅ HANYA TAMPIL JIKA SUDAH LOGIN --}}
                    @auth
                        @if($datangDariStok)
                        <div class="d-flex gap-3">
                            <a href="{{ route('stok.edit', $stok->id_produk) }}" class="btn btn-action-detail btn-edit-detail">
                                <i class="bi bi-pencil-square me-2"></i>Edit Produk
                            </a>
                            <form action="{{ route('stok.destroy', $stok->id_produk) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-action-detail btn-delete-detail">
                                    <i class="bi bi-trash me-2"></i>Hapus Produk
                                </button>
                            </form>
                        </div>
                        @endif
                    @endauth
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection