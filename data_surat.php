<?php 
// 1. Debugging & Session
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['admin'])) { 
    header("Location: index.php"); 
    exit(); 
}

include 'koneksi.php'; 

// 2. Tangkap Filter
$f_kategori = (isset($_GET['f_kategori']) && trim($_GET['f_kategori']) !== '') ? mysqli_real_escape_string($conn, trim($_GET['f_kategori'])) : '';
$f_cari     = (isset($_GET['f_cari']) && trim($_GET['f_cari']) !== '') ? mysqli_real_escape_string($conn, trim($_GET['f_cari'])) : '';
$f_mulai    = (isset($_GET['f_mulai']) && trim($_GET['f_mulai']) !== '') ? mysqli_real_escape_string($conn, trim($_GET['f_mulai'])) : '';
$f_selesai  = (isset($_GET['f_selesai']) && trim($_GET['f_selesai']) !== '') ? mysqli_real_escape_string($conn, trim($_GET['f_selesai'])) : '';

// --- LOGIKA PAGINATION ---
$batas = 10; 
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

$current_page = basename($_SERVER['PHP_SELF']);

function format_hari($date) {
    if (!$date || $date == '0000-00-00') return "-";
    $hari = date('D', strtotime($date));
    $map = ['Sun'=>'Minggu','Mon'=>'Senin','Tue'=>'Selasa','Wed'=>'Rabu','Thu'=>'Kamis','Fri'=>'Jumat','Sat'=>'Sabtu'];
    return isset($map[$hari]) ? $map[$hari] : $hari;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Arsip | PLN UPT Makassar</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root { --pln-blue: #004685; --pln-cyan: #00A2E9; --sidebar-width: 280px; }
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }
        
        /* SIDEBAR IDENTIK SESUAI PERMINTAAN */
        .sidebar { 
            width: var(--sidebar-width); height: 100vh; position: fixed; top: 0; left: 0; 
            background: linear-gradient(180deg, var(--pln-blue) 0%, #002d57 100%); 
            color: white; padding: 30px 20px; z-index: 1000; display: flex; flex-direction: column;
        }
        
        .sidebar-header { text-align: center; margin-bottom: 50px; }
        .sidebar-header img { width: 65px; margin-bottom: 15px; }
        .sidebar-header h6 { letter-spacing: 2px; font-weight: 800; font-size: 1.1rem; margin-bottom: 0; }

        .nav-link { 
            color: rgba(255,255,255,0.7); padding: 16px 20px; border-radius: 15px; 
            margin-bottom: 15px; text-decoration: none; display: flex; align-items: center; transition: 0.3s; 
            font-size: 1.05rem; font-weight: 500;
        }
        .nav-link i { font-size: 1.3rem; margin-right: 15px; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.15) !important; color: white !important; transform: translateX(8px); }
        .nav-link.active { background: var(--pln-cyan) !important; font-weight: 700; box-shadow: 0 5px 15px rgba(0, 162, 233, 0.4); }

        .sidebar-footer { margin-top: auto; }
        .btn-logout-custom {
            background: #ff3e3e; color: white !important; font-weight: 700; border-radius: 15px; padding: 15px;
            text-align: center; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 15px rgba(255, 62, 62, 0.3); margin-bottom: 20px; text-decoration: none; transition: 0.3s;
        }
        .btn-logout-custom:hover { background: #ff1a1a !important; transform: scale(1.03); }
        .footer-text { text-align: center; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; }

        /* Main Content */
        .main-content { margin-left: var(--sidebar-width); padding: 30px 40px; min-height: 100vh; }
        .card-table { border: none; border-radius: 20px; box-shadow: 0 4px 25px rgba(0,0,0,0.05); background: white; overflow: hidden; }
        .badge-kategori { background: #e0f2fe; color: var(--pln-blue); font-weight: 600; font-size: 0.75rem; padding: 6px 12px; border-radius: 8px; }

        .btn-action { width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; transition: all 0.3s ease; border: none; }
        .btn-view { background: rgba(0, 70, 133, 0.1); color: var(--pln-blue); }
        .btn-view:hover { background: var(--pln-blue); color: white; transform: translateY(-2px); }
        .btn-edit { background: rgba(255, 193, 7, 0.15); color: #947100; }
        .btn-edit:hover { background: #ffc107; color: white; transform: translateY(-2px); }
        .btn-delete { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
        .btn-delete:hover { background: #dc3545; color: white; transform: translateY(-2px); }

        .empty-state { padding: 80px 20px; text-align: center; }
        .empty-state i { font-size: 60px; color: #dee2e6; }
    </style>
</head>
<body>

<div class="sidebar shadow">
    <div class="sidebar-header">
        <img src="assets/logo_pln.png" alt="Logo">
        <h6 class="text-white">E-ARSIP PLN</h6>
        <small style="color: var(--pln-cyan); font-weight: bold;">UPT MAKASSAR</small>
    </div>
    <nav class="nav flex-column">
        <a href="dashboard.php" class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : ''; ?>"><i class="bi bi-grid-fill"></i> Dashboard</a>
        <a href="data_surat.php" class="nav-link <?= ($current_page == 'data_surat.php') ? 'active' : ''; ?>"><i class="bi bi-file-earmark-text-fill"></i> Semua Arsip</a>
        <a href="upload.php" class="nav-link <?= ($current_page == 'upload.php') ? 'active' : ''; ?>"><i class="bi bi-cloud-arrow-up-fill"></i> Upload Baru</a>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php" class="btn-logout-custom" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
            <i class="bi bi-power me-2"></i> LOGOUT SISTEM
        </a>
        <div class="footer-text">
            <small class="text-white-50" style="font-size: 0.75rem;">PT PLN (Persero) <br> <b style="color: rgba(255,255,255,0.6);">-PAK2026-</b></small>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Manajemen Arsip Digital</h4>
            <p class="text-muted small">Total arsip yang tersimpan dalam sistem UPT Makassar.</p>
        </div>
        <?php if($f_kategori !== '' || $f_mulai !== '' || $f_selesai !== '' || $f_cari !== ''): ?>
            <a href="data_surat.php" class="btn btn-outline-danger btn-sm rounded-pill px-3"><i class="bi bi-arrow-clockwise"></i> Reset Filter</a>
        <?php endif; ?>
    </div>

    <div class="card card-table mb-4">
        <div class="card-body p-4">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Kategori</label>
                    <select name="f_kategori" class="form-select border-0 bg-light">
                        <option value="">Semua Kategori</option>
                        <?php 
                        $cats = ["Operasional", "Pemeliharaan", "Aset & Inventaris", "K3" , "SOP & Regulasi" , "Administrasi" , "Pelaporan" , "Kontrak & Vendor" , "Dokumen Teknik"];
                        foreach($cats as $cat) {
                            $sel = ($f_kategori == $cat) ? 'selected' : '';
                            echo "<option value='$cat' $sel>$cat</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Mulai</label>
                    <input type="date" name="f_mulai" class="form-control border-0 bg-light" value="<?= htmlspecialchars($f_mulai); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Sampai</label>
                    <input type="date" name="f_selesai" class="form-control border-0 bg-light" value="<?= htmlspecialchars($f_selesai); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Kata Kunci</label>
                    <input type="text" name="f_cari" class="form-control border-0 bg-light" placeholder="No Surat / Judul..." value="<?= htmlspecialchars($f_cari); ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">Cari Data</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-table">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3">No</th>
                        <th class="py-3">Tanggal Dokumen</th>
                        <th class="py-3">Kategori</th>
                        <th class="py-3">Nomor Surat</th>
                        <th class="py-3">Judul Dokumen</th>
                        <th class="text-center py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $conditions = [];
                    if ($f_kategori !== '') { $conditions[] = "kategori = '$f_kategori'"; }
                    if ($f_cari !== '')     { $conditions[] = "(nomor_surat LIKE '%$f_cari%' OR judul_surat LIKE '%$f_cari%')"; }
                    if ($f_mulai !== '' && $f_selesai !== '') { $conditions[] = "tahun_dokumen BETWEEN '$f_mulai' AND '$f_selesai'"; }
                    elseif ($f_mulai !== '') { $conditions[] = "tahun_dokumen >= '$f_mulai'"; }
                    elseif ($f_selesai !== '') { $conditions[] = "tahun_dokumen <= '$f_selesai'"; }

                    $where_clause = (count($conditions) > 0) ? "WHERE " . implode(" AND ", $conditions) : "";

                    $query_count = "SELECT COUNT(*) as total FROM surat $where_clause";
                    $res_count = mysqli_query($conn, $query_count);
                    $total_data = mysqli_fetch_assoc($res_count)['total'];
                    $total_halaman = ceil($total_data / $batas);

                    $query = "SELECT * FROM surat $where_clause ORDER BY tahun_dokumen DESC LIMIT $halaman_awal, $batas";
                    $sql = mysqli_query($conn, $query);

                    $no = $halaman_awal + 1;
                    if($sql && mysqli_num_rows($sql) > 0) {
                        while($d = mysqli_fetch_array($sql)):
                            $tgl_raw = $d['tahun_dokumen'];
                            $tgl_format = ($tgl_raw && $tgl_raw != '0000-00-00') ? date('d/m/Y', strtotime($tgl_raw)) : '-';
                    ?>
                    <tr>
                        <td class="ps-4 text-muted"><?= $no++; ?></td>
                        <td>
                            <div class="small fw-bold text-dark"><?= $tgl_format; ?></div>
                            <small class="text-muted" style="font-size: 0.7rem;"><?= format_hari($tgl_raw); ?></small>
                        </td>
                        <td><span class="badge-kategori"><?= $d['kategori']; ?></span></td>
                        <td class="fw-semibold text-primary small"><?= $d['nomor_surat']; ?></td>
                        <td><div class="text-dark small fw-medium text-truncate" style="max-width: 250px;"><?= $d['judul_surat']; ?></div></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="uploads/<?= $d['file_pdf']; ?>" target="_blank" class="btn-action btn-view" title="Lihat"><i class="bi bi-eye-fill"></i></a>
                                <a href="edit_surat.php?id=<?= $d['id']; ?>" class="btn-action btn-edit" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                <a href="hapus_surat.php?id=<?= $d['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Hapus dokumen ini?')" title="Hapus"><i class="bi bi-trash3-fill"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; } else { ?>
                        <tr><td colspan="6" class="empty-state"><i class="bi bi-folder2-open d-block mb-3"></i><p class="text-muted">Tidak ada dokumen yang ditemukan.</p></td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if($total_data > 0): ?>
        <div class="card-footer bg-white border-top p-4 d-flex justify-content-between align-items-center">
            <small class="text-muted">Menampilkan <b><?= ($halaman_awal+1); ?>-<?= min($halaman_awal + $batas, $total_data); ?></b> dari <b><?= $total_data; ?></b> arsip</small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= ($halaman <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link shadow-none" href="?halaman=<?= $halaman-1; ?>&f_kategori=<?= urlencode($f_kategori); ?>&f_cari=<?= urlencode($f_cari); ?>&f_mulai=<?= $f_mulai; ?>&f_selesai=<?= $f_selesai; ?>">Prev</a>
                    </li>
                    <?php 
                    $start_page = max(1, $halaman - 2);
                    $end_page = min($total_halaman, $halaman + 2);
                    for($i=$start_page; $i<=$end_page; $i++): 
                    ?>
                        <li class="page-item <?= ($halaman == $i) ? 'active' : ''; ?>">
                            <a class="page-link shadow-none" href="?halaman=<?= $i; ?>&f_kategori=<?= urlencode($f_kategori); ?>&f_cari=<?= urlencode($f_cari); ?>&f_mulai=<?= $f_mulai; ?>&f_selesai=<?= $f_selesai; ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : ''; ?>">
                        <a class="page-link shadow-none" href="?halaman=<?= $halaman+1; ?>&f_kategori=<?= urlencode($f_kategori); ?>&f_cari=<?= urlencode($f_cari); ?>&f_mulai=<?= $f_mulai; ?>&f_selesai=<?= $f_selesai; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>