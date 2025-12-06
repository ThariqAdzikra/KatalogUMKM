<?php

return [
    "produk" => [
        "id_produk", "nama_produk", "merk", "id_kategori",
        "spesifikasi", "garansi", "harga_beli", "harga_jual",
        "stok", "gambar", "created_at", "updated_at"
    ],

    "penjualan" => [
        "id_penjualan", "id_user", "id_pelanggan",
        "tanggal_penjualan", "total_harga",
        "metode_pembayaran", "created_at", "updated_at"
    ],

    "penjualan_detail" => [
        "id_penjualan_detail", "id_penjualan", "id_produk",
        "jumlah", "harga_satuan", "subtotal",
        "created_at", "updated_at"
    ],

    "pembelian" => [
        "id_pembelian", "id_supplier", "id_user",
        "tanggal_pembelian", "total_harga",
        "created_at", "updated_at"
    ],

    "pembelian_detail" => [
        "id_pembelian_detail", "id_pembelian", "id_produk",
        "jumlah", "harga_satuan", "subtotal",
        "created_at", "updated_at"
    ],

    "pelanggan" => [
        "id_pelanggan", "id_kategori", "nama", "no_hp",
        "email", "alamat", "garansi",
        "tanggal_pembelian", "catatan",
        "created_at", "updated_at"
    ],

    "supplier" => [
        "id_supplier", "nama_supplier", "kontak",
        "alamat", "email",
        "created_at", "updated_at"
    ],

    "users" => [
        "id", "name", "email", "photo",
        "email_verified_at", "password", "role",
        "remember_token", "created_at", "updated_at"
    ]
];
