<?php
/**
 * MB Internet And Digital Studio | Mobile & Gift House
 * Application Constants
 */

// Application Info
define('APP_NAME', 'MB Internet And Digital Studio');
define('APP_SUB_BRAND', 'Mobile & Gift House');
define('APP_OWNER', 'Mukesh Bhattacharya');
define('APP_VERSION', '2.0.0');

// User Roles
define('ROLE_USER', 'user');
define('ROLE_ADMIN', 'admin');

// Statuses
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_SUSPENDED', 'suspended');

// Payment Statuses
define('PAYMENT_STATUS_PENDING', 'Pending');
define('PAYMENT_STATUS_PAID', 'Paid');
define('PAYMENT_STATUS_FAILED', 'Failed');
define('PAYMENT_STATUS_REFUNDED', 'Refunded');

// Tax Service Request Statuses
define('TAX_STATUS_PENDING', 'Pending');
define('TAX_STATUS_PROCESSING', 'Processing');
define('TAX_STATUS_NEED_INFO', 'Need More Information');
define('TAX_STATUS_COMPLETED', 'Completed');
define('TAX_STATUS_REJECTED', 'Rejected');

// File Upload Restrictions
define('MAX_UPLOAD_SIZE_BYTES', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_DOC_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png', 'xlsx', 'xls']);
define('ALLOWED_MIME_TYPES', [
    'application/pdf',
    'image/jpeg',
    'image/pjpeg',
    'image/png',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-excel'
]);

// Base Paths
define('DIR_ROOT', dirname(__DIR__));
define('DIR_STORAGE', DIR_ROOT . DIRECTORY_SEPARATOR . 'storage');
define('DIR_DOCUMENTS', DIR_STORAGE . DIRECTORY_SEPARATOR . 'documents');
define('DIR_UPLOADS', DIR_STORAGE . DIRECTORY_SEPARATOR . 'uploads');
