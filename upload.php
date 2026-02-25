<?php 
session_start();
if (!isset($_SESSION['admin'])) { 
    header("Location: index.php"); 
    exit(); 
}
include 'koneksi.php'; 

// Mendapatkan nama file untuk sidebar aktif
$current_page = basename($_SERVER['PHP_SELF']);

// Proses Simpan Data
if (isset($_POST['submit'])) {
    $nomor_surat   = mysqli_real_escape_string($conn, $_POST['nomor_surat']);
    $judul_surat   = mysqli_real_escape_string($conn, $_POST['judul_surat']);
    $kategori      = mysqli_real_escape_string($conn, $_POST['kategori']);
    $tgl_dokumen   = mysqli_real_escape_string($conn, $_POST['tgl_dokumen']); 
    
    // Proses File
    $file_name = $_FILES['file_pdf']['name'];
    $file_tmp  = $_FILES['file_pdf']['tmp_name'];
    $file_size = $_FILES['file_pdf']['size'];
    $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Membersihkan nama file
    $nama_file_baru = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
    $target_dir     = "uploads/" . $nama_file_baru;

    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // Validasi Ekstensi dan Ukuran (Maks 10MB)
    if ($file_ext == "pdf") {
        if ($file_size <= 10485760) {
            if (move_uploaded_file($file_tmp, $target_dir)) {
                $query = "INSERT INTO surat (nomor_surat, judul_surat, kategori, file_pdf, tahun_dokumen, created_at) 
                          VALUES ('$nomor_surat', '$judul_surat', '$kategori', '$nama_file_baru', '$tgl_dokumen', NOW())";
                
                if (mysqli_query($conn, $query)) {
                    echo "<script>alert('Arsip Berhasil Disimpan!'); window.location='data_surat.php';</script>";
                } else {
                    echo "<script>alert('Gagal menyimpan ke database: " . mysqli_error($conn) . "');</script>";
                }
            } else {
                echo "<script>alert('Gagal mengupload file ke server.');</script>";
            }
        } else {
            echo "<script>alert('Gagal! Ukuran file melebihi 10 MB.');</script>";
        }
    } else {
        echo "<script>alert('Hanya file PDF yang diperbolehkan!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Arsip | PLN UPT Makassar</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root { 
            --pln-blue: #004685; 
            --pln-cyan: #00A2E9; 
            --sidebar-width: 280px;
        }
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }
        
        /* SIDEBAR IDENTIK SESUAI DASHBOARD & DATA SURAT */
        .sidebar { 
            width: var(--sidebar-width); height: 100vh; position: fixed; top: 0; left: 0; 
            background: linear-gradient(180deg, var(--pln-blue) 0%, #002d57 100%); 
            color: white; padding: 30px 20px; z-index: 1000; display: flex; flex-direction: column;
        }

        .sidebar-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .sidebar-header img {
            width: 65px;
            margin-bottom: 15px;
        }

        .sidebar-header h6 {
            letter-spacing: 2px;
            font-weight: 800;
            font-size: 1.1rem;
            margin-bottom: 0;
        }
        
        .nav-link { 
            color: rgba(255,255,255,0.7); 
            padding: 16px 20px; 
            border-radius: 15px; 
            margin-bottom: 15px; 
            text-decoration: none; 
            display: flex; 
            align-items: center; 
            transition: all 0.3s;
            font-size: 1.05rem;
            font-weight: 500;
        }

        .nav-link i {
            font-size: 1.3rem;
            margin-right: 15px;
        }
        
        .nav-link:hover, .nav-link.active { 
            background: rgba(255,255,255,0.15); 
            color: white !important; 
            transform: translateX(8px);
        }

        .nav-link.active { 
            background: var(--pln-cyan) !important; 
            font-weight: 700;
            box-shadow: 0 5px 15px rgba(0, 162, 233, 0.4);
        }

        .sidebar-footer {
            margin-top: auto;
        }

        .btn-logout-custom {
            background: #ff3e3e; 
            color: white !important;
            font-weight: 700;
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(255, 62, 62, 0.3);
            margin-bottom: 20px;
            transition: 0.3s;
            text-decoration: none;
            border: none;
        }

        .btn-logout-custom:hover {
            background: #ff1a1a !important;
            transform: scale(1.03);
        }

        .footer-text {
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 15px;
        }

        /* Content Styles */
        .main-content { margin-left: var(--sidebar-width); padding: 40px; min-height: 100vh; }
        .card-upload { border: none; border-radius: 25px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); background: white; }
        .btn-pln { background: var(--pln-blue); color: white; border-radius: 12px; font-weight: 700; padding: 16px; transition: 0.3s; border: none; width: 100%; letter-spacing: 1px; }
        .btn-pln:hover { background: var(--pln-cyan); transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0, 162, 233, 0.3); }
        
        .form-label { font-weight: 600; color: #475569; font-size: 0.9rem; }
        .form-control, .form-select { border-radius: 12px; padding: 12px 15px; border: 1px solid #e2e8f0; background-color: #fdfdfd; }
        .form-control:focus, .form-select:focus { border-color: var(--pln-cyan); box-shadow: 0 0 0 4px rgba(0, 162, 233, 0.1); }
        .border-dashed { border: 2px dashed #e2e8f0 !important; transition: 0.3s; }
        .border-dashed:hover { border-color: var(--pln-cyan) !important; background: #f0f9ff !important; }
    </style>
</head>
<body>

<div class="sidebar shadow">
    <div class="sidebar-header">
        <img src="assets/logo_pln.png" alt="Logo PLN">
        <h6 class="text-white">E-ARSIP PLN</h6>
        <small style="color: var(--pln-cyan); font-weight: bold;">UPT MAKASSAR</small>
    </div>

    <nav class="nav flex-column">
        <a href="dashboard.php" class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="bi bi-grid-fill"></i> Dashboard
        </a>
        <a href="data_surat.php" class="nav-link <?= ($current_page == 'data_surat.php') ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-text-fill"></i> Semua Arsip
        </a>
        <a href="upload.php" class="nav-link <?= ($current_page == 'upload.php') ? 'active' : ''; ?>">
            <i class="bi bi-cloud-arrow-up-fill"></i> Upload Baru
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="btn-logout-custom" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
            <i class="bi bi-power me-2"></i> LOGOUT SISTEM
        </a>
        <div class="footer-text">
            <small class="text-white-50" style="font-size: 0.75rem;">
                PT PLN (Persero)<br>
                <b style="color: rgba(255,255,255,0.6);">- PAK2026 -</b>
            </small>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-upload p-4 p-md-5">
                <div class="mb-4 text-center">
                    <div class="mx-auto bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-cloud-arrow-up-fill text-primary fs-2"></i>
                    </div>
                    <h4 class="fw-bold text-dark">Arsip Dokumen Baru</h4>
                    <p class="text-muted small">Lengkapi informasi di bawah untuk mengunggah arsip ke server.</p>
                </div>
                
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nomor Surat</label>
                            <input type="text" name="nomor_surat" class="form-control" placeholder="No. Agenda / Surat" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Dokumen</label>
                            <input type="date" name="tgl_dokumen" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategori Dokumen</label>
                        <select name="kategori" class="form-select" required>
                            <option value="" selected disabled>-- Pilih Kategori --</option>
                            <option value="Operasional">Operasional</option>
                            <option value="Pemeliharaan">Pemeliharaan</option>
                            <option value="Aset & Inventaris">Aset & Inventaris</option>
                            <option value="K3">K3</option>
                            <option value="SOP & Regulasi">SOP & Regulasi</option>
                            <option value="Administrasi">Administrasi</option>
                            <option value="Pelaporan">Pelaporan</option>
                            <option value="Kontrak & Vendor">Kontrak & Vendor</option>
                            <option value="Dokumen Teknik">Dokumen Teknik</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Judul / Perihal Dokumen</label>
                        <input type="text" name="judul_surat" class="form-control" placeholder="Masukkan judul arsip lengkap" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Berkas PDF</label>
                        <div class="p-4 border-dashed text-center rounded-4 bg-light">
                            <i class="bi bi-file-earmark-pdf text-danger display-6 mb-2 d-block"></i>
                            <input type="file" name="file_pdf" class="form-control border-0 bg-transparent shadow-none mx-auto" style="max-width: 300px;" accept=".pdf" required>
                            <small class="text-muted mt-2 d-block">Hanya file <b>PDF</b> dengan ukuran maksimal <b>10 MB</b></small>
                        </div>
                    </div>

                    <div class="d-grid pt-2">
                        <button type="submit" name="submit" class="btn btn-pln">
                            <i class="bi bi-check-circle-fill me-2"></i> SIMPAN ARSIP
                        </button>
                    </div>
                </form>
            </div>
            <div class="text-center mt-4">
                <a href="data_surat.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Arsip</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>