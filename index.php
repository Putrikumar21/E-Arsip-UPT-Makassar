<?php
session_start();
include 'koneksi.php';

// Jika user sudah login, otomatis lempar ke dashboard
if (isset($_SESSION['admin'])) {
    header("Location: dashboard.php");
    exit();
}

$error = ""; 

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    
    if (mysqli_num_rows($query) > 0) {
        $_SESSION['admin'] = $username;
        echo "<script>window.location.href='dashboard.php';</script>";
        exit(); 
    } else {
        $error = "Username atau Password Salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Arsip Digital - UPT Makassar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #004685 0%, #00A2E9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #00A2E9;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card login-card">
                    <div class="card-body p-5 text-center">
                        <img src="assets/logo_pln.png" style="width: 70px;" class="mb-4" alt="Logo PLN">
                        
                        <h3 class="fw-bold" style="color: #004685; letter-spacing: -1px;">E-ARSIP UPT</h3>
                        <p class="text-muted small mb-4">Unit Pelaksana Transmisi Makassar</p>
                        
                        <form method="POST" action="" autocomplete="off">
                            <div class="mb-3">
                                <input type="text" name="username" 
                                       class="form-control form-control-lg text-center" 
                                       style="font-size: 15px; border-radius: 10px;" 
                                       placeholder="Username" 
                                       required 
                                       autocomplete="off"
                                       value="">
                            </div>
                            <div class="mb-4">
                                <input type="password" name="password" 
                                       class="form-control form-control-lg text-center" 
                                       style="font-size: 15px; border-radius: 10px;" 
                                       placeholder="Password" 
                                       required 
                                       autocomplete="new-password"
                                       value="">
                            </div>
                            
                            <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-bold shadow-sm" style="background-color: #004685; border: none; border-radius: 10px;">
                                LOGIN SISTEM
                            </button>
                            
                            <?php if($error != ""): ?>
                                <div class="alert alert-danger mt-3 py-2 small" role="alert">
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                        </form>
                        
                        <div class="mt-4">
                            <small class="text-muted">PT PLN (Persero)  <br> &copy; PAK2026</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>