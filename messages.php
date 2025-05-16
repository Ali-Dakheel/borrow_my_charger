<?php
require_once __DIR__ . '/Core/bootstrap.php';
require_once 'Models/UserData.php';
require_once 'Models/UserDataset.php';
require_once 'Models/BookingData.php';
require_once 'Models/BookingDataset.php';
require_once 'Models/ChargePointData.php';
require_once 'Models/ChargePointDataset.php';
require_once 'Models/ContactMessageData.php';
require_once 'Models/ContactMessageDataset.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user is a homeowner
if ($_SESSION['role'] !== 'homeowner') {
    $errorMessage = "Access Denied";
    $errorDetails = "You don't have permission to access this page. This area is restricted to homeowners only.";
    $backLink = "dashboard.php";
    require_once 'Views/error.phtml';
    exit();
}

// Initialize datasets
$userDataset = new UserDataset($db);
$bookingDataset = new BookingDataset($db);
$chargePointDataset = new ChargePointDataset($db);
$messageDataset = new ContactMessageDataset($db);

// Get the homeowner's charge point
$chargePoint = $chargePointDataset->getByHomeOwnerById($_SESSION['user_id']);
$userId = $_SESSION['user_id'];
$successMessage = null;
$errorMessage = null;

// Handle sending a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    try {
        // Validate data
        if (empty($_POST['message']) || !isset($_POST['booking_id']) || !isset($_POST['recipient_id'])) {
            throw new Exception("All fields are required");
        }

        // Create new message
        $messageData = [
            'booking_id' => $_POST['booking_id'],
            'sender_id' => $userId,
            'recipient_id' => $_POST['recipient_id'],
            'message' => trim($_POST['message'])
        ];

        $messageDataset->create($messageData);
        $successMessage = "Your message has been sent successfully!";
    } catch (Exception $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

// Get active conversations (bookings with messages)
$activeConversations = [];
if ($chargePoint) {
    // Get all bookings for this charge point
    $bookings = $bookingDataset->getBookingsByChargePointId($chargePoint->getId());
    
    // Group messages by booking
    foreach ($bookings as $booking) {
        $bookingId = $booking['id'];
        $messages = $messageDataset->getMessagesByBookingId($bookingId);
        
        if (!empty($messages)) {
            // Add user details
            $renterUser = $userDataset->getUserById($booking['user_id']);
            $renterName = $renterUser->getName();
            
            // Format booking date/time for display
            $bookingDateTime = date('M j, Y g:i A', strtotime($booking['start_time']));
            
            // Count unread messages (future feature)
            $unreadCount = 0; // Placeholder for future implementation
            
            // Get most recent message
            $latestMessage = end($messages);
            $latestMessageTime = date('M j, g:i A', strtotime($latestMessage['sent_at']));
            
            $activeConversations[] = [
                'booking_id' => $bookingId,
                'renter_id' => $booking['user_id'],
                'renter_name' => $renterName,
                'booking_date' => $bookingDateTime,
                'status' => $booking['status'],
                'latest_message' => $latestMessage['message'],
                'latest_message_time' => $latestMessageTime,
                'unread_count' => $unreadCount,
                'messages' => $messages
            ];
        }
    }
}

$selectedConversation = null;
$selectedBookingId = $_GET['booking_id'] ?? null;

if ($selectedBookingId) {
    foreach ($activeConversations as $conversation) {
        if ($conversation['booking_id'] == $selectedBookingId) {
            $selectedConversation = $conversation;
            break;
        }
    }
}

require 'Views/homeowner/messages.phtml';