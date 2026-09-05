<?php
/**
 * GET /api/tax-services/detail.php
 * Fetch Single Tax Service Details
 */

require_once dirname(__DIR__, 2) . '/models/TaxService.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

$idOrSlug = $_GET['id'] ?? $_GET['slug'] ?? null;

if (!$idOrSlug) {
    Response::error('Service identifier is required.', [], 400);
}

$service = TaxService::getByIdOrSlug($idOrSlug);

if (!$service) {
    Response::notFound('Tax service not found.');
}

Response::success($service, 'Tax service details fetched successfully');
