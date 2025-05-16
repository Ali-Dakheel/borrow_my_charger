<?php
require_once __DIR__ . '/Core/bootstrap.php';
require_once 'Models/ChargePointData.php';
require_once 'Models/ChargePointDataset.php';
require_once 'Models/UserData.php';
require_once 'Models/UserDataset.php';

$message = null;
$userId = null;
$userRole = null;
$username = null;
$name = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if this is a charge point creation submission from a recently registered homeowner
    if (isset($_POST['action']) && $_POST['action'] === 'create_charge_point') {
        try {
            // Create a new charge point using the dataset class
            $chargePointDataset = new ChargePointDataset($db);
            
            // Validate and sanitize input
            if (empty($_POST['address']) || empty($_POST['postcode']) || 
                empty($_POST['latitude']) || empty($_POST['longitude']) || 
                empty($_POST['price_per_kwh'])) {
                throw new Exception("All fields are required");
            }
            
            $latitude = filter_var($_POST['latitude'], FILTER_VALIDATE_FLOAT);
            $longitude = filter_var($_POST['longitude'], FILTER_VALIDATE_FLOAT);
            $price = filter_var($_POST['price_per_kwh'], FILTER_VALIDATE_FLOAT);
            
            if ($latitude === false || $longitude === false || $price === false) {
                throw new Exception("Invalid latitude, longitude, or price");
            }
            
            $homeownerId = (int)$_POST['homeowner_id'];
            
            $chargePointData = [
                'homeowner_id' => $homeownerId,
                'address' => trim($_POST['address']),
                'postcode' => trim($_POST['postcode']),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'price_per_kwh' => $price,
                'is_available' => 0, // Always set to unavailable for new registrations
                'image_path' => null // You can add image upload functionality later
            ];
            
            $chargePointDataset->create($chargePointData);
            
            // Start session and log in the user
            session_start();
            $_SESSION['user_id'] = $homeownerId;
            $_SESSION['username'] = $_POST['username'];
            $_SESSION['role'] = 'homeowner';
            
            // Redirect to dashboard with a message about approval
            $_SESSION['approval_message'] = "Your account has been registered successfully. Your charge point will be unavailable until an admin approves your account.";
            header('Location: dashboard.php');
            exit();
            
        } catch (Exception $e) {
            $message = 'Error creating charge point: ' . $e->getMessage();
            $userId = $_POST['homeowner_id'];
            $userRole = 'homeowner';
            $username = $_POST['username'];
            $name = $_POST['name'];
        }
    } 
    // Regular registration form processing
    else if (isset($_POST['username'], $_POST['name'], $_POST['password'], $_POST['confirm_password'], $_POST['role'])) {
        try {
            // Initialize the UserDataset
            $userDataset = new UserDataset($db);
            
            // Retrieve and trim input values
            $username = trim($_POST['username']);
            $name = trim($_POST['name']);
            $password = trim($_POST['password']);
            $confirmPassword = trim($_POST['confirm_password']);
            $role = trim($_POST['role']);

            // Validate that the passwords match
            if ($password !== $confirmPassword) {
                throw new Exception('Passwords do not match!');
            }
            
            // Validate username (prevent duplicates)
            $existingUser = $db->query("SELECT id FROM users WHERE username = ?", [$username])->find();
            if ($existingUser) {
                throw new Exception('Username already exists. Please choose another one.');
            }

            // Prepare user data (note: the create method in UserDataset will hash the password)
            $userData = [
                'username' => $username,
                'name' => $name,
                'password' => $password, // Will be hashed in UserDataset::create
                'role' => $role,
                'status' => 'active',
                'is_approved' => ($role === 'homeowner') ? 0 : 1 // Homeowners need approval
            ];
            
            // Use direct SQL query to avoid required email field
            // Note: UserDataset::create() requires email which isn't used in the form
            $sql = "INSERT INTO users (username, name, password, role, status) VALUES (?, ?, ?, ?, 'active')";
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $result = $db->query($sql, [$username, $name, $hashedPassword, $role]);

            // Check if a row was inserted
            if ($result->statement->rowCount() > 0) {
                // Get the inserted user's ID using connection directly
                $userId = $db->connection->lastInsertId();
                $userRole = $role;
                
                // If user is a homeowner, prompt to create a charge point
                if ($role === 'homeowner') {
                    $message = 'Registration successful! Please set up your charge point. Note that your charge point will be unavailable until an admin approves your account.';
                } else {
                    // Otherwise, log them in and redirect
                    session_start();
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;
                    
                    header('Location: dashboard.php');
                    exit();
                }
            } else {
                throw new Exception('Registration failed!');
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
    } else {
        $message = 'All fields are required!';
    }
}

require "Views/register.phtml";