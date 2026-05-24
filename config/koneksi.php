<?php
$host = "localhost";
$user = "root"; 
$pass = "1234";     
$db = "belajar_php"; 

// Buat koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
// echo "Koneksi berhasil"; // (Opsional, untuk tes)
?>   