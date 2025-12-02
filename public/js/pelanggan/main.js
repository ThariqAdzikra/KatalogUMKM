$(function() {
    // Ambil data yang di-pass dari file Blade
    const scriptData = $('#pelanggan-script-data');
    
    // Pastikan scriptData ada sebelum mencoba membaca
    if (scriptData.length === 0) {
        console.error('Data script untuk pelanggan tidak ditemukan.');
        return;
    }

    const searchUrl = scriptData.data('search-url');
    const baseUrl = scriptData.data('base-url');
    const csrfToken = scriptData.data('csrf-token');

    // 🔍 Fitur pencarian AJAX pelanggan
    const $tableBody = $('table tbody');
    const $searchInput = $('#searchInput');
    const $searchButton = $('#btnSearch');

    function renderRows(data) {
        $tableBody.empty();
        if (!data || data.length === 0) {
            $tableBody.append(`
                <tr><td colspan="6" class="text-center text-muted py-4">
                <i class="bi bi-inbox"></i> Tidak ada hasil yang cocok</td></tr>
            `);
            return;
        }

        data.forEach((p, i) => {
            // Gunakan data dari atribut data-*
            const detailUrl = `${baseUrl}/${p.id_pelanggan}`;
            const deleteUrl = `${baseUrl}/${p.id_pelanggan}`;

            $tableBody.append(`
                <tr>
                    <td class="text-center">${i + 1}</td>
                    <td><strong>${p.nama}</strong></td>
                    <td>${p.no_hp ?? '-'}</td>
                    <td>${p.email ?? '-'}</td>
                    <td>${p.alamat ?? '-'}</td>
                    <td>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="${detailUrl}" class="btn-action btn-info" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            <form method="POST" action="${deleteUrl}" class="mb-0" onsubmit="return confirm('Yakin hapus ${p.nama}?')">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn-action btn-delete" title="Hapus Pelanggan"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            `);
        });
    }

    function searchPelanggan() {
        const query = $searchInput.val().trim();
        $searchButton.prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Loading...');
        $.ajax({
            url: searchUrl, // Gunakan variabel searchUrl
            type: "GET",
            data: { query },
            success: renderRows,
            error: () => alert('Terjadi kesalahan saat mengambil data pelanggan.'),
            complete: () => $searchButton.prop('disabled', false).html('<i class="bi bi-search me-2"></i>Cari')
        });
    }

    let timer;
    $searchInput.on('input', () => { clearTimeout(timer); timer = setTimeout(searchPelanggan, 400); });
    $searchButton.on('click', searchPelanggan);
    $searchInput.on('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); searchPelanggan(); } });
});