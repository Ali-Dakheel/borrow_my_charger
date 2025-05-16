<?php
require_once __DIR__ . '/Core/bootstrap.php';


// Check if user is logged in
if ($session->isLoggedIn()) {
    $role = $session->getUserRole();
    header('Location: /dashboard.php');
    exit;
}

// Redirect to login if not authenticated
header('Location: /login.php');
exit;
