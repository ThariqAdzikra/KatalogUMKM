@extends('layouts.app')

@section('title', 'Detail Pelanggan - ' . App\Models\SiteSetting::get('brand_name'))


@push('styles')
{{-- CSS dari style.css akan otomatis diambil --}}
<link rel="stylesheet" href="/css/manajemen/style.css">
{{-- CSS dari pelanggan.css (sekarang berisi style gabungan) --}}
<link rel="stylesheet" href="/css/manajemen/pelanggan.css">
@endpush

@section('content')
<div class="container py-4">

    <div class="detail-page-header d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-person-circle me-3"></i>{{ $pelanggan->nama }}</h1>
            <p class="mb-0">Detail lengkap pelanggan dan riwayat pembelian</p>
        </div>
        </div>

    @if(session('success'))
        <div class="alert alert-success alert-custom mt-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="row g-4 mt-2 align-items-stretch">
        
        <div class="col-lg-4 d-flex flex-column">
            
            <div class="info-card-custom card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Informasi Pribadi</h5>
                </div>
                <div class="card-body">
                    <div class="info-row-item">
                        <i class="bi bi-person-fill"></i>
                        <div>
                            <span class="info-label">Nama Lengkap</span>
                            <span class="info-value">{{ $pelanggan->nama }}</span>
                        </div>
                    </div>
                    <div class="info-row-item">
                        <i class="bi bi-telephone-fill"></i>
                        <div>
                            <span class="info-label">No HP</span>
                            <span class="info-value">{{ $pelanggan->no_hp ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="info-row-item">
                        <i class="bi bi-envelope-fill"></i>
                        <div>
                            <span class="info-label">Email</span>
                            <span class="info-value">{{ $pelanggan->email ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="info-row-item">
                        <i class="bi bi-geo-alt-fill"></i>
                        <div>
                            <span class="info-label">Alamat</span>
                            <span class="info-value">{{ $pelanggan->alamat ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-card-custom card shadow-sm mt-4 flex-grow-1">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-tools me-2"></i>Aksi</h5>
                    <p class="card-text">Hanya data pribadi pelanggan yang dapat diubah. Riwayat pembelian tidak dapat diubah.</p>
                    <a href="{{ route('pelanggan.edit', $pelanggan->id_pelanggan) }}" class="btn btn-action-edit w-100">
                        <i class="bi bi-pencil-square me-2"></i>Edit Data Pelanggan
                    </a>
                    
                    <a href="{{ route('pelanggan.index') }}" class="btn btn-action-back w-100 mt-2">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="riwayat-card-custom card shadow-sm d-flex flex-column h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-basket-fill me-2"></i>Riwayat Pembelian</h5>
                </div>
                
                <div class="riwayat-search-bar">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="riwayatSearchInput" class="form-control" placeholder="Cari riwayat produk...">
                    </div>
                </div>

                <div class="riwayat-list-container flex-grow-1">
                    <ul class="list-group list-group-flush" id="riwayatList">
                        
                        @forelse ($riwayat as $item)
                            <li class="list-group-item riwayat-item">
                                <div class="riwayat-item-header">
                                    <span class="riwayat-produk-nama">{{ $item['nama_produk'] }}</span>
                                    <span class="riwayat-harga">
                                        {{ 'Rp ' . number_format($item['subtotal'], 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="riwayat-item-body">
                                    <span>
                                        <i class="bi bi-calendar-event me-1"></i>
                                        {{ $item['tanggal'] != '-' ? \Carbon\Carbon::parse($item['tanggal'])->format('d M Y') : '-' }}
                                    </span>
                                    <span>
                                        <i class="bi bi-box me-1"></i>
                                        Jumlah: {{ $item['jumlah'] }}
                                    </span>
                                    
                                    @if ($item['garansi'] > 0 && $item['tanggal_akhir'] !== null)
                                        @if ($item['sisa_hari'] > 0)
                                            <span class="text-success">
                                                <i class="bi bi-shield-check me-1"></i>
                                                Garansi s.d. {{ \Carbon\Carbon::parse($item['tanggal_akhir'])->format('d M Y') }}
                                            </span>
                                        @else
                                            <span class="text-danger">
                                                <i class="bi bi-shield-x me-1"></i>
                                                Garansi telah berakhir
                                            </span>
                                        @endif
                                    @else
                                     <span class="text-muted">
                                        <i class="bi bi-shield-slash me-1"></i> 
                                        Tanpa Garansi
                                    </span>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted p-4" id="riwayatEmptyState">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0">Tidak ada riwayat pembelian</p>
                            </li>
                        @endempty
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Memuat file JS eksternal yang baru --}}
<script src="/js/pelanggan/show.js"></script>
@endpush