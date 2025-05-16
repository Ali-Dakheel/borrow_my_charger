<?php
require_once(__DIR__ . '/ContactMessageData.php');

use Core\Database;

/**
 * Class ContactMessageDataset
 *
 * Data access layer for contact messages related to bookings. Provides methods to create,
 * retrieve, and delete messages exchanged between users regarding a booking.
 */
class ContactMessageDataset
{
    /**
     * @var Database Database connection instance
     */
    protected $db;
    
    /**
     * ContactMessageDataset constructor.
     *
     * @param Database $db The database connection to use for queries
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new contact message record.
     *
     * @param array $data Associative array with keys:
     *                    - booking_id: int Booking ID
     *                    - sender_id: int Sender user ID
     *                    - recipient_id: int Recipient user ID
     *                    - message: string Message content
     * @return mixed Result of the insert query
     * @throws Exception If the database insert fails
     */
    public function create(array $data)
    {
        try {
            return $this->db->query(
                'INSERT INTO contact_messages (booking_id, sender_id, recipient_id, message) 
                 VALUES (?, ?, ?, ?)',
                [
                    $data['booking_id'],
                    $data['sender_id'],
                    $data['recipient_id'],
                    $data['message']
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("Failed to send message: " . $e->getMessage());
        }
    }

    /**
     * Retrieve all messages for a given booking ID, including sender and recipient names.
     *
     * @param int $bookingId Booking ID to fetch messages for
     * @return array List of message records with sender_name, recipient_name, sender_role
     * @throws Exception If the database query fails
     */
    public function getMessagesByBookingId(int $bookingId): array
    {
        try {
            $rows = $this->db->query(
                'SELECT cm.*, 
                        u_sender.name as sender_name, 
                        u_recipient.name as recipient_name,
                        u_sender.role as sender_role
                 FROM contact_messages cm
                 JOIN users u_sender ON cm.sender_id = u_sender.id
                 JOIN users u_recipient ON cm.recipient_id = u_recipient.id
                 WHERE cm.booking_id = ?
                 ORDER BY cm.sent_at ASC',
                [$bookingId]
            )->findAll();
            
            // Return raw rows for view compatibility
            return $rows;
        } catch (PDOException $e) {
            throw new Exception("Failed to fetch messages: " . $e->getMessage());
        }
    }

    /**
     * Delete a contact message by its ID.
     *
     * @param int $messageId Message ID to delete
     * @return mixed Result of the delete query
     */
    public function deleteMessage(int $messageId)
    {
        return $this->db->query("DELETE FROM contact_messages WHERE id = ?", [$messageId]);
    }
    
    /**
     * Retrieve a single contact message by its ID.
     *
     * @param int $id Message ID
     * @return ContactMessageData|null ContactMessageData object or null if not found
     */
    public function getById(int $id): ?ContactMessageData
    {
        $row = $this->db->query('SELECT * FROM contact_messages WHERE id = ?', [$id])->find();
        
        if (!$row) {
            return null;
        }
        
        return new ContactMessageData($row);
    }
}
