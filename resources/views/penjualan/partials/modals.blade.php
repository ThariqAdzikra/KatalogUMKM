{{-- =====================================================
     MODAL 1: KONFIRMASI TRANSAKSI & STRUK
===================================================== --}}
<div class="modal fade" id="konfirmasiModal" tabindex="-1" aria-labelledby="konfirmasiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-struk">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="konfirmasiModalLabel">
                        <i class="bi bi-receipt me-2"></i>LaptopPremium
                    </h5>
                    <p class="store-info mb-0 small text-muted">
                        Jl. Digital No. 1, Pekanbaru<br>
                        <i class="bi bi-telephone me-1"></i>0812-3456-7890
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                {{-- Transaction Info --}}
                <div class="struk-section">
                    <div class="struk-item">
                        <span class="text-muted">Pelanggan</span>
                        <span id="struk-pelanggan" class="fw-semibold">-</span>
                    </div>
                    <div class="struk-item">
                        <span class="text-muted">Tanggal</span>
                        <span id="struk-tanggal">-</span>
                    </div>
                    <div class="struk-item">
                        <span class="text-muted">Metode Bayar</span>
                        <span id="struk-metode" class="badge bg-primary">-</span>
                    </div>
                </div>
                
                {{-- Product List --}}
                <div class="struk-section">
                    <h6 class="text-muted small fw-semibold mb-3">RINCIAN PEMBELIAN</h6>
                    <div id="struk-items-list" class="struk-produk-list">
                        {{-- Items will be populated by JavaScript --}}
                    </div>
                </div>
                
                {{-- Total --}}
                <div class="struk-total">
                    <div class="struk-item">
                        <span class="fs-5 fw-bold">TOTAL</span>
                        <span id="struk-total" class="fs-5 fw-bold text-primary">Rp 0</span>
                    </div>
                </div>

                {{-- Warning --}}
                <div class="alert alert-warning d-flex align-items-center p-3 mt-3 mb-0" style="border-radius: 12px;">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <small class="mb-0">Transaksi tidak dapat diubah setelah disimpan. Pastikan data sudah benar.</small>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-warning" id="confirmSubmitAndPrint">
                    <i class="bi bi-printer me-1"></i>Cetak Struk
                </button>
                <button type="button" class="btn btn-success" id="confirmSubmitSaveOnly">
                    <i class="bi bi-check-circle me-1"></i>Simpan Transaksi
                </button>
            </div>
        </div>
    </div>
</div>

{{-- =====================================================
     MODAL 2: PELANGGAN BARU
===================================================== --}}
<div class="modal fade" id="newCustomerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus-fill me-2 text-primary"></i>
                    Tambah Pelanggan Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">
                        Nama Lengkap <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           id="modal_nama_pelanggan" 
                           class="form-control bg-dark text-light border-secondary"
                           placeholder="Masukkan nama lengkap"
                           required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">No. HP</label>
                        <input type="text" 
                               id="modal_no_hp" 
                               class="form-control bg-dark text-light border-secondary"
                               placeholder="08xx-xxxx-xxxx">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" 
                               id="modal_email" 
                               class="form-control bg-dark text-light border-secondary"
                               placeholder="email@example.com">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Alamat</label>
                    <textarea id="modal_alamat" 
                              class="form-control bg-dark text-light border-secondary" 
                              rows="3"
                              placeholder="Masukkan alamat lengkap"></textarea>
                </div>

                <div class="alert alert-info d-flex align-items-start p-2 mb-0" style="font-size: 0.85rem;">
                    <i class="bi bi-info-circle me-2 mt-1"></i>
                    <small>Data pelanggan akan tersimpan dan dapat dipilih untuk transaksi selanjutnya.</small>
                </div>
            </div>
            
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-primary" id="btn-save-customer">
                    <i class="bi bi-save me-1"></i>Simpan Data
                </button>
            </div>
        </div>
    </div>
</div>

{{-- =====================================================
     MODAL 3: DETAIL PELANGGAN
===================================================== --}}
<div class="modal fade" id="customerDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">
                    <i class="bi bi-person-badge-fill me-2 text-info"></i>
                    Detail Pelanggan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="mb-4">
                    <label class="small text-muted d-block mb-1">Nama Lengkap</label>
                    <h5 id="detail_nama" class="fw-bold mb-0">-</h5>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="small text-muted d-block mb-1">
                            <i class="bi bi-telephone me-1"></i>No. HP
                        </label>
                        <span id="detail_hp" class="text-info fw-medium">-</span>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="small text-muted d-block mb-1">
                            <i class="bi bi-envelope me-1"></i>Email
                        </label>
                        <span id="detail_email" class="text-light fw-medium">-</span>
                    </div>
                </div>
                
                <div class="mb-0">
                    <label class="small text-muted d-block mb-1">
                        <i class="bi bi-geo-alt me-1"></i>Alamat
                    </label>
                    <p id="detail_alamat" class="mb-0 text-white-50 fst-italic">-</p>
                </div>
            </div>
            
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

{{-- =====================================================
     MODAL 4: SUCCESS NOTIFICATION
===================================================== --}}
<div class="modal fade" id="kasirSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    Transaksi Berhasil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body text-center py-4">
                <div class="mx-auto mb-3" aria-hidden="true">
                    <svg viewBox="0 0 120 120" width="100" height="100">
                        <circle class="kasir-check-circle" 
                                cx="60" cy="60" r="42" 
                                fill="none" 
                                stroke="#10b981" 
                                stroke-width="6" />
                        <path class="kasir-check-mark" 
                              d="M38 62 L54 76 L84 46" 
                              fill="none" 
                              stroke="#10b981" 
                              stroke-linecap="round" 
                              stroke-linejoin="round" 
                              stroke-width="6" />
                    </svg>
                </div>
                <p id="kasirSuccessMessage" class="mb-0 fs-6">
                    Transaksi telah berhasil disimpan ke database.
                </p>
            </div>
            
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-success px-5" data-bs-dismiss="modal">
                    <i class="bi bi-check2 me-2"></i>OK, Mengerti
                </button>
            </div>
        </div>
    </div>
</div>

{{-- =====================================================
     MODAL STYLES
===================================================== --}}
<style>
/* Struk Modal */
.modal-struk .modal-content {
    background: var(--surface-1);
    border: 1px solid var(--border-medium);
    border-radius: var(--radius-lg);
}

.modal-struk .modal-header {
    border-bottom: 1px solid var(--border-subtle);
    background: linear-gradient(to bottom, rgba(59, 130, 246, 0.1), transparent);
}

.store-info {
    line-height: 1.5;
}

.struk-section {
    padding: 1rem 0;
    border-bottom: 1px dashed var(--border-subtle);
}

.struk-section:last-of-type {
    border-bottom: none;
}

.struk-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    gap: 1rem;
}

.struk-item:last-child {
    margin-bottom: 0;
}

.struk-produk-list {
    max-height: 300px;
    overflow-y: auto;
}

.struk-produk {
    padding: 0.75rem;
    background: rgba(255, 255, 255, 0.02);
    border-radius: var(--radius-sm);
    margin-bottom: 0.5rem;
}

.struk-produk:last-child {
    margin-bottom: 0;
}

.struk-produk .nama-produk {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.375rem;
}

.struk-produk .detail-produk {
    display: flex;
    justify-content: space-between;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.struk-total {
    padding: 1.25rem 0 0.5rem;
    border-top: 2px solid var(--border-medium);
}

/* Animation for Success Modal */
.kasir-check-circle {
    stroke-dasharray: 265;
    stroke-dashoffset: 265;
    animation: kcirc 900ms ease-out forwards;
}

.kasir-check-mark {
    stroke-dasharray: 80;
    stroke-dashoffset: 80;
    animation: kcheck 700ms 350ms ease-out forwards;
}

@keyframes kcirc {
    to {
        stroke-dashoffset: 0;
    }
}

@keyframes kcheck {
    to {
        stroke-dashoffset: 0;
    }
}

/* Modal Backdrop */
.modal-backdrop {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

/* Customer Selection Wrapper */
.customer-selection-wrapper {
    background: rgba(255, 255, 255, 0.02);
    padding: 1rem;
    border-radius: var(--radius-md);
    margin-bottom: 1rem;
    border: 1px solid var(--border-subtle);
}

/* Responsive Modal */
@media (max-width: 576px) {
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .modal-struk .modal-body {
        padding: 1rem;
    }
}
</style>