<?php
$host = "localhost:3306";
$user = "root";
$pass = "";
$db   = "elearning_k3";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>