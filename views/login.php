<?php
session_start();
include '../config/koneksi.php';

// Pembersih Sesi (Anti-Loop)
if (isset($_GET['pesan'])) {
    session_unset();
    session_destroy();
    session_start();
}

// Redirect jika sudah login
if (isset($_SESSION['status']) && $_SESSION['status'] == 'login' && !isset($_GET['pesan'])) {
    if ($_SESSION['role'] == 'admin') header("Location: dashboard_admin.php");
    elseif ($_SESSION['role'] == 'mahasiswa') header("Location: dashboard_mahasiswa.php");
    elseif ($_SESSION['role'] == 'dosen') header("Location: dashboard_dosen.php");
    exit;
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Fungsi Login Sukses
    function loginSuccess($role, $user_id, $redirect) {
        session_regenerate_id(true); // Ganti ID sesi biar aman dari hijacking
        $_SESSION['status'] = 'login';
        $_SESSION['role'] = $role;
        $_SESSION['user'] = $user_id;
        echo "<script>
            alert('Login Berhasil!'); 
            window.location.href='$redirect';
        </script>";
        exit;
    }

    // --- LOGIKA CEK USER ---

    // 1. Cek Admin
    $qAdmin = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($qAdmin) > 0) {
        $d = mysqli_fetch_assoc($qAdmin);
        // Fallback: Cek password biasa dulu (untuk admin lama), kalau gagal baru cek hash
        if ($password == $d['password'] || password_verify($password, $d['password'])) {
             loginSuccess('admin', $d['username'], 'dashboard_admin.php');
        }
    }

    // 2. Cek Mahasiswa
    $qMhs = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE npm='$username'");
    if (mysqli_num_rows($qMhs) > 0) {
        $d = mysqli_fetch_assoc($qMhs);
        // Cek apakah password cocok dengan hash di database
        if (password_verify($password, $d['password'])) {
            loginSuccess('mahasiswa', $d['npm'], 'dashboard_mahasiswa.php');
        }
    }

    // 3. Cek Dosen
    $qDosen = mysqli_query($conn, "SELECT * FROM dosen WHERE kode_dosen='$username'");
    if (mysqli_num_rows($qDosen) > 0) {
        $d = mysqli_fetch_assoc($qDosen);
        // Cek Hash
        if (password_verify($password, $d['password'])) {
            loginSuccess('dosen', $d['kode_dosen'], 'dashboard_dosen.php');
        }
    }

    echo "<script>alert('Login Gagal! NPM/Username tidak ditemukan atau Password salah.');</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BimCheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden w-full max-w-4xl flex flex-col md:flex-row border border-white/50">
        
        <div class="w-full md:w-1/2 p-10 flex flex-col items-center justify-center text-center bg-white relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-purple-500 to-indigo-500"></div>
            
            <a href="../index.php" class="flex flex-col items-center cursor-pointer hover:opacity-80 transition-opacity group">
                <div class="mb-6 bg-purple-50 p-6 rounded-3xl shadow-sm group-hover:shadow-md transition-shadow">
                    <i class="fas fa-shield-alt text-6xl text-purple-600"></i>
                </div>
                <h1 class="text-4xl font-extrabold text-gray-800 mb-2 tracking-tight group-hover:text-purple-600 transition-colors">BimCheck</h1>
                <p class="text-gray-500 font-medium">Sistem Antrian Bimbingan Digital</p>
            </a>
            
            <div class="mt-8 flex gap-2">
                <div class="w-2 h-2 rounded-full bg-purple-600"></div>
                <div class="w-2 h-2 rounded-full bg-indigo-400"></div>
                <div class="w-2 h-2 rounded-full bg-blue-300"></div>
            </div>
        </div>

        <div class="w-full md:w-1/2 p-10 bg-gray-50 flex flex-col justify-center">
            <div class="text-center md:text-left mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Selamat Datang!</h2>
                <p class="text-sm text-gray-500 mt-1">Silakan login untuk mengakses akun Anda</p>
            </div>

            <form action="" method="POST" class="space-y-6">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">ID Pengguna</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" name="username" class="w-full pl-11 pr-4 py-3 rounded-xl bg-white border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" placeholder="NPM / Kode Dosen / Admin" required>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="password" class="w-full pl-11 pr-4 py-3 rounded-xl bg-white border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center text-gray-600 cursor-pointer select-none">
                        <input type="checkbox" class="mr-2 rounded text-purple-600 focus:ring-purple-500 border-gray-300"> Ingat saya
                    </label>
                    <a href="#" class="text-purple-600 hover:text-purple-800 font-bold transition">Lupa password?</a>
                </div>

                <button type="submit" name="login" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-3.5 rounded-xl font-bold shadow-lg hover:shadow-xl hover:scale-[1.02] transition transform duration-200 flex items-center justify-center gap-2">
                    <span>Masuk Aplikasi</span>
                    <i class="fas fa-arrow-right text-sm"></i>
                </button>
            </form>

            <div class="mt-8 text-center text-sm text-gray-600">
                Belum punya akun? <a href="register.php" class="font-bold text-purple-600 hover:text-purple-800 transition">Daftar Sekarang</a>
            </div>
            
            <div class="mt-4 text-center">
                <a href="../index.php" class="text-sm text-gray-500 hover:text-purple-600 transition">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Beranda
                </a>
            </div>
        </div>

    </div>

</body>
</html>