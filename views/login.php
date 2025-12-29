<?php
session_start();
include '../config/koneksi.php';

// --- PEMBERSIH SESI OTOMATIS ---
// Jika dilempar balik dengan pesan error, bersihkan sesi lama biar tidak loop
if (isset($_GET['pesan'])) {
    session_unset();
    session_destroy();
    session_start(); // Mulai sesi baru yang bersih
}

// Cek jika user sudah login (Auto-Redirect)
if (isset($_SESSION['status']) && $_SESSION['status'] == 'login' && !isset($_GET['pesan'])) {
    if ($_SESSION['role'] == 'admin') header("Location: dashboard_admin.php");
    elseif ($_SESSION['role'] == 'mahasiswa') header("Location: dashboard_mahasiswa.php");
    elseif ($_SESSION['role'] == 'dosen') header("Location: dashboard_dosen.php");
    exit;
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; 

    // Fungsi Login Sederhana
    function loginUser($role, $sessionUser, $redirect) {
        // Regenerasi ID Sesi biar tidak hilang (PENTING!)
        session_regenerate_id(true);
        
        $_SESSION['status'] = 'login';
        $_SESSION['role'] = $role;
        $_SESSION['user'] = $sessionUser;
        
        echo "<script>
            alert('Login Berhasil! Selamat datang.');
            window.location.href='$redirect';
        </script>";
        exit;
    }

    // 1. CEK ADMIN
    $qAdmin = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    if (mysqli_num_rows($qAdmin) > 0) {
        $d = mysqli_fetch_assoc($qAdmin);
        loginUser('admin', $d['username'], 'dashboard_admin.php');
    }

    // 2. CEK MAHASISWA
    $qMhs = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE npm='$username' AND password='$password'");
    if (mysqli_num_rows($qMhs) > 0) {
        $d = mysqli_fetch_assoc($qMhs);
        loginUser('mahasiswa', $d['npm'], 'dashboard_mahasiswa.php');
    }

    // 3. CEK DOSEN
    $qDosen = mysqli_query($conn, "SELECT * FROM dosen WHERE kode_dosen='$username' AND password='$password'");
    if (mysqli_num_rows($qDosen) > 0) {
        $d = mysqli_fetch_assoc($qDosen);
        loginUser('dosen', $d['kode_dosen'], 'dashboard_dosen.php');
    }

    echo "<script>alert('Login Gagal! Username atau Password salah.');</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BimCheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .animate-fade-in { animation: fadeIn 0.6s ease-out; }
        .animate-float { animation: float 3s ease-in-out infinite; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .glass-effect { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-6xl animate-fade-in">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <div class="hidden lg:block text-center space-y-6">
                <div class="flex justify-center animate-float">
                    <div class="bg-white bg-opacity-20 backdrop-blur-sm p-8 rounded-3xl shadow-2xl">
                        <svg class="w-32 h-32 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 18c-4.41 0-8-3.59-8-8V8.5l8-4.5 8 4.5V12c0 4.41-3.59 8-8 8z"/>
                            <path d="M11 7h2v6h-2zm0 8h2v2h-2z"/>
                        </svg>
                    </div>
                </div>
                <h1 class="text-5xl font-bold text-gray-800">BimCheck</h1>
                <p class="text-xl text-gray-600 font-medium">Sistem Antrian Bimbingan Digital</p>
            </div>

            <div class="glass-effect rounded-3xl shadow-2xl p-8 md:p-10">
                <div class="lg:hidden text-center mb-6">
                    <div class="flex justify-center mb-4">
                        <div class="gradient-bg p-4 rounded-2xl shadow-lg">
                            <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">BimCheck</h2>
                </div>

                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Selamat Datang!</h2>
                    <p class="text-gray-600">Silakan login untuk mengakses akun Anda</p>
                </div>

                <form action="" method="POST" class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Email / Nomor Identitas</label>
                        <input type="text" name="username" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Masukkan email atau ID" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                        <input type="password" name="password" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Masukkan password" required>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center">
                            <input type="checkbox" class="w-4 h-4 text-purple-600 rounded">
                            <span class="ml-2 text-gray-600">Ingat saya</span>
                        </label>
                        <a href="#" class="font-semibold text-purple-600 hover:text-purple-700">Lupa password?</a>
                    </div>

                    <button type="submit" name="login" class="w-full gradient-bg text-white py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition duration-300 transform hover:scale-105">
                        Login
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                    <p class="text-sm text-gray-600">
                        Belum punya akun? 
                        <a href="register.php" class="font-bold text-purple-600 hover:text-purple-700">Daftar Sekarang</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>