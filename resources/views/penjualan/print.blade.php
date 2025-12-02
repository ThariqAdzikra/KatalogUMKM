<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Penjualan - Laptop Store</title>
    {{-- Tautkan ke file CSS eksternal --}}
    <link rel="stylesheet" href="/css/manajemen/print.css">
</head>
<body>

    <div class="header">
        <h2>Laporan Penjualan Laptop Store</h2>
        <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
    </div>

    @if($penjualan->isEmpty())
        <div class="no-data">Tidak ada data penjualan ditemukan.</div>
    @else
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Metode Pembayaran</th>
                <th>Total Harga (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($penjualan as $index => $p)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($p->tanggal_penjualan)->format('d M Y H:i') }}</td>
                <td>{{ $p->pelanggan->nama ?? '-' }}</td>
                <td>{{ strtoupper($p->metode_pembayaran) }}</td>
                <td class="text-right">{{ number_format($p->total_harga, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">Total Keseluruhan</td>
                <td class="text-right">{{ number_format($total_semua, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- Tautkan ke file JS eksternal --}}
    <script src="/js/penjualan/print.js"></script>

</body>
</html>