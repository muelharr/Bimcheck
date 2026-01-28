<?php
// Error Handler & Logging Functions

// Log error ke file
function log_error($message, $context = []) {
    $log_dir = __DIR__ . '/../logs';
    
    // Buat folder logs jika belum ada
    if (!file_exists($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/error_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    
    $log_message = "[{$timestamp}] {$message}";
    
    if (!empty($context)) {
        $log_message .= " | Context: " . json_encode($context);
    }
    
    $log_message .= PHP_EOL;
    
    @file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Tampilkan error user-friendly
function show_error($user_message, $technical_details = null) {
    // Log technical details jika ada
    if ($technical_details) {
        log_error($technical_details);
    }
    
    // Tampilkan user-friendly message
    echo "<script>alert('" . addslashes($user_message) . "');</script>";
}

// Handle exception
function handle_exception($e) {
    log_error($e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Jangan expose technical details ke user
    show_error('Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.');
}

// Set custom error handler
function custom_error_handler($errno, $errstr, $errfile, $errline) {
    log_error("PHP Error: {$errstr}", [
        'file' => $errfile,
        'line' => $errline,
        'type' => $errno
    ]);
    
    // Return false untuk PHP default error handler
    return false;
}

// Set custom exception handler
function custom_exception_handler($exception) {
    handle_exception($exception);
}

// Initialize error handling
function init_error_handling() {
    // Jangan tampilkan error ke user
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    
    // Set custom handlers
    set_error_handler('custom_error_handler');
    set_exception_handler('custom_exception_handler');
}
?>
