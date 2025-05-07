<?php

use Core\Database;

class ContactMessage
{
    protected $db;
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function createMessage($bookingId, $senderId, $recipientId, $message)
    {
        $sql = "INSERT INTO contact_messages (booking_id, sender_id, recipient_id, message) 
                VALUES (:booking_id, :sender_id, :recipient_id, :message)";
        $this->db->query($sql, [
            'booking_id' => $bookingId,
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'message' => $message
        ]);
    }

    public function getMessagesByBookingId($bookingId)
    {
        $sql = "SELECT * FROM contact_messages WHERE booking_id = :booking_id ORDER BY sent_at ASC";
        return $this->db->findAll($sql, ['booking_id' => $bookingId]);
    }

    public function deleteMessage($messageId)
    {
        $sql = "DELETE FROM contact_messages WHERE id = :id";
        $this->db->query($sql, ['id' => $messageId]);
    }
}
