<?php
/**
 * POST /api/service-requests/create.php
 * Submit Online Tax & Digital Service Request with Uploaded Documents
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/includes/Validator.php';
require_once dirname(__DIR__, 2) . '/models/ServiceRequest.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

$user = Auth::requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', [], 405);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$errors = Validator::validateRequired($data, [
    'customer_name' => 'Full Name',
    'mobile'        => 'Mobile Number',
    'email'         => 'Email Address',
    'service_type'  => 'Service Type'
]);

if (!empty($errors)) {
    Response::error('Please fill all mandatory tax request fields.', $errors, 422);
}

if (!Validator::isValidIndianMobile($data['mobile'])) {
    Response::error('Please provide a valid 10-digit mobile number.', ['mobile' => 'Invalid mobile'], 422);
}

if (!empty($data['pan_number']) && strtoupper(trim($data['pan_number'])) !== 'NOT_PROVIDED') {
    if (!Validator::isValidPAN($data['pan_number'])) {
        Response::error('Please enter a valid 10-character PAN number (e.g. ABCDE1234F).', ['pan_number' => 'Invalid PAN format'], 422);
    }
}

$documentIds = [];
if (!empty($data['document_ids']) && is_array($data['document_ids'])) {
    $documentIds = array_map('intval', $data['document_ids']);
}

$result = ServiceRequest::createRequest((int)$user['id'], $data, $documentIds);

if ($result['success']) {
    Response::success($result, 'Tax service request submitted successfully! Your Request ID is ' . $result['request_number']);
} else {
    Response::error($result['message'], [], 400);
}
