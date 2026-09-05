<?php
/**
 * GET /api/digital-services/list.php
 * Public Digital Services Directory (Form Filling, Scanning, Printing)
 */

require_once dirname(__DIR__, 2) . '/models/DigitalService.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

$services = DigitalService::getAll('active');
Response::success($services, 'Digital services fetched successfully');
