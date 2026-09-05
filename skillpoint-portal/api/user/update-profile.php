<?php
/**
 * POST /api/user/update-profile.php
 * Update Profile Information for Current User
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/includes/Validator.php';
require_once dirname(__DIR__, 2) . '/models/User.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

$user = Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', [], 405);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$name = trim($data['name'] ?? '');
$mobile = trim($data['mobile'] ?? '');
$address = trim($data['address'] ?? '');

if (empty($name)) {
    Response::error('Name cannot be empty.', ['name' => 'Required'], 422);
}

if (!empty($mobile) && !Validator::isValidIndianMobile($mobile)) {
    Response::error('Please provide a valid 10-digit mobile number.', ['mobile' => 'Invalid mobile'], 422);
}

$cleanMobile = Validator::normalizeMobile($mobile ?: $user['mobile']);
User::updateProfile((int)$user['id'], $name, $cleanMobile, $address);

$updatedUser = User::getById((int)$user['id']);
$_SESSION['user_name'] = $updatedUser['name'];

Response::success($updatedUser, 'Profile updated successfully.');
