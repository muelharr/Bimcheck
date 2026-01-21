<?php
include '../config/koneksi.php';

if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $npm = mysqli_real_escape_string($conn, $_POST['identityNumber']);
    $prodi = mysqli_real_escape_string($conn, $_POST['unit']);
    
    // --- FITUR KEAMANAN: ENKRIPSI PASSWORD ---
    $password_raw = $_POST['password'];
    $password_hash = password_hash($password_raw, PASSWORD_DEFAULT); // Password diacak
    // -----------------------------------------

    $cek = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE npm = '$npm'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('NPM sudah terdaftar! Silakan login.'); window.location.href='register.php';</script>";
    } else {
        // Simpan Password yang sudah dienkripsi ($password_hash)
        $query = "INSERT INTO mahasiswa (npm, nama, email, prodi, password) 
          VALUES ('$npm', '$nama', '$email', '$prodi', '$password_hash')";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>
                    alert('Registrasi Berhasil! Akun Anda sudah aman.');
                    window.location.href='login.php';
                  </script>";
        } else {
            echo "<script>alert('Gagal Mendaftar: " . mysqli_error($conn) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - BimCheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 to-purple-100 min-h-screen flex items-center justify-center p-6">
    
    <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-10 w-full max-w-lg border border-white/50">
        <div class="text-center mb-8">
            <div class="inline-block p-3 bg-purple-100 rounded-2xl mb-4 text-purple-600">
                <i class="fas fa-user-plus text-3xl"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-800">Buat Akun Baru</h2>
            <p class="text-gray-500 text-sm mt-1">Isi data diri untuk mulai bimbingan</p>
        </div>

        <form action="" method="POST" class="space-y-5">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Nama Lengkap</label>
                <input type="text" name="nama" class="w-full px-5 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 transition" placeholder="Contoh: Budi Santoso" required>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">NPM</label>
                    <input type="text" name="identityNumber" class="w-full px-5 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 transition" placeholder="714..." required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Prodi</label>
                    <select name="unit" class="w-full px-5 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 transition" required>
                        <option value="">- Pilih -</option>
                        <option value="Teknik Informatika">Informatika</option>
                        <option value="Sistem Informasi">Sistem Informasi</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Email Kampus</label>
                <input type="email" name="email" class="w-full px-5 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 transition" placeholder="nama@mahasiswa.ulm.ac.id" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Password</label>
                <input type="password" name="password" class="w-full px-5 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 transition" placeholder="••••••••" required>
                <p class="text-xs text-gray-400 mt-1">*Gunakan password yang kuat</p>
            </div>

            <button type="submit" name="register" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl hover:scale-[1.02] transition transform duration-200">
                Daftar Sekarang
            </button>
        </form>

        <div class="mt-8 text-center border-t border-gray-100 pt-6">
            <p class="text-sm text-gray-600">
                Sudah punya akun? 
                <a href="login.php" class="font-bold text-purple-600 hover:text-purple-800 transition">Login disini</a>
            </p>
            <div class="mt-4">
                <a href="../index.php" class="text-sm text-gray-500 hover:text-purple-600 transition">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</body>
</html>