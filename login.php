<?php
session_start();
include 'includes/config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($user_id, $hashed_password, $role);
    $stmt->fetch();
    $stmt->close();

    if ($user_id && password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_role'] = $role;
        $_SESSION['username'] = $username;

        if ($role == 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: user/dashboard.php');
        }
        exit();
    } else {
        $message = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Klasifikasi Faskes</title>
    <link rel="stylesheet" href="assets/css/style.css">
        <style>
        /* Gaya Global untuk halaman login (menimpa atau melengkapi style.css) */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #71b7e6, #9b59b6); /* Background gradien yang menarik */
            display: flex;
            flex-direction: column; /* Mengatur item secara vertikal */
            justify-content: center;
            align-items: center;
            min-height: 100vh; /* Pastikan mengambil tinggi viewport penuh */
            color: #333;
            overflow: hidden; /* Mencegah scrollbar jika ada elemen keluar */
        }

        .header {
            /* Pastikan header tetap terlihat di atas */
            position: absolute;
            top: 0;
            width: 100%;
            background-color: rgba(0, 86, 179, 0.9); /* Header semi-transparan */
            color: white;
            padding: 15px 0;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000; /* Pastikan header di atas konten lain */
        }
        .header h1 {
            margin: 0;
            font-size: 1.8em;
        }

        /* Gaya untuk kontainer login */
        .login-container {
            width: 350px;
            max-width: 90%; /* Responsif untuk layar kecil */
            padding: 40px 30px;
            background-color: rgba(255, 255, 255, 0.95); /* Putih sedikit transparan */
            border-radius: 12px; /* Sudut lebih membulat */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); /* Shadow yang lebih dalam */
            text-align: center;
            position: relative; 
            z-index: 1; 
            box-sizing: border-box; /* Pastikan padding termasuk dalam lebar */
            margin-top: 80px; /* Memberi ruang dari header */
        }

        .login-container h2 {
            margin-bottom: 30px;
            color: #0056b3; /* Biru gelap untuk judul */
            font-size: 2em;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1); /* Efek bayangan teks */
        }

        /* Gaya untuk input teks dan password */
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%; 
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid #c0c0c0; /* Border abu-abu terang */
            border-radius: 8px; /* Sudut membulat */
            box-sizing: border-box; 
            font-size: 1em;
            transition: border-color 0.3s ease, box-shadow 0.3s ease; /* Transisi halus saat fokus */
        }

        .login-container input[type="text"]:focus,
        .login-container input[type="password"]:focus {
            border-color: #007bff; /* Border biru saat fokus */
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.3); /* Shadow biru saat fokus */
            outline: none; /* Hilangkan outline default browser */
        }

        /* Gaya untuk tombol login */
        .login-container button {
            width: 100%;
            padding: 12px 15px;
            background: linear-gradient(45deg, #007bff, #0056b3); /* Gradien biru pada tombol */
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            letter-spacing: 1px; /* Jarak antar huruf */
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3); /* Shadow pada tombol */
            transition: all 0.3s ease; /* Transisi untuk efek hover */
        }

        .login-container button:hover {
            background: linear-gradient(45deg, #0056b3, #003f7f); /* Gradien lebih gelap saat hover */
            box-shadow: 0 6px 15px rgba(0, 123, 255, 0.4); /* Shadow lebih besar saat hover */
            transform: translateY(-2px); /* Sedikit naik saat hover */
        }

        /* Gaya untuk pesan error */
        .message {
            color: #dc3545; /* Merah untuk pesan error */
            background-color: #f8d7da; /* Background merah muda */
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.95em;
        }

        /* Gaya untuk tautan daftar */
        .register-link {
            display: block;
            margin-top: 25px;
            text-decoration: none;
            color: #007bff;
            font-size: 0.95em;
            transition: color 0.3s ease;
        }
        .register-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }
    </style>

</head>
<body>
    <div class="login-container">
        <h2>Login Aplikasi</h2>
        <?php if ($message): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
         <a href="register.php" class="register-link">Belum punya akun? Buat akun baru</a>
    </div>
</body>
</html>