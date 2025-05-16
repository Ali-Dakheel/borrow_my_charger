<?php
require_once(__DIR__ . '/BookingData.php');

use Core\Database;

/**
 * Class BookingDataset
 *
 * Data access layer for booking records. Provides methods to retrieve,
 * create, update, and cancel bookings with various scopes and details.
 */
class BookingDataset
{
    /**
     * @var Database Database connection instance
     */
    protected $db;

    /**
     * BookingDataset constructor.
     *
     * @param Database $db The database connection to use for queries
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }
    /**
     * Gets all bookings for a specific charge point
     * 
     * @param int $chargePointId The ID of the charge point
     * @return array Array of booking data
     */
    public function getBookingsByChargePointId($chargePointId)
    {
        try {
            return $this->db->query(
                'SELECT b.*, u.name as user_name 
             FROM bookings b
             JOIN users u ON b.user_id = u.id
             WHERE b.charge_point_id = ?
             ORDER BY b.created_at DESC',
                [$chargePointId]
            )->findAll();
        } catch (PDOException $e) {
            throw new Exception("Failed to fetch bookings: " . $e->getMessage());
        }
    }
    /**
     * Retrieve bookings for a homeowner with detailed charge point and renter info.
     *
     * @param int $homeownerId The homeowner's user ID
     * @return array List of detailed booking records
     */
    public function getByHomeownerIdWithDetails($homeownerId)
    {
        return $this->db->query(
            'SELECT b.*, 
                    cp.address AS charge_point_address,
                    cp.postcode AS charge_point_postcode,
                    cp.price_per_kwh,
                    u.name AS renter_name,
                    u.id AS renter_id
             FROM bookings b
             JOIN charge_points cp ON b.charge_point_id = cp.id
             JOIN users u ON b.user_id = u.id
             WHERE cp.homeowner_id = ?
             ORDER BY b.start_time DESC',
            [$homeownerId]
        )->findAll();
    }
    /**
     * Retrieve bookings for a rental user with homeowner details.
     *
     * @param int $userId The rental user's ID
     * @return array List of booking records including homeowner info
     */
    public function getByRentalUserIdWithDetails($userId)
    {
        return $this->db->query(
            'SELECT b.*, 
                    cp.address AS charge_point_address,
                    cp.postcode AS charge_point_postcode,
                    cp.price_per_kwh,
                    u.name AS homeowner_name
             FROM bookings b
             JOIN charge_points cp ON b.charge_point_id = cp.id
             JOIN users u ON cp.homeowner_id = u.id
             WHERE b.user_id = ?
             ORDER BY b.start_time DESC',
            [$userId]
        )->findAll();
    }
    /**
     * Update the status of a booking.
     *
     * @param int $id Booking ID
     * @param string $status New status (pending|approved|cancelled|declined)
     * @return mixed The result of the update query
     * @throws InvalidArgumentException If the status is invalid
     */
    public function updateBookingStatus($id, $status)
    {
        $validStatuses = ['pending', 'approved', 'cancelled', 'declined'];

        if (!in_array($status, $validStatuses)) {
            throw new InvalidArgumentException("Invalid booking status");
        }

        return $this->db->query(
            "UPDATE bookings SET status = ? WHERE id = ?",
            [$status, $id]
        );
    }
    /**
     * Cancel a booking by setting its status to 'cancelled'.
     *
     * @param int $id Booking ID
     * @return mixed The result of the update query
     */
    public function cancelBooking($id)
    {
        return $this->db->query(
            "UPDATE bookings SET status = 'cancelled' WHERE id = ?",
            [$id]
        );
    }
    /**
     * Create a new booking record.
     *
     * @param array $data Booking data including user_id, charge_point_id, start_time, end_time, total_cost, status
     * @return mixed The result of the insert query
     */
    public function create($data)
    {
        return $this->db->query(
            'INSERT INTO `bookings`(user_id, charge_point_id, start_time, end_time, total_cost, status)
             VALUES (?,?,?,?,?,?)',
            [
                $data['user_id'],
                $data['charge_point_id'],
                $data['start_time'],
                $data['end_time'],
                $data['total_cost'],
                $data['status'],
            ]
        );
    }
    /**
     * Retrieve a booking with charge point and homeowner details for a specific user.
     *
     * @param int $bookingId Booking ID
     * @param int $userId Rental user ID
     * @return array|null ['booking' => BookingData, 'details' => array] or null if not found
     */
    public function getBookingWithDetailsByIdForUser($bookingId, $userId)
    {
        $data = $this->db->query(
            'SELECT b.*, 
                    cp.address AS charge_point_address,
                    cp.postcode AS charge_point_postcode,
                    cp.latitude, cp.longitude,
                    cp.price_per_kwh,
                    u.name AS homeowner_name,
                    u.id AS homeowner_id
             FROM bookings b
             JOIN charge_points cp ON b.charge_point_id = cp.id
             JOIN users u ON cp.homeowner_id = u.id
             WHERE b.id = ? AND b.user_id = ?',
            [$bookingId, $userId]
        )->find();

        if (!$data) {
            return null;
        }

        // Create a BookingData object with the core booking data
        $booking = new BookingData($data);

        // Return both the booking data and the full row for template use
        return [
            'booking' => $booking,
            'details' => $data
        ];
    }
    /**
     * Retrieve a booking with charge point and renter details for a specific homeowner.
     *
     * @param int $bookingId Booking ID
     * @param int $homeownerId Homeowner user ID
     * @return array|null ['booking' => BookingData, 'details' => array] or null if not found
     */
    public function getBookingWithDetailsByIdForHomeowner($bookingId, $homeownerId)
    {
        $data = $this->db->query(
            'SELECT b.*, 
                    cp.address AS charge_point_address,
                    cp.postcode AS charge_point_postcode,
                    cp.latitude, cp.longitude,
                    cp.price_per_kwh,
                    u.name AS renter_name
             FROM bookings b
             JOIN charge_points cp ON b.charge_point_id = cp.id
             JOIN users u ON b.user_id = u.id
             WHERE b.id = ? AND cp.homeowner_id = ?',
            [$bookingId, $homeownerId]
        )->find();

        if (!$data) {
            return null;
        }

        // Create a BookingData object with the core booking data
        $booking = new BookingData($data);

        // Return both the booking data and the full row for template use
        return [
            'booking' => $booking,
            'details' => $data
        ];
    }
    /**
     * Retrieve a single booking by its ID.
     *
     * @param int $id Booking ID
     * @return BookingData|null The BookingData object or null if not found
     */
    public function getById($id)
    {
        $row = $this->db->query('SELECT * FROM bookings WHERE id = ?', [$id])->find();

        if (!$row) {
            return null;
        }

        return new BookingData($row);
    }
}
