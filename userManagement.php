<?php
require_once __DIR__ . '/Core/bootstrap.php';
require_once 'Models/User.php';


// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$userModel = new User($db);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_status'])) {
            $userModel->updateUserStatus($_POST['user_id'], $_POST['new_status']);
            $_SESSION['success'] = "User status updated successfully";
        }
        elseif (isset($_POST['update_role'])) {
            $userModel->updateUserRole($_POST['user_id'], $_POST['new_role']);
            $_SESSION['success'] = "User role updated successfully";
        }
        elseif (isset($_POST['delete_user'])) {
            // Prevent admin from deleting themselves
            if ($_POST['user_id'] != $_SESSION['user_id']) {
                $userModel->deleteUser($_POST['user_id']);
                $_SESSION['success'] = "User deleted successfully";
            } else {
                $_SESSION['error'] = "You cannot delete your own account";
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    header('Location: userManagement.php');
    exit();
}

// Get search query if exists
$searchQuery = $_GET['search'] ?? '';
$users = $searchQuery 
    ? $userModel->searchUsers($searchQuery)
    : $userModel->getAllUsers();

require 'Views/admin/users.phtml';