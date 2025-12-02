{{-- ========================================================== --}}
{{-- === MODAL KONFIRMASI (BACKGROUND COKLAT & TOMBOL) === --}}
{{-- ========================================================== --}}
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel">
    <div class="modal-dialog modal-compact"> 
        <div class="modal-content modal-content-cozy">
            
            {{-- Header (Coklat) --}}
            <div class="modal-header modal-header-cozy">
                <h5 class="modal-title" id="confirmationModalLabel">
                    <i class="bi bi-clipboard-check me-2"></i>Konfirmasi Data
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- 1. Area Gambar (Preview Header) --}}
            <div class="preview-header-box">
                <div class="preview-img-wrapper" id="summaryImagePreview">
                    {{-- Placeholder default jika tidak ada gambar --}}
                    <div class="text-center text-muted">
                        <i class="bi bi-image fs-1 d-block opacity-50" aria-hidden="true"></i>
                        <span style="font-size: 0.8rem;">No Image</span>
                    </div>
                </div>
                <div class="product-badges">
                    <span class="badge bg-secondary" id="summaryKategoriBadge">Kategori</span>
                    <span class="badge bg-light text-dark border" id="summaryMerkBadge">Merk</span>
                </div>
            </div>

            {{-- 2. Rincian Data (Body - Receipt Style) --}}
            <div class="modal-body-compact">
                <div class="receipt-list">
                    {{-- Nama Produk & Spesifikasi --}}
                    <div class="mb-3 text-center">
                        <h5 class="mb-1" id="summaryNamaProduk" style="font-weight: 700; color: var(--primary-dark);">Nama Produk</h5>
                        {{-- Ini adalah elemen tersembunyi untuk data, yang terlihat ada di bawah --}}
                        <span id="summaryMerk" class="visually-hidden"></span>
                        <span id="summaryKategori" class="visually-hidden"></span>
                        
                        <div id="summarySpesifikasi" class="text-muted small fst-italic text-truncate" style="max-width: 90%; margin: 0 auto;">
                            Spesifikasi singkat...
                        </div>
                    </div>

                    {{-- Data Supplier (Jika Ada) --}}
                    @if(isset($pembelianData) && $pembelianData)
                    <div class="receipt-item">
                        <span class="r-label">Supplier</span>
                        <span class="r-value">{{ $pembelianData->supplier->nama_supplier }}</span>
                    </div>
                    @endif

                    {{-- Data Produk --}}
                    <div class="receipt-item">
                        <span class="r-label">Garansi</span>
                        <span class="r-value"><span id="summaryGaransi">0</span> Tahun</span>
                    </div>
                    
                    <div class="receipt-item">
                        <span class="r-label">Harga Beli (@)</span>
                        <span class="r-value" id="summaryHargaBeli">Rp 0</span>
                    </div>

                    <div class="receipt-item">
                        <span class="r-label" id="summaryLabelStok">Jumlah Stok</span>
                        <span class="r-value" id="summaryStok">0</span>
                    </div>

                    <div class="receipt-item">
                        <span class="r-label">Harga Jual (@)</span>
                        <span class="r-value text-success" id="summaryHargaJual">Rp 0</span>
                    </div>
                </div>

                {{-- 3. Total Harga (Highlight Box) --}}
                <div class="total-highlight-box">
                    <span class="total-title">Total Modal</span>
                    <span class="total-amount" id="summaryTotalHarga">Rp 0</span>
                </div>
            </div>

            {{-- Footer (Sekarang Background Coklat & Tombol Sama Ukuran) --}}
            <div class="modal-footer modal-footer-compact">
                {{-- Tombol Batal (MERAH) --}}
                <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1" aria-hidden="true"></i> Batal
                </button>
                
                {{-- Tombol Simpan Data (HIJAU) --}}
                <button type="button" class="btn btn-primary-custom" onclick="submitForm()">
                    <i class="bi bi-check-lg me-1" aria-hidden="true"></i> Simpan Data
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================== --}}
{{-- === MODAL PEMBATALAN (WARNING STYLE) === --}}
{{-- ========================================================== --}}
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-sm-custom"> 
        <div class="modal-content modal-content-cozy">
            
            {{-- Header (Merah Bata / Warning Style) --}}
            <div class="modal-header-warning">
                <h5 class="modal-title text-white" id="cancelModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>Konfirmasi Pembatalan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Body (Pesan Peringatan) --}}
            <div class="modal-body p-4 text-center">
                <div class="warning-icon-wrapper mb-3">
                    <i class="bi bi-x-octagon-fill text-danger" style="font-size: 3rem;" aria-hidden="true"></i>
                </div>
                <h5 class="fw-bold mb-2" style="color: var(--primary-dark);">Batalkan Input?</h5>
                <p class="text-muted mb-0">
                    Apakah Anda yakin ingin menghapus semua data yang telah diisi? <br>
                    <span class="text-danger fw-semibold" style="font-size: 0.9rem;">Tindakan ini tidak dapat dibatalkan.</span>
                </p>
            </div>

            {{-- Footer --}}
            <div class="modal-footer modal-footer-compact justify-content-center gap-2" style="background: #fff4f4;">
                {{-- Tombol Tidak --}}
                <button type="button" class="btn btn-neutral-custom" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-return-left me-1" aria-hidden="true"></i> Tidak, Kembali
                </button>
                
                {{-- Tombol Ya, Hapus --}}
                <button type="button" class="btn btn-secondary-custom" onclick="confirmReset()">
                    <i class="bi bi-trash-fill me-1" aria-hidden="true"></i> Ya, Hapus Data
                </button>
            </div>
        </div>
    </div>
</div>