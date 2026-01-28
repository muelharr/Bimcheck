<?php
session_start();
include '../config/koneksi.php';
include '../config/security.php';
include '../config/db_helper.php';
include '../actions/notification_helper.php';

header('Content-Type: application/json');

// Cek login
require_login();

$user_type = $_SESSION['role'];
$user_id = 0;

// Ambil user ID
if ($user_type == 'mahasiswa') {
    $npm = $_SESSION['user'];
    $user = db_fetch($conn, "SELECT id_mahasiswa FROM mahasiswa WHERE npm = ?", 's', [$npm]);
    $user_id = $user['id_mahasiswa'];
} elseif ($user_type == 'dosen') {
    $kode_dosen = $_SESSION['user'];
    $user = db_fetch($conn, "SELECT id_dosen FROM dosen WHERE kode_dosen = ?", 's', [$kode_dosen]);
    $user_id = $user['id_dosen'];
}

// GET - Ambil notifikasi
if (isset($_GET['action']) && $_GET['action'] == 'get') {
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $notifications = getRecentNotifications($conn, $user_type, $user_id, $limit);
    $unreadCount = getUnreadCount($conn, $user_type, $user_id);
    
    echo json_encode([
        'status' => 'success',
        'notifications' => $notifications,
        'unread_count' => $unreadCount
    ]);
    exit;
}

// POST - Tandai sudah dibaca
if (isset($_POST['mark_read'])) {
    $id_notifikasi = intval($_POST['id_notifikasi']);
    
    if (markAsRead($conn, $id_notifikasi)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update']);
    }
    exit;
}

// POST - Tandai semua sudah dibaca
if (isset($_POST['mark_all_read'])) {
    if (markAllAsRead($conn, $user_type, $user_id)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
?>
