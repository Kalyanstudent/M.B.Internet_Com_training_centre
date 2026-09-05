<?php
/**
 * MB Internet And Digital Studio | Mobile & Gift House
 * Secure Database Connection & Global Security Configuration (Core PHP)
 */

// Global Security Headers
if (!headers_sent()) {
    header("X-Frame-Options: SAMEORIGIN");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
}

// Secure Session Cookie Initialization
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $is_https,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

// Database Credentials
// $db_host = "localhost";
// $db_user = "root";
// $db_pass = "";
// $db_name = "skillpoint_portal";
// $db_port = 3306;
$db_host = "sql308.infinityfree.com";
$db_user = "if0_42841606";
$db_pass = "1xrSmNllvuj9";
$db_name = "if0_42841606_skillpoint_portal";
$db_port = 3306;

try {
    $conn = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => true
    ]);
} catch (PDOException $e) {
    // Log error securely without exposing technical details to users
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database service temporarily unavailable. Please make sure MySQL is running in XAMPP.");
}

// Dynamic Base URL Resolver
$app_url = "";
if (isset($_SERVER['HTTP_HOST'])) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $baseDir = preg_replace('#/(admin(/.*)?|includes(/.*)?|api(/.*)?)$#', '', $scriptDir);
    $app_url = rtrim($protocol . $_SERVER['HTTP_HOST'] . $baseDir, '/');
}
