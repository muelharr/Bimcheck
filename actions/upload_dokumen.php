<?php
session_start();
include '../config/koneksi.php';

header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['status']) || $_SESSION['status'] != 'login') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Validasi request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Cek apakah file diupload
if (!isset($_FILES['dokumen']) || $_FILES['dokumen']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada file yang diupload']);
    exit;
}

$file = $_FILES['dokumen'];

// Validasi error upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan saat upload file']);
    exit;
}

// Validasi ukuran file (max 5MB)
$maxSize = 5 * 1024 * 1024; // 5MB in bytes
if ($file['size'] > $maxSize) {
    echo json_encode(['status' => 'error', 'message' => 'Ukuran file terlalu besar. Maksimal 5MB']);
    exit;
}

// Validasi tipe file
$allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($fileExtension, $allowedExtensions)) {
    echo json_encode(['status' => 'error', 'message' => 'Tipe file tidak didukung. Gunakan PDF, DOC, DOCX, JPG, atau PNG']);
    exit;
}

// Buat folder jika belum ada
$uploadDir = '../uploads/dokumen_bimbingan/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate nama file unik
$timestamp = time();
$randomString = bin2hex(random_bytes(8));
$newFileName = "dokumen_{$timestamp}_{$randomString}.{$fileExtension}";
$targetPath = $uploadDir . $newFileName;

// Pindahkan file
if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // Return relative path untuk disimpan di database
    $relativePath = 'uploads/dokumen_bimbingan/' . $newFileName;
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'File berhasil diupload',
        'file_path' => $relativePath,
        'file_name' => $file['name']
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan file']);
}
?>
