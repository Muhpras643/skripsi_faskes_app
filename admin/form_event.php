<?php
session_start();
include '../includes/config.php';
include '../includes/auth_check.php';
include '../includes/functions.php';

auth_check('admin');

$message = '';
$error = '';

// Ambil username dari sesi
$current_username = $_SESSION['username'] ?? 'Tamu';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $jam = $_POST['jam'] ?? '';

    if (empty($nama) || empty($tanggal) || empty($jam)) {
        $error = "Semua kolom harus diisi!";
    } else {
        $message = "Data berhasil disimpan: Nama = " . htmlspecialchars($nama) . ", Tanggal = " . htmlspecialchars($tanggal) . ", Jam = " . htmlspecialchars($jam);
        // Di sini Anda bisa menambahkan logika untuk menyimpan data ke database jika diperlukan
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Acara</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-container input[type="text"],
        .form-container input[type="date"],
        .form-container input[type="time"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-container button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistem Klasifikasi Prioritas Fasilitas Kesehatan Kota Bekasi</h1>
        <p>Selamat Datang, <?php echo htmlspecialchars($current_username); ?>!</p>
        <a href="logout.php">Logout</a>
    </div>
    <div class="navbar">
        <a href="dashboard.php">Dashboard</a>
    <a href="manage_faskes.php">Manajemen Faskes</a>
    <a href="upload_and_classify.php">Klasifikasi Otomatis</a>
    <a href="view_results.php">Hasil Klasifikasi</a>
    <a href="form_event.php">Test Skill</a>
    </div>
    <div class="container">
        <h2>Formulir Acara</h2>
        <?php if ($message): ?>
            <p style="color: green;"><?php echo $message; ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="nama">Nama:</label>
            <input type="text" name="nama" id="nama" required>

            <label for="tanggal">Tanggal:</label>
            <input type="date" name="tanggal" id="tanggal" required>

            <label for="jam">Jam:</label>
            <input type="time" name="jam" id="jam" required>

            <button type="submit">Kirim Data</button>
        </form>
    </div>

    <script>
        // Set input tanggal ke tanggal hari ini
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        const todayDateString = `${year}-${month}-${day}`;
        document.getElementById('tanggal').value = todayDateString;

        // Set input jam ke jam saat ini
        const hours = String(today.getHours()).padStart(2, '0');
        const minutes = String(today.getMinutes()).padStart(2, '0');
        const currentTimeString = `${hours}:${minutes}`;
        document.getElementById('jam').value = currentTimeString;
    </script>
</body>
</html>
