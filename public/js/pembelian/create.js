$(document).ready(function() {
    
    // Ambil data yang di-pass dari file Blade
    const scriptData = $('#pembelian-create-data');
    const searchUrl = scriptData.data('search-url');

    if (!searchUrl) {
        console.error('Search URL not found. Make sure #pembelian-create-data element exists with data-search-url attribute.');
        return;
    }

    // Simpan elemen input dalam variabel
    const kontakInput = $('#kontak-supplier');
    const alamatInput = $('#alamat-supplier');
    const namaInput = $('#nama-supplier');

    // 1. Fungsi Autocomplete Supplier
    namaInput.autocomplete({
        source: function(request, response) {
            $.ajax({
                url: searchUrl,
                dataType: "json",
                data: {
                    term: request.term 
                },
                success: function(data) {
                    response(data);
                },
                error: function(xhr, status, error) {
                    console.error('Error searching supplier:', error);
                    response([]);
                }
            });
        },
        minLength: 2, 
        
        select: function(event, ui) {
            // Saat item DIPILIH dari dropdown (Supplier Lama)
            namaInput.val(ui.item.value); // Set nama
            kontakInput.val(ui.item.kontak); // Set kontak
            alamatInput.val(ui.item.alamat); // Set alamat

            // Jadikan TIDAK WAJIB (karena supplier lama)
            kontakInput.prop('required', false);
            alamatInput.prop('required', false);
            
            // Hapus status error jika ada
            kontakInput.removeClass('is-invalid');
            alamatInput.removeClass('is-invalid');

            // Ubah teks helper
            $('#kontak-helper').text('Kontak supplier lama (otomatis).');
            $('#alamat-helper').text('Alamat supplier lama (otomatis).');

            return false; // Mencegah default action
        }
    });

    // Dipanggil setiap kali user MENGETIK di input Nama Supplier
    namaInput.on('input', function() {
        kontakInput.prop('required', true);
        alamatInput.prop('required', true);

        // Ubah teks helper kembali ke default
        $('#kontak-helper').text('Wajib diisi untuk supplier baru.');
        $('#alamat-helper').text('Wajib diisi untuk supplier baru.');
    });


    // 2. Fungsi Tombol Batal
    $('#btn-batal').on('click', function() {
        // Mengosongkan semua input di dalam form
        $('#form-step-1')[0].reset();
        
        // Atur ulang tanggal ke hari ini
        var today = new Date().toISOString().split('T')[0];
        $('#tanggal-pembelian').val(today);

        kontakInput.prop('required', false);
        alamatInput.prop('required', false);

        // Hapus status error jika ada
        kontakInput.removeClass('is-invalid');
        alamatInput.removeClass('is-invalid');

        // Kembalikan teks helper ke default
        $('#kontak-helper').text('Wajib diisi untuk supplier baru.');
        $('#alamat-helper').text('Wajib diisi untuk supplier baru.');
    });

});