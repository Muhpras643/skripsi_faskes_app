<?php
$password_plaintext = 'admin123'; // Ini adalah kata sandi yang PASTI akan Anda ketik saat login
$hashed_password = password_hash($password_plaintext, PASSWORD_DEFAULT);
echo "Hash yang benar untuk '$password_plaintext': <br>";
echo "<strong>" . $hashed_password . "</strong>";
echo "<br><br>";

$password_pengguna = 'pengguna123'; // Kata sandi untuk pengguna biasa
$hashed_pengguna = password_hash($password_pengguna, PASSWORD_DEFAULT);
echo "Hash yang benar untuk '$password_pengguna': <br>";
echo "<strong>" . $hashed_pengguna . "</strong>";
?>