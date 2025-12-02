document.addEventListener('DOMContentLoaded', function () {

    // --- SETUP VARIABEL GLOBAL ---
    let formToSubmit = null;
    const modalEl = document.getElementById('confirmDeleteModal');
    const messageEl = document.getElementById('confirmDeleteMessage');
    const confirmBtn = document.getElementById('confirmDeleteBtn');

    const tableWrap = document.getElementById('pembelian-table');
    const filterForm = document.getElementById('filterFormPembelian');
    const searchInput = document.getElementById('searchInputPembelian');
    const clearBtn = document.getElementById('clearFiltersPembelian');
    const dariInput = filterForm ? filterForm.querySelector('input[name="dari_tanggal"]') : null;
    const sampaiInput = filterForm ? filterForm.querySelector('input[name="sampai_tanggal"]') : null;

    // --- FUNGSI UTAMA: LOAD TABEL VIA AJAX ---
    function loadTable(url) {
        const fullUrl = new URL(url, window.location.origin);
        return fetch(fullUrl.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(r => r.ok ? r.json() : r.text().then(t => Promise.reject(t)))
            .then(data => {
                if (data && data.html && tableWrap) {
                    tableWrap.innerHTML = data.html;
                    window.history.pushState({}, '', fullUrl.toString());
                }
            });
    }

    // --- FUNGSI: BANGUN URL DARI FILTER ---
    function buildUrlFromForm() {
        if (!filterForm) return window.location.href;
        const base = filterForm.getAttribute('action') || window.location.pathname;
        const params = new URLSearchParams(new FormData(filterForm));
        const url = new URL(base, window.location.origin);

        // Bersihkan value kosong
        for (const [k, v] of [...params.entries()]) {
            if (v === '' || v == null) params.delete(k);
        }
        url.search = params.toString();
        return url.toString();
    }

    // --- FUNGSI: TOGGLE TOMBOL CLEAR (X) ---
    function updateClearVisibility() {
        if (!clearBtn) return;
        const hasAny = !!(
            (searchInput && searchInput.value.trim() !== '') ||
            (dariInput && dariInput.value) ||
            (sampaiInput && sampaiInput.value)
        );
        clearBtn.classList.toggle('d-none', !hasAny);
    }

    // --- FUNGSI: SUBMIT FILTER ---
    function handleFilterSubmit(e) {
        if (e) e.preventDefault();
        const url = buildUrlFromForm();
        loadTable(url);
        updateClearVisibility();
    }

    // --- EVENT LISTENER UTAMA ---

    // 1. Delegasi Event untuk Submit Form Hapus & Filter
    document.addEventListener('submit', function (e) {
        const target = e.target;

        // Jika form hapus disubmit
        if (target.matches('form.delete-form')) {
            e.preventDefault();
            formToSubmit = target;
            const msg = target.getAttribute('data-confirm-message') || 'Apakah Anda yakin ingin menghapus data pembelian ini secara permanen?';
            if (messageEl) messageEl.textContent = msg;

            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

            // Jika form filter disubmit
        } else if (filterForm && target === filterForm) {
            handleFilterSubmit(e);
        }
    }, true);

    // 2. Auto-submit saat input tanggal berubah
    if (filterForm) {
        if (dariInput) dariInput.addEventListener('change', handleFilterSubmit);
        if (sampaiInput) sampaiInput.addEventListener('change', handleFilterSubmit);
        if (searchInput) searchInput.addEventListener('input', updateClearVisibility);
        updateClearVisibility();
    }

    // 3. Tombol Clear Filters
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (searchInput) searchInput.value = '';
            if (dariInput) dariInput.value = '';
            if (sampaiInput) sampaiInput.value = '';
            handleFilterSubmit();
        });
    }

    // 4. Pagination Links Click (AJAX)
    if (tableWrap) {
        tableWrap.addEventListener('click', function (e) {
            const a = e.target.closest('a.page-link');
            if (a && a.getAttribute('href')) {
                e.preventDefault();
                loadTable(a.getAttribute('href'));
            }
        });
    }

    // --- LOGIKA TOMBOL HAPUS DI MODAL ---
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!formToSubmit) return;
            const action = formToSubmit.getAttribute('action');
            const csrf = formToSubmit.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const params = new URLSearchParams();
            params.append('_method', 'DELETE');

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
                    // Tutup modal hapus
                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();

                    // Tampilkan modal sukses
                    const sm = bootstrap.Modal.getOrCreateInstance(document.getElementById('successModal'));
                    const msgEl = document.getElementById('successMessage');
                    if (msgEl) msgEl.textContent = (data && data.message) || 'Data pembelian berhasil dihapus';
                    sm.show();

                    // Reload tabel setelah modal sukses tertutup
                    sm._element.addEventListener('hidden.bs.modal', function handler() {
                        sm._element.removeEventListener('hidden.bs.modal', handler);
                        loadTable(window.location.href);
                    });
                })
                .catch(err => {
                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    alert((err && err.message) || 'Gagal menghapus data');
                })
                .finally(() => {
                    formToSubmit = null;
                });
        });
    }

    // --- LOGIKA MODAL SUKSES DARI SESSION (PAGE LOAD) ---
    // Mengecek apakah ada elemen penanda sukses dari Blade
    const successFlag = document.getElementById('flash-success-flag');
    if (successFlag) {
        const modalSuccessEl = document.getElementById('successModal');
        if (modalSuccessEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalSuccessEl);
            modal.show();
        }
    }

});