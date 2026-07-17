<?php
session_start();
require_once '../config/koneksi.php'; // Hubungkan ke koneksi database utama

// =========================================================================
// 🔒 SECURITY PROTEKSI: Cegah bypass URL langsung tanpa login resmi
// =========================================================================
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || !isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit;
}

$email_session = $_SESSION['email'];

// Ambil data user secara dinamis dari database berdasarkan email session aktif
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email_session'");
$data_user = mysqli_fetch_assoc($query_user);

// Sinkronisasi data ke variabel dengan proteksi nilai NULL / Kosong
$user_id    = (!empty($data_user['id'])) ? $data_user['id'] : '1';

// Jika kolom 'nama' di database NULL atau kosong, gunakan session atau generate dari Email
if (!empty($data_user['nama'])) {
    $user_nama = $data_user['nama'];
} elseif (!empty($_SESSION['nama'])) {
    $user_nama = $_SESSION['nama'];
} else {
    // Fallback cerdas: Ambil nama depan dari email (contoh: admin@gmail.com -> Admin)
    $username_part = explode('@', $email_session)[0];
    $user_nama = ucfirst($username_part); 
}

$user_email = (!empty($data_user['email'])) ? $data_user['email'] : $email_session;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Nevorix Apple AI Premium</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; transition: background 0.3s ease, color 0.3s ease, border-color 0.3s ease; }
        
        /* ==================== VARIABLE SWITCHING THEME SYSTEM ==================== */
        :root {
            --bg-gradient: linear-gradient(rgba(10, 17, 34, 0.75), rgba(5, 10, 20, 0.85)), url('../assets/images/bg-Dashboard.jpg') no-repeat center center fixed;
            --sidebar-bg: rgba(6, 11, 25, 0.85);
            --content-bg: rgba(10, 15, 30, 0.4);
            --box-bg: rgba(6, 11, 25, 0.6);
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --border-color: rgba(0, 168, 255, 0.15);
            --input-bg: rgba(6, 11, 25, 0.7);
        }

        [data-theme="light"] {
            --bg-gradient: linear-gradient(rgba(240, 244, 248, 0.9), rgba(225, 235, 245, 0.95));
            --sidebar-bg: rgba(255, 255, 255, 0.95);
            --content-bg: rgba(240, 244, 248, 0.6);
            --box-bg: rgba(255, 255, 255, 0.85);
            --text-main: #0f172a;
            --text-muted: #475569;
            --border-color: rgba(0, 120, 255, 0.25);
            --input-bg: rgba(241, 245, 249, 0.9);
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg-gradient);
            background-size: cover;
            height: 100vh; display: flex; color: var(--text-main); overflow: hidden;
        }

        /* ==================== SIDEBAR BRANDING ==================== */
        .sidebar {
            width: 280px; background: var(--sidebar-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid var(--border-color); display: flex; flex-direction: column; justify-content: space-between; padding: 20px;
        }
        .sidebar-menu-top { display: flex; flex-direction: column; gap: 10px; }
        .brand { font-size: 20px; font-weight: 800; letter-spacing: 1.5px; padding: 10px 5px 25px 5px; text-align: center; color: var(--text-main); border-bottom: 1px solid var(--border-color); margin-bottom: 15px; }
        .brand span { color: #00a8ff; }
        
        .btn-menu { display: flex; align-items: center; gap: 12px; padding: 14px 18px; color: var(--text-muted); text-decoration: none; border-radius: 12px; font-size: 14px; font-weight: 600; border: 1px solid transparent; }
        .btn-menu:hover, .btn-menu.active { color: #00a8ff; background: rgba(0, 168, 255, 0.08); border-color: rgba(0, 168, 255, 0.2); }
        .btn-new-chat { background: rgba(0, 168, 255, 0.12); color: #00a8ff; border: 1px solid rgba(0, 168, 255, 0.3); margin-bottom: 20px; justify-content: center; }
        
        .sidebar-menu-bottom { border-top: 1px solid var(--border-color); padding-top: 20px; display: flex; flex-direction: column; gap: 12px; }
        .user-profile { font-size: 13px; color: var(--text-muted); padding: 5px; word-break: break-all; display: flex; flex-direction: column; gap: 4px; }
        .user-profile b { color: var(--text-main); }
        .status-container { display: flex; align-items: center; gap: 6px; }
        .status-dot { width: 8px; height: 8px; background-color: #10b981; border-radius: 50%; box-shadow: 0 0 8px #10b981; }
        
        .btn-logout { padding: 12px; background: linear-gradient(135deg, #ef4444, #b91c1c); color: white; text-align: center; text-decoration: none; border-radius: 10px; font-size: 13px; font-weight: 700; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2); }
        .btn-logout:hover { transform: translateY(-2px); }

        /* ==================== PANELS AND CONTENT ==================== */
        .main-content { flex: 1; padding: 40px; overflow-y: auto; background: var(--content-bg); }
        .content-title { font-size: 24px; font-weight: 700; margin-bottom: 25px; color: var(--text-main); border-bottom: 2px solid var(--border-color); padding-bottom: 12px; display: flex; align-items: center; gap: 12px; }
        .section-subtitle { font-size: 14px; color: #00a8ff; font-weight: 600; margin: 25px 0 12px 0; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px; }

        .setting-box {
            background: var(--box-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--border-color); margin-bottom: 20px;
            box-shadow: 0 6px 25px rgba(0,0,0, 0.15); backdrop-filter: blur(10px);
        }

        .form-group { margin-bottom: 18px; display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-size: 14px; color: var(--text-muted); font-weight: 500; }
        .form-group input, .form-group select {
            padding: 12px 16px; background: var(--input-bg); border: 1px solid var(--border-color);
            border-radius: 10px; color: var(--text-main); font-size: 14.5px; outline: none;
        }
        .form-group input[disabled] { opacity: 0.7; cursor: not-allowed; }
        
        .toggle-row { display: flex; justify-content: space-between; align-items: center; }
        .toggle-info h5 { font-size: 15px; color: var(--text-main); margin-bottom: 2px; }
        .toggle-info p { font-size: 13px; color: var(--text-muted); }
    </style>
</head>
<body>

    <script>
        const storedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', storedTheme);
    </script>

    <div class="sidebar">
        <div class="sidebar-menu-top">
            <div class="brand">NEVORIX <span>APPLE AI</span></div>
            <a href="../index.php" class="btn-menu btn-new-chat"><i class="fa-solid fa-wand-magic-sparkles"></i> ✨ New chat</a>
            <a href="library.php" class="btn-menu"><i class="fa-solid fa-folder-open"></i> 📁 Library</a>
            <a href="projects.php" class="btn-menu"><i class="fa-solid fa-briefcase"></i> 💼 Projects</a>
            <a href="settings.php" class="btn-menu active"><i class="fa-solid fa-gear"></i> ⚙️ Settings</a>
        </div>
        <div class="sidebar-menu-bottom">
            <div class="user-profile">
                <div class="status-container">
                    <span class="status-dot"></span>
                    <span>Profil Aktif:</span>
                </div>
                <b><?= htmlspecialchars($user_email); ?></b>
            </div>
            <a href="../logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> SIGN OUT</a>
        </div>
    </div>

    <div class="main-content">
        <div class="content-title"><i class="fa-solid fa-sliders"></i> Pengaturan Platform</div>
        
        <div class="section-subtitle"><i class="fa-solid fa-user-gear"></i> Profil Akun Pengguna</div>
        <div class="setting-box">
            <div class="form-group">
                <label>ID Pengguna (User ID)</label>
                <input type="text" value="#<?= htmlspecialchars($user_id); ?>" disabled style="font-weight: 600; color: #00a8ff;">
            </div>
            <div class="form-group">
                <label>Nama Lengkap Terdaftar</label>
                <input type="text" value="<?= htmlspecialchars($user_nama); ?>" disabled>
            </div>
            <div class="form-group">
                <label>Alamat Email Terverifikasi</label>
                <input type="text" value="<?= htmlspecialchars($user_email); ?>" disabled>
            </div>
        </div>

        <div class="section-subtitle"><i class="fa-solid fa-palette"></i> Preferensi Tampilan</div>
        <div class="setting-box">
            <div class="toggle-row">
                <div class="toggle-info">
                    <h5>Tema Halaman Kerja</h5>
                    <p>Sesuaikan mode tampilan sistem untuk kenyamanan visual mata Anda.</p>
                </div>
                <select id="themeSelector" onchange="switchTheme(this.value)" style="padding: 10px 16px; background: rgba(0,168,255,0.1); border: 1px solid #00a8ff; color:#00a8ff; border-radius:8px; font-weight:600; outline:none; cursor:pointer;">
                    <option value="dark">🌙 TEMA GELAP</option>
                    <option value="light">☀️ TEMA TERANG</option>
                </select>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('themeSelector').value = localStorage.getItem('theme') || 'dark';

        function switchTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
        }
    </script>
</body>
</html>