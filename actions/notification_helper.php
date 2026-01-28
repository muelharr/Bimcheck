<?php
// Helper functions untuk notifikasi

// Buat notifikasi baru
function createNotification($conn, $user_type, $user_id, $judul, $pesan, $id_antrian = null) {
    $data = [
        'user_type' => $user_type,
        'user_id' => $user_id,
        'judul' => $judul,
        'pesan' => $pesan
    ];
    
    if ($id_antrian) {
        $data['id_antrian'] = $id_antrian;
    }
    
    include_once __DIR__ . '/../config/db_helper.php';
    return db_insert($conn, 'notifikasi', $data);
}

// Hitung notifikasi belum dibaca
function getUnreadCount($conn, $user_type, $user_id) {
    include_once __DIR__ . '/../config/db_helper.php';
    return db_count($conn, 'notifikasi', 'user_type', $user_type) + db_count($conn, 'notifikasi', 'user_id', $user_id) - db_count($conn, 'notifikasi', 'is_read', 0);
    
    // Better implementation dengan compound where
    $result = db_fetch($conn, 
        "SELECT COUNT(*) as total FROM notifikasi WHERE user_type = ? AND user_id = ? AND is_read = 0",
        'sii',
        [$user_type, $user_id, 0]
    );
    
    return $result ? (int)$result['total'] : 0;
}

// Ambil notifikasi terbaru
function getRecentNotifications($conn, $user_type, $user_id, $limit = 10) {
    include_once __DIR__ . '/../config/db_helper.php';
    return db_fetch_all($conn,
        "SELECT * FROM notifikasi WHERE user_type = ? AND user_id = ? ORDER BY waktu_kirim DESC LIMIT ?",
        'sii',
        [$user_type, $user_id, $limit]
    );
}

// Tandai sudah dibaca
function markAsRead($conn, $id_notifikasi) {
    include_once __DIR__ . '/../config/db_helper.php';
    return db_update($conn, 'notifikasi', ['is_read' => 1], 'id_notifikasi', $id_notifikasi);
}

// Tandai semua sudah dibaca
function markAllAsRead($conn, $user_type, $user_id) {
    include_once __DIR__ . '/../config/db_helper.php';
    
    $stmt = $conn->prepare("UPDATE notifikasi SET is_read = 1 WHERE user_type = ? AND user_id = ? AND is_read = 0");
    $stmt->bind_param('si', $user_type, $user_id);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

// Notifikasi otomatis saat status antrian berubah
function notifyMahasiswaStatusChange($conn, $id_antrian, $status_baru) {
    include_once __DIR__ . '/../config/db_helper.php';
    
    $antrian = db_fetch($conn,
        "SELECT a.*, m.id_mahasiswa, m.nama, d.nama_dosen 
         FROM antrian a 
         JOIN mahasiswa m ON a.id_mahasiswa = m.id_mahasiswa 
         JOIN dosen d ON a.id_dosen = d.id_dosen 
         WHERE a.id_antrian = ?",
        'i',
        [$id_antrian]
    );
    
    if (!$antrian) return;
    
    $judul = '';
    $pesan = '';
    
    switch($status_baru) {
        case 'dipanggil':
            $judul = '🔔 Anda Dipanggil!';
            $pesan = "Silakan scan QR code dosen {$antrian['nama_dosen']} untuk memulai bimbingan.";
            break;
        case 'proses':
            $judul = '✅ Bimbingan Dimulai';
            $pesan = "Bimbingan Anda dengan dosen {$antrian['nama_dosen']} telah dimulai.";
            break;
        case 'selesai':
            $judul = '🎉 Bimbingan Selesai';
            $pesan = "Bimbingan Anda dengan dosen {$antrian['nama_dosen']} telah selesai.";
            break;
        case 'dilewati':
            $judul = '⏭️ Antrian Dilewati';
            $pesan = "Antrian Anda dilewati karena tidak scan QR dalam waktu yang ditentukan.";
            break;
        default:
            return;
    }
    
    createNotification($conn, 'mahasiswa', $antrian['id_mahasiswa'], $judul, $pesan, $id_antrian);
}

// Notifikasi otomatis saat ada booking baru
function notifyDosenNewBooking($conn, $id_antrian) {
    include_once __DIR__ . '/../config/db_helper.php';
    
    $antrian = db_fetch($conn,
        "SELECT a.*, m.nama as nama_mahasiswa, d.id_dosen 
         FROM antrian a 
         JOIN mahasiswa m ON a.id_mahasiswa = m.id_mahasiswa 
         JOIN dosen d ON a.id_dosen = d.id_dosen 
         WHERE a.id_antrian = ?",
        'i',
        [$id_antrian]
    );
    
    if (!$antrian) return;
    
    $judul = '📋 Booking Bimbingan Baru';
    $pesan = "Mahasiswa {$antrian['nama_mahasiswa']} telah booking bimbingan dengan topik: {$antrian['topik']}";
    
    createNotification($conn, 'dosen', $antrian['id_dosen'], $judul, $pesan, $id_antrian);
}
?>
