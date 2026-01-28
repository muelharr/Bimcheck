<?php
session_start();
include '../config/koneksi.php';

// Cek login dosen
if (!isset($_SESSION['status']) || $_SESSION['status'] != 'login' || $_SESSION['role'] != 'dosen') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id_antrian = intval($data['id_antrian'] ?? 0);
$action = mysqli_real_escape_string($conn, $data['action'] ?? '');
$catatan = mysqli_real_escape_string($conn, $data['catatan'] ?? '');

if (!$id_antrian) {
    echo json_encode(['status' => 'error', 'message' => 'ID antrian tidak valid']);
    exit;
}

$status_baru = '';

if ($action == 'panggil') {
    $status_baru = 'dipanggil';
} elseif ($action == 'selesai') {
    $status_baru = 'selesai';
}

if ($status_baru) {
    // Get status lama
    $qCheck = mysqli_query($conn, "SELECT status FROM antrian WHERE id_antrian = $id_antrian");
    $antrian = mysqli_fetch_assoc($qCheck);
    $status_lama = $antrian['status'] ?? null;
    
    // Update antrian dengan waktu_panggil atau waktu_selesai
    $updateQuery = "UPDATE antrian SET status = '$status_baru'";
    
    if ($status_baru == 'dipanggil') {
        $updateQuery .= ", waktu_panggil = NOW()";
    } elseif ($status_baru == 'selesai') {
        $updateQuery .= ", waktu_selesai = NOW()";
    }
    
    // Tambahkan catatan jika ada
    if (!empty($catatan)) {
        $updateQuery .= ", catatan_dosen = '$catatan'";
    }
    
    $updateQuery .= " WHERE id_antrian = $id_antrian";
    
    $updated = mysqli_query($conn, $updateQuery);
    
    if ($updated) {
        // Insert ke riwayat_status
        $keterangan = "Status diubah oleh dosen" . (!empty($catatan) ? " dengan catatan" : "");
        $keterangan = mysqli_real_escape_string($conn, $keterangan);
        
        mysqli_query($conn, "
            INSERT INTO riwayat_status (id_antrian, status_lama, status_baru, keterangan)
            VALUES ($id_antrian, '$status_lama', '$status_baru', '$keterangan')
        ");
        
        // Auto-create notifikasi untuk mahasiswa
        include_once 'notification_helper.php';
        notifyMahasiswaStatusChange($conn, $id_antrian, $status_baru);
        
        echo json_encode(['status' => 'success', 'message' => 'Status berhasil diubah menjadi ' . strtoupper($status_baru)]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update database']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid']);
}
?>