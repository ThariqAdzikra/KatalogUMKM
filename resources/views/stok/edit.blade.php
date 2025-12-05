@extends('layouts.app')

@section('title', 'Detail Stok - ' . App\Models\SiteSetting::get('brand_name'))

@push('styles')
    {{-- CSS Global Manajemen --}}
    <link rel="stylesheet" href="/css/manajemen/style.css">
    <link rel="stylesheet" href="/css/manajemen/responsive.css">
    {{-- CSS Khusus Edit Stok (Animasi Modal) --}}
    <link rel="stylesheet" href="/css/manajemen/stok-edit.css">
@endpush

{{-- Modal Error (Global di halaman ini) --}}
<div class="modal fade" id="editStokErrorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-exclamation-octagon me-2 text-danger"></i>Terjadi Kesalahan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="editStokErrorMessage" class="mb-0">Gagal menyimpan perubahan. Periksa input Anda.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
 </div>

@section('content')
<div class="container py-4">
    <div class="form-container">
        <div class="form-card">
            <div class="form-header">
                <h1 class="form-title">
                    <i class="bi bi-pencil-square me-2"></i>Edit Produk
                </h1>
                <p class="form-subtitle">Perbarui informasi produk: <strong>{{ $stok->nama_produk }}</strong></p>
            </div>

            <form action="{{ route('stok.update', $stok->id_produk) }}" method="POST" enctype="multipart/form-data" id="form-edit-stok">
                @csrf
                @method('PUT')

                {{-- Informasi Produk --}}
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="bi bi-info-circle"></i>Informasi Produk
                    </h3>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                Nama Produk <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="nama_produk" 
                                   class="form-control @error('nama_produk') is-invalid @enderror" 
                                   value="{{ old('nama_produk', $stok->nama_produk) }}"
                                   placeholder="Contoh: ASUS ROG Strix G15"
                                   required>
                            @error('nama_produk')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Merk <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="merk" 
                                   class="form-control @error('merk') is-invalid @enderror" 
                                   value="{{ old('merk', $stok->merk) }}"
                                   placeholder="Contoh: ASUS"
                                   required>
                            @error('merk')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">
                                Kategori <span class="required">*</span>
                            </label>
                            <select name="id_kategori" class="form-select @error('id_kategori') is-invalid @enderror" required>
                                <option value="" disabled>-- Pilih Kategori --</option>
                                @foreach($kategori as $kat)
                                    <option value="{{ $kat->id_kategori }}" 
                                        {{ old('id_kategori', $stok->id_kategori) == $kat->id_kategori ? 'selected' : '' }}>
                                        {{ $kat->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Spesifikasi <span class="required">*</span>
                            </label>
                            <textarea name="spesifikasi" 
                                      rows="4" 
                                      class="form-control @error('spesifikasi') is-invalid @enderror" 
                                      placeholder="Contoh: Intel Core i7-11800H, RTX 3060, 16GB RAM, 512GB SSD, 15.6 FHD 144Hz"
                                      required>{{ old('spesifikasi', $stok->spesifikasi) }}</textarea>
                            @error('spesifikasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Harga dan Stok --}}
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="bi bi-currency-dollar"></i>Harga & Stok
                    </h3>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">
                                Harga Beli <span class="required">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text input-group-icon">Rp</span>
                                <input type="number" 
                                       name="harga_beli" 
                                       class="form-control @error('harga_beli') is-invalid @enderror" 
                                       value="{{ old('harga_beli', $stok->harga_beli) }}"
                                       placeholder="0"
                                       min="0"
                                       required>
                            </div>
                            @error('harga_beli')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Harga Jual <span class="required">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text input-group-icon">Rp</span>
                                <input type="number" 
                                       name="harga_jual" 
                                       class="form-control @error('harga_jual') is-invalid @enderror" 
                                       value="{{ old('harga_jual', $stok->harga_jual) }}"
                                       placeholder="0"
                                       min="0"
                                       required>
                            </div>
                            @error('harga_jual')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Stok <span class="required">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text input-group-icon">
                                    <i class="bi bi-box"></i>
                                </span>
                                <input type="number" 
                                       name="stok" 
                                       class="form-control @error('stok') is-invalid @enderror" 
                                       value="{{ old('stok', $stok->stok) }}"
                                       placeholder="0"
                                       min="0"
                                       required>
                            </div>
                            @error('stok')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Gambar Produk --}}
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="bi bi-image"></i>Gambar Produk
                    </h3>
                    
                    <div class="row">
                        <div class="col-12">
                            @if($stok->gambar)
                            <div class="mb-3">
                                <span class="badge-info">
                                    <i class="bi bi-image me-2"></i>Gambar Saat Ini
                                </span>
                                <div class="image-preview-container mt-2">
                                    {{-- ✅ FIX: Menghapus 'storage/' karena di DB sudah lengkap --}}
                                    <img src="{{ asset($stok->gambar) }}" alt="Current Image">
                                </div>
                            </div>
                            @endif

                            <label class="form-label">
                                Upload Gambar Baru (Opsional)
                            </label>
                            <input type="file" 
                                   name="gambar" 
                                   class="form-control @error('gambar') is-invalid @enderror"
                                   accept="image/*"
                                   id="imageInput"
                                   onchange="previewImage(event)">
                            <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah gambar.</small>
                            @error('gambar')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="image-preview-container mt-2" id="imagePreview" style="display: none;">
                                <div class="image-placeholder">
                                    <i class="bi bi-image"></i>
                                    <p>Preview gambar baru</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-between gap-3 mt-4">
                    <a href="{{ route('stok.index') }}" class="btn btn-secondary-custom">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                    <button type="button" class="btn btn-primary-custom" id="openConfirmEditStokBtn">
                        <i class="bi bi-save me-2"></i>Update Produk
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Edit --}}
<div class="modal fade modal-confirmation-style" id="confirmEditStokModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm-custom">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="modal-icon-wrapper">
            <i class="bi bi-pencil-square" style="color: #0d6efd;"></i>
        </div>
        <span class="modal-title-text">Konfirmasi Edit</span>
        <p class="modal-desc-text">
            Apakah Anda yakin ingin menyimpan perubahan pada data produk ini?
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-modal-action btn-cancel-soft" data-bs-dismiss="modal">
            <i class="bi bi-x-lg"></i> Batal
        </button>
        <button type="button" id="confirmEditStokBtn" class="btn btn-modal-action btn-primary-custom">
            <i class="bi bi-save"></i> Simpan
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Modal Sukses Edit (Check icon) --}}
<div class="modal fade modal-confirmation-style" id="editStokSuccessModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="anim-check mx-auto mb-2" aria-hidden="true">
          <svg viewBox="0 0 120 120" width="100" height="100">
            <circle class="check-circle" cx="60" cy="60" r="42" fill="none" stroke="#198754" stroke-width="6" />
            <path class="check-mark" d="M38 62 L54 76 L84 46" fill="none" stroke="#198754" stroke-linecap="round" stroke-linejoin="round" stroke-width="6" />
          </svg>
        </div>
        <h5 class="modal-title-text mt-3">Berhasil</h5>
        <p id="editStokSuccessMessage" class="modal-desc-text mt-2">Data produk berhasil diperbarui!</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-modal-action btn-cancel-soft" data-bs-dismiss="modal">
            <i class="bi bi-check2"></i> OK
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
{{-- Memanggil Logic JS Eksternal --}}
<script src="/js/stok/image-preview.js"></script>
<script src="/js/stok/stok-edit.js"></script>
@endpush