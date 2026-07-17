<?php
session_start();
$_SESSION['login'] = true;
$_SESSION['nama'] = "Dicki Firgiawan"; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi Fitur - Nevorix Apple AI Premium</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(rgba(10, 17, 34, 0.8), rgba(5, 10, 20, 0.9)), url('../assets/images/bg-Dashboard.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh; display: flex; color: #f1f5f9; overflow: hidden;
        }
        .sidebar {
            width: 280px; background: rgba(6, 11, 25, 0.85); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(0, 168, 255, 0.15); display: flex; flex-direction: column; justify-content: space-between; padding: 20px;
        }
        .sidebar-menu-top { display: flex; flex-direction: column; gap: 10px; }
        .brand { font-size: 20px; font-weight: 800; letter-spacing: 1.5px; padding: 10px 5px 25px 5px; text-align: center; color: #ffffff; text-shadow: 0 0 15px rgba(0, 168, 255, 0.4); border-bottom: 1px solid rgba(0, 168, 255, 0.1); margin-bottom: 15px; }
        .brand span { color: #00a8ff; }
        .btn-menu { display: flex; align-items: center; gap: 12px; padding: 14px 18px; color: #94a3b8; text-decoration: none; border-radius: 12px; font-size: 14px; font-weight: 600; transition: all 0.3s; border: 1px solid transparent; }
        .btn-menu:hover, .btn-menu.active { color: #00a8ff; background: rgba(0, 168, 255, 0.08); border-color: rgba(0, 168, 255, 0.2); box-shadow: 0 0 15px rgba(0, 168, 255, 0.1); }
        .btn-new-chat { background: rgba(0, 168, 255, 0.12); color: #00a8ff; border: 1px solid rgba(0, 168, 255, 0.3); margin-bottom: 20px; justify-content: center; }
        .sidebar-menu-bottom { border-top: 1px solid rgba(0, 168, 255, 0.1); padding-top: 20px; display: flex; flex-direction: column; gap: 15px; }
        .user-profile { display: flex; align-items: center; gap: 8px; font-size: 14px; color: #cbd5e1; }
        .status-dot { width: 8px; height: 8px; background-color: #10b981; border-radius: 50%; box-shadow: 0 0 8px #10b981; }
        .btn-logout { padding: 12px; background: linear-gradient(135deg, #ef4444, #b91c1c); color: white; text-align: center; text-decoration: none; border-radius: 10px; font-size: 13px; font-weight: 700; transition: transform 0.2s; }
        .btn-logout:hover { transform: scale(1.02); }

        .main-content { flex: 1; padding: 40px; overflow-y: auto; background: rgba(10, 15, 30, 0.4); }
        .content-title { font-size: 24px; font-weight: 700; margin-bottom: 25px; color: #ffffff; text-shadow: 0 0 10px rgba(0, 168, 255, 0.3); border-bottom: 2px solid rgba(0, 168, 255, 0.2); padding-bottom: 12px; display: flex; align-items: center; gap: 12px; }
        
        .card {
            background: rgba(6, 11, 25, 0.7); border: 1px solid rgba(0, 168, 255, 0.15);
            backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            border-radius: 16px; padding: 28px; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .card h3 { color: #00a8ff; font-size: 18px; margin-bottom: 14px; display: flex; align-items: center; gap: 10px; }
        .card p { font-size: 15px; color: #cbd5e1; line-height: 1.7; text-align: justify; }
        .tech-stack { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
        .badge-tech { background: rgba(0, 168, 255, 0.06); border: 1px solid rgba(0, 168, 255, 0.2); padding: 6px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; color: #00a8ff; display: flex; align-items: center; gap: 6px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-menu-top">
            <div class="brand">NEVORIX <span>APPLE AI</span></div>
            <a href="../index.php" class="btn-menu btn-new-chat"><i class="fa-solid fa-wand-magic-sparkles"></i> ✨ New chat</a>
            <a href="library.php" class="btn-menu"><i class="fa-solid fa-folder-open"></i> 📁 Library</a>
            <a href="projects.php" class="btn-menu active"><i class="fa-solid fa-briefcase"></i> 💼 Projects</a>
            <a href="settings.php" class="btn-menu"><i class="fa-solid fa-gear"></i> ⚙️ Settings</a>
        </div>
        <div class="sidebar-menu-bottom">
            <div class="user-profile">
                <span class="status-dot"></span>
                <span>Active: <b><?= htmlspecialchars($_SESSION['nama']); ?></b></span>
            </div>
            <a href="../logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> SIGN OUT</a>
        </div>
    </div>

    <div class="main-content">
        <div class="content-title"><i class="fa-solid fa-info-circle"></i> Ringkasan Fitur & Cara Kerja Nevorix AI</div>
        
        <div class="card">
            <h3><i class="fa-solid fa-wand-magic-sparkles"></i> Deteksi Kondisi iPhone Otomatis & Cepat</h3>
            <p>Nevorix Apple AI adalah asisten pintar interaktif yang dirancang khusus untuk membantu Anda menilai kelayakan fisik dan fungsi dari iPhone bekas secara instan. Melalui obrolan (chat) yang santai dan natural, asisten AI kami akan memahami detail spesifikasi perangkat yang Anda ketikkan, lalu menganalisisnya secara cerdas untuk memberikan gambaran kondisi unit yang akurat.</p>
            <div class="tech-stack">
                <span class="badge-tech"><i class="fa-solid fa-comments"></i> Chatbot Interaktif</span>
                <span class="badge-tech"><i class="fa-solid fa-bolt"></i> Analisis Instan</span>
                <span class="badge-tech"><i class="fa-solid fa-shield-halved"></i> Bebas Ribet</span>
            </div>
        </div>

        <div class="card">
            <h3><i class="fa-solid fa-chart-simple"></i> Standar Penilaian Berdasarkan Pasar Terbaik</h3>
            <p>Sistem kami bekerja dengan membandingkan parameter krusial iPhone seperti tingkat kesehatan baterai (Battery Health), kemulusan fisik, serta status keaslian komponen dengan data standar pasar ponsel bekas terkini. Kami memberikan prioritas lebih tinggi pada komponen vital agar rekomendasi keputusan akhir—apakah perangkat tersebut sangat layak beli atau tidak—tetap objektif, aman, dan menguntungkan bagi Anda.</p>
            <div class="tech-stack">
                <span class="badge-tech"><i class="fa-solid fa-battery-three-quarters"></i> Fokus Battery Health</span>
                <span class="badge-tech"><i class="fa-solid fa-thumbs-up"></i> Rekomendasi Akurat</span>
                <span class="badge-tech"><i class="fa-solid fa-scale-balanced"></i> Penilaian Objektif</span>
            </div>
        </div>
    </div>

</body>
</html>