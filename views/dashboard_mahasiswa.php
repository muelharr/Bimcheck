<?php
session_start();
include '../config/koneksi.php';

// 1. Cek Login & Role
if (!isset($_SESSION['status']) || $_SESSION['status'] != 'login' || $_SESSION['role'] != 'mahasiswa') {
    header("location:login.php?pesan=dilarang_akses");
    exit;
}

// 2. Ambil Data Mahasiswa dari Database
$npm_login = $_SESSION['user'];
$qMhs = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE npm = '$npm_login'");
$mhs = mysqli_fetch_assoc($qMhs);

// SAFETY CHECK
if (!$mhs) {
    session_unset();
    session_destroy();
    echo "<script>alert('Sesi tidak valid. Silakan login ulang.'); window.location.href='login.php';</script>";
    exit;
}

$id_mahasiswa = $mhs['id_mahasiswa'];

// 3. Proses Form Booking (PERBAIKAN LOGIKA + FILE UPLOAD)
if (isset($_POST['kirim_booking'])) {
    $id_dosen = $_POST['id_dosen']; 
    $topik = htmlspecialchars($_POST['topik']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $tanggal = $_POST['tanggal'];
    $waktu = $_POST['waktu'];
    $file_dokumen = null;
    
    // Handle File Upload (optional)
    if (isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['dokumen'];
        
        // Validasi ukuran file (max 5MB)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            echo "<script>alert('Ukuran file terlalu besar. Maksimal 5MB');</script>";
            goto skip_booking;
        }
        
        // Validasi tipe file
        $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            echo "<script>alert('Tipe file tidak didukung. Gunakan PDF, DOC, DOCX, JPG, atau PNG');</script>";
            goto skip_booking;
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
            $file_dokumen = 'uploads/dokumen_bimbingan/' . $newFileName;
        } else {
            echo "<script>alert('Gagal mengupload file. Silakan coba lagi.');</script>";
            goto skip_booking;
        }
    }
    
    // Validasi: Pastikan Dosen Dipilih
    if (empty($id_dosen)) {
        echo "<script>alert('Harap pilih Dosen Pembimbing!');</script>";
    } else {
        // Generate Nomor Antrian
        $cekNo = mysqli_query($conn, "SELECT COUNT(*) as total FROM antrian WHERE id_dosen='$id_dosen' AND tanggal='$tanggal'");
        $dataNo = mysqli_fetch_assoc($cekNo);
        $nomor_antrian = $dataNo['total'] + 1;

        $queryInsert = "INSERT INTO antrian (id_mahasiswa, id_dosen, nomor_antrian, tanggal, waktu_mulai, topik, deskripsi, file_dokumen, status) 
                        VALUES ('$id_mahasiswa', '$id_dosen', '$nomor_antrian', '$tanggal', '$waktu', '$topik', '$deskripsi', ". ($file_dokumen ? "'$file_dokumen'" : "NULL") .", 'menunggu')";

        if (mysqli_query($conn, $queryInsert)) {
            echo "<script>alert('Booking Berhasil! Menunggu persetujuan dosen.'); window.location.href='dashboard_mahasiswa.php';</script>";
        } else {
            // Tampilkan error database jika gagal (biar ketahuan kenapa)
            echo "<script>alert('Gagal Booking: " . mysqli_error($conn) . "');</script>";
        }
    }
    
    skip_booking:
}

// 4. Ambil Daftar Dosen (Query dipindah ke sini agar rapi)
$qDosen = mysqli_query($conn, "SELECT * FROM dosen ORDER BY nama_dosen ASC");

// 5. Ambil Antrian Aktif
$qActive = mysqli_query($conn, "
    SELECT a.*, d.nama_dosen 
    FROM antrian a 
    JOIN dosen d ON a.id_dosen = d.id_dosen 
    WHERE a.id_mahasiswa = '$id_mahasiswa' 
    AND (a.status = 'menunggu' OR a.status = 'dipanggil' OR a.status = 'proses')
    ORDER BY a.tanggal DESC
");

// 6. Ambil Riwayat
$qHistory = mysqli_query($conn, "
    SELECT a.*, d.nama_dosen 
    FROM antrian a 
    JOIN dosen d ON a.id_dosen = d.id_dosen 
    WHERE a.id_mahasiswa = '$id_mahasiswa' 
    AND (a.status = 'selesai' OR a.status = 'revisi' OR a.status = 'dilewati')
    ORDER BY a.tanggal DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BimCheck - Mahasiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
</head>
<body class="bg-gray-50">
    
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="../index.php" class="flex items-center gap-3 hover:opacity-80 transition-opacity group">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-2 rounded-lg shadow-md group-hover:shadow-lg transition-shadow">
                    <i class="fas fa-shield-alt text-white text-lg sm:text-xl"></i>
                </div>
                <h1 class="text-xl font-bold text-blue-600 group-hover:text-indigo-600 transition-colors">BimCheck</h1>
            </a>
            <div class="flex items-center space-x-4">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <?php 
                        $foto_profil = !empty($mhs['foto_profil']) ? '../' . $mhs['foto_profil'] : 'https://ui-avatars.com/api/?name=' . urlencode($mhs['nama']) . '&background=3b82f6&color=fff&size=128&bold=true';
                        ?>
                        <img src="<?php echo $foto_profil; ?>" alt="Foto Profil" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full border-2 border-blue-500 shadow-md object-cover">
                        <label for="uploadFotoMhs" class="absolute bottom-0 right-0 bg-blue-600 text-white rounded-full p-1.5 cursor-pointer hover:bg-blue-700 transition shadow-lg">
                            <i class="fas fa-camera text-xs"></i>
                        </label>
                        <input type="file" id="uploadFotoMhs" accept="image/*" class="hidden" onchange="uploadFoto('mahasiswa')">
                    </div>
                    <div class="text-right hidden md:block">
                        <p class="text-sm font-bold text-gray-700"><?php echo $mhs['nama']; ?></p>
                        <p class="text-xs text-gray-500"><?php echo $mhs['prodi']; ?></p>
                    </div>
                </div>
                <a href="../actions/logout.php" onclick="return confirm('Logout?')" class="text-red-600">
                    <i class="fas fa-sign-out-alt text-xl"></i>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto pb-20 pt-6 px-4">
        
        <div id="dashboard-page" class="page-content">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-6 text-white mb-6 shadow-lg">
                <h2 class="text-2xl font-bold mb-1">Halo, <?php echo explode(' ', $mhs['nama'])[0]; ?>! 👋</h2>
                <p class="text-blue-100 opacity-90">NIM: <?php echo $mhs['npm']; ?></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <button onclick="showPage('booking')" class="bg-white p-5 rounded-xl shadow-sm border hover:border-blue-500 hover:shadow-md transition text-left flex items-center space-x-4">
                    <div class="bg-blue-100 p-3 rounded-lg text-blue-600"><i class="fas fa-calendar-plus text-2xl"></i></div>
                    <div><h3 class="font-bold text-gray-800">Booking Pertemuan</h3><p class="text-sm text-gray-500">Ajukan jadwal baru</p></div>
                </button>
                <button onclick="showPage('scan')" class="bg-white p-5 rounded-xl shadow-sm border hover:border-green-500 hover:shadow-md transition text-left flex items-center space-x-4">
                    <div class="bg-green-100 p-3 rounded-lg text-green-600"><i class="fas fa-qrcode text-2xl"></i></div>
                    <div><h3 class="font-bold text-gray-800">Scan QR Code</h3><p class="text-sm text-gray-500">Validasi kehadiran</p></div>
                </button>
            </div>

            <h3 class="font-bold text-lg mb-3 text-gray-800">Antrian Aktif Anda</h3>
            <div class="space-y-3">
                <?php if(mysqli_num_rows($qActive) > 0) { 
                    while($row = mysqli_fetch_assoc($qActive)) { 
                        $statusClass = 'bg-yellow-100 text-yellow-700 border-yellow-500';
                        if($row['status'] == 'dipanggil') $statusClass = 'bg-green-100 text-green-700 border-green-500';
                        if($row['status'] == 'proses') $statusClass = 'bg-blue-100 text-blue-700 border-blue-500';
                ?>
                    <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 <?php echo ($row['status']=='proses' ? 'border-blue-500' : ($row['status']=='dipanggil' ? 'border-green-500' : 'border-yellow-500')); ?>">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-800"><?php echo $row['topik']; ?></h4>
                                <p class="text-sm text-gray-600 mt-1"><i class="far fa-user"></i> Dosen: <?php echo $row['nama_dosen']; ?></p>
                                <p class="text-sm text-gray-600"><i class="far fa-clock"></i> <?php echo date('d M, H:i', strtotime($row['tanggal'] . ' ' . $row['waktu_mulai'])); ?></p>
                            </div>
                            <div class="text-right flex flex-col items-end gap-2">
                                <span class="px-2 py-1 rounded text-xs font-bold uppercase <?php echo $statusClass; ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                                <p class="text-xl font-bold text-blue-600">A<?php echo str_pad($row['nomor_antrian'], 3, '0', STR_PAD_LEFT); ?></p>
                                <div class="flex gap-2">
                                    <button onclick='openDetailModal(<?php echo json_encode($row); ?>)' class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-lg font-semibold transition flex items-center gap-1">
                                        <i class="fas fa-info-circle"></i> Detail
                                    </button>
                                    <?php if($row['status'] == 'menunggu'): ?>
                                        <button onclick="cancelBooking(<?php echo $row['id_antrian']; ?>, '<?php echo addslashes($row['topik']); ?>')" class="text-sm bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1 rounded-lg font-semibold transition flex items-center gap-1">
                                            <i class="fas fa-times-circle"></i> Batalkan
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } } else { ?>
                    <div class="bg-white p-6 rounded-lg text-center text-gray-500 border border-dashed">Belum ada antrian aktif.</div>
                <?php } ?>
            </div>
            
            <h3 class="font-bold text-lg mb-3 mt-8 text-gray-800">Riwayat Bimbingan</h3>
            <div class="space-y-3">
                <?php if(mysqli_num_rows($qHistory) > 0) { 
                    while($row = mysqli_fetch_assoc($qHistory)) { 
                        $statusClass = ($row['status'] == 'selesai') ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700';
                ?>
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                        <div class="flex justify-between items-center">
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-800"><?php echo $row['topik']; ?></h4>
                                <p class="text-sm text-gray-500">Dosen: <?php echo $row['nama_dosen']; ?> • <?php echo date('d M Y', strtotime($row['tanggal'])); ?></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 h-fit rounded text-xs font-bold uppercase <?php echo $statusClass; ?>"><?php echo $row['status']; ?></span>
                                <button onclick='openDetailModal(<?php echo json_encode($row); ?>)' class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-lg font-semibold transition flex items-center gap-1">
                                    <i class="fas fa-info-circle"></i> Detail
                                </button>
                            </div>
                        </div>
                    </div>
                <?php } } else { ?>
                    <p class="text-gray-500 text-sm italic">Belum ada riwayat.</p>
                <?php } ?>
            </div>
        </div>

        <div id="booking-page" class="page-content hidden">
            <button onclick="showPage('dashboard')" class="mb-4 text-blue-600 font-bold flex items-center"><i class="fas fa-arrow-left mr-2"></i> Kembali</button>
            <div class="bg-white p-6 rounded-xl shadow-md">
                <h2 class="text-xl font-bold mb-4">Form Booking Bimbingan</h2>
                
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Pilih Dosen Pembimbing</label>
                        <select name="id_dosen" class="w-full border rounded-lg p-2 bg-white" required>
                            <option value="">-- Pilih Dosen --</option>
                            <?php 
                            if (mysqli_num_rows($qDosen) > 0) {
                                // Kita TIDAK PAKAI mysqli_data_seek, langsung loop saja
                                // karena query baru dijalankan di atas
                                while($d = mysqli_fetch_assoc($qDosen)) { 
                            ?>
                                <option value="<?php echo $d['id_dosen']; ?>">
                                    <?php echo $d['nama_dosen']; ?> (<?php echo $d['keahlian']; ?>)
                                </option>
                            <?php 
                                } 
                            } else {
                                echo "<option value='' disabled>Belum ada data dosen</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Topik</label>
                        <input type="text" name="topik" class="w-full border rounded-lg p-2" placeholder="Judul BAB / Topik" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="deskripsi" class="w-full border rounded-lg p-2" rows="3"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-file-upload mr-1 text-blue-600"></i> Upload Dokumen (Opsional)
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-blue-500 transition">
                            <input type="file" name="dokumen" id="fileDokumen" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" onchange="showFilePreview(this)">
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Format: PDF, DOC, DOCX, JPG, PNG - Maksimal 5MB
                            </p>
                            <div id="filePreview" class="hidden mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800" id="fileName"></p>
                                            <p class="text-xs text-gray-500" id="fileSize"></p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="clearFile()" class="text-red-600 hover:text-red-700">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal</label>
                            <input type="date" name="tanggal" class="w-full border rounded-lg p-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Jam</label>
                            <input type="time" name="waktu" class="w-full border rounded-lg p-2" required>
                        </div>
                    </div>
                    <button type="submit" name="kirim_booking" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700">Kirim Booking</button>
                </form>
            </div>
        </div>

        <div id="scan-page" class="page-content hidden text-center">
            <button onclick="showPage('dashboard')" class="mb-4 text-blue-600 font-bold flex items-center"><i class="fas fa-arrow-left mr-2"></i> Kembali</button>
            <div class="bg-white p-8 rounded-xl shadow-md">
                <h2 class="text-xl font-bold mb-4">Scan QR Code</h2>
                <div id="qr-reader" class="mx-auto w-full max-w-sm"></div>
                <div id="qr-reader-placeholder" class="text-gray-500 mb-4">Kamera belum aktif</div>
                <button onclick="startQRScanner()" class="mt-4 bg-green-600 text-white px-6 py-2 rounded-lg font-bold">Buka Kamera</button>
            </div>
        </div>

    </main>

    <!-- Detail Modal -->
    <div id="detailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 backdrop-blur-sm" onclick="closeDetailModal(event)">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-0 overflow-hidden transform scale-100 transition-transform m-4" onclick="event.stopPropagation()">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-info-circle mr-2"></i> Detail Booking Bimbingan
                </h3>
                <button onclick="closeDetailModal()" class="text-white hover:text-gray-200 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                <!-- Nomor Antrian -->
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <p class="text-xs text-gray-500 mb-1">Nomor Antrian:</p>
                    <p class="text-2xl font-bold text-blue-600" id="detailNomorAntrian"></p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Dosen -->
                    <div>
                        <p class="text-xs text-gray-500 mb-1"><i class="fas fa-user-tie mr-1"></i> Dosen Pembimbing:</p>
                        <p class="font-bold text-gray-800" id="detailDosen"></p>
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <p class="text-xs text-gray-500 mb-1"><i class="fas fa-flag mr-1"></i> Status:</p>
                        <span id="detailStatus" class="px-3 py-1 rounded-lg text-sm font-bold uppercase inline-block"></span>
                    </div>
                </div>
                
                <!-- Topik -->
                <div class="mb-4">
                    <p class="text-xs text-gray-500 mb-1"><i class="fas fa-book mr-1"></i> Topik Bimbingan:</p>
                    <p class="font-bold text-gray-800 text-lg" id="detailTopik"></p>
                </div>
                
                <!-- Deskripsi -->
                <div class="mb-4">
                    <p class="text-xs text-gray-500 mb-2"><i class="fas fa-align-left mr-1"></i> Deskripsi:</p>
                    <div class="text-gray-700 bg-gray-50 border border-gray-200 p-4 rounded-lg leading-relaxed" id="detailDeskripsi"></div>
                </div>
                
                <!-- Feedback Dosen (if exists) -->
                <div id="detailFeedbackContainer" class="hidden mb-4">
                    <p class="text-xs text-gray-500 mb-2"><i class="fas fa-comment-dots mr-1"></i> Feedback Dosen:</p>
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 p-4 rounded-lg">
                        <div class="flex items-start gap-3">
                            <div class="bg-blue-500 text-white p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="flex-1 text-gray-800 leading-relaxed" id="detailFeedbackContent"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Tanggal & Waktu -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1"><i class="far fa-calendar mr-1"></i> Tanggal:</p>
                        <p class="font-semibold text-gray-800" id="detailTanggal"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1"><i class="far fa-clock mr-1"></i> Waktu Mulai:</p>
                        <p class="font-semibold text-gray-800" id="detailWaktu"></p>
                    </div>
                </div>
                
                <!-- File Dokumen -->
                <div id="detailFileContainer" class="hidden">
                    <p class="text-xs text-gray-500 mb-2"><i class="fas fa-file-alt mr-1"></i> Dokumen Terlampir:</p>
                    <a id="detailFileLink" href="#" target="_blank" class="flex items-center gap-3 p-3 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                        <div class="bg-blue-600 text-white p-3 rounded-lg">
                            <i id="detailFileIcon" class="fas fa-file-pdf text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-blue-700" id="detailFileName"></p>
                            <p class="text-xs text-gray-500">Klik untuk membuka/download</p>
                        </div>
                        <i class="fas fa-external-link-alt text-blue-600"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>


    <script>
        // --- 1. NAVIGASI HALAMAN ---
        function showPage(id) {
            document.querySelectorAll('.page-content').forEach(el => el.classList.add('hidden'));
            document.getElementById(id + '-page').classList.remove('hidden');
            window.scrollTo(0,0);
        }

        // --- 2. LOGIKA SCANNER KAMERA (VERSI STABIL) ---
        let html5QrCode = null;

        function startQRScanner() {
            // UI: Sembunyikan placeholder, Tampilkan area kamera
            document.getElementById('qr-reader-placeholder').classList.add('hidden');
            document.getElementById('qr-reader').classList.remove('hidden');
            
            // Bersihkan instance lama jika ada (biar ga crash kalau dibuka 2x)
            if (html5QrCode) {
                html5QrCode.clear().catch(error => {
                    console.error("Failed to clear html5QrCode", error);
                });
            }
            
            // Inisialisasi Library
            html5QrCode = new Html5Qrcode("qr-reader");

            const config = { fps: 10, qrbox: { width: 250, height: 250 } };
            
            // LOGIKA PINTAR: Cek kamera belakang dulu
            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    // Ambil kamera terakhir (biasanya kamera belakang di HP)
                    const cameraId = devices[devices.length - 1].id;
                    startScanning(cameraId, config);
                } else {
                    // Kalau gagal deteksi ID, paksa mode environment
                    startWithFacingMode("environment"); 
                }
            }).catch(err => {
                // Kalau browser memblokir deteksi, langsung paksa mode environment
                startWithFacingMode("environment");
            });
        }

        // Helper: Mulai scan dengan ID Kamera Spesifik
        function startScanning(cameraId, config) {
            html5QrCode.start(
                cameraId, 
                config, 
                onScanSuccess, 
                (error) => { /* scanning... (biarkan kosong biar ga spam log) */ }
            ).catch(err => {
                console.warn("Gagal start ID kamera, mencoba fallback...", err);
                startWithFacingMode("environment");
            });
        }

        // Helper: Mulai scan dengan Mode (Environment/User)
        function startWithFacingMode(mode) {
            html5QrCode.start(
                { facingMode: mode }, 
                { fps: 10, qrbox: 250 }, 
                onScanSuccess, 
                (error) => {}
            ).catch(err => {
                if(mode === "environment") {
                    // Jika kamera belakang gagal, coba kamera depan (selfie)
                    console.warn("Kamera belakang gagal, coba kamera depan.");
                    startWithFacingMode("user");
                } else {
                    alert("❌ Gagal membuka kamera. Pastikan izin kamera diberikan di browser (Chrome/Safari).");
                    // Kembalikan tampilan
                    document.getElementById('qr-reader-placeholder').classList.remove('hidden');
                    document.getElementById('qr-reader').classList.add('hidden');
                }
            });
        }

        // --- 3. JIKA SCAN BERHASIL ---
        function onScanSuccess(decodedText, decodedResult) {
            // Matikan kamera & sembunyikan
            html5QrCode.stop().then(() => {
                document.getElementById('qr-reader').classList.add('hidden');
                document.getElementById('qr-reader-placeholder').classList.remove('hidden');
            }).catch(err => console.error(err));
            
            // Kirim Data ke Server (PHP)
            fetch('../actions/validasi_qr.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ qr_content: decodedText })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert("✅ SUKSES: " + data.message);
                    window.location.reload(); // Reload agar status antrian berubah
                } else {
                    alert("❌ GAGAL: " + data.message);
                    showPage('dashboard'); // Kembali ke dashboard
                }
            })
            .catch(err => {
                console.error(err);
                alert("Terjadi kesalahan koneksi server.");
            });
        }

        // Upload Foto Profil
        function uploadFoto(role) {
            const inputId = role === 'dosen' ? 'uploadFotoDosen' : 'uploadFotoMhs';
            const fileInput = document.getElementById(inputId);
            const file = fileInput.files[0];
            
            if (!file) return;
            
            // Validasi ukuran (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file terlalu besar. Maksimal 2MB');
                fileInput.value = '';
                return;
            }
            
            // Validasi tipe file
            if (!file.type.match('image.*')) {
                alert('Format file tidak didukung. Gunakan gambar (JPG, PNG, GIF)');
                fileInput.value = '';
                return;
            }
            
            // Upload menggunakan FormData
            const formData = new FormData();
            formData.append('foto', file);
            
            fetch('../actions/upload_foto.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('✅ Foto profil berhasil diupload!');
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                    fileInput.value = '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Terjadi kesalahan saat mengupload foto');
                fileInput.value = '';
            });
        }

        // --- FILE PREVIEW FUNCTIONS ---
        function showFilePreview(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const filePreview = document.getElementById('filePreview');
                const fileName = document.getElementById('fileName');
                const fileSize = document.getElementById('fileSize');
                
                // Format file size
                const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
                
                fileName.textContent = file.name;
                fileSize.textContent = `${sizeInMB} MB`;
                filePreview.classList.remove('hidden');
            }
        }

        function clearFile() {
            const fileInput = document.getElementById('fileDokumen');
            const filePreview = document.getElementById('filePreview');
            
            fileInput.value = '';
            filePreview.classList.add('hidden');
        }

        // --- DETAIL MODAL FUNCTIONS ---
        function openDetailModal(data) {
            // Populate modal with data
            document.getElementById('detailNomorAntrian').textContent = 'A' + String(data.nomor_antrian).padStart(3, '0');
            document.getElementById('detailDosen').textContent = data.nama_dosen || '-';
            document.getElementById('detailTopik').textContent = data.topik || '-';
            
            // Parse deskripsi and feedback
            let deskripsi = data.deskripsi || '-';
            let feedback = null;
            
            // Check if there's feedback in the format [Feedback Dosen: ...]
            const feedbackMatch = deskripsi.match(/\[Feedback Dosen: (.*?)\]/);
            if (feedbackMatch) {
                feedback = feedbackMatch[1];
                // Remove feedback from deskripsi
                deskripsi = deskripsi.replace(/\[Feedback Dosen: .*?\]/, '').trim();
            }
            
            // Display deskripsi
            const deskripsiEl = document.getElementById('detailDeskripsi');
            if (deskripsi && deskripsi !== '-' && deskripsi !== '') {
                deskripsiEl.innerHTML = deskripsi.split('\n').map(line => 
                    `<p class="mb-1">${line || '&nbsp;'}</p>`
                ).join('');
            } else {
                deskripsiEl.innerHTML = '<p class="text-gray-400 italic">Tidak ada deskripsi</p>';
            }
            
            // Display feedback if exists
            const feedbackContainer = document.getElementById('detailFeedbackContainer');
            if (feedback) {
                const feedbackContent = document.getElementById('detailFeedbackContent');
                feedbackContent.innerHTML = feedback.split('\n').map(line => 
                    `<p class="mb-1">${line || '&nbsp;'}</p>`
                ).join('');
                feedbackContainer.classList.remove('hidden');
            } else {
                feedbackContainer.classList.add('hidden');
            }
            
            // Format tanggal
            if (data.tanggal) {
                const tanggalObj = new Date(data.tanggal);
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                document.getElementById('detailTanggal').textContent = tanggalObj.toLocaleDateString('id-ID', options);
            }
            
            // Format waktu
            document.getElementById('detailWaktu').textContent = data.waktu_mulai || '-';
            
            // Set status badge
            const statusBadge = document.getElementById('detailStatus');
            statusBadge.textContent = data.status;
            
            // Set status color
            statusBadge.className = 'px-3 py-1 rounded-lg text-sm font-bold uppercase inline-block ';
            if (data.status === 'menunggu') {
                statusBadge.className += 'bg-yellow-100 text-yellow-700';
            } else if (data.status === 'dipanggil') {
                statusBadge.className += 'bg-green-100 text-green-700';
            } else if (data.status === 'proses') {
                statusBadge.className += 'bg-blue-100 text-blue-700';
            } else if (data.status === 'selesai') {
                statusBadge.className += 'bg-blue-100 text-blue-700';
            } else if (data.status === 'revisi') {
                statusBadge.className += 'bg-orange-100 text-orange-700';
            } else if (data.status === 'dilewati') {
                statusBadge.className += 'bg-red-100 text-red-700';
            }
            
            // Handle file dokumen
            const fileContainer = document.getElementById('detailFileContainer');
            if (data.file_dokumen) {
                const fileLink = document.getElementById('detailFileLink');
                const fileName = document.getElementById('detailFileName');
                const fileIcon = document.getElementById('detailFileIcon');
                
                fileLink.href = '../' + data.file_dokumen;
                
                // Get file extension
                const extension = data.file_dokumen.split('.').pop().toLowerCase();
                fileName.textContent = data.file_dokumen.split('/').pop();
                
                // Set icon based on file type
                if (extension === 'pdf') {
                    fileIcon.className = 'fas fa-file-pdf text-xl';
                } else if (extension === 'doc' || extension === 'docx') {
                    fileIcon.className = 'fas fa-file-word text-xl';
                } else if (extension === 'jpg' || extension === 'jpeg' || extension === 'png') {
                    fileIcon.className = 'fas fa-file-image text-xl';
                } else {
                    fileIcon.className = 'fas fa-file-alt text-xl';
                }
                
                fileContainer.classList.remove('hidden');
            } else {
                fileContainer.classList.add('hidden');
            }
            
            // Show modal
            document.getElementById('detailModal').classList.remove('hidden');
        }

        function closeDetailModal(event) {
            // Close if clicking backdrop or close button
            if (!event || event.target.id === 'detailModal' || event.currentTarget === event.target) {
                document.getElementById('detailModal').classList.add('hidden');
            }
        }

        // --- CANCEL BOOKING FUNCTION ---
        function cancelBooking(idAntrian, topik) {
            // Confirmation dialog
            if (!confirm(`Yakin ingin membatalkan booking untuk "${topik}"?\n\nBooking yang dibatalkan tidak dapat dikembalikan.`)) {
                return;
            }
            
            // Get button element
            const button = event.target.closest('button');
            const originalHTML = button.innerHTML;
            
            // Show loading state
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Membatalkan...';
            
            // POST request
            fetch('../actions/cancel_booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id_antrian=${idAntrian}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                    button.disabled = false;
                    button.innerHTML = originalHTML;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Terjadi kesalahan saat membatalkan booking');
                button.disabled = false;
                button.innerHTML = originalHTML;
            });
        }
    </script>
</body>
</html>