@extends('layouts.app')

@section('title', 'Kasir Penjualan - Laptop Store')

@push('styles')
    <link rel="stylesheet" href="/css/manajemen/kasir.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush

@section('content')
<div class="container py-4">
  {{-- Pesan sukses --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Pesan error (opsional) --}}
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

  <div class="form-container pt-4"> 

    <div class="page-header mb-4">
        <h1 class="page-title">
          <i class="bi bi-cash-register me-2 pt-4"></i>Kasir Penjualan
        </h1>
        <p class="page-subtitle">Catat transaksi dengan cepat dan mudah</p>
    </div>

    <form action="{{ route('penjualan.store') }}" method="POST" id="form-kasir">
      @csrf
      <div class="row g-4 align-items-stretch">
        
        {{-- KARTU 1: INPUT (KIRI) --}}
        <div class="col-12 col-lg-8">
          <div class="form-card h-100"> 
            <div class="form-section">
              <h3 class="section-title"><i class="bi bi-person"></i>Informasi Pelanggan</h3>

              <div class="row g-3"> 
                <div class="col-12">
                  <label class="form-label">Pelanggan</label>
                  <select name="id_pelanggan" id="pelanggan-select" class="form-select" required>
                    <option value="">-- Cari atau Ketik Nama Pelanggan --</option>
                    @foreach($pelanggan as $p)
                      <option value="{{ $p->id_pelanggan }}" 
                              data-nama="{{ $p->nama }}" 
                              data-hp="{{ $p->no_hp }}" 
                              data-email="{{ $p->email }}" 
                              data-alamat="{{ $p->alamat }}">
                        {{ $p->nama }}
                      </option>
                    @endforeach
                  </select>
                  
                  <div id="kolom-pelanggan-baru" class="mt-3" style="display: none;">
                    <h5 id="judul-info-pelanggan"><i class="bi bi-person-check-fill me-2"></i>Info Pelanggan</h5>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small mb-1">Nama Pelanggan</label>
                            <input type="text" id="nama_pelanggan_baru" name="nama_pelanggan_baru" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1">No. HP</label>
                            <input type="text" id="no_hp_baru" name="no_hp_baru" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1">Email</label>
                            <input type="email" id="email_baru" name="email_baru" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1">Alamat</label>
                            <textarea id="alamat_baru" name="alamat_baru" class="form-control form-control-sm" rows="1"></textarea> 
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="col-12">
                  <label class="form-label">Tanggal Penjualan</label>
                  <input type="datetime-local" name="tanggal_penjualan" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h3 class="section-title"><i class="bi bi-cart4"></i>Tambah Produk</h3>
              <div id="produk-wrapper">
                {{-- Baris Produk Awal --}}
                <div class="row g-3 mb-3 produk-row align-items-end" data-row-id="row-1">
                  <div class="col-md-5">
                    <div class="produk-label-wrapper">
                        <label class="form-label">Produk</label>
                        <small class="text-muted stok-info"></small> 
                    </div>
                    <select name="produk[]" class="form-select produk-select" required>
                      <option value="">-- Pilih Produk --</option>
                      @foreach($produk as $pr)
                        <option value="{{ $pr->id_produk }}" 
                          data-harga="{{ $pr->harga_jual }}" 
                          data-stok="{{ $pr->stok }}" 
                          data-nama="{{ $pr->nama_produk }}"
                          data-garansi="{{ $pr->garansi }}">
                          {{ $pr->nama_produk }}
                    </option>
                    @endforeach 

                    </select>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Jumlah</label>
                    <input type="number" name="jumlah[]" class="form-control jumlah-input" min="1" value="1" required>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Harga</label>
                    <div class="input-group">
                      <span class="input-group-text">Rp</span>
                      <input type="text" class="form-control harga-display" placeholder="0" readonly>
                      <input type="hidden" name="harga_satuan[]" class="harga-input" value="0">
                    </div>
                  </div>
                  <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-row">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
              
              <button type="button" id="add-row" class="btn btn-outline-primary-custom w-100">
                <i class="bi bi-plus-circle me-1"></i>Tambah Produk
              </button>
            </div>
          </div>
        </div>

        {{-- KARTU 2: RINGKASAN --}}
        <div class="col-12 col-lg-4">
          <div class="summary-sticky-wrapper h-100">
            <div class="form-card h-100"> 
              <div class="form-section">
                <h3 class="section-title"><i class="bi bi-receipt"></i>Ringkasan Transaksi</h3>
                <div id="ringkasan-items" class="mb-3"></div>
                <div class="mb-3">
                  <label class="form-label">Metode Pembayaran</label>
                  <select name="metode_pembayaran" id="metode_pembayaran" class="form-select" required>
                    <option value="">-- Pilih Metode Pembayaran --</option>
                    <option value="cash">Cash</option>
                    <option value="transfer">Transfer</option>
                    <option value="qris">QRIS</option>
                  </select>
                </div>
                <div id="qris-preview" class="text-center mb-3 d-none">
                  <p class="fw-semibold">Scan QRIS untuk pembayaran:</p>
                  <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Demo-QRIS" alt="QRIS Code" class="img-thumbnail" style="width: 150px;">
                </div>
                <div id="transfer-preview" class="alert alert-info d-none mb-3">
                    <h6 class="fw-bold mb-2"><i class="bi bi-bank me-2"></i>Informasi Transfer</h6>
                    <p class="mb-1 small">Silakan transfer ke rekening:</p>
                    <div class="ms-2">
                        <strong>Bank:</strong> Bank Jagok<br>
                        <strong>No. Rek:</strong> 123-456-7890<br>
                        <strong>A/N:</strong> PT Laptop Premium
                    </div>
                </div>
                <hr class="my-3">
                <div class="total-section">
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <span id="subtotal-display" class="fw-bold">Rp 0</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center" style="border-top: 2px solid #8b7355; padding-top: 0.75rem;">
                    <h5 class="fw-bold mb-0">Total:</h5>
                    <h3 id="total-display" class="fw-bolder mb-0" style="color: #8b7355;">Rp 0</h3>
                  </div>
                  <input type="hidden" name="total_harga" id="total_harga" value="0">
                </div>
                <button type="button" id="showConfirmModal" class="btn btn-primary-custom w-100 mt-4">
                  <i class="bi bi-check-circle me-2"></i>Selesaikan Transaksi
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- ✅ MODAL STRUK (Tetap ada) --}}
<div class="modal fade" id="konfirmasiModal" tabindex="-1" aria-labelledby="konfirmasiModalLabel" aria-hidden="true">
    {{-- ... (Isi modal struk tidak berubah) ... --}}
    <div class="modal-dialog modal-dialog-centered modal-struk">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="konfirmasiModalLabel">LaptopPremium</h5>
        <p class="store-info">Jl. Digital No. 1, Pekanbaru<br>0812-3456-7890</p>
      </div>
      <div class="modal-body">
        <div class="struk-section">
          <div class="struk-item">
            <span>Pelanggan:</span>
            <span id="struk-pelanggan">-</span>
          </div>
          <div class="struk-item">
            <span>Tanggal:</span>
            <span id="struk-tanggal">-</span>
          </div>
          <div class="struk-item">
            <span>Metode Bayar:</span>
            <span id="struk-metode">-</span>
          </div>
        </div>
        
        <div class="struk-section">
          <div id="struk-items-list" class="struk-produk-list">
            {{-- Item produk akan di-generate oleh JS --}}
          </div>
        </div>
        
        <div class="struk-total">
          <div class="struk-item">
            <span>TOTAL:</span>
            <span id="struk-total">Rp 0</span>
          </div>
        </div>

        <div class="alert alert-warning text-center small p-2 mt-3 mb-0" style="font-family: Arial, sans-serif;">
          <i class="bi bi-exclamation-triangle-fill"></i> Transaksi tidak dapat diubah setelah disimpan.
        </div>
      </div>
      
      <div class="modal-footer">
        {{-- Baris 1: Tombol Cetak (COKLAT) --}}
        <button type="button" class="btn btn-primary" id="confirmSubmitAndPrint">
            <i class="bi bi-printer me-1"></i>Cetak Struk
        </button>
        {{-- Baris 2: Tombol Batal (MERAH) & Simpan (HIJAU) --}}
        <div class="footer-actions-split">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <i class="bi bi-x-circle me-1"></i>Batal
            </button>
            <button type="button" class="btn btn-info" id="confirmSubmitSaveOnly">
                <i class="bi bi-save me-1"></i>Langsung Simpan
            </button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ✅ TOAST SUKSES (BARU) --}}
<div class="modal fade" id="kasirSuccessModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Berhasil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mx-auto mb-2" aria-hidden="true">
          <svg viewBox="0 0 120 120" width="100" height="100">
            <circle class="kasir-check-circle" cx="60" cy="60" r="42" fill="none" stroke="#198754" stroke-width="6" />
            <path class="kasir-check-mark" d="M38 62 L54 76 L84 46" fill="none" stroke="#198754" stroke-linecap="round" stroke-linejoin="round" stroke-width="6" />
          </svg>
        </div>
        <p id="kasirSuccessMessage">Transaksi berhasil disimpan.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal"><i class="bi bi-check2 me-2"></i>OK</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
{{-- Library --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- ✅ SKRIP KUSTOM TELAH DIPINDAHKAN --}}
<script src="/js/kasir/main.js"></script>
@endpush

@push('styles')
<style>
  .kasir-check-circle{stroke-dasharray:265;stroke-dashoffset:265;animation:kcirc 900ms ease-out forwards}
  .kasir-check-mark{stroke-dasharray:80;stroke-dashoffset:80;animation:kcheck 700ms 350ms ease-out forwards}
  @keyframes kcirc{to{stroke-dashoffset:0}}
  @keyframes kcheck{to{stroke-dashoffset:0}}
</style>
@endpush