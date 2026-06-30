<?php
session_start();
include '../includes/config.php';
include '../includes/auth_check.php';
include '../includes/functions.php';

auth_check('admin');

// Data ringkasan
$total_faskes = 0;
// Pastikan koneksi $conn sudah tersedia dari config.php
if (isset($conn)) {
    $stmt_total = $conn->prepare("SELECT COUNT(*) FROM faskes_data");
    if ($stmt_total) {
        $stmt_total->execute();
        $stmt_total->bind_result($total_faskes);
        $stmt_total->fetch();
        $stmt_total->close();
    } else {
        // Handle error jika prepare gagal
        error_log("Failed to prepare statement for total faskes: " . $conn->error);
    }
} else {
    // Handle error jika koneksi $conn tidak tersedia
    error_log("Database connection \$conn is not available.");
}


// Data klasifikasi (contoh statis — ganti dengan data dari laporan sebenarnya)
$precision = ['Rendah' => 0.67, 'Sedang' => 0.57, 'Tinggi' => 1.00];
$recall = ['Rendah' => 0.67, 'Sedang' => 0.67, 'Tinggi' => 0.86];
$f1 = ['Rendah' => 0.67, 'Sedang' => 0.62, 'Tinggi' => 0.92];
$accuracy_overall = 0.74;

$labels = json_encode(array_keys($precision));
$precision_data = json_encode(array_values($precision));
$recall_data = json_encode(array_values($recall));
$f1_data = json_encode(array_values($f1));
$accuracy_data = json_encode([$accuracy_overall, 1 - $accuracy_overall]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Penting untuk responsivitas -->
    <title>Dashboard Admin - Klasifikasi Faskes</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Pastikan path ini benar -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Gabungan CSS untuk tampilan dashboard dan diagram */

        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .header {
            background: #0056b3;
            color: #fff;
            padding: 15px;
            text-align: center;
        }

        .navbar {
            background: #007bff;
            text-align: center;
            padding: 10px 0;
        }

        .navbar a {
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: bold;
        }

        .navbar a:hover {
            background: #0056b3;
        }

        .container {
            max-width: 1000px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
        }

        /* Gaya untuk grid diagram */
        .chart-grid {
            display: flex; /* Menggunakan Flexbox untuk tata letak */
            flex-wrap: wrap; /* Memungkinkan item untuk pindah baris jika tidak cukup ruang */
            justify-content: center; /* Pusatkan item secara horizontal */
            gap: 20px; /* Jarak antar chart-container */
            margin-top: 20px; /* Sedikit jarak dari konten di atasnya */
        }

        /* Gaya untuk setiap kontainer diagram */
        .chart-container {
            flex: 1 1 45%; /* Fleksibel, dapat menyusut, dan mengambil setidaknya 45% lebar */
            max-width: 400px; /* Batasi lebar maksimum setiap diagram */
            margin: 0; /* Hapus margin auto yang lama */
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); /* Tambahkan sedikit bayangan untuk estetika */
            display: flex; /* Menggunakan flexbox di dalam container untuk memusatkan konten */
            flex-direction: column; /* Susun konten secara vertikal */
            align-items: center; /* Pusatkan item secara horizontal di dalam container */
        }

        /* Media Queries untuk responsivitas */
        @media (max-width: 768px) {
            .chart-container {
                flex: 1 1 90%; /* Pada layar kecil, setiap diagram mengambil hampir seluruh lebar */
                max-width: 350px; /* Sesuaikan max-width untuk layar kecil */
            }
        }

        @media (max-width: 480px) {
            .chart-container {
                flex: 1 1 100%; /* Pada layar sangat kecil, setiap diagram mengambil lebar penuh */
                max-width: none; /* Hapus batasan max-width */
            }
        }

        /* Gaya untuk elemen canvas */
        canvas {
            width: 100% !important;
            height: auto !important;
            max-height: 300px; /* Batasi tinggi maksimum canvas agar tidak terlalu besar */
        }

        /* Gaya untuk judul diagram */
        h3 {
            text-align: center;
            margin-bottom: 15px;
            color: #0056b3;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Sistem Klasifikasi Faskes Kota Bekasi</h1>
    <p>Selamat Datang, Admin! <a href="../logout.php" style="color: #fff; text-decoration: underline;">Logout</a></p>
</div>
<div class="navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="manage_faskes.php">Manajemen Faskes</a>
    <a href="upload_and_classify.php">Klasifikasi Otomatis</a>
    <a href="view_results.php">Hasil Klasifikasi</a>
    <a href="view_results.php">Hasil Klasifikasi</a>
</div>
<div class="container">
    <h2>Ringkasan Data</h2>
    <p>Total Faskes terdaftar: <strong><?php echo $total_faskes; ?></strong></p>

    <!-- MULAI BAGIAN PENTING: Pastikan div chart-grid ini membungkus semua chart-container -->
    <div class="chart-grid">
        <div class="chart-container">
            <h3>Overall Accuracy</h3>
            <canvas id="accuracyChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Precision Semua Kelas</h3>
            <canvas id="precisionChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Recall Semua Kelas</h3>
            <canvas id="recallChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>F1-Score Semua Kelas</h3>
            <canvas id="f1Chart"></canvas>
        </div>
    </div> <!-- AKHIR BAGIAN PENTING: Tutup div chart-grid di sini -->
</div>

<script>
// Data labels untuk diagram
const labels = <?php echo $labels; ?>;

/**
 * Fungsi untuk membuat diagram Doughnut.
 * @param {string} id - ID elemen canvas.
 * @param {Array} data - Array data untuk diagram.
 * @param {string} title - Judul diagram (tidak digunakan di options Chart.js, tapi bisa untuk debugging).
 */
function createDoughnut(id, data, title) {
    new Chart(document.getElementById(id), {
        type: 'doughnut',
        data: {
            labels: labels, // Menggunakan labels yang didefinisikan secara global
            datasets: [{
                data: data,
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'], // Warna untuk Rendah, Sedang, Tinggi
                borderWidth: 1
            }]
        },
        options: {
            responsive: true, // Diagram akan responsif terhadap ukuran kontainer
            maintainAspectRatio: false, // Memungkinkan diagram untuk tidak mempertahankan rasio aspek aslinya
            plugins: {
                legend: {
                    position: 'top', // Posisi legenda di atas
                    labels: {
                        font: {
                            size: 12 // Ukuran font legenda
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        // Callback untuk format tooltip menjadi persentase
                        label: ctx => `${ctx.label}: ${(ctx.raw * 100).toFixed(2)}%`
                    }
                }
            }
        }
    });
}

// Inisialisasi diagram Overall Accuracy
new Chart(document.getElementById("accuracyChart"), {
    type: 'doughnut',
    data: {
        labels: ['Akurasi', 'Error Rate'],
        datasets: [{
            data: <?php echo $accuracy_data; ?>,
            backgroundColor: ['#4caf50', '#ccc'], // Warna untuk Akurasi dan Error Rate
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    font: {
                        size: 12
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: ctx => `${ctx.label}: ${(ctx.raw * 100).toFixed(2)}%`
                }
            }
        }
    }
});

// Inisialisasi diagram Precision
createDoughnut('precisionChart', <?php echo $precision_data; ?>, 'Precision');
// Inisialisasi diagram Recall
createDoughnut('recallChart', <?php echo $recall_data; ?>, 'Recall');
// Inisialisasi diagram F1-Score
createDoughnut('f1Chart', <?php echo $f1_data; ?>, 'F1 Score');
</script>
</body>
</html>
