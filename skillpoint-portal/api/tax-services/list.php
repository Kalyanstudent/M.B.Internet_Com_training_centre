<?php
/**
 * GET /api/tax-services/list.php
 * Public Tax & Digital Services Catalog
 */

require_once dirname(__DIR__, 2) . '/models/TaxService.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

$services = TaxService::getAll('active');
Response::success($services, 'Tax & digital services fetched successfully');
