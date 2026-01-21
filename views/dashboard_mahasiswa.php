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

// 3. Proses Form Booking (PERBAIKAN LOGIKA)
if (isset($_POST['kirim_booking'])) {
    $id_dosen = $_POST['id_dosen']; 
    $topik = htmlspecialchars($_POST['topik']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $tanggal = $_POST['tanggal'];
    $waktu = $_POST['waktu'];
    
    // Validasi: Pastikan Dosen Dipilih
    if (empty($id_dosen)) {
        echo "<script>alert('Harap pilih Dosen Pembimbing!');</script>";
    } else {
        // Generate Nomor Antrian
        $cekNo = mysqli_query($conn, "SELECT COUNT(*) as total FROM antrian WHERE id_dosen='$id_dosen' AND tanggal='$tanggal'");
        $dataNo = mysqli_fetch_assoc($cekNo);
        $nomor_antrian = $dataNo['total'] + 1;

        $queryInsert = "INSERT INTO antrian (id_mahasiswa, id_dosen, nomor_antrian, tanggal, waktu_mulai, topik, deskripsi, status) 
                        VALUES ('$id_mahasiswa', '$id_dosen', '$nomor_antrian', '$tanggal', '$waktu', '$topik', '$deskripsi', 'menunggu')";

        if (mysqli_query($conn, $queryInsert)) {
            echo "<script>alert('Booking Berhasil! Menunggu persetujuan dosen.'); window.location.href='dashboard_mahasiswa.php';</script>";
        } else {
            // Tampilkan error database jika gagal (biar ketahuan kenapa)
            echo "<script>alert('Gagal Booking: " . mysqli_error($conn) . "');</script>";
        }
    }
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
                            <div>
                                <h4 class="font-bold text-gray-800"><?php echo $row['topik']; ?></h4>
                                <p class="text-sm text-gray-600 mt-1"><i class="far fa-user"></i> Dosen: <?php echo $row['nama_dosen']; ?></p>
                                <p class="text-sm text-gray-600"><i class="far fa-clock"></i> <?php echo date('d M, H:i', strtotime($row['tanggal'] . ' ' . $row['waktu_mulai'])); ?></p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 rounded text-xs font-bold uppercase <?php echo $statusClass; ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                                <p class="text-xl font-bold text-blue-600 mt-2">A<?php echo str_pad($row['nomor_antrian'], 3, '0', STR_PAD_LEFT); ?></p>
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
                        <div class="flex justify-between">
                            <div>
                                <h4 class="font-bold text-gray-800"><?php echo $row['topik']; ?></h4>
                                <p class="text-sm text-gray-500">Dosen: <?php echo $row['nama_dosen']; ?> • <?php echo date('d M Y', strtotime($row['tanggal'])); ?></p>
                            </div>
                            <span class="px-2 py-1 h-fit rounded text-xs font-bold uppercase <?php echo $statusClass; ?>"><?php echo $row['status']; ?></span>
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
                
                <form action="" method="POST" class="space-y-4">
                    
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
    </script>
</body>
</html>