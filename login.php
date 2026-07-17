<?php
// Memulai session untuk menyimpan data login
session_start();

// Panggil file koneksi yang tadi sudah kita buat
require_once 'config/koneksi.php';

// Polyfill untuk PHP di bawah versi 8.0 agar tidak Error call to undefined function str_ends_with
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool {
        return strlen($needle) === 0 || substr($haystack, -strlen($needle)) === $needle;
    }
}

// Jika user sudah login, langsung lempar ke halaman utama (index.php)
if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$error = '';

// ==========================================
// PROSES LOGIN MANUAL (FORM BIASA)
// ==========================================
if (isset($_POST['btn_login'])) {
    $email    = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Email dan password wajib diisi dulu, masee!";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } 
    else {
        // Cari email di database (nevorix_ios)
        $query  = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($koneksi, $query);

        if ($result && mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            
            // Verifikasi password (teks biasa, MD5, atau password_hash bawaan PHP)
            if ($password === $row['password'] || md5($password) === $row['password'] || password_verify($password, $row['password'])) {
                
                $_SESSION['login']   = true;
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['nama']    = !empty($row['nama']) ? $row['nama'] : 'User';
                $_SESSION['email']   = $row['email'];

                header("Location: index.php");
                exit;
            } else {
                $error = "Password-nya salah masee, coba inget-inget lagi!";
            }
        } else {
            $error = "Email belum terdaftar di database nevorix_ios masee!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nevorix Apple AI - Sign In</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.4)), url('assets/images/bg-login.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.03); 
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
            color: #fff;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h2 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: 700;
        }
        .login-header h2 span {
            color: #00a8ff;
        }
        .login-header p {
            margin: 0;
            color: #ccc;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #ddd;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            color: #fff;
            box-sizing: border-box;
            outline: none;
            font-size: 14px;
        }
        .form-group input:focus {
            border-color: #00a8ff;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #00a8ff, #0076ff);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: opacity 0.2s;
            margin-top: 10px;
        }
        .btn-login:hover {
            opacity: 0.9;
        }
        .error-message {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid #ef4444;
            color: #f87171;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>NEVORIX <span>APPLE AI</span></h2>
            <p>Silakan Masuk Untuk Mengakses Platform</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Alamat Email</label>
                <input type="email" name="email" placeholder= Email autocomplete="off">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" name="btn_login" class="btn-login">SIGN IN</button>
        </form>
    </div>
</body>
</html>