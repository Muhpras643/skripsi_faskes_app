<?php
session_start();
include '../includes/config.php';
include '../includes/auth_check.php';
include '../includes/functions.php';

auth_check('admin');

$message = '';
$error = '';

// Definisikan semua kolom tenaga medis dan penyakit untuk mapping dan dropdown
$medical_personnel_types = [
    'dr_spesialis' => 'Dr Spesialis',
    'dokter' => 'Dokter Umum',
    'dokter_gigi' => 'Dokter Gigi',
    'dokter_gigi_spesialis' => 'Dr Gigi Spesialis',
    'tenaga_keperawatan' => 'Perawat',
    'tenaga_kebidanan' => 'Bidan',
    'tenaga_kesehatan_masyarakat' => 'Kesmas',
    'tenaga_kesehatan_lingkungan' => 'Kesling',
    'tenaga_gizi' => 'Gizi',
    'ahli_teknologi_laboratorium_medik' => 'ATLM',
    'tenaga_teknik_biomedika_lainnya' => 'Teknik Biomedika Lainnya',
    'keterapian_fisik' => 'Fisioterapis',
    'keteknisian_medis' => 'Teknisi Medis',
    'tenaga_teknis_kefarmasian' => 'Teknis Kefarmasian',
    'apoteker' => 'Apoteker',
    'pejabat_struktural' => 'Pejabat Struktural',
    'tenaga_dukungan_manajemen' => 'Dukungan Manajemen'
];

$disease_types = [
    'hipertensi' => 'Hipertensi',
    'diabetes_mellitus' => 'Diabetes Mellitus',
    'tbc' => 'TBC',
    'ispa' => 'ISPA',
    'stroke' => 'Stroke',
    'pneumonia' => 'Pneumonia',
    'malaria' => 'Malaria',
    'demam_berdarah' => 'Demam Berdarah',
    'hepatitis' => 'Hepatitis',
    'kanker' => 'Kanker',
    'gagal_ginjal' => 'Gagal Ginjal',
    'asma' => 'Asma',
    'osteoarthritis' => 'Osteoarthritis'
];

// Rentang nilai untuk dropdown jumlah
$max_personnel_value = 50; 
$max_disease_value = 1000;
$disease_step = 5;

// Handle Add/Edit Faskes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_faskes'])) {
    $faskes_id = $_POST['faskes_id'] ?? null;
    $unit_kerja = $_POST['unit_kerja'];
    $kecamatan = $_POST['kecamatan'];

    // Inisialisasi semua fitur ke 0 terlebih dahulu (sesuai kolom DB)
    $feature_data_for_db = [];
    foreach (array_keys($medical_personnel_types) as $col) {
        $feature_data_for_db[$col] = 0;
    }
    foreach (array_keys($disease_types) as $col) {
        $feature_data_for_db[$col] = 0;
    }

    // Proses data tenaga medis dari input dinamis
    if (isset($_POST['personnel_type']) && is_array($_POST['personnel_type'])) {
        foreach ($_POST['personnel_type'] as $key => $type) {
            $quantity = (int)($_POST['personnel_quantity'][$key] ?? 0);
            if (array_key_exists($type, $medical_personnel_types)) {
                $feature_data_for_db[$type] += $quantity;
            }
        }
    }

    // Proses data penyakit dari input dinamis
    if (isset($_POST['disease_type']) && is_array($_POST['disease_type'])) {
        foreach ($_POST['disease_type'] as $key => $type) {
            $quantity = (int)($_POST['disease_quantity'][$key] ?? 0);
            if (array_key_exists($type, $disease_types)) {
                $feature_data_for_db[$type] += $quantity;
            }
        }
    }

    // Siapkan kolom dan nilai untuk SQL
    $sql_cols = ['unit_kerja', 'kecamatan'];
    $sql_placeholders = ['?', '?'];
    $bind_types = 'ss';
    $bind_values = [$unit_kerja, $kecamatan];

    foreach ($feature_data_for_db as $col_name => $value) {
        $sql_cols[] = $col_name;
        $sql_placeholders[] = '?';
        $bind_types .= 'i';
        $bind_values[] = $value;
    }

    if ($faskes_id) { // Edit existing
        $update_parts = [];
        $bind_types_update = '';
        $bind_values_update = [];
        
        // Ambil data faskes yang ada terlebih dahulu
        $stmt_current_data = $conn->prepare("SELECT * FROM faskes_data WHERE id = ?");
        $stmt_current_data->bind_param("i", $faskes_id);
        $stmt_current_data->execute();
        $current_data_result = $stmt_current_data->get_result();
        $current_faskes_data = $current_data_result->fetch_assoc();
        $stmt_current_data->close();
        
        // Gabungkan data lama dengan data baru
        $updated_data = $current_faskes_data;
        foreach ($feature_data_for_db as $key => $value) {
            $updated_data[$key] = $value; // Mengganti nilai lama dengan yang baru
        }
        
        $update_parts[] = 'unit_kerja = ?';
        $update_parts[] = 'kecamatan = ?';
        $bind_types_update .= 'ss';
        $bind_values_update[] = $unit_kerja;
        $bind_values_update[] = $kecamatan;

        foreach (array_keys($medical_personnel_types) as $col) {
            $update_parts[] = $col . ' = ?';
            $bind_types_update .= 'i';
            $bind_values_update[] = $updated_data[$col];
        }
        foreach (array_keys($disease_types) as $col) {
            $update_parts[] = $col . ' = ?';
            $bind_types_update .= 'i';
            $bind_values_update[] = $updated_data[$col];
        }
        
        $sql = "UPDATE faskes_data SET " . implode(', ', $update_parts) . " WHERE id = ?";
        $bind_types_update .= 'i';
        $bind_values_update[] = $faskes_id;

        $stmt = $conn->prepare($sql);
        if (!$stmt) { $error = "Prepare failed (update): " . $conn->error; }
        else {
            $stmt->bind_param($bind_types_update, ...$bind_values_update);
            if ($stmt->execute()) {
                $message = "Data Faskes berhasil diperbarui!";
            } else {
                $error = "Gagal memperbarui data Faskes: " . $stmt->error;
            }
            $stmt->close();
        }
    } else { // Add new
        $sql = "INSERT INTO faskes_data (" . implode(', ', $sql_cols) . ") VALUES (" . implode(', ', $sql_placeholders) . ")";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { $error = "Prepare failed (insert): " . $conn->error; }
        else {
            $stmt->bind_param($bind_types, ...$bind_values);
            if ($stmt->execute()) {
                $message = "Data Faskes berhasil ditambahkan!";
            } else {
                $error = "Gagal menambahkan data Faskes: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Handle Delete Faskes
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_to_delete = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM faskes_data WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);
    if ($stmt->execute()) {
        $message = "Data Faskes berhasil dihapus!";
    } else {
        $error = "Gagal menghapus data Faskes: " . $stmt->error;
    }
    $stmt->close();
}

// Handle Classify Single Faskes
if (isset($_GET['action']) && $_GET['action'] == 'classify_single' && isset($_GET['id'])) {
    $id_to_classify = (int)$_GET['id'];
    $stmt_faskes = $conn->prepare("SELECT * FROM faskes_data WHERE id = ?");
    if (!$stmt_faskes) {
        $error = "Prepare failed (select faskes for classify): " . $conn->error;
        goto end_classify_single_process;
    }
    $stmt_faskes->bind_param("i", $id_to_classify);
    if (!$stmt_faskes->execute()) {
        $error = "Execute failed (select faskes for classify): " . $stmt_faskes->error;
        $stmt_faskes->close();
        goto end_classify_single_process;
    }
    $result_faskes = $stmt_faskes->get_result();
    $faskes_row = $result_faskes->fetch_assoc();
    $stmt_faskes->close();

    if ($faskes_row) {
        $data_for_svm = mapDbRowToPythonInput($faskes_row);
        $classification_result = classifyFaskesData($data_for_svm);

        if ($classification_result['status'] == 'success') {
            $prioritas = $classification_result['data']['prioritas_wilayah'];
            $rekomendasi = json_encode($classification_result['data']['recommendations']);

            $stmt_insert_result = $conn->prepare("INSERT INTO classification_results (faskes_id, prioritas_wilayah, recommendations_json, classified_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE prioritas_wilayah = VALUES(prioritas_wilayah), recommendations_json = VALUES(recommendations_json), classified_at = NOW()");
            if (!$stmt_insert_result) { 
                $error = "Prepare result failed: " . $conn->error; 
                goto end_classify_single_process;
            }
            $stmt_insert_result->bind_param("iss", $id_to_classify, $prioritas, $rekomendasi);
            if (!$stmt_insert_result->execute()) { 
                $error = "Execute result failed: " . $stmt_insert_result->error; 
                $stmt_insert_result->close();
                goto end_classify_single_process;
            }
            $stmt_insert_result->close();
            $message = "Faskes '" . htmlspecialchars($faskes_row['unit_kerja']) . "' berhasil diklasifikasi ulang!";
        } else {
            $error = "Gagal mengklasifikasi Faskes '" . htmlspecialchars($faskes_row['unit_kerja']) . "': " . $classification_result['message'];
        }
    } else {
        $error = "Faskes tidak ditemukan untuk klasifikasi.";
    }
}
end_classify_single_process: // Label untuk goto

// Fetch Faskes Data for display (MENGAMBIL SEMUA KOLOM)
$faskes_list = [];
$result = $conn->query("SELECT * FROM faskes_data ORDER BY kecamatan, unit_kerja");
while ($row = $result->fetch_assoc()) {
    $faskes_list[] = $row;
}

// Get Faskes data for edit form if 'edit' action is requested
$faskes_to_edit = null;
$initial_personnel_data = []; // Data untuk mengisi form dinamis di mode edit
$initial_disease_data = [];   // Data untuk mengisi form dinamis di mode edit

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_to_edit = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM faskes_data WHERE id = ?");
    $stmt->bind_param("i", $id_to_edit);
    $stmt->execute();
    $result_edit = $stmt->get_result();
    $faskes_to_edit = $result_edit->fetch_assoc();
    $stmt->close();

    if ($faskes_to_edit) {
        // Prepare initial personnel data for JS
        foreach ($medical_personnel_types as $db_col => $label) {
            if (isset($faskes_to_edit[$db_col]) && $faskes_to_edit[$db_col] > 0) {
                $initial_personnel_data[] = ['type' => $db_col, 'quantity' => (int)$faskes_to_edit[$db_col]];
            }
        }
        // Prepare initial disease data for JS
        foreach ($disease_types as $db_col => $label) {
            if (isset($faskes_to_edit[$db_col]) && $faskes_to_edit[$db_col] > 0) {
                $initial_disease_data[] = ['type' => $db_col, 'quantity' => (int)$faskes_to_edit[$db_col]];
            }
        }
    }
}

// Definisikan semua kolom tenaga medis dan penyakit untuk header tabel
$medical_personnel_cols_display = $medical_personnel_types; // Menggunakan array yang sama
$disease_cols_display = $disease_types; // Menggunakan array yang sama

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Faskes - Klasifikasi Faskes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/script.js"></script>
    <style>
        /* Gaya khusus untuk tabel yang sangat lebar */
        .table-responsive-wrapper {
            overflow-x: auto; /* Membuat tabel bisa di-scroll horizontal */
            width: 100%;
            margin-top: 20px;
        }
        .data-table-full-width {
            min-width: 1500px; /* Lebar minimum tabel agar semua kolom terlihat */
        }
        .data-table-full-width th, .data-table-full-width td {
            white-space: nowrap; /* Mencegah teks wrap */
            padding: 6px 8px; /* Padding lebih kecil */
            font-size: 0.85em; /* Ukuran font lebih kecil */
        }
        .data-table-full-width th:first-child,
        .data-table-full-width td:first-child {
            position: sticky;
            left: 0;
            background-color: #f2f2f2; /* Background untuk kolom sticky */
            z-index: 10;
        }
        .data-table-full-width td:first-child {
            background-color: white;
        }
        /* Gaya untuk elemen select baru */
        form select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white; /* Pastikan background putih */
            cursor: pointer;
        }
        form select:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
            outline: none;
        }

        /* Gaya untuk input dinamis */
        .dynamic-input-group {
            display: flex; /* Menggunakan flexbox untuk tata letak */
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        .dynamic-input-group select,
        .dynamic-input-group input[type="number"] {
            flex: 1; /* Ambil ruang yang tersedia */
            margin-bottom: 0; /* Hapus margin bawah default */
            min-width: 80px; /* Minimum width for select/input */
        }
        .dynamic-input-group select:first-child {
            flex: 2; /* Beri dropdown jenis lebih banyak ruang */
        }
        .dynamic-input-group button {
            flex-shrink: 0; /* Jangan menyusut */
            padding: 8px 12px;
            margin-top: 0; /* Hapus margin atas default */
            background-color: #dc3545; /* Merah untuk hapus */
            font-size: 0.8em; /* Perkecil font tombol hapus */
        }
        .dynamic-input-group button:hover {
            background-color: #c82333;
        }
        .add-row-button {
            background-color: #28a745; /* Hijau untuk tambah */
            margin-top: 15px;
            margin-bottom: 20px;
            width: auto; /* Biarkan tombol tambah menyesuaikan konten */
            padding: 10px 15px;
        }
        .add-row-button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistem Klasifikasi Prioritas Fasilitas Kesehatan Kota Bekasi</h1>
        <p>Manajemen Data Fasilitas Kesehatan (Admin)</p>
        <a href="../logout.php">Logout</a>
    </div>
    <div class="navbar">
        <a href="dashboard.php">Dashboard</a>
    <a href="manage_faskes.php">Manajemen Faskes</a>
    <a href="upload_and_classify.php">Klasifikasi Otomatis</a>
    <a href="view_results.php">Hasil Klasifikasi</a>
    <a href="form_event.php">Test Skill</a>
    </div>
    <div class="container">
        <h2><?php echo $faskes_to_edit ? 'Edit Data Faskes' : 'Tambah Data Faskes Baru'; ?></h2>
        <?php if ($message): ?>
            <p style="color: green;"><?php echo $message; ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="faskes_id" value="<?php echo htmlspecialchars($faskes_to_edit['id'] ?? ''); ?>">

            <label for="unit_kerja">Unit Kerja:</label>
            <input type="text" name="unit_kerja" id="unit_kerja" value="<?php echo htmlspecialchars($faskes_to_edit['unit_kerja'] ?? ''); ?>" required>

            <label for="kecamatan">Kecamatan:</label>
            <input type="text" name="kecamatan" id="kecamatan" value="<?php echo htmlspecialchars($faskes_to_edit['kecamatan'] ?? ''); ?>" required>
            
            <h3>Data Tenaga Medis:</h3>
            <div id="personnel-inputs-container">
                <!-- Dynamic personnel inputs will be added here by JavaScript -->
            </div>
            <button type="button" class="button add-row-button" onclick="addDynamicInput('personnel')">Tambah Tenaga Medis</button>

            <h3>Data Kasus Penyakit:</h3>
            <div id="disease-inputs-container">
                <!-- Dynamic disease inputs will be added here by JavaScript -->
            </div>
            <button type="button" class="button add-row-button" onclick="addDynamicInput('disease')">Tambah Kasus Penyakit</button>

            <button type="submit" name="submit_faskes"><?php echo $faskes_to_edit ? 'Perbarui Data' : 'Tambah Data'; ?></button>
        </form>

        <hr>

        <h2>Daftar Faskes</h2>
        <div class="table-responsive-wrapper">
            <table class="data-table-full-width">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Unit Kerja</th>
                        <th>Kecamatan</th>
                        <?php foreach ($medical_personnel_types as $db_col => $label): ?>
                            <th><?php echo $label; ?></th>
                        <?php endforeach; ?>
                        <?php foreach ($disease_types as $db_col => $label): ?>
                            <th><?php echo $label; ?></th>
                        <?php endforeach; ?>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($faskes_list)): ?>
                        <tr><td colspan="<?php echo 3 + count($medical_personnel_types) + count($disease_types) + 1; ?>">Tidak ada data fasilitas kesehatan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($faskes_list as $faskes): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($faskes['id']); ?></td>
                                <td><?php echo htmlspecialchars($faskes['unit_kerja']); ?></td>
                                <td><?php echo htmlspecialchars($faskes['kecamatan']); ?></td>
                                <?php foreach ($medical_personnel_types as $db_col => $label): ?>
                                    <td><?php echo htmlspecialchars($faskes[$db_col] ?? 0); ?></td>
                                <?php endforeach; ?>
                                <?php foreach ($disease_types as $db_col => $label): ?>
                                    <td><?php echo htmlspecialchars($faskes[$db_col] ?? 0); ?></td>
                                <?php endforeach; ?>
                                <td class="action-links">
                                    <a href="manage_faskes.php?action=edit&id=<?php echo $faskes['id']; ?>">Edit</a> |
                                    <a href="manage_faskes.php?action=delete&id=<?php echo $faskes['id']; ?>" onclick="return confirmDelete('Yakin ingin menghapus data ini? Ini juga akan menghapus hasil klasifikasinya.');">Hapus</a> |
                                    <a href="manage_faskes.php?action=classify_single&id=<?php echo $faskes['id']; ?>">Klasifikasi Ulang</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const medicalPersonnelTypes = <?php echo json_encode($medical_personnel_types); ?>;
        const diseaseTypes = <?php echo json_encode($disease_types); ?>;
        const maxPersonnelValue = <?php echo $max_personnel_value; ?>;
        const maxDiseaseValue = <?php echo $max_disease_value; ?>;
        const diseaseStep = <?php echo $disease_step; ?>;

        // Data awal untuk mode edit
        const initialPersonnelData = <?php echo json_encode($initial_personnel_data); ?>;
        const initialDiseaseData = <?php echo json_encode($initial_disease_data); ?>;

        function populateDropdown(selectElement, type) {
            let options = '<option value="">-- Pilih --</option>';
            const typesMap = type === 'personnel' ? medicalPersonnelTypes : diseaseTypes;
            for (const dbCol in typesMap) {
                options += `<option value="${dbCol}">${typesMap[dbCol]}</option>`;
            }
            selectElement.innerHTML = options;
        }

        function createQuantityInput(type, initialValue = 0) {
            const input = document.createElement('input');
            input.type = 'number';
            input.name = `${type}_quantity[]`;
            input.required = true;
            input.min = "0";
            if (type === 'disease') {
                input.step = diseaseStep.toString();
            }
            input.value = initialValue;
            return input;
        }

        function addDynamicInput(type, initialType = '', initialQuantity = 0) {
            const container = document.getElementById(`${type}-inputs-container`);
            const div = document.createElement('div');
            div.classList.add('dynamic-input-group');

            const typeSelect = document.createElement('select');
            typeSelect.name = `${type}_type[]`;
            typeSelect.required = true;
            populateDropdown(typeSelect, type);
            if (initialType) {
                typeSelect.value = initialType;
            }

            const quantityInput = createQuantityInput(type, initialQuantity);

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.textContent = 'Hapus';
            removeButton.onclick = function() {
                div.remove();
            };

            div.appendChild(typeSelect);
            div.appendChild(quantityInput);
            div.appendChild(removeButton);
            container.appendChild(div);
        }

        // Initialize form for edit mode or add initial empty rows
        document.addEventListener('DOMContentLoaded', function() {
            // Jika ada data awal (mode edit), tampilkan data tersebut
            if (initialPersonnelData.length > 0) {
                initialPersonnelData.forEach(item => {
                    addDynamicInput('personnel', item.type, item.quantity);
                });
            } else {
                // Jika tidak ada data awal (mode tambah), tambahkan satu baris kosong
                addDynamicInput('personnel'); 
            }

            if (initialDiseaseData.length > 0) {
                initialDiseaseData.forEach(item => {
                    addDynamicInput('disease', item.type, item.quantity);
                });
            } else {
                addDynamicInput('disease'); 
            }
        });
    </script>
</body>
</html>