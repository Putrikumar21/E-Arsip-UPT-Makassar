<?php
session_start();
include 'koneksi.php';

// Cek login
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    // Ambil ID dan bersihkan agar aman dari SQL Injection
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // LANGKAH 1: Cari nama file PDF di database dulu sebelum datanya dihapus
    $query_cari = mysqli_query($conn, "SELECT file_pdf FROM surat WHERE id = '$id'");
    $data = mysqli_fetch_assoc($query_cari);

    if ($data) {
        $nama_file = $data['file_pdf'];
        $path_file = "uploads/" . $nama_file;

        // LANGKAH 2: Hapus data di Database
        $query_hapus = mysqli_query($conn, "DELETE FROM surat WHERE id = '$id'");

        if ($query_hapus) {
            // LANGKAH 3: Hapus file fisik di folder uploads (jika filenya ada)
            if (file_exists($path_file) && !empty($nama_file)) {
                unlink($path_file);
            }
            
            echo "<script>alert('Arsip Berhasil Dihapus!'); window.location='data_surat.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus data di database.'); window.location='data_surat.php';</script>";
        }
    } else {
        echo "<script>alert('Data tidak ditemukan!'); window.location='data_surat.php';</script>";
    }
} else {
    header("Location: data_surat.php");
}
?>