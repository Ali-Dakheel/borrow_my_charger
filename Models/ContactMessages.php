<?php

use Core\Database;

class ContactMessage
{
    protected $db;
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    // public function createMessage($bookingId, $senderId, $recipientId, $message)
    // {
    //     $sql = "INSERT INTO contact_messages (booking_id, sender_id, recipient_id, message) 
    //             VALUES (:booking_id, :sender_id, :recipient_id, :message)";
    //     $this->db->query($sql, [
    //         'booking_id' => $bookingId,
    //         'sender_id' => $senderId,
    //         'recipient_id' => $recipientId,
    //         'message' => $message
    //     ]);
    // }

    // public function getMessagesByBookingId($bookingId)
    // {
    //     $sql = "SELECT * FROM contact_messages WHERE booking_id = :booking_id ORDER BY sent_at ASC";
    //     return $this->db->findAll($sql, ['booking_id' => $bookingId]);
    // }
    public function create($data)
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

    public function getMessagesByBookingId($bookingId)
    {
        try {
            return $this->db->query(
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
        } catch (PDOException $e) {
            throw new Exception("Failed to fetch messages: " . $e->getMessage());
        }
    }

    public function deleteMessage($messageId)
    {
        $sql = "DELETE FROM contact_messages WHERE id = :id";
        $this->db->query($sql, ['id' => $messageId]);
    }
}
