<?php
/**
 * GET /api/admin/dashboard.php
 * Real Admin Dashboard Statistics & KPIs
 */

require_once dirname(__DIR__, 2) . '/includes/Auth.php';
require_once dirname(__DIR__, 2) . '/models/Admin.php';
require_once dirname(__DIR__, 2) . '/models/Order.php';
require_once dirname(__DIR__, 2) . '/models/ServiceRequest.php';
require_once dirname(__DIR__, 2) . '/includes/Response.php';

// Strict Role Guard: Only Admin can access
Auth::requireAdmin();

$kpis = Admin::getDashboardKPIs();
$revenueChart = Admin::getRevenueChartData();
$courseSalesChart = Admin::getCourseSalesChartData();
$recentOrders = Order::getAll('', 'all', 5, 0);
$recentTaxRequests = ServiceRequest::getAll('', 'all', 5, 0);

Response::success([
    'stats'               => $kpis,
    'metrics'             => $kpis,
    'charts'              => [
        'revenue_trend' => $revenueChart,
        'course_sales'  => $courseSalesChart
    ],
    'total_users'         => $kpis['total_users'],
    'total_courses'       => $kpis['total_courses'],
    'all_courses'         => $kpis['all_courses'],
    'total_orders'        => $kpis['total_orders'],
    'successful_payments' => $kpis['successful_payments'],
    'total_revenue'       => $kpis['total_revenue'],
    'pending_requests'    => $kpis['pending_requests'],
    'processing_requests' => $kpis['processing_requests'],
    'completed_requests'  => $kpis['completed_requests'],
    'revenue_chart'       => $revenueChart,
    'course_sales_chart'  => $courseSalesChart,
    'recent_orders'       => $recentOrders,
    'recent_tax_requests' => $recentTaxRequests
], 'Admin dashboard metrics retrieved successfully.');
