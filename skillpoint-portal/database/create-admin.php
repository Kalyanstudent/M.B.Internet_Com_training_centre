<?php
/**
 * MB Internet And Digital Studio | Mobile & Gift House
 * CLI & Web Script for Initializing/Resetting Administrator Account
 * Run via CLI: php database/create-admin.php
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/Validator.php';

echo "========================================================\n";
echo " MB Internet And Digital Studio - Administrator Setup\n";
echo "========================================================\n";

$name = 'Mukesh Bhattacharya (Admin)';
$email = 'admin@mbinternet.com';
$mobile = '9876500001';
$password = 'admin123';

if (php_sapi_name() === 'cli' && $argc > 1) {
    $email = $argv[1] ?? $email;
    $password = $argv[2] ?? $password;
    $name = $argv[3] ?? $name;
    $mobile = $argv[4] ?? $mobile;
}

$db = Database::getConnection();
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

// Check if admin already exists
$stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
$stmt->execute([':email' => strtolower($email)]);
$existing = $stmt->fetch();

if ($existing) {
    $upStmt = $db->prepare("
        UPDATE users 
        SET name = :name, mobile = :mobile, password_hash = :hash, role = 'admin', status = 'active', updated_at = NOW() 
        WHERE id = :id
    ");
    $upStmt->execute([
        ':name'   => $name,
        ':mobile' => $mobile,
        ':hash'   => $passwordHash,
        ':id'     => $existing['id']
    ]);
    echo "✓ Existing Administrator account ({$email}) updated with new password hash.\n";
} else {
    $inStmt = $db->prepare("
        INSERT INTO users (user_code, name, email, mobile, password_hash, address, role, status, created_at)
        VALUES (:code, :name, :email, :mobile, :hash, :address, 'admin', 'active', NOW())
    ");
    $inStmt->execute([
        ':code'    => 'adm-' . time(),
        ':name'    => $name,
        ':email'   => strtolower($email),
        ':mobile'  => $mobile,
        ':hash'    => $passwordHash,
        ':address' => 'MB Internet And Digital Studio, Main Road'
    ]);
    echo "✓ New Administrator account created successfully ({$email}).\n";
}

echo "Role: admin\n";
echo "Status: active\n";
echo "========================================================\n";
