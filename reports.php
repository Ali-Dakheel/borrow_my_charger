<?php
require_once __DIR__ . '/Core/bootstrap.php';
require_once 'Models/Report.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $errorMessage = "Access Denied";
    $errorDetails = "You don't have permission to access this page. This area is restricted to administrators only.";
    $backLink = "dashboard.php"; 
    require_once 'Views/error.phtml';
    exit();
}
$reportModel = new Report($db);

$error = '';
$results = [];
$query = '';

try {
    $metrics = $reportModel->getDashboardMetrics();
} catch (Exception $e) {
    $error = 'Error fetching metrics: ' . $e->getMessage();
    $metrics = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $query = $_POST['query'] ?? '';
    
    try {
        $results = $reportModel->executeQuery($query);
    } catch (PDOException $e) {
        $error = 'Database Error: ' . $e->getMessage();
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

require_once 'Views/admin/reports.phtml';