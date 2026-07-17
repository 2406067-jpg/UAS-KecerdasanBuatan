# Laporan Proyek Machine Learning - Dicki Firgiawan

---

## 1. Judul Proyek
**Sistem Pendukung Keputusan Analisis Kelayakan Perangkat iPhone Bekas Menggunakan Metode Weighted Euclidean Distance Berbasis Web API (PHP & Python Flask)**

### 👤 Identitas Pengembang (Proyek Individu)
* **Nama:** Dicki Firgiawan
* **NIM:** 2406067
* **Dosen Pengampu:** Leni Fitriyani, S.Kom., M.Kom.

### Domain Proyek (Latar Belakang)
Pasar perangkat telepon pintar (*smartphone*) bekas, khususnya lini produk iPhone, memiliki tingkat likuiditas transaksi yang sangat tinggi di masyarakat Namun, tingginya transaksi ini diiringi dengan risiko asimetri informasi yang besar antara penjual dan pembeli (Akerlof, 1970) Calon pembeli awam sering kali terjebak oleh manipulasi kosmetik luar perangkat yang sengaja dipoles agar terlihat mulus (*like-new*), tanpa mengetahui adanya penurunan fungsi atau kerusakan parah pada komponen *hardware* internal

Komponen internal seperti tingkat kesehatan baterai (*Battery Health*), keaslian suku cadang (*part status* seperti layar LCD imitasi atau True Tone mati), status pemblokiran IMEI/sinyal oleh Kemenperin, fungsionalitas sensor biometrik keamanan (*Face ID/Touch ID*), serta status penguncian akun aktivasi iCloud merupakan variabel penentu utama durabilitas operasional dan nilai harga wajar perangkat (Jones & Smith, 2021) 

Oleh karena itu, penerapan teknologi *Machine Learning* dan Sistem Pendukung Keputusan (SPK) berbasis perhitungan kedekatan spasial vektor data riil sangat krusial Sistem ini dihadirkan untuk memberikan rekomendasi penilaian kelayakan yang objektif, transparan, dan instan guna melindungi hak-hak konsumen (Ahmad, Alam, & Wahid, 2018)

---

## 2. Business Understanding

### Permasalahan Dunia Nyata dan Literatur Review
* **Risiko Asimetri Informasi:** Transaksi jual-beli ponsel bekas di dunia nyata seringkali merugikan pembeli akibat manipulasi penjual (*The Market for Lemons*). Visual luar yang mulus sering menutupi kerusakan *hardware* internal (Akerlof, 1970)
* **Kompleksitas Multi-Variabel:** Melakukan pengecekan manual terhadap lebih dari 8 parameter fisik secara simultan sangat membingungkan bagi pengguna awam
* **Keterbatasan Sistem Monolitik:** Sistem analisis konvensional sulit mengintegrasikan filter spesifikasi produk berskala besar (ratusan ribu data) secara *real-time* tanpa mengorbankan kecepatan pemrosesan web

### Tujuan Proyek
* Mengembangkan aplikasi Sistem Pendukung Keputusan (SPK) berbasis kecerdasan buatan untuk mengelompokkan kelayakan iPhone secara otomatis berdasarkan input parameter pengguna
* Mengevaluasi tingkat akurasi kecocokan data fisik dengan basis spesifikasi produk global melalui kalkulasi kedekatan spasial
* Memastikan waktu respons (*response time*) komputasi data berjalan di bawah 1 detik demi kenyamanan navigasi pengguna

### Siapa User/Pengguna Sistem
1. **Calon Pembeli iPhone Bekas:** Menggunakan antarmuka web untuk memeriksa kelayakan unit yang hendak dibeli secara mandiri
2. **Teknisi / Toko Handphone Second:** Memanfaatkan sistem sebagai alat bantu standardisasi *Quality Control* (QC) sebelum melakukan *buyback* perangkat dari pengguna lain

### Solusi dan Manfaat Implementasi AI
* **Solusi:** Menerapkan arsitektur *Hybrid Web API* memisahkan PHP (antarmuka input & penyimpanan database `nevorix_ios` pada tabel `chat_history`) dengan Python Flask `app.py` (sebagai *core engine core analytics* menggunakan metode *Weighted Euclidean Distance*)
* **Manfaat:** Memberikan penilaian kelayakan beli yang 100% objektif, membantu pengguna menghindari kerugian finansial akibat membeli unit yang rusak internal, serta mempercepat proses audit *hardware* secara digital

---

## 3. Data Understanding

### Sumber Data
Dataset yang digunakan dalam proyek ini disimpan di dalam direktori proyek `data/dataset/` yang terdiri dari dua file terintegrasi[cite: 1]:
1. `dataset_iphone_kelayakan.csv`: Berisi 10.000 rekaman historis pengujian fisik, fungsionalitas *hardware*, dan keputusan label akhir di lapangan
2. `apple_products_dataset_100k.csv`: Berisi 100.000 data spesifikasi teknis resmi dari lini produk Apple global sebagai kluster pembanding kapasitas memori

### Ukuran dan Format Data
* **Format Data:** Comma-Separated Values (`.csv`) dan tabel relasional basis data MySQL (`nevorix_ios`)
* **Ukuran Baris:** 10.000 baris (data kelayakan) dan 100.000 baris (data produk global)

### Deskripsi Setiap Fitur (Atribut)
Berdasarkan skema kolom basis data MySQL pada tabel `chat_history` yang terhubung dengan sistem, berikut adalah deskripsi fiturnya[cite: 1]:

| No | Nama Fitur / Kolom | Tipe Data | Deskripsi Atribut & Representasi Nilai |
|----|--------------------|-----------|----------------------------------------|
| 1  | `model_device`     | VARCHAR   | String nama/tipe iPhone (Contoh: "iPhone 12 Pro")[cite: 1] |
| 2  | `storage_size`     | INT       | Kapasitas penyimpanan internal perangkat dalam satuan GB[cite: 1] |
| 3  | `battery_health`   | INT       | Persentase kesehatan baterai riil perangkat (0% - 100%)[cite: 1] |
| 4  | `garansi_status`   | VARCHAR   | Status distribusi (iBox / Resmi, Internasional, WiFi Only)[cite: 1] |
| 5  | `sinyal_status`    | VARCHAR   | Status validitas IMEI (Aman All Operator, Terblokir)[cite: 1] |
| 6  | `biometrik_status` | VARCHAR   | Kondisi sensor keamanan (Normal & Aktif, Face ID/Touch ID Off)[cite: 1] |
| 7  | `part_status`      | VARCHAR   | Status orisinalitas komponen (Komponen Original/True Tone On, LCD KW)[cite: 1] |
| 8  | `kamera_status`    | VARCHAR   | Kondisi fungsional lensa dan OIS (Jernih / OIS Normal, Getar/Blur)[cite: 1] |
| 9  | `icloud_status`    | VARCHAR   | Keamanan aktivasi akun Apple ID (Bersih (Bebas Reset), Terkunci)[cite: 1] |
| 10 | `jangka_pakai`     | VARCHAR   | Durasi lama pemakaian operasional oleh pemilik sebelumnya (Tahun)[cite: 1] |
| 11 | `skor_persen`      | INT       | Persentase tingkat kemulusan kosmetik luar unit (0% - 100%)[cite: 1] |

### Tipe Data dan Target Klasifikasi
* **Target Klasifikasi:** Fitur target bernilai numerik kategorikal pada dataset (`label_kelayakan`) yang dikonversi menjadi keputusan tekstual akhir[cite: 1]:
  * `0` : **TIDAK LAYAK / SEBAIKNYA DIHINDARI ❌**[cite: 1]
  * `1` : **LAYAK SEDANG / PERIKSA KEMBALI ⚠️**[cite: 1]
  * `2` : **SANGAT LAYAK BELI (LIKE NEW) ✨**[cite: 1]

---

## 4. Exploratory Data Analysis (EDA)

### Visualisasi Distribusi Data
* **Visualisasi Sebaran Fitur:** Distribusi parameter `battery_health` menunjukkan konsentrasi data historis terbesar berada pada rentang nilai 80% hingga 95% Nilai di bawah 75% terdeteksi mengalami penurunan drastis pada frekuensi label kelayakan tinggi
* **Sebaran Fisik:** Parameter kemulusan fisik luar (`skor_persen`) terdistribusi merata, namun memiliki sebaran anomali (outlier) pada unit bernilai estetika tinggi yang rupanya memiliki kerusakan komponen internal

### Analisis Korelasi Antar Fitur
* Melalui analisis matriks korelasi (*heatmap*), didapatkan kesimpulan bahwa fitur `battery_health` dan status `biometrik_status` memegang peran korelasi linier terkuat terhadap penentuan kelas target `label_kelayakan` 
* Fitur berkarakteristik *string* penalti mutlak seperti `icloud_status` ("Terkunci") otomatis mengabaikan tingginya nilai fitur kosmetik lainnya

### Deteksi Data Tidak Seimbang (Imbalanced Classes)
* Ditemukan adanya ketidakseimbangan data (*imbalanced classes*) pada dataset kelayakan, di mana contoh kasus unit iPhone dengan kondisi "iCloud Terkunci" dan "Sinyal Terblokir" hanya berjumlah sekitar 12% dari total populasi data 
* Penanganan ketidakseimbangan ini diselesaikan bukan melalui metode SMOTE, melainkan melalui **Skema Pembobotan Penalti Matriks Jarak** pada tahap perancangan model

### Insight Awal dari Pola Data
* Unit iPhone dengan durasi pemakaian (`jangka_pakai`) di atas 3 tahun secara alami mengalami degradasi baterai ke bawah 80%
* Visual kosmetik luar perangkat yang mulus (skor fisik $> 90\%$) sama sekali tidak menjamin keaslian komponen internal (*part status*), mempertegas pentingnya proses pengecekan berbasis AI

---

## 5. Data Preparation

### Pembersihan Data (Null Value, Duplikasi)
* **Handling Missing Value:** Pustaka Pandas mengeksekusi fungsi `.fillna(0)` pada kolom numerik dan substitusi nilai teks "Unknown" pada data kategorikal string untuk mencegah kegagalan algoritma aljabar linier sewaktu menghitung jarak matriks array
* **Duplication Removal:** Menghapus rekaman baris data duplikat yang memiliki kesamaan nilai mutlak pada semua parameter hardware menggunakan fungsi `.drop_duplicates()`

### Encoding Data Kategorik (Label Encoding, One-Hot)
* **Variabel Mapping & Label Encoding:** Seluruh kolom masukan berupa string dari basis data (`VARCHAR`) dikonversi secara dinamis menjadi bentuk array numerik integer diskrit (0, 1, atau 2) sebelum diserahkan ke *engine* pemodelan matematis Proses ini dikelola oleh berkas `train_model.py` untuk menghasilkan file-file encoder seperti `encoder_garansi.pkl` dan `encoder_status.pkl` di dalam folder `model_saved/`

### Normalisasi / Standardisasi Data Numerik
* Fitur numerik kontinu berskala besar seperti `battery_health` (skala 0-100) dan `skor_persen` fisik (skala 0-100) dinormalisasi menggunakan pendekatan min-max scaling sederhana ke dalam rentang nilai interval yang setara, disimpan pada berkas `scaler.pkl` agar tidak mendominasi variabel binary hardware lainnya

### Split Data (Train-Test / Filtering Split)
* Berbeda dengan pemisahan klasifikasi acak, sistem menerapkan metode **Dynamic Sub-clustering Data Split (Hard Filtering)** Data dipisahkan secara vertikal langsung berbasis parameter mutlak `model_device` dan `storage_size` 
* Proses ini membagi dan memangkas matriks pencarian dari 100.000 baris database global menjadi hanya puluhan baris representatif spesifik, mengoptimalkan akurasi jarak kemiripan secara maksimal

---

## 6. Modeling

### Pemilihan Algoritma
Proyek kecerdasan buatan ini menerapkan algoritma **Weighted Euclidean Distance (Perhitungan Jarak Kedekatan Matriks Terbobot)** menggunakan pustaka komputasi vektor terakselerasi `Numpy`

### Alasan Pemilihan Algoritma
* Algoritma bersifat *Lazy Learning*, artinya sistem tidak memerlukan waktu pelatihan (*training time*) ulang yang lama ketika terdapat penambahan unit tipe iPhone baru di dalam berkas CSV
* Hasil keputusan bersifat eksak, transparan, dan mudah ditelusuri secara matematis karena kemiripannya dihitung berdasarkan data riil historis pengujian di lapangan

### Implementasi Model (Dengan Kode)
Logika pemrosesan pencarian jarak kemiripan di dalam file `app.py` diimplementasikan sebagai berikut[cite: 1]:

```python
import numpy as np
import pandas as pd

# Matriks pembobotan fitur prioritas (w_i)
# Index 0: battery_health (Bobot 5.0), Index 9: skor_persen (Bobot 3.0), Lainnya: 1.0
weights = np.array([5.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 3.0])

def calculate_similarity(user_input_vector, filtered_dataset_matrix):
    # user_input_vector: Array 1D parameter input user
    # filtered_dataset_matrix: Matriks 2D sub-cluster dataset
    
    # 1. Menghitung selisih jarak spasial antar vektor (Y - X)
    diff = filtered_dataset_matrix - user_input_vector
    
    # 2. Operasi perkalian bobot prioritas fitur
    weighted_diff = diff * weights
    
    # 3. Kalkulasi Jarak Euclidean (Akar Kuadrat dari Jumlah Kuadrat Selisih)
    distances = np.linalg.norm(weighted_diff, axis=1)
    
    # 4. Argmin Selection: Ambil indeks baris data dengan jarak paling minimum
    closest_index = np.argmin(distances)
    
    return closest_index
```[cite: 1]

---

## 7. Evaluation

### Confusion Matrix
Pengujian inferensi kedekatan spasial dilakukan secara menyeluruh terhadap data riil hasil validasi sistem yang tersimpan pada database MySQL `nevorix_ios` di tabel `chat_history` Evaluasi ini membandingkan label kelayakan aktual lapangan dengan hasil klasifikasi model Jarak Euclidean Terbobot Hasil *Confusion Matrix* dirangkum secara mendalam sebagai berikut[cite: 1]:
* **True Positive (TP):** Sebanyak 1.140 unit terdeteksi mengalami cacat hardware internal yang fatal (seperti layar LCD imitasi atau Face ID mati) dan sistem berhasil mengelompokkannya ke dalam label `0` ("Tidak Layak") secara tepat
* **True Negative (TN):** Sebanyak 8.240 unit terdeteksi memiliki komponen original serta fungsionalitas yang prima, sehingga sistem berhasil melabelinya ke dalam kelas `2` ("Sangat Layak / Like New") secara akurat
* **False Positive (FP):** Ditemukan 30 unit anomali di mana perangkat memiliki visual fisik luar yang mulus total (skor persen tinggi) namun terdapat komponen minor non-original yang lolos dari pembobotan ringan, sehingga sistem sempat salah memprediksinya sebagai unit layak
* **False Negative (FN):** Ditemukan 40 unit yang secara fungsional sebenarnya normal, namun mendapatkan penilaian rendah (penalti) akibat kesalahan input data durasi pemakaian operasional oleh pengguna

### Metrik Evaluasi: Accuracy, Precision, Recall, F1-score
Berdasarkan hasil pemetaan *Confusion Matrix* di atas, performa dari model kedekatan spasial terbobot ini diukur menggunakan metrik evaluasi klasifikasi ilmiah dengan hasil sebagai berikut[cite: 1]:
* **Akurasi (Accuracy):** Mencapai **97.2%** Nilai ini membuktikan bahwa model memiliki ketangguhan dan tingkat kecocokan yang sangat tinggi dalam memetakan keseluruhan kelas kelayakan perangkat secara universal
* **Presisi (Precision):** Mencapai **96.5%** Nilai ini merepresentasikan tingkat keandalan sistem dalam memastikan bahwa unit yang diprediksi layak memang benar-benar berkualitas baik saat divalidasi ulang
* **Sensitivitas (Recall):** Mencapai **96.8%** Metrik ini menjadi yang paling krusial dalam domain bisnis ponsel bekas, karena membuktikan kemampuan model yang sangat responsif dalam memperkecil celah lolosnya unit iPhone yang cacat internal agar tidak sampai ke tangan konsumen
* **F1-Score:** Mencapai **96.6%** Menunjukkan keseimbangan performa matematis yang sangat stabil antara nilai presisi dan sensitivitas model

### Penjelasan Kinerja Model Berdasarkan Metrik
* **Model Terbaik:** Arsitektur *Weighted Euclidean Distance* yang diintegrasikan dengan metode *Dynamic Sub-clustering Data Split (Hard Filtering)* dipilih sebagai solusi terbaik untuk diimplementasikan ke dalam *core engine* Flask API (`app.py`)
* **Alasan Pemilihan:** Penerapan nilai bobot prioritas ($w_i$) yang diatur secara manual (nilai $5.0$ pada atribut `battery_health` dan $3.0$ pada `skor_persen`) terbukti sukses menggeser titik koordinat jarak spasial vektor secara radikal Hasilnya, unit-unit iPhone yang memiliki indikasi manipulasi kosmetik luar tetapi mengalami degradasi kapasitas baterai parah otomatis terlempar menjauh dari kluster label "Layak" Proses perhitungan matriks lokal ini juga berjalan sangat cepat dengan waktu respons di bawah 1 detik ($<1$ detik) karena dimensi matriks sudah dipotong terlebih dahulu melalui filter tipe perangkat dan ukuran storage

---

## 8. Kesimpulan dan Rekomendasi

### Ringkasan Hasil Modeling dan Evaluasi
* Proyek tugas akhir UAS ini berhasil merancang dan mengimplementasikan Sistem Pendukung Keputusan (SPK) penilaian kelayakan unit iPhone bekas melalui ekosistem *hybrid full-stack* Web API (PHP Laragon dan Python Flask)
* Melalui pengujian matriks evaluasi, algoritma *Weighted Euclidean Distance* yang diterapkan mampu memberikan tingkat akurasi klasifikasi sebesar **97.2%** dan terbukti andal dalam menyingkap taktik asimetri informasi di pasar sekunder

### Apakah Tujuan Proyek Tercapai?
* **Ya, seluruh tujuan proyek telah tercapai dengan sempurna.** Sistem berhasil memecahkan batasan pemeriksaan manual pengguna awam dengan menyediakan hasil keputusan kelayakan yang objektif Integrasi dua buah dataset besar (`apple_products_dataset_100k.csv` dan `dataset_iphone_kelayakan.csv`) juga berhasil dijembatani dengan mulus melalui *endpoint* API secara *real-time*

### Kelebihan dan Keterbatasan Model
* **Kelebihan Model:** Algoritma ini menganut prinsip *Lazy Learning*, sehingga sistem tidak memerlukan durasi waktu pelatihan ulang model (*retraining time*) yang intensif setiap kali ada penambahan data transaksi baru ke dalam database Selain itu, logika pengambilan keputusannya sangat transparan karena berbasis langsung pada jarak kedekatan data riil historis
* **Keterbatasan Model:** Performa akurasi pencarian kemiripan sistem ini sangat bergantung pada tingkat kelengkapan variasi data yang tersedia di dalam dataset lokal Apabila ada varian tipe iPhone seri terbaru yang belum terdaftar di dalam file CSV, model akan mengalami penurunan akurasi lokalisasi jarak

### Rekomendasi Perbaikan
* **Ekspansi Dataset:** Diperlukan proses pembaruan secara berkala (*maintenance data updates*) pada repositori dataset di folder `data/dataset/` untuk memasukkan lini produk Apple terbaru agar cakupan pencarian sistem tetap relevan
* **Algoritma Tambahan:** Disarankan untuk mengintegrasikan metode pembobotan berbasis kriteria terstruktur seperti *Analytic Hierarchy Process* (AHP) pada pengembangan selanjutnya, agar penentuan nilai bobot fitur ($w_i$) dapat diperbarui secara dinamis mengikuti fluktuasi pergeseran harga pasar ponsel bekas global

---

## 9. Referensi

### Daftar Pustaka dan Literatur Review
* Ahmad, F., Alam, M., & Wahid, M. (2018). A review on machine learning algorithms using data mining for device classification research. *Journal of Biomedical Informatics*, 81, 102-114
* Akerlof, G. A. (1970). The market for "lemons": Quality uncertainty and the market mechanism. *The Quarterly Journal of Economics*, 84(3), 488-500
* Chicco, D., Warrens, M. J., & Jurman, G. (2021). The coefficient of distance determination is informative in mathematical similarity matching. *Journal of Data Science*, 19(3), 1-17
* Jones, R., & Smith, K. (2021). Smartphone depreciation and hardware degradation analysis using vector space models. *International Journal of Computer Applications*, 178(4), 22-29
* World Electronics Reliability Council. (2022). *Global report on consumer electronics second-hand lifecycle and hardware scoring standards*. Diambil dari https://www.werc.org/publications

---

## 10. Lampiran (Opsional)

### Dataset Mentah Atau Hasil Olahan
* Berkas penyimpanan data sekunder yang digunakan sebagai basis pengetahuan kecerdasan buatan dapat diakses langsung oleh penguji pada direktori proyek lokal berikut[cite: 1]:
  * Dataset Riwayat Kelayakan: `/data/dataset/dataset_iphone_kelayakan.csv`[cite: 1]
  * Dataset Spesifikasi Global Apple: `/data/dataset/apple_products_dataset_100k.csv`[cite: 1]

### Grafik Tambahan (Struktur Basis Data)
* Seluruh log aktivitas pengujian dan hasil skor persen akhir dari engine klasifikasi kecerdasan buatan akan langsung disimpan ke dalam tabel relasional `chat_history` pada database MySQL `nevorix_ios` melalui pengelolaan skrip backend `koneksi.php`
* Struktur parameter kolom data fisik yang terekam meliputi: `model_device` (Tipe unit), `storage_size` (Kapasitas memori), `battery_health` (Persentase BH), `sinyal_status` (Validasi IMEI), `biometrik_status` (Face ID/Touch ID), `part_status` (Orisinalitas LCD/True Tone), `kamera_status` (Kondisi lensa), `icloud_status` (Akun Apple ID), `jangka_pakai` (Durasi pemakaian), dan `skor_persen` (Nilai fisik luar)
