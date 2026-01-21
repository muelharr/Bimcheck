<?php
session_start();
include '../config/koneksi.php';

header('Content-Type: application/json');

// Cek Login Dosen
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'dosen') {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id_antrian = $data['id_antrian'];
$action = $data['action']; // 'panggil', 'selesai', 'revisi'
$catatan = $data['catatan'] ?? ''; // Opsional

$status_baru = '';

if ($action == 'panggil') {
    $status_baru = 'dipanggil';
} elseif ($action == 'selesai') {
    $status_baru = 'selesai';
} elseif ($action == 'revisi') {
    $status_baru = 'revisi';
}

if ($status_baru) {
    // START PERUBAHAN LOGIKA TIMEOUT
    $query = "UPDATE antrian SET status = '$status_baru' ";
    
    // Jika aksinya PANGGIL, catat waktu panggilnya (Reset timer 5 menit)
    if ($status_baru == 'dipanggil') {
        $query .= ", waktu_panggil = NOW() ";
    }
    
    // Jika ada feedback/catatan, simpan dengan format yang jelas
    if (!empty($catatan)) {
        // Escape string untuk keamanan
        $catatan_escaped = mysqli_real_escape_string($conn, $catatan);
        // Simpan feedback dengan format yang lebih jelas
        $query .= ", deskripsi = CONCAT(COALESCE(deskripsi, ''), 
                    CASE 
                        WHEN deskripsi IS NULL OR deskripsi = '' THEN ''
                        ELSE '\n\n'
                    END,
                    '[Feedback Dosen: ', '$catatan_escaped', ']') ";
    }
    
    $query .= " WHERE id_antrian = '$id_antrian'";
    // END PERUBAHAN
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Status berhasil diubah menjadi ' . strtoupper($status_baru)]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update database']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid']);
}
?>