<?php
require_once __DIR__ . '/Core/bootstrap.php';
require_once __DIR__ . '/Models/ChargePoint.php';
require_once __DIR__ . '/Models/ContactMessages.php';
$chargePoint = new ChargePoint($db);
$contactMessage = new ContactMessage($db);

// Initialize variables for messages
$errorMessage = null;
$successMessage = null;

// Fetch all homeowners with charge points
$homeowners = $chargePoint->getAllHomeownersWithChargePoints();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'] ?? null;
    $senderId = $_POST['sender_id'] ?? null;
    $recipientId = $_POST['recipient_id'] ?? null;
    $message = $_POST['message'] ?? '';

    if ($bookingId && $senderId && $recipientId && $message) {
        try {
            $contactMessage->createMessage($bookingId, $senderId, $recipientId, $message);
            $successMessage = "Your message has been sent successfully.";
        } catch (Exception $e) {
            $errorMessage = "An error occurred while sending your message: " . $e->getMessage();
        }
    } else {
        $errorMessage = "All fields are required. Please fill out the form completely.";
    }
}

require_once __DIR__ . '/Views/rentalUser/contactHomeowner.phtml';