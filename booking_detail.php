<?php
require_once __DIR__ . '/Core/bootstrap.php';
require_once 'Models/Booking.php';
require_once 'Models/ChargePoint.php';
require_once 'Models/Review.php';
require_once 'Models/ContactMessages.php';

// Handle POST requests for submitting reviews or sending messages
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        switch ($_POST['action'] ?? '') {
            case 'submit_review':
                // Verify the booking is eligible for review
                $bookingModel = new Booking($db);
                $booking = $bookingModel->getBookingWithDetailsByIdForUser($_POST['booking_id'], $_SESSION['user_id']);
                
                if (!$booking) {
                    throw new Exception("Booking not found");
                }
                
                if ($booking['status'] === 'pending') {
                    throw new Exception("You cannot review pending bookings");
                }
                
                // Get current date and time
                $currentTime = new DateTime('2025-05-15 05:01:03'); // Use the provided current time
                $bookingEndTime = new DateTime($booking['end_time']);
                
                if ($bookingEndTime > $currentTime) {
                    throw new Exception("You can only review bookings that have ended");
                }
                
                $reviewModel = new Review($db);
                $existing = $reviewModel->getByBookingId($_POST['booking_id']);
                $data = [
                    'booking_id' => $_POST['booking_id'],
                    'rating'     => $_POST['rating'],
                    'comment'    => $_POST['comment'],
                ];
                if ($existing) {
                    $reviewModel->update($existing['review_id'], $data);
                    $_SESSION['success'] = 'Review updated successfully!';
                } else {
                    $reviewModel->create($data);
                    $_SESSION['success'] = 'Review submitted successfully!';
                }
                break;
                
            case 'send_message':
                $messageModel = new ContactMessage($db);
                $messageData = [
                    'booking_id'   => $_POST['booking_id'],
                    'sender_id'    => $_SESSION['user_id'],
                    'recipient_id' => $_POST['recipient_id'],
                    'message'      => $_POST['message']
                ];
                $messageModel->create($messageData);
                
                // Create a notification for the homeowner
                $bookingNumber = $_POST['booking_id'];
                $notificationData = [
                    'user_id' => $_POST['recipient_id'],
                    'message' => "New message regarding booking #{$bookingNumber}",
                    'is_read' => false
                ];
                
                $_SESSION['success'] = 'Message sent successfully!';
                break;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    // Redirect to the same page to prevent form resubmission
    header('Location: booking_detail.php?id=' . $_POST['booking_id']);
    exit();
}

// Check for logged in user
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check for rental user role
if ($_SESSION['role'] !== 'user') {
    $_SESSION['error'] = 'You do not have permission to view booking details';
    header('Location: dashboard.php');
    exit();
}

// Check for booking ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'Booking ID is required';
    header('Location: booking.php');
    exit();
}

$bookingId = (int)$_GET['id'];
$bookingModel = new Booking($db);

// Get booking details for rental user
$booking = $bookingModel->getBookingWithDetailsByIdForUser($bookingId, $_SESSION['user_id']);

if (!$booking) {
    $_SESSION['error'] = 'Booking not found or you do not have permission to view it';
    header('Location: booking.php');
    exit();
}

// Get review if exists
$reviewModel = new Review($db);
$review = $reviewModel->getByBookingId($bookingId);

// Get messages for this booking
$messageModel = new ContactMessage($db);
$messages = $messageModel->getMessagesByBookingId($bookingId);

// Check if booking is eligible for review:
// 1. Status is not 'pending'
// 2. End time has passed
$currentTime = new DateTime(); // Use the provided current time
$bookingEndTime = new DateTime($booking['end_time']);
$canReview = ($booking['status'] !== 'pending' && $bookingEndTime <= $currentTime);

require __DIR__ . '/Views/rentalUser/bookingDetail.phtml';