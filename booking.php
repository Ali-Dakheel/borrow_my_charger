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

// Handle booking creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_booking'])) {
        try {
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
        } catch (Exception $e) {
            $_SESSION['error'] = 'Booking failed: ' . $e->getMessage();
        }
    } 
    
    // Handle booking cancellation
    elseif (isset($_POST['cancel_booking'])) {
        try {
            $bookingModel->cancelBooking($_POST['booking_id']);
            $_SESSION['success'] = 'Booking cancelled successfully!';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Cancellation failed: ' . $e->getMessage();
        }
    }
    
    header('Location: booking.php');
    exit();
}
// Handle review submission
elseif (isset($_POST['submit_review'])) {
    try {
        $reviewData = [
            'booking_id' => $_POST['booking_id'],
            'user_id' => $_SESSION['user_id'],
            'review_text' => $_POST['review_text']
        ];
        // $bookingModel->submitReview($reviewData);
        $_SESSION['success'] = 'Review submitted successfully!';
    } catch (Exception $e) {
        $_SESSION['error'] = 'Failed to submit review: ' . $e->getMessage();
    }
}


// Fetch all bookings for the logged-in user
$bookings = $bookingModel->getByRentalUserIdWithDetails($_SESSION['user_id']);

require "Views/rentalUser/booking.phtml";
