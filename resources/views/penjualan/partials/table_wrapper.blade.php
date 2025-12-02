<div class="table-card">
  <div class="table-header d-flex justify-content-between align-items-center">
    <h3 class="table-title">
      <i class="bi bi-table me-2"></i>Daftar Penjualan
    </h3>
    <a href="{{ route('penjualan.print') }}" target="_blank" class="btn btn-outline-info btn-sm">
      <i class="bi bi-printer"></i> Cetak PDF
    </a>
  </div>

  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>No</th>
          <th>Tanggal</th>
          <th>Pelanggan</th>
          <th>Metode</th>
          <th>Total Harga</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($penjualan as $p)
        <tr>
          {{-- Penomoran yang benar --}}
          <td>{{ $loop->iteration + ($penjualan->currentPage() - 1) * $penjualan->perPage() }}</td>
          <td>{{ \Carbon\Carbon::parse($p->tanggal_penjualan)->format('d M Y H:i') }}</td>
          <td>{{ $p->pelanggan->nama ?? '-' }}</td>
          <td>
            <span class="badge 
              {{ $p->metode_pembayaran == 'cash' ? 'bg-success' : 
                ($p->metode_pembayaran == 'transfer' ? 'bg-primary' : 'bg-warning text-dark') }}">
              {{ strtoupper($p->metode_pembayaran) }}
            </span>
          </td>
          <td class="fw-semibold text-primary">
            Rp {{ number_format($p->total_harga, 0, ',', '.') }}
          </td>
          <td>
            <div class="d-flex gap-2">
              <a href="{{ route('penjualan.show', $p->id_penjualan) }}" 
                class="btn-action btn-detail"
                title="Lihat Detail">
                <i class="bi bi-eye"></i>
              </a>
              <a href="{{ route('penjualan.edit', $p->id_penjualan) }}" 
                class="btn-action btn-edit" title="Edit">
                <i class="bi bi-pencil"></i>
              </a>
              <form action="{{ route('penjualan.destroy', $p->id_penjualan) }}" 
                    method="POST" class="d-inline delete-form"
                    data-confirm-message="Yakin ingin menghapus data penjualan ini?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-action btn-delete" title="Hapus">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6">
            <div class="empty-state">
              <i class="bi bi-inbox"></i>
              <h4>Tidak Ada Transaksi Penjualan</h4>
              <p>Belum ada transaksi yang tercatat dalam sistem.</p>
            </div>
          </td>
        </tr>
        @endForelse
      </tbody>
    </table>
  </div>

  @if($penjualan->hasPages())
  <div class="d-flex justify-content-center p-4">
    {{ $penjualan->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-5') }}
  </div>
  @endif
</div>
