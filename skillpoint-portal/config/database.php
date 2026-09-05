<?php
/**
 * MB Internet And Digital Studio
 * PDO Database Connection Singleton (XAMPP / MySQL 8.0+ Compatible)
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $port = defined('DB_PORT') ? DB_PORT : '3306';
            $dsn = "mysql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
            } catch (PDOException $e) {
                // If database doesn't exist yet, attempt connecting without dbname to create it or give clean JSON
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                if (APP_ENV === 'development') {
                    die(json_encode([
                        'success' => false,
                        'message' => 'MySQL connection failed: ' . $e->getMessage() . '. Please verify database `' . DB_NAME . '` exists and MySQL is running in XAMPP.',
                        'error_code' => $e->getCode()
                    ]));
                } else {
                    error_log('Database connection error: ' . $e->getMessage());
                    die(json_encode([
                        'success' => false,
                        'message' => 'Database connection error. Please contact system administrator.'
                    ]));
                }
            }
        }

        return self::$instance;
    }
}
