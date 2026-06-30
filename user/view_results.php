<?php
session_start();
include '../includes/config.php';
include '../includes/auth_check.php';
include '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$selected_kecamatan = isset($_GET['kecamatan']) ? $_GET['kecamatan'] : '';

$kecamatan_list = [];
$stmt_kecamatan = $conn->prepare("SELECT DISTINCT kecamatan FROM faskes_data ORDER BY kecamatan");
if (!$stmt_kecamatan) { die("Prepare failed: " . $conn->error); }
$stmt_kecamatan->execute();
$result_kecamatan = $stmt_kecamatan->get_result();
while ($row = $result_kecamatan->fetch_assoc()) {
    $kecamatan_list[] = $row['kecamatan'];
}
$stmt_kecamatan->close();


$sql = "SELECT fd.id, fd.unit_kerja, fd.kecamatan, cr.prioritas_wilayah, cr.recommendations_json 
        FROM faskes_data fd
        JOIN classification_results cr ON fd.id = cr.faskes_id";

$params = [];
$types = "";

if (!empty($selected_kecamatan)) {
    $sql .= " WHERE fd.kecamatan = ?";
    $params[] = $selected_kecamatan;
    $types .= "s";
}
$sql .= " ORDER BY fd.kecamatan, fd.unit_kerja";

$stmt = $conn->prepare($sql);
if (!$stmt) { die("Prepare failed: " . $conn->error); }
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$faskes_results = [];
while ($row = $result->fetch_assoc()) {
    $faskes_results[] = $row;
}
$stmt->close();

// Konversi hasil faskes ke JSON untuk digunakan di JavaScript
$faskes_results_json = json_encode($faskes_results);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Hasil Klasifikasi - Klasifikasi Faskes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* CSS untuk Modal */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1001; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.6); /* Black w/ opacity */
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; /* 5% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
            max-width: 800px; /* Max width for larger screens */
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
        }

        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            right: 20px;
            top: 10px;
        }

        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        
        .modal-content table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .modal-content th, .modal-content td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .modal-content th {
            background-color: #f2f2f2;
        }

        /* Gaya untuk teks "Lihat Selengkapnya" */
        .view-details-text {
            color: #007bff; /* Warna biru seperti tautan */
            cursor: pointer; /* Menunjukkan bisa diklik */
            text-decoration: underline; /* Menunjukkan ini adalah tautan */
        }
        .view-details-text:hover {
            color: #0056b3; /* Warna biru lebih gelap saat hover */
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistem Klasifikasi Prioritas Fasilitas Kesehatan Kota Bekasi</h1>
        <p>Halaman Hasil Klasifikasi (Pengguna)</p>
        <a href="../logout.php">Logout</a>
    </div>
    <div class="navbar">
        <a href="dashboard.php">Dashboard</a>
        <a href="view_results.php">Lihat Hasil Klasifikasi</a>
    </div>
    <div class="container">
        <h2>Hasil Klasifikasi Fasilitas Kesehatan</h2>

        <form method="GET" action="view_results.php">
            <label for="kecamatan_filter">Filter per Kecamatan:</label>
            <select name="kecamatan" id="kecamatan_filter" onchange="this.form.submit()">
                <option value="">-- Semua Kecamatan --</option>
                <?php foreach ($kecamatan_list as $kec): ?>
                    <option value="<?php echo htmlspecialchars($kec); ?>" <?php echo ($selected_kecamatan == $kec) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($kec); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <br>

        <?php if (empty($faskes_results)): ?>
            <p>Tidak ada data klasifikasi yang ditemukan untuk filter ini.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Unit Kerja</th>
                        <th>Kecamatan</th>
                        <th>Prioritas</th>
                        <th>Detail Rekomendasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($faskes_results as $faskes): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($faskes['unit_kerja']); ?></td>
                            <td><?php echo htmlspecialchars($faskes['kecamatan']); ?></td>
                            <td class="<?php echo strtolower($faskes['prioritas_wilayah']); ?>">
                                <?php echo htmlspecialchars($faskes['prioritas_wilayah']); ?>
                            </td>
                            <td>
                                <?php
                                $recommendations_data = json_decode($faskes['recommendations_json'], true);
                                $general_messages = [];
                                $specific_recommendations_exist = false;

                                if (!empty($recommendations_data)) {
                                    foreach ($recommendations_data as $rec) {
                                        if ($rec['type'] == 'summary' || $rec['type'] == 'advice') {
                                            $general_messages[] = htmlspecialchars($rec['message']);
                                        } elseif ($rec['type'] == 'specific_recommendation') {
                                            $specific_recommendations_exist = true;
                                        }
                                    }
                                }

                                if (!empty($general_messages)) {
                                    echo "<ul>";
                                    foreach ($general_messages as $msg) {
                                        echo "<li>" . $msg . "</li>";
                                    }
                                    echo "</ul>";
                                }

                                if ($specific_recommendations_exist) {
                                    echo '<span class="view-details-text" data-faskes-id="' . htmlspecialchars($faskes['id']) . '">Lihat Selengkapnya</span>';
                                } else {
                                    if (empty($general_messages)) {
                                        if ($faskes['prioritas_wilayah'] == 'Rendah') {
                                            echo "Tidak ada masalah spesifik teridentifikasi. Pertahankan standar pelayanan.";
                                        } elseif ($faskes['prioritas_wilayah'] == 'Sedang') {
                                            echo "Tidak ada rekomendasi spesifik yang mendesak. Lakukan pemantauan rutin dan alokasi sumber daya yang efisien.";
                                        } else { 
                                            echo "Prioritas Tinggi, namun rekomendasi spesifik lebih lanjut belum tersedia. Perlu analisis manual.";
                                        }
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- The Modal HTML Structure -->
    <div id="recommendationModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h3 id="modalTitle">Detail Rekomendasi untuk Faskes: <span id="faskesUnitKerja"></span></h3>
            <div id="modalRecommendationsContent">
                <!-- Specific recommendations table will be loaded here by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        // Store PHP data in JavaScript
        const faskesData = <?php echo $faskes_results_json; ?>;
        const faskesMap = new Map();
        faskesData.forEach(faskes => {
            faskesMap.set(faskes.id.toString(), faskes);
        });

        // Get the modal elements
        const modal = document.getElementById("recommendationModal");
        const closeButton = document.getElementsByClassName("close-button")[0];
        const modalTitle = document.getElementById("modalTitle");
        const faskesUnitKerjaSpan = document.getElementById("faskesUnitKerja");
        const modalContentDiv = document.getElementById("modalRecommendationsContent");

        // When the user clicks on the text, open the modal 
        document.querySelectorAll('.view-details-text').forEach(textElement => {
            textElement.addEventListener('click', function() {
                const faskesId = this.dataset.faskesId;
                const faskes = faskesMap.get(faskesId);

                if (faskes) {
                    faskesUnitKerjaSpan.textContent = faskes.unit_kerja + ' (' + faskes.kecamatan + ')';
                    const recommendations_data = JSON.parse(faskes.recommendations_json);
                    
                    let specificTableHtml = '';
                    let generalListHtml = '';

                    if (recommendations_data && recommendations_data.length > 0) {
                        generalListHtml += '<ul>';
                        specificTableHtml += '<h4 style="margin-top:10px; margin-bottom:5px;">Rekomendasi Spesifik:</h4>';
                        specificTableHtml += '<table style="width:100%; font-size:0.9em; border-collapse: collapse;">';
                        specificTableHtml += '<thead><tr>';
                        specificTableHtml += '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Kategori</th>';
                        specificTableHtml += '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Item</th>';
                        specificTableHtml += '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Aksi / Kebutuhan</th>';
                        specificTableHtml += '</tr></thead><tbody>';

                        recommendations_data.forEach(rec => {
                            if (rec.type === 'summary' || rec.type === 'advice') {
                                generalListHtml += '<li>' + rec.message + '</li>';
                            } else if (rec.type === 'specific_recommendation') {
                                let actionOutput = rec.action;
                                if (rec.value !== undefined && rec.value !== null && rec.value !== '') {
                                    actionOutput += ' ' + rec.value;
                                    if (rec.unit) {
                                        actionOutput += ' ' + rec.unit;
                                    }
                                }
                                specificTableHtml += '<tr>';
                                specificTableHtml += '<td style="border: 1px solid #ddd; padding: 8px;">' + rec.category + '</td>';
                                specificTableHtml += '<td style="border: 1px solid #ddd; padding: 8px;">' + rec.item + '</td>';
                                specificTableHtml += '<td style="border: 1px solid #ddd; padding: 8px;">' + actionOutput + '</td>';
                                specificTableHtml += '</tr>';
                            }
                        });
                        generalListHtml += '</ul>';
                        specificTableHtml += '</tbody></table>';
                    } else {
                        generalListHtml = '<p>Tidak ada rekomendasi spesifik yang tersedia.</p>';
                    }
                    
                    modalContentDiv.innerHTML = generalListHtml + specificTableHtml;
                    modal.style.display = "block";
                }
            });
        });

        // When the user clicks on <span> (x), close the modal
        closeButton.onclick = function() {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>