<?php
/**
 * GET /api/important-links/list.php
 * Public Important Links Directory
 */

require_once dirname(__DIR__, 2) . '/models/ImportantLink.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

$links = ImportantLink::getAll('active');
Response::success($links, 'Important links fetched successfully');
