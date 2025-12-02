<div class="table-card">
  <div class="table-header">
    <h3 class="table-title">
      <i class="bi bi-table me-2"></i>Daftar Produk
    </h3>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>No</th>
          <th>Gambar</th>
          <th>Produk</th>
          <th>Merk</th>
          <th>Kategori</th>
          <th>Harga Beli</th>
          <th>Harga Jual</th>
          <th>Stok</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($produk as $item)
          <tr>
            <td class="fw-semibold">{{ $loop->iteration + ($produk->currentPage() - 1) * $produk->perPage() }}</td>
            <td>
              @if ($item->gambar)
                <img
                  src="{{ asset($item->gambar) }}"
                  alt="{{ $item->nama_produk }}"
                  class="product-img-thumb"
                />
              @else
                <div class="product-img-thumb d-flex align-items-center justify-content-center bg-light">
                  <i class="bi bi-laptop" style="font-size: 1.5rem; color: #adb5bd;"></i>
                </div>
              @endif

            </td>
            <td><div class="fw-semibold">{{ $item->nama_produk }}</div></td>
            <td><span class="badge-merk">{{ $item->merk }}</span></td>
            <td>{{ $item->kategori->nama_kategori ?? '-' }}</td>
            <td class="fw-semibold" style="color: #28a745;">Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
            <td class="fw-semibold" style="color: #17a2b8;">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
            <td><span class="fs-5 fw-bold">{{ $item->stok }}</span></td>
            <td>
              @if($item->stok == 0)
              <span class="badge-stock badge-habis">
                <i class="bi bi-x-circle me-1"></i>Habis
              </span>
              @elseif($item->stok <= 5)
              <span class="badge-stock badge-menipis">
                <i class="bi bi-exclamation-triangle me-1"></i>Menipis
              </span>
              @else
              <span class="badge-stock badge-tersedia">
                <i class="bi bi-check-circle me-1"></i>Tersedia
              </span>
              @endif
            </td>
            <td>
              <div class="d-flex">
                <a href="{{ route('stok.show', $item->id_produk) }}" class="btn-action btn-detail" title="Detail">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('stok.edit', $item->id_produk) }}" class="btn-action btn-edit" title="Edit">
                  <i class="bi bi-pencil"></i>
                </a>
                <form action="{{ route('stok.destroy', $item->id_produk) }}" method="POST" class="d-inline delete-form" data-confirm-message="Yakin ingin menghapus produk ini?">
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
            <td colspan="10">
              <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h4>Tidak Ada Data Produk</h4>
                <p>Belum ada produk yang ditambahkan ke stok.</p>
                <a href="{{ route('stok.create') }}" class="btn btn-primary-custom mt-3">
                  <i class="bi bi-plus-circle me-2"></i>Tambah Produk Pertama
                </a>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($produk->hasPages())
    <div class="d-flex justify-content-center p-4">
      {{ $produk->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
  @endif
</div>
