<?php
require_once __DIR__ . '/Core/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'], $_POST['name'], $_POST['password'], $_POST['confirm_password'], $_POST['role'])) {
        // Retrieve and trim input values
        $username = trim($_POST['username']);
        $name = trim($_POST['name']);
        $password = trim($_POST['password']);
        $confirmPassword = trim($_POST['confirm_password']);
        $role = trim($_POST['role']);

        // Validate that the passwords match
        if ($password !== $confirmPassword) {
            echo "Passwords do not match!";
            exit;
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Prepare the SQL query and execute it with PDO parameters
        $sql = "INSERT INTO users (username, name, password, role, status) VALUES (?, ?, ?, ?, 'active')";
        $result = $db->query($sql, [$username, $name, $hashedPassword, $role]);

        // Check if a row was inserted
        if ($result->statement->rowCount() > 0) {
            echo "Registration successful!";
        } else {
            echo "Registration failed!";
        }
    } else {
        echo "All fields are required!";
    }
}

require "Views/register.phtml";
?>
