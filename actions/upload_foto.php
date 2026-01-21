<?php
session_start();
include '../config/koneksi.php';

header('Content-Type: application/json');

// Cek Login
if (!isset($_SESSION['status']) || $_SESSION['status'] != 'login') {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
    exit;
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user'];

// Validasi file upload
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada file yang diupload']);
    exit;
}

$file = $_FILES['foto'];
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
$max_size = 2 * 1024 * 1024; // 2MB

// Validasi tipe file
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['status' => 'error', 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF']);
    exit;
}

// Validasi ukuran file
if ($file['size'] > $max_size) {
    echo json_encode(['status' => 'error', 'message' => 'Ukuran file terlalu besar. Maksimal 2MB']);
    exit;
}

// Generate nama file unik
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$new_filename = $role . '_' . $user_id . '_' . time() . '.' . $extension;
$upload_path = '../uploads/profil/' . $new_filename;

// Buat folder jika belum ada
if (!file_exists('../uploads/profil')) {
    mkdir('../uploads/profil', 0777, true);
}

// Upload file
if (move_uploaded_file($file['tmp_name'], $upload_path)) {
    // Simpan path ke database
    $foto_path = 'uploads/profil/' . $new_filename;
    
    if ($role == 'mahasiswa') {
        // Hapus foto lama jika ada
        $qOld = mysqli_query($conn, "SELECT foto_profil FROM mahasiswa WHERE npm = '$user_id'");
        if ($old = mysqli_fetch_assoc($qOld)) {
            if (!empty($old['foto_profil']) && file_exists('../' . $old['foto_profil'])) {
                unlink('../' . $old['foto_profil']);
            }
        }
        
        $query = "UPDATE mahasiswa SET foto_profil = '$foto_path' WHERE npm = '$user_id'";
    } elseif ($role == 'dosen') {
        // Hapus foto lama jika ada
        $qOld = mysqli_query($conn, "SELECT foto_profil FROM dosen WHERE kode_dosen = '$user_id'");
        if ($old = mysqli_fetch_assoc($qOld)) {
            if (!empty($old['foto_profil']) && file_exists('../' . $old['foto_profil'])) {
                unlink('../' . $old['foto_profil']);
            }
        }
        
        $query = "UPDATE dosen SET foto_profil = '$foto_path' WHERE kode_dosen = '$user_id'";
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Role tidak valid']);
        exit;
    }
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Foto profil berhasil diupload', 'foto' => $foto_path]);
    } else {
        // Hapus file jika gagal update database
        unlink($upload_path);
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ke database']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupload file']);
}
?>
