<?php 
session_start();
if (!isset($_SESSION['admin'])) { header("Location: index.php"); exit(); }
include 'koneksi.php'; 

// List Kategori Dokumen
$categories = [
    'Operasional', 'Pemeliharaan', 'Aset & Inventaris', 'K3', 
    'SOP & Regulasi', 'Administrasi', 'Pelaporan', 
    'Kontrak & Vendor', 'Dokumen Teknik'
];

$data_grafik = [];
$labels_grafik = [];

// Ambil data untuk grafik
foreach ($categories as $cat) {
    $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM surat WHERE kategori = '$cat'");
    $row = mysqli_fetch_assoc($q);
    $count = $row['total'];
    $data_grafik[] = $count;
    $labels_grafik[] = $cat;
}

$total_semua = array_sum($data_grafik);
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Arsip PLN UPT Makassar | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { 
            --pln-blue: #004685; 
            --pln-cyan: #00A2E9; 
            --sidebar-width: 280px;
        }
        
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }
        
        /* SIDEBAR MODERN - LOGO DI ATAS, FOOTER DI BAWAH */
        .sidebar { 
            width: var(--sidebar-width); 
            height: 100vh; 
            position: fixed; 
            top: 0; left: 0;
            background: linear-gradient(180deg, var(--pln-blue) 0%, #002d57 100%); 
            color: white; 
            padding: 30px 20px; 
            z-index: 1000;
            display: flex;
            flex-direction: column;
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
            width: 100%;
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

        /* MAIN CONTENT */
        .main-content { margin-left: var(--sidebar-width); padding: 40px; min-height: 100vh; }
        .welcome-banner { 
            background: linear-gradient(135deg, var(--pln-blue), var(--pln-cyan)); 
            color: white; border-radius: 20px; padding: 30px; margin-bottom: 40px;
            box-shadow: 0 10px 20px rgba(0, 70, 133, 0.15);
        }

        .card-stat { border: none; border-radius: 20px; box-shadow: 0 4px 25px rgba(0,0,0,0.05); background: white; }
        .card-category { border: 1px solid #f1f5f9; border-radius: 18px; background: white; padding: 25px; transition: 0.3s; height: 100%; }
        .card-category:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); border-color: var(--pln-cyan); }
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
    <div class="welcome-banner shadow-sm">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-1">E-Arsip UPT MAKASSAR</h2>
                <p class="mb-0 opacity-75">Selamat Datang, <b><?= htmlspecialchars($_SESSION['admin']); ?></b>. Pantau statistik dokumen hari ini.</p>
            </div>
            <div class="col-md-4 text-end">
                <h1 class="fw-bold m-0"><?= $total_semua; ?></h1>
                <small class="text-uppercase fw-semibold" style="letter-spacing: 1px;">Total Arsip Tersimpan</small>
            </div>
        </div>
    </div>

    <div class="card card-stat p-4 mb-5">
        <h6 class="fw-bold mb-4 text-muted text-uppercase small"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Statistik Kategori Dokumen</h6>
        <div style="height: 350px;">
            <canvas id="plnChart"></canvas>
        </div>
    </div>

    <h6 class="fw-bold mb-4 text-dark"><i class="bi bi-tags-fill text-primary me-2"></i>EKSPLORASI KATEGORI</h6>
    <div class="row g-4">
        <?php
        $ui_config = [
            'Operasional' => ['icon' => 'bi-gear-fill', 'color' => '#004685', 'bg' => '#e0f2fe'],
            'Pemeliharaan' => ['icon' => 'bi-tools', 'color' => '#b45309', 'bg' => '#fef3c7'],
            'Aset & Inventaris' => ['icon' => 'bi-box-seam-fill', 'color' => '#15803d', 'bg' => '#dcfce7'],
            'K3' => ['icon' => 'bi-shield-check', 'color' => '#b91c1c', 'bg' => '#fee2e2'],
            'SOP & Regulasi' => ['icon' => 'bi-journal-text', 'color' => '#6d28d9', 'bg' => '#ede9fe'],
            'Administrasi' => ['icon' => 'bi-file-earmark-person', 'color' => '#334155', 'bg' => '#f1f5f9'],
            'Pelaporan' => ['icon' => 'bi-graph-up-arrow', 'color' => '#a21caf', 'bg' => '#fae8ff'],
            'Kontrak & Vendor' => ['icon' => 'bi-briefcase-fill', 'color' => '#c2410c', 'bg' => '#ffedd5'],
            'Dokumen Teknik' => ['icon' => 'bi-cpu-fill', 'color' => '#4338ca', 'bg' => '#e0e7ff'],
        ];

        foreach ($categories as $index => $cat):
            $count = $data_grafik[$index];
            $cfg = $ui_config[$cat];
        ?>
        <div class="col-md-4 col-lg-3">
            <a href="data_surat.php?f_kategori=<?= urlencode($cat); ?>" class="text-decoration-none">
                <div class="card-category text-center">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 55px; height: 55px; border-radius: 14px; background: <?= $cfg['bg']; ?>; color: <?= $cfg['color']; ?>; font-size: 1.5rem;">
                        <i class="bi <?= $cfg['icon']; ?>"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1"><?= $cat; ?></h6>
                    <p class="text-muted small mb-0"><?= $count; ?> Dokumen</p>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    const ctx = document.getElementById('plnChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels_grafik); ?>,
            datasets: [{
                label: 'Jumlah Arsip',
                data: <?= json_encode($data_grafik); ?>,
                backgroundColor: '#00A2E9',
                borderColor: '#004685',
                borderWidth: 0,
                borderRadius: 10,
                barThickness: 35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8' } },
                x: { grid: { display: false }, ticks: { color: '#64748b' } }
            }
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>