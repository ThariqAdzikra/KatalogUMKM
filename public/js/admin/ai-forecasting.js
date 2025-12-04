// Standalone AI Forecasting Filter & Pagination
document.addEventListener('DOMContentLoaded', function () {
    const btnForecast = document.getElementById('btn-generate-forecast');
    const forecastLoading = document.getElementById('forecast-loading');
    const forecastResults = document.getElementById('forecast-results');
    const forecastTableBody = document.getElementById('forecast-table-body');
    const forecastEmpty = document.getElementById('forecast-empty');
    const forecastError = document.getElementById('forecast-error');
    const forecastFilterRow = document.getElementById('forecast-filter-row');
    const forecastFilter = document.getElementById('forecast-filter');
    const forecastPagination = document.getElementById('forecast-pagination');
    const paginationControls = document.getElementById('pagination-controls');

    // Global state
    let allForecastData = [];
    let filteredData = [];
    let currentPage = 1;
    const itemsPerPage = 10;

    if (!btnForecast) {
        console.warn('AI Forecasting: Button not found');
        return;
    }

    console.log('AI Forecasting: Initialized');

    // Fetchdata on button click
    btnForecast.addEventListener('click', async function () {
        console.log('AI Forecasting: Button clicked');

        // Reset UI - hide all content, show only loading
        forecastLoading.classList.remove('d-none');
        forecastResults.classList.add('d-none');
        forecastEmpty.classList.add('d-none');
        forecastError.classList.add('d-none');
        forecastFilterRow.classList.add('d-none');
        forecastPagination.classList.add('d-none');
        btnForecast.disabled = true;

        try {
            const response = await fetch('/superadmin/dashboard/forecast');
            const data = await response.json();

            if (data.error) {
                throw new Error(data.error);
            }

            console.log(`Loaded ${data.length} forecast items`);

            // Store data globally
            allForecastData = data;
            filteredData = [...data];
            currentPage = 1;

            // Show everything together
            forecastFilterRow.classList.remove('d-none');
            renderTable();
            forecastResults.classList.remove('d-none');

        } catch (error) {
            console.error('Forecast Error:', error);
            forecastError.textContent = 'Gagal memuat prediksi: ' + error.message;
            forecastError.classList.remove('d-none');
        } finally {
            forecastLoading.classList.add('d-none');
            btnForecast.disabled = false;
        }
    });

    // Filter change event
    if (forecastFilter) {
        forecastFilter.addEventListener('change', function () {
            applyFilter(forecastFilter.value);
        });
    }

    // Apply filter
    function applyFilter(status) {
        if (status === 'all') {
            filteredData = [...allForecastData];
        } else {
            filteredData = allForecastData.filter(item => item.status === status);
        }

        currentPage = 1;
        renderTable();
    }

    // Render table with pagination
    function renderTable() {
        const totalItems = filteredData.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const pageData = filteredData.slice(startIndex, endIndex);

        console.log(`Rendering: ${totalItems} total items, page ${currentPage}/${totalPages}, showing ${pageData.length} items`);

        // Clear table
        forecastTableBody.innerHTML = '';

        // Render rows
        if (pageData.length === 0) {
            forecastTableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <i class="bi bi-inbox display-6 d-block mb-3 opacity-50" style="color: rgba(59, 130, 246, 0.5);"></i>
                        <p class="mb-0" style="color: #9ca3af;">Tidak ada data untuk filter ini</p>
                    </td>
                </tr>
            `;
        } else {
            pageData.forEach((item, index) => {
                const actualIndex = startIndex + index + 1;

                // Status badge dengan warna - match backend values: 'aman', 'warning', 'danger'
                let statusBadge = '';
                if (item.status === 'danger') {
                    statusBadge = '<span class="badge badge-stock badge-habis">Perlu Restock</span>';
                } else if (item.status === 'warning') {
                    statusBadge = '<span class="badge badge-stock badge-menipis">Waspada</span>';
                } else { // status === 'aman'
                    statusBadge = '<span class="badge badge-stock badge-tersedia">Aman</span>';
                }

                const row = `
                    <tr>
                        <td class="text-center">${actualIndex}</td>
                        <td><strong>${item.nama_produk}</strong></td>
                        <td class="text-center">${item.prediksi} Unit</td>
                        <td>${item.saran}</td>
                        <td class="text-center">${statusBadge}</td>
                    </tr>
                `;
                forecastTableBody.innerHTML += row;
            });
        }

        // Update info counter
        document.getElementById('showing-count').textContent = totalItems;
        document.getElementById('total-count').textContent = allForecastData.length;

        // Render pagination - show if more than 1 page
        if (totalPages > 1) {
            renderPagination(totalPages);
            forecastPagination.classList.remove('d-none');
            console.log(`Pagination shown: ${totalPages} pages`);
        } else {
            forecastPagination.classList.add('d-none');
            console.log('Pagination hidden: only 1 page');
        }
    }

    // Render pagination controls
    function renderPagination(totalPages) {
        const maxVisible = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);

        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }

        let html = '';

        // Previous button
        html += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        `;

        // Page numbers
        for (let i = startPage; i <= endPage; i++) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }

        // Next button
        html += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        `;

        paginationControls.innerHTML = html;

        // Update page info
        document.getElementById('current-page').textContent = currentPage;
        document.getElementById('total-pages').textContent = totalPages;

        // Add event listeners
        paginationControls.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const page = parseInt(e.currentTarget.getAttribute('data-page'));
                if (page >= 1 && page <= totalPages) {
                    currentPage = page;
                    renderTable();

                    // Smooth scroll to top of table
                    document.getElementById('forecast-results').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }
});
