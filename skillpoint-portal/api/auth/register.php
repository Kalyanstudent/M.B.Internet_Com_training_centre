<?php
/**
 * MB Internet And Digital Studio | API Register Endpoint (Core PHP)
 */
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$name = trim($data['name'] ?? '');
$email = strtolower(trim($data['email'] ?? ''));
$mobile = preg_replace('/\D/', '', trim($data['mobile'] ?? ''));
$address = trim($data['address'] ?? '');
$password = trim((string)($data['password'] ?? ''));

if (empty($name) || empty($email) || empty($mobile) || empty($password)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

if (strlen($name) < 3) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Full name must be at least 3 characters long.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

if (strlen($mobile) !== 10 || !preg_match('/^[6-9]\d{9}$/', $mobile)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid 10-digit Indian mobile number.']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long.']);
    exit;
}

try {
    $check = $conn->prepare("SELECT id FROM users WHERE email = :email OR mobile = :mobile LIMIT 1");
    $check->execute([':email' => $email, ':mobile' => $mobile]);
    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'An account with this email or mobile number already exists.']);
        exit;
    }

    $user_code = "usr-" . rand(100, 999) . "-" . rand(1000, 9999);
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("
        INSERT INTO users (user_code, name, email, mobile, password_hash, address, role, status, created_at)
        VALUES (:ucode, :name, :email, :mobile, :phash, :addr, 'user', 'active', NOW())
    ");

    $stmt->execute([
        ':ucode'  => $user_code,
        ':name'   => $name,
        ':email'  => $email,
        ':mobile' => $mobile,
        ':phash'  => $password_hash,
        ':addr'   => $address
    ]);

    $new_id = (int)$conn->lastInsertId();

    $_SESSION['user_id'] = $new_id;
    $_SESSION['user_code'] = $user_code;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['role'] = 'user';

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful.',
        'data' => [
            'user' => [
                'id'        => $new_id,
                'user_code' => $user_code,
                'name'      => $name,
                'email'     => $email,
                'mobile'    => $mobile,
                'role'      => 'user'
            ],
            'redirect' => 'dashboard.php'
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
