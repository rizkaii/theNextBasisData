<?php
session_start();
include '../config/database.php'; // pastikan file koneksi DB kamu benar

// Misal ini terjadi setelah login berhasil dan mendapatkan id dari tb_login
$id_login = $_SESSION['id_login']; // pastikan sudah disimpan di session saat login

// Cek apakah id_login sudah ada di tb_customer
$query = mysqli_query($conn, "SELECT * FROM tb_customer WHERE id_login = '$id_login'");
$cek = mysqli_num_rows($query);

if ($cek > 0) {
    // Sudah ada data customer -> lanjut ke halaman utama
    header("Location: ../user/index.php"); // ganti dengan halaman tujuan kamu
    exit();
} else {
    // Belum ada data customer -> arahkan ke halaman create customer
    header("Location: customer.php"); // halaman untuk mengisi data customer
    exit();
}
?>
