<?php
session_start();
include '../config/koneksi.php';
include '../config/security.php';
include '../config/db_helper.php';

header('Content-Type: application/json');

// Cek login mahasiswa
require_login();
require_role('mahasiswa');

// Ambil data
$npm_mahasiswa = $_SESSION['user'];
$data_json = json_decode(file_get_contents('php://input'), true);
$qr_content = sanitize_input($data_json['qr_content'] ?? ''); 

// Cari ID mahasiswa
$mhs = db_fetch($conn, "SELECT id_mahasiswa FROM mahasiswa WHERE npm = ?", 's', [$npm_mahasiswa]);
$id_mahasiswa = $mhs['id_mahasiswa'];

// Validasi QR dengan time-based token
// Format: "id_dosen|timestamp"
if (strpos($qr_content, '|') !== false) {
    list($id_dosen_dari_qr, $qr_timestamp) = explode('|', $qr_content, 2);
    
    // Validasi timestamp (terima dalam range ±10 menit)
    $current_timestamp = floor(time() / (5 * 60));
    $time_diff = abs($current_timestamp - intval($qr_timestamp));
    
    if ($time_diff > 2) {
        echo json_encode(['status' => 'error', 'message' => 'QR Code sudah kedaluwarsa. Minta dosen refresh QR.']);
        exit;
    }
} else {
    $id_dosen_dari_qr = $qr_content;
}

$result = db_fetch($conn,
    "SELECT * FROM antrian 
     WHERE id_mahasiswa = ? 
     AND id_dosen = ? 
     AND tanggal = CURDATE()
     AND status IN ('menunggu', 'dipanggil')",
    'is',
    [$id_mahasiswa, $id_dosen_dari_qr]
);

if ($result) {
    $id_antrian = $result['id_antrian'];

    // Update status ke 'proses'
    $updateData = [
        'status' => 'proses',
        'waktu_kehadiran' => date('Y-m-d H:i:s')
    ];
    
    $updated = db_update($conn, 'antrian', $updateData, 'id_antrian', $id_antrian);

    if ($updated !== false) {
        // Buat notifikasi otomatis
        include_once 'notification_helper.php';
        notifyMahasiswaStatusChange($conn, $id_antrian, 'proses');
        
        echo json_encode(['status' => 'success', 'message' => 'Presensi Berhasil!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update database']);
    }
} else {
    // Check if mahasiswa has booking with this dosen on different date
    $checkOtherDate = db_fetch($conn,
        "SELECT tanggal, status FROM antrian 
         WHERE id_mahasiswa = ? 
         AND id_dosen = ? 
         AND status IN ('menunggu', 'dipanggil') 
         ORDER BY tanggal ASC LIMIT 1",
        'is',
        [$id_mahasiswa, $id_dosen_dari_qr]
    );
    
    if ($checkOtherDate) {
        $formatted_date = date('d/m/Y', strtotime($checkOtherDate['tanggal']));
        echo json_encode([
            'status' => 'error', 
            'message' => "Booking Anda dengan dosen ini dijadwalkan tanggal {$formatted_date}, bukan hari ini. QR Code hanya bisa di-scan pada tanggal booking yang sesuai."
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Tidak ada jadwal bimbingan hari ini dengan dosen tersebut. Pastikan Anda sudah booking dan statusnya menunggu/dipanggil.'
        ]);
    }
}
?>