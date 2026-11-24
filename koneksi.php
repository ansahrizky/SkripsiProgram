<?php
// koneksi.php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_spk_smart";

$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek apakah koneksi berhasil atau gagal
if (!$koneksi) {
    die("Koneksi ke database XAMPP gagal: " . mysqli_connect_error());
}
?>