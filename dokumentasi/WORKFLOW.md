# 🔄 Workflow dan Flowchart - BimCheck

Dokumentasi lengkap alur kerja sistem dengan flowchart dalam format Graphviz DOT.

**Visualisasi:** https://dreampuf.github.io/GraphvizOnline/

---

## 📑 Daftar Isi

- [Use Case Diagram](#1-use-case-diagram)
- [Workflow Mahasiswa](#2-workflow-mahasiswa)
- [Workflow Dosen](#3-workflow-dos en)
- [Workflow Admin](#4-workflow-admin)
- [State Diagram](#5-state-diagram)
- [Activity Diagrams](#6-activity-diagrams)

---

## 1. Use Case Diagram

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

## 2. Workflow Mahasiswa

### 2.1. Login Flow

```dot
digraph LoginFlow {
    rankdir=TB;
    node [shape=box, style="rounded,filled"];
    
    start [label="Buka Aplikasi", shape=ellipse, fillcolor="#4A90E2", fontcolor=white];
    check [label="Sudah Login?", shape=diamond, fillcolor=yellow];
    dashboard [label="Dashboard Mahasiswa", fillcolor="#50E3C2"];
    form [label="Form Login"];
    input [label="Input NPM & Password"];
    submit [label="Submit"];
    validate [label="Valid?", shape=diamond, fillcolor=yellow];
    error [label="Tampilkan Error", fillcolor="#D0021B", fontcolor=white];
    session [label="Buat Session", fillcolor="#7ED321"];
    
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

### 2.2. Booking Bimbingan Flow (Lengkap)

```dot
digraph BookingFlow {
    rankdir=TB;
    node [shape=box, style="rounded,filled"];
    
    start [label="Click Booking", shape=ellipse, fillcolor="#4A90E2", fontcolor=white];
    form [label="Tampilkan Form"];
    selectDosen [label="Pilih Dosen"];
    inputTopik [label="Input Topik"];
    inputDeskripsi [label="Input Deskripsi\n(Optional)"];
    inputTanggal [label="Pilih Tanggal"];
    inputWaktu [label="Pilih Waktu"];
    upload [label="Upload File?", shape=diamond, fillcolor=yellow];
    selectFile [label="Select File"];
    validateFile [label="Valid File?\n(Type & Size)", shape=diamond, fillcolor=yellow];
    errorFile [label="Show Error\nInvalid File", fillcolor="#D0021B", fontcolor=white];
    preview [label="Preview File", fillcolor="#50E3C2"];
    submit [label="Submit Form"];
    serverValidate [label="Server Validate\nAll Input"];
    validInput [label="Valid?", shape=diamond, fillcolor=yellow];
    errorInput [label="Alert Error", fillcolor="#D0021B", fontcolor=white];
    saveFile [label="Save File\nto uploads/", fillcolor="#7ED321"];
    genNumber [label="Generate\nNomor Antrian"];
    insertDB [label="INSERT INTO\nantrian", fillcolor=orange];
    success [label="Success!\nReload Page", shape=ellipse, fillcolor="#7ED321"];
    
    start -> form;
    form -> selectDosen;
    selectDosen -> inputTopik;
    inputTopik -> inputDeskripsi;
    inputDeskripsi -> inputTanggal;
    inputTanggal -> inputWaktu;
    inputWaktu -> upload;
    upload -> selectFile [label="Ya"];
    upload -> submit [label="Tidak"];
    selectFile -> validateFile;
    validateFile -> errorFile [label="Tidak"];
    errorFile -> selectFile;
    validateFile -> preview [label="Ya"];
    preview -> submit;
    submit -> serverValidate;
    serverValidate -> validInput;
    validInput -> errorInput [label="Tidak"];
    errorInput -> form;
    validInput -> saveFile [label="Ya"];
    saveFile -> genNumber;
    genNumber -> insertDB;
    insertDB -> success;
}
```

---

### 2.3. QR Code Scan Flow (Lengkap)

```dot
digraph QRScanFlow {
    rankdir=TB;
    node [shape=box, style="rounded,filled"];
    
    start [label="Klik Scan QR", shape=ellipse, fillcolor="#4A90E2", fontcolor=white];
    request [label="Request\nCamera Access"];
    permission [label="Permission\nGranted?", shape=diamond, fillcolor=yellow];
    denied [label="Show Error\nCamera Denied", fillcolor="#D0021B", fontcolor=white];
    activate [label="Activate Camera", fillcolor="#50E3C2"];
    detect [label="Detect QR Code"];
    decode [label="Decode Token\nidDosen|timestamp"];
    post [label="POST ke\nvalidasi_qr.php"];
    serverCheck [label="Server Validate:\n1. Parse Token\n2. Check Timestamp\n3. Check Antrian"];
    valid [label="Valid?", shape=diamond, fillcolor=yellow];
    errorQR [label="Alert Error\nQR Invalid", fillcolor="#D0021B", fontcolor=white];
    updateStatus [label="UPDATE\nstatus='proses'\nwaktu_kehadiran=NOW()", fillcolor="#7ED321"];
    alertSuccess [label="Alert Success"];
    reload [label="Reload Dashboard", shape=ellipse, fillcolor="#7ED321"];
    back [label="Back to Dashboard", shape=ellipse];
    
    start -> request;
    request -> permission;
    permission -> denied [label="Tidak"];
    permission -> activate [label="Ya"];
    denied -> back;
    activate -> detect;
    detect -> decode;
    decode -> post;
    post -> serverCheck;
    serverCheck -> valid;
    valid -> errorQR [label="Tidak"];
    errorQR -> back;
    valid -> updateStatus [label="Ya"];
    updateStatus -> alertSuccess;
    alertSuccess -> reload;
}
```

---

### 2.4. View Detail Flow

```dot
digraph ViewDetailFlow {
    rankdir=TB;
    node [shape=box, style="rounded,filled"];
    
    start [label="Lihat Antrian\natau Riwayat", shape=ellipse, fillcolor="#4A90E2", fontcolor=white];
    clickDetail [label="Klik Button\nDetail"];
    fetchData [label="Fetch Data\ndari Row"];
    openModal [label="Open Modal"];
    parse [label="Parse Data:\nDecode JSON"];
    checkFeedback [label="Ada Feedback?", shape=diamond, fillcolor=yellow];
    showFeedback [label="Show Feedback\nSection", fillcolor="#50E3C2"];
    hideFeedback [label="Hide Feedback\nSection"];
    displayInfo [label="Display:\nNomor, Dosen,\nTopik, Deskripsi"];
    checkFile [label="Ada File?", shape=diamond, fillcolor=yellow];
    showFile [label="Show File\nDownload Link", fillcolor="#7ED321"];
    hideFile [label="Hide File\nSection"];
    ready [label="Modal Ready", shape=ellipse, fillcolor="#7ED321"];
    
    start -> clickDetail;
    clickDetail -> fetchData;
    fetchData -> openModal;
    openModal -> parse;
    parse -> checkFeedback;
    checkFeedback -> showFeedback [label="Ya"];
    checkFeedback -> hideFeedback [label="Tidak"];
    showFeedback -> displayInfo;
    hideFeedback -> displayInfo;
    displayInfo -> checkFile;
    checkFile -> showFile [label="Ya"];
    checkFile -> hideFile [label="Tidak"];
    showFile -> ready;
    hideFile -> ready;
}
```

---

## 3. Workflow Dosen

### 3.1. Dashboard Load Flow

```dot
digraph DashboardDosenLoad {
    rankdir=TB;
    node [shape=box, style="rounded,filled"];
    
    start [label="Login Dosen", shape=ellipse, fillcolor="#50E3C2"];
    loadPage [label="Load Dashboard Page"];
    timeout [label="Run Timeout Check:\nUPDATE dilewati\nif > 60 min", fillcolor=orange];
    stats [label="Load Statistics:\n- Total Antrian\n- Sedang Bimbingan\n- Selesai Hari Ini"];
    antrian [label="Load Antrian List:\nJOIN mahasiswa"];
    checkQueue [label="Ada Antrian?", shape=diamond, fillcolor=yellow];
    showEmpty [label="Show Empty State"];
    showTable [label="Show Antrian Table"];
    qr [label="Generate QR Code:\nidDosen|timestamp", fillcolor="#7ED321"];
    timer [label="Start Countdown\nTimer 5:00"];
    autoRefresh [label="Set Auto-Refresh\nInterval"];
    ready [label="Dashboard Ready", shape=ellipse, fillcolor="#7ED321"];
    
    start -> loadPage;
    loadPage -> timeout;
    timeout -> stats;
    stats -> antrian;
    antrian -> checkQueue;
    checkQueue -> showEmpty [label="Tidak"];
    checkQueue -> showTable [label="Ya"];
    showEmpty -> qr;
    showTable -> qr;
    qr -> timer;
    timer -> autoRefresh;
    autoRefresh -> ready;
}
```

---

### 3.2. Call Student (Panggil Mahasiswa) Flow

```dot
digraph CallStudentFlow {
    rankdir=TB;
    node [shape=box, style="rounded,filled"];
    
    start [label="Dosen Lihat\nAntrian Menunggu", shape=ellipse, fillcolor="#50E3C2"];
    clickPanggil [label="Klik Button\n'Panggil'"];
    confirm [label="Konfirmasi?", shape=diamond, fillcolor=yellow];
    post [label="POST ke\nupdate_status.php\naction='panggil'"];
    serverUpdate [label="UPDATE antrian SET\nstatus='dipanggil'\nwaktu_panggil=NOW()"];
    checkSuccess [label="Success?", shape=diamond, fillcolor=yellow];
    error [label="Show Error", fillcolor="#D0021B", fontcolor=white];
    success [label="Show Success\nAlert"];
    reload [label="Reload Page"];
    updateUI [label="UI Update:\n- Badge 'Dipanggil'\n- Start Timeout\n- Show Countdown", fillcolor="#7ED321"];
    notifyMhs [label="Mahasiswa Dashboard\nUpdate Status\n(via refresh)"];
    ready [label="Ready for Scan", shape=ellipse, fillcolor="#7ED321"];
    
    start -> clickPanggil;
    clickPanggil -> confirm;
    confirm -> post [label="Ya"];
    confirm -> start [label="Tidak"];
    post -> serverUpdate;
    serverUpdate -> checkSuccess;
    checkSuccess -> error [label="Tidak"];
    checkSuccess -> success [label="Ya"];
    error -> start;
    success -> reload;
    reload -> updateUI;
    updateUI -> notifyMhs;
    notifyMhs -> ready;
}
```

---

### 3.3. QR Generation & Auto-Refresh Flow

```dot
digraph QRGenerationFlow {
    rankdir=TB;
    node [shape=box, style="rounded,filled"];
    
    pageLoad [label="Page Load", shape=ellipse, fillcolor="#50E3C2"];
    getID [label="Get id_dosen\nfrom session"];
    genToken [label="Generate Token:\ntimestamp = floor(now/300)"];
    createQR [label="Create QR Code:\nqrToken = idDosen|timestamp", fillcolor="#7ED321"];
    display [label="Display QR\nto DOM"];
    startCountdown [label="Start Countdown\nTimer 5:00"];
    decrement [label="Decrement Timer\nevery 1 second"];
    checkTimer [label="Timer = 0?", shape=diamond, fillcolor=yellow];
    autoRegen [label="Auto Regenerate:\nGenerateQRCode()", fillcolor=orange];
    
    manualRefresh [label="Manual Refresh\nButton Clicked", shape=ellipse, fillcolor="#4A90E2", fontcolor=white];
    
    pageLoad -> getID;
    getID -> genToken;
    genToken -> createQR;
    createQR -> display;
    display -> startCountdown;
    startCountdown -> decrement;
    decrement -> checkTimer;
    checkTimer -> decrement [label="Tidak"];
    checkTimer -> autoRegen [label="Ya"];
    autoRegen -> genToken;
    
    manualRefresh -> genToken [style=dashed, color=blue];
}
```

---

### 3.4. Complete Bimbingan (Selesaikan) Flow

```dot
digraph CompleteBimbinganFlow {
    rankdir=TB;
    node [shape=box, style="rounded,filled"];
    
    start [label="Mahasiswa di\nStatus 'Proses'", shape=ellipse, fillcolor="#50E3C2"];
    clickSelesai [label="Dosen Klik\n'Selesai'"];
    openModal [label="Open Modal\nFeedback"];
    showForm [label="Show Form:\nTextarea Feedback\n(Optional)"];
    inputFeedback [label="Dosen Input\nFeedback"];
    clickSimpan [label="Klik 'Selesai\n& Simpan'"];
    validateInput [label="Validate Input"];
    post [label="POST ke\nupdate_status.php\naction='selesai'"];
    formatFeedback [label="Format Feedback:\nAppend '[Feedback Dosen: ...]'"];
    updateDB [label="UPDATE antrian SET\nstatus='selesai'\ndeskripsi=CONCAT(...)", fillcolor=orange];
    checkSuccess [label="Success?", shape=diamond, fillcolor=yellow];
    error [label="Show Error", fillcolor="#D0021B", fontcolor=white];
    closeModal [label="Close Modal"];
    reload [label="Reload Dashboard"];
    success [label="Bimbingan Selesai", shape=ellipse, fillcolor="#7ED321"];
    
    start -> clickSelesai;
    clickSelesai -> openModal;
    openModal -> showForm;
    showForm -> inputFeedback;
    inputFeedback -> clickSimpan;
    clickSimpan -> validateInput;
    validateInput -> post;
    post -> formatFeedback;
    formatFeedback -> updateDB;
    updateDB -> checkSuccess;
    checkSuccess -> error [label="Tidak"];
    checkSuccess -> closeModal [label="Ya"];
    error -> showForm;
    closeModal -> reload;
    reload -> success;
}
```

---

## 4. Workflow Admin

### 4.1. CRUD Operations Flow

```dot
digraph AdminCRUDFlow {
    rankdir=TB;
    node [shape=box, style="rounded,filled"];
    
    start [label="Admin Dashboard", shape=ellipse, fillcolor="#F5A623"];
    selectAction [label="Select Action", shape=diamond, fillcolor=yellow];
    
    // CREATE
    create [label="Create User"];
    createForm [label="Fill Form:\nUsername, Password,\nEmail, etc"];
    hashPwd [label="Hash Password\nBCrypt"];
    insertDB [label="INSERT INTO\nmahasiswa/dosen"];
    createSuccess [label="Success", fillcolor="#7ED321"];
    
    // UPDATE
    update [label="Update User"];
    loadData [label="Load User Data"];
    editForm [label="Edit Form"];
    updateDB [label="UPDATE\nmahasiswa/dosen"];
    updateSuccess [label="Success", fillcolor="#7ED321"];
    
    // DELETE
    delete [label="Delete User"];
    confirmDelete [label="Confirm Delete?", shape=diamond, fillcolor=yellow];
    deleteDB [label="DELETE FROM\nmahasiswa/dosen\n(CASCADE)", fillcolor="#D0021B", fontcolor=white];
    deleteSuccess [label="Success", fillcolor="#7ED321"];
    
    // VIEW
    view [label="View Users"];
    loadTable [label="Load Table Data"];
    pagination [label="Pagination"];
    display [label="Display Table"];
    viewSuccess [label="Done", fillcolor="#7ED321"];
    
    start -> selectAction;
    selectAction -> create [label="Create"];
    selectAction -> update [label="Update"];
    selectAction -> delete [label="Delete"];
    selectAction -> view [label="View"];
    
    create -> createForm;
    createForm -> hashPwd;
    hashPwd -> insertDB;
    insertDB -> createSuccess;
    createSuccess -> start;
    
    update -> loadData;
    loadData -> editForm;
    editForm -> updateDB;
    updateDB -> updateSuccess;
    updateSuccess -> start;
    
    delete -> confirmDelete;
    confirmDelete -> deleteDB [label="Ya"];
    confirmDelete -> start [label="Tidak"];
    deleteDB -> deleteSuccess;
    deleteSuccess -> start;
    
    view -> loadTable;
    loadTable -> pagination;
    pagination -> display;
    display -> viewSuccess;
    viewSuccess -> start;
}
```

---

## 5. State Diagram - Antrian Status

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
    dipanggil -> proses [label="Mahasiswa Scan QR"];
    dipanggil -> dilewati [label="Timeout 60 min"];
    proses -> selesai [label="Dosen Selesaikan"];
    proses -> revisi [label="Perlu Revisi\n(future)"];
    dilewati -> dipanggil [label="Panggil Ulang"];
    selesai -> end;
    revisi -> end;
    dilewati -> end;
}
```

**Status Transitions Table:**

| From | To | Trigger | Actor |
|------|-----|---------|-------|
| - | Menunggu | Booking dibuat | Mahasiswa |
| Menunggu | Dipanggil | Klik "Panggil" | Dosen |
| Dipanggil | Proses | Scan QR | Mahasiswa |
| Dipanggil | Dilewati | Timeout 60 min | System |
| Proses | Selesai | Klik "Selesai" | Dosen |
| Dilewati | Dipanggil | Klik "Panggil" lagi | Dosen |

---

## 6. Activity Diagrams

### 6.1. Complete Booking Process (End-to-End)

```dot
digraph CompleteBookingProcess {
    rankdir=TB;
    node [shape=box, style="rounded,filled"];
    
    start [label="Mahasiswa Login", shape=ellipse, fillcolor="#4A90E2", fontcolor=white];
    dashboard [label="Open Dashboard"];
    booking [label="Click Booking"];
    fillForm [label="Fill Form +\nUpload File"];
    submit [label="Submit"];
    validate [label="Server Validate", fillcolor=orange];
    saveFile [label="Save File"];
    genNo [label="Generate\nNo. Antrian"];
    insert [label="INSERT DB"];
    menunggu [label="Status: Menunggu", fillcolor="#FFF9C4"];
    
    dosenPanggil [label="Dosen Click Panggil"];
    dipanggil [label="Status: Dipanggil", fillcolor="#B2EBF2"];
    mhsScan [label="Mahasiswa Scan QR"];
    qrValid [label="QR Valid?", shape=diamond, fillcolor=yellow];
    errorQR [label="Error QR", fillcolor="#D0021B", fontcolor=white];
    proses [label="Status: Proses", fillcolor="#C5E1A5"];
    
    bimbingan [label="Bimbingan Berlangsung"];
    dosenSelesai [label="Dosen Input Feedback\n& Click Selesai"];
    selesai [label="Status: Selesai", fillcolor="#A5D6A7"];
    end [label="End", shape=ellipse, fillcolor="#7ED321"];
    
    start -> dashboard;
    dashboard -> booking;
    booking -> fillForm;
    fillForm -> submit;
    submit -> validate;
    validate -> saveFile;
    saveFile -> genNo;
    genNo -> insert;
    insert -> menunggu;
    menunggu -> dosenPanggil;
    dosenPanggil -> dipanggil;
    dipanggil -> mhsScan;
    mhsScan -> qrValid;
    qrValid -> errorQR [label="Tidak"];
    errorQR -> mhsScan;
    qrValid -> proses [label="Ya"];
    proses -> bimbingan;
    bimbingan -> dosenSelesai;
    dosenSelesai -> selesai;
    selesai -> end;
}
```

---

### 6.2. File Upload Process (Detail)

```dot
digraph FileUploadProcess {
    rankdir=TB;
    node [shape=box, style="rounded,filled"];
    
    start [label="File Selected", shape=ellipse, fillcolor="#4A90E2", fontcolor=white];
    getFile [label="Get File Object"];
    checkType [label="Check Type:\nPDF, DOC, DOCX,\nJPG, PNG?", shape=diamond, fillcolor=yellow];
    errorType [label="Error:\nInvalid Type", fillcolor="#D0021B", fontcolor=white];
    checkSize [label="Check Size:\nMax 5MB?", shape=diamond, fillcolor=yellow];
    errorSize [label="Error:\nToo Large", fillcolor="#D0021B", fontcolor=white];
    preview [label="Show Preview:\nName & Size", fillcolor="#50E3C2"];
    userSubmit [label="User Submit Form"];
    serverCheck [label="Server Re-Validate"];
    genFilename [label="Generate Unique\nFilename"];
    moveFile [label="move_uploaded_file()\nto uploads/dokumen_bimbingan/"];
    savePath [label="Save Path to DB:\nfile_dokumen=path"];
    success [label="Upload Success", shape=ellipse, fillcolor="#7ED321"];
    cancel [label="Cancel/Retry", shape=ellipse];
    
    start -> getFile;
    getFile -> checkType;
    checkType -> errorType [label="Tidak"];
    checkType -> checkSize [label="Ya"];
    errorType -> cancel;
    checkSize -> errorSize [label="Tidak"];
    checkSize -> preview [label="Ya"];
    errorSize -> cancel;
    preview -> userSubmit;
    userSubmit -> serverCheck;
    serverCheck -> genFilename;
    genFilename -> moveFile;
    moveFile -> savePath;
    savePath -> success;
}
```

---

### 6.3. Timeout Management Flow

```dot
digraph TimeoutManagement {
    rankdir=TB;
    node [shape=box, style="rounded,filled"];
    
    start [label="Cron / Page Load", shape=ellipse, fillcolor=orange];
    query [label="SELECT * FROM antrian\nWHERE status='dipanggil'"];
    checkRecords [label="Ada Records?", shape=diamond, fillcolor=yellow];
    noRecords [label="No Action", shape=ellipse, fillcolor=white];
    loopRecords [label="Loop Each Record"];
    calculate [label="Calculate:\nTIMESTAMPDIFF(MINUTE,\nwaktu_panggil, NOW())"];
    checkTimeout [label="Diff >= 60?", shape=diamond, fillcolor=yellow];
    update [label="UPDATE\nstatus='dilewati'", fillcolor="#D0021B", fontcolor=white];
    log [label="Log Timeout Event\n(Optional)"];
    next [label="Next Record"];
    done [label="Done", shape=ellipse, fillcolor="#7ED321"];
    
    start -> query;
    query -> checkRecords;
    checkRecords -> noRecords [label="Tidak"];
    checkRecords -> loopRecords [label="Ya"];
    loopRecords -> calculate;
    calculate -> checkTimeout;
    checkTimeout -> update [label="Ya"];
    checkTimeout -> next [label="Tidak"];
    update -> log;
    log -> next;
    next -> loopRecords;
    next -> done [style=dashed];
}
```

---

## 📋 Use Case Scenarios

### Scenario 1: Happy Path (Successful Booking)

**Actors:** Mahasiswa, Dosen, System  
**Pre-condition:** Mahasiswa sudah login

**Flow:**
1. Mahasiswa open dashboard → Click "Booking Pertemuan"
2. Fill form: Pilih dosen, topik, deskripsi, tanggal, waktu
3. Upload file: "draft_bab3.pdf" (validated ✅)
4. Submit → Server validate → File saved → Generate nomor A001
5. Status: **Menunggu** ← INSERT database
6. Dosen lihat antrian baru ← Dashboard refresh
7. Dosen klik "Panggil" → Status: **Dipanggil**
8. Mahasiswa lihat status berubah → Scan QR code
9. QR validated ✅ → Status: **Proses**
10. Bimbingan berlangsung...
11. Dosen klik "Selesai" → Input feedback: "Perbaiki metodologi"
12. Status: **Selesai** → Feedback tersimpan
13. Mahasiswa lihat feedback di riwayat ✅

**Post-condition:** Bimbingan recorded with feedback

---

### Scenario 2: Timeout (Mahasiswa Tidak Hadir)

**Flow:**
1-7. Same as Scenario 1 (sampai status "Dipanggil")
8. Mahasiswa **tidak datang** ❌
9. 60 menit berlalu...
10. System auto-check timeout → Status: **Dilewati**
11. Dosen lihat status berubah
12. Dosen bisa panggil mahasiswa lain
13. (Optional) Dosen bisa panggil ulang → Status kembali "Dipanggil"

---

### Scenario 3: File Upload Validation Error

**Flow:**
1-2. Same as Scenario 1
3. Upload "document.exe" ❌ Invalid type
4. Client validation: **Reject** → Alert error
5. Remove file → Upload "large_file.pdf" (7MB) ❌ Too large
6. Client validation: **Reject** → Alert error  
7. Compress file → Upload "draft_compressed.pdf" (3MB) ✅
8. Validation: **Pass**
9. Continue normal flow...

---

## 🔄 Process Summary

| Process | Start | End | Duration (est) |
|---------|-------|-----|----------------|
| Login | Open App | Dashboard | ~10 sec |
| Booking | Click Book | Status Menunggu | ~2 min |
| QR Scan | Request Camera | Status Proses | ~30 sec |
| Bimbingan | Proses | Selesai | ~30 min |
| Admin CRUD | Select Action | Success | ~1 min |

---

Dokumentasi workflow lengkap dengan semua flowchart dalam format Graphviz DOT.
