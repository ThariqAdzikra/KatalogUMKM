@extends('layouts.app')

@section('title', 'Detail Pembelian')

@push('styles')
{{-- Gunakan CSS yang sama dengan detail produk --}}
<link rel="stylesheet" href="/css/detail_produk/style.css">
<link rel="stylesheet" href="/css/manajemen/penjualan.css">
<link rel="stylesheet" href="/css/manajemen/pembelian-responsive.css">
@endpush

@section('content')
<div class="container py-4">
    <div class="detail-container">
        <div class="detail-card">
            <div class="detail-header">
                <h1 class="detail-title">
                    <i class="bi bi-receipt-cutoff me-2"></i>Detail Pembelian
                </h1>
                <p class="detail-subtitle">Informasi lengkap transaksi pembelian</p>
            </div>

            <div class="detail-body">
                
                {{-- Baris Info Pembelian dan Ringkasan --}}
                <div class="row g-4 mb-4">

                    {{-- KOLOM 1: INFORMASI PEMBELIAN --}}
                    <div class="col-lg-7">
                        <div class="detail-info-card h-100">
                            <div class="info-section">
                                <h3 class="section-title">
                                    <i class="bi bi-info-circle"></i>Informasi Pembelian
                                </h3>
                                
                                <div class="info-row">
                                    <div class="info-label">ID Pembelian</div>
                                    <div class="info-value">
                                        <code>#{{ $pembelian->id_pembelian ?? $pembelian->id }}</code>
                                    </div>
                                </div>

                                <div class="info-row">
                                    <div class="info-label">Tanggal Pembelian</div>
                                    <div class="info-value">
                                        <i class="bi bi-calendar-check me-2"></i>
                                        {{ \Carbon\Carbon::parse($pembelian->tanggal_pembelian)->format('d M Y') }}
                                    </div>
                                </div>
            
                                <div class="info-row">
                                    <div class="info-label">Supplier</div>
                                    <div class="info-value">
                                        <span class="badge-merk show-page">
                                            {{ $pembelian->supplier->nama_supplier ?? '-' }}
                                        </span>
                                    </div>
                                </div>
            
                                <div class="info-row">
                                    <div class="info-label">Pegawai</div>
                                    <div class="info-value">
                                        <i class="bi bi-person-fill me-2"></i>
                                        {{ $pembelian->user->name ?? 'Tidak Diketahui' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- KOLOM 2: RINGKASAN PEMBELIAN --}}
                    <div class="col-lg-5">
                        <div class="detail-info-card h-100">
                            <div class="info-section">
                                <h3 class="section-title">
                                    <i class="bi bi-calculator"></i>Ringkasan
                                </h3>
            
                                <div class="info-row">
                                    <div class="info-label">Total Item</div>
                                    <div class="info-value">
                                        <span style="font-size: 2rem; font-weight: 700; color: var(--primary-wood);">
                                            {{ $pembelian->detail->count() }}
                                        </span>
                                        <span class="text-muted">Jenis Barang</span>
                                    </div>
                                </div>

                                <div class="info-row">
                                    <div class="info-label">Total Jumlah</div>
                                    <div class="info-value">
                                        <span style="font-size: 1.5rem; font-weight: 600; color: var(--primary-wood);">
                                            {{ $pembelian->detail->sum('jumlah') }}
                                        </span>
                                        <span class="text-muted">Unit</span>
                                    </div>
                                </div>
            
                                <div class="info-row">
                                    <div class="info-label">Total Harga</div>
                                    <div class="info-value">
                                        <span class="price-highlight" style="color: var(--primary-wood); font-size: 1.75rem;">
                                            Rp {{ number_format($pembelian->total_harga, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Detail Barang (Full-Width) --}}
                <div class="detail-info-card mb-4">
                    <div class="info-section mb-0">
                        <h3 class="section-title">
                            <i class="bi bi-box-seam"></i>Detail Barang
                        </h3>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-dark-theme">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Barang</th>
                                        <th class="text-center text-nowrap">Jumlah</th>
                                        <th class="text-end">Harga Satuan</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pembelian->detail as $i => $detail)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            <strong>{{ $detail->produk->nama_produk ?? '-' }}</strong>
                                        </td>
                                        <td class="text-center text-nowrap">
                                            <span class="badge-merk show-page">{{ $detail->jumlah }} Unit</span>
                                        </td>
                                        <td class="text-end">
                                            Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-primary-wood">
                                                Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                            </strong>
                                        </td>
                                    </tr>
                                    @endforeach
                                    <tr class="table-row-total">
                                        <td colspan="4" class="text-end">Total Keseluruhan:</td>
                                        <td class="text-end">
                                            <span class="text-total-price">
                                                Rp {{ number_format($pembelian->total_harga, 0, ',', '.') }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Riwayat --}}
                <div class="info-section info-section-fullwidth">
                    <h3 class="section-title">
                        <i class="bi bi-clock-history"></i>Riwayat
                    </h3>

                    <div class="info-row">
                        <div class="info-label">Dibuat Pada</div>
                        <div class="info-value">
                            <i class="bi bi-calendar-check me-2"></i>
                            {{ $pembelian->created_at ? $pembelian->created_at->format('d M Y, H:i') : '-' }}
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Terakhir Diubah</div>
                        <div class="info-value">
                            <i class="bi bi-calendar-event me-2"></i>
                            {{ $pembelian->updated_at ? $pembelian->updated_at->format('d M Y, H:i') : '-' }}
                        </div>
                    </div>
                </div>

                {{-- TOMBOL AKSI --}}
                <div class="d-flex justify-content-between gap-3 mt-4 pt-4 border-top">
                    <a href="{{ route('pembelian.index') }}" class="btn btn-action-detail btn-back-detail">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection