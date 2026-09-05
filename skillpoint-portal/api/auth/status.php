<?php
/**
 * MB Internet And Digital Studio | API Auth Status Endpoint (Core PHP)
 */
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

if (is_logged_in()) {
    $user = current_user();
    if ($user) {
        echo json_encode([
            'success' => true,
            'message' => 'Authenticated',
            'data' => [
                'authenticated' => true,
                'logged_in'     => true,
                'role'          => $user['role'],
                'user'          => [
                    'id'        => (int)$user['id'],
                    'user_code' => $user['user_code'],
                    'name'      => $user['name'],
                    'email'     => $user['email'],
                    'mobile'    => $user['mobile'],
                    'address'   => $user['address'] ?? '',
                    'role'      => $user['role'],
                    'status'    => $user['status']
                ]
            ]
        ]);
        exit;
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Unauthenticated',
    'data' => [
        'authenticated' => false,
        'logged_in'     => false,
        'role'          => null,
        'user'          => null
    ]
]);
