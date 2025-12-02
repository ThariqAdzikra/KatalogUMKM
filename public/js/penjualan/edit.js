function formatIDR(n){
    if(n == null || n === '') return '0';
    n = parseInt(n, 10) || 0;
    return n.toLocaleString('id-ID');
}

function hydrateRow($row){
    // init select2 (opsional)
    $row.find('select.produk-select').select2({ theme: 'bootstrap-5', width: '100%' });

    // set stok label saat awal
    const $select = $row.find('select.produk-select');
    const $stokInfo = $row.find('.stok-info');
    const $hargaHidden = $row.find('.harga-input');
    const $hargaDisplay = $row.find('.harga-display');

    const setFromSelected = () => {
        const $opt = $select.find(':selected');
        const harga = $opt.data('harga');
        const stok  = $opt.data('stok');

        if (typeof harga !== 'undefined') {
            $hargaHidden.val(harga);
            $hargaDisplay.val(formatIDR(harga));
        } else {
            // kalau kosong
            $hargaHidden.val('');
            $hargaDisplay.val('');
        }

        if (typeof stok !== 'undefined') {
            $stokInfo.text(`Stok: ${stok}`);
        } else {
            $stokInfo.text('');
        }
    };

    // trigger awal
    setFromSelected();

    // perubahan produk -> update harga & stok
    $select.on('change', setFromSelected);
}

// hydrate semua row yang sudah ada
$(document).ready(function(){
    $('#produk-wrapper .produk-row').each(function(){ hydrateRow($(this)); });
    
    // 隼 aktifkan select2 untuk pelanggan juga
    $('select[name="id_pelanggan"]').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: '-- Pilih Pelanggan --',
        allowClear: true
    });
});

// tambah row baru dengan struktur yang sama
document.getElementById('add-row').addEventListener('click', () => {
    const wrapper = document.getElementById('produk-wrapper');
    const first = wrapper.firstElementChild;
    const clone = first.cloneNode(true);

    // reset nilai
    $(clone).find('select.produk-select').val('').trigger('change.select2'); // reset select2
    $(clone).find('.jumlah-input').val(1);
    $(clone).find('.harga-input').val('');
    $(clone).find('.harga-display').val('');
    $(clone).find('.stok-info').text('');

    wrapper.appendChild(clone);
    hydrateRow($(clone));
});

// helper to show error modal (defined in Blade)
function showPenjualanEditError(message){
    try {
        const em = document.getElementById('editPenjualanErrorModal');
        const msgEl = document.getElementById('editPenjualanErrorMessage');
        if (msgEl) msgEl.textContent = message || 'Terjadi kesalahan.';
        if (em && window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(em).show();
            return;
        }
    } catch(_) {}
    // fallback
    console.error(message || 'Terjadi kesalahan.');
}

// hapus row
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-row') || e.target.closest('.remove-row')) {
        const rows = document.querySelectorAll('#produk-wrapper .produk-row');
        if (rows.length > 1) {
            e.target.closest('.produk-row').remove();
        } else {
            showPenjualanEditError('Minimal harus ada satu produk!');
        }
    }
});