<?php
// error_reporting(0); // Aktifkan untuk debugging, nonaktifkan untuk produksi

$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "db_faskeskotabekasi";

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

if (!$conn) {
    die("Tidak dapat terhubung ke database: " . mysqli_connect_error());
}
?>