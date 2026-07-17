<?php
// Konfigurasi Database UAS Dicki Firgiawan (NIM: 2406067)
$host     = "localhost";     // Host bawaan Laragon / XAMPP
$user     = "root";          // Username default database
$password = "";              // Password default (kosongkan jika pakai Laragon/XAMPP)
$database = "nevorix_ios";   // Nama database yang sinkron dengan SQL baru

// Proses koneksi ke MySQL
$koneksi = mysqli_connect($host, $user, $password, $database);

// Cek apakah koneksi berhasil atau gagal
if (!$koneksi) {
    die("Aduh masee, koneksi database gagal cok! Periksa Laragon lu: " . mysqli_connect_error());
}

// Set charset ke utf8mb4 agar sinkron dengan struktur tabel database
mysqli_set_charset($koneksi, "utf8mb4");

// Koneksi Aman dan Siap Dipakai masee!
?>