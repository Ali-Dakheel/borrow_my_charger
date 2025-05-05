<?php
require_once __DIR__ . '/Core/bootstrap.php';
require_once 'Models/ChargePoint.php';
require_once 'Models/User.php';

$chargePointModel = new ChargePoint($db);
$usersModel = new User($db);
$allUsers = $usersModel->getAllUsers();
$limit = 10; // Records per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Fetch paginated charge points
$chargePoints = $chargePointModel->getPaginated($limit, $offset);

// Calculate total pages
$totalCount = $chargePointModel->getTotalCount();
$totalPages = ceil($totalCount / $limit);
if ($_SESSION['role'] === 'homeowner') {
    $chargePoint = $chargePointModel->getByHomeOwnerById($_SESSION['user_id']);
}

// Handle AJAX search requests
if (isset($_GET['ajax']) && $_GET['ajax'] === 'search') {
    header('Content-Type: application/json');

    try {
        // Get parameters (only those that were provided)
        $location = $_GET['location'] ?? '';
        $minPrice = isset($_GET['minPrice']) ? floatval($_GET['minPrice']) : null;
        $maxPrice = isset($_GET['maxPrice']) ? floatval($_GET['maxPrice']) : null;
        $availableOnly = filter_var($_GET['availableOnly'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $latitude = isset($_GET['latitude']) ? floatval($_GET['latitude']) : null;
        $longitude = isset($_GET['longitude']) ? floatval($_GET['longitude']) : null;

        // Get all charge points
        $chargePoints = $chargePointModel->getAll();

        // Filter results
        $filtered = array_filter($chargePoints, function ($point) use ($location, $minPrice, $maxPrice, $availableOnly, $latitude, $longitude) {
            // Availability check
            if ($availableOnly && !$point['is_available']) return false;

            // Price check (only if filters provided)
            $price = floatval($point['price_per_kwh']);
            if ($minPrice !== null && $price < $minPrice) return false;
            if ($maxPrice !== null && $price > $maxPrice) return false;

            // Location text search
            if (
                !empty($location) &&
                stripos($point['address'], $location) === false &&
                stripos($point['postcode'], $location) === false
            ) {
                return false;
            }

            // Coordinate checks (only if filters provided)
            if ($latitude !== null && abs(floatval($point['latitude']) - $latitude) > 0.1) {
                return false;
            }
            if ($longitude !== null && abs(floatval($point['longitude']) - $longitude) > 0.1) {
                return false;
            }

            return true;
        });

        // Format results
        $result = array_map(function ($point) {
            return [
                'id' => $point['id'],
                'address' => $point['address'],
                'postcode' => $point['postcode'],
                'latitude' => floatval($point['latitude']),
                'longitude' => floatval($point['longitude']),
                'price_per_kwh' => floatval($point['price_per_kwh']),
                'is_available' => (bool)$point['is_available'],
                'image_path' => $point['image_path'] ?? null
            ];
        }, $filtered);

        echo json_encode(array_values($result));
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $existingChargePoint = $chargePointModel->getByHomeOwnerById($_POST['homeowner_id']);
                if ($existingChargePoint) {
                    header("Location: chargePoint.php?error=homeowner_already_has_charge_point");
                    exit;
                }

                $chargePointModel->create([
                    'homeowner_id' => $_POST['homeowner_id'],
                    'address' => $_POST['address'],
                    'postcode' => $_POST['postcode'],
                    'latitude' => $_POST['latitude'],
                    'longitude' => $_POST['longitude'],
                    'price_per_kwh' => $_POST['price_per_kwh'],
                    'is_available' => isset($_POST['is_available']) ? 1 : 0,
                    'image_path' => uploadImage($_FILES['image'] ?? null)
                ]);
                header("Location: chargePoint.php?success=created");
                exit;
            case 'update':
                $chargePointModel->update([
                    'id' => $_POST['id'],
                    'homeowner_id' => $_POST['homeowner_id'],
                    'address' => $_POST['address'],
                    'postcode' => $_POST['postcode'],
                    'latitude' => $_POST['latitude'],
                    'longitude' => $_POST['longitude'],
                    'price_per_kwh' => $_POST['price_per_kwh'],
                    'is_available' => isset($_POST['is_available']) ? 1 : 0,
                    'image_path' => uploadImage($_FILES['image'] ?? null) ?? $_POST['existing_image']
                ]);
                header("Location: chargePoint.php?success=updated");
                exit;
            case 'delete':
                $chargePointModel->delete($_POST['id']);
                header("Location: chargePoint.php?success=deleted");
                exit;
        }
    }
}

// $chargePoints = $chargePointModel->getAll();

function uploadImage($file)
{
    if (! $file || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $targetDir = __DIR__ . '/assets/images/';
    if (! is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (! in_array($ext, $allowed, true)) {
        throw new Exception("Invalid image format. Allowed: " . implode(', ', $allowed));
    }

    $basename = bin2hex(random_bytes(8));
    $filename = "{$basename}.{$ext}";
    $targetFs = $targetDir  . $filename;
    $relative = "assets/images/{$filename}";
    if (! move_uploaded_file($file['tmp_name'], $targetFs)) {
        throw new Exception("Failed to move uploaded file.");
    }

    return $relative;
}

if ($_SESSION['role'] === 'admin') {
    require "Views/admin/chargePoint.phtml";
} elseif ($_SESSION['role'] === 'homeowner') {
    require "Views/homeowner/chargePoint.phtml";
} elseif ($_SESSION['role'] === 'user') {
    require "Views/rentalUser/chargePoint.phtml";
}
