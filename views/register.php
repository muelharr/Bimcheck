<?php
include '../config/koneksi.php';

if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $npm = mysqli_real_escape_string($conn, $_POST['identityNumber']); // Di form name="identityNumber"
    $prodi = mysqli_real_escape_string($conn, $_POST['unit']);         // Di form name="unit"
    $password = $_POST['password']; 

    // Cek apakah NPM sudah ada
    $cek = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE npm = '$npm'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('NPM sudah terdaftar!'); window.location.href='register.php';</script>";
    } else {
        // Simpan ke database
        $query = "INSERT INTO mahasiswa (npm, nama, email, prodi, password) 
                  VALUES ('$npm', '$nama', '$email', '$prodi', '$password')";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>
                    alert('Registrasi Berhasil! Silakan Login.');
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
    <title>Register - BimCheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .glass-effect { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-6xl">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <div class="hidden lg:block text-center space-y-6">
                <div class="flex justify-center">
                    <div class="bg-white bg-opacity-20 backdrop-blur-sm p-8 rounded-3xl shadow-2xl">
                        <svg class="w-32 h-32 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 18c-4.41 0-8-3.59-8-8V8.5l8-4.5 8 4.5V12c0 4.41-3.59 8-8 8z"/>
                            <path d="M11 7h2v6h-2zm0 8h2v2h-2z"/>
                        </svg>
                    </div>
                </div>
                <h1 class="text-5xl font-bold text-gray-800">Bergabung Sekarang</h1>
                <p class="text-xl text-gray-600 font-medium">Platform Bimbingan Digital Terpadu</p>
            </div>

            <div class="glass-effect rounded-3xl shadow-2xl p-8 md:p-10 max-h-[90vh] overflow-y-auto">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Buat Akun Baru</h2>
                    <p class="text-gray-600">Lengkapi data di bawah ini</p>
                </div>

                <form action="" method="POST" class="space-y-4">
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Daftar Sebagai</label>
                        <select name="role" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white" required>
                            <option value="mahasiswa">Mahasiswa</option>
                            <option value="dosen">Dosen</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" name="nama" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Nama Lengkap" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="email@contoh.com" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nomor Identitas</label>
                            <input type="text" name="identityNumber" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="NPM (Mhs) atau Kode Dosen" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Unit / Jurusan</label>
                            <input type="text" name="unit" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Contoh: Teknik Informatika" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                            <input type="password" name="password" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Minimal 8 karakter" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Konfirmasi Password</label>
                            <input type="password" name="confirmPassword" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Ulangi password" required>
                        </div>
                    </div>

                    <button type="submit" name="register" class="w-full gradient-bg text-white py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition duration-300 transform hover:scale-105">
                        Daftar Sekarang
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Sudah punya akun? 
                        <a href="login.php" class="font-bold text-purple-600 hover:text-purple-700">Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>