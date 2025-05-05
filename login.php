<?php
require_once __DIR__ . '/Core/bootstrap.php';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'], $_POST['password'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $user = $db->query(
            "SELECT id, username, password, role, status FROM users WHERE username = ?",
            [$username]
        )->find();

        if ($user) {
            // Check if account is suspended
            if ($user['status'] == 'suspended') {
                $error = 'Your account has been suspended. Please contact support.';
            } else if (password_verify($password, $user['password'])) {
                // Successful login
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['role']      = $user['role'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = 'Incorrect password!';
            }
        } else {
            $error = 'User not found!';
        }
    } else {
        $error = 'Both username and password are required!';
    }
}


require 'Views/login.phtml';
