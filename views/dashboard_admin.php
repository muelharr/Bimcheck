<?php
session_start();
include '../config/koneksi.php';

// Cek Login
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    header("location:login.php?pesan=dilarang_akses");
    exit;
}

// Statistik Real-time
$totalMhs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM mahasiswa"))['total'];
$totalDosen = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM dosen"))['total'];
$totalBimbingan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM antrian WHERE tanggal = CURDATE()"))['total'];
$totalAntrian = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM antrian"))['total'];

// Logic View
$view = isset($_GET['view']) ? $_GET['view'] : 'mahasiswa';

if ($view == 'dosen') {
    $title = "Data Dosen";
    $btnText = "+ Tambah Dosen";
    $btnFunction = "openModal('dosen', 'tambah')"; // JS Function
    $queryData = mysqli_query($conn, "SELECT * FROM dosen ORDER BY nama_dosen ASC");

} elseif ($view == 'bimbingan') {
    $title = "Riwayat Bimbingan";
    $btnText = ""; 
    $btnFunction = "";
    $queryData = mysqli_query($conn, "SELECT a.*, m.nama as mhs, d.nama_dosen as dsn FROM antrian a JOIN mahasiswa m ON a.id_mahasiswa = m.id_mahasiswa JOIN dosen d ON a.id_dosen = d.id_dosen ORDER BY a.tanggal DESC");

} elseif ($view == 'laporan') {
    // Data untuk Top 5 Dosen
    $qTopDosen = mysqli_query($conn, "SELECT d.nama_dosen, COUNT(a.id_antrian) as total FROM dosen d LEFT JOIN antrian a ON d.id_dosen = a.id_dosen GROUP BY d.id_dosen ORDER BY total DESC LIMIT 5");
    
    // Data Statistik Status (dengan default 0 jika null)
    $qSelesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM antrian WHERE status='selesai'"))['total'] ?? 0;
    $qMenunggu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM antrian WHERE status='menunggu'"))['total'] ?? 0;
    $qProses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM antrian WHERE status='proses'"))['total'] ?? 0;
    $qDilewati = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM antrian WHERE status='dilewati'"))['total'] ?? 0;
    
    // Data Trend Bulanan (6 bulan terakhir)
    // Perbaikan: Hanya ambil bulan untuk GROUP BY, label dibuat di PHP
    $qTrendBulanan = mysqli_query($conn, "
        SELECT 
            DATE_FORMAT(tanggal, '%Y-%m') as bulan,
            COUNT(*) as total
        FROM antrian 
        WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(tanggal, '%Y-%m')
        ORDER BY bulan ASC
    ");
    
    // Data Top Dosen untuk Chart (array)
    $topDosenData = [];
    $topDosenLabels = [];
    mysqli_data_seek($qTopDosen, 0); // Reset pointer
    while($row = mysqli_fetch_assoc($qTopDosen)) {
        $topDosenLabels[] = $row['nama_dosen'];
        $topDosenData[] = (int)$row['total'];
    }
    
    // Data Trend untuk Chart (dengan fallback jika tidak ada data)
    $trendLabels = [];
    $trendData = [];
    if ($qTrendBulanan && mysqli_num_rows($qTrendBulanan) > 0) {
        // Array nama bulan dalam bahasa Indonesia
        $namaBulan = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
            '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
        ];
        
        while($row = mysqli_fetch_assoc($qTrendBulanan)) {
            // Format: "Jan 2026" dari "2026-01"
            $bulanParts = explode('-', $row['bulan']);
            $bulanAngka = $bulanParts[1];
            $tahun = $bulanParts[0];
            $bulanLabel = ($namaBulan[$bulanAngka] ?? $bulanAngka) . ' ' . $tahun;
            
            $trendLabels[] = $bulanLabel;
            $trendData[] = (int)$row['total'];
        }
    } else {
        // Jika tidak ada data, set default
        $trendLabels = ['Tidak ada data'];
        $trendData = [0];
    }
    
    // Pastikan array topDosen tidak kosong
    if (empty($topDosenLabels)) {
        $topDosenLabels = ['Belum ada data'];
        $topDosenData = [0];
    }

} else { // Default Mahasiswa
    $title = "Data Mahasiswa";
    $btnText = "+ Tambah Mahasiswa";
    $btnFunction = "openModal('mahasiswa', 'tambah')"; // JS Function
    $queryData = mysqli_query($conn, "SELECT * FROM mahasiswa ORDER BY nama ASC");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .gradient-header { background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 100%); }
        .tab-active { background-color: #4f46e5; color: white; box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2); }
        .tab-inactive { background-color: white; color: #4b5563; }
    </style>
</head>
<body class="bg-gray-100 pb-10">

    <header class="gradient-header text-white shadow-lg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 p-2 rounded-lg"><i class="fas fa-shield-alt text-2xl"></i></div>
                <h1 class="text-xl font-bold">Admin Dashboard</h1>
            </div>
            <a href="../actions/logout.php" onclick="return confirm('Keluar?')" class="bg-white/20 hover:bg-white/30 p-2 rounded-lg"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 mt-8">
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-indigo-500">
                <p class="text-xs font-bold text-gray-400">TOTAL MAHASISWA</p>
                <h3 class="text-3xl font-bold text-indigo-600"><?php echo $totalMhs; ?></h3>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500">
                <p class="text-xs font-bold text-gray-400">TOTAL DOSEN</p>
                <h3 class="text-3xl font-bold text-green-600"><?php echo $totalDosen; ?></h3>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-orange-500">
                <p class="text-xs font-bold text-gray-400">BIMBINGAN HARI INI</p>
                <h3 class="text-3xl font-bold text-orange-500"><?php echo $totalBimbingan; ?></h3>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-red-500">
                <p class="text-xs font-bold text-gray-400">TOTAL ANTRIAN</p>
                <h3 class="text-3xl font-bold text-red-500"><?php echo $totalAntrian; ?></h3>
            </div>
        </div>

        <div class="flex gap-4 mb-6 overflow-x-auto">
            <a href="?view=mahasiswa" class="px-6 py-2 rounded-full font-bold text-sm <?php echo ($view=='mahasiswa') ? 'tab-active' : 'tab-inactive'; ?>"><i class="fas fa-user-graduate mr-2"></i> Mahasiswa</a>
            <a href="?view=dosen" class="px-6 py-2 rounded-full font-bold text-sm <?php echo ($view=='dosen') ? 'tab-active' : 'tab-inactive'; ?>"><i class="fas fa-chalkboard-teacher mr-2"></i> Dosen</a>
            <a href="?view=bimbingan" class="px-6 py-2 rounded-full font-bold text-sm <?php echo ($view=='bimbingan') ? 'tab-active' : 'tab-inactive'; ?>"><i class="fas fa-clipboard-list mr-2"></i> Bimbingan</a>
            <a href="?view=laporan" class="px-6 py-2 rounded-full font-bold text-sm <?php echo ($view=='laporan') ? 'tab-active' : 'tab-inactive'; ?>"><i class="fas fa-chart-bar mr-2"></i> Laporan</a>
        </div>

        <?php if ($view == 'laporan') { ?>
            <!-- GRAFIK LAPORAN -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- 1. Doughnut Chart - Distribusi Status -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-bold text-lg mb-4 text-gray-800 flex items-center">
                        <i class="fas fa-chart-pie text-purple-600 mr-2"></i> Distribusi Status Bimbingan
                    </h3>
                    <div class="h-64 flex items-center justify-center">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                        <div class="flex items-center"><span class="w-3 h-3 bg-green-500 rounded mr-2"></span>Selesai: <?php echo $qSelesai; ?></div>
                        <div class="flex items-center"><span class="w-3 h-3 bg-yellow-500 rounded mr-2"></span>Menunggu: <?php echo $qMenunggu; ?></div>
                        <div class="flex items-center"><span class="w-3 h-3 bg-blue-500 rounded mr-2"></span>Proses: <?php echo $qProses; ?></div>
                        <div class="flex items-center"><span class="w-3 h-3 bg-red-500 rounded mr-2"></span>Dilewati: <?php echo $qDilewati; ?></div>
                    </div>
                </div>

                <!-- 2. Bar Chart - Top 5 Dosen Teraktif -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-bold text-lg mb-4 text-gray-800 flex items-center">
                        <i class="fas fa-chart-bar text-indigo-600 mr-2"></i> Top 5 Dosen Teraktif
                    </h3>
                    <div class="h-64">
                        <canvas id="dosenChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- 3. Line Chart - Trend Bimbingan 6 Bulan Terakhir -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h3 class="font-bold text-lg mb-4 text-gray-800 flex items-center">
                    <i class="fas fa-chart-line text-blue-600 mr-2"></i> Trend Bimbingan (6 Bulan Terakhir)
                </h3>
                <div class="h-80">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <!-- 4. Statistik Detail (Card) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-4 text-white shadow-lg">
                    <p class="text-xs opacity-90 mb-1">Selesai</p>
                    <h3 class="text-3xl font-bold"><?php echo $qSelesai; ?></h3>
                </div>
                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl p-4 text-white shadow-lg">
                    <p class="text-xs opacity-90 mb-1">Menunggu</p>
                    <h3 class="text-3xl font-bold"><?php echo $qMenunggu; ?></h3>
                </div>
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-4 text-white shadow-lg">
                    <p class="text-xs opacity-90 mb-1">Proses</p>
                    <h3 class="text-3xl font-bold"><?php echo $qProses; ?></h3>
                </div>
                <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-4 text-white shadow-lg">
                    <p class="text-xs opacity-90 mb-1">Dilewati</p>
                    <h3 class="text-3xl font-bold"><?php echo $qDilewati; ?></h3>
                </div>
            </div>

        <?php } else { ?>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center text-white">
                    <h2 class="font-bold text-lg"><?php echo $title; ?></h2>
                    <?php if($btnText != "") { ?>
                        <button onclick="<?php echo $btnFunction; ?>" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-bold transition">
                            <?php echo $btnText; ?>
                        </button>
                    <?php } ?>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b text-gray-500 text-xs uppercase">
                            <tr>
                                <?php if($view == 'mahasiswa') { ?>
                                    <th class="px-6 py-4">NPM</th><th class="px-6 py-4">Nama</th><th class="px-6 py-4">Prodi</th><th class="px-6 py-4">Email</th><th class="px-6 py-4 text-center">Aksi</th>
                                <?php } elseif($view == 'dosen') { ?>
                                    <th class="px-6 py-4">Kode</th><th class="px-6 py-4">Nama</th><th class="px-6 py-4">Keahlian</th><th class="px-6 py-4 text-center">Aksi</th>
                                <?php } else { ?>
                                    <th class="px-6 py-4">Tanggal</th><th class="px-6 py-4">Mahasiswa</th><th class="px-6 py-4">Dosen</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-center">Aksi</th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <?php while($row = mysqli_fetch_assoc($queryData)) { ?>
                                <tr class="hover:bg-gray-50 transition">
                                    
                                    <?php if($view == 'mahasiswa') { ?>
                                        <td class="px-6 py-4 font-bold"><?php echo $row['npm']; ?></td>
                                        <td class="px-6 py-4"><?php echo $row['nama']; ?></td>
                                        <td class="px-6 py-4 text-gray-500"><?php echo $row['prodi']; ?></td>
                                        <td class="px-6 py-4 text-gray-500"><?php echo $row['email']; ?></td>
                                        <td class="px-6 py-4 text-center flex justify-center gap-2">
                                            <button onclick='openModal("mahasiswa", "edit", <?php echo json_encode($row); ?>)' class="bg-blue-100 text-blue-600 px-3 py-1 rounded text-xs font-bold hover:bg-blue-200">Edit</button>
                                            <a href="../actions/admin_crud.php?aksi=hapus&type=mahasiswa&id=<?php echo $row['id_mahasiswa']; ?>" onclick="return confirm('Hapus data ini?')" class="bg-red-100 text-red-600 px-3 py-1 rounded text-xs font-bold hover:bg-red-200">Hapus</a>
                                        </td>

                                    <?php } elseif($view == 'dosen') { ?>
                                        <td class="px-6 py-4 font-bold"><?php echo $row['kode_dosen']; ?></td>
                                        <td class="px-6 py-4"><?php echo $row['nama_dosen']; ?></td>
                                        <td class="px-6 py-4 text-gray-500"><?php echo $row['keahlian']; ?></td>
                                        <td class="px-6 py-4 text-center">
                                            <?php 
                                            $status = $row['status_aktif'] ?? 'aktif';
                                            $badgeClass = ($status == 'aktif') ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600';
                                            $iconClass = ($status == 'aktif') ? 'fa-check-circle' : 'fa-ban';
                                            ?>
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase <?php echo $badgeClass; ?>">
                                                <i class="fas <?php echo $iconClass; ?> mr-1"></i><?php echo $status; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center flex justify-center gap-2">
                                            <button onclick='toggleDosenStatus(<?php echo json_encode($row); ?>)' class="<?php echo ($status == 'aktif') ? 'bg-gray-100 text-gray-600' : 'bg-green-100 text-green-600'; ?> px-3 py-1 rounded text-xs font-bold hover:opacity-80">
                                                <?php echo ($status == 'aktif') ? 'Nonaktifkan' : 'Aktifkan'; ?>
                                            </button>
                                            <button onclick='openModal("dosen", "edit", <?php echo json_encode($row); ?>)' class="bg-blue-100 text-blue-600 px-3 py-1 rounded text-xs font-bold hover:bg-blue-200">Edit</button>
                                            <a href="../actions/admin_crud.php?aksi=hapus&type=dosen&id=<?php echo $row['id_dosen']; ?>" onclick="return confirm('Hapus data ini?')" class="bg-red-100 text-red-600 px-3 py-1 rounded text-xs font-bold hover:bg-red-200">Hapus</a>
                                        </td>

                                    <?php } else { ?>
                                        <td class="px-6 py-4 text-gray-500"><?php echo $row['tanggal']; ?></td>
                                        <td class="px-6 py-4 font-bold"><?php echo $row['mhs']; ?></td>
                                        <td class="px-6 py-4"><?php echo $row['dsn']; ?></td>
                                        <td class="px-6 py-4"><span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs font-bold uppercase"><?php echo $row['status']; ?></span></td>
                                        <td class="px-6 py-4 text-center">
                                            <a href="../actions/admin_crud.php?aksi=hapus&type=bimbingan&id=<?php echo $row['id_antrian']; ?>" onclick="return confirm('Hapus riwayat ini?')" class="bg-red-100 text-red-600 px-3 py-1 rounded text-xs font-bold hover:bg-red-200">Hapus</a>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } ?>
    </main>

    <div id="modalMahasiswa" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-xl w-96 shadow-2xl">
            <h3 id="modalTitleMhs" class="text-xl font-bold mb-4">Tambah Mahasiswa</h3>
            <form action="../actions/admin_crud.php" method="POST">
                <input type="hidden" name="simpan_mhs" value="1">
                <input type="hidden" name="aksi" id="aksiMhs" value="tambah">
                <input type="hidden" name="id_mahasiswa" id="idMhs">
                
                <div class="space-y-3">
                    <input type="text" name="npm" id="npmMhs" placeholder="NPM" class="w-full border p-2 rounded" required>
                    <input type="text" name="nama" id="namaMhs" placeholder="Nama Lengkap" class="w-full border p-2 rounded" required>
                    <input type="text" name="prodi" id="prodiMhs" placeholder="Program Studi" class="w-full border p-2 rounded" required>
                    <input type="email" name="email" id="emailMhs" placeholder="Email" class="w-full border p-2 rounded" required>
                    <input type="text" name="no_telepon" id="noTeleponMhs" placeholder="Nomor Telepon (08xxx)" class="w-full border p-2 rounded" maxlength="15">
                    <input type="password" name="password" id="passMhs" placeholder="Password (Kosongkan jika tidak ubah)" class="w-full border p-2 rounded">
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modalMahasiswa').classList.add('hidden')" class="px-4 py-2 text-gray-500">Batal</button>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded font-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalDosen" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-xl w-96 shadow-2xl">
            <h3 id="modalTitleDosen" class="text-xl font-bold mb-4">Tambah Dosen</h3>
            <form action="../actions/admin_crud.php" method="POST">
                <input type="hidden" name="simpan_dosen" value="1">
                <input type="hidden" name="aksi" id="aksiDosen" value="tambah">
                <input type="hidden" name="id_dosen" id="idDosen">
                
                <div class="space-y-3">
                    <input type="text" name="kode_dosen" id="kodeDosen" placeholder="Kode Dosen (ex: MYH)" class="w-full border p-2 rounded" required>
                    <input type="text" name="nama_dosen" id="namaDosen" placeholder="Nama Dosen" class="w-full border p-2 rounded" required>
                    <input type="email" name="email" id="emailDosen" placeholder="Email" class="w-full border p-2 rounded" required>
                    <input type="text" name="no_telepon" id="noTeleponDosen" placeholder="Nomor Telepon" class="w-full border p-2 rounded" maxlength="15">
                    <input type="text" name="keahlian" id="keahlianDosen" placeholder="Keahlian / Bidang" class="w-full border p-2 rounded" required>
                    <input type="password" name="password" id="passDosen" placeholder="Password (Kosongkan jika tidak ubah)" class="w-full border p-2 rounded">
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modalDosen').classList.add('hidden')" class="px-4 py-2 text-gray-500">Batal</button>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded font-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        <?php if ($view == 'laporan') { ?>
        // === GRAFIK LAPORAN ===
        
        // 1. Doughnut Chart - Distribusi Status
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Selesai', 'Menunggu', 'Proses', 'Dilewati'],
                datasets: [{
                    data: [
                        <?php echo $qSelesai; ?>,
                        <?php echo $qMenunggu; ?>,
                        <?php echo $qProses; ?>,
                        <?php echo $qDilewati; ?>
                    ],
                    backgroundColor: [
                        '#10b981', // green
                        '#eab308', // yellow
                        '#3b82f6', // blue
                        '#ef4444'  // red
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.parsed || 0;
                                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // 2. Bar Chart - Top 5 Dosen
        const dosenCtx = document.getElementById('dosenChart').getContext('2d');
        new Chart(dosenCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($topDosenLabels); ?>,
                datasets: [{
                    label: 'Jumlah Sesi',
                    data: <?php echo json_encode($topDosenData); ?>,
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(124, 58, 237, 0.8)'
                    ],
                    borderColor: [
                        'rgb(99, 102, 241)',
                        'rgb(139, 92, 246)',
                        'rgb(59, 130, 246)',
                        'rgb(168, 85, 247)',
                        'rgb(124, 58, 237)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Sesi: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // 3. Line Chart - Trend Bulanan
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($trendLabels); ?>,
                datasets: [{
                    label: 'Jumlah Bimbingan',
                    data: <?php echo json_encode($trendData); ?>,
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: 'rgb(99, 102, 241)',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Bimbingan: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        <?php } ?>

        function openModal(type, mode, data = null) {
            if (type === 'mahasiswa') {
                const modal = document.getElementById('modalMahasiswa');
                document.getElementById('aksiMhs').value = mode;
                document.getElementById('modalTitleMhs').innerText = mode === 'tambah' ? 'Tambah Mahasiswa' : 'Edit Mahasiswa';
                
                if (mode === 'edit' && data) {
                    document.getElementById('idMhs').value = data.id_mahasiswa;
                    document.getElementById('npmMhs').value = data.npm;
                    document.getElementById('namaMhs').value = data.nama;
                    document.getElementById('prodiMhs').value = data.prodi;
                    document.getElementById('emailMhs').value = data.email;
                    document.getElementById('noTeleponMhs').value = data.no_telepon || '';
                    document.getElementById('passMhs').required = false; // Pass tidak wajib saat edit
                } else {
                    document.getElementById('idMhs').value = '';
                    document.getElementById('npmMhs').value = '';
                    document.getElementById('namaMhs').value = '';
                    document.getElementById('prodiMhs').value = '';
                    document.getElementById('emailMhs').value = '';
                    document.getElementById('noTeleponMhs').value = '';
                    document.getElementById('passMhs').required = true;
                }
                modal.classList.remove('hidden');
            } 
            else if (type === 'dosen') {
                const modal = document.getElementById('modalDosen');
                document.getElementById('aksiDosen').value = mode;
                document.getElementById('modalTitleDosen').innerText = mode === 'tambah' ? 'Tambah Dosen' : 'Edit Dosen';
                
                if (mode === 'edit' && data) {
                    document.getElementById('idDosen').value = data.id_dosen;
                    document.getElementById('kodeDosen').value = data.kode_dosen;
                    document.getElementById('namaDosen').value = data.nama_dosen;
                    document.getElementById('emailDosen').value = data.email || '';
                    document.getElementById('noTeleponDosen').value = data.no_telepon || '';
                    document.getElementById('keahlianDosen').value = data.keahlian;
                    document.getElementById('passDosen').required = false;
                } else {
                    document.getElementById('idDosen').value = '';
                    document.getElementById('kodeDosen').value = '';
                    document.getElementById('namaDosen').value = '';
                    document.getElementById('emailDosen').value = '';
                    document.getElementById('noTeleponDosen').value = '';
                    document.getElementById('keahlianDosen').value = '';
                    document.getElementById('passDosen').required = true;
                }
                modal.classList.remove('hidden');
            }
        }

        // Toggle Dosen Status Aktif
        function toggleDosenStatus(data) {
            const currentStatus = data.status_aktif || 'aktif';
            const newStatus = (currentStatus === 'aktif') ? 'nonaktif' : 'aktif';
            const confirmMsg = (newStatus === 'nonaktif') 
                ? `Nonaktifkan dosen ${data.nama_dosen}? Dosen tidak akan muncul di pilihan mahasiswa.`
                : `Aktifkan kembali dosen ${data.nama_dosen}?`;
            
            if (!confirm(confirmMsg)) return;
            
            fetch('../actions/admin_settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `toggle_dosen_status=1&id_dosen=${data.id_dosen}&status_baru=${newStatus}`
            })
            .then(res => res.json())
            .then(result => {
                if (result.status === 'success') {
                    alert('Status berhasil diubah!');
                    location.reload();
                } else {
                    alert('Gagal: ' + result.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan!');
            });
        }
    </script>
</body>
</html>