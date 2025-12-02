document.addEventListener('DOMContentLoaded', () => {

    const produkWrapper = document.getElementById('produk-wrapper');
    const addRowButton = document.getElementById('add-row');
    const rowTemplate = document.getElementById('produk-row-template');

    addRowButton.addEventListener('click', () => {
        // Periksa apakah template ada
        if (!rowTemplate) {
            console.error('Template #produk-row-template tidak ditemukan!');
            return;
        }

        // Clone konten dari template
        const templateContent = rowTemplate.content.cloneNode(true);
        const newRow = templateContent.querySelector('.produk-row');

        // Dapatkan elemen input di dalam baris baru
        const select = newRow.querySelector('.product-select');
        const quantity = newRow.querySelector('.quantity-input');
        const price = newRow.querySelector('.price-input');

        select.name = `produk[${newRowIndex}][id_produk]`;
        quantity.name = `produk[${newRowIndex}][jumlah]`;
        price.name = `produk[${newRowIndex}][harga_satuan]`;

        // Tambahkan index untuk baris berikutnya
        newRowIndex++;

        // Tambahkan baris baru ke dalam wrapper
        produkWrapper.appendChild(newRow);
    });

    // --- 2. Logika Hapus Baris (Event Delegation) ---
    produkWrapper.addEventListener('click', (e) => {
        // Cari tombol remove yang paling dekat dengan elemen yang di-klik
        const removeButton = e.target.closest('.remove-row');

        if (removeButton) {
            const rows = produkWrapper.querySelectorAll('.produk-row');

            if (rows.length > 1) {
                // Hapus elemen .produk-row terdekat
                removeButton.closest('.produk-row').remove();
            } else {
                alert('Minimal harus ada satu produk!');
            }
        }
    });

    // --- 3. Logika Update Harga & Jumlah (Event Delegation) ---
    produkWrapper.addEventListener('change', (e) => {
        const productSelect = e.target.closest('.product-select');

        if (productSelect) {
            const selectedProductId = productSelect.value;
            const row = productSelect.closest('.produk-row');

            // Temukan input harga dan jumlah di DALAM BARIS YANG SAMA
            const priceInput = row.querySelector('.price-input');
            const quantityInput = row.querySelector('.quantity-input');

            if (selectedProductId && allProducts[selectedProductId]) {
                // Jika produk dipilih dan ada di data JSON kita
                const productData = allProducts[selectedProductId];

                // Update nilai harga satuan
                if (priceInput) {
                    priceInput.value = productData.harga_satuan;
                }

                // Reset jumlah menjadi 1 (default)
                if (quantityInput) {
                    quantityInput.value = 1;
                }

            } else {
                if (priceInput) {
                    priceInput.value = 0;
                }
                if (quantityInput) {
                    quantityInput.value = 1; 
                }
            }
        }
    });

});