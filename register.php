<?php
session_start();
include 'includes/config.php'; // Sertakan file koneksi database

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = 'pengguna'; // Default role for public registration

    // Validasi input
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Semua kolom harus diisi!";
    } elseif ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok!";
    } elseif (strlen($password) < 6) { // Contoh: password minimal 6 karakter
        $error = "Password minimal 6 karakter!";
    } else {
        // Cek apakah username sudah ada
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $error = "Username sudah terdaftar. Silakan pilih username lain.";
        }
        $stmt_check->close();

        if (empty($error)) { // Jika tidak ada error validasi
            $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash password

            $stmt_insert = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("sss", $username, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                $message = "Akun berhasil dibuat! Silakan login.";
                // Redirect ke halaman login setelah berhasil
                header('Location: login.php?success=registered');
                exit();
            } else {
                $error = "Gagal membuat akun: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru - Sistem Klasifikasi Faskes</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .register-container {
            width: 350px;
            margin: 50px auto;
            padding: 25px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .register-container h2 {
            margin-bottom: 25px;
        }
        .register-container label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .register-container input[type="text"],
        .register-container input[type="password"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .register-container button {
            width: 100%;
            padding: 10px;
            background-color: #28a745; /* Warna hijau untuk daftar */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .register-container button:hover {
            background-color: #218838;
        }
        .message.success {
            color: green;
            margin-bottom: 15px;
        }
        .message.error {
            color: red;
            margin-bottom: 15px;
        }
        .login-link {
            display: block;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
        }
        .login-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistem Klasifikasi Prioritas Fasilitas Kesehatan Kota Bekasi</h1>
    </div>
    <div class="register-container">
        <h2>Daftar Akun Baru</h2>
        <?php if ($message): ?>
            <p class="message success"><?php echo $message; ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="message error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <label for="confirm_password">Konfirmasi Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
            
            <button type="submit">Daftar Akun</button>
        </form>
        <a href="login.php" class="login-link">Sudah punya akun? Kembali ke Login</a>
    </div>
</body>
</html>