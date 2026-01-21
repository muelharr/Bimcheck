# 📊 BimCheck - Graphviz Diagrams Collection

Kumpulan semua diagram dalam format Graphviz DOT untuk visualisasi di:
- https://dreampuf.github.io/GraphvizOnline/
- https://edotor.net/
- http://www.webgraphviz.com/

---

## 📑 Daftar Diagram

1. [System Architecture](#1-system-architecture)
2. [Deployment Architecture](#2-deployment-architecture)
3. [Entity Relationship Diagram](#3-entity-relationship-diagram)
4. [Use Case Diagram](#4-use-case-diagram)
5. [Login Flow](#5-login-flow)
6. [Booking Flow](#6-booking-flow)
7. [QR Scan Flow](#7-qr-scan-flow)
8. [Cancel Booking Flow](#8-cancel-booking-flow) **[NEW]**
9. [Status State Diagram](#9-status-state-diagram)
10. [Dashboard Dosen Load Flow](#10-dashboard-dosen-load-flow)
11. [Complete Booking Process](#11-complete-booking-process)

---

## 1. System Architecture

**Copy & paste ke Graphviz Online:**

```dot
digraph SystemArchitecture {
    rankdir=TB;
    node [shape=box, style=filled];
    
    Client [label="Client Browser", fillcolor="#4A90E2", fontcolor=white];
    WebServer [label="Web Server\nPHP 8.1", fillcolor="#50E3C2"];
    Database [label="MySQL\nDatabase", shape=cylinder, fillcolor="#F5A623"];
    FileSystem [label="File System\nuploads/", shape=folder, fillcolor="#BD10E0", fontcolor=white];
    
    Client -> WebServer [label="HTTP Request"];
    WebServer -> Database [label="SQL Query"];
    WebServer -> FileSystem [label="Read/Write"];
    WebServer -> Client [label="HTTP Response"];
}
```

---

## 2. Deployment Architecture

```dot
digraph DeploymentArchitecture {
    rankdir=LR;
    node [shape=box, style=filled];
    
    User [label="User", fillcolor="#4A90E2", fontcolor=white];
    LB [label="Load Balancer\n(Optional)", fillcolor="#F5A623"];
    WS1 [label="Web Server 1", fillcolor="#50E3C2"];
    WS2 [label="Web Server 2", fillcolor="#50E3C2"];
    DB [label="Primary DB", shape=cylinder, fillcolor="#D0021B", fontcolor=white];
    DBR [label="Replica DB\n(Read Only)", shape=cylinder, fillcolor="#7ED321"];
    
    User -> LB [label="HTTPS"];
    LB -> WS1;
    LB -> WS2;
    WS1 -> DB;
    WS2 -> DB;
    DB -> DBR [style=dashed];
}
```

---

## 3. Entity Relationship Diagram

```dot
digraph ERDiagram {
    rankdir=LR;
    node [shape=record, style=filled, fillcolor=lightblue];
    
    mahasiswa [label="{mahasiswa|id_mahasiswa (PK)\lnpm (UK)\lnama\lemail\lprodi\lpassword\lfoto_profil\lcreated_at\l}"];
    
    dosen [label="{dosen|id_dosen (PK)\lkode_dosen (UK)\lnama_dosen\lemail\lpassword\lfoto_profil\lkeahlian\lcreated_at\l}"];
    
    antrian [label="{antrian|id_antrian (PK)\lid_mahasiswa (FK)\lid_dosen (FK)\lnomor_antrian\ltanggal\lwaktu_mulai\lwaktu_panggil\lwaktu_kehadiran\ltopik\ldeskripsi\lfile_dokumen (NEW)\lwaktu_booking\lstatus\l}", fillcolor=lightyellow];
    
    users [label="{users|id_user (PK)\lusername\lpassword\lrole\l}", fillcolor=lightgreen];
    
    mahasiswa -> antrian [label="1:N\nmembuat", dir=both, arrowtail=crow, arrowhead=crow];
    dosen -> antrian [label="1:N\nmelayani", dir=both, arrowtail=crow, arrowhead=crow];
}
```

---

## 4. Use Case Diagram

```dot
digraph UseCaseDiagram {
    rankdir=LR;
    node [shape=ellipse, style=filled, fillcolor=white];
    
    // Actors
    M [label="Mahasiswa", shape=box, fillcolor="#4A90E2", fontcolor=white];
    D [label="Dosen", shape=box, fillcolor="#50E3C2"];
    A [label="Admin", shape=box, fillcolor="#F5A623"];
    
    // Use Cases - Mahasiswa
    UC1 [label="Login"];
    UC2 [label="Booking Bimbingan"];
    UC3 [label="Upload Dokumen"];
    UC4 [label="Scan QR Code"];
    UC5 [label="Lihat Antrian"];
    UC6 [label="Lihat Riwayat"];
    UC7 [label="Lihat Detail"];
    UC8 [label="Upload Foto Profil"];
    
    // Use Cases - Dosen
    UC9 [label="Generate QR Code"];
    UC10 [label="Panggil Mahasiswa"];
    UC11 [label="Selesaikan Bimbingan"];
    UC12 [label="Beri Feedback"];
    UC13 [label="Lihat Dashboard"];
    
    // Use Cases - Admin
    UC14 [label="Kelola Mahasiswa"];
    UC15 [label="Kelola Dosen"];
    UC16 [label="Kelola Admin"];
    
    // Mahasiswa connections
    M -> {UC1 UC2 UC3 UC4 UC5 UC6 UC7 UC8};
    
    // Dosen connections  
    D -> {UC1 UC9 UC10 UC11 UC12 UC13 UC8};
    
    // Admin connections
    A -> {UC1 UC14 UC15 UC16};
}
```

---

## 5. Login Flow

```dot
digraph LoginFlow {
    rankdir=TB;
    node [shape=box, style=rounded];
    
    start [label="Buka Aplikasi", shape=ellipse, fillcolor="#4A90E2", fontcolor=white, style=filled];
    check [label="Sudah Login?", shape=diamond, fillcolor=yellow, style=filled];
    dashboard [label="Dashboard", fillcolor="#50E3C2", style=filled];
    form [label="Form Login"];
    input [label="Input NPM & Password"];
    submit [label="Submit"];
    validate [label="Valid?", shape=diamond, fillcolor=yellow, style=filled];
    error [label="Tampilkan Error", fillcolor="#D0021B", fontcolor=white, style=filled];
    session [label="Buat Session", fillcolor="#7ED321", style=filled];
    
    start -> check;
    check -> dashboard [label="Ya"];
    check -> form [label="Tidak"];
    form -> input;
    input -> submit;
    submit -> validate;
    validate -> error [label="Tidak"];
    error -> form;
    validate -> session [label="Ya"];
    session -> dashboard;
}
```

---

## 6. Booking Flow

```dot
digraph BookingFlow {
    rankdir=TB;
    node [shape=box, style=rounded];
    
    start [label="Click Booking", shape=ellipse, fillcolor="#4A90E2", fontcolor=white, style=filled];
    form [label="Fill Form"];
    upload [label="Upload File?", shape=diamond, fillcolor=yellow, style=filled];
    selectFile [label="Select File"];
    validate [label="Valid?", shape=diamond, fillcolor=yellow, style=filled];
    preview [label="Preview File", fillcolor="#50E3C2", style=filled];
    submit [label="Submit Form"];
    serverValidate [label="Server Validate"];
    validInput [label="Valid Input?", shape=diamond, fillcolor=yellow, style=filled];
    saveFile [label="Save File", fillcolor="#7ED321", style=filled];
    genNumber [label="Generate No. Antrian"];
    insertDB [label="Insert Database", fillcolor=orange, style=filled];
    success [label="Success", shape=ellipse, fillcolor="#7ED321", style=filled];
    
    start -> form;
    form -> upload;
    upload -> selectFile [label="Ya"];
    upload -> submit [label="Tidak"];
    selectFile -> validate;
    validate -> selectFile [label="Tidak\n(Show Error)"];
    validate -> preview [label="Ya"];
    preview -> submit;
    submit -> serverValidate;
    serverValidate -> validInput;
    validInput -> form [label="Tidak"];
    validInput -> saveFile [label="Ya"];
    saveFile -> genNumber;
    genNumber -> insertDB;
    insertDB -> success;
}
```

---

## 7. QR Scan Flow

```dot
digraph QRScanFlow {
    rankdir=TB;
    node [shape=box, style=rounded];
    
    start [label="Klik Scan QR", shape=ellipse, fillcolor="#4A90E2", fontcolor=white, style=filled];
    request [label="Request Camera"];
    permission [label="Permission\nGranted?", shape=diamond, fillcolor=yellow, style=filled];
    activateCamera [label="Activate Camera", fillcolor="#50E3C2", style=filled];
    detect [label="Detect QR Code"];
    decode [label="Decode Token"];
    post [label="POST ke Server"];
    serverValidate [label="Server Validate"];
    valid [label="Valid?", shape=diamond, fillcolor=yellow, style=filled];
    updateStatus [label="Update Status", fillcolor="#7ED321", style=filled];
    alertSuccess [label="Alert Success"];
    reload [label="Reload Dashboard", shape=ellipse, fillcolor="#7ED321", style=filled];
    error [label="Show Error", fillcolor="#D0021B", fontcolor=white, style=filled];
    back [label="Back to Dashboard", shape=ellipse];
    
    start -> request;
    request -> permission;
    permission -> error [label="Denied"];
    permission -> activateCamera [label="Granted"];
    error -> back;
    activateCamera -> detect;
    detect -> decode;
    decode -> post;
    post -> serverValidate;
    serverValidate -> valid;
    valid -> error [label="Tidak"];
    valid -> updateStatus [label="Ya"];
    updateStatus -> alertSuccess;
    alertSuccess -> reload;
}
```

---

## 8. Cancel Booking Flow **[NEW]**

```dot
digraph CancelBookingFlow {
    rankdir=TB;
    node [shape=box, style="rounded,filled"];
    
    start [label="Mahasiswa Lihat\nAntrian Menunggu", shape=ellipse, fillcolor="#4A90E2", fontcolor=white];
    checkStatus [label="Status =\n'menunggu'?", shape=diamond, fillcolor=yellow];
    showButton [label="Show Button\n'Batalkan'", fillcolor="#50E3C2"];
    hideButton [label="Hide Button"];
    clickCancel [label="Klik 'Batalkan'"];
    confirm [label="Konfirmasi Dialog:\nYakin batalkan?", shape=diamond, fillcolor=yellow];
    userCancel [label="User Decline", shape=ellipse];
    loading [label="Loading State:\n'Membatalkan...'"];
    post [label="POST ke\ncancel_booking.php"];
    serverValidate [label="Server Validate:\n1. Session check\n2. Ownership check\n3. Status check"];
    valid [label="Valid?", shape=diamond, fillcolor=yellow];
    errorMsg [label="Show Error Alert", fillcolor="#D0021B", fontcolor=white];
    deleteDB [label="DELETE FROM antrian\nWHERE id_antrian", fillcolor=orange];
    success [label="Alert:\n'Booking berhasil\ndibatalkan'", fillcolor="#7ED321"];
    reload [label="location.reload()", shape=ellipse, fillcolor="#7ED321"];
    
    start -> checkStatus;
    checkStatus -> showButton [label="Ya"];
    checkStatus -> hideButton [label="Tidak"];
    hideButton -> start [style=dashed];
    showButton -> clickCancel;
    clickCancel -> confirm;
    confirm -> userCancel [label="Cancel"];
    confirm -> loading [label="OK"];
    userCancel -> start [style=dashed];
    loading -> post;
    post -> serverValidate;
    serverValidate -> valid;
    valid -> errorMsg [label="Tidak"];
    errorMsg -> start [style=dashed];
    valid -> deleteDB [label="Ya"];
    deleteDB -> success;
    success -> reload;
}
```

---

## 9. Status State Diagram

```dot
digraph StatusStateDiagram {
    rankdir=LR;
    node [shape=box, style="rounded,filled"];
    
    // States
    start [label="Start", shape=ellipse, fillcolor=white];
    menunggu [label="Menunggu", fillcolor="#FFF9C4"];
    dipanggil [label="Dipanggil", fillcolor="#B2EBF2"];
    proses [label="Proses", fillcolor="#C5E1A5"];
    selesai [label="Selesai", fillcolor="#A5D6A7"];
    revisi [label="Revisi", fillcolor="#FFCC80"];
    dilewati [label="Dilewati", fillcolor="#FFCDD2"];
    end [label="End", shape=ellipse, fillcolor=white];
    
    // Transitions
    start -> menunggu [label="Booking Created"];
    menunggu -> dipanggil [label="Dosen Panggil"];
    dipanggil -> proses [label="Mahasiswa\nScan QR"];
    dipanggil -> dilewati [label="Timeout\n60 min"];
    proses -> selesai [label="Dosen Selesaikan"];
    proses -> revisi [label="Perlu Revisi"];
    dilewati -> dipanggil [label="Panggil Ulang"];
    selesai -> end;
    revisi -> end;
    dilewati -> end;
}
```

---

## 10. Dashboard Dosen Load Flow

```dot
digraph DashboardDosenLoad {
    rankdir=TB;
    node [shape=box, style=rounded];
    
    start [label="Login Dosen", shape=ellipse, fillcolor="#50E3C2", style=filled];
    loadDashboard [label="Load Dashboard"];
    timeout [label="Run Timeout Check", fillcolor=orange, style=filled];
    stats [label="Load Statistics"];
    antrian [label="Load Antrian List"];
    qr [label="Generate QR Code", fillcolor="#7ED321", style=filled];
    timer [label="Start Timer"];
    ready [label="Dashboard Ready", shape=ellipse, fillcolor="#7ED321", style=filled];
    
    start -> loadDashboard;
    loadDashboard -> timeout;
    timeout -> stats;
    stats -> antrian;
    antrian -> qr;
    qr -> timer;
    timer -> ready;
}
```

---

## 11. Complete Booking Process (Activity Diagram)

```dot
digraph CompleteBookingProcess {
    rankdir=TB;
    node [shape=box, style=rounded];
    
    start [label="Mahasiswa Login", shape=ellipse, fillcolor="#4A90E2", fontcolor=white, style=filled];
    openDashboard [label="Open Dashboard"];
    clickBooking [label="Click Booking"];
    fillForm [label="Fill Form"];
    submitForm [label="Submit Form"];
    serverValidate [label="Server Validate"];
    saveFile [label="Save File", fillcolor="#50E3C2", style=filled];
    generateNo [label="Generate No. Antrian"];
    insertDB [label="Insert Database", fillcolor=orange, style=filled];
    statusMenunggu [label="Status: Menunggu", fillcolor="#FFF9C4", style=filled];
    dosenPanggil [label="Dosen Click Panggil"];
    statusDipanggil [label="Status: Dipanggil", fillcolor="#B2EBF2", style=filled];
    mahasiswaScan [label="Mahasiswa Scan QR"];
    qrValid [label="QR Valid?", shape=diamond, fillcolor=yellow, style=filled];
    statusProses [label="Status: Proses", fillcolor="#C5E1A5", style=filled];
    bimbinganSelesai [label="Bimbingan Selesai"];
    dosenFeedback [label="Dosen Input Feedback"];
    statusSelesai [label="Status: Selesai", fillcolor="#A5D6A7", style=filled];
    end [label="End", shape=ellipse, fillcolor="#7ED321", style=filled];
    errorQR [label="Show Error", fillcolor="#D0021B", fontcolor=white, style=filled];
    
    start -> openDashboard;
    openDashboard -> clickBooking;
    clickBooking -> fillForm;
    fillForm -> submitForm;
    submitForm -> serverValidate;
    serverValidate -> saveFile;
    saveFile -> generateNo;
    generateNo -> insertDB;
    insertDB -> statusMenunggu;
    statusMenunggu -> dosenPanggil;
    dosenPanggil -> statusDipanggil;
    statusDipanggil -> mahasiswaScan;
    mahasiswaScan -> qrValid;
    qrValid -> errorQR [label="Tidak"];
    errorQR -> mahasiswaScan;
    qrValid -> statusProses [label="Ya"];
    statusProses -> bimbinganSelesai;
    bimbinganSelesai -> dosenFeedback;
    dosenFeedback -> statusSelesai;
    statusSelesai -> end;
}
```

---

## 📝 Cara Menggunakan

1. **Pilih diagram** yang ingin divisualisasi
2. **Copy kode DOT** dari diagram tersebut
3. **Buka Graphviz Online:** https://dreampuf.github.io/GraphvizOnline/
4. **Paste kode** di editor
5. **Lihat hasil render** di sebelah kanan
6. **Export** sebagai PNG/SVG jika perlu

---

## 🎨 Keterangan Warna

| Warna | Hex Code | Penggunaan |
|-------|----------|------------|
| Biru | #4A90E2 | Mahasiswa / User |
| Hijau Muda | #50E3C2 | Dosen / Web Server |
| Orange | #F5A623 | Admin / Load Balancer |
| Merah | #D0021B | Database Primary / Error |
| Hijau | #7ED321 | Success / Replica DB |
| Ungu | #BD10E0 | File System |
| Kuning | Yellow | Decision / Kondisi |

---

## 🔧 Tips Graphviz

### Mengubah Layout
```dot
// Horizontal
rankdir=LR;

// Vertical  
rankdir=TB;
```

### Node Shapes
```dot
shape=box         // Kotak
shape=ellipse     // Oval
shape=diamond     // Diamond (untuk kondisi)
shape=cylinder    // Silinder (untuk database)
shape=folder      // Folder
shape=record      // Tabel (untuk ER diagram)
```

### Styling
```dot
style=filled              // Isi warna
style=rounded            // Sudut membulat
style="rounded,filled"   // Kombinasi
fillcolor="#HEXCODE"     // Warna isi
fontcolor=white          // Warna teks
```

---

Semua diagram ini merepresentasikan arsitektur dan workflow lengkap aplikasi BimCheck.
