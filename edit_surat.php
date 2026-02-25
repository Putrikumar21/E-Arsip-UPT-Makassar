<?php 
session_start();
if (!isset($_SESSION['admin'])) { header("Location: index.php"); exit(); }
include 'koneksi.php'; 

// Pastikan ID ada dan aman
if (!isset($_GET['id'])) { header("Location: data_surat.php"); exit(); }

$id = mysqli_real_escape_string($conn, $_GET['id']);
$get_data = mysqli_query($conn, "SELECT * FROM surat WHERE id = '$id'");
$d = mysqli_fetch_array($get_data);

// Jika data tidak ditemukan di database
if (!$d) { 
    echo "<script>alert('Data tidak ditemukan!'); window.location='data_surat.php';</script>";
    exit(); 
}

if (isset($_POST['update'])) {
    $nomor_surat   = mysqli_real_escape_string($conn, $_POST['nomor_surat']);
    $judul_surat   = mysqli_real_escape_string($conn, $_POST['judul_surat']);
    $kategori      = mysqli_real_escape_string($conn, $_POST['kategori']);
    $tahun_dokumen = mysqli_real_escape_string($conn, $_POST['tahun_dokumen']); // Format dari input date adalah YYYY-MM-DD
    
    if ($_FILES['file_pdf']['name'] != "") {
        $file_name = $_FILES['file_pdf']['name'];
        $file_tmp  = $_FILES['file_pdf']['tmp_name'];
        // Membersihkan nama file dari spasi dan karakter aneh
        $nama_file_baru = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
        
        if (file_exists("uploads/" . $d['file_pdf'])) {
            unlink("uploads/" . $d['file_pdf']);
        }
        
        move_uploaded_file($file_tmp, "uploads/" . $nama_file_baru);
        $file_sql = ", file_pdf = '$nama_file_baru'";
    } else {
        $file_sql = ""; 
    }

    // Query UPDATE disesuaikan dengan kolom DATE
    $query = "UPDATE surat SET 
                nomor_surat = '$nomor_surat', 
                judul_surat = '$judul_surat', 
                kategori = '$kategori', 
                tahun_dokumen = '$tahun_dokumen' 
                $file_sql 
              WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data Berhasil Diperbarui!'); window.location='data_surat.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Arsip | PLN UPT Makassar</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root { --pln-blue: #004685; --pln-cyan: #00A2E9; }
        body { background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif; }
        .card-edit { 
            border-radius: 20px; 
            border: none; 
            box-shadow: 0 10px 40px rgba(0,70,133,0.08); 
            background: white;
        }
        .form-label { color: #475569; font-size: 0.85rem; margin-bottom: 8px; }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
        }
        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(0, 70, 133, 0.1);
            border-color: var(--pln-blue);
        }
        .btn-save {
            background: var(--pln-blue);
            border: none;
            border-radius: 12px;
            padding: 12px 25px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-save:hover { background: #003361; transform: translateY(-2px); }
        .header-icon {
            width: 50px;
            height: 50px;
            background: rgba(0, 70, 133, 0.1);
            color: var(--pln-blue);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card card-edit p-4 p-md-5">
                <div class="header-icon">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <h3 class="fw-bold text-dark">Edit Dokumen</h3>
                <p class="text-muted small mb-4">Ubah data dokumen yang terpilih di bawah ini.</p>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label class="form-label fw-bold text-secondary">Nomor Surat</label>
                            <input type="text" name="nomor_surat" class="form-control shadow-none" value="<?= htmlspecialchars($d['nomor_surat']); ?>" placeholder="Contoh: 001/UPT-MKS/2026" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label fw-bold text-secondary">Tanggal Terbit Dokumen</label>
                            <input type="date" name="tahun_dokumen" class="form-control shadow-none" 
                                   value="<?= ($d['tahun_dokumen'] != '0000-00-00') ? date('Y-m-d', strtotime($d['tahun_dokumen'])) : ''; ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Kategori</label>
                        <select name="kategori" class="form-select shadow-none" required>
                            <?php 
                            $kats = ["Operasional", "Pemeliharaan", "Aset & Inventaris", "K3", "SOP & Regulasi", "Administrasi", "Pelaporan", "Kontrak & Vendor", "Dokumen Teknik"];
                            foreach($kats as $k) {
                                $sel = ($d['kategori'] == $k) ? "selected" : "";
                                echo "<option value='$k' $sel>$k</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Judul / Perihal</label>
                        <textarea name="judul_surat" class="form-control shadow-none" rows="3" required><?= htmlspecialchars($d['judul_surat']); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">File Lampiran (PDF)</label>
                        <div class="p-3 border rounded-3 bg-light">
                            <input type="file" name="file_pdf" class="form-control shadow-none mb-2" accept=".pdf">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>
                                <span style="font-size: 0.8rem;">File saat ini: <strong><?= $d['file_pdf']; ?></strong></span>
                            </div>
                        </div>
                        <small class="text-muted" style="font-size: 0.7rem;">*Biarkan kosong jika tidak ingin mengganti file PDF.</small>
                    </div>

                    <div class="d-flex gap-2 pt-2">
                        <button type="submit" name="update" class="btn btn-primary btn-save px-4 shadow-sm">
                            <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                        </button>
                        <a href="data_surat.php" class="btn btn-light border-0 px-4 fw-semibold text-secondary" style="border-radius: 12px; padding: 12px;">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>