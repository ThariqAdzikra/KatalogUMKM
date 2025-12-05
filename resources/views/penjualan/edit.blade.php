@extends('layouts.app')

@section('title', 'Edit Penjualan - Laptop Store')

@push('styles')
<link rel="stylesheet" href="/css/manajemen/style.css">
<link rel="stylesheet" href="/css/manajemen/responsive.css">
{{-- optional: pakai select2 biar konsisten --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush

@section('content')
<div class="container py-4">
    <div class="form-container mt-4">
        <div class="form-card">
            <div class="form-header">
                <h1 class="form-title">
                    <i class="bi bi-pencil-square me-2"></i>Edit Penjualan
                </h1>
                <p class="form-subtitle">Perbarui informasi transaksi penjualan kepada pelanggan</p>
            </div>

            <form action="{{ route('penjualan.update', $penjualan->id_penjualan) }}" method="POST" id="form-edit-penjualan">
                @csrf
                @method('PUT')

                {{-- Informasi Penjualan --}}
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="bi bi-info-circle"></i>Informasi Penjualan
                    </h3>

                    <div class="produk-row">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">
                                    Pelanggan <span class="required">*</span>
                                </label>
                                <select name="id_pelanggan" class="form-select @error('id_pelanggan') is-invalid @enderror" required>
                                    <option value="">-- Pilih Pelanggan --</option>
                                    @foreach($pelanggan as $p)
                                        <option value="{{ $p->id_pelanggan }}" {{ $p->id_pelanggan == old('id_pelanggan', $penjualan->id_pelanggan) ? 'selected' : '' }}>
                                            {{ $p->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_pelanggan')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">
                                    Tanggal Penjualan <span class="required">*</span>
                                </label>
                                <input type="datetime-local" 
                                       name="tanggal_penjualan"
                                       class="form-control @error('tanggal_penjualan') is-invalid @enderror"
                                       value="{{ old('tanggal_penjualan', \Illuminate\Support\Str::of($penjualan->tanggal_penjualan)->replace(' ', 'T')) }}"
                                       required>
                                @error('tanggal_penjualan')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">
                                    Metode Pembayaran <span class="required">*</span>
                                </label>
                                <select name="metode_pembayaran" class="form-select @error('metode_pembayaran') is-invalid @enderror" required>
                                    <option value="">-- Pilih Metode --</option>
                                    <option value="cash"     {{ old('metode_pembayaran', $penjualan->metode_pembayaran) == 'cash' ? 'selected' : '' }}>跳 Cash</option>
                                    <option value="transfer" {{ old('metode_pembayaran', $penjualan->metode_pembayaran) == 'transfer' ? 'selected' : '' }}>嘗 Transfer Bank</option>
                                    <option value="qris"     {{ old('metode_pembayaran', $penjualan->metode_pembayaran) == 'qris' ? 'selected' : '' }}>導 QRIS</option>
                                </select>
                                @error('metode_pembayaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Daftar Produk --}}
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="bi bi-box-seam"></i>Daftar Produk
                    </h3>

                    <div id="produk-wrapper">
                        @foreach($penjualan->detail as $detail)
                        <div class="produk-row">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <div class="produk-label-wrapper d-flex justify-content-between">
                                        <label class="form-label">Produk <span class="required">*</span></label>
                                        <small class="text-muted stok-info"></small>
                                    </div>
                                    <select name="produk[]" class="form-select produk-select" required>
                                        <option value="">-- Pilih Produk --</option>
                                        @foreach($produk as $pr)
                                            <option 
                                                value="{{ $pr->id_produk }}" 
                                                data-harga="{{ $pr->harga_jual }}" 
                                                data-stok="{{ $pr->stok }}" 
                                                data-nama="{{ $pr->nama_produk }}"
                                                {{ $pr->id_produk == $detail->id_produk ? 'selected' : '' }}>
                                                {{ $pr->nama_produk }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Jumlah <span class="required">*</span></label>
                                    <input type="number" name="jumlah[]" class="form-control jumlah-input" min="1" value="{{ $detail->jumlah }}" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Harga Satuan <span class="required">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-icon">Rp</span>
                                        {{-- visible display (readonly) --}}
                                        <input type="text" class="form-control harga-display" 
                                               value="{{ number_format($detail->harga_satuan, 0, ',', '.') }}" 
                                               placeholder="0" readonly>
                                        {{-- hidden real value submitted --}}
                                        <input type="hidden" name="harga_satuan[]" class="harga-input" 
                                               value="{{ $detail->harga_satuan }}">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <button type="button" class="btn btn-remove remove-row w-100">
                                        <i class="bi bi-trash me-2"></i>Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <button type="button" id="add-row" class="btn btn-add-row">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Produk
                    </button>
                </div>

                <div class="d-flex justify-content-between gap-3 mt-4">
                    <a href="{{ route('penjualan.index') }}" class="btn btn-secondary-custom">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                    <button type="submit" class="btn btn-primary-custom" id="openConfirmEditBtn">
                        <i class="bi bi-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- jQuery + select2 (optional, untuk UX) --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- Memuat script eksternal untuk halaman ini --}}
<script src="/js/penjualan/edit.js"></script>

<script>
  (function(){
    const form = document.getElementById('form-edit-penjualan');
    const modalEl = document.getElementById('confirmEditPenjualanModal');
    const confirmBtn = document.getElementById('confirmEditPenjualanBtn');
    let submitting = false;

    const openBtn = document.getElementById('openConfirmEditBtn');
    if (openBtn) {
      openBtn.addEventListener('click', function(e){
        if (!form) return;
        e.preventDefault();
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
      });
    }

    if (form) {
      form.addEventListener('submit', function(e){
        // jika submit via enter, tetap tampilkan modal
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
        localStorage.setItem('penjualan_edit_success', (data && data.message) || 'Data penjualan berhasil diperbarui');
        window.location.reload();
      })
      .catch(err => {
        modal.hide();
        const em = document.getElementById('editPenjualanErrorModal');
        const msgEl = document.getElementById('editPenjualanErrorMessage');
        if (msgEl) msgEl.textContent = (err && (err.message || err.error || err.errors || typeof err === 'string' && err) ) || 'Gagal menyimpan perubahan. Periksa input Anda.';
        if (em) bootstrap.Modal.getOrCreateInstance(em).show();
      })
      .finally(() => { submitting = false; });
    }

    if (confirmBtn) {
      confirmBtn.addEventListener('click', doAjaxSave);
    }

    // tampilkan modal sukses setelah reload
    document.addEventListener('DOMContentLoaded', function(){
      const flag = localStorage.getItem('penjualan_edit_success');
      if (!flag) return;
      localStorage.removeItem('penjualan_edit_success');
      const successEl = document.getElementById('editPenjualanSuccessModal');
      if (successEl) {
        const msgEl = document.getElementById('editPenjualanSuccessMessage');
        if (msgEl) msgEl.textContent = flag;
        bootstrap.Modal.getOrCreateInstance(successEl).show();
      }
    });
  })();
</script>
@endpush

{{-- Modal Konfirmasi Edit Penjualan --}}
<div class="modal fade modal-confirmation-style" id="confirmEditPenjualanModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm-custom">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="modal-icon-wrapper">
            <i class="bi bi-pencil-square"></i>
        </div>
        <span class="modal-title-text">Konfirmasi Edit</span>
        <p class="modal-desc-text">
            Apakah Anda yakin ingin menyimpan perubahan pada data penjualan ini?
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-modal-action btn-cancel-soft" data-bs-dismiss="modal">
            <i class="bi bi-x-lg"></i> Batal
        </button>
        <button type="button" id="confirmEditPenjualanBtn" class="btn btn-modal-action btn-primary-custom">
            <i class="bi bi-save-fill"></i> Simpan
        </button>
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

{{-- Modal Error (seragam) --}}
<div class="modal fade" id="editPenjualanErrorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-exclamation-octagon me-2 text-danger"></i>Terjadi Kesalahan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="editPenjualanErrorMessage" class="mb-0">Terjadi kesalahan. Coba lagi.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

{{-- Modal Sukses Edit (Check icon) --}}
<div class="modal fade modal-confirmation-style" id="editPenjualanSuccessModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="modal-icon-wrapper success mx-auto mb-2">
            <i class="bi bi-check-lg"></i>
        </div>
        <h5 class="modal-title-text mt-3">Berhasil</h5>
        <p class="modal-desc-text mt-2" id="editPenjualanSuccessMessage">Data penjualan berhasil diperbarui</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-modal-action btn-cancel-soft" data-bs-dismiss="modal">
            <i class="bi bi-check2 me-2"></i>OK
        </button>
      </div>
    </div>
  </div>
</div>