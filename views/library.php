<?php
session_start();

// Bypass session login biar langsung masuk dashboard (Bersih dari invisible characters)
$_SESSION['login'] = true;
$_SESSION['user_id'] = 1;
$_SESSION['email'] = "dicki.firgiawan@nevorix.ai";
$_SESSION['nama'] = "Dicki Firgiawan";

// SINKRONISASI DATABASE: Mundur satu folder untuk mengambil config koneksi
require_once '../config/koneksi.php';

// =========================================================================
// 🗑️ LOGIKA ACTION: HAPUS SESI CHAT (JIKA DIAKSES DARI SIDEBAR LIBRARY)
// =========================================================================
if (isset($_GET['delete_session'])) {
    $session_to_delete = mysqli_real_escape_string($koneksi, $_GET['delete_session']);
    mysqli_query($koneksi, "DELETE FROM chat_history WHERE session_id = '$session_to_delete' AND email = '" . $_SESSION['email'] . "'");
    
    if (isset($_SESSION['current_chat_session']) && $_SESSION['current_chat_session'] == $session_to_delete) {
        unset($_SESSION['current_chat_session']);
    }
    header("Location: library.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library - Nevorix Apple AI Premium</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; transition: background 0.3s ease, color 0.3s ease, border-color 0.3s ease; }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0, 168, 255, 0.2); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0, 168, 255, 0.4); }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(rgba(10, 17, 34, 0.75), rgba(5, 10, 20, 0.85)), url('../assets/images/bg-Dashboard.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            color: #f1f5f9;
            overflow: hidden;
        }

        /* SIDEBAR STYLE UTUH */
        .sidebar {
            width: 300px;
            background: rgba(6, 11, 25, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(0, 168, 255, 0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px;
            z-index: 10;
        }
        
        .sidebar-menu-top { display: flex; flex-direction: column; gap: 4px; }
        
        .brand {
            font-size: 20px; font-weight: 800; letter-spacing: 1.5px; padding: 10px 5px 20px 5px; text-align: center;
            color: #ffffff; text-shadow: 0 0 15px rgba(0, 168, 255, 0.4); border-bottom: 1px solid rgba(0, 168, 255, 0.1); margin-bottom: 10px;
        }
        .brand span { color: #00a8ff; }
        
        .btn-menu {
            display: flex; align-items: center; padding: 11px 15px; color: #94a3b8; text-decoration: none;
            border-radius: 10px; font-size: 14px; font-weight: 600; border: 1px solid transparent;
        }
        .btn-menu:hover {
            color: #00a8ff; background: rgba(0, 168, 255, 0.08); border-color: rgba(0, 168, 255, 0.15);
        }
        .btn-menu.active {
            color: #00a8ff; background: rgba(0, 168, 255, 0.08); border-color: rgba(0, 168, 255, 0.2);
            box-shadow: 0 0 15px rgba(0, 168, 255, 0.1);
        }
        .btn-new-chat {
            background: rgba(0, 168, 255, 0.12); color: #00a8ff; border: 1px solid rgba(0, 168, 255, 0.3);
            margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0, 168, 255, 0.15); justify-content: center;
        }
        .btn-new-chat:hover { background: rgba(0, 168, 255, 0.2); color: #ffffff; }

        /* 🔍 SEARCH & HISTORY SECTION */
        .search-and-history-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            margin-top: 15px;
        }
        .search-history-box { padding: 2px 5px 10px 5px; }
        .search-history-box input {
            width: 100%; padding: 10px 14px; background: rgba(255,255,255,0.04); border: 1px solid rgba(0, 168, 255, 0.15);
            border-radius: 8px; color: #f1f5f9; font-size: 13px; outline: none;
        }
        .search-history-box input:focus { border-color: #00a8ff; box-shadow: 0 0 8px rgba(0, 168, 255, 0.3); }

        .history-title { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #00a8ff; letter-spacing: 1px; margin: 5px 0 8px 8px; }
        .history-container { flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 4px; padding-right: 2px; }
        
        .history-wrapper { display: flex; align-items: center; justify-content: space-between; position: relative; border-radius: 8px; }
        .history-item { flex: 1; display: block; padding: 10px 12px; font-size: 13px; color: #94a3b8; text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; border-left: 3px solid transparent; }
        .history-wrapper:hover { background: rgba(255, 255, 255, 0.05); }
        .history-wrapper:hover .history-item { color: #f1f5f9; }
        .history-wrapper.active { background: rgba(0, 168, 255, 0.1); }
        .history-wrapper.active .history-item { color: #00a8ff; border-left-color: #00a8ff; font-weight: 600; }
        
        .btn-delete-session { display: none; padding: 8px 12px; color: #ef4444; text-decoration: none; font-size: 14px; font-weight: bold; cursor: pointer; }
        .history-wrapper:hover .btn-delete-session { display: block; }
        .btn-delete-session:hover { color: #ff6b6b; }

        .sidebar-menu-bottom { border-top: 1px solid rgba(0, 168, 255, 0.1); padding-top: 15px; display: flex; flex-direction: column; gap: 10px; }
        .user-profile { font-size: 12px; color: #94a3b8; padding: 2px 5px; display: flex; flex-direction: column; gap: 2px; }
        .user-profile b { color: #ffffff; }
        .btn-logout {
            padding: 12px; background: linear-gradient(135deg, #ef4444, #b91c1c); color: white; text-align: center;
            text-decoration: none; border-radius: 10px; font-size: 13px; font-weight: 700;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2);
        }
        .btn-logout:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4); }

        /* CONTENT CONTAINER AREA */
        .main-content {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
            background: rgba(10, 15, 30, 0.4);
        }
        .content-title {
            font-size: 24px; font-weight: 700; margin-bottom: 25px; color: #ffffff;
            text-shadow: 0 0 10px rgba(0, 168, 255, 0.3); border-bottom: 2px solid rgba(0, 168, 255, 0.2); padding-bottom: 12px;
        }

        .table-container {
            background: rgba(6, 11, 25, 0.7); border: 1px solid rgba(0, 168, 255, 0.15);
            backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            border-radius: 16px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 18px 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); font-size: 14px; vertical-align: top; }
        th { color: #00a8ff; font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; }
        tr:hover { background: rgba(0, 168, 255, 0.02); }
        
        .badge-id { background: rgba(0, 168, 255, 0.15); color: #00a8ff; padding: 4px 8px; border-radius: 6px; font-weight: 700; font-family: monospace; }
        .text-muted { color: #94a3b8; font-style: italic; }

        /* =========================================================================
        * 💎 PREMIUM UPGRADE: KARTU DISPLAY PRODUK BESAR DI KOLOM KIRI (DASHBOARD LOOK)
        * ========================================================================= */
        .query-product-card {
            background: rgba(6, 11, 25, 0.8);
            border: 1px solid rgba(0, 168, 255, 0.25);
            border-radius: 16px;
            padding: 24px;
            width: 100%;
            max-width: 360px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5), inset 0 0 20px rgba(0, 168, 255, 0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
            margin-top: 15px;
        }
        .premium-product-wrapper {
            background: radial-gradient(circle, rgba(0, 168, 255, 0.15) 0%, rgba(0, 0, 0, 0.4) 75%);
            border: 1px solid rgba(0, 168, 255, 0.3);
            border-radius: 12px;
            width: 100%;
            height: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 10px;
        }
        .premium-product-img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 15px 25px rgba(0, 168, 255, 0.5));
        }
        .device-info-text {
            text-align: center;
            width: 100%;
        }
        .device-title {
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 6px;
        }
        .device-color-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            color: #ffffff;
            background: #ff6b00; /* Warna Orange */
            padding: 5px 14px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .device-color-badge.blue {
            background: #007aff; /* Warna Blue */
        }
        
        /* 📱 Row Spesifikasi Kecil di Bagian Bawah */
        .specs-row {
            display: flex;
            justify-content: space-between;
            width: 100%;
            gap: 6px;
            margin-top: 5px;
        }
        .spec-mini-box {
            flex: 1;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 8px 4px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }
        .spec-mini-box strong {
            color: #ffffff;
            font-size: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-query-text {
            color: #f1f5f9;
            font-size: 14px;
            line-height: 1.5;
            font-weight: 600;
            background: rgba(0, 168, 255, 0.1);
            padding: 8px 12px;
            border-radius: 8px;
            border-left: 3px solid #00a8ff;
            display: inline-block;
            margin-bottom: 10px;
        }
        .ai-response-box {
            color: #e2e8f0;
            line-height: 1.7;
            font-size: 14.5px;
            background: rgba(255, 255, 255, 0.01);
            border: 1px solid rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            padding: 16px;
        }

        /* Hilangkan box gambar default atau ikon rusak di dalam AI Response */
        .ai-response-box img {
            display: none !important;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-menu-top">
            <div class="brand">NEVORIX <span>APPLE AI</span></div>
            <a href="../index.php?action=new_chat" class="btn-menu btn-new-chat">✨ New chat</a>
            <a href="library.php" class="btn-menu active">📁 Library</a>
            <a href="projects.php" class="btn-menu">💼 Projects</a>
            <a href="settings.php" class="btn-menu">⚙️ Settings</a>
        </div>

        <div class="search-and-history-section">
            <div class="history-title">Telusuri Percakapan</div>
            <div class="search-history-box">
                <input type="text" id="searchChatInput" placeholder="Cari percakapan lama..." onkeyup="filterConversations()">
            </div>
            
            <div class="history-container" id="historyContainerBox">
                <?php
                $email_aktif = $_SESSION['email'];
                
                $list_sessions = mysqli_query($koneksi, "
                    SELECT ch.session_id, ch.user_message 
                    FROM chat_history ch
                    INNER JOIN (
                        SELECT session_id, MIN(id) as min_id
                        FROM chat_history
                        WHERE email = '$email_aktif' AND session_id IS NOT NULL AND session_id != ''
                        GROUP BY session_id
                    ) grp ON ch.id = grp.min_id
                    ORDER BY ch.id DESC 
                    LIMIT 20
                ");

                if ($list_sessions && mysqli_num_rows($list_sessions) > 0) {
                    while ($s_row = mysqli_fetch_assoc($list_sessions)) {
                        $isActiveClass = (isset($_SESSION['current_chat_session']) && $s_row['session_id'] == $_SESSION['current_chat_session']) ? 'active' : '';
                        $judul_chat = htmlspecialchars(mb_strimwidth($s_row['user_message'], 0, 24, "..."));
                        
                        echo '<div class="history-wrapper ' . $isActiveClass . '">';
                        echo '  <a href="../index.php?select_session=' . $s_row['session_id'] . '" class="history-item">💬 ' . $judul_chat . '</a>';
                        echo '  <a href="library.php?delete_session=' . $s_row['session_id'] . '" class="btn-delete-session" title="Hapus Sesi" onclick="return confirm(\'Hapus permanen percakapan ini masee?\')">×</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<div style="font-size:12px; color:#94a3b8; padding:10px; font-style:italic;" class="no-history">Belum ada history chat.</div>';
                }
                ?>
            </div>
        </div>

        <div class="sidebar-menu-bottom">
            <div class="user-profile">🟢 Active Account:<br><b><?= htmlspecialchars($_SESSION['nama']); ?></b></div>
            <a href="../logout.php" class="btn-logout">SIGN OUT</a>
        </div>
    </div>

    <div class="main-content">
        <div class="content-title">Hasil Analisis Perangkat</div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;">Log ID</th>
                        <th style="width: 42%;">Query Pertanyaan User</th> 
                        <th style="width: 50%;">HASIL DIAGNOSTIK AI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = mysqli_query($koneksi, "
                        SELECT 
                            ch.id,
                            ch.session_id,
                            ch.user_message as query_awal,
                            (SELECT bot_response FROM chat_history WHERE session_id = ch.session_id AND bot_response LIKE '%Hasil Analisis%' ORDER BY id DESC LIMIT 1) as hasil_akhir
                        FROM chat_history ch
                        INNER JOIN (
                            SELECT session_id, MIN(id) as min_id
                            FROM chat_history
                            WHERE email = '$email_aktif' AND session_id IS NOT NULL AND session_id != ''
                            GROUP BY session_id
                        ) grp ON ch.id = grp.min_id
                        ORDER BY grp.min_id DESC 
                        LIMIT 50
                    ");

                    if ($query && mysqli_num_rows($query) > 0) {
                        while ($row = mysqli_fetch_assoc($query)) {
                            $output_response = $row['hasil_akhir'];
                            if (empty($output_response)) {
                                $safe_session_id = mysqli_real_escape_string($koneksi, $row['session_id']);
                                $backup_query = mysqli_query($koneksi, "SELECT bot_response FROM chat_history WHERE session_id = '$safe_session_id' AND bot_response != '' ORDER BY id DESC LIMIT 1");
                                if ($backup_query && mysqli_num_rows($backup_query) > 0) {
                                    $backup_row = mysqli_fetch_assoc($backup_query);
                                    $output_response = $backup_row['bot_response'];
                                } else {
                                    $output_response = '<span class="text-muted">Analisis belum diselesaikan</span>';
                                }
                            }

                            // 🛠️ DETEKSI DETAIL PERANGKAT SECARA DINAMIS
                            $image_src = "../assets/images/iphones/apple_15blue.jpg"; // Fallback
                            $device_name = "iPhone 15 Blue"; 
                            $color_name = "BLUE";
                            $is_orange = false;
                            
                            // Bersihkan tag [Image of...] bawaan AI
                            $output_response = preg_replace('/\[Image of.*?\]/i', '', $output_response);

                            // Prioritas 1: Deteksi spesifik warna Blue / Deep Blue
                            if (stripos($output_response, 'DEEPBLUE') !== false || stripos($row['query_awal'], 'deepblue') !== false || stripos($row['query_awal'], 'blue') !== false) {
                                $image_src = "../assets/images/iphones/apple_17ProMaxDeepBlue.jpg";
                                $device_name = "iPhone 17 Pro Max";
                                $color_name = "DEEP BLUE";
                                $is_orange = false;
                            
                            // Prioritas 2: Deteksi spesifik warna Orange
                            } elseif (stripos($output_response, 'ORANGE') !== false || stripos($row['query_awal'], 'orange') !== false) {
                                $image_src = "../assets/images/iphones/apple_17ProMaxOrange.jpg";
                                $device_name = "iPhone 17 Pro Max";
                                $color_name = "ORANGE";
                                $is_orange = true;
                            
                            // Prioritas 3: Deteksi generik angka 17 (tanpa keterangan warna spesifik)
                            } elseif (stripos($row['query_awal'], '17') !== false) {
                                $image_src = "../assets/images/iphones/apple_17ProMaxDeepBlue.jpg";
                                $device_name = "iPhone 17 Pro Max";
                                $color_name = "DEEP BLUE";
                                $is_orange = false;
                            }

                            // =========================================================================
                            // LOGIKA EKSTRAKSI REAL-TIME (DARI QUERY USER ATAU RESPON BOT)
                            // =========================================================================
                            // 1. Ekstraksi Storage (Mencari pola angka diikuti GB/gb/Gb)
                            $detected_storage = "256 GB"; // Default fallback
                            if (preg_match('/(\d+)\s*GB/i', $row['query_awal'] . ' ' . $output_response, $matches_storage)) {
                                $detected_storage = $matches_storage[1] . " GB";
                            }

                            // 2. Ekstraksi Masa Pakai (Mencari pola angka diikuti Tahun/tahun)
                            $detected_masa_pakai = "1 Tahun"; // Default fallback
                            if (preg_match('/(\d+)\s*Tahun/i', $row['query_awal'] . ' ' . $output_response, $matches_time)) {
                                $detected_masa_pakai = $matches_time[1] . " Tahun";
                            }

                            // 3. Ekstraksi Distribusi (Mencari iBox atau Inter)
                            $detected_distribusi = "iBox / Resmi"; // Default fallback
                            if (stripos($row['query_awal'] . ' ' . $output_response, 'inter') !== false) {
                                $detected_distribusi = "International";
                            }

                            echo "<tr>";
                            echo "<td><span class='badge-id'>#{$row['id']}</span></td>";
                            
                            // 📸 KOLOM QUERY: PREMIUM LOOK DENGAN FOTO BESAR & BADGE SPESIFIKASI DI BAWAHNYA
                            echo "<td>";
                            echo "  <div class='user-query-text'> " . htmlspecialchars($row['query_awal'] ?? 'Data chat awal kosong') . "</div>";
                            if (strip_tags($output_response) !== 'Analisis belum diselesaikan') {
                                $badge_class = $is_orange ? 'device-color-badge' : 'device-color-badge blue';
                                echo "  <div class='query-product-card'>";
                                echo "      <div class='premium-product-wrapper'>";
                                echo "          <img src='{$image_src}' class='premium-product-img' alt='{$device_name}'>";
                                echo "      </div>";
                                echo "      <div class='device-info-text'>";
                                echo "          <div class='device-title'>{$device_name}</div>";
                                echo "          <span class='{$badge_class}'>{$color_name}</span>";
                                echo "      </div>";
                                echo "      <div class='specs-row'>";
                                // Diubah menjadi dinamis mengikuti variabel hasil ekstraksi regex di atas
                                echo "          <div class='spec-mini-box'>Storage<strong>" . htmlspecialchars($detected_storage) . "</strong></div>";
                                echo "          <div class='spec-mini-box'>Distribusi<strong>" . htmlspecialchars($detected_distribusi) . "</strong></div>";
                                echo "          <div class='spec-mini-box'>Masa Pakai<strong>" . htmlspecialchars($detected_masa_pakai) . "</strong></div>";
                                echo "      </div>";
                                echo "  </div>";
                            }
                            echo "</td>";
                            
                            // 📝 KOLOM AI RESPONSE: Bersih & Menghilangkan Gambar Rusak/Ikon Kamera Bawaan
                            echo "<td>";
                            if (strip_tags($output_response) !== 'Analisis belum diselesaikan') {
                                // Ganti tag img yang rusak dari database agar tidak nampak sama sekali
                                $clean_response = preg_replace('/<img[^>]+\>/i', '', $output_response);
                                echo "  <div class='ai-response-box'>{$clean_response}</div>";
                            } else {
                                echo $output_response;
                            }
                            echo "</td>";
                            
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' style='text-align:center; color:#64748b;'>Belum ada data history log chat terdeteksi masee.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // 🔍 Client-Side Filter + Auto Cut dynamic flow list ke bawah
        function filterConversations() {
            const input = document.getElementById('searchChatInput');
            const filter = input.value.toLowerCase();
            const container = document.getElementById('historyContainerBox');
            const wrappers = container.getElementsByClassName('history-wrapper');

            for (let i = 0; i < wrappers.length; i++) {
                let item = wrappers[i].getElementsByClassName('history-item')[0];
                if (item) {
                    let textValue = item.textContent || item.innerText;
                    if (textValue.toLowerCase().indexOf(filter) > -1) {
                        wrappers[i].style.display = "flex";
                    } else {
                        wrappers[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</body>
</html>