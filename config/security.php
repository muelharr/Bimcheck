<?php
// Security Helper Functions
// Fungsi-fungsi untuk input sanitization, CSRF protection, dan XSS prevention

// Membersihkan input dari karakter berbahaya
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    return $data;
}

// Mencegah XSS pada output
function prevent_xss($data) {
    if (is_array($data)) {
        return array_map('prevent_xss', $data);
    }
    
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Generate CSRF token
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($token)) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Regenerate CSRF token (setelah login/logout)
function regenerate_csrf_token() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

// Validasi email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validasi tanggal (format Y-m-d)
function validate_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Validasi waktu (format H:i)
function validate_time($time) {
    return preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $time) === 1;
}

// Validasi nomor (integer positif)
function validate_positive_integer($value) {
    return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
}

// Generate secure random string
function generate_random_string($length = 16) {
    return bin2hex(random_bytes($length / 2));
}

// Session security - regenerate ID secara berkala
function secure_session_start() {
    // Session config
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Regenerate session ID setiap 30 menit
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['status']) && $_SESSION['status'] === 'login';
}

// Check user role
function check_role($required_role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $required_role;
}

// Redirect jika belum login
function require_login($redirect_to = '../index.php') {
    if (!is_logged_in()) {
        header("Location: $redirect_to");
        exit;
    }
}

// Redirect jika role tidak sesuai
function require_role($required_role, $redirect_to = '../index.php') {
    if (!check_role($required_role)) {
        header("Location: $redirect_to");
        exit;
    }
}
?>
