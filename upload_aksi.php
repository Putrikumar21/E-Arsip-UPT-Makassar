<?php
session_start();
include 'koneksi.php';

// 1. Proteksi Session
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['simpan'])) {
    // Tambahkan ini agar server tidak memutus koneksi saat upload file 100MB
    set_time_limit(300); // Batas waktu 5 menit

    $nomor = mysqli_real_escape_string($conn, $_POST['nomor_surat']);
    $judul = mysqli_real_escape_string($conn, $_POST['judul_surat']);
    
    // Penanganan File
    $file_input = $_FILES['file_arsip'];
    $nama_file  = $file_input['name'];
    $ukuran_file = $file_input['size'];
    $tmp_file   = $file_input['tmp_name'];
    $error_file = $file_input['error'];

    // 2. Cek Error Upload dari Server
    if ($error_file !== UPLOAD_ERR_OK) {
        $pesan = "Terjadi kesalahan sistem.";
        switch ($error_file) {
            case UPLOAD_ERR_INI_SIZE:   $pesan = "File terlalu besar! Cek setting php.ini (max 100MB)."; break;
            case UPLOAD_ERR_FORM_SIZE:  $pesan = "File melebihi batas form."; break;
            case UPLOAD_ERR_PARTIAL:    $pesan = "File hanya terunggah sebagian."; break;
            case UPLOAD_ERR_NO_FILE:    $pesan = "Pilih file terlebih dahulu!"; break;
        }
        echo "<script>alert('$pesan'); window.location='upload.php';</script>";
        exit;
    }

    // 3. Validasi Manual Ukuran (100MB = 104,857,600 Bytes)
    $max_size = 100 * 1024 * 1024;
    if ($ukuran_file > $max_size) {
        echo "<script>alert('Gagal! File melebihi batas 100MB.'); window.location='upload.php';</script>";
        exit;
    }

    // 4. Validasi Ekstensi
    $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
    if ($ekstensi != "pdf") {
        echo "<script>alert('Hanya file PDF yang diizinkan!'); window.location='upload.php';</script>";
        exit;
    }

    // 5. Siapkan Folder & Nama File Unik
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // Bersihkan judul dari karakter aneh untuk nama file
    $judul_clean = preg_replace("/[^a-zA-Z0-9]/", "_", $judul);
    $nama_baru = time() . '_' . $judul_clean . '.' . $ekstensi;
    $tujuan    = 'uploads/' . $nama_baru;

    // 6. Eksekusi Pindah File
    if (move_uploaded_file($tmp_file, $tujuan)) {
        $query = "INSERT INTO surat (nomor_surat, judul_surat, file_pdf) VALUES ('$nomor', '$judul', '$nama_baru')";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Arsip Berhasil Disimpan!'); window.location='data_surat.php';</script>";
        } else {
            // Hapus file jika database gagal agar tidak jadi sampah
            if (file_exists($tujuan)) { unlink($tujuan); }
            echo "Error Database: " . mysqli_error($conn);
        }
    } else {
        echo "Gagal memindahkan file ke folder uploads. Pastikan izin folder benar.";
    }
} else {
    header("Location: upload.php");
}
?>