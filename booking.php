<?php
require_once __DIR__ . '/Core/bootstrap.php';
require_once 'Models/Booking.php';
$bookingModel = new Booking($db);
// Simplified booking.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $bookingData = [
            'user_id' => $_POST['user_id'],
            'charge_point_id' => $_POST['charge_point_id'],
            'start_time' => $_POST['start_time'],
            'end_time' => $_POST['end_time'],
            'total_cost' => $_POST['total_cost'],
            'status' => 'pending' // Default status
        ];

        $bookingModel->create($bookingData);
        header('Location: booking_success.php');
        exit();
    } catch (Exception $e) {
        die('Booking failed: ' . $e->getMessage());
    }
}