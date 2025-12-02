(function() {
    'use strict';

    const form = document.getElementById('form-edit-stok');
    const openBtn = document.getElementById('openConfirmEditStokBtn');
    const modalEl = document.getElementById('confirmEditStokModal');
    const confirmBtn = document.getElementById('confirmEditStokBtn');
    let submitting = false;

    // Event: Klik Tombol Update (Buka Modal Konfirmasi)
    if (openBtn) {
        openBtn.addEventListener('click', function(e) {
            if (!form) return;
            e.preventDefault();
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        });
    }

    // Event: Submit Form (Intercept buat Buka Modal jika di-enter)
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        });
    }

    // Fungsi: Eksekusi Simpan via AJAX
    function doAjaxSave() {
        if (!form || submitting) return;
        
        // Cek validasi HTML5 dulu
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        submitting = true;
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        const action = form.getAttribute('action');
        const fd = new FormData(form);
        fd.set('_method', 'PUT'); // Method spoofing Laravel
        
        // Ambil CSRF Token
        const csrf = (form.querySelector('input[name="_token"]')?.value) || 
                     (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

        fetch(action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf
            },
            body: fd
        })
        .then(r => r.ok ? r.json() : r.json().then(j => Promise.reject(j)))
        .then(data => {
            modal.hide();
            // Simpan flag sukses di localStorage biar muncul setelah reload
            localStorage.setItem('stok_edit_success', (data && data.message) || 'Data produk berhasil diperbarui!');
            window.location.reload();
        })
        .catch((err) => {
            modal.hide();
            const em = document.getElementById('editStokErrorModal');
            const msgEl = document.getElementById('editStokErrorMessage');
            let msg = 'Gagal menyimpan perubahan. Periksa input Anda.';
            
            // Parsing error message dari Laravel
            if (err) {
                if (typeof err === 'string') msg = err;
                else if (err.message) msg = err.message;
                else if (err.errors) {
                    const first = Object.values(err.errors)[0];
                    if (Array.isArray(first) && first.length) msg = first[0];
                }
            }
            
            if (msgEl) msgEl.textContent = msg;
            if (em) bootstrap.Modal.getOrCreateInstance(em).show();
        })
        .finally(() => {
            submitting = false;
        });
    }

    // Event: Klik "Simpan" di Modal Konfirmasi
    if (confirmBtn) {
        confirmBtn.addEventListener('click', doAjaxSave);
    }

    // Event: Cek localStorage saat halaman dimuat (untuk menampilkan modal sukses setelah reload)
    document.addEventListener('DOMContentLoaded', function() {
        const flag = localStorage.getItem('stok_edit_success');
        if (!flag) return;
        
        localStorage.removeItem('stok_edit_success');
        const successEl = document.getElementById('editStokSuccessModal');
        
        if (successEl) {
            const msgEl = document.getElementById('editStokSuccessMessage');
            if (msgEl) msgEl.textContent = flag;
            bootstrap.Modal.getOrCreateInstance(successEl).show();
        }
    });
})();