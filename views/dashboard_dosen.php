<?php
session_start();
include '../config/koneksi.php';

// 1. LOGIKA OTOMATIS: TIMEOUT 5 MENIT
// Cek jika ada yang dipanggil > 5 menit, ubah jadi 'dilewati'
$queryTimeout = "UPDATE antrian 
                 SET status = 'dilewati' 
                 WHERE status = 'dipanggil' 
                 AND TIMESTAMPDIFF(MINUTE, waktu_panggil, NOW()) >= 5";
mysqli_query($conn, $queryTimeout);

// 2. Cek Login
if (!isset($_SESSION['status']) || $_SESSION['status'] != 'login' || $_SESSION['role'] != 'dosen') {
    header("location:login.php?pesan=dilarang_akses");
    exit;
}

// 3. Ambil Data Dosen
$kode_dosen = $_SESSION['user'];
$qDosen = mysqli_query($conn, "SELECT * FROM dosen WHERE kode_dosen = '$kode_dosen'");
$dosen = mysqli_fetch_assoc($qDosen);
$id_dosen = $dosen['id_dosen'];

// 4. Statistik
$qMenunggu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM antrian WHERE id_dosen='$id_dosen' AND tanggal=CURDATE() AND status='menunggu'"));
$qProses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM antrian WHERE id_dosen='$id_dosen' AND tanggal=CURDATE() AND status IN ('dipanggil', 'proses')"));
$qSelesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM antrian WHERE id_dosen='$id_dosen' AND tanggal=CURDATE() AND status='selesai'"));
$qRevisi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM antrian WHERE id_dosen='$id_dosen' AND tanggal=CURDATE() AND status='revisi'"));

// 5. Ambil Antrian Aktif
$qAntrian = mysqli_query($conn, "
    SELECT a.*, m.nama, m.npm, m.prodi 
    FROM antrian a 
    JOIN mahasiswa m ON a.id_mahasiswa = m.id_mahasiswa 
    WHERE a.id_dosen = '$id_dosen' 
    AND a.tanggal = CURDATE() 
    AND a.status IN ('menunggu', 'dipanggil', 'proses', 'dilewati') 
    ORDER BY FIELD(a.status, 'proses', 'dipanggil', 'menunggu', 'dilewati'), a.nomor_antrian ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BimCheck Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .glass-header {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100">

    <div class="max-w-7xl mx-auto p-6">
        
        <div class="glass-header rounded-2xl p-6 text-white mb-8 shadow-lg flex justify-between items-center relative overflow-hidden">
            <div class="relative z-10">
                <a href="../index.php" class="flex items-center gap-3 hover:opacity-90 transition-opacity group">
                    <div class="bg-white/20 p-2 rounded-lg group-hover:bg-white/30 transition">
                        <i class="fas fa-shield-alt text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold group-hover:text-purple-200 transition-colors">BimCheck Dashboard</h1>
                        <p class="text-purple-100 text-sm">Sistem Antrian Bimbingan Digital</p>
                    </div>
                </a>
            </div>
            
            <div class="relative z-10 flex items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <?php 
                        $foto_profil = !empty($dosen['foto_profil']) ? '../' . $dosen['foto_profil'] : 'https://ui-avatars.com/api/?name=' . urlencode($dosen['nama_dosen']) . '&background=667eea&color=fff&size=128&bold=true';
                        ?>
                        <img src="<?php echo $foto_profil; ?>" alt="Foto Profil" class="w-12 h-12 rounded-full border-2 border-white shadow-lg object-cover">
                        <label for="uploadFotoDosen" class="absolute bottom-0 right-0 bg-purple-600 text-white rounded-full p-1.5 cursor-pointer hover:bg-purple-700 transition shadow-lg">
                            <i class="fas fa-camera text-xs"></i>
                        </label>
                        <input type="file" id="uploadFotoDosen" accept="image/*" class="hidden" onchange="uploadFoto('dosen')">
                    </div>
                    <div class="text-right">
                        <p class="font-bold"><?php echo $dosen['nama_dosen']; ?></p>
                        <p class="text-xs text-purple-200">Kode: <?php echo $dosen['kode_dosen']; ?></p>
                    </div>
                </div>
                <a href="../actions/logout.php" onclick="return confirm('Keluar?')" class="bg-white/20 hover:bg-white/30 p-2 rounded-lg transition">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
            
            <div class="absolute top-0 right-0 -mr-10 -mt-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute bottom-0 left-0 -ml-10 -mb-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-5 rounded-2xl shadow-sm flex justify-between items-center">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Total Antrian</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $qMenunggu['total']; ?></h3>
                </div>
                <div class="p-3 bg-blue-500 text-white rounded-xl shadow-lg shadow-blue-200">
                    <i class="fas fa-users"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm flex justify-between items-center">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Sedang Bimbingan</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $qProses['total']; ?></h3>
                </div>
                <div class="p-3 bg-green-500 text-white rounded-xl shadow-lg shadow-green-200">
                    <i class="fas fa-clock"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm flex justify-between items-center">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Selesai Hari Ini</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $qSelesai['total']; ?></h3>
                </div>
                <div class="p-3 bg-purple-500 text-white rounded-xl shadow-lg shadow-purple-200">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm flex justify-between items-center">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Perlu Revisi</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $qRevisi['total']; ?></h3>
                </div>
                <div class="p-3 bg-orange-500 text-white rounded-xl shadow-lg shadow-orange-200">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-bolt text-yellow-500 mr-2"></i> Aksi Cepat
                    </h3>
                    <div class="flex flex-wrap gap-3">
                        <button onclick="panggilNext()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-md transition flex items-center">
                            <i class="fas fa-bullhorn mr-2"></i> Panggil Selanjutnya
                        </button>
                        <button onclick="location.reload()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-xl font-bold transition flex items-center border border-gray-200">
                            <i class="fas fa-sync-alt mr-2"></i> Refresh
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-4">
                        <h3 class="text-white font-bold flex items-center">
                            <i class="fas fa-list-ul mr-2"></i> Daftar Antrian Bimbingan
                        </h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-4 font-bold">No. Antrian</th>
                                    <th class="px-6 py-4 font-bold">Mahasiswa</th>
                                    <th class="px-6 py-4 font-bold">Topik</th>
                                    <th class="px-6 py-4 font-bold">Status</th>
                                    <th class="px-6 py-4 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <?php if(mysqli_num_rows($qAntrian) > 0) { 
                                    while($row = mysqli_fetch_assoc($qAntrian)) { 
                                        $status = $row['status'];
                                        $badgeClass = 'bg-gray-100 text-gray-600';
                                        
                                        if($status == 'menunggu') $badgeClass = 'bg-yellow-100 text-yellow-700';
                                        if($status == 'dipanggil') $badgeClass = 'bg-blue-100 text-blue-700';
                                        if($status == 'proses') $badgeClass = 'bg-green-100 text-green-700';
                                        if($status == 'dilewati') $badgeClass = 'bg-red-100 text-red-700';
                                ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 bg-purple-50 text-purple-700 rounded-lg font-bold">
                                            A<?php echo str_pad($row['nomor_antrian'], 3, '0', STR_PAD_LEFT); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-gray-800"><?php echo $row['nama']; ?></p>
                                        <p class="text-xs text-gray-500"><?php echo $row['npm']; ?></p>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600"><?php echo $row['topik']; ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase <?php echo $badgeClass; ?>">
                                            <?php echo $status; ?>
                                        </span>

                                        <?php if($status == 'dipanggil' && !empty($row['waktu_panggil'])) { ?>
                                            <div class="mt-2 text-[10px] bg-blue-50 border border-blue-100 p-1.5 rounded text-blue-600 font-medium">
                                                <i class="far fa-clock"></i> 
                                                <?php echo date('H:i', strtotime($row['waktu_panggil'])); ?> WIB
                                                <div class="text-red-500 font-bold mt-0.5">
                                                    Batas: <?php echo date('H:i', strtotime($row['waktu_panggil'] . ' +5 minutes')); ?>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        
                                        <?php if($status == 'proses') { ?>
                                            <div class="mt-1 text-xs text-green-600 font-bold flex items-center">
                                                <i class="fas fa-check-circle mr-1"></i> Hadir
                                            </div>
                                        <?php } ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if($status == 'menunggu' || $status == 'dilewati') { ?>
                                            <button onclick="updateStatus(<?php echo $row['id_antrian']; ?>, 'panggil')" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-blue-700 shadow transition">
                                                Panggil
                                            </button>
                                        <?php } elseif($status == 'dipanggil') { ?>
                                            <button disabled class="bg-gray-100 text-gray-400 px-3 py-1.5 rounded-lg text-xs font-bold border cursor-not-allowed">
                                                Menunggu Scan
                                            </button>
                                        <?php } elseif($status == 'proses') { ?>
                                            <button onclick="bukaModalSelesai(<?php echo $row['id_antrian']; ?>, '<?php echo $row['nama']; ?>')" class="bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-green-700 shadow transition">
                                                Selesai
                                            </button>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php } } else { ?>
                                    <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400 italic">Belum ada antrian.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm text-center border border-gray-100 sticky top-24">
                    <div class="bg-gradient-to-r from-purple-600 to-blue-600 text-white py-2 px-4 rounded-lg inline-block mb-4 text-xs font-bold uppercase tracking-wider">
                        QR Validasi Kehadiran
                    </div>
                    
                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 mb-4 flex justify-center bg-gray-50">
                        <div id="qrcode"></div>
                    </div>
                    
                    <div class="bg-purple-50 text-purple-700 p-3 rounded-lg text-xs font-medium">
                        <i class="fas fa-info-circle mr-1"></i> Tunjukkan QR ini ke mahasiswa untuk memulai bimbingan.
                    </div>
                    
                    <input type="hidden" id="idDosenVal" value="<?php echo $id_dosen; ?>">
                </div>
            </div>

        </div>
    </div>

    <div id="modalSelesai" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-0 overflow-hidden transform scale-100 transition-transform">
            <div class="bg-gradient-to-r from-purple-600 to-blue-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-comment-dots mr-2"></i> Berikan Feedback
                </h3>
                <button onclick="document.getElementById('modalSelesai').classList.add('hidden')" class="text-white hover:text-gray-200 transition"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="p-6">
                <div class="mb-4 pb-4 border-b border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Mahasiswa:</p>
                    <p class="text-lg font-bold text-gray-800" id="modalNamaMhs"></p>
                </div>
                
                <input type="hidden" id="modalIdAntrian">
                
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Feedback Bimbingan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="modalCatatan" class="w-full border-2 border-gray-300 rounded-xl p-4 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition" rows="5" placeholder="Tulis feedback untuk mahasiswa..." required></textarea>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i> Feedback akan tersimpan dan dapat dilihat mahasiswa
                    </p>
                </div>
                
                <button onclick="kirimFeedback()" class="w-full bg-gradient-to-r from-purple-600 to-blue-600 text-white hover:from-purple-700 hover:to-blue-700 px-6 py-3 rounded-xl font-bold text-sm shadow-lg transition transform hover:scale-[1.02] flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    <span>Selesai & Simpan Feedback</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        // 1. Generate QR Code
        const idDosen = document.getElementById('idDosenVal').value;
        new QRCode(document.getElementById("qrcode"), {
            text: idDosen, width: 140, height: 140,
            colorDark : "#4c1d95", colorLight : "#ffffff", correctLevel : QRCode.CorrectLevel.H
        });

        // 2. Fungsi Panggil
        function updateStatus(id, action) {
            fetch('../actions/update_status.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_antrian: id, action: action })
            }).then(res => res.json()).then(data => {
                if(data.status === 'success') location.reload();
                else alert(data.message);
            });
        }

        // 3. Modal Functions
        function bukaModalSelesai(id, nama) {
            document.getElementById('modalIdAntrian').value = id;
            document.getElementById('modalNamaMhs').innerText = nama;
            document.getElementById('modalCatatan').value = ''; // Reset textarea
            document.getElementById('modalSelesai').classList.remove('hidden');
        }

        function kirimFeedback() {
            const id = document.getElementById('modalIdAntrian').value;
            const feedback = document.getElementById('modalCatatan').value.trim();
            
            // Validasi: Feedback wajib diisi
            if (!feedback) {
                alert('⚠️ Mohon isi feedback terlebih dahulu!');
                document.getElementById('modalCatatan').focus();
                return;
            }
            
            // Deteksi otomatis status berdasarkan isi feedback
            // Jika feedback mengandung kata kunci revisi, set status revisi
            const feedbackLower = feedback.toLowerCase();
            const kataRevisi = ['revisi', 'perlu perbaikan', 'perbaiki', 'kurang', 'belum tepat', 'salah'];
            const perluRevisi = kataRevisi.some(kata => feedbackLower.includes(kata));
            
            // Tentukan action berdasarkan feedback
            const action = perluRevisi ? 'revisi' : 'selesai';
            
            // Kirim ke server
            fetch('../actions/update_status.php', {
                method: 'POST', 
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    id_antrian: id, 
                    action: action, 
                    catatan: feedback 
                })
            }).then(res => res.json()).then(data => {
                if(data.status === 'success') {
                    // Tutup modal dan reload
                    document.getElementById('modalSelesai').classList.add('hidden');
                    location.reload();
                } else {
                    alert('❌ Gagal menyimpan feedback: ' + data.message);
                }
            }).catch(err => {
                console.error(err);
                alert('❌ Terjadi kesalahan saat menyimpan feedback.');
            });
        }

        // Panggil Next Otomatis (Opsional)
        function panggilNext() {
            // Cari tombol panggil pertama di tabel dan klik
            const btn = document.querySelector("button[onclick*='panggil']");
            if(btn) btn.click();
            else alert("Tidak ada antrian menunggu saat ini.");
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