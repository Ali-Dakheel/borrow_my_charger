<?php

/**
 * Dashboard Model
 * 
 * Handles data retrieval for admin, homeowner and user dashboards
 */
class Dashboard {
    /** @var object Database connection instance */
    private $db;
    
    /**
     * Constructor
     * 
     * @param object $db Database connection instance
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Admin dashboard methods
    /**
     * Get count of new users registered in the last 24 hours
     * 
     * @return int Number of new users
     */
    public function getNewUsers24h() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ')->find()['count'];
    }
    
    /**
     * Get count of charge points created in the last 24 hours
     * 
     * @return int Number of new charge points
     */
    public function getChargePoints24h() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM charge_points
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ')->find()['count'];
    }
    
    /**
     * Get count of bookings made in the last 24 hours
     * 
     * @return int Number of new bookings
     */
    public function getBookings24h() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ')->find()['count'];
    }
    
    /**
     * Get count of reviews submitted in the last 24 hours
     * 
     * @return int Number of new reviews
     */
    public function getReviews24h() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM reviews
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ')->find()['count'];
    }
    
    /**
     * Get list of homeowners waiting for approval
     * 
     * @return array List of pending homeowner users
     */
    public function getPendingHomeowners() {
        return $this->db->query('
            SELECT id, username, created_at FROM users
            WHERE role = "homeowner" AND is_approved = 0 AND status = "pending"
            ORDER BY created_at DESC
            LIMIT 5
        ')->findAll();
    }
    
    /**
     * Get count of new users registered in the last 7 days
     * 
     * @return int Number of new users
     */
    public function getNewUsersWeek() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ')->find()['count'];
    }
    
    /**
     * Get count of new users registered in the last 30 days
     * 
     * @return int Number of new users
     */
    public function getNewUsersMonth() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ')->find()['count'];
    }
    
    /**
     * Get count of bookings made in the last 7 days
     * 
     * @return int Number of bookings
     */
    public function getBookingsWeek() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ')->find()['count'];
    }
    
    /**
     * Get count of bookings made in the last 30 days
     * 
     * @return int Number of bookings
     */
    public function getBookingsMonth() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ')->find()['count'];
    }
    
    /**
     * Get total revenue from approved bookings in the last 24 hours
     * 
     * @return float Total revenue amount
     */
    public function getRevenue24h() {
        return $this->db->query('
            SELECT SUM(total_cost) as total FROM bookings
            WHERE status = "approved"
            AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ')->find()['total'] ?? 0;
    }
    
    /**
     * Get total revenue from approved bookings in the last 7 days
     * 
     * @return float Total revenue amount
     */
    public function getRevenueWeek() {
        return $this->db->query('
            SELECT SUM(total_cost) as total FROM bookings
            WHERE status = "approved"
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ')->find()['total'] ?? 0;
    }
    
    /**
     * Get total revenue from approved bookings in the last 30 days
     * 
     * @return float Total revenue amount
     */
    public function getRevenueMonth() {
        return $this->db->query('
            SELECT SUM(total_cost) as total FROM bookings
            WHERE status = "approved"
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ')->find()['total'] ?? 0;
    }
    
    /**
     * Get count of charge points created in the last 7 days
     * 
     * @return int Number of charge points
     */
    public function getChargePointsWeek() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM charge_points
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ')->find()['count'];
    }
    
    /**
     * Get count of charge points created in the last 30 days
     * 
     * @return int Number of charge points
     */
    public function getChargePointsMonth() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM charge_points
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ')->find()['count'];
    }
    
    // Homeowner dashboard methods
    /**
     * Get charge point belonging to a specific homeowner
     * 
     * @param int $homeownerId The ID of the homeowner
     * @return array|null Charge point details
     */
    public function getHomeownerChargePoint($homeownerId) {
        return $this->db->query('
            SELECT * FROM charge_points
            WHERE homeowner_id = ?
        ', [$homeownerId])->find();
    }
    
    /**
     * Get count of pending bookings for a charge point
     * 
     * @param int $chargePointId The ID of the charge point
     * @return int Number of pending bookings
     */
    public function getPendingBookingsCount($chargePointId) {
        return $this->db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE charge_point_id = ? AND status = "pending"
        ', [$chargePointId])->find()['count'] ?? 0;
    }
    
    /**
     * Get count of approved bookings for a charge point
     * 
     * @param int $chargePointId The ID of the charge point
     * @return int Number of approved bookings
     */
    public function getApprovedBookingsCount($chargePointId) {
        return $this->db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE charge_point_id = ? AND status = "approved"
        ', [$chargePointId])->find()['count'] ?? 0;
    }
    
    /**
     * Get total revenue earned from a charge point
     * 
     * @param int $chargePointId The ID of the charge point
     * @return float Total revenue amount
     */
    public function getTotalRevenue($chargePointId) {
        return $this->db->query('
            SELECT COALESCE(SUM(total_cost), 0) as total FROM bookings
            WHERE charge_point_id = ? AND status = "approved"
        ', [$chargePointId])->find()['total'] ?? 0;
    }
    
    /**
     * Get list of pending bookings for a charge point
     * 
     * @param int $chargePointId The ID of the charge point
     * @return array List of pending bookings
     */
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
    
    /**
     * Get recent messages for a user
     * 
     * @param int $userId The ID of the user
     * @return array List of recent messages
     */
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
    /**
     * Get count of future approved bookings for a user
     * 
     * @param int $userId The ID of the user
     * @return int Number of upcoming bookings
     */
    public function getUpcomingBookings($userId) {
        return $this->db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE user_id = ? AND status = "approved" AND end_time > NOW()
        ', [$userId])->find()['count'] ?? 0;
    }
    
    /**
     * Get count of pending bookings for a user
     * 
     * @param int $userId The ID of the user
     * @return int Number of pending bookings
     */
    public function getPendingBookingsForUser($userId) {
        return $this->db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE user_id = ? AND status = "pending"
        ', [$userId])->find()['count'] ?? 0;
    }
    
    /**
     * Get count of past approved bookings for a user
     * 
     * @param int $userId The ID of the user
     * @return int Number of completed bookings
     */
    public function getCompletedBookings($userId) {
        return $this->db->query('
            SELECT COUNT(*) as count FROM bookings
            WHERE user_id = ? AND status = "approved" AND end_time < NOW()
        ', [$userId])->find()['count'] ?? 0;
    }
    
    /**
     * Get total amount spent by a user on approved bookings
     * 
     * @param int $userId The ID of the user
     * @return float Total amount spent
     */
    public function getTotalSpent($userId) {
        return $this->db->query('
            SELECT COALESCE(SUM(total_cost), 0) as total FROM bookings
            WHERE user_id = ? AND status = "approved"
        ', [$userId])->find()['total'] ?? 0;
    }
    
    /**
     * Get the next upcoming booking for a user
     * 
     * @param int $userId The ID of the user
     * @return array|null Next booking details
     */
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
    
    /**
     * Get count of available charge points
     * 
     * @return int Number of available charge points
     */
    public function getAvailableChargePointsCount() {
        return $this->db->query('
            SELECT COUNT(*) as count FROM charge_points
            WHERE is_available = 1
        ')->find()['count'] ?? 0;
    }
    
    /**
     * Get average price per kWh across all available charge points
     * 
     * @return float Average price per kWh
     */
    public function getAveragePrice() {
        return $this->db->query('
            SELECT AVG(price_per_kwh) as avg FROM charge_points
            WHERE is_available = 1
        ')->find()['avg'] ?? 0;
    }
    
    /**
     * Get list of recent bookings for a user
     * 
     * @param int $userId The ID of the user
     * @return array List of recent bookings
     */
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