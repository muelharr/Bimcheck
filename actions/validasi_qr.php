<?php
session_start();
include '../config/koneksi.php';

header('Content-Type: application/json');

// 1. Cek Login
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'mahasiswa') {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
    exit;
}

// 2. Ambil Data
$npm_mahasiswa = $_SESSION['user'];
$data_json = json_decode(file_get_contents('php://input'), true);
$qr_content = $data_json['qr_content'] ?? ''; 

// 3. Cari ID Mahasiswa
$qMhs = mysqli_query($conn, "SELECT id_mahasiswa FROM mahasiswa WHERE npm = '$npm_mahasiswa'");
$mhs = mysqli_fetch_assoc($qMhs);
$id_mahasiswa = $mhs['id_mahasiswa'];

// 4. LOGIKA VALIDASI
// Kita cek: Apakah mahasiswa ini punya jadwal 'menunggu' atau 'dipanggil' HARI INI?
// Dan apakah QR yang discan cocok dengan ID Dosen di jadwal tersebut?

// Asumsi: Isi QR Code adalah ID Dosen (contoh: "1" atau "2")
// Kita bersihkan dulu datanya biar aman
$id_dosen_dari_qr = mysqli_real_escape_string($conn, $qr_content); 
$tanggal_hari_ini = date('Y-m-d');

$queryCek = "SELECT * FROM antrian 
             WHERE id_mahasiswa = '$id_mahasiswa' 
             AND id_dosen = '$id_dosen_dari_qr' 
             AND tanggal = '$tanggal_hari_ini'
             AND status IN ('menunggu', 'dipanggil')";

$result = mysqli_query($conn, $queryCek);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $id_antrian = $row['id_antrian'];

    // 5. UPDATE DATABASE
    // Ubah status jadi 'proses' dan catat waktu saat ini
    $update = mysqli_query($conn, "UPDATE antrian SET status = 'proses', waktu_kehadiran = NOW() WHERE id_antrian = '$id_antrian'");

    if ($update) {
        echo json_encode(['status' => 'success', 'message' => 'Presensi Berhasil! Status antrian Anda sekarang: PROSES.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update database.']);
    }
} else {
    // Pesan error detail
    echo json_encode(['status' => 'error', 'message' => 'Jadwal tidak ditemukan! Pastikan Anda punya jadwal hari ini dengan dosen tersebut.']);
}
?>