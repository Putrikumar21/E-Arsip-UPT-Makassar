<nav class="navbar navbar-pln navbar-dark mb-5 shadow-sm" style="background-color: #004685;">
    <div class="container">
        <div class="d-flex align-items-center">
            <a class="navbar-brand fw-bold me-3" href="dashboard.php">
                <img src="https://upload.wikimedia.org/wikipedia/commons/2/20/Logo_PLN.svg" width="30" class="me-2">
                E-ARSIP
            </a>
            
            <button onclick="history.back()" class="btn btn-outline-light btn-sm border-0">
                <i class="bi bi-chevron-left"></i> Kembali
            </button>
        </div>

        <div class="d-flex align-items-center">
            <span class="text-white me-3 d-none d-md-inline small">
                User: <strong><?= $_SESSION['admin']; ?></strong>
            </span>
            
            <a href="logout.php" class="btn btn-danger btn-sm px-3 rounded-pill" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</nav>