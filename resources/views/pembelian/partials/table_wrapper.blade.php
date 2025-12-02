<div class="table-card">
  <div class="table-header">
    <h3 class="table-title">
      <i class="bi bi-table me-2"></i>Daftar Pembelian
    </h3>
  </div>
  <div class="table-responsive">
    <table class="table table-custom">
      <thead>
        <tr>
          <th>No</th>
          <th>Tanggal</th>
          <th>Supplier</th>
          <th>Total Harga</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($pembelian as $p)
        <tr>
          <td>
            <span class="badge-number">
              {{ $loop->iteration + ($pembelian->currentPage() - 1) * $pembelian->perPage() }}
            </span>
          </td>
          <td class="fw-semibold">
            {{ \Carbon\Carbon::parse($p->tanggal_pembelian)->format('d M Y') }}
          </td>
          <td>
            <div class="fw-semibold">{{ $p->supplier->nama_supplier ?? '-' }}</div>
          </td>
          <td>
            <span class="price-highlight fs-5">
              Rp {{ number_format($p->total_harga, 0, ',', '.') }}
            </span>
          </td>
          <td>
            <div class="d-flex">
              <a href="{{ route('pembelian.show', $p->id_pembelian) }}" 
                 class="btn-action btn-info me-2"
                 title="Lihat Detail">
                <i class="bi bi-eye"></i>
              </a>
              <a href="{{ route('pembelian.edit', $p->id_pembelian) }}" 
                 class="btn-edit btn-action btn-warning"
                 title="Edit">
                <i class="bi bi-pencil"></i>
              </a>
              <form action="{{ route('pembelian.destroy', $p->id_pembelian) }}" 
                    method="POST" 
                    class="d-inline delete-form"
                    data-confirm-message="Yakin ingin menghapus data pembelian ini?">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="btn-action btn-delete"
                        title="Hapus">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5">
            <div class="empty-state">
              <i class="bi bi-inbox"></i>
              <h4>Tidak Ada Data Pembelian</h4>
              <p>Belum ada transaksi pembelian yang tercatat.</p>
              <a href="{{ route('pembelian.create') }}" class="btn-add mt-3">
                <i class="bi bi-plus-circle me-2"></i>Tambah Pembelian Pertama
              </a>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div> {{-- End table-responsive --}}

  {{-- Pagination inside card --}}
  @if($pembelian->hasPages())
    <div class="p-4 text-center border-top">
      {{ $pembelian->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
  @endif
</div> {{-- End table-card --}}
