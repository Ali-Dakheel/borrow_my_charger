<?php
require_once __DIR__ . '/Core/bootstrap.php';
require_once __DIR__ . '/Models/UserData.php';
require_once __DIR__ . '/Models/UserDataset.php';

$userDataset = new UserDataset($db);

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $errorMessage = "Access Denied";
    $errorDetails = "You don't have permission to access this page. This area is restricted to administrators only.";
    $backLink = "dashboard.php"; 
    require_once 'Views/error.phtml';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'])) {
    try {
        $userDataset->approveHomeowner($_POST['approve_id']);
        $_SESSION['success'] = 'Homeowner approved successfully!';
    } catch (Exception $e) {
        $_SESSION['error'] = 'An error occurred while approving the homeowner. Please try again.';
    }
    header("Location: approveHomeowner.php");
    exit;
}

$pendingHomeowners = $userDataset->getAllPendingHomeowners();

require_once __DIR__ .'/Views/admin/approveHomeowner.phtml';