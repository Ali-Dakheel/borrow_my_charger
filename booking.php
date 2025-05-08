<?php
// booking.php

session_start();
require_once __DIR__ . '/Core/bootstrap.php';
require_once 'Models/Booking.php';
require_once 'Models/ChargePoint.php';
require_once 'Models/Review.php';


if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json');
    $model = new Booking($db);
    $bookings = $model->getByRentalUserIdWithDetails($_SESSION['user_id']);
    echo json_encode($bookings);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 1) HANDLE POST *before* any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Use the 'action' field, not isset($_POST['create_booking'])
        switch ($_POST['action'] ?? '') {
            case 'create_booking':
                $bookingData = [
                    'user_id'         => $_POST['user_id'],
                    'charge_point_id' => $_POST['charge_point_id'],
                    'start_time'      => $_POST['start_time'],
                    'end_time'        => $_POST['end_time'],
                    'total_cost'      => $_POST['total_cost'],
                    'status'          => 'pending',
                ];
                (new Booking($db))->create($bookingData);
                $_SESSION['success'] = 'Booking created successfully!';
                break;

            case 'cancel_booking':
                $newStatus = ($_POST['current_status'] === 'cancelled')
                    ? 'pending' : 'cancelled';
                (new Booking($db))->updateBookingStatus(
                    $_POST['booking_id'],
                    $newStatus
                );
                $_SESSION['success'] = 'Booking ' .
                    ($newStatus === 'cancelled' ? 'cancelled' : 'restored') .
                    ' successfully!';
                break;

            case 'update_status':
                (new Booking($db))->updateBookingStatus(
                    $_POST['booking_id'],
                    $_POST['new_status']
                );
                $_SESSION['success'] =
                    'Status updated to ' . htmlspecialchars($_POST['new_status']);
                break;

            case 'submit_review':
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
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    // redirect *before* any output
    header('Location: booking.php');
    exit();
}

// 2) FALL‐THROUGH GET‐REQUEST: fetch bookings and render view
$bookingModel     = new Booking($db);
$chargePointModel = new ChargePoint($db);

if ($_SESSION['role'] === 'homeowner') {
    $bookings = $bookingModel->getByHomeownerIdWithDetails($_SESSION['user_id']);
    require __DIR__ . '/Views/homeowner/booking.phtml';
} elseif ($_SESSION['role'] === 'user') {
    $bookings = $bookingModel->getByRentalUserIdWithDetails($_SESSION['user_id']);
    require __DIR__ . '/Views/rentalUser/booking.phtml';
} else {
    $_SESSION['error'] = 'Invalid user role.';
    header('Location: dashboard.php');
    exit();
}
