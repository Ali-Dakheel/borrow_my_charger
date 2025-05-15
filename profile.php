<?php
require_once __DIR__ . '/Core/bootstrap.php';
require_once __DIR__ . '/Models/User.php';
$users = new User($db);
$user = $users->getUserById($_SESSION['user_id']);
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [];
    
    if (!empty($_POST['name'])) {
        $data['name'] = $_POST['name'];
    }
    if (!empty($_POST['email'])) {
        $data['email'] = $_POST['email'];
    }
    if (!empty($_POST['phone'])) {
        $data['phone'] = $_POST['phone'];
    }
    if (!empty($_POST['username'])) {
        $data['username'] = $_POST['username'];
    }
    
    if (!empty($_POST['password'])) {
        if ($_POST['password'] === $_POST['confirm_password']) {
            $data['password'] = $_POST['password'];
        } else {
            $message = "Passwords do not match";
        }
    }
    
    if (!empty($data) && $message === null) {
        $result = $users->update($_SESSION['user_id'], $data);
        
        if ($result) {
            $message = "Profile updated successfully!";
            $user = $users->getUserById($_SESSION['user_id']); 
        } else {
            $message = "Failed to update profile. Please try again.";
        }
    }
}

require_once __DIR__ . '/Views/profile.phtml';
