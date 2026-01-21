<?php
session_start();
include __DIR__ . '/../config/koneksi.php';

header('Content-Type: application/json');

// Check login
if (!isset($_SESSION['status']) || $_SESSION['status'] != 'login' || $_SESSION['role'] != 'mahasiswa') {
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada akses. Silakan login.']);
    exit;
}

// Check POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak valid']);
    exit;
}

// Get mahasiswa data from session (same as dashboard)
$npm_login = $_SESSION['user'];
$qMhs = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE npm = '$npm_login'");
$mhs = mysqli_fetch_assoc($qMhs);

if (!$mhs) {
    echo json_encode(['status' => 'error', 'message' => 'Data mahasiswa tidak ditemukan']);
    exit;
}

$id_mahasiswa = $mhs['id_mahasiswa'];

// Get booking ID from POST
$id_antrian = isset($_POST['id_antrian']) ? intval($_POST['id_antrian']) : 0;

if ($id_antrian <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID antrian tidak valid']);
    exit;
}

// Check ownership and status
$query = "SELECT * FROM antrian WHERE id_antrian = $id_antrian AND id_mahasiswa = $id_mahasiswa";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Booking tidak ditemukan atau bukan milik Anda']);
    exit;
}

$booking = mysqli_fetch_assoc($result);

// Check if status is 'menunggu'
if ($booking['status'] != 'menunggu') {
    echo json_encode(['status' => 'error', 'message' => 'Hanya booking dengan status "Menunggu" yang dapat dibatalkan']);
    exit;
}

// Delete booking
$deleteQuery = "DELETE FROM antrian WHERE id_antrian = $id_antrian";
$deleteResult = mysqli_query($conn, $deleteQuery);

if ($deleteResult) {
    echo json_encode(['status' => 'success', 'message' => 'Booking berhasil dibatalkan']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal membatalkan booking: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>
