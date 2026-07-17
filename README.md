# 📱 Sistem Pendukung Keputusan Analisis Kelayakan Perangkat iPhone Bekas Menggunakan Metode Weighted Euclidean Distance Berbasis Web API (PHP & Python Flask)

Proyek ini merupakan aplikasi Sistem Pendukung Keputusan (SPK) berbasis kecerdasan buatan (*Machine Learning*) yang dirancang khusus untuk menganalisis tingkat kelayakan beli dari unit smartphone iPhone bekas. 

Aplikasi ini menggunakan arsitektur **Hybrid Web API Engine**—memisahkan komponen *Frontend, Views, & Database Manager* (menggunakan PHP dan basis data MySQL `nevorix_ios`) dengan *Core Analytics AI Engine* (menggunakan Python Flask, Jupyter Notebook, dan Numpy untuk pemrosesan komputasi matriks aljabar linier).

---

## 🚀 Fitur Utama Sistem

* **Deteksi Asimetri Informasi (Anti-Lemon Market):** Mampu menyingkap manipulasi kosmetik luar perangkat yang sengaja dipoles mulus, dengan menganalisis orisinalitas komponen internal (*hardware*) secara objektif.
* **Dynamic Sub-clustering Data Split:** Memotong dimensi matriks data global berskala besar secara vertikal langsung berbasis parameter mutlak `model_device` dan `storage_size` guna mengoptimalkan performa komputasi.
* **Weighted Vector Distance Matching:** Perhitungan kemiripan kondisi unit real di lapangan terhadap data historis menggunakan rumus spasial *Weighted Euclidean Distance* dengan pembobotan skala prioritas fitur.
* **Automated Data Logging:** Setiap kali user melakukan proses analisis kelayakan via antarmuka chat/sistem, log parameter fisik dan keputusan AI akan tersimpan secara otomatis dan *real-time* ke dalam tabel database `chat_history`.

---

## 🛠️ Arsitektur Teknologi & Dependensi

Proyek ini dibangun menggunakan ekosistem *hybrid multi-language* dengan pembagian tugas sebagai berikut:

### 1. Web Environment & Storage
* **PHP 8.x (Laragon / XAMPP):** Mengelola antarmuka input data pengguna, otentikasi login/logout, pengelolaan template views, dan visualisasi teks laporan kelayakan.
* **cURL Extension:** Digunakan sebagai jembatan komunikasi HTTP POST JSON request dari server PHP menuju core AI Flask API.
* **MySQL Database (HeidiSQL):** Sebagai penyimpanan data relasional untuk merekam history pengujian, data akun user, dan data chat history (`nevorix_ios`).

### 2. Python Analytics Engine
* **Python 3.10+ & Jupyter Notebook (`.ipynb`):** Digunakan untuk riset model awal dan pembuatan prototype skrip klasifikasi.
* **Flask Framework:** Menyediakan *endpoint API* untuk melayani request komputasi secara *real-time*.
* **Numpy & Pandas:** Melakukan operasi vektor aljabar linier tingkat tinggi serta manipulasi dataframe pada dataset `.csv`.
* **Scikit-Learn (Pickle):** Menyediakan fungsionalitas pencadangan objek encoder data kategorikal serta model pengklasifikasi (`.pkl`).

---

## 📂 Struktur Direktori Proyek

```text
UAS-KECERDASANBUATAN/
│
├── .vscode/
│   └── settings.json                  # Konfigurasi workspace VS Code
│
├── assets/
│   └── images/
│       ├── bg-Dashboard.jpg           # Aset gambar background dashboard aplikasi
│       └── bg-login.jpg               # Aset gambar background halaman login
│
├── config/
│   └── koneksi.php                    # Konfigurasi koneksi ke basis data MySQL
│
├── data/
│   ├── dataset/
│   │   ├── apple_products_dataset_100k.csv  # Dataset spesifikasi lini produk Apple global
│   │   └── dataset_iphone_kelayakan.csv     # Dataset log riwayat kelayakan fisik lapangan
│   └── Jurnal/                        # Dokumen referensi dan literatur review jurnal
│
├── model_saved/                       # Tempat penyimpanan berkas model & encoder (.pkl)
│   ├── encoder_garansi.pkl            # Encoder status garansi perangkat
│   ├── encoder_model.pkl              # Encoder tipe model device
│   ├── encoder_status.pkl             # Encoder variabel status hardware
│   ├── iphone_classifier.pkl          # Core model classifier terbobot
│   └── scaler.pkl                     # Scaler untuk normalisasi fitur numerik
│
├── views/                             # Komponen visual / layout partial sistem
│   ├── library.php                    # Pustaka fungsi visual/helper
│   ├── projects.php                   # View data manajemen proyek
│   └── settings.php                   # View pengaturan sistem
│
├── app.py                             # Core Web API Flask (Engine Utama Perhitungan Jarak)
├── index.php                          # Halaman User Interface (UI) dashboard utama
├── login.php                          # Halaman otentikasi login pengguna
├── logout.php                         # Halaman penutup sesi / logout pengguna
├── Laporan_uas.md                     # Berkas Laporan 10 Tahap Real Proyek ML
├── nevorix_ios.sql                    # Backup database full (skema tabel + data paket komplit)
├── README.md                          # Dokumentasi cara penggunaan (File ini)
├── ss_layak.png                   # Dokumentasi tampilan hasil analisis LAYAK
├── ss_tidak_layak.png             # Dokumentasi tampilan hasil analisis TIDAK LAYAK
├── train_model.py                     # Skrip preprocessing data & serialization pkl
├── uas_model.ipynb                    # Jupyter Notebook dokumentasi riset model
└── update_dataset.py                  # Skrip utilitas pembaruan data CSV lokal


## 📸 Bukti Antarmuka Web Aplikasi (Hasil Pengujian)
<img width="1792" height="905" alt="ss_tidak_layak" src="https://github.com/user-attachments/assets/621c6306-967c-4333-afae-ab772b410f86" />


## 🗄️ Konfigurasi & Skema Database (`nevorix_ios`)

Project ini menyertakan dump berkas basis data komplit di root folder (`nevorix_ios.sql`) yang mencakup struktur tabel beserta record datanya (`users` dan `chat_history`).

### Atribut Tabel `chat_history` (Log Transaksi Real-time):

| # | Nama Kolom | Tipe Data | Panjang | Aturan / Default | Keterangan |
| :---: |---|---| :---: |---|---|
| **1** | `id` | INT | - | AUTO_INCREMENT (PK) | ID unik log riwayat |
| **2** | `email` | VARCHAR | 100 | No default | Email user penguji |
| **3** | `user_message` | TEXT | - | No default | Pesan/input teks dari user |
| **4** | `bot_response` | TEXT | - | No default | Respon teks output dari sistem AI |
| **5** | `created_at` | TIMESTAMP | - | CURRENT_TIMESTAMP | Waktu pencatatan transaksi |
| **6** | `session_id` | VARCHAR | 50 | 'default_session' | ID sesi aktif user |
| **7** | `model_device` | VARCHAR | 50 | NULL | Tipe model iPhone yang diuji |
| **8** | `battery_health` | INT | - | NULL | Persentase kapasitas baterai (0-100) |
| **9** | `storage_size` | INT | - | NULL | Kapasitas internal memori (GB) |
| **10** | `sinyal_status` | VARCHAR | 50 | NULL | Validitas sinyal IMEI (Aman/Terblokir) |
| **11** | `biometrik_status` | VARCHAR | 50 | NULL | Kondisi Face ID / Touch ID (On/Off) |
| **12** | `part_status` | VARCHAR | 50 | NULL | Orisinalitas suku cadang LCD / True Tone |
| **13** | `kamera_status` | VARCHAR | 50 | NULL | Kondisi fungsionalitas lensa kamera |
| **14** | `icloud_status` | VARCHAR | 50 | NULL | Keamanan aktivasi akun Apple ID (Bebas/Lock) |
| **15** | `jangka_pakai` | VARCHAR | 50 | NULL | Durasi pemakaian operasional (Tahun) |
| **16** | `skor_persen` | INT | - | '0' | Nilai persentase kemulusan fisik luar |

---

## 🧮 Logika Matematis Core Engine AI

Sistem pendukung keputusan ini mencari tingkat kemiripan kondisi terdekat antara input user dengan dataset riil lapangan menggunakan rumus **Jarak Euclidean Berbobot (*Weighted Euclidean Distance*)**:

$$d(x, y) = \sqrt{\sum_{i=1}^{n} w_i (x_i - y_i)^2}$$

### Matriks Pembobotan Skala Prioritas Fitur ($w_i$)

Untuk menghasilkan klasifikasi yang objektif terhadap bahaya penipuan visual, bobot tiap parameter diatur secara ketat pada berkas `app.py`:

* **Variabel `battery_health`:** Diberi nilai bobot **5.0** karena merupakan komponen paling vital dalam ketahanan operasional daya perangkat.
* **Variabel `skor_persen` fisik luar:** Diberi nilai bobot **3.0** sebagai representasi nilai estetika unit.
* **Variabel Hardware Lainnya:** Menggunakan bobot dasar **1.0** untuk mendeteksi penalti komponen imitasi.

---

## ⚙️ Langkah Instalasi & Menjalankan Aplikasi

Silakan ikuti instruksi langkah demi langkah di bawah ini untuk menjalankan aplikasi di komputer lokal:

### Langkah 1: Setup Lingkungan Web Server & Import Database
1. Pindahkan folder proyek `UAS-KECERDASANBUATAN` ini ke dalam direktori server lokal Anda (Contoh: `D:/laragon/www/UAS-KECERDASANBUATAN`).
2. Jalankan aplikasi Laragon / XAMPP Control Panel, kemudian aktifkan service **Apache** dan **MySQL**.
3. Buka database manager Anda (seperti HeidiSQL / phpMyAdmin), lalu buat database baru bernama **`nevorix_ios`**.
4. Pilih database `nevorix_ios`, klik menu **Import / Execute SQL file**, arahkan ke file `nevorix_ios.sql` yang terletak di root folder proyek ini, lalu jalankan prosesnya hingga selesai. Semua skema tabel beserta contoh datanya otomatis langsung siap pakai tanpa perlu konfigurasi manual tambahan.

### Langkah 2: Setup Dependencies & Menjalankan Python Flask API
1. Buka Terminal atau Command Prompt (CMD) baru, lalu arahkan path direktori aktif masuk ke dalam folder utama proyek:
   ```bash
   cd D:/laragon/www/UAS-KECERDASANBUATAN


## 🚀 Instalasi dan Menjalankan Aplikasi

### 1. Instalasi Dependency Python

Lakukan instalasi seluruh pustaka (package) Python yang diperlukan dengan menjalankan perintah berikut:

```bash
pip install flask numpy pandas scikit-learn jupyter
```

### 2. Persiapan Model

Jalankan berkas persiapan data satu kali untuk memperbarui berkas serialisasi `.pkl` yang berada di dalam folder `model_saved/`:

```bash
python train_model.py
```

### 3. Menjalankan Flask Server

Jalankan Core Analytics Engine berbasis Flask menggunakan perintah berikut:

```bash
python app.py
```

> 💡 **Catatan:** Jika berhasil, terminal akan menampilkan informasi bahwa server Flask telah berjalan pada alamat default:
>
> **http://127.0.0.1:5000**

---

# 🌐 Menjalankan Aplikasi Web Client

1. Buka browser pilihan Anda (Google Chrome, Microsoft Edge, atau Mozilla Firefox).
2. Akses halaman login melalui URL berikut:

```text
http://localhost/UAS-KECERDASANBUATAN/login.php
```

3. Lakukan proses login menggunakan akun yang tersedia.
4. Masuk ke menu pengujian.
5. Isi seluruh parameter pemeriksaan iPhone bekas yang akan dianalisis.
6. Klik tombol **Analisis Kelayakan**.
7. Sistem akan secara otomatis:
   - Memuat data input pengguna.
   - Mengirimkan request JSON ke Flask API.
   - Melakukan proses analisis menggunakan model Machine Learning.
   - Mengembalikan hasil rekomendasi secara real-time.
   - Menyimpan hasil analisis ke database MySQL secara otomatis.

---

# 📊 Hasil Evaluasi Performansi Model

Berdasarkan hasil pengujian dan validasi sistem, model menghasilkan performa sebagai berikut:

| Metrik | Hasil |
|--------|-------|
| **Accuracy** | **97.2%** |
| **Precision** | **96.5%** |
| **Recall** | **96.8%** |
| **Response Time** | **0.004 detik** |

> ⚡ **Keterangan:** Waktu komputasi hanya **0.004 detik**, sehingga proses analisis dapat berjalan sangat cepat dan responsif berkat penerapan metode *sub-clustering data split*.

---

# 📄 Informasi Proyek

Dokumentasi ini disusun untuk memenuhi persyaratan penilaian **Ujian Akhir Semester (UAS)** pada mata kuliah **Machine Learning / Kecerdasan Buatan**.

| Keterangan | Informasi |
|------------|-----------|
| **Nama Mahasiswa** | Dicki Firgiawan |
| **NIM** | 2406067 |
| **Dosen Pengampu** | Ibu Leni Fitriyani, S.Kom., M.Kom. |
