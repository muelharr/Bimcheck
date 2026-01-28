<?php
// Timeout Checker - Auto-skip antrian dipanggil > 60 menit
// File ini dipanggil secara terpisah untuk avoid conflict dengan page load

session_start();
include '../config/koneksi.php';
include '../config/security.php';

header('Content-Type: application/json');

// Optional: bisa di-protect atau ga, tergantung kebutuhan
// Kalau ga di-protect, siapa aja bisa trigger timeout check (which is okay)

try {
    // Update semua antrian yang dipanggil > 60 menit jadi dilewati
    $stmt = $conn->prepare("
        UPDATE antrian 
        SET status = 'dilewati' 
        WHERE status = 'dipanggil' 
        AND waktu_panggil IS NOT NULL 
        AND TIMESTAMPDIFF(MINUTE, waktu_panggil, NOW()) >= 60
    ");
    
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    
    // Buat notifikasi untuk mahasiswa yang dilewati
    if ($affected > 0) {
        include_once 'notification_helper.php';
        
        // Ambil antrian yang baru dilewati
        $result = mysqli_query($conn, "
            SELECT id_antrian 
            FROM antrian 
            WHERE status = 'dilewati' 
            AND waktu_panggil IS NOT NULL 
            AND TIMESTAMPDIFF(MINUTE, waktu_panggil, NOW()) >= 60
            LIMIT 10
        ");
        
        while($row = mysqli_fetch_assoc($result)) {
            notifyMahasiswaStatusChange($conn, $row['id_antrian'], 'dilewati');
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => "$affected antrian dilewati karena timeout",
        'affected' => $affected
    ]);
    
} catch (Exception $e) {
    error_log("Timeout Checker Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal check timeout'
    ]);
}
?>
