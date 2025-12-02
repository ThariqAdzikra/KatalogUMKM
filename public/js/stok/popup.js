document.addEventListener('DOMContentLoaded', function () {

    // --- VARIABEL & ELEMENT ---
    let formToSubmit = null;

    // Mengambil elemen modal berdasarkan ID yang disesuaikan di Blade
    const modalDeleteEl = document.getElementById('confirmDeleteModal');
    const messageEl = document.getElementById('confirmDeleteMessage');
    const confirmBtn = document.getElementById('confirmDeleteBtn');

    // --- 1. LOGIKA MODAL KONFIRMASI HAPUS ---

    // Event delegation untuk menangkap submit form hapus dari tabel
    document.addEventListener('submit', function (e) {
        const target = e.target;

        // Cek apakah form memiliki class 'delete-form'
        // Pastikan tombol hapus di partials/table_wrapper menggunakan class ini
        if (target.matches('form.delete-form')) {
            e.preventDefault(); // Mencegah submit langsung
            formToSubmit = target; // Simpan form sementara

            // Ambil pesan dari data attribute, atau gunakan default
            const msg = target.getAttribute('data-confirm-message') || 'Apakah Anda yakin ingin menghapus data stok produk ini?';
            if (messageEl) messageEl.textContent = msg;

            // Tampilkan Modal Konfirmasi
            if (modalDeleteEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalDeleteEl);
                modal.show();
            }
        }
    }, true);

    // Saat tombol "Hapus" di dalam modal diklik
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (formToSubmit) {
                // Tandai form agar tidak memicu loading animasi
                formToSubmit.setAttribute('data-skip-loader', 'true');

                // Submit form secara native (Synchronous)
                formToSubmit.submit();

                // Sembunyikan modal agar tidak freeze
                const modal = bootstrap.Modal.getInstance(modalDeleteEl);
                if (modal) modal.hide();
            }
        });
    }

    // --- 2. LOGIKA MODAL SUKSES (FLASH SESSION) ---

    // Cek apakah ada elemen penanda sukses (dikirim dari Blade)
    const successFlag = document.getElementById('flash-success-flag');
    if (successFlag) {
        const modalSuccessEl = document.getElementById('successModal'); // ID disesuaikan dengan Blade
        if (modalSuccessEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalSuccessEl);
            modal.show();
        }
    }
});