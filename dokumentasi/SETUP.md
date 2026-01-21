# ⚙️ Setup dan Deployment - BimCheck

Panduan lengkap instalasi, konfigurasi, dan deployment aplikasi BimCheck.

---

## 📑 Daftar Isi

- [Requirements](#requirements)
- [Instalasi Lokal](#instalasi-lokal)
- [Konfigurasi](#konfigurasi)
- [Deployment Production](#deployment-production)
- [Troubleshooting](#troubleshooting)

---

## 💻 Requirements

### Minimum Requirements

| Component | Version | Notes |
|-----------|---------|-------|
| PHP | 8.1+ | Dengan extension mysqli |
| MySQL/MariaDB | 8.0+ / 10.5+ | InnoDB engine |
| Web Server | Apache 2.4+ / Nginx | Atau PHP built-in |
| Browser | Chrome 90+ / Firefox 88+ / Safari 14+ | Untuk QR scanner |

### PHP Extensions Required

```bash
php -m | grep -E 'mysqli|gd|fileinfo|session'
```

Pastikan extension ini enabled:
- `mysqli` - Database connection
- `gd` - Image processing
- `fileinfo` - File type detection
- `session` - Session management

---

##🚀 Instalasi Lokal

### Option 1: Menggunakan XAMPP/WAMP/Laragon

**1. Download dan Install**
- Download Laragon: https://laragon.org/
- Install dengan default settings
- Start Apache & MySQL

**2. Clone/Extract Project**
```bash
# Navigate ke web root
cd C:\laragon\www

# Clone project (or copy folder)
git clone <repository-url> Bimcheck
# atau extract ZIP ke C:\laragon\www\Bimcheck
```

**3. Import Database**

Via phpMyAdmin:
- Buka http://localhost/phpmyadmin
- Create database `bimcheck`
- Import file `bimcheck.sql`
- Import migration: `migration_add_file_column.sql`

Via MySQL CLI:
```bash
mysql -u root -p
CREATE DATABASE bimcheck;
USE bimcheck;
SOURCE C:/laragon/www/Bimcheck/bimcheck.sql;
SOURCE C:/laragon/www/Bimcheck/migration_add_file_column.sql;
EXIT;
```

**4. Konfigurasi Database**

Edit `config/koneksi.php`:
```php
<?php
$host = "localhost";
$user = "root";
$pass = "";  // Default Laragon: kosong
$db   = "bimcheck";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
```

**5. Set Permissions (Windows)**
```powershell
# Pastikan folder uploads writable
icacls "C:\laragon\www\Bimcheck\uploads" /grant Everyone:F
```

**6. Access Website**
```
http://localhost/Bimcheck
```

---

### Option 2: PHP Built-in Server

**1. Navigate to Project**
```bash
cd C:\laragon\www\Bimcheck
```

**2. Import Database** (same as Option 1 step 3)

**3. Configure** (same as Option 1 step 4)

**4. Start Server**
```bash
php -S localhost:8000
```

**5. Access**
```
http://localhost:8000
```

---

## 🔧 Konfigurasi

### Database Configuration

**File:** `config/koneksi.php`

```php
<?php
// Development
$host = "localhost";
$user = "root";
$pass = "";
$db   = "bimcheck";

// Production (example)
// $host = "mysql.example.com";
// $user = "bimcheck_user";
// $pass = "strong_password_here";
// $db   = "bimcheck_prod";

$conn = mysqli_connect($host, $user, $pass, $db);

// Error handling
if (!$conn) {
    // Development: Show error
    die("Koneksi gagal: " . mysqli_connect_error());
    
    // Production: Log error, show generic message
    // error_log("DB Connection failed: " . mysqli_connect_error());
    // die("Service temporarily unavailable");
}

// Set character set
mysqli_set_charset($conn, "utf8mb4");
?>
```

---

### PHP Configuration

**File:** `php.ini` atau `.htaccess`

**Recommended Settings:**
```ini
; File Uploads
file_uploads = On
upload_max_filesize = 5M
post_max_size = 8M
max_file_uploads = 20

; Memory & Execution
memory_limit = 256M
max_execution_time = 60
max_input_time = 60

; Session
session.save_path = "/path/to/sessions"
session.gc_maxlifetime = 3600

; Error Reporting
; Development
display_errors = On
error_reporting = E_ALL

; Production
;display_errors = Off
;error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
;log_errors = On
;error_log = /path/to/php-error.log
```

---

### Folder Permissions

**Linux/macOS:**
```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/Bimcheck

# Set permissions
sudo chmod -R 755 /var/www/Bimcheck
sudo chmod -R 775 /var/www/Bimcheck/uploads
sudo chmod 644 /var/www/Bimcheck/config/koneksi.php
```

**Windows:**
```powershell
# Give IIS/Apache user write access to uploads
icacls "C:\inetpub\wwwroot\Bimcheck\uploads" /grant "IIS_IUSRS:(OI)(CI)F"
```

---

### Environment Variables (Recommended)

Create `.env` file (not in repo):
```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=bimcheck

APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

UPLOAD_MAX_SIZE=5242880  # 5MB in bytes
SESSION_LIFETIME=3600
```

Then load in `config/koneksi.php`:
```php
<?php
// Load environment variables
$env = parse_ini_file(__DIR__ . '/../.env');

$host = $env['DB_HOST'];
$user = $env['DB_USER'];
$pass = $env['DB_PASS'];
$db   = $env['DB_NAME'];
?>
```

---

## 🌐 Deployment Production

### Preparation Checklist

- [ ] Change debug mode to false
- [ ] Use strong database credentials
- [ ] Enable HTTPS
- [ ] Set proper file permissions
- [ ] Configure error logging
- [ ] Backup database
- [ ] Test all features
- [ ] Enable security headers

---

### Option 1: Shared Hosting (cPanel)

**1. Upload Files**
```bash
# Via FTP/SFTP
- Connect to server
- Navigate to public_html/
- Upload all files except:
  - .git/
  - dokumentasi/
  - *.sql files
```

**2. Create Database**
```
1. Login to cPanel
2. MySQL Databases → Create Database: "bimcheck"
3. Create MySQL User
4. Grant All Privileges
5. Note: hostname, username, password, database name
```

**3. Import Database**
```
1. phpMyAdmin
2. Select "bimcheck" database
3. Import → bimcheck.sql
4. Import → migration_add_file_column.sql
```

**4. Update Config**
```php
// config/koneksi.php
$host = "localhost";  // atau hostname dari cPanel
$user = "cpanel_username_bimcheck";
$pass = "strong_password";
$db   = "cpanel_username_bimcheck";
```

**5. Set Folder Permissions**
```bash
# Via cPanel File Manager or SSH
chmod 755 public_html/Bimcheck
chmod 775 public_html/Bimcheck/uploads
```

---

### Option 2: VPS (Ubuntu/Debian)

**1. Install LEMP Stack**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Nginx
sudo apt install nginx -y

# Install MySQL
sudo apt install mysql-server -y
sudo mysql_secure_installation

# Install PHP 8.1
sudo apt install php8.1-fpm php8.1-mysql php8.1-gd php8.1-mbstring -y
```

**2. Configure Nginx**
```nginx
# /etc/nginx/sites-available/bimcheck
server {
    listen 80;
    server_name bimcheck.example.com;
    root /var/www/Bimcheck;
    index index.php index.html;

    # Logging
    access_log /var/log/nginx/bimcheck-access.log;
    error_log /var/log/nginx/bimcheck-error.log;

    # PHP handling
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Deny access to sensitive files
    location ~ /\.env {
        deny all;
    }
    
    location ~ /(config|dokumentasi) {
        deny all;
    }

    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

**3. Enable Site**
```bash
sudo ln -s /etc/nginx/sites-available/bimcheck /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

**4. Setup SSL (Let's Encrypt)**
```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d bimcheck.example.com
```

**5. Deploy Application**
```bash
# Clone/upload to server
cd /var/www
sudo git clone <repo-url> Bimcheck

# Set permissions
sudo chown -R www-data:www-data /var/www/Bimcheck
sudo chmod -R 755 /var/www/Bimcheck
sudo chmod -R 775 /var/www/Bimcheck/uploads

# Import database
mysql -u root -p
CREATE DATABASE bimcheck;
CREATE USER 'bimcheck_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL ON bimcheck.* TO 'bimcheck_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

mysql -u root -p bimcheck < /var/www/Bimcheck/bimcheck.sql
mysql -u root -p bimcheck < /var/www/Bimcheck/migration_add_file_column.sql
```

---

### Security Hardening

**1. Disable Directory Listing**
```nginx
# Nginx
autoindex off;
```

```apache
# Apache (.htaccess)
Options -Indexes
```

**2. Protect Config Files**
```nginx
# Nginx
location ~ /config/ {
    deny all;
}
```

```apache
# Apache (.htaccess)
<FilesMatch "koneksi\.php">
    Require all denied
</FilesMatch>
```

**3. Use Prepared Statements**
```php
// Replace all queries with prepared statements
// BAD
$query = "SELECT * FROM users WHERE username='$username'";

// GOOD
$stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
```

**4. HTTPS Only**
```nginx
# Force HTTPS redirect
server {
    listen 80;
    server_name bimcheck.example.com;
    return 301 https://$server_name$request_uri;
}
```

---

## 🔍 Troubleshooting

### Database Connection Error

**Problem:** "Koneksi gagal"

**Solutions:**
1. Check MySQL service running
   ```bash
   # Linux
   sudo systemctl status mysql
   
   # Windows (Laragon)
   Check Laragon menu
   ```

2. Verify credentials
   ```bash
   mysql -u root -p
   # Try login manually
   ```

3. Check hostname
   ```php
   // Try 127.0.0.1 instead of localhost
   $host = "127.0.0.1";
   ```

---

### File Upload Not Working

**Problem:** File tidak tersimpan

**Solutions:**
1. Check folder exists
   ```bash
   mkdir -p uploads/dokumen_bimbingan
   mkdir -p uploads/foto_profil
   ```

2. Check permissions
   ```bash
   chmod 775 uploads/
   ```

3. Check PHP settings
   ```ini
   upload_max_filesize = 5M
   post_max_size = 8M
   file_uploads = On
   ```

4. Check disk space
   ```bash
   df -h
   ```

---

### QR Scanner Not Working

**Problem:** Kamera tidak aktif

**Solutions:**
1. **Use HTTPS:** QR scanner requires HTTPS
   ```
   http://localhost  ✅ (exception)
   https://example.com ✅
   http://example.com ❌
   ```

2. Check browser permissions
   - Chrome: Settings → Privacy → Site Settings → Camera
   - Allow camera access

3. Test on mobile
   - Use ngrok for testing
   ```bash
   ngrok http 8000
   ```

---

### Session Not Persisting

**Problem:** Auto-logout setelah refresh

**Solutions:**
1. Check session folder writable
   ```bash
   chmod 777 /tmp  # Linux
   ```

2. Increase session lifetime
   ```php
   ini_set('session.gc_maxlifetime', 3600);
   session_start();
   ```

3. Check cookies enabled in browser

---

### Slow Performance

**Problem:** Loading lambat

**Solutions:**
1. Add database indexes (already done)
2. Enable PHP opcache
   ```ini
   opcache.enable=1
   opcache.memory_consumption=128
   ```

3. Optimize queries
   ```php
   // Use LIMIT
   SELECT * FROM antrian LIMIT 20
   
   // Avoid SELECT *
   SELECT id, topik, status FROM antrian
   ```

4. Enable caching
   - Browser caching (headers)
   - Database query caching

---

## 📊 Monitoring

### Logging

**Error Log:**
```php
// config/koneksi.php
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');
```

**Access Log:**
```nginx
# Nginx
access_log /var/log/nginx/bimcheck-access.log;
```

###Backup Strategy

**Daily Backup:**
```bash
#!/bin/bash
# backup.sh
DATE=$(date +%Y%m%d)
mysqldump -u root -p bimcheck > /backups/bimcheck_$DATE.sql
tar -czf /backups/uploads_$DATE.tar.gz /var/www/Bimcheck/uploads/

# Keep only last 7 days
find /backups -mtime +7 -delete
```

**Cron job:**
```bash
# crontab -e
0 2 * * * /path/to/backup.sh
```

---

## 🚀 Performance Tips

1. **Enable Gzip Compression**
   ```nginx
   gzip on;
   gzip_types text/css application/javascript;
   ```

2. **Use CDN for Libraries**
   - TailwindCSS, Font Awesome already using CDN ✅

3. **Optimize Images**
   - Compress uploads
   - Use WebP format

4. **Database Tuning**
   ```sql
   -- Already have indexes ✅
   -- Consider query caching
   SET GLOBAL query_cache_size = 1048576;
   ```

---

Dokumentasi setup ini mencakup semua skenario deployment BimCheck dari development hingga production.
