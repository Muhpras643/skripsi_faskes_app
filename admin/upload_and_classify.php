<?php
session_start();
include '../includes/config.php';
include '../includes/auth_check.php';
include '../includes/functions.php';

auth_check('admin');

$message = '';
$error = '';

// --- Definisi Template CSV ---
// Header kolom harus sama persis dengan yang diharapkan oleh model Python
$csv_header = "No;Unit Kerja;Kecamatan;Dr Spesialis;Dokter;Dokter Gigi;Dokter Gigi Spesialis;Tenaga Keperawatan;Tenaga Kebidanan;Tenaga Kesehatan Masyarakat;Tenaga Kesehatan Lingkungan;Tenaga Gizi;Ahli Teknologi Laboratorium Medik;Tenaga Teknik Biomedika Lainnya;Keterapian Fisik;Keteknisian Medis;Tenaga Teknis Kefarmasian;Apoteker;Pejabat Struktural;Tenaga Dukungan Manajemen;Hipertensi;Diabetes Mellitus;TBC;ISPA;Stroke;Pneumonia;Malaria;Demam Berdarah;Hepatitis;Kanker;Gagal Ginjal;Asma;Osteoarthritis";

// Beberapa baris contoh data Faskes dan Kecamatan
// Isi kolom numerik lainnya dengan 0 sebagai placeholder
// DAFTAR FASKES DAN KECAMATAN YANG LEBIH LENGKAP
$csv_sample_rows = [
    "1;Puskesmas Pondok Gede;Pondok Gede;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "2;Puskesmas Jati Makmur;Pondok Gede;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "3;Puskesmas Jati Bening;Pondok Gede;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "4;Puskesmas Jati Bening Baru;Pondok Gede;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "5;Puskesmas Jati Rahayu;Pondok Gede;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "6;Puskesmas Jati Warna;Jati Sampurna;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "7;Puskesmas Jati Sampurna;Jati Sampurna;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "8;Puskesmas Jati Karya;Jati Sampurna;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "9;Puskesmas Jati Ranggon;Jati Sampurna;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "10;Puskesmas Jati Asih;Jatiasih;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "11;Puskesmas Jati Mekar;Jatiasih;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "12;Puskesmas Jati Kramat;Jatiasih;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "13;Puskesmas Jati Luhur;Jatiasih;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "14;Puskesmas Bojong Rawalumbu;Rawalumbu;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "15;Puskesmas Pengasinan;Rawalumbu;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "16;Puskesmas Bojong Menteng;Rawalumbu;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "17;Puskesmas Karang Kitri;Bekasi Timur;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "18;Puskesmas Bekasi Jaya;Bekasi Timur;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "19;Puskesmas Aren Jaya;Bekasi Timur;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "20;Puskesmas Duren Jaya;Bekasi Timur;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "21;Puskesmas Pekayon Jaya;Bekasi Selatan;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "22;Puskesmas Jaka Mulya;Bekasi Selatan;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "23;Puskesmas Jaka Setia;Bekasi Selatan;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "24;Puskesmas Marga Jaya;Bekasi Selatan;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "25;Puskesmas Perumnas II;Bekasi Utara;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "26;Puskesmas Seroja;Bekasi Utara;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "27;Puskesmas Perwira;Bekasi Utara;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "28;Puskesmas Kali Abang Tengah;Bekasi Utara;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "29;Puskesmas Marga Mulya;Bekasi Utara;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "30;Puskesmas Teluk Pucung;Bekasi Utara;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "31;Puskesmas Harapan Baru;Bekasi Utara;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "32;Puskesmas Rawa Tembaga;Bekasi Utara;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "33;Puskesmas Bintara Jaya;Bekasi Barat;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "34;Puskesmas Bintara;Bekasi Barat;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "35;Puskesmas Kranji;Bekasi Barat;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "36;Puskesmas Kotabaru;Bekasi Barat;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "37;Puskesmas Pejuang;Medan Satria;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "38;Puskesmas Harapan Mulya;Medan Satria;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "39;Puskesmas Medan Satria;Medan Satria;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "40;Puskesmas Kalibaru;Medan Satria;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "41;Puskesmas Bantargebang;Bantargebang;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "42;Puskesmas Cikiwul;Bantargebang;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "43;Puskesmas Ciketing Udik;Bantargebang;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "44;Puskesmas Sumur Batu;Bantargebang;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "45;Puskesmas Mustika Jaya;Mustikajaya;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "46;Puskesmas Mustika Sari;Mustikajaya;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "47;Puskesmas Cimuning;Mustikajaya;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "48;Puskesmas Padurenan;Mustikajaya;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "49;RSUD Pondok Gede;Pondok Gede;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "50;RS Masmitra;Pondok Gede;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "51;RS Karunia Kasih;Pondok Gede;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "52;RS Helsa Jati Rahayu;Pondok Gede;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "53;RSUD Jati Sampurna;Jati Sampurna;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "54;RS Jati Sampurna;Jati Sampurna;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "55;RS Permata Cibubur;Jati Sampurna;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "56;RS Mitra Keluarga Cibubur;Jati Sampurna;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "57;RS Kartika Husada;Jatiasih;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "58;RS Mitra Keluarga Pratama;Jatiasih;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "59;RS Rawa Lumbu;Rawalumbu;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "60;RS St. Elisabeth;Rawalumbu;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "61;RS Siloam Bekasi Sepanjang;Rawalumbu;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "62;RS Mitra Keluarga Bekasi;Bekasi Timur;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "63;RS Primaya Bekasi Timur;Bekasi Timur;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "64;RS Bhakti Kartini;Bekasi Timur;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "65;RS Mekar Sari;Bekasi Timur;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "66;RS Bella;Bekasi Timur;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "67;RS Graha Juanda;Bekasi Timur;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "68;RS Islam Dr. Subki Abdulkadir;Bekasi Timur;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "69;RS Siloam Sentosa;Bekasi Timur;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "70;RS Juwita;Bekasi Timur;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "71;RS Siloam Bekasi Timur;Bekasi Timur;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "72;RSUD Dr. Chasbullah;Bekasi Selatan;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "73;RS Mitra Keluarga Bekasi;Bekasi Selatan;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "74;RS Hermina;Bekasi Selatan;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "75;RS Primaya Bekasi Barat;Bekasi Barat;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "76;RS Anna;Bekasi Barat;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "77;RS Hermina Galaxy;Bekasi Selatan;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "78;RS Emc Pekayon;Bekasi Selatan;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "79;RS Dokter Adam Talib;Tambun Selatan;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "80;RS Anna Medika;Bekasi Utara;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "81;RS Taman Harapan Baru;Bekasi Utara;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "82;RS Primaya Bekasi Utara;Bekasi Utara;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "83;RS Seto Hasbadi;Bekasi Utara;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "84;RSIA Rinova Intan;Bekasi Utara;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "85;RSUD Teluk Pucung;Bekasi Utara;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "86;RSIA Selasih Medika;Bekasi Utara;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "87;RS Ananda;Bekasi Utara;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "88;RS Citra Harapan;Bekasi Utara;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "89;RS Emhaka;Bekasi Utara;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "90;RSUD Bantargebang;Bantargebang;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "91;RS Karya Medika Bantargebang;Bantargebang;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "92;RS Permata Bekasi;Mustikajaya;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "93;RS Satria Medika;Mustikajaya;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
    "94;RS Mustika Medika Bekasi;Mustikajaya;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0",
];

$csv_template_content = $csv_header . "\n" . implode("\n", $csv_sample_rows);
// --- Akhir Definisi Template CSV ---


if (isset($_POST['upload_csv']) && isset($_FILES['csv_file'])) {
    $file_mimes = array(
        'text/x-comma-separated-values',
        'text/comma-separated-values',
        'application/octet-stream',
        'application/vnd.ms-excel',
        'application/x-csv',
        'text/x-csv',
        'text/csv',
        'application/csv',
        'application/excel',
        'application/vnd.msexcel'
    );

    if (in_array($_FILES['csv_file']['type'], $file_mimes)) {
        $upload_path = '../uploads/'; // Pastikan folder 'uploads' ada dan writable
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        $file_name = uniqid() . '-' . basename($_FILES['csv_file']['name']);
        $target_file = $upload_path . $file_name;

        if (move_uploaded_file($_FILES['csv_file']['tmp_name'], $target_file)) {
            $expected_features = getExpectedFeatureNames();
            $file_handle = fopen($target_file, 'r');
            $header = fgetcsv($file_handle, 0, ';');

            $header_valid = true;
            $missing_cols = [];
            foreach ($expected_features as $feature) {
                if (!in_array($feature, $header)) {
                    $missing_cols[] = $feature;
                    $header_valid = false;
                }
            }
            if (!in_array('Unit Kerja', $header)) { $missing_cols[] = 'Unit Kerja'; $header_valid = false; }
            if (!in_array('Kecamatan', $header)) { $missing_cols[] = 'Kecamatan'; $header_valid = false; }


            if (!$header_valid) {
                $error = "Error: File CSV tidak sesuai. Kolom yang hilang: " . implode(', ', $missing_cols);
            } else {
                $success_count = 0;
                $fail_count = 0;
                $row_num = 1;

                while (($row = fgetcsv($file_handle, 0, ';')) !== FALSE) {
                    $row_num++;
                    $faskes_data = [];
                    foreach ($header as $index => $col_name) {
                        $faskes_data[$col_name] = $row[$index];
                    }

                    $data_for_svm = [];
                    foreach ($expected_features as $feature_name) {
                        $data_for_svm[$feature_name] = isset($faskes_data[$feature_name]) ? (int)$faskes_data[$feature_name] : 0;
                    }

                    $unit_kerja = isset($faskes_data['Unit Kerja']) ? $faskes_data['Unit Kerja'] : 'N/A';
                    $kecamatan = isset($faskes_data['Kecamatan']) ? $faskes_data['Kecamatan'] : 'N/A';

                    $current_faskes_id = null;
                    $stmt_check = $conn->prepare("SELECT id FROM faskes_data WHERE unit_kerja = ? AND kecamatan = ?");
                    $stmt_check->bind_param("ss", $unit_kerja, $kecamatan);
                    $stmt_check->execute();
                    $stmt_check->bind_result($existing_faskes_id);
                    $stmt_check->fetch();
                    $stmt_check->close();
                    
                    $db_col_map = [
                        'Unit Kerja' => 'unit_kerja', 'Kecamatan' => 'kecamatan',
                        'Dr Spesialis' => 'dr_spesialis', 'Dokter' => 'dokter',
                        'Dokter Gigi' => 'dokter_gigi', 'Dokter Gigi Spesialis' => 'dokter_gigi_spesialis',
                        'Tenaga Keperawatan' => 'tenaga_keperawatan', 'Tenaga Kebidanan' => 'tenaga_kebidanan',
                        'Tenaga Kesehatan Masyarakat' => 'tenaga_kesehatan_masyarakat',
                        'Tenaga Kesehatan Lingkungan' => 'tenaga_kesehatan_lingkungan', 'Tenaga Gizi' => 'tenaga_gizi',
                        'Ahli Teknologi Laboratorium Medik' => 'ahli_teknologi_laboratorium_medik',
                        'Tenaga Teknik Biomedika Lainnya' => 'tenaga_teknik_biomedika_lainnya',
                        'Keterapian Fisik' => 'keterapian_fisik', 'Keteknisian Medis' => 'keteknisian_medis',
                        'Tenaga Teknis Kefarmasian' => 'tenaga_teknis_kefarmasian', 'Apoteker' => 'apoteker',
                        'Pejabat Struktural' => 'pejabat_struktural', 'Tenaga Dukungan Manajemen' => 'tenaga_dukungan_manajemen',
                        'Hipertensi' => 'hipertensi', 'Diabetes Mellitus' => 'diabetes_mellitus',
                        'TBC' => 'tbc', 'ISPA' => 'ispa', 'Stroke' => 'stroke', 'Pneumonia' => 'pneumonia',
                        'Malaria' => 'malaria', 'Demam Berdarah' => 'demam_berdarah', 'Hepatitis' => 'hepatitis',
                        'Kanker' => 'kanker', 'Gagal Ginjal' => 'gagal_ginjal', 'Asma' => 'asma', 'Osteoarthritis' => 'osteoarthritis'
                    ];

                    if ($existing_faskes_id) {
                        $current_faskes_id = $existing_faskes_id;
                        $update_cols_sql = [];
                        $bind_params = '';
                        $bind_values = [];

                        foreach ($faskes_data as $csv_col => $val) {
                            if (isset($db_col_map[$csv_col]) && $db_col_map[$csv_col] != 'unit_kerja' && $db_col_map[$csv_col] != 'kecamatan') {
                                $update_cols_sql[] = $db_col_map[$csv_col] . ' = ?';
                                $bind_params .= 'i';
                                $bind_values[] = (int)$val;
                            }
                        }
                        $bind_params .= 'ssi'; // For unit_kerja, kecamatan, id
                        $bind_values[] = $unit_kerja;
                        $bind_values[] = $kecamatan;
                        $bind_values[] = $current_faskes_id;

                        $sql_update = "UPDATE faskes_data SET " . implode(', ', $update_cols_sql) . " WHERE unit_kerja = ? AND kecamatan = ? AND id = ?";
                        $stmt_update = $conn->prepare($sql_update);
                        if (!$stmt_update) { $error .= "Prepare update failed: " . $conn->error . " at row " . $row_num . "<br>"; $fail_count++; continue; }
                        $stmt_update->bind_param($bind_params, ...$bind_values);
                        if (!$stmt_update->execute()) { $error .= "Execute update failed: " . $stmt_update->error . " at row " . $row_num . "<br>"; $fail_count++; continue; }
                        $stmt_update->close();

                    } else {
                        $insert_cols_sql = ['unit_kerja', 'kecamatan'];
                        $insert_placeholders = ['?', '?'];
                        $bind_params = 'ss';
                        $bind_values = [$unit_kerja, $kecamatan];

                        foreach ($faskes_data as $csv_col => $val) {
                            if (isset($db_col_map[$csv_col]) && $db_col_map[$csv_col] != 'unit_kerja' && $db_col_map[$csv_col] != 'kecamatan') {
                                $insert_cols_sql[] = $db_col_map[$csv_col];
                                $insert_placeholders[] = '?';
                                $bind_params .= 'i';
                                $bind_values[] = (int)$val;
                            }
                        }
                        
                        $sql_insert = "INSERT INTO faskes_data (" . implode(', ', $insert_cols_sql) . ") VALUES (" . implode(', ', $insert_placeholders) . ")";
                        $stmt_insert = $conn->prepare($sql_insert);
                        if (!$stmt_insert) { $error .= "Prepare insert failed: " . $conn->error . " at row " . $row_num . "<br>"; $fail_count++; continue; }
                        $stmt_insert->bind_param($bind_params, ...$bind_values);
                        if (!$stmt_insert->execute()) { $error .= "Execute insert failed: " . $stmt_insert->error . " at row " . $row_num . "<br>"; $fail_count++; continue; }
                        $current_faskes_id = $stmt_insert->insert_id;
                        $stmt_insert->close();
                    }

                    $classification_result = classifyFaskesData($data_for_svm);

                    if ($classification_result['status'] == 'success') {
                        $prioritas = $classification_result['data']['prioritas_wilayah'];
                        $rekomendasi = json_encode($classification_result['data']['recommendations']);

                        $stmt_insert_result = $conn->prepare("INSERT INTO classification_results (faskes_id, prioritas_wilayah, recommendations_json, classified_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE prioritas_wilayah = VALUES(prioritas_wilayah), recommendations_json = VALUES(recommendations_json), classified_at = NOW()");
                        if (!$stmt_insert_result) { $error .= "Prepare result failed: " . $conn->error . " at row " . $row_num . "<br>"; $fail_count++; continue; }
                        $stmt_insert_result->bind_param("iss", $current_faskes_id, $prioritas, $rekomendasi);
                        if (!$stmt_insert_result->execute()) { $error .= "Execute result failed: " . $stmt_insert_result->error . " at row " . $row_num . "<br>"; $fail_count++; continue; }
                        $stmt_insert_result->close();
                        $success_count++;
                    } else {
                        $error .= "Baris " . $row_num . " (Faskes: " . $unit_kerja . "): " . $classification_result['message'] . "<br>";
                        $fail_count++;
                    }
                }
                fclose($file_handle);
                $message = "Klasifikasi otomatis selesai. Berhasil: $success_count, Gagal: $fail_count.";
            }
            unlink($target_file);
        } else {
            $error = "Error saat mengunggah file.";
        }
    } else {
        $error = "Format file tidak didukung. Harap unggah file CSV.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klasifikasi Otomatis - Klasifikasi Faskes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="header">
        <h1>Sistem Klasifikasi Prioritas Fasilitas Kesehatan Kota Bekasi</h1>
        <p>Halaman Klasifikasi Otomatis (Admin)</p>
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
        <h2>Unggah CSV untuk Klasifikasi Otomatis</h2>
        <?php if ($message): ?>
            <p style="color: green;"><?php echo $message; ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <div class="template-section" style="margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;">
            <h3>Unduh Template CSV</h3>
            <p>Untuk memastikan proses klasifikasi otomatis berjalan lancar, gunakan template CSV yang telah disediakan. Pastikan kolom-kolom di file Anda sesuai dengan template ini.</p>
            <a href="data:text/csv;charset=utf-8,<?php echo rawurlencode($csv_template_content); ?>" download="template_faskes_bekasi.csv" class="button">Unduh Template CSV</a>
            <p style="font-size: 0.85em; color: #666; margin-top: 10px;">*Pastikan delimiter yang digunakan adalah titik koma (`;`).</p>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <label for="csv_file">Pilih File CSV:</label>
            <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
            <br><br>
            <button type="submit" name="upload_csv">Upload dan Klasifikasi</button>
        </form>
        <p>Pastikan file CSV memiliki semua kolom yang diharapkan oleh model (sesuai template). Jika tidak, akan muncul error.</p>
    </div>
</body>
</html>