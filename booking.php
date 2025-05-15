<?php
require_once __DIR__ . '/Core/bootstrap.php';
require_once 'Models/BookingData.php';
require_once 'Models/BookingDataset.php';
require_once 'Models/ChargePointData.php';
require_once 'Models/ChargePointDataset.php';
require_once 'Models/ReviewData.php';
require_once 'Models/ReviewDataset.php';

if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json');
    $bookingDataset = new BookingDataset($db);
    $bookings = $bookingDataset->getByRentalUserIdWithDetails($_SESSION['user_id']);
    echo json_encode($bookings);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
if (isset($_POST['return_detail']) && $_POST['return_detail']) {
    header('Location: booking_detail.php?id=' . $_POST['booking_id']);
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
                $bookingDataset = new BookingDataset($db);
                $bookingDataset->create($bookingData);
                $_SESSION['success'] = 'Booking created successfully!';
                break;

            case 'cancel_booking':
                $newStatus = ($_POST['current_status'] === 'cancelled')
                    ? 'pending' : 'cancelled';
                $bookingDataset = new BookingDataset($db);
                $bookingDataset->updateBookingStatus(
                    $_POST['booking_id'],
                    $newStatus
                );
                $_SESSION['success'] = 'Booking ' .
                    ($newStatus === 'cancelled' ? 'cancelled' : 'restored') .
                    ' successfully!';
                break;

            case 'update_status':
                $bookingDataset = new BookingDataset($db);
                $bookingDataset->updateBookingStatus(
                    $_POST['booking_id'],
                    $_POST['new_status']
                );
                $_SESSION['success'] =
                    'Status updated to ' . htmlspecialchars($_POST['new_status']);
                break;

            case 'submit_review':
                $reviewsDataset = new ReviewsDataset($db);
                $existing = $reviewsDataset->getByBookingId($_POST['booking_id']);
                
                if ($existing) {
                    $reviewsDataset->update($existing->getReviewId(), $_POST['rating'], $_POST['comment']);
                    $_SESSION['success'] = 'Review updated successfully!';
                } else {
                    $reviewsDataset->create($_POST['booking_id'], $_POST['rating'], $_POST['comment']);
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
$bookingDataset = new BookingDataset($db);
$chargePointDataset = new ChargePointDataset($db);

if ($_SESSION['role'] === 'homeowner') {
    $bookings = $bookingDataset->getByHomeownerIdWithDetails($_SESSION['user_id']);
    require __DIR__ . '/Views/homeowner/booking.phtml';
} elseif ($_SESSION['role'] === 'user') {
    $bookings = $bookingDataset->getByRentalUserIdWithDetails($_SESSION['user_id']);
    require __DIR__ . '/Views/rentalUser/booking.phtml';
} else {
    $_SESSION['error'] = 'Invalid user role.';
    header('Location: dashboard.php');
    exit();
}