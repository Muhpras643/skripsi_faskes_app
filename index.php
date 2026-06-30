<?php
session_start();
if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] == 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Klasifikasi Prioritas Fasilitas Kesehatan Kota Bekasi</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="header">
        <h1>Sistem Klasifikasi Prioritas Fasilitas Kesehatan Kota Bekasi</h1>
        <p>Aplikasi Penentu Prioritas Penambahan Tenaga Medis Berdasarkan Urgensi Penyakit dan Sebaran Fasilitas Kesehatan Menggunakan SVM.</p>
    </div>
    <div class="container" style="text-align: center; margin-top: 50px;">
        <h2>Selamat Datang!</h2>
        <p>Silakan masuk untuk menggunakan aplikasi.</p>
        <a href="login.php" class="button">Login</a>
    </div>
</body>
</html>