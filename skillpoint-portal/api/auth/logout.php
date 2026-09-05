<?php
/**
 * MB Internet And Digital Studio | API Logout Endpoint (Core PHP)
 */
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

echo json_encode([
    'success' => true,
    'message' => 'Logged out successfully.'
]);
