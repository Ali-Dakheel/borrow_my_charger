<?php
require_once __DIR__ . '/Core/bootstrap.php';
require_once 'Models/Report.php';
require_once 'Models/User.php';
require_once 'Models/Dashboard.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$reportModel = new Report($db);
$dashboardModel = new Dashboard($db);

// Get user role and route to appropriate dashboard
$userRole = $_SESSION['role'] ?? 'user';
$userId = $_SESSION['user_id'];

// Current date and time
$currentDateTime = new DateTime();
$currentUser = $_SESSION['username'];

// Fetch data for the dashboard based on user role
switch ($userRole) {
    case 'admin':
        // Get dashboard metrics for admin
        $metrics = $reportModel->getDashboardMetrics();
        
        // Process user stats
        $totalUsers = $metrics['users']['total'];
        $activeUsers = 0;
        $pendingUsers = 0;
        foreach ($metrics['users']['statuses'] as $status) {
            if ($status['status'] === 'active') $activeUsers = $status['count'];
            if ($status['status'] === 'pending') $pendingUsers = $status['count'];
        }
        
        // Get users created in the last 24 hours
        $newUsers24h = $db->query('
            SELECT COUNT(*) as count FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ')->find()['count'];
        
        // Process charge point stats
        $totalChargePoints = $metrics['chargePoints']['total'];
        $availableChargePoints = $metrics['chargePoints']['available'];
        $avgPrice = $metrics['chargePoints']['avg_price'];
        
        // Get charge points created in the last 24 hours
        $chargePoints24h = $db->query('
            SELECT COUNT(*) as count FROM charge_points
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ')->find()['count'];
        
        // Process booking stats
        $totalBookings = $metrics['bookings']['total'];
        $totalRevenue = $metrics['bookings']['revenue'];
        $pendingBookings = 0;
        foreach ($metrics['bookings']['statuses'] as $status) {
            if ($status['status'] === 'pending') $pendingBookings = $status['count'];
        }
        
        // Get bookings created in the last 24 hours
        $bookings24h = $db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ')->find()['count'];
        
        // Process review stats
        $totalReviews = $metrics['activity']['reviews'];
        $avgRating = $metrics['activity']['avg_rating'];
        
        // Get reviews created in the last 24 hours
        $reviews24h = $db->query('
            SELECT COUNT(*) as count FROM reviews
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ')->find()['count'];
        
        // Get pending homeowners to approve
        $pendingHomeowners = $db->query('
            SELECT id, username, created_at FROM users
            WHERE role = "homeowner" AND is_approved = 0 AND status = "pending"
            ORDER BY created_at DESC
            LIMIT 5
        ')->findAll();
        
        // Get recent system activity
        $recentActivity = $reportModel->getRecentSystemActivity();
        
        // Get additional statistics for the system statistics table
        $newUsersWeek = $db->query('
            SELECT COUNT(*) as count FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ')->find()['count'];
        
        $newUsersMonth = $db->query('
            SELECT COUNT(*) as count FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ')->find()['count'];
        
        $bookingsWeek = $db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ')->find()['count'];
        
        $bookingsMonth = $db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ')->find()['count'];
        
        $revenue24h = $db->query('
            SELECT SUM(total_cost) as total FROM bookings
            WHERE status = "approved"
            AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ')->find()['total'] ?? 0;
        
        $revenueWeek = $db->query('
            SELECT SUM(total_cost) as total FROM bookings
            WHERE status = "approved"
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ')->find()['total'] ?? 0;
        
        $revenueMonth = $db->query('
            SELECT SUM(total_cost) as total FROM bookings
            WHERE status = "approved"
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ')->find()['total'] ?? 0;
        
        $chargePointsWeek = $db->query('
            SELECT COUNT(*) as count FROM charge_points
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ')->find()['count'];
        
        $chargePointsMonth = $db->query('
            SELECT COUNT(*) as count FROM charge_points
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ')->find()['count'];
        
        require "Views/admin/dashboard.phtml";
        break;
        
    case 'homeowner':
        // Get homeowner's charge point information
        $chargePoint = $dashboardModel->getHomeownerChargePoint($userId);
        
        // Get booking statistics
        $chargePointId = $chargePoint['id'] ?? null;
        $bookingStats = $dashboardModel->getHomeownerBookingStats($chargePointId);
        
        $pendingBookings = $bookingStats['pending'];
        $upcomingBookings = $bookingStats['upcoming'];
        $bookingsToday = $bookingStats['today'];
        $totalRevenue = $bookingStats['total_revenue'];
        $revenueThisMonth = $bookingStats['revenue_this_month'];
        $completedBookings = $bookingStats['completed'];
        
        // Get pending booking requests
        $pendingBookingsList = $dashboardModel->getPendingBookings($chargePointId);
        
        // Get recent messages
        $recentMessages = $dashboardModel->getRecentMessages($userId);
        
        // Get performance metrics
        $performanceMetrics = $dashboardModel->getPerformanceMetrics($chargePointId);
        
        $averageRating = $performanceMetrics['average_rating'];
        $totalReviews = $performanceMetrics['total_reviews'];
        $approvalRate = $performanceMetrics['approval_rate'];
        $approvedBookings = $performanceMetrics['approved_bookings'];
        $totalProcessedBookings = $performanceMetrics['total_processed_bookings'];
        $performanceRank = $performanceMetrics['performance_rank'];
        $performancePercentile = $performanceMetrics['performance_percentile'];
        
        // Helper function for ordinal numbers
        function ordinalNumber($number) {
            $suffix = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
            if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
                return $number . 'th';
            } else {
                return $number . $suffix[$number % 10];
            }
        }
        
        require "Views/homeowner/dashboard.phtml";
        break;
        
    case 'user':
    default:
        // Get nearby charge points
        $nearbyChargePoints = $dashboardModel->getNearbyChargePoints($userId);
        $searchRadius = 10; // Default radius in miles
        
        // Get average price of nearby charge points
        $avgPrice = $dashboardModel->getAverageChargePointPrice();
        
        // Get new charge points added in the last week
        $newChargePoints = $dashboardModel->getNewChargePoints(7);
        
        // Get user's bookings
        $bookingStats = $dashboardModel->getUserBookingStats($userId);
        $upcomingBookings = $bookingStats['upcoming'];
        $pendingBookings = $bookingStats['pending'];
        
        // Get next booking if available
        $nextBooking = $dashboardModel->getNextBooking($userId);
        
        // Get message statistics
        $messageStats = $dashboardModel->getUserMessageStats($userId);
        $unreadMessages = $messageStats['unread'];
        $totalConversations = $messageStats['conversations'];
        $newMessages24h = $messageStats['new_today'];
        
        // Get recent bookings
        $recentBookings = $dashboardModel->getUserRecentBookings($userId, 5);
        
        // Get recent messages
        $recentMessages = $dashboardModel->getUserRecentMessages($userId, 5);
        
        require "Views/rentalUser/dashboard.phtml";
        break;
}