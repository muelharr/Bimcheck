<?php
session_start();
include '../config/koneksi.php';

// Cek Login Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Toggle Status Aktif Dosen
if (isset($_POST['toggle_dosen_status'])) {
    $id_dosen = mysqli_real_escape_string($conn, $_POST['id_dosen']);
    $status_baru = mysqli_real_escape_string($conn, $_POST['status_baru']);
    
    // Validasi status
    if (!in_array($status_baru, ['aktif', 'nonaktif'])) {
        echo json_encode(['status' => 'error', 'message' => 'Status tidak valid']);
        exit;
    }
    
    $query = "UPDATE dosen SET status_aktif = '$status_baru' WHERE id_dosen = '$id_dosen'";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Status dosen berhasil diubah',
            'new_status' => $status_baru
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengubah status: ' . mysqli_error($conn)]);
    }
    exit;
}

// Simpan Pengaturan Sistem
if (isset($_POST['simpan_pengaturan'])) {
    $key = mysqli_real_escape_string($conn, $_POST['key']);
    $value = mysqli_real_escape_string($conn, $_POST['value']);
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    
    // Cek apakah key sudah ada
    $cek = mysqli_query($conn, "SELECT * FROM pengaturan_sistem WHERE setting_key = '$key'");
    
    if (mysqli_num_rows($cek) > 0) {
        // Update existing
        $query = "UPDATE pengaturan_sistem SET setting_value = '$value', description = '$description', updated_at = NOW() WHERE setting_key = '$key'";
    } else {
        // Insert new
        $query = "INSERT INTO pengaturan_sistem (setting_key, setting_value, description) VALUES ('$key', '$value', '$description')";
    }
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Pengaturan berhasil disimpan']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan: ' . mysqli_error($conn)]);
    }
    exit;
}

// Hapus Pengaturan Sistem
if (isset($_POST['hapus_pengaturan'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    
    $query = "DELETE FROM pengaturan_sistem WHERE id = '$id'";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Pengaturan berhasil dihapus']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus: ' . mysqli_error($conn)]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
?>
