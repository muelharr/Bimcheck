# 🏗️ Arsitektur Sistem BimCheck

Dokumentasi arsitektur dan design pattern yang digunakan dalam aplikasi BimCheck.

---

## 📑 Daftar Isi

- [Arsitektur Tingkat Tinggi](#arsitektur-tingkat-tinggi)
- [Arsitektur Aplikasi](#arsitektur-aplikasi)
- [Design Patterns](#design-patterns)
- [Technology Stack](#technology-stack)
- [Security Architecture](#security-architecture)

---

## 🌐 Arsitektur Tingkat Tinggi

### System Architecture Diagram

**Visualize at:** https://dreampuf.github.io/GraphvizOnline/

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

### Deployment Architecture

**Visualize at:** https://dreampuf.github.io/GraphvizOnline/

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

## 📐 Arsitektur Aplikasi

### Layered Architecture

BimCheck menggunakan arsitektur berlapis (layered architecture):

```
┌─────────────────────────────────────┐
│   Presentation Layer (Views)        │
│   - dashboard_mahasiswa.php         │
│   - dashboard_dosen.php             │
│   - login.php                       │
└─────────────────────────────────────┘
              ↓↑
┌─────────────────────────────────────┐
│   Business Logic Layer (Actions)    │
│   - validasi_qr.php                 │
│   - update_status.php               │
│   - upload_dokumen.php              │
└─────────────────────────────────────┘
              ↓↑
┌─────────────────────────────────────┐
│   Data Access Layer (Config)        │
│   - koneksi.php                     │
│   - mysqli queries                  │
└─────────────────────────────────────┘
              ↓↑
┌─────────────────────────────────────┐
│   Database Layer (MySQL)            │
│   - Tables: antrian, mahasiswa,     │
│     dosen, users                    │
└─────────────────────────────────────┘
```

### MVC-like Pattern

Meskipun tidak pure MVC, aplikasi ini mengikuti prinsip separation of concerns:

**Model** → Implicit dalam queries di setiap file
**View** → File PHP dengan HTML/CSS/JavaScript
**Controller** → Actions folder + logic di dalam views

---

## 🎯 Design Patterns

### 1. Front Controller Pattern

**Implementation:** `index.php`

```php
// index.php sebagai entry point
if (!isset($_SESSION['status'])) {
    // Not logged in
    include 'views/login.php';
} else {
    // Route based on role
    switch($_SESSION['role']) {
        case 'mahasiswa': 
            include 'views/dashboard_mahasiswa.php';
            break;
        case 'dosen':
            include 'views/dashboard_dosen.php';
            break;
        // ...
    }
}
```

**Benefits:**
- Centralized routing
- Session checking
- Easy to add middleware

---

### 2. Repository Pattern (Simplified)

**Current Implementation:** Direct mysqli queries

**Example:**
```php
// views/dashboard_mahasiswa.php
$qActive = mysqli_query($conn, "
    SELECT a.*, d.nama_dosen 
    FROM antrian a 
    JOIN dosen d ON a.id_dosen = d.id_dosen 
    WHERE a.id_mahasiswa = '$id_mahasiswa'
");
```

**Recommended:** Extract to repository classes
```php
// Recommendation
class AntrianRepository {
    public function getActiveQueue($id_mahasiswa) {
        // Query logic here
    }
}
```

---

### 3. Strategy Pattern for Status Updates

**Used in:** `actions/update_status.php`

Different strategies for different actions:

```php
switch($action) {
    case 'panggil':
        // Strategy: Call student
        $query = "UPDATE antrian SET 
                  status='dipanggil', 
                  waktu_panggil=NOW() 
                  WHERE id_antrian='$id'";
        break;
        
    case 'selesai':
        // Strategy: Complete booking
        $query = "UPDATE antrian SET 
                  status='selesai', 
                  deskripsi=CONCAT(deskripsi, '$feedback')
                  WHERE id_antrian='$id'";
        break;
}
```

---

### 4. Observer Pattern (QR Scanning)

**Flow:**
1. Dosen generates QR (Observable)
2. Mahasiswa scans QR (Observer)
3. Server validates & updates status (Event Handler)
4. Dashboard refreshes (UI Update)

```mermaid
sequenceDiagram
    participant D as Dosen
    participant S as Server
    participant M as Mahasiswa
    
    D->>S: Generate QR Token
    S-->>D: Display QR Code
    M->>D: Scan QR Code
    M->>S: POST {qr_content}
    S->>S: Validate Token
    S->>S: Update Status
    S-->>M: Success Response
    M->>M: Refresh Dashboard
```

---

## 🔧 Technology Stack Detail

### Backend Stack

```yaml
Language: PHP 8.1+
  Features Used:
    - Type declarations
    - Arrow functions
    - Null coalescing operator
    - Spread operator
    
Database: MySQL 8.0+
  Features Used:
    - Foreign Keys
    - Indexes (composite)
    - ENUM types
    - DATETIME functions
    - JSON (recommended)
    
Authentication: Session-based
  Storage: Server filesystem
  Security: HTTP-only cookies
```

### Frontend Stack

```yaml
HTML5:
  - Semantic elements
  - Form validation
  - Input types (date, time, file)
  
CSS: TailwindCSS 3.x (CDN)
  - Utility-first
  - Responsive utilities
  - Custom color palette
  - Gradient backgrounds
  
JavaScript ES6+:
  - Fetch API
  - Promises
  - Template literals
  - Arrow functions
  - Destructuring
```

### Libraries & Dependencies

| Library | Version | Purpose |
|---------|---------|---------|
| Html5-QRCode | 2.3.8 | QR Scanner (mahasiswa) |
| QRCode.js | 1.0.0 | QR Generator (dosen) |
| Font Awesome | 6.4.0 | Icons |
| TailwindCSS | 3.x | CSS Framework |

---

## 🔐 Security Architecture

### Authentication Flow

```mermaid
graph TD
    Start([User Access]) --> Login{Logged In?}
    Login -->|No| ShowLogin[Show Login Page]
    ShowLogin --> Submit[Submit Credentials]
    Submit --> Validate{Valid?}
    Validate -->|No| Error[Show Error]
    Error --> ShowLogin
    Validate -->|Yes| CreateSession[Create Session]
    CreateSession --> CheckRole{Check Role}
    
    Login -->|Yes| CheckRole
    CheckRole -->|Mahasiswa| DashMhs[Dashboard Mahasiswa]
    CheckRole -->|Dosen| DashDosen[Dashboard Dosen]
    CheckRole -->|Admin| DashAdmin[Dashboard Admin]
    
    style Start fill:#4A90E2
    style CreateSession fill:#50E3C2
    style DashMhs fill:#F5A623
    style DashDosen fill:#7ED321
    style DashAdmin fill:#BD10E0
```

### Authorization Layers

```
Request → Session Check → Role Validation → Resource Access
   ↓           ↓               ↓                 ↓
 HTTP      isset()      $_SESSION['role']    Allow/Deny
Request    status?         == expected?       
```

### Password Hashing

```php
// Registration
$hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

// Login
if (password_verify($inputPassword, $hashedPassword)) {
    // Success
}
```

### File Upload Security

```
Upload Request
    ↓
Type Validation (extension whitelist)
    ↓
Size Validation (max 5MB)
    ↓
Generate Unique Filename (timestamp + random)
    ↓
Store in uploads/ (outside public if possible)
    ↓
Save path in DB (relative path)
    ↓
Return Success
```

---

## 📊 Data Flow Architecture

### Booking Flow

```mermaid
graph LR
    A[Mahasiswa<br/>Fill Form] --> B[Upload File<br/>Optional]
    B --> C[Submit Form]
    C --> D{Server<br/>Validate}
    D -->|Invalid| E[Show Error]
    E --> A
    D -->|Valid| F[Save File]
    F --> G[Insert DB]
    G --> H[Return Success]
    H --> I[Refresh Dashboard]
    
    style A fill:#4A90E2
    style F fill:#F5A623
    style G fill:#D0021B
    style I fill:#50E3C2
```

### QR Validation Flow

```mermaid
sequenceDiagram
    participant M as Mahasiswa
    participant C as Camera
    participant S as Server
    participant DB as Database
    
    M->>C: Open Camera
    C->>M: Stream Video
    M->>C: Scan QR Code
    C->>M: Decode: "idDosen|timestamp"
    M->>S: POST /validasi_qr.php
    S->>S: Parse Token
    S->>S: Validate Timestamp (±5min)
    S->>DB: Check Antrian Status
    DB-->>S: Return Status
    S->>S: Verify = 'dipanggil'
    S->>DB: UPDATE status='proses'
    DB-->>S: Success
    S-->>M: JSON Success
    M->>M: Reload Dashboard
```

---

## 🗂️ File Organization

### Directory Structure Philosophy

```
Separation of Concerns:
├── /actions     → Backend logic (controllers)
├── /config      → Configuration (database)
├── /views       → Frontend (presentation)
├── /assets      → Static resources
├── /uploads     → User-generated content
└── /dokumentasi → Technical documentation
```

### Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| Database | snake_case | `id_mahasiswa`, `waktu_panggil` |
| PHP Variables | camelCase | `$idDosen`, `$nomorAntrian` |
| PHP Files | snake_case | `dashboard_mahasiswa.php` |
| CSS Classes | kebab-case | `bg-blue-500`, `rounded-lg` |
| JavaScript | camelCase | `openDetailModal()` |

---

## 🚀 Performance Architecture

### Database Optimization

**Indexes:**
```sql
-- Single column indexes
KEY `idx_tanggal` (`tanggal`)
KEY `idx_status` (`status`)

-- Composite indexes (lebih efisien)
KEY `idx_tanggal_status` (`tanggal`,`status`)
KEY `idx_id_dosen_tanggal` (`id_dosen`,`tanggal`)
```

**Query Optimization:**
- Use JOINs instead of subqueries
- WHERE dengan indexed columns
- LIMIT untuk pagination
- Avoid SELECT *

### Caching Strategy (Recommended)

```
┌────────────┐
│   Client   │
└─────┬──────┘
      │ Request
      ↓
┌────────────┐
│   Cache    │  ← Redis/Memcached
│  (Session) │     (Recommended)
└─────┬──────┘
      │ Cache Miss
      ↓
┌────────────┐
│  Database  │
└────────────┘
```

---

## 📈 Scalability Considerations

### Horizontal Scaling

**Current:** Single server
**Recommended:**
- Load balancer (Nginx)
- Multiple PHP-FPM workers
- Database replication (master-slave)
- Shared session storage (Redis)

### Vertical Scaling

**PHP Configuration:**
```ini
memory_limit = 256M
max_execution_time = 60
upload_max_filesize = 5M
post_max_size = 8M
```

---

## 🔄 API Architecture (Future)

**Recommended:** RESTful API separation

```
/api/v1/
  ├── /auth
  │   ├── POST /login
  │   └── POST /logout
  ├── /antrian
  │   ├── GET /
  │   ├── POST /
  │   └── PUT /:id
  ├── /qr
  │   ├── GET /generate
  │   └── POST /validate
  └── /users
      ├── GET /
      ├── POST /
      ├── PUT /:id
      └── DELETE /:id
```

---

Dokumentasi arsitektur ini menjelaskan design decisions dan best practices yang digunakan dalam BimCheck.
