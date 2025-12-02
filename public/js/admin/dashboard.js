document.addEventListener('DOMContentLoaded', () => {
    // 1. Fungsi untuk mengambil data cuaca
    async function fetchWeather() {
        const apiKey = '93b7587a55f39ff4f0dc94e189ea5bd3';
        const city = 'Pekanbaru';
        const url = `https://api.openweathermap.org/data/2.5/weather?q=${city}&units=metric&lang=id&appid=${apiKey}`;

        const weatherWidget = document.getElementById('weather-widget');
        if (!weatherWidget) return;

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error('Gagal mengambil data cuaca');
            }

            const data = await response.json();

            const temp = Math.round(data.main.temp);
            const description = data.weather[0].description;
            const iconCode = data.weather[0].icon.slice(0, -1);

            const weatherIcons = {
                '01': '<i class="bi bi-sun-fill"></i>',
                '02': '<i class="bi bi-cloud-sun-fill"></i>',
                '03': '<i class="bi bi-cloud-fill"></i>',
                '04': '<i class="bi bi-clouds-fill"></i>',
                '09': '<i class="bi bi-cloud-drizzle-fill"></i>',
                '10': '<i class="bi bi-cloud-rain-fill"></i>',
                '11': '<i class="bi bi-cloud-lightning-rain-fill"></i>',
                '13': '<i class="bi bi-snow-fill"></i>',
                '50': '<i class="bi bi-cloud-fog-fill"></i>'
            };

            const icon = weatherIcons[iconCode] || '<i class="bi bi-cloud-sun"></i>';

            weatherWidget.innerHTML = `
                <span class="bi-icon">${icon}</span>
                <strong>${temp}°C</strong>
                <span style="opacity: 0.9;">${description} di ${city}</span>
            `;

            const iconElement = weatherWidget.querySelector('.bi-icon i');
            if (iconElement) {
                iconElement.style.fontSize = '1.5rem';
                iconElement.style.verticalAlign = 'bottom';
            }

        } catch (error) {
            console.error('Error fetching weather:', error);
            weatherWidget.innerHTML = '<span>Gagal memuat cuaca</span>';
        }
    }

    // 2. Fungsi untuk mengatur gambar Siang/Malam
    function updateWeatherImage() {
        const imgElement = document.getElementById('weather-image');
        if (!imgElement) return;

        const hour = new Date().getHours();
        const isDayTime = hour >= 6 && hour < 18;

        if (isDayTime) {
            imgElement.src = '/images/matahari.png';
            imgElement.alt = 'Ilustrasi Siang Hari';
        } else {
            imgElement.src = '/images/bulan.png';
            imgElement.alt = 'Ilustrasi Malam Hari';
        }

        imgElement.onerror = function () {
            console.warn('Gambar cuaca tidak ditemukan di path:', this.src);
            this.style.display = 'none';
        };
    }

    function updateClock() {
        const clockWidget = document.getElementById('live-clock');
        if (!clockWidget) return;

        const now = new Date();

        const dateOptions = { weekday: 'long', day: 'numeric', month: 'long' };
        const formattedDate = now.toLocaleDateString('id-ID', dateOptions);

        const timeOptions = { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Asia/Jakarta' };
        const formattedTime = now.toLocaleTimeString('id-ID', timeOptions).replace(/\./g, ':');

        clockWidget.innerHTML = `
            <span class="bi-icon"><i class="bi bi-clock"></i></span>
            <strong>${formattedTime}</strong>
            <span style="opacity: 0.9;">${formattedDate}</span>
        `;

        const iconElement = clockWidget.querySelector('.bi-icon i');
        if (iconElement) {
            iconElement.style.fontSize = '1.5rem';
            iconElement.style.verticalAlign = 'bottom';
        }
    }

    // Panggil fungsi cuaca, gambar, dan jam (SAMA SEPERTI SEBELUMNYA)
    fetchWeather();
    updateWeatherImage();
    updateClock();
    setInterval(updateClock, 1000);

    // Variabel global untuk menyimpan instance chart
    let penjualanChart = null;

    const canvas = document.getElementById('chartPenjualan');
    const ctx = canvas?.getContext('2d');
    const filterForm = document.getElementById('chart-filter-form');
    const dateStartInput = document.getElementById('chart-date-start');
    const dateEndInput = document.getElementById('chart-date-end');
    const loader = document.getElementById('chart-loader');

    // Pastikan semua elemen ada
    if (canvas && filterForm && dateStartInput && dateEndInput && loader) {

        // Fungsi untuk mengambil data dan memperbarui chart
        async function fetchAndUpdateChart(startDate, endDate) {

            // Tampilkan loader
            loader.classList.add('show');
            canvas.style.opacity = '0.3';

            try {
                // Sesuaikan URL jika route Anda berbeda
                const url = `/superadmin/dashboard/chart-data?start=${startDate}&end=${endDate}`;
                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                // Format tanggal (label) ke Bahasa Indonesia
                const formattedLabels = data.labels.map(date => {
                    const d = new Date(date);
                    const options = { day: 'numeric', month: 'short', timeZone: 'UTC' }; // UTC penting!
                    return d.toLocaleDateString('id-ID', options);
                });

                // Render atau update chart
                renderChart(formattedLabels, data.values);

            } catch (error) {
                console.error('Gagal mengambil data chart:', error);
                // Tampilkan pesan error di UI jika perlu
            } finally {
                // Sembunyikan loader
                loader.classList.remove('show');
                canvas.style.opacity = '1';
            }
        }

        // Fungsi untuk me-render chart (atau menghancurkan & me-render ulang)
        function renderChart(labels, values) {

            // Hancurkan chart lama jika ada
            if (penjualanChart) {
                penjualanChart.destroy();
            }

            // Warna tema untuk grafik - Dark Cyber Theme
            const mainColor = '#3b82f6';        // Electric Blue
            const accentColor = '#06b6d4';      // Cyan
            const gradientStart = '#8b5cf6';    // Purple

            // Buat chart baru
            penjualanChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Penjualan (Rp)',
                        data: values,
                        borderColor: mainColor,
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: mainColor,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointHoverBackgroundColor: accentColor,
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 3,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                color: '#cbd5e1',           // Light grey for dark theme
                                font: {
                                    size: 13,
                                    weight: '500'
                                },
                                padding: 15,
                                usePointStyle: true,
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(30, 33, 57, 0.95)',  // Dark navy
                            titleColor: '#ffffff',
                            bodyColor: '#e5e7eb',
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            borderColor: 'rgba(59, 130, 246, 0.3)',
                            borderWidth: 1,
                            callbacks: {
                                title: function (context) {
                                    return context[0].label;
                                },
                                label: function (context) {
                                    const value = context.parsed.y;
                                    return 'Total: Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#9ca3af',           // Medium grey
                                font: {
                                    size: 11
                                },
                                callback: function (value) {
                                    if (value >= 1000000) {
                                        return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                                    } else if (value >= 1000) {
                                        return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                                    }
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                }
                            },
                            grid: {
                                drawBorder: false,
                                color: 'rgba(59, 130, 246, 0.1)',  // Electric blue very subtle
                                lineWidth: 1,
                            }
                        },
                        x: {
                            ticks: {
                                color: '#9ca3af',           // Medium grey
                                font: {
                                    size: 11
                                }
                            },
                            grid: {
                                display: false,
                            }
                        }
                    },
                }
            });
        }

        // Fungsi helper untuk format tanggal YYYY-MM-DD
        function getFormattedDate(date) {
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        const today = new Date();
        const sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(today.getDate() - 6);

        const defaultStartDate = getFormattedDate(sevenDaysAgo);
        const defaultEndDate = getFormattedDate(today);

        dateStartInput.value = defaultStartDate;
        dateEndInput.value = defaultEndDate;

        // Tambahkan listener ke form 'submit'
        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const startDate = dateStartInput.value;
            const endDate = dateEndInput.value;

            if (startDate && endDate) {
                fetchAndUpdateChart(startDate, endDate);
            }
        });

        // Muat data chart pertama kali saat halaman dibuka
        fetchAndUpdateChart(defaultStartDate, defaultEndDate);

    } else {
        console.warn('Elemen filter chart, canvas, atau loader tidak ditemukan.');
    }

    const animateValue = (element, start, end, duration) => {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            element.textContent = new Intl.NumberFormat('id-ID').format(value);
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    };

    // Animasi untuk angka di stat-box
    document.querySelectorAll('.stat-box h2').forEach(el => {
        const finalValue = parseInt(el.textContent.replace(/\./g, ''));
        if (!isNaN(finalValue)) {
            animateValue(el, 0, finalValue, 1000);
        }
    });

    // Animasi untuk angka di statistik cepat
    document.querySelectorAll('.quick-stat-item h4').forEach(el => {
        const text = el.textContent.trim();
        if (!isNaN(text)) {
            const finalValue = parseInt(text);
            animateValue(el, 0, finalValue, 800);
        }
    });

});