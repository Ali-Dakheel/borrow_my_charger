<?php
require_once __DIR__ . '/Core/bootstrap.php';
require_once 'Models/Report.php';
require_once 'Models/UserData.php';
require_once 'Models/UserDataset.php';
require_once 'Models/Dashboard.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Initialize models
$reportModel = new Report($db);
$dashboardModel = new Dashboard($db);
$userDataset = new UserDataset($db);

// Get user role and route to appropriate dashboard
$userRole = $_SESSION['role'] ?? 'user';
$userId = $_SESSION['user_id'];

// Set current date/time and username
$currentDateTime = date('Y-m-d H:i:s');
$currentUser = $_SESSION['username'];

switch ($userRole) {
    case 'admin':
        // Get dashboard metrics for admin (keeping your admin code as is)
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
        $newUsers24h = $dashboardModel->getNewUsers24h();
        
        // Process charge point stats
        $totalChargePoints = $metrics['chargePoints']['total'];
        $availableChargePoints = $metrics['chargePoints']['available'];
        $avgPrice = $metrics['chargePoints']['avg_price'];
        
        // Get charge points created in the last 24 hours
        $chargePoints24h = $dashboardModel->getChargePoints24h();
        
        // Process booking stats
        $totalBookings = $metrics['bookings']['total'];
        $totalRevenue = $metrics['bookings']['revenue'];
        $pendingBookings = 0;
        foreach ($metrics['bookings']['statuses'] as $status) {
            if ($status['status'] === 'pending') $pendingBookings = $status['count'];
        }
        
        // Get bookings created in the last 24 hours
        $bookings24h = $dashboardModel->getBookings24h();
        
        // Process review stats
        $totalReviews = $metrics['activity']['reviews'];
        $avgRating = $metrics['activity']['avg_rating'];
        
        // Get reviews created in the last 24 hours
        $reviews24h = $dashboardModel->getReviews24h();
        
        // Get pending homeowners to approve - use UserDataset instead of Dashboard
        $pendingHomeowners = $userDataset->getAllPendingHomeowners();
        
        // Get recent system activity
        $recentActivity = $reportModel->getRecentSystemActivity();
        
        // Get additional statistics for the system statistics table
        $newUsersWeek = $dashboardModel->getNewUsersWeek();
        $newUsersMonth = $dashboardModel->getNewUsersMonth();
        $bookingsWeek = $dashboardModel->getBookingsWeek();
        $bookingsMonth = $dashboardModel->getBookingsMonth();
        $revenue24h = $dashboardModel->getRevenue24h();
        $revenueWeek = $dashboardModel->getRevenueWeek();
        $revenueMonth = $dashboardModel->getRevenueMonth();
        $chargePointsWeek = $dashboardModel->getChargePointsWeek();
        $chargePointsMonth = $dashboardModel->getChargePointsMonth();
        
        require "Views/admin/dashboard.phtml";
        break;
        
    case 'homeowner':
        // Simple counts for homeowner dashboard
        
        // Get homeowner's charge point
        $chargePoint = $dashboardModel->getHomeownerChargePoint($userId);
        $chargePointId = $chargePoint['id'] ?? null;
        
        // Check if homeowner is approved using UserDataset
        $isApproved = $userDataset->isHomeownerApproved($userId);
        
        // Basic stats
        if ($chargePointId) {
            // Pending bookings count
            $pendingBookings = $dashboardModel->getPendingBookingsCount($chargePointId);
            
            // Approved bookings count
            $approvedBookings = $dashboardModel->getApprovedBookingsCount($chargePointId);
            
            // Revenue
            $totalRevenue = $dashboardModel->getTotalRevenue($chargePointId);
            
            // Get pending booking requests (simple list)
            $pendingBookingsList = $dashboardModel->getPendingBookingsList($chargePointId);
            
            // Get recent messages
            $recentMessages = $dashboardModel->getRecentMessages($userId);
            
        } else {
            $pendingBookings = 0;
            $approvedBookings = 0;
            $totalRevenue = 0;
            $pendingBookingsList = [];
            $recentMessages = [];
        }
        
        require "Views/homeowner/dashboard.phtml";
        break;
        
    case 'user':
    default:
        // Simple stats for user dashboard
        
        // Count user's bookings
        $upcomingBookings = $dashboardModel->getUpcomingBookings($userId);
        $pendingBookings = $dashboardModel->getPendingBookingsForUser($userId);
        $completedBookings = $dashboardModel->getCompletedBookings($userId);
        
        // Total spent
        $totalSpent = $dashboardModel->getTotalSpent($userId);
        
        // Next booking
        $nextBooking = $dashboardModel->getNextBooking($userId);
        
        // Count available charge points (simplified)
        $availableChargePoints = $dashboardModel->getAvailableChargePointsCount();
        
        // Average price
        $avgPrice = $dashboardModel->getAveragePrice();
        
        // Recent bookings
        $recentBookings = $dashboardModel->getRecentBookings($userId);
        
        require "Views/rentalUser/dashboard.phtml";
        break;
}