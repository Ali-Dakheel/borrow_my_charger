<?php

class Report
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Get total users count breakdown by role
     */
    public function getUsersStats()
    {
        $totalUsers = $this->db->query('SELECT COUNT(*) as total FROM users')->find()['total'];
        
        $roleBreakdown = $this->db->query('
            SELECT 
                role, 
                COUNT(*) as count 
            FROM users 
            GROUP BY role
        ')->findAll();
        
        $statusBreakdown = $this->db->query('
            SELECT 
                status, 
                COUNT(*) as count 
            FROM users 
            GROUP BY status
        ')->findAll();
        
        return [
            'total' => $totalUsers,
            'roles' => $roleBreakdown,
            'statuses' => $statusBreakdown
        ];
    }

    /**
     * Get charge points statistics
     */
    public function getChargePointsStats()
    {
        $totalChargePoints = $this->db->query('SELECT COUNT(*) as total FROM charge_points')->find()['total'];
        
        $availableChargePoints = $this->db->query('
            SELECT COUNT(*) as count FROM charge_points WHERE is_available = 1
        ')->find()['count'];
        
        $avgPrice = $this->db->query('
            SELECT AVG(price_per_kwh) as avg_price FROM charge_points
        ')->find()['avg_price'];
        
        // Get charge points added in the last 30 days
        $recentlyAdded = $this->db->query('
            SELECT COUNT(*) as count FROM charge_points 
            WHERE created_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 DAY)
        ')->find()['count'];
        
        return [
            'total' => $totalChargePoints,
            'available' => $availableChargePoints,
            'avg_price' => $avgPrice,
            'recently_added' => $recentlyAdded
        ];
    }

    /**
     * Get booking statistics
     */
    public function getBookingsStats()
    {
        $totalBookings = $this->db->query('SELECT COUNT(*) as total FROM bookings')->find()['total'];
        
        $statusBreakdown = $this->db->query('
            SELECT 
                status, 
                COUNT(*) as count 
            FROM bookings 
            GROUP BY status
        ')->findAll();
        
        $totalRevenue = $this->db->query('
            SELECT SUM(total_cost) as total FROM bookings 
            WHERE status = "approved"
        ')->find()['total'] ?? 0;
        
        // Get bookings created in the last 30 days
        $recentBookings = $this->db->query('
            SELECT COUNT(*) as count FROM bookings 
            WHERE created_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 DAY)
        ')->find()['count'];
        
        return [
            'total' => $totalBookings,
            'statuses' => $statusBreakdown,
            'revenue' => $totalRevenue,
            'recent' => $recentBookings
        ];
    }

    /**
     * Get system-wide activity stats
     */
    public function getActivityStats()
    {
        // Total messages sent
        $totalMessages = $this->db->query('
            SELECT COUNT(*) as total FROM contact_messages
        ')->find()['total'];
        
        // Average rating from reviews
        $avgRating = $this->db->query('
            SELECT AVG(rating) as avg_rating FROM reviews
        ')->find()['avg_rating'] ?? 0;
        
        // Count of reviews
        $totalReviews = $this->db->query('
            SELECT COUNT(*) as total FROM reviews
        ')->find()['total'];
        
        // Most active day (day of week with most bookings)
        $mostActiveDay = $this->db->query('
            SELECT 
                DAYNAME(created_at) as day_name, 
                COUNT(*) as count 
            FROM bookings 
            GROUP BY day_name 
            ORDER BY count DESC 
            LIMIT 1
        ')->find();
        
        return [
            'messages' => $totalMessages,
            'reviews' => $totalReviews,
            'avg_rating' => $avgRating,
            'most_active_day' => $mostActiveDay['day_name'] ?? 'N/A'
        ];
    }
    
    /**
     * Get system metrics for quick dashboard view
     */
    public function getDashboardMetrics()
    {
        return [
            'users' => $this->getUsersStats(),
            'chargePoints' => $this->getChargePointsStats(),
            'bookings' => $this->getBookingsStats(),
            'activity' => $this->getActivityStats()
        ];
    }
    
    /**
     * Get recent system activity for the admin dashboard
     */
    public function getRecentSystemActivity()
    {
        $activities = [];
        
        // Recent user registrations
        $newUsers = $this->db->query('
            SELECT id, username, name, role, created_at 
            FROM users 
            ORDER BY created_at DESC 
            LIMIT 3
        ')->findAll();
        
        foreach ($newUsers as $user) {
            $activities[] = [
                'title' => 'New User Registration',
                'description' => "User {$user['username']} ({$user['role']}) has registered",
                'user' => $user['name'],
                'time' => $user['created_at'],
                'icon' => 'bi-person-plus'
            ];
        }
        
        // Recent bookings
        $newBookings = $this->db->query('
            SELECT b.id, b.status, b.created_at, b.total_cost, 
                   u.username as user_username, u.name as user_name,
                   cp.address as charge_point_address
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN charge_points cp ON b.charge_point_id = cp.id
            ORDER BY b.created_at DESC
            LIMIT 3
        ')->findAll();
        
        foreach ($newBookings as $booking) {
            $activities[] = [
                'title' => 'New Booking ' . ucfirst($booking['status']),
                'description' => "Booking at {$booking['charge_point_address']} (£{$booking['total_cost']})",
                'user' => $booking['user_name'],
                'time' => $booking['created_at'],
                'icon' => 'bi-calendar-plus'
            ];
        }
        
        // Recent reviews
        $newReviews = $this->db->query('
            SELECT r.rating, r.comment, r.created_at,
                   u.name as user_name,
                   cp.address as charge_point_address
            FROM reviews r
            JOIN bookings b ON r.booking_id = b.id
            JOIN users u ON b.user_id = u.id
            JOIN charge_points cp ON b.charge_point_id = cp.id
            ORDER BY r.created_at DESC
            LIMIT 3
        ')->findAll();
        
        foreach ($newReviews as $review) {
            $activities[] = [
                'title' => "New {$review['rating']}-Star Review",
                'description' => "Review for {$review['charge_point_address']}: \"" . substr($review['comment'], 0, 50) . (strlen($review['comment']) > 50 ? '...' : '') . "\"",
                'user' => $review['user_name'],
                'time' => $review['created_at'],
                'icon' => 'bi-star'
            ];
        }
        
        // Sort by time (newest first)
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });
        
        // Take only the 10 most recent
        return array_slice($activities, 0, 10);
    }

    /**
     * Get user growth data for the chart (last 12 months)
     */
    public function getUserGrowthData()
    {
        return $this->db->query('
            SELECT 
                DATE_FORMAT(created_at, "%b %Y") as month,
                COUNT(*) as count
            FROM users
            WHERE created_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, "%Y-%m")
            ORDER BY MIN(created_at)
        ')->findAll();
    }

    /**
     * Get monthly revenue data for the chart (last 6 months)
     */
    public function getMonthlyRevenueData()
    {
        return $this->db->query('
            SELECT 
                DATE_FORMAT(created_at, "%b %Y") as month,
                SUM(total_cost) as revenue
            FROM bookings
            WHERE status = "approved"
            AND created_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, "%Y-%m")
            ORDER BY MIN(created_at)
        ')->findAll();
    }

    /**
     * Get booking trends data for the chart (last 30 days)
     */
    public function getBookingsTrendData()
    {
        $dates = $this->db->query('
            SELECT 
                DATE_FORMAT(d, "%d %b") as date
            FROM (
                SELECT CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as d
                FROM (SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) as a
                CROSS JOIN (SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) as b
                CROSS JOIN (SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) as c
            ) as dates
            WHERE d BETWEEN DATE_SUB(CURDATE(), INTERVAL 29 DAY) AND CURDATE()
            ORDER BY d
        ')->findAll();
        
        $approved = $this->db->query('
            SELECT 
                DATE_FORMAT(created_at, "%d %b") as date,
                COUNT(*) as count
            FROM bookings
            WHERE status = "approved"
            AND created_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 DAY)
            GROUP BY DATE_FORMAT(created_at, "%d %b")
            ORDER BY MIN(created_at)
        ')->findAll();
        
        $pending = $this->db->query('
            SELECT 
                DATE_FORMAT(created_at, "%d %b") as date,
                COUNT(*) as count
            FROM bookings
            WHERE status = "pending"
            AND created_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 DAY)
            GROUP BY DATE_FORMAT(created_at, "%d %b")
            ORDER BY MIN(created_at)
        ')->findAll();
        
        $declined = $this->db->query('
            SELECT 
                DATE_FORMAT(created_at, "%d %b") as date,
                COUNT(*) as count
            FROM bookings
            WHERE status = "declined"
            AND created_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 DAY)
            GROUP BY DATE_FORMAT(created_at, "%d %b")
            ORDER BY MIN(created_at)
        ')->findAll();
        
        // Create associative arrays for easy lookup
        $approvedMap = [];
        foreach ($approved as $item) {
            $approvedMap[$item['date']] = $item['count'];
        }
        
        $pendingMap = [];
        foreach ($pending as $item) {
            $pendingMap[$item['date']] = $item['count'];
        }
        
        $declinedMap = [];
        foreach ($declined as $item) {
            $declinedMap[$item['date']] = $item['count'];
        }
        
        // Create complete dataset with zeros for missing dates
        $approvedComplete = [];
        $pendingComplete = [];
        $declinedComplete = [];
        
        foreach ($dates as $date) {
            $d = $date['date'];
            $approvedComplete[] = ['date' => $d, 'count' => $approvedMap[$d] ?? 0];
            $pendingComplete[] = ['date' => $d, 'count' => $pendingMap[$d] ?? 0];
            $declinedComplete[] = ['date' => $d, 'count' => $declinedMap[$d] ?? 0];
        }
        
        return [
            'dates' => $dates,
            'approved' => $approvedComplete,
            'pending' => $pendingComplete,
            'declined' => $declinedComplete
        ];
    }

    /**
     * Execute custom SQL query
     * 
     * Allows for executing arbitrary SQL queries for custom reporting needs
     *
     * @param string $query The SQL query to execute
     * @return array The result of the query as an array of records
     */
    public function executeQuery($query)
    {
        $this->db->query($query);
        return $this->db->findAll();
    }
    
    /**
     * Helper function to format time ago
     * 
     * Converts a datetime string to a human-readable "time ago" format
     * For example: "2 hours ago", "3 days ago", etc.
     *
     * @param string $datetime The datetime string to format
     * @return string Human-readable time difference
     */
    public function timeAgo($datetime)
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $timestamp);
        }
    }
}