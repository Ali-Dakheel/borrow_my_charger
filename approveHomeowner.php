<?php
require_once __DIR__ . '/Core/bootstrap.php';
require_once __DIR__ . '/Models/User.php';

$users = new User($db);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'])) {
    try {
        $users->approveHomeowner($_POST['approve_id']);
        $_SESSION['success'] = 'Homeowner approved successfully!';
    } catch (Exception $e) {
        $_SESSION['error'] = 'An error occurred while approving the homeowner. Please try again.';
    }
    header("Location: approveHomeowner.php");
    exit;
}

$pendingHomeowners = $users->getAllPendingHomeowners();

require_once __DIR__ .'/Views/admin/approveHomeowner.phtml';