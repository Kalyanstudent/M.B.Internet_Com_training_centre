<?php
/**
 * MB Internet And Digital Studio | API CSRF Token Endpoint (Core PHP)
 */
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__, 2) . '/config/db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo json_encode([
    'success' => true,
    'data' => [
        'csrf_token' => $_SESSION['csrf_token']
    ]
]);
