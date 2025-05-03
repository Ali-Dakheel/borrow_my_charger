<?php
require_once __DIR__ . '/Core/bootstrap.php';
require_once 'Models/Booking.php';
require_once 'Models/ChargePoint.php';
require_once 'Models/Review.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$bookingModel    = new Booking($db);
$chargePointModel = new ChargePoint($db);
$reviewModel     = new Review($db);

// Handle all booking actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['create_booking'])) {
            $bookingData = [
                'user_id'         => $_POST['user_id'],
                'charge_point_id' => $_POST['charge_point_id'],
                'start_time'      => $_POST['start_time'],
                'end_time'        => $_POST['end_time'],
                'total_cost'      => $_POST['total_cost'],
                'status'          => 'pending'
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
            $_SESSION['success'] = "Booking status updated to " . htmlspecialchars($_POST['new_status']);
        }
        elseif (isset($_POST['submit_review'])) {
            $bookingId = $_POST['booking_id'];
            $reviewData = [
                'booking_id' => $bookingId,
                'rating'     => $_POST['rating'],
                'comment'    => $_POST['comment']
            ];
            // Check for existing review
            $existingReview = $reviewModel->getByBookingId($bookingId);
            if ($existingReview) {
                // Update existing review
                $reviewModel->update($existingReview['review_id'], $reviewData);
                $_SESSION['success'] = 'Review updated successfully!';
            } else {
                // Create new review
                $reviewModel->create($reviewData);
                $_SESSION['success'] = 'Review submitted successfully!';
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    header('Location: booking.php');
    exit();
}

// Fetch bookings based on user role
if ($_SESSION['role'] === 'homeowner') {
    // For homeowners, fetch bookings for charge points they own
    $bookings = $bookingModel->getByHomeownerIdWithDetails($_SESSION['user_id']);
    require "Views/homeowner/booking.phtml";
} elseif ($_SESSION['role'] === 'user') {
    // For rental users, fetch their own bookings
    $bookings = $bookingModel->getByRentalUserIdWithDetails($_SESSION['user_id']);
    require "Views/rentalUser/booking.phtml";
} else {
    $_SESSION['error'] = 'Invalid user role for booking management.';
    header('Location: dashboard.php');
    exit();
}
?>
