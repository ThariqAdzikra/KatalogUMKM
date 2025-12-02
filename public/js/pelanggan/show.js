// =============================================
// === Script Search Bar Riwayat Pembelian ===
// =============================================
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('riwayatSearchInput');
    const riwayatList = document.getElementById('riwayatList');
    
    // Periksa apakah elemen-elemen ada sebelum melanjutkan
    if (!searchInput || !riwayatList) {
        return;
    }

    const riwayatItems = riwayatList.querySelectorAll('.riwayat-item');
    const emptyState = document.getElementById('riwayatEmptyState');

    // Sembunyikan empty state default jika ada item
    if (riwayatItems.length > 0 && emptyState) {
        emptyState.style.display = 'none';
    }

    searchInput.addEventListener('keyup', function () {
        const searchTerm = searchInput.value.toLowerCase();
        let itemsFound = 0;

        riwayatItems.forEach(function (item) {
            const namaProdukEl = item.querySelector('.riwayat-produk-nama');
            if (namaProdukEl) {
                const namaProduk = namaProdukEl.textContent.toLowerCase();
                
                if (namaProduk.includes(searchTerm)) {
                    item.style.display = ''; 
                    itemsFound++;
                } else {
                    item.style.display = 'none';
                }
            }
        });

        // Tampilkan atau sembunyikan pesan "Tidak ada hasil"
        if (emptyState) {
            if (itemsFound === 0) {
                // Jika state awal sudah "Tidak ada riwayat", jangan ubah
                if (riwayatItems.length > 0) {
                    emptyState.innerHTML = `
                        <i class="bi bi-search" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">Tidak ada hasil untuk "${searchInput.value}"</p>
                    `;
                }
                emptyState.style.display = '';
            } else {
                emptyState.style.display = 'none';
            }
        }
    });
});