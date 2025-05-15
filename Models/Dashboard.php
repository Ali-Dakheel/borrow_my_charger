<?php

class Dashboard {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Admin dashboard methods
    public function getNewUsers24h() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ')->find()['count'];
    }
    
    public function getChargePoints24h() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM charge_points
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ')->find()['count'];
    }
    
    public function getBookings24h() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ')->find()['count'];
    }
    
    public function getReviews24h() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM reviews
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ')->find()['count'];
    }
    
    public function getPendingHomeowners() {
        return $this->db->query('
            SELECT id, username, created_at FROM users
            WHERE role = "homeowner" AND is_approved = 0 AND status = "pending"
            ORDER BY created_at DESC
            LIMIT 5
        ')->findAll();
    }
    
    public function getNewUsersWeek() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ')->find()['count'];
    }
    
    public function getNewUsersMonth() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ')->find()['count'];
    }
    
    public function getBookingsWeek() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ')->find()['count'];
    }
    
    public function getBookingsMonth() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ')->find()['count'];
    }
    
    public function getRevenue24h() {
        return $this->db->query('
            SELECT SUM(total_cost) as total FROM bookings
            WHERE status = "approved"
            AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ')->find()['total'] ?? 0;
    }
    
    public function getRevenueWeek() {
        return $this->db->query('
            SELECT SUM(total_cost) as total FROM bookings
            WHERE status = "approved"
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ')->find()['total'] ?? 0;
    }
    
    public function getRevenueMonth() {
        return $this->db->query('
            SELECT SUM(total_cost) as total FROM bookings
            WHERE status = "approved"
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ')->find()['total'] ?? 0;
    }
    
    public function getChargePointsWeek() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM charge_points
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ')->find()['count'];
    }
    
    public function getChargePointsMonth() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM charge_points
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ')->find()['count'];
    }
    
    // Homeowner dashboard methods
    public function getHomeownerChargePoint($homeownerId) {
        return $this->db->query('
            SELECT * FROM charge_points
            WHERE homeowner_id = ?
        ', [$homeownerId])->find();
    }
    
    public function getPendingBookingsCount($chargePointId) {
        return $this->db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE charge_point_id = ? AND status = "pending"
        ', [$chargePointId])->find()['count'] ?? 0;
    }
    
    public function getApprovedBookingsCount($chargePointId) {
        return $this->db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE charge_point_id = ? AND status = "approved"
        ', [$chargePointId])->find()['count'] ?? 0;
    }
    
    public function getTotalRevenue($chargePointId) {
        return $this->db->query('
            SELECT COALESCE(SUM(total_cost), 0) as total FROM bookings
            WHERE charge_point_id = ? AND status = "approved"
        ', [$chargePointId])->find()['total'] ?? 0;
    }
    
    public function getPendingBookingsList($chargePointId) {
        return $this->db->query('
            SELECT b.*, u.username 
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            WHERE b.charge_point_id = ? AND b.status = "pending"
            ORDER BY b.start_time ASC
            LIMIT 5
        ', [$chargePointId])->findAll() ?? [];
    }
    
    public function getRecentMessages($userId) {
        return $this->db->query('
            SELECT cm.*, u.username as sender, b.id as booking_id
            FROM contact_messages cm
            JOIN users u ON cm.sender_id = u.id
            JOIN bookings b ON cm.booking_id = b.id
            WHERE cm.recipient_id = ?
            ORDER BY cm.sent_at DESC
            LIMIT 5
        ', [$userId])->findAll() ?? [];
    }
    
    // User dashboard methods
    public function getUpcomingBookings($userId) {
        return $this->db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE user_id = ? AND status = "approved" AND end_time > NOW()
        ', [$userId])->find()['count'] ?? 0;
    }
    
    public function getPendingBookingsForUser($userId) {
        return $this->db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE user_id = ? AND status = "pending"
        ', [$userId])->find()['count'] ?? 0;
    }
    
    public function getCompletedBookings($userId) {
        return $this->db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE user_id = ? AND status = "approved" AND end_time < NOW()
        ', [$userId])->find()['count'] ?? 0;
    }
    
    public function getTotalSpent($userId) {
        return $this->db->query('
            SELECT COALESCE(SUM(total_cost), 0) as total FROM bookings
            WHERE user_id = ? AND status = "approved"
        ', [$userId])->find()['total'] ?? 0;
    }
    
    public function getNextBooking($userId) {
        return $this->db->query('
            SELECT b.*, cp.address
            FROM bookings b
            JOIN charge_points cp ON b.charge_point_id = cp.id
            WHERE b.user_id = ? AND b.status = "approved"
            AND b.start_time > NOW()
            ORDER BY b.start_time ASC
            LIMIT 1
        ', [$userId])->find();
    }
    
    public function getAvailableChargePointsCount() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM charge_points
            WHERE is_available = 1
        ')->find()['count'] ?? 0;
    }
    
    public function getAveragePrice() {
        return $this->db->query('
            SELECT AVG(price_per_kwh) as avg FROM charge_points
            WHERE is_available = 1
        ')->find()['avg'] ?? 0;
    }
    
    public function getRecentBookings($userId) {
        return $this->db->query('
            SELECT b.*, cp.address
            FROM bookings b
            JOIN charge_points cp ON b.charge_point_id = cp.id
            WHERE b.user_id = ?
            ORDER BY b.created_at DESC
            LIMIT 5
        ', [$userId])->findAll() ?? [];
    }
}