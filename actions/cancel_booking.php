<?php
session_start();
include __DIR__ . '/../config/koneksi.php';
include __DIR__ . '/../config/security.php';
include __DIR__ . '/../config/db_helper.php';

header('Content-Type: application/json');

// Check login
require_login();
require_role('mahasiswa');

// Check POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak valid']);
    exit;
}

// Get mahasiswa data
$npm_login = $_SESSION['user'];
$mhs = db_fetch($conn, "SELECT * FROM mahasiswa WHERE npm = ?", 's', [$npm_login]);

if (!$mhs) {
    echo json_encode(['status' => 'error', 'message' => 'Data mahasiswa tidak ditemukan']);
    exit;
}

$id_mahasiswa = $mhs['id_mahasiswa'];

// Get booking ID
$id_antrian = isset($_POST['id_antrian']) ? intval($_POST['id_antrian']) : 0;

if (!validate_positive_integer($id_antrian)) {
    echo json_encode(['status' => 'error', 'message' => 'ID antrian tidak valid']);
    exit;
}

// Check ownership and status
$booking = db_fetch($conn, 
    "SELECT * FROM antrian WHERE id_antrian = ? AND id_mahasiswa = ?", 
    'ii', 
    [$id_antrian, $id_mahasiswa]
);

if (!$booking) {
    echo json_encode(['status' => 'error', 'message' => 'Booking tidak ditemukan atau bukan milik Anda']);
    exit;
}

// Check if status is 'menunggu'
if ($booking['status'] != 'menunggu') {
    echo json_encode(['status' => 'error', 'message' => 'Hanya booking dengan status "Menunggu" yang dapat dibatalkan']);
    exit;
}

// Delete booking
$deleted = db_delete($conn, 'antrian', 'id_antrian', $id_antrian);

if ($deleted) {
    echo json_encode(['status' => 'success', 'message' => 'Booking berhasil dibatalkan']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal membatalkan booking']);
}
?>
