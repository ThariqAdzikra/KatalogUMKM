$(document).ready(function () {
  // =====================================================
  // 1. INITIALIZATION & CONFIGURATION
  // =====================================================

  // CSRF Token Setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // Application State
  let cart = [];
  const products = window.productsData || [];

  // DOM Element References
  const $productGrid = $('#product-grid');
  const $cartContainer = $('#cart-items-container');
  const $subtotalDisplay = $('#subtotal-display');
  const $totalDisplay = $('#total-display');
  const $searchInput = $('#search-product');
  const $categoryFilter = $('#filter-category');
  const $pelangganSelect = $('#pelanggan-select');
  const $btnViewCustomer = $('#btn-view-customer');

  // =====================================================
  // 2. UTILITY FUNCTIONS
  // =====================================================

  /**
   * Format number to Indonesian Rupiah
   */
  const formatRupiah = (angka) => {
    const number = Number(angka) || 0;
    return 'Rp ' + number.toLocaleString('id-ID');
  };

  /**
   * Show toast notification
   */
  const showToast = (message, type = 'info') => {
    const $container = $('#toast-container');
    const id = 'toast-' + Date.now();

    let icon = 'bi-info-circle';
    let headerClass = 'text-primary';

    if (type === 'success') { icon = 'bi-check-circle'; headerClass = 'text-success'; }
    if (type === 'warning') { icon = 'bi-exclamation-triangle'; headerClass = 'text-warning'; }
    if (type === 'error') { icon = 'bi-x-circle'; headerClass = 'text-danger'; }

    const html = `
      <div id="${id}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <i class="bi ${icon} me-2 ${headerClass}"></i>
          <strong class="me-auto ${headerClass}">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
          <small class="text-muted">Baru saja</small>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          ${message}
        </div>
      </div>
    `;

    $container.append(html);
    const toastEl = document.getElementById(id);
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();

    // Remove from DOM after hidden
    toastEl.addEventListener('hidden.bs.toast', () => {
      toastEl.remove();
    });
  };

  /**
   * Debounce function for search
   */
  const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  };

  // =====================================================
  // 3. RENDER FUNCTIONS
  // =====================================================

  /**
   * Render product grid with items
   */
  function renderProductGrid(items) {
    $productGrid.empty();

    if (items.length === 0) {
      $productGrid.html(`
        <div class="col-12 text-center text-muted py-5" style="grid-column: 1 / -1;">
          <i class="bi bi-inbox display-4 opacity-25 mb-3"></i>
          <p class="mb-0">Produk tidak ditemukan</p>
          <small class="opacity-75">Coba kata kunci lain</small>
        </div>
      `);
      return;
    }

    items.forEach(product => {
      // Image handling with fallback
      let imgHtml;
      if (product.gambar) {
        // Use the path directly, assuming it's stored correctly or needs 'storage/' prefix
        // If the path already contains 'storage/', don't add it again.
        const imgSrc = product.gambar.startsWith('storage/')
          ? `/${product.gambar}`
          : `/storage/${product.gambar}`;

        imgHtml = `
          <div class="product-img-wrapper">
            <img src="${imgSrc}" 
                 alt="${product.nama_produk}" 
                 loading="lazy"
                 onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\\'bi bi-image text-muted\\' style=\\'font-size: 3rem; opacity: 0.5;\\'></i>'">
          </div>
        `;
      } else {
        imgHtml = `
          <div class="product-img-wrapper">
            <i class="bi bi-laptop text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
          </div>
        `;
      }

      // Stock badge
      const stockBadge = product.stok > 0
        ? `<span class="badge bg-success bg-opacity-25 text-success position-absolute top-0 end-0 m-2" style="font-size: 0.7rem;">Stok: ${product.stok}</span>`
        : `<span class="badge bg-danger bg-opacity-25 text-danger position-absolute top-0 end-0 m-2" style="font-size: 0.7rem;">Habis</span>`;

      // Category badge
      const categoryName = product.kategori ? product.kategori.nama_kategori : '';
      const categoryBadge = categoryName
        ? `<span class="product-category"><i class="bi bi-tag"></i> ${categoryName}</span>`
        : '';

      // Stock info
      const stockInfo = `<span class="product-stock"><i class="bi bi-box-seam"></i> ${product.stok > 0 ? product.stok + ' unit' : 'Habis'}</span>`;

      // Product card HTML
      const cardHtml = `
        <div class="product-card ${product.stok <= 0 ? 'opacity-50' : ''}" 
             onclick="addToCart(${product.id_produk})"
             data-product-id="${product.id_produk}">
          ${imgHtml}
          <div class="product-info">
            <div class="product-title" title="${product.nama_produk}">
              ${product.nama_produk}
            </div>
            <div class="product-meta">
              ${categoryBadge}
              ${stockInfo}
            </div>
            <div class="product-price">${formatRupiah(product.harga_jual)}</div>
          </div>
        </div>
      `;

      $productGrid.append(cardHtml);
    });
  }

  /**
   * Render shopping cart
   */
  function renderCart() {
    $cartContainer.empty();
    let subtotal = 0;

    // Empty cart state
    if (cart.length === 0) {
      $cartContainer.html(`
        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted py-5">
          <i class="bi bi-basket display-4 opacity-25 mb-3"></i>
          <p class="mb-1 fw-medium">Keranjang Kosong</p>
          <small class="opacity-75">Pilih produk untuk memulai transaksi</small>
        </div>
      `);
      updateTotals(0);
      $('#cart-summary').addClass('d-none');
      return;
    }

    // Show summary if items exist
    $('#cart-summary').removeClass('d-none');

    // Render cart items
    cart.forEach((item, index) => {
      const itemSubtotal = item.price * item.qty;
      subtotal += itemSubtotal;

      const html = `
        <div class="cart-item" data-cart-index="${index}">
          <div class="cart-item-details">
            <div class="cart-item-title">${item.name}</div>
            <div class="cart-item-price">${formatRupiah(item.price)} × ${item.qty}</div>
          </div>
          <div class="cart-item-actions">
            <button class="btn-qty" onclick="updateQty(${index}, -1)" title="Kurangi">
              <i class="bi bi-dash"></i>
            </button>
            <span class="qty-display">${item.qty}</span>
            <button class="btn-qty" onclick="updateQty(${index}, 1)" title="Tambah">
              <i class="bi bi-plus"></i>
            </button>
          </div>
        </div>
      `;
      $cartContainer.append(html);
    });

    updateTotals(subtotal);
  }

  // Summary Toggle Logic
  $('#cart-summary-toggle').click(function () {
    const $summary = $('#cart-summary');
    $summary.toggleClass('collapsed');

    if ($summary.hasClass('collapsed')) {
      $(this).attr('title', 'Tampilkan detail pembayaran');
    } else {
      $(this).attr('title', 'Sembunyikan detail pembayaran');
    }
  });

  /**
   * Update total display
   */
  function updateTotals(subtotal) {
    $subtotalDisplay.text(formatRupiah(subtotal));
    $totalDisplay.text(formatRupiah(subtotal));
    $('#hidden_total_harga').val(subtotal);
  }

  // =====================================================
  // 4. CART LOGIC
  // =====================================================

  /**
   * Add product to cart
   */
  window.addToCart = function (id) {
    const product = products.find(p => p.id_produk == id);

    if (!product) {
      showToast('Produk tidak ditemukan', 'error');
      return;
    }

    if (product.stok <= 0) {
      showToast('Stok produk habis!', 'warning');
      return;
    }

    const existingItem = cart.find(item => item.id == id);

    if (existingItem) {
      if (existingItem.qty < product.stok) {
        existingItem.qty++;
        showToast(`${product.nama_produk} ditambahkan`, 'success');
      } else {
        showToast('Stok tidak mencukupi!', 'warning');
      }
    } else {
      cart.push({
        id: product.id_produk,
        name: product.nama_produk,
        price: product.harga_jual,
        qty: 1,
        maxStock: product.stok,
        garansi: product.garansi
      });
      showToast(`${product.nama_produk} ditambahkan ke keranjang`, 'success');
    }

    renderCart();
  };

  /**
   * Update item quantity
   */
  window.updateQty = function (index, change) {
    if (index < 0 || index >= cart.length) return;

    const item = cart[index];
    const product = products.find(p => p.id_produk == item.id);

    // Calculate new quantity
    const newQty = item.qty + change;

    // If quantity would go to 0 or below, remove the item
    if (newQty <= 0) {
      removeFromCart(index);
      return;
    }

    // Check stock limit when increasing
    if (change > 0 && product && newQty > product.stok) {
      showToast('Stok tidak cukup!', 'warning');
      return;
    }

    // Update quantity
    item.qty = newQty;
    renderCart();
    showToast('Jumlah diperbarui', 'success');
  };
  window.removeFromCart = function (index) {
    const item = cart[index];

    // Remove item if quantity is 0 or less
    cart.splice(index, 1);
    renderCart();
    showToast('Produk dihapus dari keranjang', 'info');
  };

  // =====================================================
  // 5. SEARCH & FILTER
  // =====================================================

  /**
   * Filter products based on search and category
   */
  function filterProducts() {
    const keyword = $searchInput.val().toLowerCase().trim();
    const category = $categoryFilter.val();

    const filtered = products.filter(p => {
      const matchName = p.nama_produk.toLowerCase().includes(keyword) ||
        (p.merk && p.merk.toLowerCase().includes(keyword)) ||
        (p.spesifikasi && p.spesifikasi.toLowerCase().includes(keyword));

      const matchCategory = category === 'all' ||
        (p.kategori && p.kategori.toLowerCase() === category);

      return matchName && matchCategory;
    });

    renderProductGrid(filtered);
  }

  // Debounced search
  const debouncedFilter = debounce(filterProducts, 300);

  $searchInput.on('input', debouncedFilter);
  $categoryFilter.on('change', filterProducts);

  // =====================================================
  // 6. CUSTOMER MANAGEMENT
  // =====================================================

  /**
   * Initialize Select2 for customer selection
   */
  // Initialize Select2 for Customer Selection with AJAX
  // Initialize Select2 for Customer Selection with AJAX
  $pelangganSelect.select2({
    theme: 'bootstrap-5',
    placeholder: '-- Cari atau Ketik Nama Pelanggan --',
    allowClear: true,
    minimumInputLength: 0,
    tags: true, // Allow typing new names
    ajax: {
      url: '/api/pelanggan/search',
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return {
          search: params.term || '',
          page: params.page || 1
        };
      },
      processResults: function (data) {
        return {
          results: data.data.map(function (customer) {
            return {
              id: String(customer.id_pelanggan), // Force string ID
              text: customer.nama_pelanggan + ' (' + customer.no_telepon + ')',
              customer: customer
            };
          }),
          pagination: {
            more: data.current_page < data.last_page
          }
        };
      },
      cache: true
    },
    createTag: function (params) {
      const term = $.trim(params.term);
      if (term === '') return null;
      return {
        id: 'NEW_TEMP_' + term,
        text: term,
        newOption: true
      };
    },
    templateResult: function (data) {
      if (data.loading) return data.text;
      if (data.newOption) {
        return $('<span><i class="bi bi-plus-circle me-1"></i> Tambah Pelanggan Baru: <strong>' + data.text + '</strong></span>');
      }
      return $('<span>').text(data.text);
    },
    templateSelection: function (data) {
      if (!data.id) return data.text; // Placeholder

      const text = data.text || String(data.id) || 'Pelanggan Terpilih';

      // Return a jQuery object with explicit styles to force visibility
      return $('<span>')
        .css({
          'color': '#ffffff',
          'display': 'block',
          'width': '100%',
          'font-weight': '500'
        })
        .text(text);
    }
  });

  /**
   * Handle customer selection
   */
  $pelangganSelect.on('select2:select', function (e) {
    const data = e.params.data;

    if (data.newOption) {
      // New customer - show modal
      const modal = new bootstrap.Modal(document.getElementById('newCustomerModal'));
      modal.show();

      // Pre-fill name
      $('#modal_nama_pelanggan').val(data.text);
      $('#modal_no_hp, #modal_email, #modal_alamat').val('');

      // Clear selection temporarily until saved
      $pelangganSelect.val(null).trigger('change');
      $btnViewCustomer.hide();
    } else {
      // Existing customer
      $btnViewCustomer.show();

      // Manually attach customer data to the option element
      // This ensures our fallback logic in 'View Detail' works
      if (data.customer) {
        const $option = $(this).find(`option[value="${data.id}"]`);
        if ($option.length) {
          $option.data('customer', data.customer);
        }
      }

      // Clear new customer fields
      $('#hidden_nama_pelanggan_baru, #hidden_no_hp_baru, #hidden_email_baru, #hidden_alamat_baru').val('');
    }
  });

  /**
   * Save new customer data
   */
  $('#btn-save-customer').click(function () {
    const nama = $('#modal_nama_pelanggan').val().trim();
    const hp = $('#modal_no_hp').val().trim();
    const email = $('#modal_email').val().trim();
    const alamat = $('#modal_alamat').val().trim();

    if (!nama) {
      alert('Nama pelanggan wajib diisi!');
      $('#modal_nama_pelanggan').focus();
      return;
    }

    // Store in hidden inputs for form submission
    $('#hidden_nama_pelanggan_baru').val(nama);
    $('#hidden_no_hp_baru').val(hp);
    $('#hidden_email_baru').val(email);
    $('#hidden_alamat_baru').val(alamat);

    // Create a temporary customer object for display
    const tempId = 'NEW_' + Date.now();
    const newOption = new Option(nama + ' (' + hp + ')', tempId, true, true);

    // Attach data to the option
    $(newOption).data('customer', {
      nama_pelanggan: nama,
      no_telepon: hp,
      email: email,
      alamat: alamat
    });

    // Add to Select2 and select it
    $pelangganSelect.append(newOption).trigger('change');

    // Update View Detail button visibility
    $btnViewCustomer.show();

    // Close modal
    const modalEl = document.getElementById('newCustomerModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    modal.hide();

    showToast('Data pelanggan disimpan sementara', 'success');
  });

  /**
   * View customer details
   */
  $btnViewCustomer.click(function () {
    // Try to get data from Select2
    let data = $pelangganSelect.select2('data')[0];
    let customer = null;

    if (data) {
      // 1. Try direct customer property (from AJAX)
      if (data.customer) {
        customer = data.customer;
      }
      // 2. Try data from element (from manually added option)
      else if (data.element) {
        customer = $(data.element).data('customer');
      }
    }

    // 3. Fallback: Try to find the selected option manually
    if (!customer) {
      const selectedId = $pelangganSelect.val();
      if (selectedId) {
        const $option = $pelangganSelect.find(`option[value="${selectedId}"]`);
        if ($option.length) {
          customer = $option.data('customer');
        }
      }
    }

    if (!customer) {
      // If still no customer, maybe it's an AJAX loaded one but 'customer' prop is lost?
      // In that case, we might only have text.
      if (data && data.text) {
        // Parse text if possible? No, that's unreliable.
        console.warn('Customer data not found, only text available:', data.text);
      }
      return;
    }

    $('#detail_nama').text(customer.nama_pelanggan || '-');
    $('#detail_hp').text(customer.no_telepon || '-');
    $('#detail_email').text(customer.email || '-');
    $('#detail_alamat').text(customer.alamat || '-');

    const modal = new bootstrap.Modal(document.getElementById('customerDetailModal'));
    modal.show();
  });

  // =====================================================
  // 7. CHECKOUT PROCESS
  // =====================================================

  /**
   * Prepare checkout modal
   */
  $('#btn-bayar').click(function () {
    // Validation
    if (cart.length === 0) {
      alert('Keranjang masih kosong!\nSilakan pilih produk terlebih dahulu.');
      return;
    }

    const pelangganId = $pelangganSelect.val();
    if (!pelangganId) {
      alert('Silakan pilih pelanggan terlebih dahulu.');
      $pelangganSelect.select2('open');
      return;
    }

    // Get customer name
    const pelangganText = $pelangganSelect.find('option:selected').text().trim() ||
      $('#hidden_nama_pelanggan_baru').val() ||
      'Umum';

    // Get payment method
    const metodeValue = $('#metode_pembayaran').val();
    const metodeText = $('#metode_pembayaran option:selected').text();

    // Get total
    const totalText = $totalDisplay.text();

    // Get current date
    const now = new Date();
    const tanggalText = now.toLocaleString('id-ID', {
      day: '2-digit',
      month: 'long',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });

    // Populate modal
    $('#struk-pelanggan').text(pelangganText);
    $('#struk-tanggal').text(tanggalText);
    $('#struk-metode').text(metodeText);
    $('#struk-total').text(totalText);

    // Populate items list
    let strukHtml = '';
    cart.forEach(item => {
      strukHtml += `
        <div class="struk-produk">
          <div class="nama-produk">${item.name}</div>
          <div class="detail-produk">
            <span>${item.qty}x @ ${formatRupiah(item.price)}</span>
            <span class="fw-semibold">${formatRupiah(item.price * item.qty)}</span>
          </div>
        </div>
      `;
    });
    $('#struk-items-list').html(strukHtml);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('konfirmasiModal'));
    modal.show();
  });

  /**
   * Submit transaction
   */
  function submitTransaction(printStruk = false) {
    // Populate hidden form fields
    $('#hidden_id_pelanggan').val($pelangganSelect.val());

    // Date in ISO format
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    $('#hidden_tanggal_penjualan').val(now.toISOString().slice(0, 16));

    $('#hidden_metode_pembayaran').val($('#metode_pembayaran').val());

    // Product inputs
    const $productInputs = $('#hidden-product-inputs');
    $productInputs.empty();

    cart.forEach(item => {
      $productInputs.append(`<input type="hidden" name="produk[]" value="${item.id}">`);
      $productInputs.append(`<input type="hidden" name="jumlah[]" value="${item.qty}">`);
      $productInputs.append(`<input type="hidden" name="harga_satuan[]" value="${item.price}">`);
    });

    // Disable buttons during submission
    const $modalButtons = $('#konfirmasiModal .modal-footer button');
    $modalButtons.prop('disabled', true);

    // AJAX submission
    const formData = $('#form-kasir').serialize();

    $.ajax({
      url: '/penjualan',
      method: 'POST',
      data: formData,
      success: function (response) {
        // Close confirmation modal
        bootstrap.Modal.getInstance(document.getElementById('konfirmasiModal')).hide();

        // Print if requested
        if (printStruk) {
          window.print();
        }

        // Show success modal
        const successModal = new bootstrap.Modal(document.getElementById('kasirSuccessModal'));
        successModal.show();

        // Reset application state
        resetApplication();

        $modalButtons.prop('disabled', false);
      },
      error: function (xhr) {
        const errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan transaksi';
        alert('ERROR: ' + errorMsg);

        $modalButtons.prop('disabled', false);
      }
    });
  }

  /**
   * Reset application to initial state
   */
  function resetApplication() {
    // Clear cart
    cart = [];
    renderCart();

    // Clear customer selection
    $pelangganSelect.val(null).trigger('change');
    $btnViewCustomer.hide();

    // Clear hidden inputs
    $('#hidden_nama_pelanggan_baru, #hidden_no_hp_baru, #hidden_email_baru, #hidden_alamat_baru').val('');

    // Reset search
    $searchInput.val('');
    $categoryFilter.val('all');
    renderProductGrid(products);
  }

  // Bind submit buttons
  $('#confirmSubmitSaveOnly').click(() => submitTransaction(false));
  $('#confirmSubmitAndPrint').click(() => submitTransaction(true));

  // Reset Cart Button
  $('#btn-reset-cart').click(function () {
    if (cart.length > 0) {
      if (confirm('Apakah Anda yakin ingin mengosongkan keranjang?')) {
        resetApplication();
        showToast('Keranjang berhasil dikosongkan', 'info');
      }
    } else {
      showToast('Keranjang sudah kosong', 'warning');
    }
  });

  // =====================================================
  // INITIALIZE
  // =====================================================

  // Initial render
  renderProductGrid(products);

  // Log initialization
  console.log('✅ POS System initialized');
  console.log(`📦 Loaded ${products.length} products`);

  // =====================================================
  // INFO WIDGET CLOCK
  // =====================================================
  function updateWidgetClock() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('id-ID', { hour12: false });
    const clockElement = document.getElementById('widget-clock');
    if (clockElement) {
      clockElement.innerText = timeString;
    }
  }

  // Update clock every second
  setInterval(updateWidgetClock, 1000);
  updateWidgetClock();
});