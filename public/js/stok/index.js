(function() {
    'use strict';

    const form = document.querySelector('.filter-card form');
    const tableWrap = document.getElementById('stok-table');
    const confirmModalEl = document.getElementById('stokConfirmDeleteModal');
    const confirmBtn = document.getElementById('stokConfirmDeleteBtn');
    const confirmMsgEl = document.getElementById('stokConfirmDeleteMessage');
    const successModalEl = document.getElementById('stokSuccessModal');
    let pendingDelete = null; // { action, csrf }

    // Fungsi untuk memuat ulang tabel via AJAX
    function loadTable(url) {
        if (!url) return;
        const fullUrl = new URL(url, window.location.origin);
        fetch(fullUrl.toString(), {
                method: 'GET',
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
            })
            .catch(() => {
                window.location.href = fullUrl.toString();
            });
    }

    // Event Listener untuk Form Filter
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const params = new URLSearchParams(new FormData(form));
            const url = form.getAttribute('action') + '?' + params.toString();
            loadTable(url);
        });

        const searchInput = document.getElementById('stok-search-input');
        const status = form.querySelector('select[name="status_stok"]');
        const sortBy = form.querySelector('select[name="sort_by"]');
        const clearBtn = document.getElementById('stok-clear-search');

        function anyFilterActive() {
            const s = (searchInput?.value || '').trim();
            const st = status ? (status.value || '') : '';
            const sb = sortBy ? (sortBy.value || 'created_at') : 'created_at';
            return s.length > 0 || st !== '' || sb !== 'created_at';
        }

        function toggleClear() {
            if (!clearBtn) return;
            clearBtn.classList.toggle('d-none', !anyFilterActive());
        }

        if (status) status.addEventListener('change', () => {
            toggleClear();
            form.requestSubmit();
        });
        if (sortBy) sortBy.addEventListener('change', () => {
            toggleClear();
            form.requestSubmit();
        });
        if (searchInput) searchInput.addEventListener('input', toggleClear);

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                if (searchInput) searchInput.value = '';
                if (status) status.value = '';
                if (sortBy) sortBy.value = 'created_at';
                toggleClear();
                form.requestSubmit();
            });
        }

        toggleClear();
    }

    // Event Delegation untuk Pagination & Tombol Hapus di dalam Tabel AJAX
    if (tableWrap) {
        tableWrap.addEventListener('click', function(e) {
            const a = e.target.closest('a.page-link');
            if (a && a.getAttribute('href')) {
                e.preventDefault();
                loadTable(a.getAttribute('href'));
            }
        });

        tableWrap.addEventListener('submit', function(e) {
            const dform = e.target.closest('form.delete-form');
            if (dform) {
                e.preventDefault();
                pendingDelete = {
                    action: dform.getAttribute('action'),
                    csrf: (dform.querySelector('input[name="_token"]')?.value) || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '')
                };
                const msg = dform.getAttribute('data-confirm-message') || 'Yakin ingin menghapus produk ini?';
                if (confirmMsgEl) confirmMsgEl.textContent = msg;
                bootstrap.Modal.getOrCreateInstance(confirmModalEl).show();
            }
        });
    }

    // Logika Konfirmasi Hapus
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (!pendingDelete) return;
            const { action, csrf } = pendingDelete;
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
                    bootstrap.Modal.getOrCreateInstance(confirmModalEl).hide();
                    const sm = bootstrap.Modal.getOrCreateInstance(successModalEl);
                    const msgEl = document.getElementById('stokSuccessMessage');
                    if (msgEl) msgEl.textContent = (data && data.message) || 'Produk berhasil dihapus dari stok';
                    sm.show();
                    sm._element.addEventListener('hidden.bs.modal', function handler() {
                        sm._element.removeEventListener('hidden.bs.modal', handler);
                        loadTable(window.location.href);
                    });
                })
                .catch(err => {
                    bootstrap.Modal.getOrCreateInstance(confirmModalEl).hide();
                    alert((err && err.message) || 'Gagal menghapus produk');
                })
                .finally(() => {
                    pendingDelete = null;
                });
        });
    }
})();