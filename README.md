<div align="center">

# Katalog UMKM: Sistem POS & Manajemen Terintegrasi AI

### Solusi Manajemen Bisnis dengan Integrasi Artificial Intelligence

[![Powered By](https://img.shields.io/badge/Powered%20By-Kolosal.ai-00d2ff?style=for-the-badge&logo=sparkles&logoColor=white)](https://kolosal.ai)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)

**Katalog UMKM** adalah sistem aplikasi kasir dan manajemen operasional cerdas yang **ditenagai oleh kecerdasan buatan dari Kolosal.ai**. Sistem ini menggabungkan manajemen stok, POS, dan _Generative AI_ untuk memberikan wawasan bisnis yang tajam.

[Instalasi](#-instalasi) • [Fitur AI](#-fitur-unggulan) • [Tim Pengembang](#-tim-telur-gulung)

</div>

---

## ⚡ Powered by Kolosal.ai

Aplikasi ini mendemonstrasikan kekuatan **Kolosal.ai** dalam mengubah cara UMKM bekerja. Dengan integrasi model LLM tercanggih, assistant kami mampu memahami konteks database bisnis secara mendalam dan memberikan rekomendasi yang akurat.

---

## Tentang Project

Aplikasi ini dibangun dengan fokus pada keamanan hak akses dan bantuan cerdas untuk operasional harian.

### Fitur Unggulan

1.  **Deep Think AI Assistant**

    -   Asisten virtual yang terintegrasi dengan database (Produk, Stok, Penjualan, Pelanggan).
    -   **Context-Aware:** Mampu menjawab pertanyaan terkait omset, pelanggan, dan stok barang.
    -   **Universal Search:** Pencarian data supplier, riwayat harga, dan tren penjualan.

2.  **AI Forecasting & Restock**

    -   Algoritma prediktif untuk menganalisa tren penjualan 3 bulan ke belakang.
    -   Memberikan rekomendasi restock barang berdasarkan prediksi permintaan.

3.  **Strict Role-Based Security**
    -   **Super Admin:** Kontrol penuh Dashboard, Laporan Keuangan, Manajemen User. Tidak memiliki akses input transaksi POS.
    -   **Pegawai:** Fokus pada operasional toko (POS) dan Input Pelanggan. Tidak memiliki akses ke laporan sensitif.

---

## Tim Telur Gulung

Project ini dikembangkan untuk **Hackathon IMPHNEN x Kolosal.AI 2025** oleh:

| No  | Nama Anggota                 |
| :-: | :--------------------------- |
| 1.  | **Muhammad Thariq Adzikra**  |
| 2.  | **Ryanda Valents Anakri**    |
| 3.  | **Andika Fitra Darmawan**    |
| 4.  | **Devakhri Farhan Hafizhan** |

---

## Fitur Lengkap

### Point of Sales (POS)

-   **Keranjang Belanja:** Input barang dengan scanner atau pencarian.
-   **Multi-Payment:** Dukungan Tunai, Transfer, dan QRIS.
-   **Cetak Struk:** Invoice otomatis dalam format PDF.
-   **Customer Database:** Penyimpanan data pelanggan.

### Manajemen Inventaris

-   **Stock Tracking:** Pengurangan stok otomatis saat penjualan.
-   **Low Stock Alert:** Notifikasi untuk barang hampir habis.
-   **Supplier Management:** Database pemasok dan riwayat pembelian.

### Laporan & Analitik (Super Admin)

-   **Dashboard:** Ringkasan Omset Harian, Mingguan, Bulanan.
-   **Top Products:** Grafik produk paling laris.
-   **Laporan Laba Rugi:** Export PDF/Excel.

### Katalog Online

-   **Landing Page:** Carousel promo, produk terbaru, dan profil toko.
-   **Detail Produk:** Informasi spesifikasi dan stok tersedia.

---

## Tech Stack

-   **Backend:** Laravel 12 (PHP 8.2+)
-   **Database:** MySQL 8.0
-   **Frontend:** Blade, TailwindCSS, Bootstrap 5, Alpine.js
-   **AI Engine:** Kolosal.ai (Claude Sonnet 3.5 Model) / OpenAI GPT-4o
-   **PDF Engine:** DomPDF
-   **Excel Engine:** Spatie Simple Excel

---

## Instalasi

Ikuti langkah ini untuk menjalankan project di lokal komputer Anda:

### 1. Clone Repository

```bash
git clone https://github.com/ThariqAdzikra/katalogLaptop.git
cd katalogLaptop
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Setup Environment

Salin file `.env` dan atur database serta API Key AI.

```bash
cp .env.example .env
php artisan key:generate
```

**Konfigurasi .env:**

```env
DB_DATABASE=katalog_umkm
# Pilih salah satu AI provider
KOLOSAL_API_KEY=your_key_here
# atau
OPENAI_API_KEY=your_key_here
```

### 4. Setup Database & Seeding

```bash
# Setup storage link untuk gambar
php artisan storage:link

# Migrasi dan isi data dummy
php artisan migrate --seed
```

### 5. Jalankan Aplikasi

```bash
# Terminal 1 (Build Assets)
npm run dev

# Terminal 2 (Server Laravel)
php artisan serve
```

Akses aplikasi di: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## Akun Demo

| Role            | Email                 | Password   | Akses                       |
| :-------------- | :-------------------- | :--------- | :-------------------------- |
| **Super Admin** | `admin@example.com`   | `password` | Dashboard, Laporan, AI Chat |
| **Pegawai**     | `pegawai@example.com` | `password` | POS Kasir, Input Pelanggan  |

---

## Contoh Prompt AI

Anda dapat mencoba prompt berikut pada fitur Chat Assistant:

-   _"Tampilkan 5 produk dengan stok paling sedikit"_
-   _"Berapa total omset penjualan bulan ini?"_
-   _"Siapa pelanggan yang paling sering belanja?"_
-   _"Apakah ada supplier yang menjual Laptop Acer?"_

---

<div align="center">

**Dibuat dengan ❤️ untuk Hackathon IMPHNEN x Kolosal.AI 2025**

</div>
