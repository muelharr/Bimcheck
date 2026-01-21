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

// 4. LOGIKA VALIDASI DENGAN TIME-BASED TOKEN
// Format QR: "id_dosen|timestamp" dimana timestamp adalah blok 5 menit
// Contoh: "4|12345678" 

$qr_content_cleaned = mysqli_real_escape_string($conn, $qr_content);
$tanggal_hari_ini = date('Y-m-d');

// Parse QR Token
if (strpos($qr_content_cleaned, '|') !== false) {
    // Format baru: id_dosen|timestamp
    list($id_dosen_dari_qr, $qr_timestamp) = explode('|', $qr_content_cleaned, 2);
    
    // Validasi timestamp: Terima token dalam range ±2 blok (±10 menit)
    $current_timestamp = floor(time() / (5 * 60)); // Current 5-minute block
    $time_diff = abs($current_timestamp - intval($qr_timestamp));
    
    if ($time_diff > 2) {
        echo json_encode(['status' => 'error', 'message' => 'QR Code sudah kedaluwarsa. Minta dosen refresh QR Code.']);
        exit;
    }
} else {
    // Format lama (backward compatibility): hanya id_dosen
    $id_dosen_dari_qr = $qr_content_cleaned;
}

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