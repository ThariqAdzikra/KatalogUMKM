@extends('layouts.app')

@section('title', 'Edit Pembelian - Laptop Store')

@push('styles')
<link rel="stylesheet" href="/css/manajemen/style.css">
@endpush

@section('content')
<div class="container py-4">
    <div class="form-container mt-4">
        <div class="form-card">
            <div class="form-header">
                <h1 class="form-title">
                    <i class="bi bi-pencil-square me-2"></i>Edit Pembelian
                </h1>
                <p class="form-subtitle">Perbarui informasi pembelian dari supplier</p>
            </div>

            <form action="{{ route('pembelian.update', $pembelian->id_pembelian) }}" method="POST" id="form-edit-pembelian">
                @csrf
                @method('PUT')

                {{-- Informasi Pembelian --}}
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="bi bi-info-circle"></i>Informasi Pembelian
                    </h3>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                Supplier <span class="required">*</span>
                            </label>
                            <select name="id_supplier" class="form-select @error('id_supplier') is-invalid @enderror" required>
                                @foreach($supplier as $s)
                                    <option value="{{ $s->id_supplier }}" {{ $s->id_supplier == old('id_supplier', $pembelian->id_supplier) ? 'selected' : '' }}>
                                        {{ $s->nama_supplier }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_supplier')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Tanggal Pembelian <span class="required">*</span>
                            </label>
                            <input type="date" 
                                   name="tanggal_pembelian" 
                                   class="form-control @error('tanggal_pembelian') is-invalid @enderror" 
                                   value="{{ old('tanggal_pembelian', $pembelian->tanggal_pembelian) }}"
                                   required>
                            @error('tanggal_pembelian')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Daftar Produk --}}
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="bi bi-box-seam"></i>Daftar Produk
                    </h3>

                    <div id="produk-wrapper">
                        {{-- Gunakan @forelse untuk menangani detail yang ada atau tampilkan baris kosong jika tidak ada --}}
                        @forelse($pembelian->detail as $index => $detail)
                        <div class="produk-row">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Produk <span class="required">*</span></label>
                                    {{-- Menggunakan nama array terstruktur --}}
                                    <select name="produk[{{ $index }}][id_produk]" class="form-select product-select" required>
                                        <option value="">-- Pilih Produk --</option>
                                        @foreach($produk as $p)
                                            <option value="{{ $p->id_produk }}" {{ $p->id_produk == $detail->id_produk ? 'selected' : '' }}>
                                                {{ $p->nama_produk }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Jumlah <span class="required">*</span></label>
                                    <input type="number" 
                                           name="produk[{{ $index }}][jumlah]" 
                                           class="form-control quantity-input" 
                                           value="{{ $detail->jumlah }}" 
                                           min="1" 
                                           required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Harga Satuan <span class="required">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-icon">Rp</span>
                                        <input type="number" 
                                               step="0.01" 
                                               name="produk[{{ $index }}][harga_satuan]" 
                                               class="form-control price-input" 
                                               value="{{ $detail->harga_satuan }}" 
                                               min="0" 
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-remove remove-row">
                                        <i class="bi bi-trash me-2"></i>Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                        @empty
                        {{-- Jika tidak ada detail, tampilkan satu baris kosong --}}
                        <div class="produk-row">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Produk <span class="required">*</span></label>
                                    <select name="produk[0][id_produk]" class="form-select product-select" required>
                                        <option value="" selected>-- Pilih Produk --</option>
                                        @foreach($produk as $p)
                                            <option value="{{ $p->id_produk }}">{{ $p->nama_produk }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Jumlah <span class="required">*</span></label>
                                    <input type="number" name="produk[0][jumlah]" class="form-control quantity-input" value="1" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Harga Satuan <span class="required">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-icon">Rp</span>
                                        <input type="number" step="0.01" name="produk[0][harga_satuan]" class="form-control price-input" value="0" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-remove remove-row">
                                        <i class="bi bi-trash me-2"></i>Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforelse
                    </div>

                    <button type="button" id="add-row" class="btn btn-add-row">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Produk
                    </button>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-between gap-3 mt-4">
                    <a href="{{ route('pembelian.index') }}" class="btn btn-secondary-custom">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>

            {{-- Template untuk baris produk baru (jauh lebih aman daripada kloning) --}}
            <template id="produk-row-template">
                <div class="produk-row">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Produk <span class="required">*</span></label>
                            {{-- Nama akan diatur oleh JS --}}
                            <select class="form-select product-select" required> 
                                <option value="" selected>-- Pilih Produk --</option>
                                @foreach($produk as $p)
                                    {{-- Pastikan semua produk tersedia di template --}}
                                    <option value="{{ $p->id_produk }}">{{ $p->nama_produk }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Jumlah <span class="required">*</span></label>
                            <input type="number" class="form-control quantity-input" value="1" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Harga Satuan <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-icon">Rp</span>
                                <input type="number" step="0.01" class="form-control price-input" value="0" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-remove remove-row">
                                <i class="bi bi-trash me-2"></i>Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </template>

        </div>
    </div>
</div>
@endsection

{{-- Modal Konfirmasi Edit --}}
<div class="modal fade" id="confirmEditModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Konfirmasi Edit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menyimpan perubahan pada data pembelian ini?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bi bi-x-circle me-2"></i>Batal</button>
        <button type="button" id="confirmEditBtn" class="btn btn-primary-custom"><i class="bi bi-save me-2"></i>Simpan</button>
      </div>
    </div>
  </div>
</div>

{{-- Modal Sukses Edit (Check icon) --}}
<div class="modal fade" id="editSuccessModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-check-circle me-2 icon-animate-pop"></i>
          Berhasil
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="anim-check mx-auto mb-2" aria-hidden="true">
          <svg viewBox="0 0 120 120" width="100" height="100">
            <circle class="check-circle" cx="60" cy="60" r="42" fill="none" stroke="#198754" stroke-width="6" />
            <path class="check-mark" d="M38 62 L54 76 L84 46" fill="none" stroke="#198754" stroke-linecap="round" stroke-linejoin="round" stroke-width="6" />
          </svg>
        </div>
        <p id="editSuccessMessage" class="mt-2">Data pembelian berhasil diperbarui</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal"><i class="bi bi-check2 me-2"></i>OK</button>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
  .icon-animate-pop { animation: popIn 400ms ease both; }
  @keyframes popIn { 0% { transform: scale(0.6); opacity: 0; } 60% { transform: scale(1.15); opacity: 1; } 100% { transform: scale(1); } }
  .anim-check .check-circle { stroke-dasharray: 265; stroke-dashoffset: 265; animation: circle-draw 900ms ease-out forwards; }
  .anim-check .check-mark { stroke-dasharray: 80; stroke-dashoffset: 80; animation: check-draw 700ms 350ms ease-out forwards; }
  @keyframes circle-draw { to { stroke-dashoffset: 0; } }
  @keyframes check-draw { to { stroke-dashoffset: 0; } }
</style>
@endpush

@push('scripts')
<script>
    // Mengirim data produk dari PHP ke JavaScript
    // Kita asumsikan $produk memiliki properti 'harga_beli' sebagai harga satuan pembelian.
    // Ganti 'harga_beli' jika nama field di model Produk Anda berbeda.
    const allProducts = @json($produk->mapWithKeys(function ($p) {
        return [$p->id_produk => [
            'harga_satuan' => $p->harga_beli ?? 0 
        ]];
    }));

    // Variabel ini untuk memastikan baris baru memiliki 'index' unik
    // Dimulai dari jumlah item yang sudah ada
    let newRowIndex = {{ $pembelian->detail->count() }};
</script>

{{-- Memuat file JS eksternal yang baru --}}
<script src="/js/pembelian/edit.js"></script>

<script>
  (function(){
    const form = document.getElementById('form-edit-pembelian');
    const modalEl = document.getElementById('confirmEditModal');
    const confirmBtn = document.getElementById('confirmEditBtn');
    let submitting = false;

    if (form) {
      form.addEventListener('submit', function(e){
        e.preventDefault();
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
      });
    }

    function doAjaxSave(){
      if (!form || submitting) return;
      if (!form.checkValidity()) { form.reportValidity(); return; }
      submitting = true;
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

      const action = form.getAttribute('action');
      const fd = new FormData(form);
      fd.set('_method', 'PUT');
      const csrf = (form.querySelector('input[name="_token"]')?.value) || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

      fetch(action, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf,
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: new URLSearchParams([...fd])
      })
      .then(r => r.ok ? r.json() : r.json().then(j => Promise.reject(j)))
      .then(data => {
        modal.hide();
        localStorage.setItem('pembelian_edit_success', (data && data.message) || 'Data pembelian berhasil diperbarui');
        window.location.reload();
      })
      .catch(err => {
        modal.hide();
        alert((err && err.message) || 'Gagal menyimpan perubahan. Periksa input Anda.');
      })
      .finally(() => { submitting = false; });
    }

    if (confirmBtn) {
      confirmBtn.addEventListener('click', doAjaxSave);
    }

    // Tampilkan modal sukses setelah reload
    document.addEventListener('DOMContentLoaded', function(){
      const flag = localStorage.getItem('pembelian_edit_success');
      if (!flag) return;
      localStorage.removeItem('pembelian_edit_success');
      const successEl = document.getElementById('editSuccessModal');
      if (successEl) {
        const msgEl = document.getElementById('editSuccessMessage');
        if (msgEl) msgEl.textContent = flag;
        bootstrap.Modal.getOrCreateInstance(successEl).show();
      }
    });
  })();
</script>
@endpush

{{-- Modal Konfirmasi Edit --}}
<div class="modal fade" id="confirmEditModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Konfirmasi Edit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menyimpan perubahan pada data pembelian ini?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bi bi-x-circle me-2"></i>Batal</button>
        <button type="button" id="confirmEditBtn" class="btn btn-primary-custom"><i class="bi bi-save me-2"></i>Simpan</button>
      </div>
    </div>
  </div>
</div>