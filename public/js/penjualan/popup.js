document.addEventListener('DOMContentLoaded', function () {

    // Variabel Global untuk Delete
    let pendingDelete = null; // Menyimpan data { action, csrf } sementara

    // Element Modal Konfirmasi
    const confirmModalEl = document.getElementById('confirmDeleteModal');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const confirmMessageEl = document.getElementById('confirmDeleteMessage');

    // Element Modal Sukses
    const successModalEl = document.getElementById('successModal');
    const successMessageEl = document.getElementById('successMessage');

    // Element Modal Error
    const errorModalEl = document.getElementById('penjualanErrorModal');
    const errorMessageEl = document.getElementById('penjualanErrorMessage');

    // 1. Event Delegation: Menangkap submit form hapus (karena tabel direload via AJAX)
    document.addEventListener('submit', function (e) {
        // Cek apakah yang disubmit adalah form dengan class 'delete-form'
        const formEl = e.target.closest('form.delete-form');

        if (formEl) {
            e.preventDefault(); // Mencegah reload halaman standar

            // Ambil pesan dari atribut data-confirm-message
            const msg = formEl.getAttribute('data-confirm-message') || 'Apakah Anda yakin ingin menghapus data penjualan ini?';
            if (confirmMessageEl) confirmMessageEl.textContent = msg;

            // Simpan data aksi dan token CSRF
            pendingDelete = {
                action: formEl.getAttribute('action'),
                csrf: (formEl.querySelector('input[name="_token"]')?.value) || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '')
            };

            // Tampilkan Modal Konfirmasi
            const modal = bootstrap.Modal.getOrCreateInstance(confirmModalEl);
            modal.show();
        }
    });

    // 2. Eksekusi Hapus saat tombol "Hapus" di modal diklik
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!pendingDelete) return;

            const { action, csrf } = pendingDelete;
            const params = new URLSearchParams();
            params.append('_method', 'DELETE');

            // Kirim request AJAX DELETE
            fetch(action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: params.toString()
            })
                .then(r => r.ok ? r.json() : r.json().then(j => Promise.reject(j)))
                .then(data => {
                    // Tutup Modal Konfirmasi
                    bootstrap.Modal.getOrCreateInstance(confirmModalEl).hide();

                    // Set Pesan Sukses
                    if (successMessageEl) {
                        successMessageEl.textContent = (data && data.message) || 'Data penjualan berhasil dihapus';
                    }

                    // Tampilkan Modal Sukses
                    const sm = bootstrap.Modal.getOrCreateInstance(successModalEl);
                    sm.show();

                    // Saat modal sukses ditutup, reload tabel
                    // Kita memanggil fungsi global loadPenjualanTable yang didefinisikan di index.blade.php
                    sm._element.addEventListener('hidden.bs.modal', function handler() {
                        sm._element.removeEventListener('hidden.bs.modal', handler);
                        if (typeof window.loadPenjualanTable === 'function') {
                            window.loadPenjualanTable(window.location.href);
                        } else {
                            window.location.reload();
                        }
                    });
                })
                .catch(err => {
                    // Handle Error
                    bootstrap.Modal.getOrCreateInstance(confirmModalEl).hide();

                    if (errorMessageEl) {
                        errorMessageEl.textContent = (err && err.message) || 'Gagal menghapus data. Silakan coba lagi.';
                    }

                    if (errorModalEl) {
                        bootstrap.Modal.getOrCreateInstance(errorModalEl).show();
                    }
                })
                .finally(() => {
                    pendingDelete = null;
                });
        });
    }
});