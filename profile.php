<?php

require_once __DIR__ . '/Core/bootstrap.php';
require_once __DIR__ . '/Models/UserData.php';
require_once __DIR__ . '/Models/UserDataset.php';

$userDataset = new UserDataset($db);
$userData = $userDataset->getUserById($_SESSION['user_id']);

// Convert UserData object to array for the view
$user = $userData->toArray();
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
        $result = $userDataset->update($_SESSION['user_id'], $data);
        
        if ($result) {
            $message = "Profile updated successfully!";
            $userData = $userDataset->getUserById($_SESSION['user_id']);
            $user = $userData->toArray();
        } else {
            $message = "Failed to update profile. Please try again.";
        }
    }
}

require_once __DIR__ . '/Views/profile.phtml';