# BimCheck - Sistem Antrian Bimbingan Digital

![Version](https://img.shields.io/badge/version-2.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.1+-purple)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange)
![License](https://img.shields.io/badge/license-MIT-green)

## 📖 Daftar Isi

- [Tentang BimCheck](#tentang-bimcheck)
- [Fitur Utama](#fitur-utama)
- [Teknologi](#teknologi)
- [Struktur Folder](#struktur-folder)
- [Quick Start](#quick-start)
- [Dokumentasi Lengkap](#dokumentasi-lengkap)

---

## 🎯 Tentang BimCheck

**BimCheck** adalah aplikasi web berbasis PHP untuk mengelola antrian bimbingan akademik antara mahasiswa dan dosen. Sistem ini menggunakan teknologi QR Code untuk validasi kehadiran dan menyediakan interface yang modern dan user-friendly.

### Tujuan Aplikasi

- ✅ Mendigitalkan proses antrian bimbingan
- ✅ Mengurangi waktu tunggu mahasiswa
- ✅ Memudahkan dosen mengelola jadwal bimbingan
- ✅ Menyediakan riwayat bimbingan yang terstruktur
- ✅ Meningkatkan efisiensi proses bimbingan akademik

---

## ⭐ Fitur Utama

### 👨‍🎓 Untuk Mahasiswa

- **Booking Bimbingan**: Ajukan jadwal bimbingan dengan dosen
- **Upload Dokumen**: Lampirkan file pendukung (PDF, DOC, JPG, PNG)
- **QR Code Scanner**: Scan QR code dosen untuk validasi kehadiran
- **Tracking Status**: Monitor status antrian real-time
- **Riwayat Bimbingan**: Lihat history dan feedback dosen
- **Detail View**: Modal detail dengan informasi lengkap

### 👨‍🏫 Untuk Dosen

- **Dashboard Monitor**: Lihat semua antrian hari ini
- **QR Code Generator**: Generate QR dengan auto-refresh (5 menit)
- **Manajemen Antrian**: Panggil, proses, selesaikan bimbingan
- **Feedback System**: Berikan catatan untuk mahasiswa
- **Timeout Management**: Auto-lewati mahasiswa yang tidak hadir (60 menit)
- **History Tracking**: Riwayat bimbingan dengan filter

### 🔐 Untuk Admin

- **User Management**: Kelola data mahasiswa dan dosen
- **CRUD Operations**: Create, Read, Update, Delete
- **Role Management**: Atur hak akses pengguna

---

## 🛠️ Teknologi

### Backend
- **PHP 8.1+**: Server-side scripting
- **MySQL 8.0+**: Database management
- **Session-based Auth**: Keamanan autentikasi

### Frontend
- **HTML5**: Struktur halaman
- **TailwindCSS**: Styling framework
- **JavaScript ES6**: Client-side logic
- **Font Awesome**: Icon library

### Libraries
- **Html5-QRCode**: QR code scanner untuk mahasiswa
- **QRCode.js**: QR code generator untuk dosen
- **BCrypt**: Password hashing

---

## 📁 Struktur Folder

```
Bimcheck/
├── actions/                    # Backend handlers
│   ├── admin_crud.php         # CRUD operations untuk admin
│   ├── logout.php             # Logout handler
│   ├── update_status.php      # Update status antrian
│   ├── upload_dokumen.php     # Upload file handler (NEW)
│   ├── upload_foto.php        # Upload foto profil
│   └── validasi_qr.php        # QR validation handler
│
├── assets/                     # Static assets
│   └── (images, icons, etc)
│
├── config/                     # Configuration files
│   └── koneksi.php            # Database connection
│
├── dokumentasi/               # Technical documentation (NEW)
│   ├── README.md              # Overview
│   ├── FITUR.md               # Feature details
│   ├── ARSITEKTUR.md          # System architecture
│   ├── DATABASE.md            # Database schema
│   ├── WORKFLOW.md            # Application workflows
│   └── SETUP.md               # Installation guide
│
├── uploads/                    # User uploads
│   ├── foto_profil/           # Profile pictures
│   └── dokumen_bimbingan/     # Booking documents (NEW)
│
├── views/                      # Frontend pages
│   ├── dashboard_mahasiswa.php
│   ├── dashboard_dosen.php
│   └── login.php
│
├── index.php                   # Landing page
├── bimcheck.sql               # Database dump
└── migration_add_file_column.sql  # DB migration (NEW)
```

---

## 🚀 Quick Start

### Prerequisites

- PHP >= 8.1
- MySQL >= 8.0
- Web server (Apache/Nginx) atau PHP built-in server
- Browser modern (Chrome, Firefox, Safari)

### Instalasi

1. **Clone/Download Repository**
   ```bash
   git clone <repository-url>
   cd Bimcheck
   ```

2. **Import Database**
   ```bash
   mysql -u root -p bimcheck < bimcheck.sql
   mysql -u root -p bimcheck < migration_add_file_column.sql
   ```

3. **Konfigurasi Database**
   
   Edit `config/koneksi.php`:
   ```php
   $host = "localhost";
   $user = "root";
   $pass = "your_password";
   $db   = "bimcheck";
   ```

4. **Jalankan Server**
   ```bash
   php -S localhost:8000
   ```

5. **Akses Aplikasi**
   
   Buka browser: `http://localhost:8000`

### Default Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin |
| Mahasiswa | 714 | admin |
| Dosen | 713 | admin |

---

## 📚 Dokumentasi Lengkap

Untuk dokumentasi lebih detail, silakan lihat:

- [📋 FITUR.md](./FITUR.md) - Penjelasan lengkap fitur-fitur
- [🏗️ ARSITEKTUR.md](./ARSITEKTUR.md) - Arsitektur sistem dan design pattern
- [💾 DATABASE.md](./DATABASE.md) - Skema database dan relasi
- [🔄 WORKFLOW.md](./WORKFLOW.md) - Workflow dan use case
- [⚙️ SETUP.md](./SETUP.md) - Panduan instalasi dan deployment

---

## 🔒 Keamanan

- ✅ Password hashing menggunakan BCrypt
- ✅ Session-based authentication
- ✅ SQL injection prevention (prepared statements recommended)
- ✅ File upload validation (type & size)
- ✅ Access control berbasis role

---

## 🤝 Kontribusi

Kontribusi sangat diterima! Silakan:

1. Fork repository
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

---

## 📝 Changelog

### Version 2.0 (Latest)
- ✨ File upload di form booking
- ✨ Detail modal untuk lihat info lengkap
- ✨ Improved feedback display
- ✨ Enhanced UI/UX
- 🔧 Database optimization

### Version 1.0
- 🎉 Initial release
- ✅ QR code dengan time-based token
- ✅ 60 menit timeout untuk mahasiswa
- ✅ Real-time status tracking

---

## 📄 License

MIT License - silakan gunakan untuk keperluan akademik dan komersial.

---

## 👥 Tim Pengembang

Dikembangkan sebagai solusi digitalisasi proses bimbingan akademik.

---

## 📞 Support

Untuk pertanyaan, bug report, atau feature request:
- Create an issue di GitHub
- Email: support@bimcheck.id (contoh)

---

**BimCheck** - Digitalisasi Bimbingan Akademik 🎓
