<?php
require_once __DIR__ . '/Core/bootstrap.php';
require_once 'Models/Booking.php';
require_once 'Models/ChargePoint.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$bookingModel = new Booking($db);
$chargePointModel = new ChargePoint($db);

// Handle all booking actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['create_booking'])) {
            $bookingData = [
                'user_id' => $_POST['user_id'],
                'charge_point_id' => $_POST['charge_point_id'],
                'start_time' => $_POST['start_time'],
                'end_time' => $_POST['end_time'],
                'total_cost' => $_POST['total_cost'],
                'status' => 'pending'
            ];
            $bookingModel->create($bookingData);
            $_SESSION['success'] = 'Booking created successfully!';
        } 
        elseif (isset($_POST['cancel_booking'])) {
            $newStatus = ($_POST['current_status'] === 'cancelled') ? 'pending' : 'cancelled';
            $bookingModel->updateBookingStatus($_POST['booking_id'], $newStatus);
            $action = ($newStatus === 'cancelled') ? 'cancelled' : 'restored';
            $_SESSION['success'] = "Booking {$action} successfully!";
        }
        elseif (isset($_POST['update_status'])) {
            $bookingModel->updateBookingStatus($_POST['booking_id'], $_POST['new_status']);
            $_SESSION['success'] = "Booking status updated to {$_POST['new_status']}";
        }
        elseif (isset($_POST['submit_review'])) {
            // Handle review submission
            $_SESSION['success'] = 'Review submitted successfully!';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: booking.php');
    exit();
}

// Get the appropriate bookings based on user role
if ($_SESSION['role'] === 'homeowner') {
    $bookings = $bookingModel->getByHomeownerIdWithDetails($_SESSION['user_id']);
    require "Views/homeowner/booking.phtml";
} else {
    // Regular user view
    $bookings = $bookingModel->getByRentalUserIdWithDetails($_SESSION['user_id']);
    require "Views/rentalUser/booking.phtml";
}