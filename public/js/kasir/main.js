$(document).ready(function () {
  
    // ----------------------------------------------------
    // Mengatur CSRF Token untuk semua request AJAX
    // (Penting untuk keamanan)
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    // ----------------------------------------------------


  const formatRupiah = (angka) => 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID');
  const formatDate = (datetimeString) => {
    const options = { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(datetimeString).toLocaleDateString('id-ID', options);
  };
  const formatTanggalStruk = (datetimeString) => {
    const d = new Date(datetimeString);
    const tgl = d.getDate().toString().padStart(2, '0');
    const bln = (d.getMonth() + 1).toString().padStart(2, '0');
    const thn = d.getFullYear();
    const jam = d.getHours().toString().padStart(2, '0');
    const mnt = d.getMinutes().toString().padStart(2, '0');
    return `${tgl}/${bln}/${thn} ${jam}:${mnt}`;
  };

  /**
   * Menghitung tanggal akhir garansi.
   * @param {Date} startDate - Tanggal mulai (tanggal pembelian)
   * @param {number} years - Durasi garansi dalam tahun
   * @returns {object} - Objek berisi duration (teks durasi) & endDate (teks tgl berakhir)
   */
  function calculateWarranty(startDate, years) {
      if (!years || years <= 0 || isNaN(startDate.getTime())) {
          return { duration: 'Tidak ada garansi', endDate: '' };
      }
      
      const endDate = new Date(startDate);
      endDate.setFullYear(endDate.getFullYear() + years);
      
      const options = { day: 'numeric', month: 'long', year: 'numeric' };
      const formattedEndDate = endDate.toLocaleDateString('id-ID', options);
      
      const durationText = years + (years > 1 ? ' tahun' : ' tahun');
      
      return { 
          duration: `Garansi ${durationText}`, 
          endDate: `Berakhir: ${formattedEndDate}`
      };
  }


  // ========================
  // 🧮 Hitung Total (DIPERBARUI)
  // ========================
  function updateTotal() {
    let grandTotal = 0;
    const $ringkasan = $('#ringkasan-items');
    $ringkasan.empty();

    // Ambil tanggal pembelian SATU KALI
    const purchaseDate = new Date($('input[name="tanggal_penjualan"]').val());

    $('.produk-row').each(function () {
      const $selectedOption = $(this).find('.produk-select option:selected');
      const nama = $selectedOption.text().trim();
      const harga = parseFloat($(this).find('.harga-input').val()) || 0;
      const jumlah = parseInt($(this).find('.jumlah-input').val()) || 0;
      const subtotal = harga * jumlah;

      // Ambil data garansi
      const garansiTahun = parseInt($selectedOption.data('garansi')) || 0;
      let garansiHtml = '';

      if (garansiTahun > 0 && !isNaN(purchaseDate.getTime())) {
          const warrantyInfo = calculateWarranty(purchaseDate, garansiTahun);

          garansiHtml = `
            <div class="garansi-info-wrapper">
                <span class="garansi-note">${warrantyInfo.duration}</span>
                <span class="garansi-end-date">${warrantyInfo.endDate}</span>
            </div>`;
      }

      if (nama && harga > 0 && jumlah > 0) {
        grandTotal += subtotal;
        
        // Masukkan HTML garansi ke ringkasan
        $ringkasan.append(
          `<div class="ringkasan-item d-flex justify-content-between">
            <div>
              <strong>${nama}</strong><br>
              <small class="text-muted">${jumlah}x @ ${formatRupiah(harga)}</small>
              ${garansiHtml}
            </div>
            <div class="fw-semibold">${formatRupiah(subtotal)}</div>
          </div>`
        );
      }
    });
    $('#subtotal-display').text(formatRupiah(grandTotal));
    $('#total-display').text(formatRupiah(grandTotal));
    $('#total_harga').val(grandTotal);
  }

  // ========================
  // Inisialisasi Select Produk
  // ========================
  function initProdukSelect($select) {
    $select.select2({ theme: 'bootstrap-5', width: '100%', placeholder: '-- Pilih Produk --' });
    $select.on('select2:select', function () {
      const $row = $(this).closest('.produk-row');
      const selectedOption = $(this).find('option:selected');
      const harga = parseFloat(selectedOption.data('harga')) || 0;
      const stok = parseInt(selectedOption.data('stok')) || 0;
      
      $row.find('.harga-display').val(harga.toLocaleString('id-ID'));
      $row.find('.harga-input').val(harga);
      $row.find('.stok-info').text('Stok: ' + stok);
      $row.find('.jumlah-input').attr('max', stok); 
      updateTotal();
    });
  }
  initProdukSelect($('.produk-select'));
  
  // Update total saat jumlah diubah
  $(document).on('input', '.jumlah-input', function() {
    updateTotal();
  });

  $('input[name="tanggal_penjualan"]').on('change', function() {
    updateTotal();
  });

  // ========================
  // Tambah/Hapus Produk
  // ========================
  $('#add-row').click(function () {
    const produkOptions = $('.produk-select:first option').map(function () {
      return `<option value="${$(this).val()}" data-harga="${$(this).data('harga')}" data-stok="${$(this).data('stok')}" data-garansi="${$(this).data('garansi')}">${$(this).text()}</option>`;
    }).get().join('');
    
    const newRow = $(`
      <div class="row g-3 mb-3 produk-row align-items-end">
        <div class="col-md-5">
            <div class="produk-label-wrapper">
                <label class="form-label">Produk</label>
                <small class="text-muted stok-info"></small> 
            </div>
          <select name="produk[]" class="form-select produk-select" required>${produkOptions}</select>
        </div>
        <div class="col-md-2"><label class="form-label">Jumlah</label>
          <input type="number" name="jumlah[]" class="form-control jumlah-input" value="1" min="1" required>
        </div>
        <div class="col-md-3"><label class="form-label">Harga</label>
          <div class="input-group"><span class="input-group-text">Rp</span>
            <input type="text" class="form-control harga-display" readonly>
            <input type="hidden" name="harga_satuan[]" class="harga-input" value="0">
          </div>
        </div>
        <div class="col-md-2"><button type="button" class="btn btn-danger remove-row"><i class="bi bi-trash"></i></button></div>
      </div>`);
    $('#produk-wrapper').append(newRow);
    initProdukSelect(newRow.find('.produk-select'));
  });

  $(document).on('click', '.remove-row', function () {
    if ($('.produk-row').length > 1) {
        $(this).closest('.produk-row').remove();
        updateTotal();
    }
  });

  // ========================
  // Metode Pembayaran
  // ========================
  $('#metode_pembayaran').change(function () {
    const selectedMethod = $(this).val();
    $('#qris-preview').toggleClass('d-none', selectedMethod !== 'qris');
    $('#transfer-preview').toggleClass('d-none', selectedMethod !== 'transfer');
  });

  // ===================================
  // Pelanggan (Select2) - DIPERBARUI
  // ===================================
  $('#pelanggan-select').select2({
    theme: 'bootstrap-5',
    width: '100%',
    placeholder: '-- Cari atau Ketik Nama Pelanggan --',
    tags: true, 
    createTag: params => ({
      id: 'NEW_' + params.term, 
      text: params.term,
      newOption: true
    }),
    templateResult: data => {
      const $result = $('<span>').text(data.text);
      if (data.newOption) $result.append(' <em>(pelanggan baru)</em>');
      return $result;
    }
  });

  $('#pelanggan-select').on('select2:select', function (e) {
    const data = e.params.data;
    const $infoBox = $('#kolom-pelanggan-baru');
    const $judul = $('#judul-info-pelanggan');

    if (!data.id || data.id === "") {
        $infoBox.slideUp();
        // Kosongkan, hapus required, dan hapus readonly
        $('#nama_pelanggan_baru, #no_hp_baru, #email_baru, #alamat_baru')
            .val('')
            .prop('required', false)
            .prop('readonly', false);
        return; 
    }
    
    if (data.newOption) {
        // === KASUS PELANGGAN BARU ===
        $judul.html('<i class="bi bi-person-plus-fill me-2"></i>Data Pelanggan Baru');
        $infoBox.slideDown();
        
        // Atur field untuk input pelanggan baru
        $('#nama_pelanggan_baru').val(data.text).prop('required', true).prop('readonly', false);
        $('#no_hp_baru').val('').prop('required', true).prop('readonly', false).focus(); 
        $('#email_baru').val('').prop('required', false).prop('readonly', false);
        $('#alamat_baru').val('').prop('required', false).prop('readonly', false);
    
    } else {
        // === KASUS PELANGGAN LAMA ===
        const opt = $(this).find('option[value="' + data.id + '"]'); 
        
        $judul.html('<i class="bi bi-person-check-fill me-2"></i>Info Pelanggan Terdaftar');
        $infoBox.slideDown();

        // Isi field dari data-attributes dan buat readonly
        $('#nama_pelanggan_baru').val(opt.data('nama')).prop('required', false).prop('readonly', true);
        $('#no_hp_baru').val(opt.data('hp')).prop('required', false).prop('readonly', true);
        $('#email_baru').val(opt.data('email')).prop('required', false).prop('readonly', true);
        $('#alamat_baru').val(opt.data('alamat')).prop('required', false).prop('readonly', true);
    }
  });


  // ========================
  // Tampilkan Modal Konfirmasi (Struk)
  // ========================
  $('#showConfirmModal').on('click', function (e) {
    e.preventDefault();
    updateTotal(); 
    const form = $('#form-kasir')[0];

    // Validasi Form Bawaan
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    // Validasi Kustom (Pelanggan & Total)
    const pelangganValue = $('#pelanggan-select').val();
    if (!pelangganValue) {
        alert('Harap pilih pelanggan atau masukkan nama pelanggan baru.');
        $('#pelanggan-select').select2('open');
        return;
    }
    
    const total = parseFloat($('#total_harga').val()) || 0;
    if (total <= 0) {
      alert('Total belanja tidak boleh Rp 0. Pastikan produk dan jumlah sudah benar.');
      return;
    }

    // --- Lolos Validasi, Isi Modal Struk ---
    let pelangganText = $('#pelanggan-select option:selected').text().trim();
    if (pelangganValue.startsWith('NEW_')) {
        pelangganText = $('#nama_pelanggan_baru').val().trim();
    }
    let metodeText = $('#metode_pembayaran option:selected').text().trim() || '-';
    
    // Ambil tanggal pembelian dan format
    const purchaseDateString = $('input[name="tanggal_penjualan"]').val();
    const purchaseDate = new Date(purchaseDateString);
    let tanggalText = formatTanggalStruk(purchaseDateString);
    
    let totalDisplay = $('#total-display').text();
    let ringkasanProdukHTML = '';

    $('.produk-row').each(function () {
      const $selectedOption = $(this).find('.produk-select option:selected');
      const nama = $selectedOption.text().trim();
      const harga = parseFloat($(this).find('.harga-input').val());
      const jumlah = parseInt($(this).find('.jumlah-input').val());
      
      // Ambil data garansi
      const garansiTahun = parseInt($selectedOption.data('garansi')) || 0;
      let garansiStrukHtml = '';

      if (garansiTahun > 0 && !isNaN(purchaseDate.getTime())) {
          const warrantyInfo = calculateWarranty(purchaseDate, garansiTahun);
          // STRUKTUR BARU UNTIL MODAL STRUK
          garansiStrukHtml = `
          <div class="struk-garansi">
              <span class="garansi-note-struk">${warrantyInfo.duration}</span><br>
              <span class="garansi-end-date-struk">${warrantyInfo.endDate}</span>
          </div>`;
      }
      
      if (nama && harga > 0 && jumlah > 0 && $selectedOption.val()) {
        // Masukkan HTML garansi ke struk
        ringkasanProdukHTML += `
          <div class="struk-produk">
            <div class="nama-produk">${nama}</div>
            <div class="detail-produk">
              <span>${jumlah}x @ ${formatRupiah(harga)}</span>
              <span>${formatRupiah(harga * jumlah)}</span>
            </div>
            ${garansiStrukHtml}
          </div>`;
      }
    });

    // Masukkan data ke modal struk
    $('#struk-pelanggan').text(pelangganText);
    $('#struk-tanggal').text(tanggalText);
    $('#struk-metode').text(metodeText);
    $('#struk-items-list').html(ringkasanProdukHTML || '<p>Tidak ada produk.</p>');
    $('#struk-total').text(totalDisplay);

    bootstrap.Modal.getOrCreateInstance(document.getElementById('konfirmasiModal')).show();
  });

  // ===============================================
  // FUNGSI: Submit Form via AJAX (DIPERBAIKI / CARA PAKSA)
  // ===============================================

  // Variabel timer global untuk toast
  let toastTimer;

  function handleFormSubmit() {
    const $form = $('#form-kasir');
    const $printButton = $('#confirmSubmitAndPrint');
    const $saveButton = $('#confirmSubmitSaveOnly');
    const $batalButton = $('#konfirmasiModal .btn-secondary');

    // 1. Ambil data formulir
    let formData = $form.serialize();

    // 2. Ambil token CSRF secara manual dari <meta> tag
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // 3. Tambahkan token ke data yang akan dikirim
    formData += `&_token=${csrfToken}`;


    // Set loading state
    $printButton.prop('disabled', true);
    $saveButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
    $batalButton.prop('disabled', true);


    $.ajax({
        url: '/penjualan', // <-- PERBAIKAN PAKSA. Kirim ke rute WEB
        method: 'POST',
        data: formData,  // <-- Menggunakan data yang sudah kita perbaiki
        dataType: 'json',
        
        // Tambahkan header X-CSRF-TOKEN secara manual
        headers: {
            'X-CSRF-TOKEN': csrfToken 
        },

        success: function(response) {
            const konfirmasiModalEl = document.getElementById('konfirmasiModal');
            // Sembunyikan modal struk
            bootstrap.Modal.getOrCreateInstance(konfirmasiModalEl).hide();

            // Tampilkan modal sukses kasir (dengan animasi ceklis)
            const kasirSuccessEl = document.getElementById('kasirSuccessModal');
            if (kasirSuccessEl) {
                $('#kasirSuccessMessage').text(response.message || 'Transaksi berhasil disimpan.');
                const successModal = bootstrap.Modal.getOrCreateInstance(kasirSuccessEl);
                successModal.show();
                // Setelah modal sukses ditutup, reset form kasir
                kasirSuccessEl.addEventListener('hidden.bs.modal', resetKasirForm, { once: true });
            } else {
                // Fallback jika elemen modal tidak ditemukan
                alert(response.message || 'Transaksi berhasil disimpan.');
                resetKasirForm();
            }
        },

        error: function(xhr) {
            // Terjadi error (misal, stok habis)
            let errorMsg = 'Terjadi kesalahan. Silakan coba lagi.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.status === 422) {
                errorMsg = "Data tidak valid. Cek kembali stok atau inputan Anda.";
            } else if (xhr.status === 419 || xhr.status === 401) {
                // Error 419 (Page Expired) atau 401 (Unauthenticated)
                errorMsg = "Sesi Anda telah berakhir. Halaman akan dimuat ulang untuk keamanan.";
                // Muat ulang halaman untuk mendapatkan sesi baru
                location.reload(); 
            }
            alert('Error: ' + errorMsg); // Tampilkan error sebagai alert biasa

            // Kembalikan tombol ke state normal JIKA GAGAL
            $printButton.prop('disabled', false);
            $saveButton.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Langsung Simpan');
            $batalButton.prop('disabled', false);
        }
    });
  }

  // ===============================================
  // ✅ Hubungkan tombol ke fungsi yang TEPAT
  // ===============================================
  
  // --- Tombol "Langsung Simpan": HANYA menyimpan ---
  $('#confirmSubmitSaveOnly').on('click', function () {
    handleFormSubmit(); // Panggil fungsi simpan
  });

  // --- Tombol "Cetak Struk": HANYA mencetak ---
  $('#confirmSubmitAndPrint').on('click', function () {
    window.print(); // Panggil dialog print BROWSER
  });
  
  // ✅ BARU: Reset tombol jika modal konfirmasi ditutup manual
  document.getElementById('konfirmasiModal').addEventListener('hidden.bs.modal', function() {
      // Jika tombol masih disabled (karena tidak klik simpan), aktifkan lagi
      const $saveButton = $('#confirmSubmitSaveOnly');
      if ($saveButton.prop('disabled')) {
          $('#confirmSubmitAndPrint').prop('disabled', false);
          $saveButton.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Langsung Simpan');
          $('#konfirmasiModal .btn-secondary').prop('disabled', false);
      }
  });


  // --- Fungsi untuk reset form ---
  function resetKasirForm() {
      $('#form-kasir')[0].reset();
      
      // Reset tanggal ke 'now()'
      const now = new Date();
      now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
      // ✅ PERBAIKAN FINAL: Ubah .slice(0, 19) kembali ke .slice(0, 16)
      $('input[name="tanggal_penjualan"]').val(now.toISOString().slice(0, 16));

      $('#pelanggan-select').val(null).trigger('change');
      $('#kolom-pelanggan-baru').slideUp().find('input, textarea').prop('required', false).prop('readonly', false);
      $('#qris-preview, #transfer-preview').addClass('d-none');
      
      $('.produk-row:not(:first)').remove(); // Hapus baris produk tambahan
      
      // Reset baris produk pertama
      const $firstRow = $('.produk-row:first');
      $firstRow.find('.produk-select').val(null).trigger('change');
      $firstRow.find('.jumlah-input').val(1);
      $firstRow.find('.harga-display').val('');
      $firstRow.find('.harga-input').val(0);
      $firstRow.find('.stok-info').text('');

      updateTotal();
  }

  // Inisialisasi awal
  // Set tanggal saat load (jika value default dari blade tidak terekam JS)
  const defaultTgl = $('input[name="tanggal_penjualan"]').val();
  if (!defaultTgl) {
      const now = new Date();
      now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
      // ✅ PERBAIKAN FINAL: Ubah .slice(0, 19) kembali ke .slice(0, 16)
      $('input[name="tanggal_penjualan"]').val(now.toISOString().slice(0, 16));
  }
  updateTotal();

});