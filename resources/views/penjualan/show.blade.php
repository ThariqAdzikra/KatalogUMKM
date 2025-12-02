@extends('layouts.app')

@section('title', 'Detail Penjualan')

@push('styles')
{{-- Gunakan CSS yang sama dengan detail produk --}}
<link rel="stylesheet" href="/css/detail_produk/style.css">
@endpush

@section('content')
<div class="container py-4">
    <div class="detail-container">
        <div class="detail-card">
            <div class="detail-header">
                <h1 class="detail-title">
                    <i class="bi bi-cash-coin me-2"></i>Detail Penjualan
                </h1>
                <p class="detail-subtitle">Informasi lengkap transaksi penjualan</p>
            </div>

            <div class="detail-body">
                
                {{-- Baris Info Penjualan dan Ringkasan --}}
                <div class="row g-4 mb-4">

                    {{-- KOLOM 1: INFORMASI PENJUALAN --}}
                    <div class="col-lg-7">
                        <div class="detail-info-card h-100">
                            <div class="info-section">
                                <h3 class="section-title">
                                    <i class="bi bi-info-circle"></i>Informasi Transaksi
                                </h3>
                                
                                <div class="info-row">
                                    <div class="info-label">ID Penjualan</div>
                                    <div class="info-value">
                                        <code>#{{ $penjualan->id_penjualan ?? $penjualan->id }}</code>
                                    </div>
                                </div>

                                <div class="info-row">
                                    <div class="info-label">Tanggal Penjualan</div>
                                    <div class="info-value">
                                        <i class="bi bi-calendar-check me-2"></i>
                                        {{ \Carbon\Carbon::parse($penjualan->tanggal_penjualan)->format('d M Y, H:i') }}
                                    </div>
                                </div>
            
                                <div class="info-row">
                                    <div class="info-label">Pelanggan</div>
                                    <div class="info-value">
                                        <span class="badge-merk show-page">
                                            <i class="bi bi-person-fill me-2"></i>
                                            {{ $penjualan->pelanggan->nama ?? '-' }}
                                        </span>
                                    </div>
                                </div>
            
                                <div class="info-row">
                                    <div class="info-label">Metode Pembayaran</div>
                                    <div class="info-value">
                                        @php
                                            $metodeBadge = [
                                                'cash' => ['color' => '#28a745', 'icon' => 'cash-stack'],
                                                'transfer' => ['color' => '#0d6efd', 'icon' => 'bank'],
                                                'qris' => ['color' => '#fd7e14', 'icon' => 'qr-code']
                                            ];
                                            $metode = $metodeBadge[$penjualan->metode_pembayaran] ?? ['color' => '#6c757d', 'icon' => 'credit-card'];
                                        @endphp
                                        <span class="badge-merk show-page" style="background-color: {{ $metode['color'] }}20; color: {{ $metode['color'] }}; border: 1px solid {{ $metode['color'] }};">
                                            <i class="bi bi-{{ $metode['icon'] }} me-2"></i>
                                            {{ strtoupper($penjualan->metode_pembayaran) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="info-row">
                                    <div class="info-label">Kasir</div>
                                    <div class="info-value">
                                        <i class="bi bi-person-badge-fill me-2"></i>
                                        {{ $penjualan->user->name ?? 'Tidak Diketahui' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- KOLOM 2: RINGKASAN PENJUALAN --}}
                    <div class="col-lg-5">
                        <div class="detail-info-card h-100">
                            <div class="info-section">
                                <h3 class="section-title">
                                    <i class="bi bi-calculator"></i>Ringkasan Pembayaran
                                </h3>
            
                                <div class="info-row">
                                    <div class="info-label">Total Item</div>
                                    <div class="info-value">
                                        <span style="font-size: 2rem; font-weight: 700; color: var(--primary-wood);">
                                            {{ $penjualan->detail->count() }}
                                        </span>
                                        <span class="text-muted">Jenis Barang</span>
                                    </div>
                                </div>

                                <div class="info-row">
                                    <div class="info-label">Total Jumlah</div>
                                    <div class="info-value">
                                        <span style="font-size: 1.5rem; font-weight: 600; color: var(--primary-wood);">
                                            {{ $penjualan->detail->sum('jumlah') }}
                                        </span>
                                        <span class="text-muted">Unit</span>
                                    </div>
                                </div>
            
                                <div class="info-row">
                                    <div class="info-label">Total Harga</div>
                                    <div class="info-value">
                                        <span class="price-highlight" style="color: var(--primary-wood); font-size: 1.75rem;">
                                            Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Detail Barang (Full-Width) --}}
                <div class="info-section info-section-fullwidth">
                    <h3 class="section-title">
                        <i class="bi bi-bag-check"></i>Produk yang Terjual
                    </h3>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead style="background: linear-gradient(135deg, var(--primary-wood) 0%, var(--secondary-wood) 100%); color: white;">
                                <tr>
                                    <th style="border: none; padding: 1rem;">No</th>
                                    <th style="border: none; padding: 1rem;">Nama Produk</th>
                                    <th style="border: none; padding: 1rem;" class="text-center">Jumlah</th>
                                    <th style="border: none; padding: 1rem;" class="text-end">Harga Satuan</th>
                                    <th style="border: none; padding: 1rem;" class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($penjualan->detail as $i => $d)
                                <tr style="border-bottom: 1px solid #e0e0e0;">
                                    <td style="padding: 1rem;">{{ $i + 1 }}</td>
                                    <td style="padding: 1rem;">
                                        <strong>{{ $d->produk->nama_produk ?? '-' }}</strong>
                                    </td>
                                    <td style="padding: 1rem;" class="text-center">
                                        <span class="badge-merk show-page">{{ $d->jumlah }} Unit</span>
                                    </td>
                                    <td style="padding: 1rem;" class="text-end">
                                        Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}
                                    </td>
                                    <td style="padding: 1rem;" class="text-end">
                                        <strong style="color: var(--primary-wood);">
                                            Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                                        </strong>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">Tidak ada data produk untuk penjualan ini.</p>
                                    </td>
                                </tr>
                                @endforelse
                                <tr style="background-color: #f8f9fa; font-weight: bold; border-top: 2px solid var(--primary-wood);">
                                    <td colspan="4" style="padding: 1rem;" class="text-end">Total Keseluruhan:</td>
                                    <td style="padding: 1rem;" class="text-end">
                                        <span style="color: var(--primary-wood); font-size: 1.25rem;">
                                            Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
                            {{ $penjualan->created_at ? $penjualan->created_at->format('d M Y, H:i') : '-' }}
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Terakhir Diubah</div>
                        <div class="info-value">
                            <i class="bi bi-calendar-event me-2"></i>
                            {{ $penjualan->updated_at ? $penjualan->updated_at->format('d M Y, H:i') : '-' }}
                        </div>
                    </div>
                </div>

                {{-- TOMBOL AKSI --}}
                <div class="d-flex justify-content-between gap-3 mt-4 pt-4 border-top">
                    <a href="{{ route('penjualan.index') }}" class="btn btn-action-detail btn-back-detail">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection