<?php
// booking_detail.php

session_start();
require_once __DIR__ . '/Core/bootstrap.php';
require_once 'Models/Booking.php';
require_once 'Models/ChargePoint.php';
require_once 'Models/Review.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'Booking ID is required';
    header('Location: booking.php');
    exit();
}

$bookingId = (int)$_GET['id'];
$bookingModel = new Booking($db);

if ($_SESSION['role'] === 'user') {
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
    
    require __DIR__ . '/Views/rentalUser/bookingDetail.phtml';
} elseif ($_SESSION['role'] === 'homeowner') {
    // Get booking details for homeowner
    $booking = $bookingModel->getBookingWithDetailsByIdForHomeowner($bookingId, $_SESSION['user_id']);
    
    if (!$booking) {
        $_SESSION['error'] = 'Booking not found or you do not have permission to view it';
        header('Location: booking.php');
        exit();
    }
    
    // Get review if exists
    $reviewModel = new Review($db);
    $review = $reviewModel->getByBookingId($bookingId);
    
    require __DIR__ . '/Views/homeowner/bookingDetail.phtml';
} else {
    $_SESSION['error'] = 'Invalid user role';
    header('Location: dashboard.php');
    exit();
}