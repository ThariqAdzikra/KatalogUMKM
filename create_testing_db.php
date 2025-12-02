<?php

/**
 * Script untuk membuat database 'testing' via PHP
 * Jalankan dengan: php create_testing_db.php
 */

// Load Laravel config
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Ambil credentials dari .env
$host = env('DB_HOST', '127.0.0.1');
$username = env('DB_USERNAME', 'root');
$password = env('DB_PASSWORD', '');

try {
    // Connect ke MySQL (tanpa specify database)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Buat database testing
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `testing` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    echo "✅ Database 'testing' berhasil dibuat!\n";
    echo "Sekarang jalankan: php artisan test\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nPastikan MySQL server berjalan dan credentials di .env benar.\n";
    exit(1);
}
