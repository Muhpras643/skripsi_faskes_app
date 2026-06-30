<?php
// Fungsi untuk memanggil API Python (klasifikasi)
function classifyFaskesData($data) {
    $python_api_url = "http://localhost:5000/classify_faskes"; // Sesuaikan URL ini

    $ch = curl_init($python_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($response === FALSE) {
        return ['status' => 'error', 'message' => 'Gagal terhubung ke API Python: ' . $curl_error];
    } else {
        $result = json_decode($response, true);
        if ($http_status == 200 && isset($result['prioritas_wilayah'])) {
            return ['status' => 'success', 'data' => $result];
        } else {
            return ['status' => 'error', 'message' => 'Error dari API: ' . (isset($result['error']) ? $result['error'] : 'Error tidak diketahui'), 'http_status' => $http_status];
        }
    }
}

// Fungsi untuk mendapatkan nama-nama fitur yang diharapkan oleh model SVM
function getExpectedFeatureNames() {
    return [
        'Dr Spesialis', 'Dokter', 'Dokter Gigi', 'Dokter Gigi Spesialis',
        'Tenaga Keperawatan', 'Tenaga Kebidanan', 'Tenaga Kesehatan Masyarakat',
        'Tenaga Kesehatan Lingkungan', 'Tenaga Gizi', 'Ahli Teknologi Laboratorium Medik',
        'Tenaga Teknik Biomedika Lainnya', 'Keterapian Fisik', 'Keteknisian Medis',
        'Tenaga Teknis Kefarmasian', 'Apoteker', 'Pejabat Struktural', 'Tenaga Dukungan Manajemen',
        'Hipertensi', 'Diabetes Mellitus', 'TBC', 'ISPA', 'Stroke', 'Pneumonia',
        'Malaria', 'Demam Berdarah', 'Hepatitis', 'Kanker', 'Gagal Ginjal',
        'Asma', 'Osteoarthritis'
    ];
}

// Fungsi untuk memetakan nama kolom dari database (snake_case) ke format yang diharapkan Python (Title Case/CamelCase)
function mapDbRowToPythonInput($db_row) {
    $python_input = [];
    $feature_map = [
        'dr_spesialis' => 'Dr Spesialis',
        'dokter' => 'Dokter',
        'dokter_gigi' => 'Dokter Gigi',
        'dokter_gigi_spesialis' => 'Dokter Gigi Spesialis',
        'tenaga_keperawatan' => 'Tenaga Keperawatan',
        'tenaga_kebidanan' => 'Tenaga Kebidanan',
        'tenaga_kesehatan_masyarakat' => 'Tenaga Kesehatan Masyarakat',
        'tenaga_kesehatan_lingkungan' => 'Tenaga Kesehatan Lingkungan',
        'tenaga_gizi' => 'Tenaga Gizi',
        'ahli_teknologi_laboratorium_medik' => 'Ahli Teknologi Laboratorium Medik',
        'tenaga_teknik_biomedika_lainnya' => 'Tenaga Teknik Biomedika Lainnya',
        'keterapian_fisik' => 'Keterapian Fisik',
        'keteknisian_medis' => 'Keteknisian Medis',
        'tenaga_teknis_kefarmasian' => 'Tenaga Teknis Kefarmasian',
        'apoteker' => 'Apoteker',
        'pejabat_struktural' => 'Pejabat Struktural',
        'tenaga_dukungan_manajemen' => 'Tenaga Dukungan Manajemen',
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

    foreach ($feature_map as $db_col => $py_col) {
        $python_input[$py_col] = isset($db_row[$db_col]) ? (int)$db_row[$db_col] : 0;
    }
    return $python_input;
}
?>