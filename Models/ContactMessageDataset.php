<?php
// ContactMessageDataset.php
require_once(__DIR__ . '/ContactMessageData.php');

use Core\Database;

class ContactMessageDataset
{
    protected $db;
    
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

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
            
            // For compatibility with the existing view, return the array directly
            // In a more complete refactoring, we would return ContactMessageData objects
            return $rows;
            
            // Alternative implementation that returns objects but might require view changes:
            /*
            $messages = [];
            foreach ($rows as $row) {
                $messages[] = new ContactMessageData($row);
            }
            return $messages;
            */
        } catch (PDOException $e) {
            throw new Exception("Failed to fetch messages: " . $e->getMessage());
        }
    }

    public function deleteMessage($messageId)
    {
        return $this->db->query("DELETE FROM contact_messages WHERE id = ?", [$messageId]);
    }
    
    public function getById($id)
    {
        $row = $this->db->query('SELECT * FROM contact_messages WHERE id = ?', [$id])->find();
        
        if (!$row) {
            return null;
        }
        
        return new ContactMessageData($row);
    }
}