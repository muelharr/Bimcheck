# 📋 Fitur-Fitur BimCheck

Dokumentasi lengkap semua fitur yang tersedia di aplikasi BimCheck.

---

## 📑 Daftar Isi

- [Fitur Mahasiswa](#fitur-mahasiswa)
- [Fitur Dosen](#fitur-dosen)
- [Fitur Admin](#fitur-admin)
- [Fitur Keamanan](#fitur-keamanan)

---

## 👨‍🎓 Fitur Mahasiswa

### 1. Registration & Authentication

**Login System**
- Login menggunakan NPM sebagai username
- Password ter-hash dengan BCrypt
- Session-based authentication
- Auto-redirect ke dashboard setelah login

**Profile Management**
- Upload foto profil
- Validasi: max 2MB, format JPG/PNG/GIF
- Preview foto real-time
- Simpan ke database

---

### 2. Booking Bimbingan

**Form Booking**

Location: `views/dashboard_mahasiswa.php` (Lines 237-318)

**Input Fields:**
- Dropdown pilih dosen pembimbing
- Input topik bimbingan (required)
- Textarea deskripsi (optional)
- Input tanggal (date picker)
- Input waktu mulai (time picker)
- **[NEW]** Upload dokumen pendukung (optional)

**File Upload:**
- Accepted formats: PDF, DOC, DOCX, JPG, PNG
- Maximum size: 5MB
- File preview dengan nama & ukuran
- Clear button untuk remove file
- Auto-validasi client & server side

**Backend Processing:**
```php
// File: views/dashboard_mahasiswa.php (Lines 26-99)
- Validasi file type & size
- Generate unique filename
- Store di uploads/dokumen_bimbingan/
- Save path ke database
- Auto-generate nomor antrian
```

---

### 3. QR Code Scanner

**Scan QR untuk Validasi Kehadiran**

Location: `views/dashboard_mahasiswa.php` (Lines 323-332)

**Features:**
- Camera access dengan permission handling
- Auto-detect kamera belakang (untuk mobile)
- Fallback ke kamera depan jika perlu
- Real-time scanning
- Visual feedback saat scan

**Workflow:**
1. Mahasiswa klik "Scan QR Code"
2. Browser minta izin akses kamera
3. Scanner aktif, tampilkan preview
4. Scan QR code dari dashboard dosen
5. Validasi token di server
6. Update status: dipanggil → proses
7. Redirect ke dashboard

**QR Validation:**
```javascript
// File: actions/validasi_qr.php
- Decode QR content: "idDosen|timestamp"
- Check timestamp masih valid (±5 menit)
- Verify mahasiswa punya antrian dipanggil
- Update waktu_kehadiran & status
- Return JSON response
```

---

### 4. Tracking Antrian

**Antrian Aktif**

Display: Real-time status antrian hari ini

**Status Types:**
- 🟡 **Menunggu**: Booking diterima, menunggu dipanggil
- 🟢 **Dipanggil**: Dosen memanggil, mahasiswa harus scan QR dalam 60 menit
- 🔵 **Proses**: Mahasiswa sudah hadir, sedang bimbingan

**Information Displayed:**
- Nomor antrian (format: A001, A002, ...)
- Topik bimbingan
- Nama dosen
- Tanggal & waktu
- Status badge (color-coded)
- Tombol "Detail"
- **[NEW]** Tombol "Batalkan" (hanya untuk status menunggu)

---

### 4. Cancel Booking **[NEW]**

**Batalkan Booking Bimbingan**

Location: `views/dashboard_mahasiswa.php` + `actions/cancel_booking.php`

**Features:**
- Button "Batalkan" hanya muncul untuk booking dengan status **"menunggu"**
- Confirmation dialog sebelum cancel (prevent accidental cancellation)
- Cannot cancel booking yang sudah dipanggil/proses
- Auto-reload setelah cancel sukses
- Booking dihapus dari database (hard delete)

**Workflow:**
1. Mahasiswa lihat booking dengan status "menunggu"
2. Klik button "Batalkan" (red button)
3. Konfirmasi dialog: "Yakin ingin membatalkan? Tidak dapat dikembalikan"
4. Click OK → Loading state ("Membatalkan...")
5. Server validate ownership & status
6. DELETE dari database
7. Success alert → Auto-reload page
8. Booking hilang dari active queue

**Backend Logic:**
```php
// File: actions/cancel_booking.php
1. Check session (mahasiswa logged in)
2. Get NPM from $_SESSION['user']
3. Query database untuk id_mahasiswa
4. Verify ownership (id_mahasiswa matches)
5. Verify status = 'menunggu'
6. DELETE FROM antrian WHERE id_antrian = X
7. Return JSON success/error
```

**Validations:**
- ✅ Must be logged in as mahasiswa
- ✅ Booking must belong to current user
- ✅ Status must be 'menunggu' (not dipanggil/proses)
- ✅ Confirmation required

**Error Handling:**
- "Tidak ada akses" → Session invalid
- "Booking tidak ditemukan" → Wrong ID or ownership
- "Hanya booking menunggu yang bisa dibatalkan" → Wrong status

---

### 5. Detail View Modal

**[NEW Feature]** - Lihat informasi lengkap booking

Location: `views/dashboard_mahasiswa.php` (Lines 334-413)

**Modal Content:**
- 📋 Nomor antrian (large display)
- 👨‍🏫 Dosen pembimbing
- 🏷️ Status (color-coded badge)
- 📚 Topik bimbingan
- 📝 Deskripsi (formatted with line breaks)
- 💬 Feedback dosen (jika ada, dengan icon & gradient background)
- 📅 Tanggal (formatted: "21 Januari 2026")
- ⏰ Waktu mulai
- 📎 File dokumen (jika ada, dengan icon sesuai type + download link)

**UI Features:**
- Gradient header (blue to indigo)
- Responsive design
- Scrollable content
- Click outside to close
- X button untuk close

---

### 6. Riwayat Bimbingan

**History Log**

Display: Semua bimbingan yang sudah selesai

**Status Types:**
- ✅ **Selesai**: Bimbingan berhasil diselesaikan
- 🔄 **Revisi**: Perlu perbaikan/revisi
- ⏭️ **Dilewati**: Mahasiswa tidak hadir

**Features:**
- Filter otomatis by status
- Sort by tanggal (terbaru dulu)
- Tampilkan feedback dosen
- Tombol detail untuk lihat lengkap
- Akses dokumen yang diupload

---

## 👨‍🏫 Fitur Dosen

### 1. Dashboard Overview

**Statistik Hari Ini**

Location: `views/dashboard_dosen.php` (Lines 115-145)

**Cards:**
1. Total Antrian (menunggu)
2. Sedang Bimbingan (dipanggil + proses)
3. Selesai Hari Ini

**Visual:** Icon dengan warna & shadow matching

---

### 2. QR Code Generator

**Time-Based QR Code**

Location: `views/dashboard_dosen.php` (Lines 319-343)

**Features:**
- Generate QR dengan token: `idDosen|timestamp`
- Timestamp dalam 5-minute blocks
- Auto-refresh setiap 5 menit
- Countdown timer (5:00 → 0:00)
- Manual refresh button
- Purple/blue gradients

**JavaScript Logic:**
```javascript
// File: dashboard_dosen.php (Lines 476-543)
- generateQRCode(): Create new QR
- startCountdown(): 5-minute timer
- startAutoRefresh(): Auto-regenerate
- regenerateQR(): Manual button
```

**Security:**
- Token valid ±5 menit dari generate
- Prevent replay attack
- One-time use per mahasiswa

---

### 3. Manajemen Antrian

**Tabel Antrian Aktif**

Location: `views/dashboard_dosen.php` (Lines 174-248)

**Columns:**
- No. Antrian (formatted badge)
- Mahasiswa (nama + NPM)
- Topik
- Status (color-coded)
- Aksi (button sesuai status)
- **[NEW]** Detail (modal button)

**Actions by Status:**

**Menunggu/Dilewati:**
- Button "Panggil"
- Update status → dipanggil
- Set waktu_panggil = NOW()

**Dipanggil:**
- "Menunggu Scan" (disabled)
- Countdown batas waktu
- Auto-timeout 60 menit → dilewati

**Proses:**
- Button "Selesai"
- Open modal feedback
- Input catatan (optional)
- Update status → selesai

---

### 4. Feedback System

**Modal Feedback Bimbingan**

Location: `views/dashboard_dosen.php` (Lines 348-393)

**Features:**
- Textarea untuk catatan/feedback
- Optional (boleh kosong)
- Auto-save ke deskripsi dengan format: `[Feedback Dosen: ...]`
- Character support: multiline
- Gradient button (purple to blue)

**Backend:**
```php
// File: actions/update_status.php
- Append feedback ke deskripsi
- Update status = 'selesai'
- Set waktu_selesai = NOW()
- Return JSON response
```

---

### 5. Timeout Management

**Auto-Lewati Mahasiswa**

Location: `views/dashboard_dosen.php` (Lines 5-11)

**Logic:**
```sql
UPDATE antrian 
SET status = 'dilewati' 
WHERE status = 'dipanggil' 
AND TIMESTAMPDIFF(MINUTE, waktu_panggil, NOW()) >= 60
```

**Features:**
- Cek setiap page load
- 60 menit grace period
- Auto-update tanpa refresh
- Mahasiswa bisa dipanggil ulang

---

### 6. Riwayat Bimbingan

**History Table**

Location: `views/dashboard_dosen.php` (Lines 260-314)

**Columns:**
- Tanggal (formatted)
- Mahasiswa info
- Topik
- Status badge
- Feedback (truncated)
- **[NEW]** Detail button

**Features:**
- Limit 20 entries
- ORDER BY tanggal DESC
- Filter: selesai, revisi, dilewati
- Hover effect pada row

---

### 7. Detail View Modal

**[NEW Feature]** - Lihat detail mahasiswa & booking

Location: `views/dashboard_dosen.php` (Lines 395-473)

**Additional Info (vs Mahasiswa):**
- 👨‍🎓 Nama mahasiswa + NPM
- 📝 Deskripsi terpisah dari feedback
- 💬 Feedback yang diberikan (highlight dengan purple gradient)
- 📎 Dokumen yang diupload mahasiswa

---

## 🔐 Fitur Admin

### 1. User Management

**CRUD Operations**

Location: `actions/admin_crud.php`

**Entities:**
- Mahasiswa
- Dosen
- Admin users

**Operations:**
- Create: Tambah user baru
- Read: Lihat daftar user
- Update: Edit data user
- Delete: Hapus user (cascade delete)

**Validations:**
- Unique NPM/Kode Dosen
- Password hashing
- Email format
- Required fields

---

### 2. Dashboard Admin

**Features:**
- Tabel semua users
- Search & filter
- Pagination
- Bulk actions
- Export to CSV

---

## 🔒 Fitur Keamanan

### 1. Authentication

**Password Security:**
- BCrypt hashing (cost factor 10)
- Salt auto-generated
- One-way encryption

**Session Management:**
```php
session_start();
$_SESSION['status'] = 'login';
$_SESSION['user'] = $username;
$_SESSION['role'] = $role;
```

---

### 2. Authorization

**Role-Based Access Control:**

| Page | Mahasiswa | Dosen | Admin |
|------|-----------|-------|-------|
| dashboard_mahasiswa.php | ✅ | ❌ | ❌ |
| dashboard_dosen.php | ❌ | ✅ | ❌ |
| admin CRUD | ❌ | ❌ | ✅ |

**Middleware:**
```php
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'expected_role') {
    header("location:login.php?pesan=dilarang_akses");
    exit;
}
```

---

### 3. File Upload Security

**Validations:**
- Type whitelist (PDF, DOC, DOCX, JPG, PNG)
- Size limit (5MB dokumen, 2MB foto)
- Unique filename generation
- Safe storage path

**Implementation:**
```php
// Client-side
<input accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">

// Server-side
$allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
if (!in_array($extension, $allowedExtensions)) {
    // Reject
}
```

---

### 4. SQL Injection Prevention

**Current:** String concatenation (❌ vulnerable)

**Recommended:** Prepared statements
```php
// BAD (current)
$query = "SELECT * FROM users WHERE username='$username'";

// GOOD (recommended)
$stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
```

---

### 5. XSS Prevention

**htmlspecialchars() usage:**
```php
$topik = htmlspecialchars($_POST['topik']);
$deskripsi = htmlspecialchars($_POST['deskripsi']);
```

---

## 🎨 UI/UX Features

### 1. Responsive Design

- Mobile-first approach
- Breakpoints: sm, md, lg, xl
- Grid system (Tailwind)
- Hamburger menu (planned)

### 2. Color-Coded Status

| Status | Color | Background |
|--------|-------|------------|
| Menunggu | Yellow | bg-yellow-100 |
| Dipanggil | Green/Blue | bg-green-100 |
| Proses | Blue | bg-blue-100 |
| Selesai | Blue/Green | bg-blue-100 |
| Revisi | Orange | bg-orange-100 |
| Dilewati | Red | bg-red-100 |

### 3. Micro-interactions

- Hover effects
- Button transitions
- Modal animations
- Loading states
- Toast notifications (planned)

---

## 📊 Reporting Features (Planned)

- Export PDF riwayat
- Statistik bulanan
- Chart visualizations
- Email notifications

---

## 🚀 Performance Optimizations

1. **Database Indexing:**
   - idx_tanggal
   - idx_status
   - idx_tanggal_status (composite)
   - idx_id_dosen_tanggal (composite)

2. **Query Optimization:**
   - JOINs instead of multiple queries
   - LIMIT for pagination
   - WHERE clauses dengan indexed columns

3. **Frontend:**
   - CDN untuk libraries
   - Lazy loading images
   - Minified CSS/JS (planned)

---

Dokumentasi fitur ini akan terus diupdate seiring perkembangan aplikasi.
