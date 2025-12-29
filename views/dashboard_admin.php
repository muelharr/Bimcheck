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
    $qTopDosen = mysqli_query($conn, "SELECT d.nama_dosen, COUNT(a.id_antrian) as total FROM dosen d LEFT JOIN antrian a ON d.id_dosen = a.id_dosen GROUP BY d.id_dosen ORDER BY total DESC LIMIT 5");
    $qSelesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM antrian WHERE status='selesai'"))['total'];
    $qRevisi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM antrian WHERE status='revisi'"))['total'];

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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-bold text-lg mb-4 text-gray-800">Statistik Penyelesaian</h3>
                    <div class="space-y-4">
                        <div class="bg-green-50 p-4 rounded-lg flex justify-between"><span class="text-gray-600">Selesai</span><span class="font-bold text-green-600"><?php echo $qSelesai; ?></span></div>
                        <div class="bg-orange-50 p-4 rounded-lg flex justify-between"><span class="text-gray-600">Revisi</span><span class="font-bold text-orange-600"><?php echo $qRevisi; ?></span></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-bold text-lg mb-4 text-gray-800">Top 5 Dosen Teraktif</h3>
                    <?php while($top = mysqli_fetch_assoc($qTopDosen)) { ?>
                        <div class="flex justify-between p-3 border-b"><span class="text-gray-700"><?php echo $top['nama_dosen']; ?></span><span class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs font-bold"><?php echo $top['total']; ?> Sesi</span></div>
                    <?php } ?>
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
                                        <td class="px-6 py-4 text-center flex justify-center gap-2">
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
                    <input type="text" name="kode_dosen" id="kodeDosen" placeholder="Kode Dosen (ex: DSN01)" class="w-full border p-2 rounded" required>
                    <input type="text" name="nama_dosen" id="namaDosen" placeholder="Nama Dosen" class="w-full border p-2 rounded" required>
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
                    document.getElementById('passMhs').required = false; // Pass tidak wajib saat edit
                } else {
                    document.getElementById('idMhs').value = '';
                    document.getElementById('npmMhs').value = '';
                    document.getElementById('namaMhs').value = '';
                    document.getElementById('prodiMhs').value = '';
                    document.getElementById('emailMhs').value = '';
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
                    document.getElementById('keahlianDosen').value = data.keahlian;
                    document.getElementById('passDosen').required = false;
                } else {
                    document.getElementById('idDosen').value = '';
                    document.getElementById('kodeDosen').value = '';
                    document.getElementById('namaDosen').value = '';
                    document.getElementById('keahlianDosen').value = '';
                    document.getElementById('passDosen').required = true;
                }
                modal.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>