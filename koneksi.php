<?php
// 1. Pengaturan Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_arsip_upt";

// 2. Koneksi ke Database
$conn = mysqli_connect($host, $user, $pass, $db);

// 3. Cek Koneksi
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

/**
 * PENGATURAN WAKTU OTOMATIS
 * Anda bisa mengganti 'Asia/Makassar' ke 'Asia/Jakarta' atau 'Asia/Jayapura' 
 * di satu baris ini saja, dan seluruh sistem akan berubah otomatis.
 */
date_default_timezone_set('Asia/Makassar');

// 4. Sinkronisasi waktu antara PHP dan MySQL
// Ini memastikan fungsi NOW() di SQL sama dengan jam di PHP
mysqli_query($conn, "SET time_zone = '" . date('P') . "'");
?>