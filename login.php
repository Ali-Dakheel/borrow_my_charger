<?php
require_once __DIR__ . '/Core/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'], $_POST['password'])) {
        $username = trim($_POST['username']);
        $password = trim(string: $_POST['password']);

        $user = $db->query(
            "SELECT id, username, password, role, status FROM users WHERE username = ?",
            [$username]
        )->find();

        if ($user) {
            // Verify the submitted password against the hashed password in the database
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['role']      = $user['role'];
                echo "Login successful!";
                header("Location: dashboard.php");
                exit;
            } else {
                echo "Incorrect password!";
            }
        } else {
            echo "User not found!";
        }
    } else {
        echo "Both username and password are required!";
    }
}

require 'Views/login.phtml';
?>
