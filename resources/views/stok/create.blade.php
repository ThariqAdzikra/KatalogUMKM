@extends('layouts.app')

@section('title', 'Tambah Produk - ' . App\Models\SiteSetting::get('brand_name'))

@push('styles')
<link rel="stylesheet" href="/css/manajemen/style.css">
<link rel="stylesheet" href="/css/manajemen/responsive.css">
{{-- CSS Select2 --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
    /* Styling khusus agar Select2 Kategori terlihat konsisten (Style asli) */
    .select2-container--bootstrap-5 .select2-selection--single {
        height: 38px;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="form-container">
        <div class="form-card">
            <div class="form-header">
                <h1 class="form-title">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Produk Baru
                </h1>
                <p class="form-subtitle">Lengkapi form di bawah ini untuk menambahkan produk ke stok</p>
            </div>

            {{-- Notifikasi --}}
            @if(session('info'))
            <div class="alert alert-info alert-custom" role="alert" style="border-left-color: #0dcaf0;">
                <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
            </div>
            @endif
            @if(session('success'))
            <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <form id="formProduk" action="{{ route('stok.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($id_pembelian) && $id_pembelian)
                    <input type="hidden" name="id_pembelian" value="{{ $id_pembelian }}">
                @endif
                <input type="hidden" name="id_produk_existing" id="id_produk_existing" value="">

                {{-- Informasi Produk --}}
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="bi bi-info-circle"></i>Informasi Produk
                    </h3>
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">
                                Nama Produk <span class="required">*</span>
                                <span id="stok_saat_ini_info" class="stok-info"></span>
                            </label>
                            
                            {{-- Select2 Nama Produk (Existing) --}}
                            <select name="nama_produk" 
                                    id="nama_produk_select" 
                                    class="form-select @error('nama_produk') is-invalid @enderror" 
                                    required
                                    data-placeholder="Cari atau ketik nama produk baru">
                                <option></option>
                                @foreach($semua_produk as $produk)
                                    <option value="{{ $produk->id_produk }}" {{ old('nama_produk') == $produk->nama_produk ? 'selected' : '' }}>
                                        {{ $produk->nama_produk }}
                                    </option>
                                @endforeach
                            </select>
                            
                            @error('nama_produk')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Form Merk --}}
                        <div class="col-md-4">
                            <label class="form-label">Merk <span class="required">*</span></label>
                            <input type="text" name="merk" id="merk" class="form-control @error('merk') is-invalid @enderror" value="{{ old('merk') }}" placeholder="Contoh: ASUS" required>
                            @error('merk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        {{-- Form Kategori (INPUT BISA DIKETIK) --}}
                        <div class="col-md-4">
                            <label class="form-label">Kategori <span class="required">*</span></label>
                            {{-- Input Hidden untuk mendeteksi apakah ini kategori baru --}}
                            <input type="hidden" name="kategori_baru_input" id="kategori_baru_input">
                            
                            <select name="id_kategori" id="id_kategori" class="form-select @error('id_kategori') is-invalid @enderror" required data-placeholder="-- Pilih atau Ketik Baru --">
                                <option></option> 
                                @foreach($kategori as $kat)
                                    <option value="{{ $kat->id_kategori }}" {{ old('id_kategori') == $kat->id_kategori ? 'selected' : '' }}>
                                        {{ $kat->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Garansi (Tahun) <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text input-group-icon"><i class="bi bi-shield-check"></i></span>
                                <input type="number" name="garansi" id="garansi" class="form-control @error('garansi') is-invalid @enderror" value="{{ old('garansi') }}" placeholder="0" min="0" required>
                                <span class="input-group-text">Tahun</span>
                            </div>
                            @error('garansi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Spesifikasi <span class="required">*</span></label>
                            <textarea name="spesifikasi" id="spesifikasi" rows="4" class="form-control @error('spesifikasi') is-invalid @enderror" placeholder="Contoh: Intel Core i7-11800H..." required>{{ old('spesifikasi') }}</textarea>
                            @error('spesifikasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Harga dan Stok --}}
                <div class="form-section">
                    <h3 class="section-title"><i class="bi bi-currency-dollar"></i>Harga & Stok</h3>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Harga Beli <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text input-group-icon">Rp</span>
                                <input type="number" name="harga_beli" id="harga_beli" class="form-control @error('harga_beli') is-invalid @enderror" value="{{ old('harga_beli') }}" placeholder="0" min="0" required>
                            </div>
                            @error('harga_beli')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">
                                @if(isset($id_pembelian) && $id_pembelian) Jumlah Dibeli @else <span id="label_stok">Stok Awal</span> @endif <span class="required">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text input-group-icon"><i class="bi bi-box"></i></span>
                                <input type="number" name="stok" id="stok_awal" class="form-control @error('stok') is-invalid @enderror" value="{{ old('stok') }}" placeholder="0" min="1" required>
                            </div>
                            @error('stok')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Harga Jual <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text input-group-icon">Rp</span>
                                <input type="number" name="harga_jual" id="harga_jual" class="form-control @error('harga_jual') is-invalid @enderror" value="{{ old('harga_jual') }}" placeholder="0" min="0" required>
                            </div>
                            @error('harga_jual')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row g-3 mt-3">
                        <div class="col-12">
                            <label class="form-label">Total Harga (Subtotal)</label>
                            <div class="input-group">
                                <span class="input-group-text input-group-icon">Rp</span>
                                <input type="text" id="total_harga" class="form-control" value="0" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Gambar Produk --}}
                <div class="form-section">
                    <h3 class="section-title"><i class="bi bi-image"></i>Gambar Produk</h3>
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label" id="label_gambar">Upload Gambar <span class="text-danger" id="bintang_gambar">*</span></label>
                            <input type="file" name="gambar" id="imageInput" class="form-control @error('gambar') is-invalid @enderror" accept="image/*" onchange="previewImage(event)">
                            <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                            @error('gambar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="image-preview-container" id="imagePreview">
                                <div class="image-placeholder">
                                    <i class="bi bi-image"></i>
                                    <p>Preview gambar akan muncul di sini</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- 
                    ================================================================
                    LOGIKA TOMBOL BATAL & SIMPAN (DIPERBARUI)
                    ================================================================
                --}}
                @if(isset($id_pembelian) && $id_pembelian)
                    <script>const isPembelianMode = true;</script>

                    <div class="d-flex justify-content-between gap-3 mt-4">
                        {{-- Tombol Kembali (Pemicu Batal) --}}
                        {{-- Menggunakan mode 'kembali' yang langsung redirect di JS --}}
                        <button type="button" onclick="openCancelModal('kembali')" class="btn btn-secondary-custom">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </button>

                        <div class="d-flex gap-3">
                            {{-- Menggunakan mode 'batal_transaksi' yang memicu form DELETE di JS --}}
                            <button type="button" class="btn btn-danger" onclick="openCancelModal('batal_transaksi')" style="padding: 0.875rem 2.5rem; border-radius: 8px; font-weight: 600;">
                                <i class="bi bi-x-circle me-2"></i>Batalkan Transaksi
                            </button>
                            <button type="button" class="btn btn-primary-custom" onclick="openConfirmationModal()">
                                <i class="bi bi-save me-2"></i>Simpan
                            </button>
                        </div>
                    </div>
                @else
                    <script>const isPembelianMode = false;</script>

                    <div class="d-flex justify-content-between gap-3 mt-4">
                        <a href="{{ route('stok.index') }}" class="btn btn-secondary-custom">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </a>
                        <div class="d-flex gap-3">
                            {{-- Menggunakan mode 'hapus_input' yang memicu reset form di JS --}}
                            <button type="button" class="btn btn-danger" onclick="openCancelModal('hapus_input')" style="padding: 0.875rem 2.5rem; border-radius: 8px; font-weight: 600;">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Batal & Hapus Input
                            </button>
                            <button type="button" class="btn btn-primary-custom" onclick="openConfirmationModal()">
                                <i class="bi bi-save me-2"></i>Simpan Produk
                            </button>
                        </div>
                    </div>
                @endif
            </form>
            
            {{-- FORM DELETE TERSEMBUNYI UNTUK MEMBATALKAN PEMBELIAN --}}
            {{-- Diletakkan di luar formProduk untuk menghindari submission ganda --}}
            @if(isset($id_pembelian) && $id_pembelian)
            <form id="form-delete-pembelian" action="{{ route('pembelian.destroy', $id_pembelian) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
            @endif
        </div>
    </div>
</div>

{{-- Load Modal dari Partial --}}
@include('stok.partials.create_modals') 

@endsection

@push('scripts')
<script src="/js/stok/image-preview.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- CONFIG JS --}}
<script>
    const dataProduk = @json($semua_produk_data);
    const storageBaseUrl = "{{ asset('storage') }}/"; 
    const pembelianIndexUrl = "{{ route('pembelian.index') }}";
    
    $(document).ready(function() {
        $('#id_kategori').select2({
            theme: "bootstrap-5",
            tags: true, 
            width: '100%',
            placeholder: "-- Pilih atau Ketik Kategori Baru --",
            allowClear: true,
            createTag: function (params) {
                var term = params.term;
                if (term === '') { return null; }
                return {
                    id: term, 
                    text: term + ' (Baru)', 
                    newOption: true
                }
            },
            language: { 
                noResults: () => "Kategori tidak ditemukan. Ketik lalu tekan Enter untuk membuat baru." 
            }
        });

        $('#id_kategori').on('select2:select', function (e) {
            var data = e.params.data;
            if (data.newOption) {
                $('#kategori_baru_input').val(data.id); 
            } else {
                $('#kategori_baru_input').val('');
            }
        });
    });

    // PENTING: Tambahkan global variable untuk menyimpan mode pembatalan
    window.cancelMode = ''; 
</script>

<script src="/js/stok/create.js"></script>
@endpush