<?php
session_start();

// =========================================================================
// 🛠️ SINKRONISASI SESSION AUTH INTERNAL DEVELOPER
// =========================================================================
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    $_SESSION['login'] = true;
    $_SESSION['id'] = 1; 
    $_SESSION['email'] = "dicki.firgiawan@nevorix.ai"; 
    $_SESSION['nama'] = "Dicki Firgiawan"; 
}

require_once 'config/koneksi.php'; 

// PENGAMAN AUTOMATIC DATABASE COLUMNS
$kolom_baru = [
    'session_id' => "VARCHAR(50) DEFAULT 'default_session'",
    'sinyal_status' => "INT DEFAULT 0",
    'biometrik_status' => "INT DEFAULT 0",
    'part_status' => "INT DEFAULT 0",
    'kamera_status' => "INT DEFAULT 0",
    'icloud_status' => "INT DEFAULT 0",
    'jangka_pakai' => "INT DEFAULT 1",
    'skor_persen' => "INT DEFAULT 100"
];

foreach ($kolom_baru as $col => $definition) {
    $check = mysqli_query($koneksi, "SHOW COLUMNS FROM `chat_history` LIKE '$col'");
    if ($check && mysqli_num_rows($check) == 0) {
        mysqli_query($koneksi, "ALTER TABLE `chat_history` ADD COLUMN `$col` $definition");
    }
}

if (!isset($_SESSION['current_chat_session'])) {
    $_SESSION['current_chat_session'] = uniqid('session_', true);
}

// 🎨 STRUKTUR DATA UTAMA KELAYAKAN GADGET (TOTAL 11 LANGKAH DIAGNOSA)
function resetTempData() {
    $_SESSION['temp_data'] = [
        'model' => null, 
        'warna' => 'default', 
        'storage' => null, 
        'bh' => null, 
        'garansi' => null,
        'sinyal' => null, 
        'biometrik' => null, 
        'part' => null,
        'kamera' => null, 
        'icloud' => null, 
        'jangka_pakai' => 1
    ];
    $_SESSION['current_step'] = 1; 
}

if (!isset($_SESSION['temp_data']) || !isset($_SESSION['current_step'])) {
    resetTempData();
}

// =========================================================================
// 🔄 LOGIKA ACTION: NEW CHAT / RESET SESSION
// =========================================================================
if (isset($_GET['action']) && $_GET['action'] == 'new_chat') {
    resetTempData();
    $_SESSION['current_chat_session'] = uniqid('session_', true);
    header("Location: index.php");
    exit;
}

if (isset($_GET['select_session'])) {
    $_SESSION['current_chat_session'] = $_GET['select_session'];
    resetTempData(); 
    header("Location: index.php");
    exit;
}

// =========================================================================
// 🤖 API INTERNAL CHATBOT AI (Menangani Request AJAX dari chatbot.js)
// =========================================================================
if (isset($_GET['action']) && $_GET['action'] == 'send_chat') {
    header('Content-Type: application/json');
    
    $raw_message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $email_user  = $_SESSION['email'];
    $session_id  = $_SESSION['current_chat_session']; 

    $jawaban_bot = "";
    $is_ready_to_predict = false;
    $current_step = $_SESSION['current_step'];

    // --- 🧭 LINEAR STATE MACHINE & STRICT PARSING ---
    switch ($current_step) {
        case 1: // INPUT TIPE MODEL
            $msg_lower = strtolower($raw_message);
            $map_tipe = [
                'iphone 17 pro max' => 30, 'iphone 17 pro' => 29, 'iphone 17 plus' => 28, 'iphone 17' => 27,
                'iphone 16 pro max' => 26, 'iphone 16 pro' => 25, 'iphone 16 plus' => 24, 'iphone 16' => 23,
                'iphone 15 pro max' => 22, 'iphone 15 pro' => 21, 'iphone 15 plus' => 20, 'iphone 15' => 19,
                'iphone 14 pro max' => 18, 'iphone 14 pro' => 17, 'iphone 14 plus' => 16, 'iphone 14' => 15,
                'iphone 13 pro max' => 14, 'iphone 13 pro' => 13, 'iphone 13 mini' => 11, 'iphone 13' => 12,
                'iphone 12 pro max' => 10, 'iphone 12 pro' => 9,  'iphone 12 mini' => 7,  'iphone 12' => 8,
                'iphone 11 pro max' => 6,  'iphone 11 pro' => 5,  'iphone 11' => 4,
                'iphone xs max' => 3,      'iphone xs' => 2,      'iphone xr' => 1,       'iphone x' => 0
            ];

            $ditemukan = false;
            foreach ($map_tipe as $key => $id_tipe) {
                $alt_key = str_replace('iphone', 'ip', $key); 
                if (strpos($msg_lower, $key) !== false || strpos($msg_lower, $alt_key) !== false) {
                    $_SESSION['temp_data']['model'] = $id_tipe;
                    $_SESSION['current_step'] = 2;
                    $ditemukan = true;
                    break; 
                }
            }

            if (!$ditemukan) {
                $pola_fallback = [
                    '/(17\s?pro\s?max|17\s?pm)/i' => 30, '/17\s?pro/i' => 29, '/17\s?plus/i' => 28, '/17/i' => 27,
                    '/(16\s?pro\s?max|16\s?pm)/i' => 26, '/16\s?pro/i' => 25, '/16\s?plus/i' => 24, '/16/i' => 23,
                    '/(15\s?pro\s?max|15\s?pm)/i' => 22, '/15\s?pro/i' => 21, '/15\s?plus/i' => 20, '/15/i' => 19,
                    '/(14\s?pro\s?max|14\s?pm)/i' => 18, '/14\s?pro/i' => 17, '/14\s?plus/i' => 16, '/14/i' => 15,
                    '/(13\s?pro\s?max|13\s?pm)/i' => 14, '/13\s?pro/i' => 13, '/13\s?mini/i' => 11, '/13/i' => 12,
                    '/(12\s?pro\s?max|12\s?pm)/i' => 10, '/12\s?pro/i' => 9,  '/12\s?mini/i' => 7,  '/12/i' => 8,
                    '/(11\s?pro\s?max|11\s?pm)/i' => 6,  '/11\s?pro/i' => 5,  '/11/i' => 4,
                    '/xs\s?max/i' => 3, '/xs/i' => 2, '/xr/i' => 1, '/x/i' => 0
                ];
                foreach ($pola_fallback as $pattern => $id_tipe) {
                    if (preg_match($pattern, $msg_lower)) {
                        $_SESSION['temp_data']['model'] = $id_tipe;
                        $_SESSION['current_step'] = 2;
                        break;
                    }
                }
            }
            break;

        case 2: // TANGKAP INPUT WARNA USER
            $warna_raw = trim($raw_message);
            $warna_clean = strtolower(str_replace(' ', '', $warna_raw));
            $_SESSION['temp_data']['warna'] = $warna_clean;
            $_SESSION['current_step'] = 3;
            break;

        case 3: // INPUT STORAGE
            if (preg_match('/\b(64|128|256|512|1)\s*(gb|g|tb|t)?\b/i', $raw_message, $match)) {
                $val = intval($match[1]);
                $_SESSION['temp_data']['storage'] = ($val == 1) ? 1024 : $val; 
                $_SESSION['current_step'] = 4;
            }
            break;

        case 4: // INPUT BATTERY HEALTH
            if (preg_match_all('/\b([4-9]\d|100)\b/', $raw_message, $matches)) {
                foreach ($matches[0] as $num) {
                    $_SESSION['temp_data']['bh'] = intval($num);
                    $_SESSION['current_step'] = 5;
                    break;
                }
            }
            break;

        case 5: // INPUT GARANSI
            if (preg_match('/(ibox|resmi)/i', $raw_message)) {
                $_SESSION['temp_data']['garansi'] = 0;
                $_SESSION['current_step'] = 6;
            } elseif (preg_match('/inter/i', $raw_message)) {
                $_SESSION['temp_data']['garansi'] = 1;
                $_SESSION['current_step'] = 6;
            } elseif (preg_match('/wifi/i', $raw_message)) {
                $_SESSION['temp_data']['garansi'] = 2;
                $_SESSION['temp_data']['sinyal'] = 2; 
                $_SESSION['current_step'] = 7; 
            }
            break;

        case 6: // INPUT STATUS SINYAL
            if (preg_match('/(all\s?operator|all\s?opr|sinyal\s?aman|aman|normal|bagus)/i', $raw_message)) {
                $_SESSION['temp_data']['sinyal'] = 0;
                $_SESSION['current_step'] = 7;
            } elseif (preg_match('/(blokir|terblokir|wifi\s?only|no\s?sinyal|rusak)/i', $raw_message)) {
                $_SESSION['temp_data']['sinyal'] = 2;
                $_SESSION['temp_data']['garansi'] = 2; 
                $_SESSION['current_step'] = 7;
            }
            break;

        case 7: // INPUT BIOMETRIK
            if (preg_match('/(mati|off|rusak|ga aktif)/i', $raw_message)) {
                $_SESSION['temp_data']['biometrik'] = 1;
                $_SESSION['current_step'] = 8;
            } elseif (preg_match('/(aktif|normal|aman|on|bisa)/i', $raw_message)) {
                $_SESSION['temp_data']['biometrik'] = 0;
                $_SESSION['current_step'] = 8;
            }
            break;

        case 8: // INPUT KEASLIAN PART
            if (preg_match('/(off|gantian|unknown|error|ganti|rusak)/i', $raw_message)) {
                $_SESSION['temp_data']['part'] = 1;
                $_SESSION['current_step'] = 9;
            } elseif (preg_match('/(on|ori|original|aman|normal)/i', $raw_message)) {
                $_SESSION['temp_data']['part'] = 0;
                $_SESSION['current_step'] = 9;
            }
            break;

        case 9: // INPUT KAMERA
            if (preg_match('/(getar|mati|rusak|blur|minus)/i', $raw_message)) {
                $_SESSION['temp_data']['kamera'] = 1;
                $_SESSION['current_step'] = 10;
            } elseif (preg_match('/(jernih|aman|normal|bagus|oke)/i', $raw_message)) {
                $_SESSION['temp_data']['kamera'] = 0;
                $_SESSION['current_step'] = 10;
            }
            break;

        case 10: // INPUT ICLOUD
            if (preg_match('/(nyangkut|kunci|bypass|ada)/i', $raw_message)) {
                $_SESSION['temp_data']['icloud'] = 1;
                $_SESSION['current_step'] = 11;
            } elseif (preg_match('/(kosong|aman|bebas|bersih)/i', $raw_message)) {
                $_SESSION['temp_data']['icloud'] = 0;
                $_SESSION['current_step'] = 11;
            }
            break;

        case 11: // INPUT JANGKA PAKAI
            if (preg_match('/\b(\d+)\b/', $raw_message, $match_pakai)) {
                $_SESSION['temp_data']['jangka_pakai'] = intval($match_pakai[1]);
                $_SESSION['current_step'] = 12; 
            }
            break;
    }

    // --- 🖋️ PUSAT EDIT BAHASA FORMAL & PREMIUM PERTANYAAN BOT ---
    $updated_step = $_SESSION['current_step'];
    $s_model_hint = $_SESSION['temp_data']['model'] ?? -1;
    $hint_warna_teks = "Hitam, Putih, Merah, Silver, Gold";

    if ($s_model_hint == 30 || $s_model_hint == 29) { $hint_warna_teks = "DeepBlue, Titanium, SpaceGray, Orange"; }
    elseif ($s_model_hint == 26 || $s_model_hint == 25) { $hint_warna_teks = "DesertTitanium, NaturalTitanium, White, Black"; }
    elseif ($s_model_hint == 22 || $s_model_hint == 21) { $hint_warna_teks = "BlueTitanium, NaturalTitanium, White, Black"; }
    elseif ($s_model_hint == 14 || $s_model_hint == 13) { $hint_warna_teks = "SierraBlue, AlpineGreen, Gold, Graphite, Silver"; }
    elseif ($s_model_hint == 10 || $s_model_hint == 9)  { $hint_warna_teks = "PacificBlue, Gold, Graphite, Silver"; }
    elseif ($s_model_hint == 6 || $s_model_hint == 5)   { $hint_warna_teks = "MidnightGreen, SpaceGray, Silver, Gold"; }
    elseif ($s_model_hint == 4) { $hint_warna_teks = "Hitam, Putih, Merah, Ungu, Hijau, Kuning"; }
    elseif ($s_model_hint == 1) { $hint_warna_teks = "Hitam, Putih, Merah, Biru, Kuning, Coral"; }

    if ($updated_step == 1) {
        $jawaban_bot = "Selamat datang di <b>Nevorix Apple AI Premium Diagnostic</b>.<br>Mohon informasikan tipe unit iPhone yang ingin Anda lakukan pengujian kelayakan fungsi saat ini.<br><span style='color:var(--text-muted); font-size:12px;'>Contoh: iPhone 17 Pro Max, iPhone 13 Pro, atau iPhone 11</span>";
    } elseif ($updated_step == 2) {
        $jawaban_bot = "Terima kasih. Selanjutnya, silakan masukkan varian <b>warna resmi</b> dari unit iPhone tersebut.<br><span style='color:var(--text-muted); font-size:12px;'>Rekomendasi opsi: " . $hint_warna_teks . "</span>";
    } elseif ($updated_step == 3) {
        $jawaban_bot = "Berapakah kapasitas penyimpanan internal (Storage) perangkat Anda?<br><span style='color:var(--text-muted); font-size:12px;'>Opsi standar: 64 GB, 128 GB, 256 GB, atau 512 GB</span>";
    } elseif ($updated_step == 4) {
        $jawaban_bot = "Mohon sebutkan persentase kapasitas maksimal sisa dari sitem daya kesehatan baterai unit perangkat tersebut.<br><span style='color:var(--text-muted); font-size:12px;'>Contoh input: 85 atau BH 90</span>";
    } elseif ($updated_step == 5) {
        $jawaban_bot = "Bagaimanakah status distribusi jaminan/garansi unit yang diperiksa?<br><span style='color:var(--text-muted); font-size:12px;'>Pilihan: Resmi <b>iBox</b>, Eks Internasional (<b>Inter</b>), atau <b>WiFi Only</b></span>";
    } elseif ($updated_step == 6) {
        $jawaban_bot = "Bagaimanakah kondisi dan status penangkapan <b>Jaringan Sinyal Seluler</b> pada unit gadget tersebut?<br><span style='color:var(--text-muted); font-size:12px;'>Opsi: Aman All Operator atau Terblokir / Terkunci (WiFi Only)</span>";
    } elseif ($updated_step == 7) {
        $jawaban_bot = "Apakah sistem keamanan sensor biometrik perangkat (seperti fungsi <b>Face ID</b> atau <b>Touch ID</b>) masih berfungsi normal?<br><span style='color:var(--text-muted); font-size:12px;'>Pilihan: Aktif Normal atau Nonaktif / Rusak</span>";
    } elseif ($updated_step == 8) {
        $jawaban_bot = "Apakah fitur **True Tone** pada layar LCD terdeteksi aktif dan seluruh komponen unit masih original?<br><span style='color:var(--text-muted); font-size:12px;'>Pilihan: Original / Aktif atau Komponen Pernah Diganti (Unknown Part)</span>";
    } elseif ($updated_step == 9) {
        $jawaban_bot = "Bagaimanakah status performa fungsionalitas visual kamera utama dan kamera depan gadget saat ini?<br><span style='color:var(--text-muted); font-size:12px;'>Pilihan: Jernih & Normal atau Mengalami Kendala (Getar / Blur / Mati)</span>";
    } elseif ($updated_step == 10) {
        $jawaban_bot = "Apakah akun otentikasi keamanan <b>iCloud</b> unit berada dalam status bersih, bebas di-reset, dan kosong?<br><span style='color:var(--text-muted); font-size:12px;'>Pilihan: Bersih (Bebas Reset) atau Terkunci / Menggunakan Metode Bypass</span>";
    } elseif ($updated_step == 11) {
        $jawaban_bot = "Sebagai informasi penutup diagnosa, mohon sebutkan perkiraan masa durasi penggunaan aktif perangkat tersebut (dalam satuan tahun).<br><span style='color:var(--text-muted); font-size:12px;'>Contoh input: 1 atau 2 tahun</span>";
    } else {
        $is_ready_to_predict = true;
    }

    $s_model   = $_SESSION['temp_data']['model'] ?? 8; 
    $s_storage = $_SESSION['temp_data']['storage'] ?? 128;
    $s_bh      = $_SESSION['temp_data']['bh'] ?? 100;
    $s_garansi = $_SESSION['temp_data']['garansi'] ?? 0;
    $s_sinyal  = $_SESSION['temp_data']['sinyal'] ?? 0;
    $s_bio     = $_SESSION['temp_data']['biometrik'] ?? 0;
    $s_part    = $_SESSION['temp_data']['part'] ?? 0;
    $s_kam     = $_SESSION['temp_data']['kamera'] ?? 0;
    $s_icloud  = $_SESSION['temp_data']['icloud'] ?? 0;
    $s_pakai   = $_SESSION['temp_data']['jangka_pakai'] ?? 1;
    $s_warna   = $_SESSION['temp_data']['warna'] ?? 'default';

    // =========================================================================
    // 🔥 PUSAT KALKULASI PERSENTASE SKOR REAL-TIME
    // =========================================================================
    $skor = 100;
    if ($s_bh < 70) {
        $skor = 35; 
    } elseif ($s_bh >= 70 && $s_bh < 80) {
        $skor -= 20; 
    } elseif ($s_bh >= 80 && $s_bh <= 89) {
        $skor -= 8;  
    }

    if ($s_bh >= 70) {
        if ($s_icloud >= 1)   $skor -= 35; 
        if ($s_sinyal >= 2)   $skor -= 15; 
        if ($s_bio >= 1)      $skor -= 15; 
        if ($s_kam >= 1)      $skor -= 15; 
        if ($s_part >= 1)     $skor -= 10; 
    }
    $skor = max(10, min(100, $skor));

    // =========================================================================
    // 🔥 CORE INTEGRATION & AUTO-EXPORT FILE CSV
    // =========================================================================
    if ($is_ready_to_predict) {
        $payload = [
            'tipe_iphone' => $s_model, 'kapasitas_storage' => $s_storage, 'battery_health' => $s_bh,
            'garansi_status' => $s_garansi, 'sinyal_status' => $s_sinyal, 'biometrik_status' => $s_bio,
            'part_status' => $s_part, 'kamera_status' => $s_kam, 'icloud_status' => $s_icloud,
            'jangka_pakai' => $s_pakai, 'skor_persen' => $skor
        ];

        $ch = curl_init('http://127.0.0.1:5000/predict');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($s_bh < 70 || $skor < 40) {
            $predikat = "TIDAK LAYAK BELI (KONDISI BURUK / RUSAK) 🔴";
            $csv_label = "TIDAK LAYAK";
            $color_badge = "#ff4d4d";
        } elseif ($skor >= 40 && $skor < 55) {
            $predikat = "KURANG LAYAK BELI (BUTUH SERVIS) 🟠";
            $csv_label = "KURANG LAYAK";
            $color_badge = "#ff9f43";
        } elseif ($skor >= 55 && $skor < 75) {
            $predikat = "LAYAK BELI (KONDISI SEDANG) 🟡";
            $csv_label = "LAYAK (SEDANG)";
            $color_badge = "#f1c40f";
        } elseif ($skor >= 75 && $skor < 88) {
            $predikat = "LAYAK BELI (KONDISI BAIK) ✅";
            $csv_label = "LAYAK (BAIK)";
            $color_badge = "#2ecc71";
        } else {
            $predikat = "SANGAT LAYAK BELI (KONDISI PRIMA) 🟢";
            $csv_label = "SANGAT LAYAK";
            $color_badge = "#00db64";
        }

        $txt_sinyal = ($s_sinyal == 0) ? "<span style='color:#2ecc71'>● Aman All Operator</span>" : "<span style='color:#ff4d4d'>● WiFi Only / Terblokir</span>";
        $txt_icloud = ($s_icloud == 0) ? "<span style='color:#2ecc71'>● Bersih (Bebas Reset)</span>" : "<span style='color:#ff4d4d'>● Nyangkut / Bypass</span>";
        $txt_bio    = ($s_bio == 0)    ? "<span style='color:#2ecc71'>● Normal & Aktif</span>" : "<span style='color:#ff4d4d'>● Mati / Rusak</span>";
        $txt_part   = ($s_part == 0)   ? "<span style='color:#2ecc71'>● Komponen Original</span>" : "<span style='color:#ff4d4d'>● Unknown Part / KW</span>";
        $txt_kam    = ($s_kam == 0)    ? "<span style='color:#2ecc71'>● Jernih & Normal</span>" : "<span style='color:#ff4d4d'>● Kamera Minus</span>";
        
        $map_tipe_nama = [
            30 => 'iPhone 17 Pro Max', 29 => 'iPhone 17 Pro', 28 => 'iPhone 17 Plus', 27 => 'iPhone 17',
            26 => 'iPhone 16 Pro Max', 25 => 'iPhone 16 Pro', 24 => 'iPhone 16 Plus', 23 => 'iPhone 16',
            22 => 'iPhone 15 Pro Max', 21 => 'iPhone 15 Pro', 20 => 'iPhone 15 Plus', 19 => 'iPhone 15',
            18 => 'iPhone 14 Pro Max', 17 => 'iPhone 14 Pro', 16 => 'iPhone 14 Plus', 15 => 'iPhone 14',
            14 => 'iPhone 13 Pro Max', 13 => 'iPhone 13 Pro', 11 => 'iPhone 13 Mini', 12 => 'iPhone 13',
            10 => 'iPhone 12 Pro Max', 9 => 'iPhone 12 Pro',  7 => 'iPhone 12 Mini',  8 => 'iPhone 12',
            6  => 'iPhone 11 Pro Max', 5 => 'iPhone 11 Pro',  4 => 'iPhone 11',
            3  => 'iPhone XS Max',     2 => 'iPhone XS',      1 => 'iPhone XR',       0 => 'iPhone X'
        ];
        $nama_hp = $map_tipe_nama[$s_model] ?? "iPhone Resmi";
        $txt_garansi = ($s_garansi == 0) ? "iBox / Resmi" : (($s_garansi == 1) ? "Inter" : "WiFi Only");

        // SYSTEM AUTOMATIC CLEANING MAPPING FOTO
        $warna_camel = "";
        if ($s_warna == 'deepblue') $warna_camel = "DeepBlue";
        elseif ($s_warna == 'orange') $warna_camel = "Orange";
        elseif ($s_warna == 'blue') $warna_camel = "Blue";
        else $warna_camel = ucfirst($s_warna);

        $tipe_clean = str_replace(' ', '', str_replace('iPhone ', '', $nama_hp));
        $nama_file_gambar = "apple_" . $tipe_clean . $warna_camel . ".jpg";
        $path_gambar = "assets/images/iphones/" . $nama_file_gambar;

        $html_gambar = "";
        if (file_exists($path_gambar)) {
            // MENGGUNAKAN mix-blend-mode MENGHILANGKAN BACKROUND HITAM/PUTIH JIKA DICAMPUR DENGAN CARD GLASSMORPHISM
            $html_gambar = "<div style='display: flex; justify-content: center; align-items: center; background: rgba(255,255,255,0.02); border-radius: 16px; padding: 15px; margin-bottom: 20px; border: 1px solid rgba(255,255,255,0.05); overflow:hidden;'>
                                <img src='" . $path_gambar . "' alt='" . $nama_hp . "' style='max-width:140px; max-height:180px; object-fit: contain; mix-blend-mode: lighten; filter: drop-shadow(0px 8px 25px rgba(0, 198, 255, 0.35));'>
                            </div>";
        }

        // LUXURY DESIGN SYSTEM FOR PREMIUM DIAGNOSTIC SUMMARY
        $jawaban_bot = "<div style='background: linear-gradient(145deg, rgba(15, 23, 42, 0.6), rgba(30, 41, 59, 0.4)); border: 1px solid rgba(0, 168, 255, 0.25); border-radius: 20px; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); font-family: -apple-system, BlinkMacSystemFont, sans-serif;'>";
        $jawaban_bot .= "<div style='display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; margin-bottom: 20px;'>
                            <h3 style='margin: 0; color: #00c6ff; font-size: 16px; font-weight: 800; letter-spacing: 1px;'>📋 PREMIUM DIAGNOSTIC REPORT</h3>
                            <span style='background: " . $color_badge . "; color: #000; font-size: 11px; font-weight: 900; padding: 4px 10px; border-radius: 8px; text-transform: uppercase;'>" . $skor . "% SCORE</span>
                         </div>";
        
        $jawaban_bot .= $html_gambar;
        
        // GRID SPECIFICATIONS 
        $jawaban_bot .= "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; background: rgba(0,0,0,0.2); padding: 14px; border-radius: 12px; font-size:13px;'>";
        $jawaban_bot .= "<div>Tipe: <b style='color:#fff;'>" . $nama_hp . "</b></div>";
        $jawaban_bot .= "<div>Storage: <b style='color:#fff;'>" . $s_storage . " GB</b></div>";
        $jawaban_bot .= "<div>Warna: <b style='color:#fff;'>" . strtoupper($s_warna) . "</b></div>";
        $jawaban_bot .= "<div>Kesehatan Baterai: <b style='color:#fff;'>" . $s_bh . "%</b></div>";
        $jawaban_bot .= "<div>Distribusi: <b style='color:#fff;'>" . $txt_garansi . "</b></div>";
        $jawaban_bot .= "<div>Masa Pakai: <b style='color:#fff;'>" . $s_pakai . " Tahun</b></div>";
        $jawaban_bot .= "</div>";
        
        // HARDWARE CHECKS
        $jawaban_bot .= "<h4 style='margin-bottom: 10px; color: #94a3b8; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;'>🛠️ Hardware Authentication</h4>";
        $jawaban_bot .= "<div style='display: flex; flex-direction: column; gap: 6px; font-size: 13px; margin-bottom: 20px; background: rgba(255,255,255,0.02); padding: 12px; border-radius: 10px;'>";
        $jawaban_bot .= "<div>Sinyal Seluler: " . $txt_sinyal . "</div>";
        $jawaban_bot .= "<div>Status iCloud: " . $txt_icloud . "</div>";
        $jawaban_bot .= "<div>Biometric Sensor: " . $txt_bio . "</div>";
        $jawaban_bot .= "<div>Keaslian Part: " . $txt_part . "</div>";
        $jawaban_bot .= "<div>Modul Kamera: " . $txt_kam . "</div>";
        $jawaban_bot .= "</div>";
        
        // CONCLUSION LUXURY BADGE
        $jawaban_bot .= "<div style='background: rgba(0, 168, 255, 0.08); border: 1px dashed rgba(0, 168, 255, 0.3); padding: 15px; border-radius: 12px; text-align: center;'>";
        $jawaban_bot .= "<div style='font-size: 11px; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px;'>Rekomendasi Keputusan Akhir</div>";
        $jawaban_bot .= "<div style='font-size: 14px; font-weight: 800; color: #fff;'>" . $predikat . "</div>";
        $jawaban_bot .= "</div>";
        
        $jawaban_bot .= "</div>";

        // 📄 AUTO-WRITE DAN APPEND KE FILE CSV
        $file_csv = 'dataset_iphone.csv';
        $apakah_baru = !file_exists($file_csv);
        
        $buka_file = fopen($file_csv, 'a');
        if ($buka_file) {
            if ($apakah_baru) {
                fputcsv($buka_file, [
                    'Timestamp', 'Email', 'Tipe Model', 'Warna', 'Storage (GB)', 'Battery Health', 
                    'Status Garansi', 'Status Sinyal', 'Biometrik', 'Keaslian Part', 
                    'Status Kamera', 'Status iCloud', 'Jangka Pakai (Tahun)', 'Skor Akhir (%)', 'Predikat'
                ]);
            }
            fputcsv($buka_file, [
                date('Y-m-d H:i:s'), $email_user, $nama_hp, strtoupper($s_warna), $s_storage, $s_bh,
                $s_garansi, $s_sinyal, $s_bio, $s_part, $s_kam, $s_icloud, $s_pakai, $skor, $csv_label
            ]);
            fclose($buka_file);
        }

        resetTempData();
    }

    // 💾 SIMPAN DATA KE DATABASE MYSQL
    $pesan_user_db = mysqli_real_escape_string($koneksi, $raw_message);
    $jawaban_bot_db = mysqli_real_escape_string($koneksi, $jawaban_bot);

    $query_save = "INSERT INTO `chat_history` (
        `email`, `user_message`, `bot_response`, `session_id`,
        `sinyal_status`, `biometrik_status`, `part_status`, 
        `kamera_status`, `icloud_status`, `jangka_pakai`, `skor_persen`
    ) VALUES (
        '$email_user', '$pesan_user_db', '$jawaban_bot_db', '$session_id',
        '$s_sinyal', '$s_bio', '$s_part', '$s_kam', '$s_icloud', '$s_pakai', '$skor'
    )";
    
    $insert_result = mysqli_query($koneksi, $query_save);

    echo json_encode([
        'status' => 'success', 
        'reply' => $jawaban_bot, 
        'db_status' => $insert_result ? 'saved' : 'error'
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Nevorix Apple AI Premium</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; transition: background 0.3s ease, color 0.3s ease; }
        :root {
            --sidebar-bg: rgba(5, 10, 24, 0.94);
            --content-bg: rgba(7, 11, 28, 0.4);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(0, 198, 255, 0.15);
            --input-bg: rgba(10, 17, 40, 0.75);
            --bubble-bot: rgba(255, 255, 255, 0.03);
            --bubble-bot-border: rgba(255, 255, 255, 0.07);
            --gradient-accent: linear-gradient(135deg, #00c6ff, #0072ff);
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(rgba(4, 8, 20, 0.92), rgba(2, 4, 10, 0.98)), url('assets/images/bg-Dashboard.jpg') no-repeat center center fixed;
            background-size: cover; height: 100vh; display: flex; color: var(--text-main); overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }
        
        .sidebar {
            width: 320px; background: var(--sidebar-bg); backdrop-filter: blur(40px); -webkit-backdrop-filter: blur(40px);
            border-right: 1px solid var(--border-color); display: flex; flex-direction: column; justify-content: space-between; padding: 25px;
            box-shadow: 10px 0 30px rgba(0,0,0,0.4);
        }
        .sidebar-menu-top { display: flex; flex-direction: column; flex: 1; overflow: hidden; gap: 6px; }
        .brand { font-size: 22px; font-weight: 900; letter-spacing: 2px; padding: 10px 5px 25px 5px; text-align: center; border-bottom: 1px solid var(--border-color); margin-bottom: 20px; font-family: 'SF Pro Display', sans-serif; }
        .brand span { color: #00a8ff; background: var(--gradient-accent); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        
        .btn-menu { display: flex; align-items: center; padding: 12px 18px; color: var(--text-muted); text-decoration: none; border-radius: 12px; font-size: 14px; font-weight: 600; gap: 12px; border: 1px solid transparent; }
        .btn-menu:hover { color: #f8fafc; background: rgba(0, 168, 255, 0.08); border-color: rgba(0,168,255,0.15); }
        .btn-new-chat { background: linear-gradient(135deg, rgba(0, 168, 255, 0.15), rgba(0, 114, 255, 0.05)); color: #00c6ff; border: 1px solid rgba(0, 168, 255, 0.3); margin-bottom: 20px; justify-content: center; box-shadow: 0 4px 15px rgba(0,168,255,0.05); font-weight: 700; }
        .btn-new-chat:hover { background: var(--gradient-accent); color:#ffffff; box-shadow: 0 4px 20px rgba(0,114,255,0.4); border-color:transparent; }

        .dev-menu-section { border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 15px; display: flex; flex-direction: column; gap: 4px; }
        .dev-menu-section-title { font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #00c6ff; font-weight: 800; padding: 5px 8px; }

        .history-container { flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 4px; margin-top: 5px; padding-right: 5px; }
        .history-container::-webkit-scrollbar { width: 4px; }
        .history-container::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .history-wrapper { display: flex; align-items: center; justify-content: space-between; border-radius: 10px; border: 1px solid transparent; }
        .history-item { flex: 1; padding: 11px 14px; font-size: 13.5px; color: var(--text-muted); text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .history-wrapper:hover { background: rgba(255, 255, 255, 0.03); border-color: rgba(255,255,255,0.05); }
        .history-wrapper.active { background: rgba(0, 168, 255, 0.08); border-color: rgba(0, 168, 255, 0.25); }
        .history-wrapper.active .history-item { color: #00c6ff; font-weight: 700; }
        
        .main-content { flex: 1; display: flex; flex-direction: column; height: 100vh; background: var(--content-bg); }
        .chat-area { flex: 1; padding: 50px 24%; overflow-y: auto; display: flex; flex-direction: column; gap: 30px; scroll-behavior: smooth; }
        .chat-area::-webkit-scrollbar { width: 6px; }
        .chat-area::-webkit-scrollbar-thumb { background: rgba(0,168,255,0.2); border-radius: 10px; }
        
        .message { max-width: 85%; padding: 18px 24px; border-radius: 20px; font-size: 14.5px; line-height: 1.7; animation: fadeInUp 0.4s ease backwards; box-shadow: 0 10px 30px rgba(0,0,0,0.25); }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        
        .message.user { background: var(--gradient-accent); color: #ffffff; align-self: flex-end; border-bottom-right-radius: 4px; box-shadow: 0 8px 30px rgba(0,114,255,0.3); }
        .message.bot { background: var(--bubble-bot); border: 1px solid var(--bubble-bot-border); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); color: var(--text-main); align-self: flex-start; border-bottom-left-radius: 4px; }
        
        .input-area { padding: 25px 24px 40px 24px; max-width: 1200px; width: 100%; margin: 0 auto; background: transparent; }
        .input-form { display: flex; gap: 15px; position: relative; background: var(--input-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); padding: 8px; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: 0 20px 50px rgba(0,0,0,0.4); }
        .input-form:focus-within { border-color: #00c6ff; box-shadow: 0 0 25px rgba(0,198,255,0.25); }
        .input-form input { flex: 1; padding: 15px 20px; background: transparent; border: none; color: var(--text-main); outline: none; font-size: 14.5px; }
        .input-form button { padding: 0 35px; background: var(--gradient-accent); color: white; border: none; border-radius: 16px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 15px rgba(0,114,255,0.3); letter-spacing: 0.5px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-menu-top">
            <div class="brand">NEVORIX <span>APPLE AI</span></div>
            <a href="index.php?action=new_chat" class="btn-menu btn-new-chat">✨ Mulai Sesi Baru</a>
            
            <div class="dev-menu-section">
                <div class="dev-menu-section-title">📂 Developer Views</div>
                <a href="views/library.php" class="btn-menu">📚 Kumpulan Pustaka Data</a>
                <a href="views/projects.php" class="btn-menu">💻 Proyek Inti Sistem</a>
                <a href="views/settings.php" class="btn-menu">⚙️ Pengaturan Engine</a>
            </div>

            <div class="dev-menu-section-title">💬 Riwayat Diagnosa Terbaru</div>
            <div class="history-container">
                <?php
                $email_aktif = mysqli_real_escape_string($koneksi, $_SESSION['email']);
                $list_sessions = mysqli_query($koneksi, "
                    SELECT ch.session_id, ch.user_message 
                    FROM chat_history ch
                    INNER JOIN (
                        SELECT session_id, MIN(id) as min_id
                        FROM chat_history
                        WHERE email = '$email_aktif' AND session_id IS NOT NULL AND session_id != ''
                        GROUP BY session_id
                    ) grp ON ch.id = grp.min_id
                    ORDER BY ch.id DESC LIMIT 10
                ");
                while ($list_sessions && $s_row = mysqli_fetch_assoc($list_sessions)) {
                    $isActive = (isset($_SESSION['current_chat_session']) && $s_row['session_id'] == $_SESSION['current_chat_session']) ? 'active' : '';
                    echo '<div class="history-wrapper '.$isActive.'"><a href="index.php?select_session='.htmlspecialchars($s_row['session_id']).'" class="history-item">📁 '.htmlspecialchars(substr($s_row['user_message'], 0, 24)).'...</a></div>';
                }
                ?>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="chat-area" id="chatArea">
            <div class="message bot">
                Selamat datang di <b>Nevorix Apple AI Premium Diagnostic</b>.<br>Mohon informasikan tipe unit iPhone yang ingin Anda lakukan pengujian kelayakan fungsi saat ini.<br><span style='color:var(--text-muted); font-size:12px;'>Contoh: iPhone 17 Pro Max, iPhone 13 Pro, atau iPhone 11</span>
            </div>
        </div>
        
        <div class="input-area">
            <form class="input-form" id="chatForm">
                <input type="text" id="userInput" placeholder="Ketik spesifikasi unit di sini..." autocomplete="off" required>
                <button type="submit">Kirim AI</button>
            </form>
        </div>
    </div>

    <script>
        // SCRIPT FRONTEND BERHUBUNGAN DENGAN chatbot.js PUNYAMU
        document.getElementById('chatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const inputField = document.getElementById('userInput');
            const message = inputField.value.trim();
            if(!message) return;

            // Render User Bubble
            const chatArea = document.getElementById('chatArea');
            const userBubble = document.createElement('div');
            userBubble.className = 'message user';
            userBubble.textContent = message;
            chatArea.appendChild(userBubble);
            inputField.value = '';
            chatArea.scrollTop = chatArea.scrollHeight;

            // Send via AJAX
            const formData = new FormData();
            formData.append('message', message);

            fetch('index.php?action=send_chat', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    const botBubble = document.createElement('div');
                    botBubble.className = 'message bot';
                    botBubble.innerHTML = data.reply;
                    chatArea.appendChild(botBubble);
                    chatArea.scrollTop = chatArea.scrollHeight;
                }
            });
        });
    </script>
</body>
</html>