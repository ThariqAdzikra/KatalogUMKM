$(function () {
    // Ambil data yang di-pass dari file Blade
    const scriptData = $("#pegawai-script-data");
    const searchUrl = scriptData.data("search-url");
    const csrfToken = scriptData.data("csrf-token");

    // Buat template URL untuk Edit dan Delete
    // Kita akan mengganti placeholder 'ID' dengan ID pegawai yang sebenarnya
    const editUrlTemplate = scriptData.data("edit-url-template");
    const deleteUrlTemplate = scriptData.data("delete-url-template");

    const $tableBody = $("table tbody");
    const $searchInput = $('input[name="search"]');
    const $searchButton = $(".btn-search");

    // ========== SEARCH FUNCTION ==========
    function renderRows(data) {
        $tableBody.empty();

        if (!data || data.length === 0) {
            $tableBody.append(`
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="bi bi-inbox"></i> Tidak ada pegawai yang cocok
                    </td>
                </tr>
            `);
            return;
        }

        data.forEach((p, i) => {
            // Ganti placeholder 'ID' dengan ID pegawai
            const editUrl = editUrlTemplate.replace("ID", p.id);
            const deleteUrl = deleteUrlTemplate.replace("ID", p.id);
            const formattedDate = p.created_at
                ? new Date(p.created_at).toLocaleDateString("id-ID", {
                      day: "2-digit",
                      month: "2-digit",
                      year: "numeric",
                  })
                : "-";

            $tableBody.append(`
                <tr>
                    <td class="text-center">${i + 1}</td>
                    <td><strong>${p.name}</strong></td>
                    <td>${p.email ?? "-"}</td>
                    <td><span class="badge bg-secondary">${
                        p.role ?? "-"
                    }</span></td>
                    <td>
                        <div class="d-flex gap-2">
                            <button 
                                class="btn-action btn-info" 
                                data-bs-toggle="modal"
                                data-bs-target="#detailModal"
                                data-nama="${p.name}"
                                data-email="${p.email ?? "-"}"
                                data-role="${p.role ?? "-"}"
                                data-tanggal="${formattedDate}"
                                title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </button>
                            <a href="${editUrl}" class="btn-action btn-edit" title="Edit Pegawai">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="${deleteUrl}" onsubmit="return confirm('Yakin hapus ${
                p.name
            }?')">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn-action btn-delete" title="Hapus Pegawai">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            `);
        });
    }

    function searchPegawai() {
        const query = $searchInput.val().trim();
        $searchButton
            .prop("disabled", true)
            .html('<i class="bi bi-hourglass-split me-2"></i>Memuat...');

        $.ajax({
            url: searchUrl, // Menggunakan URL dari data atribut
            type: "GET",
            data: { query },
            success: function (response) {
                renderRows(response);
            },
            error: function () {
                alert("Terjadi kesalahan saat memuat data pegawai.");
            },
            complete: function () {
                $searchButton
                    .prop("disabled", false)
                    .html('<i class="bi bi-search me-2"></i>Cari');
            },
        });
    }

    // event untuk tombol & input search
    if ($searchInput.length > 0) {
        $searchButton.on("click", searchPegawai);

        $searchInput.on("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                searchPegawai();
            }
        });

        let typingTimer;
        $searchInput.on("keyup", function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(searchPegawai, 400);
        });
    }

    // ✅ EVENT MODAL (ini versi benar)
    const detailModal = document.getElementById("detailModal");
    if (detailModal) {
        detailModal.addEventListener("show.bs.modal", function (event) {
            const button = event.relatedTarget;

            const nama = button.getAttribute("data-nama");
            const email = button.getAttribute("data-email");
            const role = button.getAttribute("data-role");
            const tanggal = button.getAttribute("data-tanggal");

            document.getElementById("modalNama").textContent = nama || "-";
            document.getElementById("modalEmail").textContent = email || "-";
            document.getElementById("modalTanggal").textContent =
                tanggal || "-";

            // Logic Badge Role
            const roleBadgeEl = document.getElementById("modalRoleBadge");
            if (role === "admin") {
                roleBadgeEl.innerHTML =
                    '<span class="badge bg-primary rounded-pill px-3">Admin</span>';
            } else if (role === "pegawai") {
                roleBadgeEl.innerHTML =
                    '<span class="badge bg-secondary rounded-pill px-3">Pegawai</span>';
            } else {
                roleBadgeEl.innerHTML = `<span class="badge bg-light text-dark border rounded-pill px-3">${
                    role || "-"
                }</span>`;
            }
        });
    }
});
