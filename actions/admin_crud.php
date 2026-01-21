<?php
session_start();
include '../config/koneksi.php';

// Cek Login Admin (Keamanan)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("location:../views/login.php");
    exit;
}

// === 1. FITUR HAPUS (DELETE) ===
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $type = $_GET['type'];
    $id = $_GET['id'];

    if ($type == 'mahasiswa') {
        // Hapus antrian terkait dulu (Biar tidak error foreign key)
        mysqli_query($conn, "DELETE FROM antrian WHERE id_mahasiswa='$id'");
        $query = "DELETE FROM mahasiswa WHERE id_mahasiswa='$id'";
    } elseif ($type == 'dosen') {
        mysqli_query($conn, "DELETE FROM antrian WHERE id_dosen='$id'");
        $query = "DELETE FROM dosen WHERE id_dosen='$id'";
    } elseif ($type == 'bimbingan') {
        $query = "DELETE FROM antrian WHERE id_antrian='$id'";
    }

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data berhasil dihapus!'); window.location.href='../views/dashboard_admin.php?view=$type';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data.'); window.location.href='../views/dashboard_admin.php?view=$type';</script>";
    }
}

// === 2. FITUR TAMBAH & EDIT MAHASISWA ===
if (isset($_POST['simpan_mhs'])) {
    $aksi = $_POST['aksi'];
    $id = $_POST['id_mahasiswa'];
    $npm = mysqli_real_escape_string($conn, $_POST['npm']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $prodi = mysqli_real_escape_string($conn, $_POST['prodi']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password_raw = $_POST['password'];

    if ($aksi == 'tambah') {
        // ENKRIPSI PASSWORD SAAT TAMBAH
        $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);
        $query = "INSERT INTO mahasiswa (npm, nama, prodi, email, password) VALUES ('$npm', '$nama', '$prodi', '$email', '$password_hash')";
    } else {
        $query = "UPDATE mahasiswa SET npm='$npm', nama='$nama', prodi='$prodi', email='$email'";
        if (!empty($password_raw)) { // Update password cuma kalau diisi
            $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);
            $query .= ", password='$password_hash'";
        }
        $query .= " WHERE id_mahasiswa='$id'";
    }

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data Mahasiswa Berhasil Disimpan!'); window.location.href='../views/dashboard_admin.php?view=mahasiswa';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan: " . mysqli_error($conn) . "'); window.location.href='../views/dashboard_admin.php?view=mahasiswa';</script>";
    }
}

// === 3. FITUR TAMBAH & EDIT DOSEN ===
if (isset($_POST['simpan_dosen'])) {
    $aksi = $_POST['aksi'];
    $id = $_POST['id_dosen'];
    $kode = mysqli_real_escape_string($conn, $_POST['kode_dosen']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_dosen']);
    $keahlian = mysqli_real_escape_string($conn, $_POST['keahlian']);
    $password_raw = $_POST['password'];

    if ($aksi == 'tambah') {
        // ENKRIPSI PASSWORD SAAT TAMBAH (PENTING!)
        $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);
        $query = "INSERT INTO dosen (kode_dosen, nama_dosen, keahlian, password) VALUES ('$kode', '$nama', '$keahlian', '$password_hash')";
    } else {
        $query = "UPDATE dosen SET kode_dosen='$kode', nama_dosen='$nama', keahlian='$keahlian'";
        if (!empty($password_raw)) {
            $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);
            $query .= ", password='$password_hash'";
        }
        $query .= " WHERE id_dosen='$id'";
    }

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data Dosen Berhasil Disimpan!'); window.location.href='../views/dashboard_admin.php?view=dosen';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan: " . mysqli_error($conn) . "'); window.location.href='../views/dashboard_admin.php?view=dosen';</script>";
    }
}
?>