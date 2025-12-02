// public/js/stok/create.js

$(document).ready(function () {

    // --- 1. Kalkulator Total Harga ---
    const hargaBeliInput = document.getElementById('harga_beli');
    const stokAwalInput = document.getElementById('stok_awal');
    const totalHargaOutput = document.getElementById('total_harga');

    window.calculateTotal = function () { // Dijadikan global agar bisa dipanggil di luar ready()
        const harga = parseFloat(hargaBeliInput.value) || 0;
        const stok = parseInt(stokAwalInput.value) || 0;
        const total = harga * stok;
        if (totalHargaOutput) totalHargaOutput.value = total.toLocaleString('id-ID');
    }

    // Pasang listener secara lokal
    if (hargaBeliInput && stokAwalInput) {
        hargaBeliInput.addEventListener('input', calculateTotal);
        stokAwalInput.addEventListener('input', calculateTotal);

        // Panggil saat load untuk mengisi nilai awal
        calculateTotal();
    }

    // --- 2. Inisialisasi Select2 untuk Nama Produk ---
    $('#nama_produk_select').select2({
        theme: "bootstrap-5",
        tags: true,
        width: '100%',
        placeholder: "Cari atau ketik nama produk baru...",
        allowClear: false,
        language: { noResults: () => "Produk tidak ditemukan. Tekan Enter untuk buat baru." }
    }).on('select2:select', function (e) {
        handleProductSelection(e.params.data.id);
    });


    // ========================================================
    // 4. FUNGSI UTAMA: HANDLE SAAT PRODUK DIPILIH
    // ========================================================
    function handleProductSelection(selectedValue) {
        const previewContainer = document.getElementById('imagePreview');
        const placeholderHTML = `
            <div class="image-placeholder">
                <i class="bi bi-image"></i>
                <p>Preview gambar akan muncul di sini</p>
            </div>`;

        const safeSetText = (id, text) => {
            const el = document.getElementById(id);
            if (el) el.textContent = text;
        };

        const safeSetStyle = (id, property, value) => {
            const el = document.getElementById(id);
            if (el) el.style[property] = value;
        };

        // Memeriksa apakah nilai yang dipilih adalah ID produk yang valid dari dataProduk
        const isExistingProduct = dataProduk.hasOwnProperty(selectedValue) && !isNaN(selectedValue);

        if (isExistingProduct) {
            const produk = dataProduk[selectedValue];

            document.getElementById('id_produk_existing').value = produk.id_produk;
            document.getElementById('merk').value = produk.merk;

            if ($('#id_kategori').find("option[value='" + produk.id_kategori + "']").length) {
                $('#id_kategori').val(produk.id_kategori).trigger('change');
            } else {
                $('#id_kategori').val(null).trigger('change');
            }

            document.getElementById('spesifikasi').value = produk.spesifikasi;
            document.getElementById('harga_beli').value = produk.harga_beli;
            document.getElementById('harga_jual').value = produk.harga_jual;
            document.getElementById('stok_awal').value = 0; // Reset stok awal karena ini restock
            document.getElementById('garansi').value = produk.garansi;

            // Tampilkan info stok saat ini hanya jika bukan mode pembelian
            if (typeof isPembelianMode !== 'undefined' && !isPembelianMode) {
                safeSetText('stok_saat_ini_info', `(Stok saat ini: ${produk.stok})`);
                safeSetStyle('stok_saat_ini_info', 'display', 'inline');
            }

            safeSetText('label_stok', 'Jumlah Ditambah');
            safeSetText('label_gambar', 'Upload Gambar (Opsional)');
            safeSetStyle('bintang_gambar', 'display', 'none');

            if (produk.gambar) {
                // Menghapus 'public/' atau 'storage/' dari path dan membuat URL lengkap
                let cleanPath = produk.gambar.replace(/^public\/|^storage\//, '');
                let fullImageUrl = storageBaseUrl + cleanPath;

                previewContainer.innerHTML = `
                    <img src="${fullImageUrl}" 
                         alt="${produk.nama_produk}" 
                         style="max-width: 100%; max-height: 200px; object-fit: contain; border-radius: 8px; border: 1px solid #ddd;">
                `;
            } else {
                previewContainer.innerHTML = placeholderHTML;
            }

        } else {
            // Logika untuk Produk Baru (atau input teks manual)
            document.getElementById('id_produk_existing').value = '';
            document.getElementById('merk').value = '';
            document.getElementById('spesifikasi').value = '';
            document.getElementById('harga_beli').value = '';
            document.getElementById('harga_jual').value = '';
            document.getElementById('stok_awal').value = '';
            document.getElementById('garansi').value = 0;

            $('#id_kategori').val(null).trigger('change');
            $('#kategori_baru_input').val('');

            safeSetStyle('stok_saat_ini_info', 'display', 'none');
            safeSetText('label_stok', 'Stok Awal');
            safeSetText('label_gambar', 'Upload Gambar');
            safeSetStyle('bintang_gambar', 'display', 'inline');

            previewContainer.innerHTML = placeholderHTML;
        }

        calculateTotal();
    }

    // Panggil calculateTotal agar total dihitung saat form dimuat
    calculateTotal();
});

// --- Helper Functions untuk Modal ---

window.openConfirmationModal = function () {
    const form = document.getElementById('formProduk');
    // Memicu validasi HTML5
    if (!form.checkValidity()) { form.reportValidity(); return; }

    const namaProdukVal = $('#nama_produk_select').val();
    const hargaBeli = parseFloat(document.getElementById('harga_beli').value) || 0;
    const stok = parseInt(document.getElementById('stok_awal').value) || 0;
    const imageInput = document.getElementById('imageInput');
    const isRestock = document.getElementById('id_produk_existing').value !== '';

    if (!namaProdukVal) { alert("Nama produk wajib diisi!"); return; }
    if (hargaBeli <= 0) { alert("Harga Beli harus lebih dari 0!"); return; }
    // Untuk restock, stok boleh 0 (tapi tidak wajar). Untuk produk baru, wajib > 0.
    if (stok <= 0 && !isRestock) { alert("Stok awal minimal 1!"); return; }
    // Untuk produk baru, gambar wajib ada
    if (!isRestock && imageInput.files.length === 0) { alert("Produk baru wajib upload gambar!"); return; }

    // Isi Modal Summary
    const select2Data = $('#nama_produk_select').select2('data');
    const namaProdukText = (select2Data && select2Data.length > 0) ? select2Data[0].text : namaProdukVal;

    document.getElementById('summaryNamaProduk').textContent = namaProdukText;
    document.getElementById('summaryMerk').textContent = document.getElementById('merk').value;

    let kategoriText = '-';
    const katData = $('#id_kategori').select2('data');
    if ((!katData || katData.length === 0) && $('#id_kategori').val()) {
        kategoriText = $('#id_kategori').val();
    } else if (katData && katData.length > 0) {
        if (katData[0].newOption) {
            kategoriText = katData[0].text.replace(' (Baru)', '');
        } else {
            kategoriText = katData[0].text;
        }
    }

    const safeSetText = (id, text) => { const el = document.getElementById(id); if (el) el.textContent = text; };

    safeSetText('summaryKategori', kategoriText);
    safeSetText('summaryKategoriBadge', kategoriText);
    safeSetText('summaryMerkBadge', document.getElementById('merk').value);
    safeSetText('summarySpesifikasi', document.getElementById('spesifikasi').value);
    safeSetText('summaryGaransi', document.getElementById('garansi').value);

    safeSetText('summaryHargaBeli', 'Rp ' + hargaBeli.toLocaleString('id-ID'));
    safeSetText('summaryStok', stok);
    safeSetText('summaryHargaJual', 'Rp ' + (parseFloat(document.getElementById('harga_jual').value) || 0).toLocaleString('id-ID'));

    const total = hargaBeli * stok;
    safeSetText('summaryTotalHarga', 'Rp ' + total.toLocaleString('id-ID'));

    if (isRestock) {
        safeSetText('summaryLabelStok', 'Jumlah Ditambah:');
    } else {
        safeSetText('summaryLabelStok', 'Stok Awal:');
    }

    // Preview Gambar Modal
    const summaryImagePreview = document.getElementById('summaryImagePreview');
    const restockImagePlaceholder = `<div class="text-center text-muted"><i class="bi bi-image fs-1 d-block opacity-50"></i><span style="font-size: 0.8rem;">Gunakan Gambar Lama</span></div>`;
    const defaultImagePlaceholder = `<div class="text-center text-muted"><i class="bi bi-image fs-1 d-block opacity-50"></i><span style="font-size: 0.8rem;">Belum ada gambar</span></div>`;

    if (imageInput.files && imageInput.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            summaryImagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 100%; height: auto;">`;
        };
        reader.readAsDataURL(imageInput.files[0]);
    } else {
        if (isRestock) {
            const produkId = $('#nama_produk_select').val();
            if (produkId && dataProduk.hasOwnProperty(produkId)) {
                const produk = dataProduk[produkId];
                if (produk && produk.gambar) {
                    let cleanPath = produk.gambar.replace(/^public\/|^storage\//, '');
                    const imageUrl = storageBaseUrl + cleanPath;
                    summaryImagePreview.innerHTML = `<img src="${imageUrl}" alt="${produk.nama_produk}" style="max-width: 100%; height: auto;">`;
                } else {
                    summaryImagePreview.innerHTML = restockImagePlaceholder;
                }
            } else {
                summaryImagePreview.innerHTML = defaultImagePlaceholder;
            }
        } else {
            summaryImagePreview.innerHTML = defaultImagePlaceholder;
        }
    }

    const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    modal.show();
}

window.openCancelModal = function (mode) {
    // Simpan mode pembatalan ke variabel global
    window.cancelMode = mode;

    // Logika untuk tombol "Kembali" (Hanya di mode Pembelian)
    // Langsung redirect tanpa modal menggunakan variabel global
    if (mode === 'kembali') {
        if (typeof pembelianIndexUrl !== 'undefined') {
            window.location.href = pembelianIndexUrl;
        } else {
            console.error('pembelianIndexUrl tidak terdefinisi');
            window.location.href = '/pembelian';
        }
        return;
    }

    // Ambil elemen modal
    const titleEl = document.querySelector('#cancelModal .modal-header-warning .modal-title');
    const bodyEl = document.querySelector('#cancelModal .modal-body h5');
    const descEl = document.querySelector('#cancelModal .modal-body p');
    const btnConfirmEl = document.querySelector('#cancelModal .btn-secondary-custom');
    const btnReturnEl = document.querySelector('#cancelModal .btn-neutral-custom');

    // Logic untuk Batalkan Transaksi (Mode Pembelian)
    if (mode === 'batal_transaksi') {
        if (titleEl) titleEl.innerHTML = '<i class="bi bi-x-circle-fill me-2"></i>Batalkan Pembelian';
        if (bodyEl) bodyEl.textContent = 'Hapus Data Pembelian?';
        if (descEl) descEl.innerHTML = 'Data pembelian yang baru dibuat akan dihapus. <br><span class="text-danger fw-semibold" style="font-size: 0.9rem;">Anda akan kembali ke halaman daftar pembelian.</span>';
        if (btnConfirmEl) btnConfirmEl.innerHTML = '<i class="bi bi-trash-fill me-1"></i> Ya, Batalkan';
        if (btnReturnEl) btnReturnEl.innerHTML = '<i class="bi bi-arrow-return-left me-1"></i> Lanjutkan';
    }
    // Logic untuk Hapus Input (Mode Stok Biasa)
    else if (mode === 'hapus_input') {
        if (titleEl) titleEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Pembatalan';
        if (bodyEl) bodyEl.textContent = 'Batalkan Input?';
        if (descEl) descEl.innerHTML = 'Apakah Anda yakin ingin menghapus semua data yang telah diisi? <br><span class="text-danger fw-semibold" style="font-size: 0.9rem;">Tindakan ini tidak dapat dibatalkan.</span>';
        if (btnConfirmEl) btnConfirmEl.innerHTML = '<i class="bi bi-trash-fill me-1"></i> Ya, Hapus Data';
        if (btnReturnEl) btnReturnEl.innerHTML = '<i class="bi bi-arrow-return-left me-1"></i> Tidak, Kembali';
    }

    $('#cancelModal').modal('show');
}

window.confirmReset = function () {
    const modalEl = document.getElementById('cancelModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();

    // === LOGIKA HAPUS PEMBELIAN (Memicu DELETE /pembelian/{id}) ===
    if (window.cancelMode === 'batal_transaksi') {
        const formDelete = document.getElementById('form-delete-pembelian');
        if (formDelete) {
            // Log untuk debugging
            console.log('Form Delete Pembelian ditemukan');
            console.log('Action URL:', formDelete.action);
            console.log('Method:', formDelete.method);

            // Pastikan form method adalah POST (karena Laravel menggunakan method spoofing)
            formDelete.method = 'POST';

            // Submit form
            setTimeout(function () {
                formDelete.submit();
            }, 100);
        } else {
            console.error("Form DELETE pembelian (form-delete-pembelian) tidak ditemukan!");
            // Fallback: redirect ke halaman index pembelian
            if (typeof pembelianIndexUrl !== 'undefined') {
                window.location.href = pembelianIndexUrl;
            } else {
                window.location.href = '/pembelian';
            }
        }
        return; // PENTING: Hentikan eksekusi di sini!
    }

    // === LOGIKA RESET BIASA (MODE STOK atau mode hapus_input) ===
    setTimeout(() => {
        document.getElementById('formProduk').reset();

        $('#nama_produk_select').val(null).trigger('change');
        $('#id_kategori').val(null).trigger('change');
        $('#kategori_baru_input').val('');

        document.getElementById('imagePreview').innerHTML = `
            <div class="image-placeholder"><i class="bi bi-image"></i><p>Preview gambar akan muncul di sini</p></div>`;

        const safeSetStyle = (id, prop, val) => { const el = document.getElementById(id); if (el) el.style[prop] = val; };
        const safeSetText = (id, text) => { const el = document.getElementById(id); if (el) el.textContent = text; };

        safeSetStyle('stok_saat_ini_info', 'display', 'none');
        safeSetText('label_stok', 'Stok Awal');
        safeSetText('label_gambar', 'Upload Gambar');
        safeSetStyle('bintang_gambar', 'display', 'inline');

        document.getElementById('total_harga').value = '0';
        document.getElementById('id_produk_existing').value = '';

    }, 200);
}

// Fungsi Submit Akhir
window.submitForm = function () {
    document.getElementById('formProduk').submit();
}