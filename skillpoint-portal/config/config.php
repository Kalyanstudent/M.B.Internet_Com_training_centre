<?php
/**
 * MB Internet And Digital Studio | Mobile & Gift House
 * Central Application Configuration & Environment Settings (XAMPP Compatible)
 */

// Application Environment: 'development' or 'production'
defined('APP_ENV') or define('APP_ENV', getenv('APP_ENV') ?: 'development');

// Dynamic Base Application URL Detection for XAMPP / Apache
if (!defined('APP_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // Remove /api/* or /admin from the base path if called from subfolder
    $baseDir = preg_replace('#/(api(/.*)?|admin(/.*)?|database(/.*)?)$#', '', $scriptDir);
    $baseDir = rtrim($baseDir, '/');
    define('APP_URL', $protocol . $host . $baseDir);
}

// Database Connection Parameters (Configured for XAMPP MySQL)
defined('DB_HOST')     or define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
defined('DB_PORT')     or define('DB_PORT', getenv('DB_PORT') ?: '3306');
defined('DB_NAME')     or define('DB_NAME', getenv('DB_NAME') ?: 'skillpoint_portal');
defined('DB_USER')     or define('DB_USER', getenv('DB_USER') ?: 'root');
defined('DB_PASSWORD') or define('DB_PASSWORD', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (getenv('DB_PASS') ?: ''));
defined('DB_PASS')     or define('DB_PASS', DB_PASSWORD);
defined('DB_CHARSET')  or define('DB_CHARSET', 'utf8mb4');

// Razorpay API Credentials (Configurable via ENV or Settings table)
defined('RAZORPAY_KEY_ID')     or define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: 'rzp_test_demo12345678');
defined('RAZORPAY_KEY_SECRET') or define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: 'test_secret_demo12345678');

// Error Reporting
if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}

// Secure PHP Session Initialization for XAMPP
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');

    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }

    session_start();
}
